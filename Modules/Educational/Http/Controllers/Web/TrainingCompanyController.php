<?php

namespace Modules\Educational\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Educational\Domain\Models\TrainingCompany;
use Modules\Educational\Domain\Models\Track;
use Modules\Educational\Domain\Models\JobProfile;
use Modules\Educational\Application\Services\CompanyMediaService;

class TrainingCompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $companies = TrainingCompany::latest()->get();
        return view('educational::training_companies.index', compact('companies'));
    }

    public function create()
    {
        $tracks = Track::with(['jobProfiles' => fn($q) => $q->active()])->active()->get();
        return view('educational::training_companies.create', compact('tracks'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, CompanyMediaService $mediaService)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'registration_number' => 'nullable|string|unique:' . TrainingCompany::class . ',registration_number',
            'contact_email' => 'nullable|email',
            'website' => 'nullable|url',
            'address' => 'nullable|string',
            'logo' => 'nullable|image|max:1024',
            'status' => 'required|in:active,inactive,suspended',
            'job_profiles' => 'nullable|array',
            'job_profiles.*' => 'exists:' . JobProfile::class . ',id'
        ]);

        $company = TrainingCompany::create($validated);

        if ($request->hasFile('logo')) {
            $mediaService->uploadLogo($company, $request->file('logo'));
        }

        if (!empty($validated['job_profiles'])) {
            $company->jobProfiles()->sync($validated['job_profiles']);
        }

        return redirect()->route('educational.companies.index')
            ->with('success', __('educational::messages.company_saved'));
    }

    public function edit($id)
    {
        $company = TrainingCompany::with('jobProfiles')->findOrFail($id);
        $tracks = Track::with(['jobProfiles' => fn($q) => $q->active()])->active()->get();
        $selectedProfiles = $company->jobProfiles->pluck('id')->toArray();

        return view('educational::training_companies.edit', compact('company', 'tracks', 'selectedProfiles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id, CompanyMediaService $mediaService)
    {
        $company = TrainingCompany::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'registration_number' => 'nullable|string|unique:' . TrainingCompany::class . ',registration_number,' . $company->id,
            'contact_email' => 'nullable|email',
            'website' => 'nullable|url',
            'address' => 'nullable|string',
            'logo' => 'nullable|image|max:1024',
            'status' => 'required|in:active,inactive,suspended',
            'job_profiles' => 'nullable|array',
            'job_profiles.*' => 'exists:' . JobProfile::class . ',id'
        ]);

        $company->update($validated);

        if ($request->boolean('remove_logo')) {
            $mediaService->removeLogo($company);
        } elseif ($request->hasFile('logo')) {
            $mediaService->uploadLogo($company, $request->file('logo'));
        }

        // Sync job profiles
        $company->jobProfiles()->sync($validated['job_profiles'] ?? []);

        return redirect()->route('educational.companies.index')
            ->with('success', __('educational::messages.company_saved'));
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $company = TrainingCompany::findOrFail($id);
        $company->delete();

        return redirect()->route('educational.companies.index')->with('success', __('educational::messages.company_deleted'));
    }
}
