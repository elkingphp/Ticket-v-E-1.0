<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LocalizationController extends Controller
{
    /**
     * Switch the application locale.
     *
     * @param  string  $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switch ($locale)
    {
        if (in_array($locale, ['en', 'ar'])) {
            Session::put('locale', $locale);

            if (auth()->check()) {
                auth()->user()->update(['language' => $locale]);
            }
        }

        return redirect()->back();
    }
}