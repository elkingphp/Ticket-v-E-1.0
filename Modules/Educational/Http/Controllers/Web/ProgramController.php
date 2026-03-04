<?php

namespace Modules\Educational\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Educational\Domain\Models\Program;
use Modules\Educational\Domain\Models\Campus;
use Illuminate\Support\Facades\DB;

class ProgramController extends Controller
{
    public function index(Request $request)
    {
        $query = Program::with('campuses')->withCount('campuses');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'ilike', '%' . $request->search . '%')
                    ->orWhere('code', 'ilike', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $programs = $query->latest()->get();
        return view('educational::programs.index', compact('programs'));
    }

    public function create()
    {
        $campuses = Campus::all();
        return view('educational::programs.create', compact('campuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:pgsql.education.programs,code',
            'status' => 'required|in:draft,published,running,completed,archived',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
            'campuses' => 'nullable|array',
            'campuses.*' => 'exists:pgsql.education.campuses,id',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $program = Program::create([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'status' => $validated['status'],
                'starts_at' => $validated['starts_at'],
                'ends_at' => $validated['ends_at'],
            ]);

            if ($request->has('campuses')) {
                $program->campuses()->sync($request->campuses);
            }
        });

        return redirect()->route('educational.programs.index')->with('success', __('educational::messages.program_created'));
    }

    public function edit($id)
    {
        $program = Program::with('campuses')->findOrFail($id);
        $campuses = Campus::all();
        return view('educational::programs.edit', compact('program', 'campuses'));
    }

    public function update(Request $request, $id)
    {
        $program = Program::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:pgsql.education.programs,code,' . $program->id,
            'status' => 'required|in:draft,published,running,completed,archived',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
            'campuses' => 'nullable|array',
            'campuses.*' => 'exists:pgsql.education.campuses,id',
        ]);

        DB::transaction(function () use ($validated, $request, $program) {
            $program->update([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'status' => $validated['status'],
                'starts_at' => $validated['starts_at'],
                'ends_at' => $validated['ends_at'],
            ]);

            if ($request->has('campuses')) {
                $program->campuses()->sync($request->campuses);
            } else {
                $program->campuses()->detach();
            }
        });

        return redirect()->route('educational.programs.index')->with('success', __('educational::messages.program_updated'));
    }

    public function destroy($id, \Modules\Core\Application\Services\ApprovalService $approvalService)
    {
        $program = Program::findOrFail($id);

        // Check if there's already a pending approval for this program
        if ($program->pendingApprovalRequest()) {
            return redirect()->back()->with('error', 'يوجد طلب معلق لهذا البرنامج بالفعل.');
        }

        try {
            $approvalService->requestApproval(
                approvable: $program,
                schema: 'education',
                action: 'delete',
                metadata: [
                    'name' => $program->name,
                    'code' => $program->code,
                    'reason' => 'حذف البرنامج التدريبي'
                ],
                levels: 1
            );

            return redirect()->route('educational.programs.index')->with('success', 'تم إرسال طلب حذف البرنامج للمراجعة والموافقة.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'فشل في إرسال طلب الحذف: ' . $e->getMessage());
        }
    }
}
