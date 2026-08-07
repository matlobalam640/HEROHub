<?php

namespace App\Http\Controllers;

use App\Support\CoverageFormTranslations;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PortalLocaleController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', 'in:'.implode(',', CoverageFormTranslations::supportedLocales())],
        ]);

        $request->session()->put('portal_locale', $validated['locale']);

        return redirect()->back();
    }
}
