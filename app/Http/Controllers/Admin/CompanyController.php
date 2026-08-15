<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->hasRole('admin'), 403);

        $search = trim((string) $request->query('q', ''));

        $companies = Company::query()
            ->with(['ownerUser', 'defaultPlan'])
            ->withCount([
                'memberships',
                'memberships as active_memberships_count' => fn ($query) => $query->where('status', 'active'),
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('billing_email', 'like', '%'.$search.'%')
                        ->orWhereHas('ownerUser', fn ($owner) => $owner->where('email', 'like', '%'.$search.'%'));
                });
            })
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('admin.companies.index', [
            'companies' => $companies,
            'search' => $search,
        ]);
    }
}
