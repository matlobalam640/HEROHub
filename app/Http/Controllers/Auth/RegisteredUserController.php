<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RegisteredUserController extends Controller
{
    /**
     * Registration is disabled in the portal — send prospects to the public site.
     */
    public function create(): RedirectResponse
    {
        return redirect()->away(config('heroportal.membership_subscribe_url'));
    }

    /**
     * Block direct POST registration attempts as well.
     */
    public function store(Request $request): RedirectResponse
    {
        return redirect()->away(config('heroportal.membership_subscribe_url'));
    }
}
