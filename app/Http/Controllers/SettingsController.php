<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Rol;
use App\Models\Company;
use App\Models\CompanySettings;
use App\Models\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller
{
    /*Configuración*/
    //Funcion para mostrar la configuracion
    public function showSettings()
    {
        $authUser = Auth::user();

        $currentRoleName = $this->getUserRoleName($authUser);
        $currentUserId = $authUser->userr_id;
        $canEditBusinessProfile = $currentRoleName !== 'GERENTE';
        $ownerUserId = $this->getCompanyOwnerUserId((int) $authUser->company_idfk);

        $branches = DB::table('branch')
            ->where('company_idfk', $authUser->company_idfk)
            ->orderBy('name_branch')
            ->get(['branch_id', 'name_branch']);

        $company = Company::findOrFail($authUser->company_idfk);

        $roles = DB::table('rol')->orderBy('type_rol')->get();

        $users = User::where('company_idfk', $authUser->company_idfk)->get();

        $settings = CompanySettings::firstOrCreate(
            ['company_idfk' => $authUser->company_idfk],
            [
                'notify_low_stock' => true,
                'notify_sale_cancelled' => true,
                'notify_out_of_stock' => true,
                'timezone' => 'America/Mexico_City',
                'date_format' => 'd/m/Y',
                'time_format' => 'H:i',
                'auto_print' => true,
                'show_taxes' => true,
                'printer_width' => '80mm',
                'theme' => 'light',
                'price_decimals' => '2',
            ]
        );

        $userBranchMap = DB::table('user_branch')
            ->whereIn('userr_idfk', $users->pluck('userr_id'))
            ->pluck('branch_idfk', 'userr_idfk');
        
        foreach($users as $user){
            $user->branch_idfk = $userBranchMap[$user->userr_id] ?? null;
        }

        $assignedBranchId = DB::table('user_branch')
            ->where('userr_idfk', $authUser->userr_id)
            ->value('branch_idfk');

        $assignedBranch = null;

        if($assignedBranchId){
            $assignedBranch = DB::table('branch')
                ->where('branch_id', $assignedBranchId)
                ->where('company_idfk', $authUser->company_idfk)
                ->first([
                    'branch_id',
                    'name_branch',
                    'address',
                    'city',
                    'state',
                    'phone',
                    'responsible',
                    'email',
                ]);
        }

        return view('settings', compact( 
            'company', 
            'roles', 
            'users', 
            'settings',
            'branches',
            'currentRoleName',
            'currentUserId',
            'canEditBusinessProfile',
            'assignedBranch',
            'ownerUserId'
        ));
    }

    //Funcion para actualizar o agregar datos en configuracion
    public function updateSettings(Request $request)
    {
        $authUser = Auth::user();
        
        if($this->isGerente()){
            abort(403, 'No autorizado.');
        }

        $company = Company::findOrFail($authUser->company_idfk);

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
        $authUser = Auth::user();

        $validated = $request->validate([
            'name_user' => 'required|string|max:100',
            'phone' => 'required|string|max:10',
            'email' => 'required|email|max:320|unique:userr,email',
            'password' => 'required|min:8|confirmed',
            'rol_idfk' => 'required|integer|exists:rol,rol_id',
            'branch_idfk' => 'nullable|integer|exists:branch,branch_id',
        ]);

        $role = Rol::findOrFail($validated['rol_idfk']);
        $roleName = strtoupper(trim((string) $role->type_rol));

        if($this->isGerente() && $roleName !== 'CAJERO'){
            throw ValidationException::withMessages([
                'rol_idfk' => ['El gerente solo puede crear usuarios con rol de cajero.'],
            ]);
        }

        if($roleName === 'CAJERO' && empty($validated['branch_idfk'])){
            throw ValidationException::withMessages([
                'branch_idfk' => ['Debes asignar una sucursal al cajero.'],
            ]);
        }

        if($roleName !== 'CAJERO'){
            $validated['branch_idfk'] = null;
        }

        if(!empty($validated['branch_idfk'])){
            $branchBelongsToCompany = DB::table('branch')
                ->where('branch_id', $validated['branch_idfk'])
                ->where('company_idfk', $authUser->company_idfk)
                ->exists();
            
            if(!$branchBelongsToCompany){
                throw ValidationException::withMessages([
                    'branch_idfk' => ['La sucursal seleccionada no pertenece a tu empresa.'],
                ]);
            }
        }

        $user = User::create([
            'name_user' => $validated['name_user'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'name_company' => $authUser->name_company,
            'password' => Hash::make($validated['password']),
            'rol_idfk' => $validated['rol_idfk'],
            'company_idfk' => $authUser->company_idfk,
            'state' => 1,
        ]);

        if($roleName === 'CAJERO' && !empty($validated['branch_idfk'])){
            DB::table('user_branch')->updateOrInsert(
                ['userr_idfk' => $user->userr_id],
                ['branch_idfk' => $validated['branch_idfk']] 
            );
        }

        return redirect()->route('settings', ['tab' => 'usuarios'])
            ->with('success', 'Usuario registrado correctamente.');
    }

    public function editUser($id)
    {
        $authUser = Auth::user();
        
        $user = User::where('company_idfk', $authUser->company_idfk)
            ->where('userr_id', $id)
            ->firstOrFail();
        
        if($this->cannotManageOwner($authUser, $user)){
            return redirect()->route('settings', ['tab' => 'usuarios'])
                ->withErrors(['error' => 'No tienes permisos para editar al administrador dueño.']);
        }

        if($this->gerenteCannotManageTarget($authUser, $user)){
            return redirect()->route('settings', ['tab' => 'usuarios'])
                ->withErrors(['error' => 'No tienes permisos para editar un administrador.']);
        }

        $roles = DB::table('rol')->orderBy('type_rol')->get();

        $branchId = DB::table('user_branch')
            ->where('userr_idfk', $user->userr_id)
            ->value('branch_idfk');
        
        $user->branch_idfk = $branchId;

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
        
        if($this->cannotManageOwner($authUser, $user)){
            return redirect()->route('settings', ['tab' => 'usuarios'])
                ->withErrors(['error' => 'No tienes permisos para editar al administrador dueño.']);
        }
        
        if ($this->gerenteCannotManageTarget($authUser, $user)){
            return redirect()->route('settings', ['tab' => 'usuarios'])
                ->withErrors(['error' => 'No tienes permisos para editar un administrador.']);
        }

        $validated = $request->validate([
            'name_user' => 'required|string|max:100',
            'phone' => 'required|string|max:10',
            'email' => [
                'required',
                'email',
                'max:320',
                Rule::unique('userr', 'email')->ignore($user->userr_id, 'userr_id'),
            ],            
            'rol_idfk' => 'required|integer|exists:rol,rol_id',
            'branch_idfk' => 'nullable|integer|exists:branch,branch_id',
        ]);

        $authRoleName = $this->getUserRoleName($authUser);
        $isSelf = (int) $authUser->userr_id === (int) $user->userr_id;

        $ownerUserId = $this->getCompanyOwnerUserId((int) $authUser->company_idfk);
        $isTargetOwner = $ownerUserId !== null && (int) $ownerUserId === (int) $user->userr_id;

        $currentBranchId = DB::table('user_branch')
            ->where('userr_idfk', $user->userr_id)
            ->value('branch_idfk');

        if($isTargetOwner) {
            $validated['rol_idfk'] = $user->rol_idfk;
        }

        if($isSelf && in_array($authRoleName, ['ADMIN', 'ADMINISTRADOR'], true)){
            $validated['rol_idfk'] = $user->rol_idfk;
        }

        if($authRoleName === 'GERENTE' && $isSelf){
            $validated['rol_idfk'] = $user->rol_idfk;
            $validated['branch_idfk'] = $currentBranchId;
        }

        $role = Rol::findOrFail($validated['rol_idfk']);
        $roleName = strtoupper(trim((string) $role->type_rol));

        if($roleName === 'CAJERO' && empty($validated['branch_idfk'])){
            throw ValidationException::withMessages([
                'branch_idfk' => ['Debes asignar una sucursal al cajero.'],
            ]);
        }

        if(!empty($validated['branch_idfk'])){
            $branchBelongsToCompany = DB::table('branch')
                ->where('branch_id', $validated['branch_idfk'])
                ->where('company_idfk', $authUser->company_idfk)
                ->exists();
            
            if(!$branchBelongsToCompany){
                throw ValidationException::withMessages([
                    'branch_idfk' => ['La sucursal seleccionada no pertenece a tu empresa.'],
                ]);
            }
        }

        $user->update([
            'name_user' => $validated['name_user'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'rol_idfk' => $validated['rol_idfk'],
        ]);

        if(in_array($roleName, ['CAJERO', 'GERENTE'], true) && !empty($validated['branch_idfk'])) {
            DB::table('user_branch')->updateOrInsert(
                ['userr_idfk' => $user->userr_id],
                ['branch_idfk' => $validated['branch_idfk']]
            );
        } else {
            DB::table('user_branch')
                ->where('userr_idfk', $user->userr_id)
                ->delete();
        }

        return redirect()->route('settings', ['tab' => 'usuarios'])
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function deleteUser($id)
    {
        $authUser = Auth::user();

        $user = User::where('company_idfk', $authUser->company_idfk)
            ->where('userr_id', $id)
            ->firstOrFail();
        
        if($this->cannotManageOwner($authUser, $user)){
            return redirect()->route('settings', ['tab' => 'usuarios'])
                ->withErrors(['error' => 'No tienes permisos para eliminar al administrador dueño.']);
        }

        if($user->userr_id == $authUser->userr_id){
            return redirect()->route('settings', ['tab' => 'usuarios'])
                ->withErrors(['error' => 'No puedes eliminar tu propio usuario.']);
        }

        if($this->gerenteCannotManageTarget($authUser, $user)){
            return redirect()->route('settings', ['tab' => 'usuarios'])
                ->withErrors(['error' => 'No tienes permisos para eliminar un administrador.']);
        }

        DB::table('user_branch')
            ->where('userr_idfk', $user->userr_id)
            ->delete();

        $user->delete();

        return redirect()->route('settings', ['tab' => 'usuarios'])
            ->with('success', 'Usuario eliminado correctamente.');
    }

    public function updateNotifications(Request $request)
    {
        $authUser = Auth::user();

        $settings = CompanySettings::firstOrCreate([
            'company_idfk' => $authUser->company_idfk
        ]);

        $settings->update([
            'notify_low_stock' => $request->has('notify_low_stock'),
            'notify_sale_cancelled' => $request->has('notify_sale_cancelled'),
            'notify_out_of_stock' => $request->has('notify_out_of_stock'),
        ]);

        return redirect()->route('settings', ['tab' => 'notificaciones'])
            ->with('success', 'Notificaciones actualizadas correctamente.');
    }

    public function notificationsList()
    {
        $authUser = Auth::user();

        $notifications = SystemNotification::where('company_idfk', $authUser->company_idfk)
            ->orderByDesc('notification_id')
            ->limit(20)
            ->get();
        
        return view('notifications-index', compact('notifications'));
    }

    public function updatePreferences(Request $request)
    {
        $authUser = Auth::user();

        $settings = CompanySettings::firstOrCreate([
            'company_idfk' => $authUser->company_idfk
        ]);

        $request->validate([
            'timezone' => 'required|string|max:100',
            'date_format' => 'required|string|max:30',
            'time_format' => 'required|string|max:20',
            'printer_width' => 'required|string|max:10',
            'theme' => 'required|string|in:light,dark,auto',
            'price_decimals' => 'required|in:0,2',
        ]);

        $settings->update([
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
        $authUser = Auth::user();

        $settings = CompanySettings::firstOrCreate([
            'company_idfk' => $authUser->company_idfk
        ]);

        $settings->update([
            'timezone' => 'America/Mexico_City',
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i',
            'auto_print' => true,
            'show_taxes' => true,
            'printer_width' => '80mm',
            'theme' => 'light',
            'price_decimals' => '2',
        ]);

        return redirect()->route('settings', ['tab' => 'preferencias'])
            ->with('success', 'Preferencias restablecidas correctamente.');
    }

    private function isGerente(): bool
    {
        return $this->getUserRoleName(Auth::user()) === 'GERENTE';
    }

    private function getUserRoleName(User $user): string
    {
        return match ((int) $user->rol_idfk) {
            1 => 'ADMINISTRADOR',
            2 => 'GERENTE',
            3 => 'CAJERO',
            default => '',
        };
    }

    private function isAdminRoleName(string $roleName): bool
    {
        return in_array($roleName, ['ADMIN', 'ADMINISTRADOR'], true);
    }

    private function gerenteCannotManageTarget(User $authUser, User $targetUser): bool
    {
        $authRole = $this->getUserRoleName($authUser);
        $targetRole = $this->getUserRoleName($targetUser);

        return $authRole === 'GERENTE' && $this->isAdminRoleName($targetRole);
    }

    private function getCompanyOwnerUserId(int $companyId): ?int
    {
        return User::where('company_idfk', $companyId)
            ->where('rol_idfk', 1)
            ->orderBy('userr_id')
            ->value('userr_id');
    }

    private function isCompanyOwner(User $user): bool
    {
        $ownerId = $this->getCompanyOwnerUserId((int) $user->company_idfk);

        return $ownerId !== null && (int) $ownerId ===  (int) $user->userr_id;
    }

    private function cannotManageOwner(User $authUser, User $targetUser): bool
    {
        $ownerId = $this->getCompanyOwnerUserId((int) $authUser->company_idfk);

        if($ownerId === null){
            return false;
        }

        $isTargetOwner = (int) $targetUser->userr_id === (int) $ownerId;
        $isAuthOwner = (int) $authUser->userr_id === (int) $ownerId;

        return $isTargetOwner && !$isAuthOwner;
    }
}
