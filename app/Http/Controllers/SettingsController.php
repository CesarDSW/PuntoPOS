<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\User;
use App\Models\UserPreference;
use App\Support\UserAccess;
use App\Models\Rol;
use App\Models\Company;
use App\Models\CompanySettings;
use App\Models\SystemNotification;
use App\Support\TimezoneCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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
        $currentUser = $authUser;

        $access = UserAccess::summary($currentUser);
        $grantablePermissions = UserAccess::grantablePermissions($currentUser);

        $currentRoleName = UserAccess::roleName($currentUser);
        $currentUserId = $currentUser->userr_id;
        $canEditBusinessProfile = $access['can_edit_business_profile'];
        $ownerUserId = $this->getCompanyOwnerUserId((int) $authUser->company_idfk);

        $branchCards = DB::table('branch')
            ->where('company_idfk', $authUser->company_idfk)
            ->orderBy('name_branch')
            ->get([
                'branch_id',
                'name_branch',
                'address',
                'city',
                'state',
                'phone',
                'responsible',
                'email',
            ]);

        $branches = $branchCards->map(function ($branch) {
            return (object) [
                'branch_id' => $branch->branch_id,
                'name_branch' => $branch->name_branch,
            ];
        });

        $company = Company::findOrFail($authUser->company_idfk);
        $roles = DB::table('rol')->orderBy('type_rol')->get();
        $baseUsers = User::where('company_idfk', $authUser->company_idfk)->get();

        $userBranchMap = DB::table('user_branch')
            ->whereIn('userr_idfk', $baseUsers->pluck('userr_id'))
            ->pluck('branch_idfk', 'userr_idfk');

        $users = collect($baseUsers)->map(function ($userItem) use ($currentUser, $userBranchMap) {
            $targetUser = User::find($userItem->userr_id);

            $userItem->can_edit_ui = $targetUser
                ? UserAccess::canEditTarget($currentUser, $targetUser)
                : false;

            $userItem->can_delete_ui = $targetUser
                ? UserAccess::canDeleteTarget($currentUser, $targetUser)
                : false;

            $userItem->permission_states = $targetUser
                ? UserAccess::userOverrideStates((int) $targetUser->userr_id)
                : [];

            $userItem->branch_idfk = $userBranchMap[$userItem->userr_id] ?? null;

            return $userItem;
        });

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
                'price_decimals' => '2',
            ]
        );

        $userPreference = UserPreference::firstOrCreate(
            ['userr_idfk' => $authUser->userr_id],
            [
                'theme' => 'light',
            ]
        );

        $assignedBranchId = DB::table('user_branch')
            ->where('userr_idfk', $authUser->userr_id)
            ->value('branch_idfk');

        $assignedBranch = null;

        if ($assignedBranchId) {
            $assignedBranch = $branchCards->firstWhere('branch_id', $assignedBranchId);
        }

        return view('settings', [
            'company' => $company,
            'roles' => $roles,
            'users' => $users,
            'settings' => $settings,
            'userPreference' => $userPreference,
            'branches' => $branches,
            'branchCards' => $branchCards,
            'currentRoleName' => $currentRoleName,
            'currentUserId' => $currentUserId,
            'canEditBusinessProfile' => $canEditBusinessProfile,
            'assignedBranch' => $assignedBranch,
            'ownerUserId' => $ownerUserId,
            'access' => $access,
            'grantablePermissions' => $grantablePermissions,
        ]);
    }

    //Funcion para actualizar o agregar datos en configuracion
    public function updateSettings(Request $request)
    {
        $authUser = Auth::user();
        
        if(!UserAccess::has($authUser, 'settings.profile.edit')) {
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
            $oldLogo = $company->logo;

            $newLogoPath = $request->file('logo')->store('logos', 'public');
            $data['logo'] = $newLogoPath;

            if (!empty($oldLogo) && $oldLogo !== $newLogoPath && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
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
        $currentUser = $authUser;

        $validated = $request->validate([
            'name_user' => 'required|string|max:100',
            'phone' => 'required|string|max:10',
            'email' => 'required|email|max:320|unique:userr,email',
            'password' => 'required|min:8|confirmed',
            'rol_idfk' => 'required|integer|exists:rol,rol_id',
            'branch_idfk' => 'nullable|integer|exists:branch,branch_id',
        ]);

        $targetRole = DB::table('rol')
            ->where('rol_id', $validated['rol_idfk'])
            ->value('type_rol');

        $roleName = UserAccess::normalizeRoleName($targetRole);

        if (!UserAccess::canCreateRole($currentUser, $roleName)) {
            return back()
                ->withErrors(['rol_idfk' => 'No tienes permiso para crear usuarios con ese rol.'])
                ->withInput();
        }

        if (!UserAccess::canAssignBranch($currentUser)) {
            $validated['branch_idfk'] = null;
        }

        if (!empty($validated['branch_idfk'])) {
            $branchBelongsToCompany = DB::table('branch')
                ->where('branch_id', $validated['branch_idfk'])
                ->where('company_idfk', $authUser->company_idfk)
                ->exists();

            if (!$branchBelongsToCompany) {
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

        if (!empty($validated['branch_idfk'])) {
            DB::table('user_branch')->updateOrInsert(
                ['userr_idfk' => $user->userr_id],
                ['branch_idfk' => $validated['branch_idfk']]
            );
        }

        if ($roleName === 'GERENTE' && !empty($validated['branch_idfk'])) {
            $this->syncBranchResponsibleFromManager(
                (int) $user->userr_id,
                (int) $validated['branch_idfk'],
                (int) $authUser->company_idfk
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

        if (!UserAccess::canEditTarget($authUser, $user)) {
            return redirect()->route('settings', ['tab' => 'usuarios'])
                ->withErrors(['error' => 'No tienes permiso para editar este usuario.']);
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
        
        $currentUser = $authUser;
        $targetUser = $user;

        if(!UserAccess::canEditTarget($currentUser, $targetUser)){
            return back()->withErrors([
                'general' => 'No tienes permiso para editar este usuario.'
            ]);
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

        $currentBranchId = DB::table('user_branch')
            ->where('userr_idfk', $targetUser->userr_id)
            ->value('branch_idfk');
        

        $isSelf = (int) $currentUser->userr_id === (int) $targetUser->userr_id;
        $isTargetOwner = UserAccess::isOwner($targetUser);
        $currentActorRole = UserAccess::roleName($currentUser);

        if ($isTargetOwner || ($isSelf && in_array($currentActorRole, ['ADMINISTRADOR', 'GERENTE'], true))) {
            $validated['rol_idfk'] = $targetUser->rol_idfk;
        }

        if ($isSelf && $currentActorRole === 'GERENTE') {
            $validated['branch_idfk'] = $currentBranchId;
        }

        if (!UserAccess::canAssignBranch($currentUser)) {
            $validated['branch_idfk'] = $currentBranchId;
        }

        $role = Rol::findOrFail($validated['rol_idfk']);
        $roleName = UserAccess::normalizeRoleName($role->type_rol);

        if (!in_array($roleName, ['CAJERO', 'GERENTE'], true)) {
            $validated['branch_idfk'] = null;
        }

        if (!empty($validated['branch_idfk'])) {
            $branchBelongsToCompany = DB::table('branch')
                ->where('branch_id', $validated['branch_idfk'])
                ->where('company_idfk', $authUser->company_idfk)
                ->exists();

            if (!$branchBelongsToCompany) {
                throw ValidationException::withMessages([
                    'branch_idfk' => ['La sucursal seleccionada no pertenece a tu empresa.'],
                ]);
            }
        }

        $oldName = $user->name_user;
        $oldEmail = $user->email;
        $oldBranchId = $currentBranchId;
        $oldRoleName = UserAccess::roleName($user);

        $user->update([
            'name_user' => $validated['name_user'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'rol_idfk' => $validated['rol_idfk'],
        ]);

        if (in_array($roleName, ['CAJERO', 'GERENTE'], true) && !empty($validated['branch_idfk'])) {
            DB::table('user_branch')->updateOrInsert(
                ['userr_idfk' => $user->userr_id],
                ['branch_idfk' => $validated['branch_idfk']]
            );
        } else {
            DB::table('user_branch')
                ->where('userr_idfk', $user->userr_id)
                ->delete();
        }

        if ($oldRoleName === 'GERENTE' && $oldBranchId && (
            $roleName !== 'GERENTE' || (int) ($validated['branch_idfk'] ?? 0) !== (int) $oldBranchId
        )) {
            $this->clearBranchResponsibleIfMatches(
                (int) $oldBranchId,
                $oldName,
                $oldEmail,
                (int) $authUser->company_idfk
            );
        }

        if ($roleName === 'GERENTE' && !empty($validated['branch_idfk'])) {
            $this->syncBranchResponsibleFromManager(
                (int) $user->userr_id,
                (int) $validated['branch_idfk'],
                (int) $authUser->company_idfk
            );
        }

        if (UserAccess::canManagePermissions($currentUser)) {
            UserAccess::syncUserOverrides(
                $currentUser,
                $targetUser,
                $request->input('permission_states', [])
            );
        }

        return redirect()->route('settings', ['tab' => 'usuarios'])
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function storeBranch(Request $request)
    {
        $authUser = Auth::user();

        if (!UserAccess::has($authUser, 'branch.create')) {
            return response()->json([
                'message' => 'No tienes permiso para crear sucursales.'
            ], 403);
        }

        $validated = $request->validate([
            'name_branch' => 'required|string|max:50',
            'address' => 'required|string|max:150',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'phone' => 'nullable|string|max:10',
            'responsible_user_id' => 'nullable|integer|exists:userr,userr_id',
        ]);

        $companyId = (int) $authUser->company_idfk;

        $branchesCount = DB::table('branch')
            ->where('company_idfk', $companyId)
            ->count();

        $isFirstBranch = $branchesCount === 0;

        $responsibleUser = null;
        $responsibleName = null;
        $responsibleEmail = null;
        $previousBranchId = null;

        if ($isFirstBranch) {
            $responsibleUser = $authUser;
            $responsibleName = $authUser->name_user;
            $responsibleEmail = $authUser->email;
        } else {
            if (empty($validated['responsible_user_id'])) {
                return response()->json([
                    'errors' => [
                        'responsible_user_id' => ['Debes seleccionar un gerente responsable para la sucursal.']
                    ]
                ], 422);
            }

            $responsibleUser = User::where('company_idfk', $companyId)
                ->where('userr_id', $validated['responsible_user_id'])
                ->first();

            if (!$responsibleUser) {
                return response()->json([
                    'errors' => [
                        'responsible_user_id' => ['El responsable seleccionado no pertenece a tu empresa.']
                    ]
                ], 422);
            }

            if (UserAccess::roleName($responsibleUser) !== 'GERENTE') {
                return response()->json([
                    'errors' => [
                        'responsible_user_id' => ['Solo puedes asignar como responsable a un usuario con rol gerente.']
                    ]
                ], 422);
            }

            $responsibleName = $responsibleUser->name_user;
            $responsibleEmail = $responsibleUser->email;

            $previousBranchId = DB::table('user_branch')
                ->where('userr_idfk', $responsibleUser->userr_id)
                ->value('branch_idfk');
        }

        $branchId = DB::table('branch')->insertGetId([
            'name_branch' => $validated['name_branch'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'phone' => $validated['phone'] ?? null,
            'responsible' => $responsibleName,
            'email' => $responsibleEmail,
            'company_idfk' => $companyId,
        ]);

        if ($responsibleUser) {
            if (!$isFirstBranch && $previousBranchId && (int) $previousBranchId !== (int) $branchId) {
                $this->clearBranchResponsibleIfMatches(
                    (int) $previousBranchId,
                    $responsibleName,
                    $responsibleEmail,
                    $companyId
                );
            }

            DB::table('user_branch')->updateOrInsert(
                ['userr_idfk' => $responsibleUser->userr_id],
                ['branch_idfk' => $branchId]
            );
        }

        return response()->json([
            'message' => $isFirstBranch
                ? 'Primera sucursal creada correctamente y asignada al owner.'
                : 'Sucursal creada correctamente.',
            'branch_id' => $branchId,
        ]);
    }

    public function updateNotifications(Request $request)
    {
        $user = auth()->user();

        $settings = CompanySettings::firstOrCreate(
            [
                'company_idfk' => $user->company_idfk,
            ],
            [
                'notify_low_stock' => true,
                'notify_out_of_stock' => true,
                'notify_sale_cancelled' => true,
                'notify_sale_pending' => true,
                'notify_sale_completed' => true,
            ]
        );

        $settings->update([
            'notify_low_stock' => $request->has('notify_low_stock'),
            'notify_out_of_stock' => $request->has('notify_out_of_stock'),
            'notify_sale_cancelled' => $request->has('notify_sale_cancelled'),
            'notify_sale_pending' => $request->has('notify_sale_pending'),
            'notify_sale_completed' => $request->has('notify_sale_completed'),
        ]);

        return redirect()
            ->route('settings', ['tab' => 'notificaciones'])
            ->with('success', 'Configuración de notificaciones actualizada correctamente.');
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
        $validated = $request->validate([
            'timezone' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!TimezoneCatalog::isValid($value)) {
                        $fail('Selecciona una zona horaria válida.');
                    }
                },
            ],
            'date_format' => ['required', 'string', Rule::in(['d/m/Y', 'm/d/Y', 'Y-m-d'])],
            'time_format' => ['required', 'string', Rule::in(['H:i', 'h:i A'])],
            'price_decimals' => ['required', 'string', Rule::in(['0', '2'])],
            'printer_width' => ['required', 'string', Rule::in(['58mm', '80mm'])],
            'theme' => ['required', 'string', Rule::in(['light', 'dark', 'auto'])],
        ]);

        $user = auth()->user();
        $companyId = (int) $user->company_idfk;

        $settings = CompanySettings::firstOrCreate(
            ['company_idfk' => $companyId],
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
                'price_decimals' => '2',
            ]
        );

        $settings->timezone = $validated['timezone'];
        $settings->date_format = $validated['date_format'];
        $settings->time_format = $validated['time_format'];
        $settings->price_decimals = $validated['price_decimals'];
        $settings->printer_width = $validated['printer_width'];
        $settings->auto_print = $request->boolean('auto_print');
        $settings->show_taxes = $request->boolean('show_taxes');
        $settings->save();

        UserPreference::updateOrCreate(
            ['userr_idfk' => $user->userr_id],
            [
                'theme' => $validated['theme'],
            ]
        );

        return redirect()
            ->route('settings', ['tab' => 'preferencias'])
            ->with('success', 'Preferencias actualizadas correctamente.');
    }

    public function resetPreferences()
    {
        $user = auth()->user();
        $companyId = (int) $user->company_idfk;

        $settings = CompanySettings::firstOrCreate(
            ['company_idfk' => $companyId],
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
                'price_decimals' => '2',
            ]
        );

        $settings->timezone = 'America/Mexico_City';
        $settings->date_format = 'd/m/Y';
        $settings->time_format = 'H:i';
        $settings->price_decimals = '2';
        $settings->printer_width = '80mm';
        $settings->auto_print = true;
        $settings->show_taxes = true;
        $settings->save();

        UserPreference::updateOrCreate(
            ['userr_idfk' => $user->userr_id],
            [
                'theme' => 'light',
            ]
        );

        return redirect()
            ->route('settings', ['tab' => 'preferencias'])
            ->with('success', 'Preferencias restablecidas correctamente.');
    }

    private function getCompanyOwnerUserId(int $companyId): ?int
    {
        return User::where('company_idfk', $companyId)
            ->where('rol_idfk', 1)
            ->orderBy('userr_id')
            ->value('userr_id');
    }

    private function syncBranchResponsibleFromManager(?int $userId, ?int $branchId, int $companyId): void
    {
        if (!$userId || !$branchId) {
            return;
        }

        $manager = User::find($userId);

        if (!$manager || (int) $manager->company_idfk !== $companyId) {
            return;
        }

        if (UserAccess::roleName($manager) !== 'GERENTE') {
            return;
        }

        DB::table('branch')
            ->where('branch_id', $branchId)
            ->where('company_idfk', $companyId)
            ->update([
                'responsible' => $manager->name_user,
                'email' => $manager->email,
            ]);
    }

    private function clearBranchResponsibleIfMatches(?int $branchId, ?string $oldName, ?string $oldEmail, int $companyId): void
    {
        if (!$branchId) {
            return;
        }

        $branch = DB::table('branch')
            ->where('branch_id', $branchId)
            ->where('company_idfk', $companyId)
            ->first(['responsible', 'email']);

        if (!$branch) {
            return;
        }

        $branchName = trim((string) ($branch->responsible ?? ''));
        $branchEmail = trim((string) ($branch->email ?? ''));
        $compareName = trim((string) ($oldName ?? ''));
        $compareEmail = trim((string) ($oldEmail ?? ''));

        if ($branchName === $compareName && $branchEmail === $compareEmail) {
            DB::table('branch')
                ->where('branch_id', $branchId)
                ->where('company_idfk', $companyId)
                ->update([
                    'responsible' => null,
                    'email' => null,
                ]);
        }
    }
}
