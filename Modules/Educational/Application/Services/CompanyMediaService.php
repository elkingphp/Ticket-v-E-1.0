<?php

namespace Modules\Educational\Application\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Educational\Domain\Models\TrainingCompany;

/**
 * Service to handle Training Company Media (Logos, etc.)
 * Level 4.5: Separates storage logic from Controllers.
 */
class CompanyMediaService
{
    /**
     * Upload and update company logo.
     */
    public function uploadLogo(TrainingCompany $company, UploadedFile $file): string
    {
        // 1. Cleanup old logo
        if ($company->logo_path) {
            Storage::disk($company->logo_disk ?? 'public')->delete($company->logo_path);
        }

        // 2. Store new logo (normalized under public disk by default)
        $path = $file->store('companies/logos', ['disk' => 'public']);

        // 3. Update model
        $company->update([
            'logo_path' => $path,
            'logo_disk' => 'public'
        ]);

        return $path;
    }

    /**
     * Remove company logo.
     */
    public function removeLogo(TrainingCompany $company): void
    {
        if ($company->logo_path) {
            Storage::disk($company->logo_disk ?? 'public')->delete($company->logo_path);
            $company->update([
                'logo_path' => null,
                'logo_disk' => 'public'
            ]);
        }
    }
}
