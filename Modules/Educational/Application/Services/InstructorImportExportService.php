<?php

namespace Modules\Educational\Application\Services;

use Modules\Educational\Domain\Models\InstructorProfile;
use Modules\Users\Domain\Models\User;
use Modules\Educational\Domain\Models\Governorate;
use Modules\Educational\Domain\Models\Track;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class InstructorImportExportService
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
        'employment_type', // full_time, part_time, contractor
        'status',          // active, inactive, suspended
        'arabic_name',
        'english_name',
        'national_id',
        'passport_number',
        'date_of_birth',   // YYYY-MM-DD
        'gender',          // male, female
        'address',
        'governorate_id',
        'track_id'
    ];

    /**
     * Export all instructors to CSV
     */
    public function export(array $filters = [])
    {
        $query = InstructorProfile::with('user');

        if (!empty($filters['track_id'])) {
            $query->where('track_id', $filters['track_id']);
        }
        if (!empty($filters['company_id'])) {
            $query->whereHas('companies', function ($q) use ($filters) {
                $q->where('training_companies.id', $filters['company_id']);
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

        $instructors = $query->latest()->get();

        $filename = "instructors_export_" . now()->format('Y-m-d_H-i-s') . ".csv";
        $handle = fopen('php://temp', 'w');

        // Add UTF-8 BOM for Arabic support in Excel
        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Header
        fputcsv($handle, $this->columns);

        foreach ($instructors as $instructor) {
            /** @var InstructorProfile $instructor */
            fputcsv($handle, [
                $instructor->user->first_name,
                $instructor->user->last_name,
                $instructor->user->email,
                $instructor->user->phone,
                $instructor->user->username,
                '', // Password not exported for security
                $instructor->employment_type,
                $instructor->status,
                $instructor->arabic_name,
                $instructor->english_name,
                $instructor->getSensitiveData('national_id'),
                $instructor->getSensitiveData('passport_number'),
                $instructor->date_of_birth,
                $instructor->gender,
                $instructor->address,
                $instructor->governorate_id,
                $instructor->track_id
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
        $filename = "instructors_import_template.csv";
        $handle = fopen('php://temp', 'w');

        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($handle, $this->columns);

        // Example Row
        fputcsv($handle, [
            'Ahmed',
            'Ali',
            'ahmed@example.com',
            '0123456789',
            'ahmed_lecturer',
            'password123',
            'full_time',
            'active',
            'أحمد علي',
            'Ahmed Ali',
            '12345678901234',
            'A1234567',
            '1985-05-20',
            'male',
            'Cairo, Egypt',
            '1',
            '1'
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
     * Import instructors from CSV
     */
    public function import($filePath)
    {
        $handle = fopen($filePath, 'r');

        // Check BOM
        $bom = fread($handle, 3);
        if ($bom !== chr(0xEF) . chr(0xBB) . chr(0xBF)) {
            rewind($handle);
        }

        $header = fgetcsv($handle);

        if (!$header) {
            fclose($handle);
            throw new \Exception("Invalid CSV file header.");
        }

        $count = 0;
        $errors = [];
        $rowNum = 1;

        DB::beginTransaction();
        try {
            while (($data = fgetcsv($handle)) !== FALSE) {
                $rowNum++;
                if (count($data) < 5)
                    continue; // Skip empty rows

                $row = array_combine($this->columns, array_pad($data, count($this->columns), ''));

                // Basic Validation
                if (empty($row['email']) || empty($row['username']) || empty($row['first_name'])) {
                    $errors[] = "Row $rowNum: Missing required fields (email, username, or name).";
                    continue;
                }

                if (User::where('email', $row['email'])->orWhere('username', $row['username'])->exists()) {
                    $errors[] = "Row $rowNum: User with email or username already exists.";
                    continue;
                }

                $user = User::create([
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'],
                    'email' => $row['email'],
                    'phone' => $row['phone'],
                    'username' => $row['username'],
                    'password' => Hash::make($row['password'] ?: 'password123'),
                    'status' => 'active',
                    'joined_at' => now(),
                ]);

                \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Instructor', 'guard_name' => 'web']);
                $user->assignRole('Instructor');

                InstructorProfile::create([
                    'user_id' => $user->id,
                    'employment_type' => $row['employment_type'] ?: 'full_time',
                    'status' => $row['status'] ?: 'active',
                    'arabic_name' => $row['arabic_name'],
                    'english_name' => $row['english_name'],
                    'national_id' => $row['national_id'],
                    'passport_number' => $row['passport_number'],
                    'date_of_birth' => $row['date_of_birth'] ?: null,
                    'gender' => $row['gender'] ?: 'male',
                    'address' => $row['address'],
                    'governorate_id' => $row['governorate_id'] ?: null,
                    'track_id' => $row['track_id'] ?: null,
                ]);

                $count++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Import failed at row $rowNum: " . $e->getMessage());
            throw $e;
        }

        fclose($handle);

        return [
            'count' => $count,
            'errors' => $errors
        ];
    }
}
