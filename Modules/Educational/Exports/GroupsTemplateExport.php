<?php

namespace Modules\Educational\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

use Modules\Educational\Domain\Models\Program;

class GroupsTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        $program = Program::first();
        $programId = $program ? $program->id : '1';

        return [
            [
                'Example Training Group', // name
                $programId,               // program_id
                '25',                     // capacity
                'Fall 2026',              // term
                'active',                 // status
                '',                       // job_profile_id
                '',                       // remarks
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'name',
            'program_id',
            'capacity',
            'term',
            'status',
            'job_profile_id',
            'remarks'
        ];
    }
}
