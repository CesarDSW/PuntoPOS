<?php

namespace App\Http\Controllers\Api\Catalogo;

use App\Support\UserAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CategoryController extends CatalogBaseController
{
    private function getEditableCategoryOrFail(int $id, int $companyId)
    {
        $category = DB::table('category')
            ->where('category_id', $id)
            ->where(function ($q) use ($companyId) {
                $q->where('company_idfk', $companyId)
                  ->orWhereNull('company_idfk');
            })
            ->first();

        if (!$category) {
            throw ValidationException::withMessages([
                'category_id' => ['La categoría no existe para la empresa del usuario.'],
            ]);
        }

        return $category;
    }

    public function index()
    {
        $this->authorizeCategoryPermission('view');

        $authUser = Auth::user();
        $companyId = (int) $authUser->company_idfk;

        $categories = DB::table('category as c')
            ->where('c.company_idfk', $companyId)
            ->select([
                'c.category_id',
                'c.name_category',
                'c.description_category',
                'c.status_category',
            ])
            ->selectRaw("
                (
                    SELECT COUNT(*)
                    FROM productt p
                    WHERE p.company_idfk = c.company_idfk
                    AND p.category_idfk = c.category_id
                ) as products_count
            ")
            ->selectRaw("
                (
                    SELECT COUNT(*)
                    FROM servicee s
                    WHERE s.company_idfk = c.company_idfk
                    AND s.category_idfk = c.category_id
                ) as services_count
            ")
            ->orderBy('c.name_category')
            ->get()
            ->map(function ($row) {
                return [
                    'category_id' => (int) $row->category_id,
                    'name_category' => $row->name_category,
                    'description_category' => $row->description_category,
                    'status_category' => (int) $row->status_category,
                    'products_count' => (int) $row->products_count,
                    'services_count' => (int) $row->services_count,
                    'items_count' => (int) $row->products_count + (int) $row->services_count,
                    'can_edit' => true,
                    'can_delete' => true,
                ];
            })
            ->values();

        return response()->json($categories);
    }

    public function show(int $id)
    {
        $this->authorizeCategoryPermission('view');

        $companyId = $this->getCompanyId();

        $category = $this->getEditableCategoryOrFail($id, $companyId);

        return response()->json($category);
    }

    public function store(Request $request)
    {
        $this->authorizeCategoryPermission('create');

        $companyId = $this->getCompanyId();

        $validated = $request->validate([
            'name_category' => ['required', 'string', 'max:15'],
            'description_category' => ['nullable', 'string', 'max:250'],
        ]);

        $alreadyExists = DB::table('category')
            ->whereRaw('LOWER(name_category) = ?', [mb_strtolower(trim($validated['name_category']))])
            ->where(function ($q) use ($companyId) {
                $q->where('company_idfk', $companyId)
                  ->orWhereNull('company_idfk');
            })
            ->exists();

        if ($alreadyExists) {
            throw ValidationException::withMessages([
                'name_category' => ['Ya existe una categoría con ese nombre.'],
            ]);
        }

        $categoryId = DB::table('category')->insertGetId([
            'name_category' => trim($validated['name_category']),
            'description_category' => $validated['description_category'] ?? null,
            'type_category' => 'GENERAL',
            'company_idfk' => $companyId,
            'status_category' => 1,
        ]);

        return response()->json([
            'message' => 'Categoría creada correctamente.',
            'category_id' => $categoryId,
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $this->authorizeCategoryPermission('edit');

        $companyId = $this->getCompanyId();
        $category = $this->getEditableCategoryOrFail($id, $companyId);

        $validated = $request->validate([
            'name_category' => ['required', 'string', 'max:15'],
            'description_category' => ['nullable', 'string', 'max:250'],
            'status' => ['nullable'],
        ]);

        $alreadyExists = DB::table('category')
            ->where('category_id', '<>', $id)
            ->whereRaw('LOWER(name_category) = ?', [mb_strtolower(trim($validated['name_category']))])
            ->where(function ($q) use ($companyId) {
                $q->where('company_idfk', $companyId)
                  ->orWhereNull('company_idfk');
            })
            ->exists();

        if ($alreadyExists) {
            throw ValidationException::withMessages([
                'name_category' => ['Ya existe una categoría con ese nombre.'],
            ]);
        }

        DB::table('category')
            ->where('category_id', $id)
            ->update([
                'name_category' => trim($validated['name_category']),
                'description_category' => $validated['description_category'] ?? null,
                'status_category' => $this->normalizeStatusValue($validated['status'] ?? ($category->status_category ?? 1), true),
            ]);

        return response()->json([
            'message' => 'Categoría actualizada correctamente.',
        ]);
    }

    public function deactivate(int $id)
    {
        $this->authorizeCategoryPermission('edit');

        $companyId = $this->getCompanyId();
        $this->getEditableCategoryOrFail($id, $companyId);

        DB::table('category')
            ->where('category_id', $id)
            ->update([
                'status_category' => 0,
            ]);

        return response()->json([
            'message' => 'Categoría desactivada correctamente.',
        ]);
    }

    public function destroy(int $id)
    {
        $this->authorizeCategoryPermission('delete');

        $authUser = Auth::user();
        $companyId = (int) $authUser->company_idfk;

        $category = DB::table('category')
            ->where('category_id', $id)
            ->where('company_idfk', $companyId)
            ->first();
        
        if (!$category) {
            return response()->json([
                'message' => 'La categoría no existe o no pertenece a tu empresa.'
            ], 404);
        }

        $productsCount = DB::table('productt')
            ->where('company_idfk', $companyId)
            ->where('category_idfk', $id)
            ->count();

        $servicesCount = DB::table('servicee')
            ->where('company_idfk', $companyId)
            ->where('category_idfk', $id)
            ->count();

        if (($productsCount > 0 || $servicesCount) > 0) {
            return response()->json([
                'message' => 'No se puede eliminar la categoría porque tiene productos o servicios asociados.',
                'errors' => [
                    'category_id' => [
                        "La categoría tiene {$productsCount} producto(s) y {$servicesCount} servicio(s) asociados."
                    ]
                ],
                'products_count'  => $productsCount,
                'services_count' => $servicesCount,
            ], 422);
        }

        DB::table('category')
            ->where('category_id', $id)
            ->where('company_idfk', $companyId)
            ->delete();

        return response()->json([
            'message' => 'Categoría eliminada correctamente.',
        ]);
    }

    private function authorizeCategoryPermission(string $ability): void 
    {
        $user = Auth::user();

        $allowed = match ($ability) {
            'view' => UserAccess::has($user, 'catalog.view'),
            'create' => UserAccess::has($user, 'catalog.categories.create'),
            'edit' => UserAccess::has($user, 'catalog.categories.edit'),
            'delete' => UserAccess::has($user, 'catalog.categories.delete'),
            default => false,
        };

        if (!$user || !$allowed) {
            abort(403, 'No autorizado.');
        }
    }
}