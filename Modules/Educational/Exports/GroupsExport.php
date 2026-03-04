<?php

namespace Modules\Educational\Exports;

use Modules\Educational\Domain\Models\Group;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GroupsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $programId;
    protected $status;

    public function __construct($programId = null, $status = null)
    {
        $this->programId = $programId;
        $this->status = $status;
    }

    public function collection()
    {
        $query = Group::with(['program', 'jobProfile', 'transferredToGroup']);

        if ($this->programId) {
            $query->where('program_id', $this->programId);
        }
        if ($this->status) {
            $query->where('status', $this->status);
        }

        return $query->latest()->get();
    }

    public function map($group): array
    {
        return [
            $group->id,
            $group->name,
            $group->program->name ?? '',
            $group->term ?? '',
            $group->capacity,
            $group->jobProfile->name ?? '',
            __('educational::messages.status_' . $group->status) ?? $group->status,
            $group->cancellation_reason ?? ($group->transferredToGroup->name ?? '')
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            __('educational::messages.group_name'),
            __('educational::messages.program'),
            __('educational::messages.term'),
            __('educational::messages.capacity'),
            __('educational::messages.job_profile'),
            __('educational::messages.status'),
            __('educational::messages.remarks')
        ];
    }
}
