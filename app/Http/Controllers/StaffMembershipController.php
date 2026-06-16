<?php

namespace App\Http\Controllers;

use App\Models\Membership;
use Illuminate\View\View;

class StaffMembershipController extends Controller
{
    public function show(Membership $membership): View
    {
        $membership->load(['plan', 'company', 'accountUser']);

        return view('portal.membership-show', [
            'membership' => $membership,
        ]);
    }
}
