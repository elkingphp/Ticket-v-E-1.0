<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Session;
use Illuminate\Routing\Controllers\HasMiddleware;
use Modules\Core\Application\Services\SessionManagerService;
use Modules\Core\Application\Services\ActivityFeedService;
use Modules\Core\Domain\Models\NotificationEventType;
use Modules\Core\Domain\Models\NotificationPreference;
use Modules\Core\Application\Jobs\ExportUserData;

class ProfileController extends Controller implements HasMiddleware
{
    protected $sessionManager;
    protected $activityFeed;
    protected $notificationResolver;

    public function __construct(
        SessionManagerService $sessionManager,
        ActivityFeedService $activityFeed,
        \Modules\Core\Application\Services\NotificationPreferenceResolver $notificationResolver
    ) {
        $this->sessionManager = $sessionManager;
        $this->activityFeed = $activityFeed;
        $this->notificationResolver = $notificationResolver;
    }

    public static function middleware(): array
    {
        return [
            'auth',
        ];
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $sessions = $this->sessionManager->getUserSessions($user->id);
        $activities = $this->activityFeed->getUserTimeline($user->id);
        $notificationTypes = NotificationEventType::all();
        $userPreferences = $user->notificationPreferences->keyBy('event_type');
        $timezones = \DateTimeZone::listIdentifiers();
        $exports = $this->getUserExports($user->id);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('core::profile.partials.activity_items', compact('activities'))->render(),
                'hasMore' => $activities->hasMorePages(),
                'nextPage' => $activities->currentPage() + 1
            ]);
        }

        return view('core::profile.index', compact('user', 'sessions', 'activities', 'notificationTypes', 'userPreferences', 'timezones', 'exports'));
    }

    public function update(Request $request)
    {
        if (!auth()->user()->can('update profile')) {
            return back()->with('error', __('core::profile.unauthorized'));
        }

        $user = auth()->user();

        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'language' => ['required', 'string', Rule::in(['en', 'ar'])],
            'timezone' => ['required', 'string'],
        ]);

        $user->fill($request->only([
            'first_name',
            'last_name',
            'email',
            'phone',
            'language',
            'timezone',
        ]));

        $user->updateSecurityScore(); // This calls save() once with all changes

        // Trigger notification
        $user->notify(new \App\Notifications\GenericAlert('profile_update', [
            'title' => __('core::profile.update_success'),
            'message' => __('core::profile.profile_updated_success'),
            'priority' => 'info',
            'action_url' => route('profile.index')
        ]));

        return response()->json([
            'success' => true,
            'message' => __('core::profile.update_success')
        ]);


    }

    public function updateAvatar(Request $request)
    {
        if (!auth()->user()->can('update profile')) {
            return response()->json(['success' => false, 'message' => __('Unauthorized')], 403);
        }

        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $user = auth()->user();

        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $user->update(['avatar' => $path]);

            return response()->json([
                'success' => true,
                'message' => __('core::profile.avatar_update_success'),
                'avatar_url' => asset('storage/' . $path)
            ]);
        }

        return response()->json(['success' => false, 'message' => __('core::profile.no_file_uploaded')]);
    }

    public function deleteAvatar(Request $request)
    {
        if (!auth()->user()->can('update profile')) {
            return response()->json(['success' => false, 'message' => __('core::profile.unauthorized')], 403);
        }

        $user = auth()->user();

        if ($user->avatar) {
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }
            $user->update(['avatar' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => __('core::profile.avatar_removed_success'),
            'initials' => $user->initials
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        auth()->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('core::profile.password_update_success')
        ]);
    }

    public function logoutOtherDevices(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        \Illuminate\Support\Facades\Auth::logoutOtherDevices($request->password);

        return back()->with('success', __('core::profile.sessions_terminate_others_success'));
    }

    public function sudo()
    {
        return view('core::profile.sudo');
    }

    public function sudoConfirm(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        Session::put('auth.sudo_verified_at', time());

        if ($request->ajax() || $request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'success' => true,
                'message' => __('core::profile.reauth_success')
            ]);
        }

        $intendedUrl = Session::pull('auth.sudo_intended_url', route('profile.index'));

        return redirect()->to($intendedUrl)->with('success', __('core::profile.reauth_success'));
    }

    public function terminateSession(Request $request, string $id)
    {
        $deleted = $this->sessionManager->terminateSession($id, auth()->id());

        return response()->json([
            'success' => $deleted,
            'message' => $deleted ? __('core::profile.sessions_terminate_success') : __('core::profile.failed_terminate_session')
        ]);
    }

    public function terminateOtherSessions(Request $request)
    {
        $this->sessionManager->terminateOtherSessions(auth()->id());

        return response()->json([
            'success' => true,
            'message' => __('core::profile.sessions_terminate_others_success')
        ]);
    }

    public function updateNotifications(Request $request)
    {
        $this->notificationResolver->updateUserPreferences(auth()->id(), $request->input('prefs', []));

        return response()->json([
            'success' => true,
            'message' => __('core::profile.notification_preferences_success')
        ]);
    }

    public function destroy(Request $request)
    {
        $user = auth()->user();
        $user->scheduled_for_deletion_at = now()->addDays(14);
        $user->save();

        // Terminate all sessions except current one
        $this->sessionManager->terminateOtherSessions($user->id, session()->getId());

        return response()->json([
            'success' => true,
            'message' => __('core::profile.delete_scheduled_success')
        ]);
    }

    public function cancelDeletion(Request $request)
    {
        $user = auth()->user();
        $user->scheduled_for_deletion_at = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => __('core::profile.delete_cancelled_success')
        ]);
    }

    public function exportData(Request $request)
    {
        // Dispatch Export Job
        ExportUserData::dispatch(auth()->user());

        return response()->json([
            'success' => true,
            'message' => __('core::profile.export_queued_success')
        ]);
    }

    public function downloadExport($filename)
    {
        $path = 'exports/' . $filename;

        if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($path)) {
            abort(404);
        }

        // Check if file belongs to user
        if (!str_contains($filename, "user_data_" . auth()->id() . "_")) {
            abort(403);
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = \Illuminate\Support\Facades\Storage::disk('local');
        return $disk->download($path);
    }

    public function deleteExport($filename)
    {
        $path = 'exports/' . $filename;

        if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($path)) {
            return response()->json(['success' => false, 'message' => __('File not found')], 404);
        }

        // Check if file belongs to user
        if (!str_contains($filename, "user_data_" . auth()->id() . "_")) {
            return response()->json(['success' => false, 'message' => __('Unauthorized')], 403);
        }

        \Illuminate\Support\Facades\Storage::disk('local')->delete($path);

        return response()->json([
            'success' => true,
            'message' => __('Export deleted successfully')
        ]);
    }


    private function getUserExports($userId)
    {
        $directory = 'exports';
        $files = \Illuminate\Support\Facades\Storage::disk('local')->files($directory);
        $userExports = [];
        foreach ($files as $file) {
            $filename = basename($file);
            if (str_contains($filename, "user_data_{$userId}_")) {
                $userExports[] = [
                    'name' => $filename,
                    'path' => $file,
                    'size' => round(\Illuminate\Support\Facades\Storage::disk('local')->size($file) / 1024, 2) . ' KB',
                    'created_at' => date('Y-m-d H:i:s', \Illuminate\Support\Facades\Storage::disk('local')->lastModified($file)),
                    'url' => route('profile.export.download', ['filename' => $filename]),
                ];
            }
        }

        // Sort by created_at desc
        usort($userExports, function ($a, $b) {
            return $b['created_at'] <=> $a['created_at'];
        });

        return $userExports;
    }
}