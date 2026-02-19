<?php

namespace Modules\Core\Application\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Users\Domain\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ExportUserData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle(): void
    {
        // Increase memory and time limit for large exports
        ini_set('memory_limit', '512M');
        set_time_limit(600);

        $directory = 'exports';
        if (!Storage::disk('local')->exists($directory)) {
            Storage::disk('local')->makeDirectory($directory);
        }

        $timestamp = now()->format('YmdHis');
        $zipFilename = "user_data_{$this->user->id}_{$timestamp}.zip";
        $zipPath = Storage::disk('local')->path($directory . '/' . $zipFilename);

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            // 1. Profile Data
            $profileData = json_encode([
                'profile' => $this->user->toArray(),
                'exported_at' => now()->toDateTimeString(),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $zip->addFromString('profile.json', $profileData);

            // 2. Sessions (usually few)
            $sessions = \Illuminate\Support\Facades\DB::table('sessions')->where('user_id', $this->user->id)->get()->toArray();
            $zip->addFromString('sessions.json', json_encode($sessions, JSON_PRETTY_PRINT));

            // 3. Audit Logs (Can be very large, process carefully)
            $auditLogsCount = \Modules\Core\Domain\Models\AuditLog::where('user_id', $this->user->id)->count();

            if ($auditLogsCount > 0) {
                $tempFile = tempnam(sys_get_temp_dir(), 'audit_logs_');
                $handle = fopen($tempFile, 'w');
                fwrite($handle, "[\n");

                $first = true;
                \Modules\Core\Domain\Models\AuditLog::where('user_id', $this->user->id)
                    ->orderBy('id')
                    ->chunk(1000, function ($logs) use ($handle, &$first) {
                        foreach ($logs as $log) {
                            if (!$first) {
                                fwrite($handle, ",\n");
                            }
                            fwrite($handle, json_encode($log));
                            $first = false;
                        }
                    });

                fwrite($handle, "\n]");
                fclose($handle);

                $zip->addFile($tempFile, 'audit_logs.json');
            }

            $zip->close();

            if (isset($tempFile) && file_exists($tempFile)) {
                unlink($tempFile);
            }

            // Notify User
            $this->user->notify(new \App\Notifications\GenericAlert('data_export_ready', [
                'title' => __('core::profile.export_ready_title'),
                'message' => __('core::profile.export_ready_message'),
                'action_url' => route('profile.index') . '#privacy',
                'priority' => 'success'
            ]));
        } else {
            \Illuminate\Support\Facades\Log::error("Failed to create ZIP export for user {$this->user->id}");
        }
    }
}