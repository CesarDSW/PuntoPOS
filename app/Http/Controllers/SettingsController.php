<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Company;
use App\Models\CompanySettings;
use App\Models\CompanySetting;

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

        $settings = CompanySettings::firstOrCreate(
            ['company_idfk' => $user->company_idfk],
            [
                'notify_low_stock' => true,
                'notify_sale_cancelled' => true,
                'notify_out_of_stock' => true,
                'language' => 'Español (México)',
                'timezone' => 'Ciudad de México (GMT-6)',
                'date_format' => 'DD/MM/YYYY',
                'time_format' => '24 horas',
                'auto_print' => true,
                'show_taxes' => true,
                'printer_width' => '80mm',
                'theme' => 'Claro',
                'price_decimals' => '2',
            ]
        );

        return view('settings', compact('company', 'roles', 'users', 'settings'));
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

    public function editUser($id)
    {
        $authUser = Auth::user();
        
        $user = User::where('company_idfk', $authUser->company_idfk)
            ->where('userr_id', $id)
            ->firstOrFail();
        
        $roles = DB::table('rol')->get();

        return redirect()->route('settings', [
            'tab' => 'usuarios',
            'edit_user' => $user->userr_id
        ])->with([
            'editUserData' => $user,
            'editRolesData' => $roles
        ]);
    }

    public function updateUser(Request $request, $id)
    {
        $authUser = Auth::user();

        $user = User::where('company_idfk', $authUser->company_idfk)
            ->where('userr_id', $id)
            ->firstOrFail();
        
        $request->validate([
            'name_user' => 'required|string|max:100',
            'phone' => 'required|string|max:10',
            'email' => [
                'required',
                'email',
                Rule::unique('userr', 'email')->ignore($user->userr_id, 'userr_id'),
            ],            
            'rol_idfk' => 'required|integer'
        ]);

        $user->update([
            'name_user' => $request->name_user,
            'phone' => $request->phone,
            'email' => $request->email,
            'rol_idfk' => $request->rol_idfk,
        ]);

        return redirect()->route('settings', ['tab' => 'usuarios'])
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function deleteUser($id)
    {
        $authUser = Auth::user();

        $user = User::where('company_idfk', $authUser->company_idfk)
            ->where('userr_id', $id)
            ->firstOrFail();
        
        if($user->userr_id == $authUser->userr_id){
            return redirect()->route('settings', ['tab' => 'usuarios'])
                ->withErrors(['error' => 'No puedes eliminar tu propio usuario.']);
        }

        $user->delete();

        return redirect()->route('settings', ['tab' => 'usuarios'])
            ->with('success', 'Usuario eliminado correctamente.');
    }

    public function updateNotifications(Request $request)
    {
        $user = Auth::user();

        $settings = CompanySetting::firstOrCreate([
            'company_idfk' => $user->company_idfk
        ]);

        $settings->update([
            'notify_low_stock' => $request->has('notify_low_stock'),
            'notify_sale_cancelled' => $request->has('notify_sale_cancelled'),
            'notify_out_of_stock' => $request->has('notify_out_of_stock'),
        ]);

        return redirect()->route('settings', ['tab' => 'notificaciones'])
            ->with('success', 'Notificaciones actualizadas correctamente.');
    }

    public function updatePreferences(Request $request)
    {
        $user = Auth::user();

        $settings = CompanySetting::firstOrCreate([
            'company_idfk' => $user->company_idfk
        ]);

        $request->validate([
            'language' => 'required|string|max:50',
            'timezone' => 'required|string|max:100',
            'date_format' => 'required|string|max:30',
            'time_format' => 'required|string|max:20',
            'printer_width' => 'required|string|max:10',
            'theme' => 'required|string|max:20',
            'price_decimals' => 'required|string|max:20',
        ]);

        $settings->update([
            'language' => $request->language,
            'timezone' => $request->timezone,
            'date_format' => $request->date_format,
            'time_format' => $request->time_format,
            'auto_print' => $request->has('auto_print'),
            'show_taxes' => $request->has('show_taxes'),
            'printer_width' => $request->printer_width,
            'theme' => $request->theme,
            'price_decimals' => $request->price_decimals,
        ]);

        return redirect()->route('settings', ['tab' => 'preferencias'])
            ->with('success', 'Preferencias actualizadas correctamente.');
    }

    public function resetPreferences()
    {
        $user = Auth::user();

        $settings = CompanySetting::firstOrCreate([
            'company_idfk' => $user->company_idfk
        ]);

        $settings->update([
            'language' => 'Español (México)',
            'timezone' => 'Ciudad de México (GMT-6)',
            'date_format' => 'DD/MM/YYYY',
            'time_format' => '24 horas',
            'auto_print' => true,
            'show_taxes' => true,
            'printer_width' => '80mm',
            'theme' => 'Claro',
            'price_decimals' => '2',
        ]);

        return redirect()->route('settings', ['tab' => 'preferencias'])
            ->with('success', 'Preferencias restablecidas correctamente.');
    }
}
