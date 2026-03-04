<?php

namespace Modules\Educational\Application\Services;

use Modules\Educational\Domain\Models\TraineeProfile;
use Modules\Users\Domain\Models\User;
use Modules\Educational\Domain\Models\Governorate;
use Modules\Educational\Domain\Models\Program;
use Modules\Educational\Domain\Models\Group;
use Modules\Educational\Domain\Models\JobProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class StudentImportExportService
{
    /**
     * Columns for the CSV template and data
     */
    protected $columns = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'username',
        'password',
        'enrollment_status', // active, on_leave, graduated, withdrawn, suspended
        'arabic_name',
        'english_name',
        'national_id',
        'passport_number',
        'date_of_birth',   // YYYY-MM-DD
        'gender',          // male, female
        'address',
        'governorate_id',
        'program_id',
        'group_id',
        'job_profile_id',
        'religion',
        'sect',
        'device_code',
        'military_number'
    ];

    /**
     * Export all students to CSV
     */
    public function export(array $filters = [])
    {
        $query = TraineeProfile::query()->with(['user', 'governorate', 'program', 'group', 'jobProfile']);

        // Apply filters
        if (!empty($filters['status']) && $filters['status'] != 'all') {
            $query->where('enrollment_status', $filters['status']);
        }
        if (!empty($filters['program_id'])) {
            $query->where('program_id', $filters['program_id']);
        }
        if (!empty($filters['group_id'])) {
            $query->where('group_id', $filters['group_id']);
        }
        if (!empty($filters['campus_id'])) {
            $query->whereHas('group.lectures.room.floor.building', function ($q) use ($filters) {
                $q->where('campus_id', $filters['campus_id']);
            });
        }
        if (!empty($filters['building_id'])) {
            $query->whereHas('group.lectures.room.floor', function ($q) use ($filters) {
                $q->where('building_id', $filters['building_id']);
            });
        }
        if (!empty($filters['floor_id'])) {
            $query->whereHas('group.lectures.room', function ($q) use ($filters) {
                $q->where('floor_id', $filters['floor_id']);
            });
        }
        if (!empty($filters['room_id'])) {
            $query->whereHas('group.lectures', function ($q) use ($filters) {
                $q->where('room_id', $filters['room_id']);
            });
        }
        if (!empty($filters['search'])) {
            $search = $filters['search'];
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

        $students = $query->latest()->get();

        $filename = "students_export_" . now()->format('Y-m-d_H-i-s') . ".csv";
        $handle = fopen('php://temp', 'w');

        // Add UTF-8 BOM for Arabic support in Excel
        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Header
        fputcsv($handle, $this->columns);

        foreach ($students as $student) {
            /** @var TraineeProfile $student */
            fputcsv($handle, [
                $student->user->first_name,
                $student->user->last_name,
                $student->user->email,
                $student->user->phone,
                $student->user->username,
                '', // Password not exported for security
                $student->enrollment_status,
                $student->arabic_name,
                $student->english_name,
                $student->revealSensitive('national_id'),
                $student->revealSensitive('passport_number'),
                $student->date_of_birth ? $student->date_of_birth->format('Y-m-d') : '',
                $student->gender,
                $student->address,
                $student->governorate->name ?? $student->governorate_id,
                $student->program->name ?? $student->program_id,
                $student->group->name ?? $student->group_id,
                $student->jobProfile->name ?? $student->job_profile_id,
                $student->revealSensitive('religion') ?? $student->religion,
                $student->sect,
                $student->device_code,
                $student->military_number
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return [
            'content' => $content,
            'filename' => $filename
        ];
    }

    /**
     * Download CSV template
     */
    public function downloadTemplate()
    {
        $filename = "students_import_template.csv";
        $handle = fopen('php://temp', 'w');

        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($handle, $this->columns);

        // Example Row
        fputcsv($handle, [
            'Mohamed',
            'Saeed',
            'mohamed.saeed@example.com',
            '01012345678',
            'mohamed_trainee',
            'password123',
            'active',
            'محمد سعيد',
            'Mohamed Saeed',
            '29001011234567',
            'A0000000',
            '1995-10-15',
            'male',
            'Nasr City, Cairo',
            '1', // governorate_id
            '1', // program_id
            '1', // group_id
            '1', // job_profile_id
            'muslim', // religion
            '', // sect
            'DEV-1234', // device_code
            'MIL-9876' // military_number
        ]);

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return [
            'content' => $content,
            'filename' => $filename
        ];
    }

    /**
     * Import students from CSV
     */
    public function prepareImport($filePath)
    {
        $handle = fopen($filePath, 'r');
        $bom = fread($handle, 3);
        if ($bom !== chr(0xEF) . chr(0xBB) . chr(0xBF)) {
            rewind($handle);
        }
        $header = fgetcsv($handle);
        if (!$header || count($header) < count($this->columns) || count(array_diff($this->columns, $header)) > 0) {
            fclose($handle);
            throw new \Exception(__('educational::messages.invalid_template_file') ?? "الملف غير مطابق للقالب المعتمد.");
        }

        $duplicates = [];
        $errors = [];
        $rowNum = 1;

        while (($data = fgetcsv($handle)) !== FALSE) {
            $rowNum++;
            $data = array_map('trim', $data);
            if (count($data) < 3 || empty(implode('', $data)))
                continue;

            $data = array_slice($data, 0, count($this->columns));
            $row = array_combine($this->columns, array_pad($data, count($this->columns), ''));

            if (empty($row['email']) || empty($row['username']) || empty($row['first_name'])) {
                $errors[] = "السطر $rowNum: بيانات ناقصة.";
                continue;
            }

            $user = User::where('email', $row['email'])->orWhere('username', $row['username'])->first();
            if ($user) {
                $profile = TraineeProfile::where('user_id', $user->id)->first();
                if ($profile) {
                    // Active duplicate
                    $duplicates[] = [
                        'row' => $rowNum,
                        'email' => $row['email'],
                        'username' => $row['username'],
                        'current_name' => $profile->arabic_name ?? $user->first_name,
                        'new_name' => $row['arabic_name'] ?? $row['first_name'],
                    ];
                }
            }
        }
        fclose($handle);

        return [
            'duplicates' => $duplicates,
            'errors' => $errors
        ];
    }

    public function processImport($filePath, array $updateEmails = [])
    {
        $handle = fopen($filePath, 'r');
        $bom = fread($handle, 3);
        if ($bom !== chr(0xEF) . chr(0xBB) . chr(0xBF))
            rewind($handle);
        $header = fgetcsv($handle);

        $count = 0;
        $errors = [];
        $rowNum = 1;
        $roleName = 'Trainee';

        DB::beginTransaction();
        try {
            while (($data = fgetcsv($handle)) !== FALSE) {
                $rowNum++;
                $data = array_map('trim', $data);
                if (count($data) < 3 || empty(implode('', $data)))
                    continue;

                $data = array_slice($data, 0, count($this->columns));
                $row = array_combine($this->columns, array_pad($data, count($this->columns), ''));

                if (empty($row['email']) || empty($row['username']) || empty($row['first_name']))
                    continue;

                /** @var \Modules\Users\Domain\Models\User|null $user */
                $user = User::where('email', $row['email'])->orWhere('username', $row['username'])->first();

                /** @var \Modules\Educational\Domain\Models\TraineeProfile|null $profile */
                $profile = $user ? TraineeProfile::where('user_id', $user->id)->first() : null;

                if ($user && $profile && !in_array($row['email'], $updateEmails)) {
                    continue; // Skip duplicate that wasn't approved
                }

                $governorateId = $this->resolveId(Governorate::class, $row['governorate_id']);
                $programId = $this->resolveId(Program::class, $row['program_id']);
                $groupId = $this->resolveId(Group::class, $row['group_id']);
                $jobProfileId = $this->resolveId(JobProfile::class, $row['job_profile_id']);

                try {
                    if (!$user) {
                        $user = User::create([
                            'first_name' => $row['first_name'],
                            'last_name' => $row['last_name'],
                            'email' => $row['email'],
                            'phone' => $row['phone'],
                            'username' => $row['username'],
                            'password' => Hash::make($row['password'] ?: 'student123'),
                            'status' => 'active',
                            'joined_at' => now(),
                        ]);
                        if (method_exists($user, 'assignRole')) {
                            try {
                                $user->assignRole($roleName);
                            } catch (\Exception $e) {
                            }
                        }
                    } else {
                        // User exists
                        $user->update([
                            'first_name' => $row['first_name'],
                            'last_name' => $row['last_name'],
                            'phone' => $row['phone'],
                        ]);
                    }

                    $profileData = [
                        'user_id' => $user->id,
                        'enrollment_status' => $row['enrollment_status'] ?: 'active',
                        'arabic_name' => $row['arabic_name'],
                        'english_name' => $row['english_name'],
                        'national_id' => $row['national_id'] ?: null,
                        'passport_number' => $row['passport_number'] ?: null,
                        'date_of_birth' => !empty($row['date_of_birth']) ? date('Y-m-d', strtotime($row['date_of_birth'])) : null,
                        'gender' => strtolower($row['gender']) == 'female' ? 'female' : 'male',
                        'address' => $row['address'],
                        'governorate_id' => $governorateId,
                        'program_id' => $programId,
                        'group_id' => $groupId,
                        'job_profile_id' => $jobProfileId,
                        'religion' => $row['religion'] ?? null,
                        'sect' => $row['sect'] ?? null,
                        'device_code' => $row['device_code'] ?? null,
                        'military_number' => $row['military_number'] ?? null,
                    ];

                    if (!$profile) {
                        $newProfile = TraineeProfile::create($profileData);
                        \Modules\Core\Domain\Models\AuditLog::create([
                            'user_id' => auth()->id() ?? $user->id,
                            'auditable_type' => TraineeProfile::class,
                            'auditable_id' => $newProfile->id,
                            'event' => 'restored',
                            'old_values' => null,
                            'new_values' => ['message' => __('educational::messages.student_restored_via_import')],
                            'ip_address' => request()->ip(),
                            'user_agent' => request()->userAgent(),
                        ]);
                    } else {
                        $profile->update($profileData);
                    }
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
            Log::error("Student Import failed: " . $e->getMessage());
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
     * Sanitize technical DB errors into readable Arabic/English
     */
    private function sanitizeErrorMessage($message)
    {
        if (str_contains($message, 'foreign key constraint')) {
            return __('educational::messages.error_foreign_key') . ' ' . $message;
        }
        if (str_contains($message, 'Duplicate entry') || str_contains($message, 'duplicate key')) {
            return __('educational::messages.error_duplicate_db') . ' ' . $message;
        }
        if (str_contains($message, 'not-null constraint')) {
            return __('educational::messages.error_missing_data') . ' ' . $message;
        }
        if (str_contains($message, 'numeric value out of range')) {
            return __('educational::messages.error_numeric') . ' ' . $message;
        }

        return __('educational::messages.error_general') . ' ' . $message;
    }
}
