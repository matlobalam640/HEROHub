<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DestroyManagedUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserManagementController extends Controller
{
    public function destroy(DestroyManagedUserRequest $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            abort(403, 'You cannot delete your own account from this list.');
        }

        if ($user->hasRole('admin') && User::role('admin')->count() <= 1) {
            abort(403, 'Cannot remove the last admin account.');
        }

        DB::transaction(function () use ($user) {
            $tables = config('permission.table_names');

            DB::table($tables['model_has_roles'])
                ->where('model_id', $user->id)
                ->where('model_type', $user->getMorphClass())
                ->delete();

            DB::table($tables['model_has_permissions'])
                ->where('model_id', $user->id)
                ->where('model_type', $user->getMorphClass())
                ->delete();

            $sessionTable = config('session.table', 'sessions');
            if (Schema::hasTable($sessionTable)) {
                DB::table($sessionTable)->where('user_id', $user->id)->delete();
            }

            $user->tokens()->delete();

            $user->delete();
        });

        return redirect()
            ->route('portal.coming-soon', ['page' => 'settings'])
            ->with('status', __('User account removed.'));
    }
}
