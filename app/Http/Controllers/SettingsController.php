<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Company;


class SettingsController extends Controller
{
    /*Configuración*/
    //Funcion para mostrar la configuracion
    public function showSettings()
    {
        $user = Auth::user();
        $company = Company::findOrFail($user->company_idfk);

        $roles = DB::table('rol')->get();

        $users = User::where('company_idfk', $user->company_idfk)->get();

        return view('settings', compact('company', 'roles', 'users'));
    }

    //Funcion para actualizar o agregar datos en configuracion
    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        $company = Company::findOrFail($user->company_idfk);

        $request->validate([
            'name_company' => 'nullable|string|max:100',
            'rfc' => 'nullable|string|max:13',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:10',
            'phone' => 'nullable|string|max:10',
            'email' => 'nullable|email|max:320',
            'currency' => 'nullable|string|max:20',
            'opening_time' => 'nullable',
            'closing_time' => 'nullable',
            'description_company' => 'nullable|string',
            'payment_methods' => 'nullable|array',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = [];

        if($request->has('name_company')) $data['name_company'] = $request->name_company;
        if($request->has('rfc')) $data['rfc'] = $request->rfc;     
        if($request->has('address')) $data['address'] = $request->address;
        if($request->has('city')) $data['city'] = $request->city;
        if($request->has('state')) $data['state'] = $request->state;
        if($request->has('zip_code')) $data['zip_code'] = $request->zip_code;
        if($request->has('phone')) $data['phone'] = $request->phone;
        if($request->has('email')) $data['email'] = $request->email;
        if($request->has('currency')) $data['currency'] = $request->currency;
        if($request->has('opening_time')) $data['opening_time'] = $request->opening_time;
        if($request->has('closing_time')) $data['closing_time'] = $request->closing_time;
        if($request->has('description_company')) $data['description_company'] = $request->description_company;
        
        if($request->has('payment_methods')) {
            $data['payment_methods'] = json_encode($request->payment_methods);
        } elseif($request->input('tab_section') === 'pagos'){
            $data['payment_methods'] = null;
        }
        
        if($request->hasFile('logo')){
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }

        if(!empty($data)){
            $company->update($data);
        }
     
        return redirect()->back()->with('success', 'Configuración actualizada correctamente.');
    }

    //Funcion para crear un nuevo usuario (gerente o cajero)
    public function createUser(Request $request)
    {
        $request->validate([
            'name_user' => 'required|string|max:100',
            'phone' => 'required|string|max:10',
            'email' => 'required|email|unique:userr,email',
            'password' => 'required|min:8|confirmed',
            'rol_idfk' => 'required|integer'
        ]);

        $authUser = Auth::user();

        User::create([
            'name_user' => $request->name_user,
            'phone' => $request->phone,
            'email' => $request->email,
            'name_company' => $authUser->name_company,
            'password' => Hash::make($request->password),
            'rol_idfk' => $request->rol_idfk,
            'company_idfk' => $authUser->company_idfk,
            'state' => 1,
        ]);

        return redirect()->route('settings', ['tab' => 'usuarios'])
            ->with('success', 'Usuario registrado correctamente.');
    }
}
