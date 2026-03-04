<?php

namespace Modules\Educational\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Educational\Domain\Models\InstructorProfile;
use Modules\Educational\Domain\Models\Governorate;
use Modules\Educational\Domain\Models\Track;
use Modules\Educational\Domain\Models\SessionType;
use Modules\Users\Domain\Models\User;
use Modules\Educational\Application\Services\InstructorImportExportService;
use Modules\Core\Domain\Models\ApprovalRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Settings\Domain\Interfaces\SettingRepositoryInterface;

class InstructorController extends Controller
{
    public function export(Request $request, InstructorImportExportService $service)
    {
        $data = $service->export($request->all());
        return response($data['content'])
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$data['filename']}");
    }

    public function downloadTemplate(InstructorImportExportService $service)
    {
        $data = $service->downloadTemplate();
        return response($data['content'])
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$data['filename']}");
    }

    public function import(Request $request, InstructorImportExportService $service)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt'
        ]);

        try {
            $result = $service->import($request->file('import_file')->getRealPath());

            $msg = __('educational::messages.import_success', ['count' => $result['count']]);
            if (count($result['errors']) > 0) {
                return redirect()->back()->with('warning', $msg . ' ولكن تم رصد بعض الأخطاء: ' . implode(', ', array_slice($result['errors'], 0, 3)));
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('educational::messages.import_error', ['message' => $e->getMessage()]));
        }
    }

    public function index(Request $request)
    {
        $query = InstructorProfile::with(['user', 'companies', 'approvalRequests', 'track', 'sessionTypes']);

        // Filtering
        if ($request->filled('track_id')) {
            $query->where('track_id', $request->track_id);
        }
        if ($request->filled('session_type_id')) {
            $query->whereHas('sessionTypes', function ($q) use ($request) {
                $q->where('education.session_types.id', $request->session_type_id);
            });
        }
        if ($request->filled('company_id')) {
            $query->whereHas('companies', function ($q) use ($request) {
                $q->where('training_companies.id', $request->company_id);
            });
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('arabic_name', 'ilike', "%{$search}%")
                    ->orWhere('english_name', 'ilike', "%{$search}%")
                    ->orWhereHas('user', function ($sq) use ($search) {
                        $sq->where('username', 'ilike', "%{$search}%")
                            ->orWhere('email', 'ilike', "%{$search}%")
                            ->orWhere('first_name', 'ilike', "%{$search}%")
                            ->orWhere('last_name', 'ilike', "%{$search}%");
                    });
            });
        }

        $stats = [
            'total' => InstructorProfile::count(),
            'active' => InstructorProfile::where('status', 'active')->count(),
            'suspended' => InstructorProfile::where('status', 'suspended')->count(),
            'pending' => ApprovalRequest::where('approvable_type', InstructorProfile::class)
                ->where('status', 'pending')
                ->count()
        ];

        $perPage = (int) get_setting('educational_instructors_per_page', 12);
        if (!in_array($perPage, [12, 24, 36]))
            $perPage = 12;

        $instructors = $query->latest()->paginate($perPage)->withQueryString();

        $tracks = Track::active()->get();
        $sessionTypes = SessionType::where('is_active', true)->get();
        $companies = \Modules\Educational\Domain\Models\TrainingCompany::where('status', 'active')->get();

        return view('educational::instructors.index', compact('instructors', 'stats', 'tracks', 'sessionTypes', 'companies'));
    }

    public function create()
    {
        $companies = \Modules\Educational\Domain\Models\TrainingCompany::with('jobProfiles')
            ->where('status', 'active')->get();
        $governorates = Governorate::where('status', 'active')->get();
        $tracks = Track::active()->get();
        $sessionTypes = SessionType::where('is_active', true)->get();

        return view('educational::instructors.create', compact('companies', 'governorates', 'tracks', 'sessionTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:' . User::class . ',email',
            'phone' => 'nullable|string|max:50',
            'username' => 'required|string|unique:' . User::class . ',username',
            'password' => 'required|min:8',
            'employment_type' => 'required|in:full_time,part_time,contractor',
            'status' => 'required|in:active,inactive,suspended',
            'arabic_name' => 'nullable|string|max:255',
            'english_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'national_id' => 'nullable|string|max:50',
            'passport_number' => 'nullable|string|max:50',
            'governorate_id' => 'nullable|exists:' . Governorate::class . ',id',
            'track_id' => 'nullable|exists:' . Track::class . ',id',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'company_assignments' => 'nullable|array',
            'company_assignments.*' => 'nullable|array', // key is company_id, value is array of job_profile_ids
            'session_types' => 'nullable|array',
            'session_types.*' => 'exists:session_types,id',
        ], [], [
            'session_types.*' => 'نوع المحاضرة'
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'status' => 'active',
                'joined_at' => now(),
            ]);

            \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Instructor', 'guard_name' => 'web']);
            $user->assignRole('Instructor');

            $instructor = InstructorProfile::create([
                'user_id' => $user->id,
                'bio' => $request->bio,
                'specialization_notes' => $request->specialization,
                'employment_type' => $request->employment_type,
                'status' => $request->status,
                'arabic_name' => $request->arabic_name,
                'english_name' => $request->english_name,
                'address' => $request->address,
                'national_id' => $request->national_id,
                'passport_number' => $request->passport_number,
                'governorate_id' => $request->governorate_id,
                'track_id' => $request->track_id,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
            ]);

            if ($request->has('company_assignments') && is_array($request->company_assignments)) {
                $syncData = [];
                foreach ($request->company_assignments as $companyId => $jobProfileIds) {
                    if (is_array($jobProfileIds)) {
                        foreach ($jobProfileIds as $jobProfileId) {
                            // Sync expects pivot id or arrays for multiples, but since we have a custom pivot table
                            // we'll insert them manually or format sync data properly.
                            // However, we can't sync M:M:M easily with default sync. It's better to use DB insert.
                            DB::table('education.instructor_company_assignments')->insert([
                                'instructor_profile_id' => $instructor->id,
                                'company_id' => $companyId,
                                'job_profile_id' => $jobProfileId,
                                'assigned_at' => now(),
                                'status' => 'active',
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }
                    }
                }
            }
            if ($request->has('session_types') && is_array($request->session_types)) {
                $instructor->sessionTypes()->sync($request->session_types);
            }
        });

        return redirect()->route('educational.instructors.index')->with('success', __('educational::messages.instructor_saved'));
    }

    public function show($id)
    {
        $instructor = InstructorProfile::with(['user', 'governorate', 'track', 'companies', 'approvalRequests'])
            ->findOrFail($id);

        // Get grouped assignments with profile names
        $assignments = DB::table('education.instructor_company_assignments as ica')
            ->join('education.training_companies as tc', 'ica.company_id', '=', 'tc.id')
            ->join('education.job_profiles as jp', 'ica.job_profile_id', '=', 'jp.id')
            ->where('ica.instructor_profile_id', $instructor->id)
            ->select('tc.name as company_name', 'jp.name as profile_name', 'jp.code as profile_code')
            ->get()
            ->groupBy('company_name');

        return view('educational::instructors.show', compact('instructor', 'assignments'));
    }

    public function edit($id)
    {
        $instructor = InstructorProfile::with(['user', 'companies'])->findOrFail($id);
        $companies = \Modules\Educational\Domain\Models\TrainingCompany::with('jobProfiles')
            ->where('status', 'active')->get();
        $governorates = Governorate::where('status', 'active')->get();
        $tracks = Track::active()->get();
        $sessionTypes = SessionType::where('is_active', true)->get();

        // Group assignments by company for easy access in view
        $instructorAssignments = DB::table('education.instructor_company_assignments')
            ->where('instructor_profile_id', $instructor->id)
            ->get()
            ->groupBy('company_id')
            ->map(function ($rows) {
                return $rows->pluck('job_profile_id')->toArray();
            })->toArray();

        $instructorSessionTypes = $instructor->sessionTypes->pluck('id')->toArray();

        return view('educational::instructors.edit', compact('instructor', 'companies', 'instructorAssignments', 'governorates', 'tracks', 'sessionTypes', 'instructorSessionTypes'));
    }

    public function update(Request $request, $id)
    {
        $instructor = InstructorProfile::findOrFail($id);
        $user = $instructor->user;

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:' . User::class . ',email,' . $user->id,
            'phone' => 'nullable|string|max:50',
            'username' => 'required|string|unique:' . User::class . ',username,' . $user->id,
            'employment_type' => 'required|in:full_time,part_time,contractor',
            'status' => 'required|in:active,inactive,suspended',
            'arabic_name' => 'nullable|string|max:255',
            'english_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'national_id' => 'nullable|string|max:50',
            'passport_number' => 'nullable|string|max:50',
            'governorate_id' => 'nullable|exists:' . Governorate::class . ',id',
            'track_id' => 'nullable|exists:' . Track::class . ',id',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'company_assignments' => 'nullable|array',
            'company_assignments.*' => 'nullable|array',
            'session_types' => 'nullable|array',
            'session_types.*' => 'exists:session_types,id',
        ], [], [
            'session_types.*' => 'نوع المحاضرة'
        ]);

        DB::transaction(function () use ($request, $instructor, $user) {
            $user->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'username' => $request->username,
            ]);

            if ($request->filled('password')) {
                $user->update(['password' => Hash::make($request->password)]);
            }

            $instructor->update([
                'bio' => $request->bio,
                'specialization_notes' => $request->specialization,
                'employment_type' => $request->employment_type,
                'status' => $request->status,
                'arabic_name' => $request->arabic_name,
                'english_name' => $request->english_name,
                'address' => $request->address,
                'national_id' => $request->national_id,
                'passport_number' => $request->passport_number,
                'governorate_id' => $request->governorate_id,
                'track_id' => $request->track_id,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
            ]);

            // Clean previous associations
            DB::table('education.instructor_company_assignments')
                ->where('instructor_profile_id', $instructor->id)
                ->delete();

            if ($request->has('company_assignments') && is_array($request->company_assignments)) {
                foreach ($request->company_assignments as $companyId => $jobProfileIds) {
                    if (is_array($jobProfileIds)) {
                        foreach ($jobProfileIds as $jobProfileId) {
                            DB::table('education.instructor_company_assignments')->insert([
                                'instructor_profile_id' => $instructor->id,
                                'company_id' => $companyId,
                                'job_profile_id' => $jobProfileId,
                                'assigned_at' => now(),
                                'status' => 'active',
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }
                    }
                }
            }
            if ($request->has('session_types') && is_array($request->session_types)) {
                $instructor->sessionTypes()->sync($request->session_types);
            } else {
                $instructor->sessionTypes()->detach();
            }
        });

        return redirect()->route('educational.instructors.index')->with('success', __('educational::messages.instructor_saved'));
    }

    public function destroy($id)
    {
        $instructor = InstructorProfile::findOrFail($id);
        $instructor->delete();
        return redirect()->route('educational.instructors.index')->with('success', __('educational::messages.instructor_deleted') ?? 'Instructor deleted.');
    }

    public function settings()
    {
        $currentPerPage = (int) get_setting('educational_instructors_per_page', 12);
        return view('modules.educational.instructors.settings', compact('currentPerPage'));
    }

    public function saveSettings(Request $request)
    {
        $request->validate([
            'per_page' => 'required|integer|in:12,24,36',
        ]);

        app(SettingRepositoryInterface::class)->setByKey(
            'educational_instructors_per_page',
            $request->per_page
        );

        return redirect()->route('educational.instructors.settings')
            ->with('success', 'تم حفظ الإعدادات بنجاح.');
    }
}
