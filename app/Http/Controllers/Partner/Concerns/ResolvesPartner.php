<?php

namespace App\Http\Controllers\Partner\Concerns;

use App\Models\Partner;
use Illuminate\Http\Request;

trait ResolvesPartner
{
    protected function currentPartner(Request $request): ?Partner
    {
        $user = $request->user();
        if (! $user) {
            return null;
        }

        return Partner::query()->where('user_id', $user->id)->first();
    }

    protected function requirePartner(Request $request): Partner
    {
        $partner = $this->currentPartner($request);
        abort_unless($partner && $partner->active, 403, 'Partner account is inactive or not linked.');

        return $partner;
    }
}
