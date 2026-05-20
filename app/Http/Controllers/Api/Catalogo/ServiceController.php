<?php

namespace App\Http\Controllers\Api\Catalogo;

use App\Support\UserAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class ServiceController extends CatalogBaseController
{
    public function show(int $id)
    {
        $this->authorizeServicePermission('view');

        $companyId = $this->getCompanyId();

        $service = DB::table('servicee')
            ->where('service_id', $id)
            ->where('company_idfk', $companyId)
            ->first();

        if (!$service) {
            throw ValidationException::withMessages([
                'service_id' => ['El servicio no existe para la empresa del usuario.'],
            ]);
        }

        return response()->json($service);
    }

    public function store(Request $request)
    {
        $this->authorizeServicePermission('create');

        $companyId = $this->getCompanyId();

        $validated = $request->validate([
            'name_service' => ['required', 'string', 'max:80'],
            'code_service' => [
                'nullable',
                'string',
                'max:15',
                Rule::unique('servicee', 'code_service')->where(function ($q) use ($companyId) {
                    return $q->where('company_idfk', $companyId);
                }),
            ],
            'category_id' => ['required', 'integer', 'exists:category,category_id'],
            'price' => ['required', 'numeric', 'min:0'],
            'description_service' => ['nullable', 'string', 'max:250'],
            'status' => ['nullable'],
        ]);

        $this->getCategoryOrFail((int) $validated['category_id'], $companyId);

        $status = $this->normalizeStatusValue($validated['status'] ?? 1, true);

        $serviceId = DB::table('servicee')->insertGetId([
            'name_service' => trim($validated['name_service']),
            'code_service' => isset($validated['code_service']) && $validated['code_service'] !== ''
                ? trim($validated['code_service'])
                : null,
            'description_service' => $validated['description_service'] ?? null,
            'price' => $validated['price'],
            'status_service' => $status,
            'company_idfk' => $companyId,
            'category_idfk' => (int) $validated['category_id'],
        ]);

        return response()->json([
            'message' => 'Servicio creado correctamente.',
            'service_id' => $serviceId,
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $this->authorizeServicePermission('edit');

        $companyId = $this->getCompanyId();

        $service = DB::table('servicee')
            ->where('service_id', $id)
            ->where('company_idfk', $companyId)
            ->first();

        if (!$service) {
            throw ValidationException::withMessages([
                'service_id' => ['El servicio no existe para la empresa del usuario.'],
            ]);
        }

        $validated = $request->validate([
            'name_service' => ['required', 'string', 'max:80'],
            'code_service' => [
                'nullable',
                'string',
                'max:15',
                Rule::unique('servicee', 'code_service')
                    ->ignore($id, 'service_id')
                    ->where(function ($q) use ($companyId) {
                        return $q->where('company_idfk', $companyId);
                    }),
            ],
            'category_id' => ['required', 'integer', 'exists:category,category_id'],
            'price' => ['required', 'numeric', 'min:0'],
            'description_service' => ['nullable', 'string', 'max:250'],
            'status' => ['nullable'],
        ]);

        $this->getCategoryOrFail((int) $validated['category_id'], $companyId);

        $status = $this->normalizeStatusValue($validated['status'] ?? $service->status_service, true);

        DB::table('servicee')
            ->where('service_id', $id)
            ->where('company_idfk', $companyId)
            ->update([
                'name_service' => trim($validated['name_service']),
                'code_service' => isset($validated['code_service']) && $validated['code_service'] !== ''
                    ? trim($validated['code_service'])
                    : null,
                'description_service' => $validated['description_service'] ?? null,
                'price' => $validated['price'],
                'status_service' => $status,
                'category_idfk' => (int) $validated['category_id'],
            ]);

        return response()->json([
            'message' => 'Servicio actualizado correctamente.',
        ]);
    }

    public function deactivate(int $id)
    {
        $this->authorizeServicePermission('edit');

        $companyId = $this->getCompanyId();

        $exists = DB::table('servicee')
            ->where('service_id', $id)
            ->where('company_idfk', $companyId)
            ->exists();

        if (!$exists) {
            throw ValidationException::withMessages([
                'service_id' => ['El servicio no existe para la empresa del usuario.'],
            ]);
        }

        DB::table('servicee')
            ->where('service_id', $id)
            ->where('company_idfk', $companyId)
            ->update([
                'status_service' => 0,
            ]);

        return response()->json([
            'message' => 'Servicio desactivado correctamente.',
        ]);
    }

    public function destroy(int $id)
    {
        $this->authorizeServicePermission('delete');

        $companyId = $this->getCompanyId();

        $exists = DB::table('servicee')
            ->where('service_id', $id)
            ->where('company_idfk', $companyId)
            ->exists();

        if (!$exists) {
            throw ValidationException::withMessages([
                'service_id' => ['El servicio no existe para la empresa del usuario.'],
            ]);
        }

        try {
            DB::table('servicee')
                ->where('service_id', $id)
                ->where('company_idfk', $companyId)
                ->delete();

            return response()->json([
                'message' => 'Servicio eliminado correctamente.',
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'No se puede eliminar el servicio porque tiene registros relacionados.',
            ], 422);
        }
    }

    private function authorizeServicePermission(string $ability): void 
    {
        $user = Auth::user();

        $allowed = match ($ability) {
            'view' => UserAccess::has($user, 'catalog.view'),
            'create' => UserAccess::has($user, 'catalog.services.create'),
            'edit' => UserAccess::has($user, 'catalog.services.edit'),
            'delete' => UserAccess::has($user, 'catalog.services.delete'),
            default => false,
        };

        if (!$user || !$allowed) {
            abort(403, 'No autorizado.');
        }
    }
}