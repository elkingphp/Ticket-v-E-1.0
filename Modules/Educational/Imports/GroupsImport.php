<?php

namespace Modules\Educational\Imports;

use Modules\Educational\Domain\Models\Group;
use Modules\Educational\Domain\Models\Program;
use Modules\Educational\Domain\Models\JobProfile;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class GroupsImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    public function model(array $row)
    {
        // Try finding program by ID or name
        $program = Program::where('id', $row['program_id'] ?? null)
            ->orWhere('name', $row['program'] ?? '')
            ->first();

        // Try finding job profile by ID or name
        $jobProfile = JobProfile::where('id', $row['job_profile_id'] ?? null)
            ->orWhere('name', $row['job_profile'] ?? '')
            ->first();

        if (empty($row['name']) && empty($row['group_name'])) {
            return null; // Skip empty rows
        }

        if (!$program) {
            throw new \Exception("البرنامج التدريبي غير موجود للصف الذي يحمل اسم المجموعة: " . ($row['name'] ?? $row['group_name']));
        }

        return new Group([
            'name' => $row['name'] ?? $row['group_name'] ?? 'Imported Group',
            'term' => $row['term'] ?? null,
            'capacity' => $row['capacity'] ?? 20,
            'status' => in_array($row['status'] ?? '', ['active', 'completed', 'cancelled', 'transferred']) ? $row['status'] : 'active',
            'program_id' => $program->id,
            'job_profile_id' => $jobProfile->id ?? null,
            'cancellation_reason' => $row['remarks'] ?? $row['cancellation_reason'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            // 'program' => 'required',
        ];
    }
}
