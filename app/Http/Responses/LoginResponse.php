<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        $user->loadMissing('rol');

        $role = strtoupper(trim((string) optional($user->rol)->type_rol));

        $assignedBranchId = DB::table('user_branch')
            ->where('userr_idfk', $user->userr_id)
            ->value('branch_idfk');

        if ($role === 'CAJERO') {
            if(!$assignedBranchId) {
                Auth::guard()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'El cajero no tiene una sucursal asignada. Contacta con el administrador.',
                    ]);
            }

            $request->session()->put('current_branch_id', (int) $assignedBranchId);

            return redirect()->route('sales.pos');
        }

        if ($role === 'GERENTE' && $assignedBranchId) {
            $request->session()->put('current_branch_id', (int) $assignedBranchId);
        }

        return redirect()->intended(route('dashboard'));
    }
}