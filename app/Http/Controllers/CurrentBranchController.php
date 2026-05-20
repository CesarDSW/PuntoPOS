<?php

namespace App\Http\Controllers;

use App\Support\BranchContext;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Auth;

class CurrentBranchController extends Controller
{
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branch,branch_id'],
        ]);

        BranchContext::set($user, (int) $validated['branch_id']);

        return back()->with('success', 'Sucursal actual actualizada correctamente.');
    }
}
