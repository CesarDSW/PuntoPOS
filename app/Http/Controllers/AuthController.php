<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Rol;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Tag;
use App\Models\TagCustomer;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    //Autenticación
        //Registro de usuario
            //Funcion que manda a llamar el formulario de registro
    public function showRegister()
    {
        return view('auth.register');
    }

    
            //Funcion que procesa el formulario de registro
    public function register(Request $request)
    {
        $request->validate([
            'name_user' => 'required|string|max:100',
            'phone' => 'required|string|max:10',
            'email' => 'required|email|unique:userr,email',
            'name_company' => 'required|string|max:100',
            'password' => 'required|min:8|confirmed',
        ]);

        $companyExists = Company::where('name_company', $request->name_company)->exists();

        if ($companyExists) {
            return back()->withErrors([
                'name_company' => 'El nombre de la empresa ya existe. Por favor, elige otro nombre.'
            ]);
        }

        DB::beginTransaction();

        try {
            $user = User::create([
                'name_user' => $request->name_user,
                'phone' => $request->phone,
                'email' => $request->email,
                'name_company' => $request->name_company,
                'password' => Hash::make($request->password),
                'rol_idfk' => 1,
                'company_idfk' => null,
                'state' => 1,
            ]);

            $company = Company::Create([
                'name_company' => $request->name_company,
                'owner_user_id' => $user->userr_id,
            ]);

            $user->company_idfk = $company->company_id;
            $user->save();        
            
            DB::commit();
            
            return redirect()->route('login')->with('success', 'Usuario registrado exitosamente.');
            
            } catch(\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Ocurrio un error al registrar el usuario y la empresa: '. $e->getMessage()
            ])->withInput();
        }   
    }

            //Funcion que manda a llamar el formulario de inicio de sesion.
    public function showLogin()
    {
        return view('auth.login');
    }

        //Inicio de sesion
            //Funcion que procesa el formulario de inicio de sesion
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden.',
        ]) ->onlyInput('email');
    }

            //Funcion que cierra la sesion del usuario
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }

            //Funcion que actualiza la contraseña
    public function updatePassword(Request $request){
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        if(Hash::check($request->new_password, $user->password)){
            return back()->withErrors([
                'new_password' => 'La nueva contraseña no puede ser igual a la actual.'
            ]);
        }

        if(!Hash::check($request->current_password, $user->password)){
            return back()->withErrors([
                'current_password' => 'La contraseña actual es incorrecta.'
            ]);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with('success_password', 'Contraseña actualizada correctamente.');
    }

    //Dashboard
        //Funcion para guardar los datos del onboarding
    public function storeOnboarding(Request $request)
    {
        $user = Auth::user();
        $company = Company::findOrFail($user->company_idfk);
        
        if($request->has('skip')){
            $company->update([
                'onboarding_completed' => 1,
            ]);
            return redirect()->route('dashboard');
        }

        $request->validate([
            'address' => 'nullable|string|max:255',
            'currency' => 'nullable|string|max:20',
            'opening_time' => 'nullable',
            'closing_time' => 'nullable',
            'payment_methods' => 'nullable|array',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);


        $logoPath = $company->logo;

        if($request->hasFile('logo')){
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        $company->update([
            'address' => $request->address,
            'currency' => $request->currency,
            'opening_time' => $request->opening_time,
            'closing_time' => $request->closing_time,
            'logo' => $logoPath,
            'payment_methods' => $request->payment_methods ? json_encode($request->payment_methods) : null,
            'onboarding_completed' => 1,
        ]);

        return redirect()->route('dashboard')->with('success', 'Configuración inicial guardada.');
    }

    //Configuración
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

        //Funcion para crear un nuevo usuario
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

    //Clientes
        //Funcion para mostrar la pagina de clientes
    public function showCustomers()
    {
        $user = Auth::user();
        
        $customers = Customer::where('company_idfk', $user->company_idfk)->get();
        $tags = Tag::all();

        return view('customers.index', compact('customers', 'tags'));
    }

        //Funcion para actualizar o agregar clientes
    public function storeCustomers(Request $request)
    {
        $request->validate([
            'name_customer' => 'required|string|max:100',
            'phone' => 'required|string|max:10',
            'email' => 'required|email|max:320',
            'tags' => 'nullable|array',
        ]);

        $user = Auth::user();

        DB::beginTransaction();

        try{
            $customer = Customer::create([
            'name_customer' => $request->name_customer,
            'phone' => $request->phone,
            'email' => $request->email,
            'company_idfk' => $user->company_idfk,
        ]);

        if($request->has('tags')){
            foreach($request->tags as $tagId){
                TagCustomer::create([
                    'customer_idfk' => $customer->customer_id,
                    'tag_idfk' => $tagId,
                ]);
            }
        }

        DB::commit();
        
        return redirect()->route('customers')->with('success', 'Cliente registrado correctamente.');
        } catch(\Exception $e){
            DB::rollback();

            dd($e->getMessage());
        }
    }
}