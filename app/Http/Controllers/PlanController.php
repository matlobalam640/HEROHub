<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function retail(): View
    {
        return view('plans.retail', [
            'retailCatalogSections' => Plan::retailCatalogSections(),
        ]);
    }

    public function smallBusiness(): View
    {
        return view('plans.small-business', [
            'smallBusinessCatalogSections' => Plan::smallBusinessCatalogSections(),
        ]);
    }

    public function corporate(): View
    {
        return view('plans.corporate', [
            'corporateCatalogSections' => Plan::corporateCatalogSections(),
        ]);
    }
}
