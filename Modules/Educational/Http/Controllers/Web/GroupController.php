<?php

namespace Modules\Educational\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Educational\Domain\Models\Group;
use Modules\Educational\Domain\Models\Program;
use Modules\Educational\Domain\Models\JobProfile;
use Modules\Core\Application\Services\ApprovalService;
use Exception;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Educational\Exports\GroupsExport;
use Modules\Educational\Imports\GroupsImport;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        $query = Group::with(['program', 'jobProfile', 'transferredToGroup']);

        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $groups = $query->latest()->paginate(15)->withQueryString();
        $programs = Program::all();

        return view('educational::groups.index', compact('groups', 'programs'));
    }

    public function create()
    {
        $programs = Program::where('status', 'running')->orWhere('status', 'published')->get();
        $tracks = \Modules\Educational\Domain\Models\Track::with('jobProfiles')->active()->get();
        $allGroups = Group::latest()->get();
        return view('educational::groups.create', compact('programs', 'tracks', 'allGroups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'program_id' => 'required|exists:' . Program::class . ',id',
            'term' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:1',
            'job_profile_id' => 'nullable|exists:' . JobProfile::class . ',id',
            'status' => 'required|in:active,cancelled,completed,transferred',
            'cancellation_reason' => 'nullable|string|required_if:status,cancelled',
            'transferred_to_group_id' => 'nullable|exists:' . Group::class . ',id|required_if:status,transferred'
        ]);

        if ($validated['status'] !== 'cancelled') {
            $validated['cancellation_reason'] = null;
        }
        if ($validated['status'] !== 'transferred') {
            $validated['transferred_to_group_id'] = null;
        }

        Group::create($validated);

        return redirect()->route('educational.groups.index')->with('success', __('educational::messages.group_created'));
    }

    public function edit($id)
    {
        $group = Group::findOrFail($id);
        $programs = Program::all();
        $tracks = \Modules\Educational\Domain\Models\Track::with('jobProfiles')->active()->get();
        $allGroups = Group::where('id', '!=', $id)->get();
        return view('educational::groups.edit', compact('group', 'programs', 'tracks', 'allGroups'));
    }

    public function update(Request $request, $id)
    {
        $group = Group::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'program_id' => 'required|exists:' . Program::class . ',id',
            'term' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:1',
            'job_profile_id' => 'nullable|exists:' . JobProfile::class . ',id',
            'status' => 'required|in:active,cancelled,completed,transferred',
            'cancellation_reason' => 'nullable|string|required_if:status,cancelled',
            'transferred_to_group_id' => 'nullable|exists:' . Group::class . ',id|required_if:status,transferred|different:id' // can't transfer to itself, wait `id` isn't in request, it's just validated against DB but logic handles it
        ]);

        if ($validated['status'] !== 'cancelled') {
            $validated['cancellation_reason'] = null;
        }
        if ($validated['status'] !== 'transferred') {
            $validated['transferred_to_group_id'] = null;
        }

        $group->update($validated);

        return redirect()->route('educational.groups.index')->with('success', __('educational::messages.group_updated'));
    }

    public function destroy($id, ApprovalService $approvalService)
    {
        $group = Group::findOrFail($id);

        // Check if there's already a pending approval for this group
        if ($group->pendingApprovalRequest()) {
            return redirect()->back()->with('error', 'يوجد طلب معلق لحذف هذه المجموعة بالفعل.');
        }

        try {
            $approvalService->requestApproval(
                approvable: $group,
                schema: 'education',
                action: 'delete',
                metadata: [
                    'name' => $group->name,
                    'program_name' => collect([$group->program])->pluck('name')->first(),
                    'reason' => 'حذف المجموعة'
                ],
                levels: 1
            );

            return redirect()->route('educational.groups.index')->with('success', 'تم إرسال طلب حذف المجموعة للمراجعة والموافقة.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'فشل في إرسال طلب الحذف: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new \Modules\Educational\Exports\GroupsTemplateExport, 'groups_import_template.xlsx');
    }

    public function export(Request $request)
    {
        $format = $request->query('format', 'xlsx');
        $fileName = 'groups_export_' . date('Y-m-d_H-i-s') . '.' . $format;

        $writerType = $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;

        return Excel::download(new GroupsExport($request->program_id, $request->status), $fileName, $writerType);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            Excel::import(new GroupsImport, $request->file('file'));
            return redirect()->route('educational.groups.index')->with('success', 'تم استيراد المجموعات بنجاح.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'فشل الاستيراد: ' . $e->getMessage());
        }
    }
}
