<?php

namespace App\Http\Controllers\Api\Catalogo;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $companyId = $this->getCompanyId();

        $categories = DB::table('category')
            ->where(function ($q) use ($companyId) {
                $q->where('company_idfk', $companyId)
                  ->orWhereNull('company_idfk');
            })
            ->orderBy('name_category')
            ->get();

        $productCounts = DB::table('productt')
            ->where('company_idfk', $companyId)
            ->select('category_idfk', DB::raw('COUNT(*) as total'))
            ->groupBy('category_idfk')
            ->pluck('total', 'category_idfk');

        $serviceCounts = DB::table('servicee')
            ->where('company_idfk', $companyId)
            ->select('category_idfk', DB::raw('COUNT(*) as total'))
            ->groupBy('category_idfk')
            ->pluck('total', 'category_idfk');

        $result = $categories->map(function ($category) use ($productCounts, $serviceCounts) {
            $productTotal = (int) ($productCounts[$category->category_id] ?? 0);
            $serviceTotal = (int) ($serviceCounts[$category->category_id] ?? 0);

            return [
                'category_id' => $category->category_id,
                'name_category' => $category->name_category,
                'description_category' => $category->description_category,
                'type_category' => $category->type_category,
                'status_category' => (int) ($category->status_category ?? 1),
                'items_count' => $productTotal + $serviceTotal,
                'can_edit' => true,
                'can_delete' => ($productTotal + $serviceTotal) === 0,
            ];
        });

        return response()->json($result);
    }

    public function show(int $id)
    {
        $companyId = $this->getCompanyId();

        $category = $this->getEditableCategoryOrFail($id, $companyId);

        return response()->json($category);
    }

    public function store(Request $request)
    {
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
        $companyId = $this->getCompanyId();
        $this->getEditableCategoryOrFail($id, $companyId);

        $productsCount = DB::table('productt')
            ->where('company_idfk', $companyId)
            ->where('category_idfk', $id)
            ->count();

        $servicesCount = DB::table('servicee')
            ->where('company_idfk', $companyId)
            ->where('category_idfk', $id)
            ->count();

        if (($productsCount + $servicesCount) > 0) {
            throw ValidationException::withMessages([
                'category_id' => ['No puedes eliminar una categoría que todavía tiene elementos asociados.'],
            ]);
        }

        DB::table('category')
            ->where('category_id', $id)
            ->delete();

        return response()->json([
            'message' => 'Categoría eliminada correctamente.',
        ]);
    }
}