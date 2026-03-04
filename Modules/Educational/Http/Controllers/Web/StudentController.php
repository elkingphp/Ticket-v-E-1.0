<?php

namespace Modules\Educational\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Educational\Domain\Models\TraineeProfile;
use Modules\Educational\Domain\Models\Governorate;
use Modules\Educational\Domain\Models\JobProfile;
use Modules\Educational\Domain\Models\Program;
use Modules\Educational\Domain\Models\Group;
use Modules\Educational\Domain\Models\TraineeEmergencyContact;
use Modules\Educational\Domain\Models\TraineeDocument;
use Modules\Users\Domain\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Modules\Educational\Application\Services\StudentImportExportService;
use Modules\Educational\Domain\Models\Attendance;
use Modules\Tickets\Domain\Models\Ticket;
use Modules\Core\Domain\Models\AuditLog;
use Modules\Educational\Domain\Models\Lecture;
use Modules\Educational\Domain\Models\Campus;
use Modules\Educational\Domain\Models\Building;
use Modules\Educational\Domain\Models\Floor;
use Modules\Educational\Domain\Models\Room;
use Modules\Settings\Domain\Interfaces\SettingRepositoryInterface;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = TraineeProfile::query()->with(['user', 'program', 'group', 'jobProfile']);

        // Filtering
        if ($request->filled('status') && $request->status != 'all') {
            $query->where('enrollment_status', $request->status);
        }
        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }
        if ($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
        }
        if ($request->filled('campus_id')) {
            $query->whereHas('group.lectures.room.floor.building', function ($q) use ($request) {
                $q->where('campus_id', $request->campus_id);
            });
        }
        if ($request->filled('building_id')) {
            $query->whereHas('group.lectures.room.floor', function ($q) use ($request) {
                $q->where('building_id', $request->building_id);
            });
        }
        if ($request->filled('floor_id')) {
            $query->whereHas('group.lectures.room', function ($q) use ($request) {
                $q->where('floor_id', $request->floor_id);
            });
        }
        if ($request->filled('room_id')) {
            $query->whereHas('group.lectures', function ($q) use ($request) {
                $q->where('room_id', $request->room_id);
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
            'total' => TraineeProfile::count(),
            'active' => TraineeProfile::where('enrollment_status', 'active')->count(),
            'graduated' => TraineeProfile::where('enrollment_status', 'graduated')->count(),
            'other' => TraineeProfile::whereNotIn('enrollment_status', ['active', 'graduated'])->count()
        ];

        $perPage = (int) get_setting('educational_students_per_page', 12);
        if (!in_array($perPage, [12, 24, 36]))
            $perPage = 12;

        $students = $query->latest()->paginate($perPage)->withQueryString();

        // Data for filters
        $programs = Program::all();
        $groups = Group::all();
        $campuses = Campus::all();
        $buildings = Building::all();
        $floors = Floor::all();
        $rooms = Room::all();

        return view('educational::students.index', compact(
            'students',
            'stats',
            'programs',
            'groups',
            'campuses',
            'buildings',
            'floors',
            'rooms'
        ));
    }

    public function create()
    {
        $governorates = Governorate::where('status', 'active')->get();
        $tracks = \Modules\Educational\Domain\Models\Track::with('jobProfiles')->active()->get();
        $programs = Program::all();
        $groups = Group::all();

        return view('educational::students.create', compact('governorates', 'tracks', 'programs', 'groups'));
    }

    public function show($id)
    {
        $student = TraineeProfile::with(['user', 'emergencyContacts', 'program', 'group', 'jobProfile', 'governorate'])->findOrFail($id);

        // Fetch Attendance
        $attendances = Attendance::with('lecture')
            ->where('trainee_profile_id', $student->id)
            ->latest()
            ->take(10)
            ->get();

        // Fetch Complaints (Tickets)
        $tickets = [];
        if (class_exists(Ticket::class)) {
            $tickets = Ticket::where('user_id', $student->user_id)
                ->with(['category', 'status', 'lecture.sessionType'])
                ->latest()
                ->take(5)
                ->get();
        }

        $ticketStages = \Modules\Tickets\Domain\Models\TicketStage::with('categories.complaints.subComplaints')->get();
        $ticketPriorities = \Modules\Tickets\Domain\Models\TicketPriority::all();

        // Fetch Group Schedule
        $schedule = [];
        if ($student->group_id) {
            $startDate = request('from_date') ? \Carbon\Carbon::parse(request('from_date'))->startOfDay() : now()->startOfDay();
            $endDate = request('to_date') ? \Carbon\Carbon::parse(request('to_date'))->endOfDay() : now()->addDays(14)->endOfDay();

            $schedule = \Modules\Educational\Domain\Models\Lecture::with([
                'room.floor.building',
                'instructorProfile.user',
                'sessionType',
                'attendances' => function ($q) use ($student) {
                    $q->where('trainee_profile_id', $student->id);
                }
            ])
                ->where('group_id', $student->group_id)
                ->where('starts_at', '>=', $startDate)
                ->where('starts_at', '<=', $endDate)
                ->orderBy('starts_at', 'asc')
                ->get();
        }

        // Stats calculation (Institutional Accuracy)
        $now = now();
        $totalLectures = Lecture::where('group_id', $student->group_id)->count();
        $totalPassedLectures = Lecture::where('group_id', $student->group_id)
            ->where('starts_at', '<=', $now)
            ->count();

        $attendedCount = Attendance::where('trainee_profile_id', $student->id)
            ->whereIn('status', ['present', 'late'])
            ->count();

        $lateCount = Attendance::where('trainee_profile_id', $student->id)
            ->where('status', 'late')
            ->count();

        $absentCount = Attendance::where('trainee_profile_id', $student->id)
            ->where('status', 'absent')
            ->count();

        $attendancePercentage = $totalPassedLectures > 0 ? round(($attendedCount / $totalPassedLectures) * 100) : 0;

        // Weekly Stats for schedule tab
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();
        $weeklyLecturesCount = Lecture::where('group_id', $student->group_id)
            ->whereBetween('starts_at', [$startOfWeek, $endOfWeek])->count();
        $weeklyHours = Lecture::where('group_id', $student->group_id)
            ->whereBetween('starts_at', [$startOfWeek, $endOfWeek])
            ->get()->sum(function ($l) {
                return $l->starts_at->diffInHours($l->ends_at);
            });
        $weeklyAttendance = Attendance::where('trainee_profile_id', $student->id)
            ->whereHas('lecture', function ($q) use ($startOfWeek, $endOfWeek) {
                $q->whereBetween('starts_at', [$startOfWeek, $endOfWeek]);
            })->whereIn('status', ['present', 'late'])->count();

        $openTicketsCount = 0;
        $totalTicketsCount = 0;
        if (class_exists(Ticket::class)) {
            $totalTicketsCount = Ticket::where('user_id', $student->user_id)->count();
            $openTicketsCount = Ticket::where('user_id', $student->user_id)
                ->whereHas('status', function ($q) {
                    $q->where('is_final', false);
                })->count();
        }

        // Fetch Activities
        $activities = AuditLog::with('user')
            ->where(function ($q) use ($student) {
                $q->where(function ($sq) use ($student) {
                    $sq->where('auditable_type', TraineeProfile::class)
                        ->where('auditable_id', $student->id);
                })->orWhere(function ($sq) use ($student) {
                    $sq->where('auditable_type', User::class)
                        ->where('auditable_id', $student->user_id);
                });
            })
            ->latest()
            ->take(10)
            ->get();

        return view('educational::students.show', compact(
            'student',
            'attendances',
            'tickets',
            'attendancePercentage',
            'totalLectures',
            'totalPassedLectures',
            'attendedCount',
            'lateCount',
            'absentCount',
            'activities',
            'openTicketsCount',
            'totalTicketsCount',
            'schedule',
            'weeklyLecturesCount',
            'weeklyHours',
            'weeklyAttendance',
            'ticketStages',
            'ticketPriorities'
        ));
    }

    public function storeTicket(Request $request, $id)
    {
        $student = TraineeProfile::findOrFail($id);

        $request->validate([
            'subject' => 'nullable|string|max:255',
            'details' => 'required|string',
            'stage_id' => 'required',
            'category_id' => 'required',
            'complaint_id' => 'nullable',
            'priority_id' => 'required',
            'lecture_id' => 'nullable|exists:' . \Modules\Educational\Domain\Models\Lecture::class . ',id',
        ]);

        $ticketService = app(\Modules\Tickets\Application\Services\TicketService::class);

        $subject = $request->subject;
        if (empty($subject)) {
            $stage = \Modules\Tickets\Domain\Models\TicketStage::find($request->stage_id);
            $category = \Modules\Tickets\Domain\Models\TicketCategory::find($request->category_id);
            $subject = ($stage ? $stage->name : '') . ' > ' . ($category ? $category->name : '');
        }

        $data = [
            'stage_id' => $request->stage_id,
            'category_id' => $request->category_id,
            'complaint_id' => $request->complaint_id,
            'priority_id' => $request->priority_id,
            'subject' => $subject,
            'details' => $request->details,
            'lecture_id' => $request->lecture_id,
        ];

        $ticket = $ticketService->createTicket($student->user, $data);

        return back()->with('success', __('educational::messages.complaint_created_successfully'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:' . User::class . ',email',
            'username' => 'required|string|unique:' . User::class . ',username',
            'password' => 'required|min:8',
            'phone' => 'nullable|string|max:50',
            'enrollment_status' => 'required|in:active,on_leave,graduated,withdrawn,suspended',
            'arabic_name' => 'nullable|string|max:255',
            'english_name' => 'nullable|string|max:255',
            'secondary_phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'nationality' => 'nullable|string|max:100',
            'date_of_birth' => 'nullable|date',
            'national_id' => 'nullable|string|max:50',
            'passport_number' => 'nullable|string|max:50',
            'governorate_id' => 'nullable|exists:' . Governorate::class . ',id',
            'gender' => 'nullable|in:male,female',
            'educational_status' => 'nullable|in:student,graduate',
            'military_number' => 'nullable|string|max:100',
            'device_code' => 'nullable|string|max:100',
            'religion' => 'nullable|string|max:50',
            'sect' => 'nullable|string|max:100',
            'job_profile_id' => 'nullable|exists:' . JobProfile::class . ',id',
            'program_id' => 'nullable|exists:' . Program::class . ',id',
            'group_id' => 'nullable|exists:' . Group::class . ',id',
            'emergency_contacts' => 'nullable|array',
            'emergency_contacts.*.relation' => 'required_with:emergency_contacts|string',
            'emergency_contacts.*.name' => 'required_with:emergency_contacts|string',
            'emergency_contacts.*.phone' => 'required_with:emergency_contacts|string',
            'emergency_contacts.*.photo' => 'nullable|image|max:2048',
            'documents' => 'nullable|array',
            'documents.*' => 'nullable|file|max:5120'
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'username' => $request->username,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'status' => 'active',
                'joined_at' => now(),
            ]);

            $user->assignRole('Trainee');

            $student = TraineeProfile::create([
                'user_id' => $user->id,
                'arabic_name' => $request->arabic_name,
                'english_name' => $request->english_name,
                'secondary_phone' => $request->secondary_phone,
                'address' => $request->address,
                'nationality' => $request->nationality ?? 'egyptian',
                'date_of_birth' => $request->date_of_birth,
                'national_id' => $request->national_id,
                'passport_number' => $request->passport_number,
                'governorate_id' => $request->governorate_id,
                'gender' => $request->gender,
                'educational_status' => $request->educational_status,
                'military_number' => $request->military_number,
                'device_code' => $request->device_code,
                'religion' => $request->religion,
                'sect' => ($request->religion == 'christian') ? $request->sect : null,
                'job_profile_id' => $request->job_profile_id,
                'program_id' => $request->program_id,
                'group_id' => $request->group_id,
                'enrollment_status' => $request->enrollment_status,
            ]);

            if ($request->has('emergency_contacts') && is_array($request->emergency_contacts)) {
                foreach ($request->emergency_contacts as $index => $contactData) {
                    $photoPath = null;
                    if ($request->hasFile("emergency_contacts.{$index}.photo")) {
                        $photoPath = $request->file("emergency_contacts.{$index}.photo")->store('emergency_contacts', 'public');
                    }

                    TraineeEmergencyContact::create([
                        'trainee_profile_id' => $student->id,
                        'relation' => $contactData['relation'],
                        'name' => $contactData['name'],
                        'national_id' => $contactData['national_id'] ?? null,
                        'phone' => $contactData['phone'],
                        'phone2' => $contactData['phone2'] ?? null,
                        'email' => $contactData['email'] ?? null,
                        'address' => $contactData['address'] ?? null,
                        'governorate_id' => $contactData['governorate_id'] ?? null,
                        'photo_path' => $photoPath,
                        'photo_disk' => 'public',
                    ]);
                }
            }
        });

        return redirect()->route('educational.students.index')->with('success', __('educational::messages.student_saved') ?? 'Student Profile Saved.');
    }

    public function edit($id)
    {
        $student = TraineeProfile::with(['user', 'emergencyContacts'])->findOrFail($id);

        $governorates = Governorate::where('status', 'active')->get();
        $tracks = \Modules\Educational\Domain\Models\Track::with('jobProfiles')->active()->get();
        $programs = Program::all();
        $groups = Group::all();
        $student->load('documents');

        return view('educational::students.edit', compact('student', 'governorates', 'tracks', 'programs', 'groups'));
    }

    public function update(Request $request, $id)
    {
        $student = TraineeProfile::findOrFail($id);
        $user = $student->user;

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:' . User::class . ',email,' . $user->id,
            'username' => 'required|string|unique:' . User::class . ',username,' . $user->id,
            'phone' => 'nullable|string|max:50',
            'enrollment_status' => 'required|in:active,on_leave,graduated,withdrawn,suspended',
            'arabic_name' => 'nullable|string|max:255',
            'english_name' => 'nullable|string|max:255',
            'secondary_phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'nationality' => 'nullable|string|max:100',
            'date_of_birth' => 'nullable|date',
            'national_id' => 'nullable|string|max:50',
            'passport_number' => 'nullable|string|max:50',
            'governorate_id' => 'nullable|exists:' . Governorate::class . ',id',
            'gender' => 'nullable|in:male,female',
            'educational_status' => 'nullable|in:student,graduate',
            'military_number' => 'nullable|string|max:100',
            'device_code' => 'nullable|string|max:100',
            'religion' => 'nullable|string|max:50',
            'sect' => 'nullable|string|max:100',
            'job_profile_id' => 'nullable|exists:' . JobProfile::class . ',id',
            'program_id' => 'nullable|exists:' . Program::class . ',id',
            'group_id' => 'nullable|exists:' . Group::class . ',id',
            'emergency_contacts' => 'nullable|array',
            'emergency_contacts.*.relation' => 'required_with:emergency_contacts|string',
            'emergency_contacts.*.name' => 'required_with:emergency_contacts|string',
            'emergency_contacts.*.phone' => 'required_with:emergency_contacts|string',
            'emergency_contacts.*.photo' => 'nullable|image|max:2048',
            'documents' => 'nullable|array',
            'documents.*' => 'nullable|file|max:5120'
        ]);

        DB::transaction(function () use ($request, $student, $user) {
            $user->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'username' => $request->username,
                'phone' => $request->phone,
            ]);

            if ($request->filled('password')) {
                $user->update(['password' => Hash::make($request->password)]);
            }

            $student->update([
                'arabic_name' => $request->arabic_name,
                'english_name' => $request->english_name,
                'secondary_phone' => $request->secondary_phone,
                'address' => $request->address,
                'nationality' => $request->nationality ?? 'egyptian',
                'date_of_birth' => $request->date_of_birth,
                'national_id' => $request->national_id,
                'passport_number' => $request->passport_number,
                'governorate_id' => $request->governorate_id,
                'gender' => $request->gender,
                'educational_status' => $request->educational_status,
                'military_number' => $request->military_number,
                'device_code' => $request->device_code,
                'religion' => $request->religion,
                'sect' => ($request->religion == 'christian') ? $request->sect : null,
                'job_profile_id' => $request->job_profile_id,
                'program_id' => $request->program_id,
                'group_id' => $request->group_id,
                'enrollment_status' => $request->enrollment_status,
            ]);

            // Track kept contacts
            $keptContactIds = collect($request->emergency_contacts)->pluck('id')->filter()->toArray();

            // Delete removed contacts
            $todelete = TraineeEmergencyContact::where('trainee_profile_id', $student->id)->whereNotIn('id', $keptContactIds)->get();
            /** @var \Modules\Educational\Domain\Models\TraineeEmergencyContact $del */
            foreach ($todelete as $del) {
                if ($del->photo_path) {
                    Storage::disk('public')->delete($del->photo_path);
                }
                $del->delete();
            }

            if ($request->has('emergency_contacts') && is_array($request->emergency_contacts)) {
                foreach ($request->emergency_contacts as $index => $contactData) {
                    $photoPath = null;
                    $existingContact = null;

                    if (isset($contactData['id'])) {
                        $existingContact = TraineeEmergencyContact::where('trainee_profile_id', $student->id)->find($contactData['id']);
                        $photoPath = $existingContact->photo_path ?? null;
                    }

                    if ($request->hasFile("emergency_contacts.{$index}.photo")) {
                        if ($photoPath) {
                            Storage::disk('public')->delete($photoPath);
                        }
                        $photoPath = $request->file("emergency_contacts.{$index}.photo")->store('emergency_contacts', 'public');
                    }

                    TraineeEmergencyContact::updateOrCreate(
                        [
                            'id' => $contactData['id'] ?? null,
                            'trainee_profile_id' => $student->id
                        ],
                        [
                            'relation' => $contactData['relation'],
                            'name' => $contactData['name'],
                            'national_id' => $contactData['national_id'] ?? null,
                            'phone' => $contactData['phone'],
                            'phone2' => $contactData['phone2'] ?? null,
                            'email' => $contactData['email'] ?? null,
                            'address' => $contactData['address'] ?? null,
                            'governorate_id' => $contactData['governorate_id'] ?? null,
                            'photo_path' => $photoPath,
                            'photo_disk' => 'public',
                        ]
                    );
                }
            }

            // Handle Documents
            if ($request->has('delete_documents')) {
                foreach ($request->delete_documents as $docId) {
                    $doc = TraineeDocument::where('trainee_profile_id', $student->id)->find($docId);
                    if ($doc) {
                        Storage::disk('public')->delete($doc->file_path);
                        $doc->delete();
                    }
                }
            }

            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $path = $file->store('student_documents', 'public');
                    TraineeDocument::create([
                        'trainee_profile_id' => $student->id,
                        'name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
                }
            }
        });

        return redirect()->route('educational.students.index')->with('success', __('educational::messages.student_saved') ?? 'Student Profile Updated.');
    }

    public function destroy($id)
    {
        $student = TraineeProfile::findOrFail($id);
        $student->delete();
        return redirect()->route('educational.students.index')->with('success', __('educational::messages.student_deleted') ?? 'Student profile removed.');
    }

    public function export(Request $request, StudentImportExportService $service)
    {
        $export = $service->export($request->all());
        return response($export['content'])
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$export['filename']}");
    }

    public function downloadTemplate(StudentImportExportService $service)
    {
        $template = $service->downloadTemplate();
        return response($template['content'])
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$template['filename']}");
    }

    public function import(Request $request, StudentImportExportService $service)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        try {
            $file = $request->file('file');
            $path = $file->storeAs('temp_imports', 'students_' . time() . '.csv', 'local');
            $fullPath = \Storage::disk('local')->path($path);

            $prepare = $service->prepareImport($fullPath);

            if (!empty($prepare['duplicates'])) {
                $duplicates = $prepare['duplicates'];
                return view('modules.educational.students.import_preview', compact('duplicates', 'path'));
            }

            $result = $service->processImport($fullPath, []);
            @unlink($fullPath);

            $msg = __('educational::messages.import_success_msg', ['count' => $result['count']]);
            if (!empty($result['errors'])) {
                return redirect()->back()
                    ->with('warning', $msg . __('educational::messages.import_success_with_errors'))
                    ->with('import_errors', $result['errors']);
            }
            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('educational::messages.import_failed') . ': ' . $e->getMessage());
        }
    }

    public function confirmImport(Request $request, StudentImportExportService $service)
    {
        $request->validate([
            'file_path' => 'required',
            'update_emails' => 'nullable|array'
        ]);

        try {
            $fullPath = \Storage::disk('local')->path($request->file_path);
            if (!file_exists($fullPath)) {
                return redirect()->route('educational.students.index')->with('error', __('educational::messages.session_expired'));
            }

            $result = $service->processImport($fullPath, $request->update_emails ?? []);
            @unlink($fullPath);

            $msg = __('educational::messages.import_success_msg', ['count' => $result['count']]);
            if (!empty($result['errors'])) {
                return redirect()->route('educational.students.index')
                    ->with('warning', $msg . __('educational::messages.import_success_with_errors'))
                    ->with('import_errors', $result['errors']);
            }

            return redirect()->route('educational.students.index')->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->route('educational.students.index')->with('error', __('educational::messages.import_failed') . ': ' . $e->getMessage());
        }
    }

    public function exportEmergencyContacts(Request $request, \Modules\Educational\Application\Services\EmergencyContactImportExportService $service)
    {
        $export = $service->export($request->all());
        return response($export['content'])
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$export['filename']}");
    }

    public function downloadEmergencyContactsTemplate(\Modules\Educational\Application\Services\EmergencyContactImportExportService $service)
    {
        $template = $service->downloadTemplate();
        return response($template['content'])
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$template['filename']}");
    }

    public function importEmergencyContacts(Request $request, \Modules\Educational\Application\Services\EmergencyContactImportExportService $service)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        try {
            $file = $request->file('file');
            $path = $file->storeAs('temp_imports', 'emergency_contacts_' . time() . '.csv', 'local');
            $fullPath = \Storage::disk('local')->path($path);

            $result = $service->processImport($fullPath);
            @unlink($fullPath);

            $msg = __('educational::messages.import_success_msg', ['count' => $result['count']]);
            if (!empty($result['errors'])) {
                return redirect()->back()
                    ->with('warning', $msg . __('educational::messages.import_success_with_errors'))
                    ->with('import_errors', $result['errors']);
            }
            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('educational::messages.import_failed') . ': ' . $e->getMessage());
        }
    }

    public function settings()
    {
        $currentPerPage = (int) get_setting('educational_students_per_page', 12);
        return view('modules.educational.students.settings', compact('currentPerPage'));
    }

    public function saveSettings(Request $request)
    {
        $request->validate([
            'per_page' => 'required|integer|in:12,24,36',
        ]);

        app(SettingRepositoryInterface::class)->setByKey(
            'educational_students_per_page',
            $request->per_page
        );

        return redirect()->route('educational.students.settings')
            ->with('success', 'تم حفظ الإعدادات بنجاح.');
    }
}
