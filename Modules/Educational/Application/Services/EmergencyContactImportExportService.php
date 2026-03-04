<?php

namespace Modules\Educational\Application\Services;

use Modules\Educational\Domain\Models\TraineeProfile;
use Modules\Educational\Domain\Models\TraineeEmergencyContact;
use Modules\Educational\Domain\Models\Governorate;
use Modules\Users\Domain\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmergencyContactImportExportService
{
    /**
     * Columns for the CSV template and data
     */
    protected $columns = [
        'student_email',
        'relation',
        'name',
        'national_id',
        'phone',
        'phone2',
        'email',
        'address',
        'governorate_id'
    ];

    /**
     * Download an empty template
     */
    public function downloadTemplate()
    {
        $headers = collect($this->columns)->map(function ($col) {
            return __('educational::messages.emergency_contact_columns.' . $col) !== 'educational::messages.emergency_contact_columns.' . $col
                ? __('educational::messages.emergency_contact_columns.' . $col)
                : $col;
        })->toArray();

        // Sample row definition
        $sampleRow = [
            'student@example.com',
            'الأب',
            'محمد أحمد',
            '12345678901234',
            '01000000000',
            '',
            'father@example.com',
            '123 شارع السلام',
            '1'
        ];

        $output = fopen('php://temp', 'w');
        // Add BOM for Arabic Excel support
        fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

        fputcsv($output, $headers);
        fputcsv($output, $sampleRow);

        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        return [
            'filename' => 'emergency_contacts_template.csv',
            'content' => $content
        ];
    }

    /**
     * Export existing data to CSV
     */
    public function export(array $filters = [])
    {
        $query = TraineeEmergencyContact::with(['traineeProfile.user', 'governorate']);

        // add any filters if needed...

        $contacts = $query->get();

        $headers = collect($this->columns)->map(function ($col) {
            return __('educational::messages.emergency_contact_columns.' . $col) !== 'educational::messages.emergency_contact_columns.' . $col
                ? __('educational::messages.emergency_contact_columns.' . $col)
                : $col;
        })->toArray();

        $output = fopen('php://temp', 'w');
        // Add BOM
        fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
        fputcsv($output, $headers);

        foreach ($contacts as $contact) {
            $row = [
                $contact->traineeProfile->user->email ?? '',
                $contact->relation,
                $contact->name,
                $contact->national_id,
                $contact->phone,
                $contact->phone2,
                $contact->email,
                $contact->address,
                $contact->governorate ? $contact->governorate->name_ar : '',
            ];
            fputcsv($output, $row);
        }

        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        return [
            'filename' => 'emergency_contacts_export_' . date('Y-m-d') . '.csv',
            'content' => $content
        ];
    }

    /**
     * Import data from CSV
     */
    public function processImport($filePath)
    {
        $handle = fopen($filePath, 'r');
        $bom = fread($handle, 3);
        if ($bom !== chr(0xEF) . chr(0xBB) . chr(0xBF)) {
            rewind($handle);
        }
        $headers = fgetcsv($handle);

        $expectedHeaders = collect($this->columns)->map(function ($col) {
            return __('educational::messages.emergency_contact_columns.' . $col) !== 'educational::messages.emergency_contact_columns.' . $col
                ? __('educational::messages.emergency_contact_columns.' . $col)
                : $col;
        })->toArray();

        if (!$headers || count($headers) < count($expectedHeaders) || count(array_diff($expectedHeaders, $headers)) > 0) {
            fclose($handle);
            throw new \Exception(__('educational::messages.invalid_template_file') ?? "الملف غير مطابق للقالب المعتمد.");
        }

        $count = 0;
        $errors = [];
        $rowNum = 1;

        DB::beginTransaction();

        try {
            while (($data = fgetcsv($handle)) !== false) {
                $rowNum++;

                if (count($data) < count($this->columns)) {
                    $errors[] = __('educational::messages.row_number') . " $rowNum: " . __('educational::messages.error_missing_data');
                    continue;
                }

                $data = array_slice($data, 0, count($this->columns));
                $row = array_combine($this->columns, array_pad($data, count($this->columns), ''));

                if (empty($row['student_email']) || empty($row['relation']) || empty($row['name']) || empty($row['phone'])) {
                    $errors[] = __('educational::messages.row_number') . " $rowNum: " . __('educational::messages.error_missing_data');
                    continue;
                }

                $user = User::where('email', $row['student_email'])->first();
                $profile = $user ? TraineeProfile::where('user_id', $user->id)->first() : null;

                if (!$user || !$profile) {
                    $errors[] = __('educational::messages.row_number') . " $rowNum: " . __('educational::messages.student_not_found');
                    continue;
                }

                $governorateId = $this->resolveId(Governorate::class, $row['governorate_id']);

                try {
                    // Always create a new emergency contact
                    TraineeEmergencyContact::create([
                        'trainee_profile_id' => $profile->id,
                        'relation' => $row['relation'],
                        'name' => $row['name'],
                        'national_id' => $row['national_id'] ?: null,
                        'phone' => $row['phone'],
                        'phone2' => $row['phone2'] ?: null,
                        'email' => $row['email'] ?: null,
                        'address' => $row['address'] ?: null,
                        'governorate_id' => $governorateId,
                    ]);

                    $count++;
                } catch (\Exception $e) {
                    $errors[] = __('educational::messages.row_number') . " $rowNum: " . $this->sanitizeErrorMessage($e->getMessage());
                }
            }

            if (!empty($errors)) {
                DB::rollBack();
                $count = 0;
            } else {
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Emergency Contact Import failed: " . $e->getMessage());
            $count = 0;
        }

        fclose($handle);
        return ['count' => $count, 'errors' => $errors];
    }

    /**
     * Resolve ID from either numeric ID or Name
     */
    private function resolveId($model, $value)
    {
        if (empty($value))
            return null;
        if (is_numeric($value))
            return (int) $value;

        try {
            $record = $model::where('name', 'ilike', "%{$value}%")
                ->orWhere('name_ar', 'ilike', "%{$value}%")
                ->orWhere('name_en', 'ilike', "%{$value}%")
                ->first();
            return $record ? $record->id : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Sanitize technical DB errors into readable text
     */
    private function sanitizeErrorMessage($message)
    {
        if (str_contains($message, 'foreign key constraint')) {
            return __('educational::messages.error_foreign_key');
        }
        if (str_contains($message, 'Duplicate entry') || str_contains($message, 'duplicate key')) {
            return __('educational::messages.error_duplicate_db');
        }
        if (str_contains($message, 'not-null constraint')) {
            return __('educational::messages.error_missing_data');
        }
        if (str_contains($message, 'numeric value out of range')) {
            return __('educational::messages.error_numeric');
        }
        return __('educational::messages.error_general');
    }
}
