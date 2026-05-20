<?php

namespace App\Http\Controllers\Api\Catalogo;

use App\Support\UserAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CatalogBulkUploadController extends CatalogBaseController
{
    private function normalizeText($value): string
    {
        if ($value === null) {
            return '';
        }

        $text = (string) $value;
        $text = str_replace("\xC2\xA0", ' ', $text);

        return trim($text);
    }

    public function template()
    {
        $this->authorizeBulkImport();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'Tipo* (producto/servicio)',
            'Nombre*',
            'SKU/Código*',
            'Categoría*',
            'Precio*',
            'Costo',
            'Stock inicial',
            'Stock mínimo',
            'Descripción',
            'Estado* (activo/inactivo)',
        ];

        foreach ($headers as $index => $header) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($column . '1', $header);
        }

        $sheet->setCellValue('A2', 'producto');
        $sheet->setCellValue('B2', 'Ejemplo Producto');
        $sheet->setCellValue('C2', 'PROD-001');
        $sheet->setCellValue('D2', 'Electrónica');
        $sheet->setCellValue('E2', '1500');
        $sheet->setCellValue('F2', '1100');
        $sheet->setCellValue('G2', '10');
        $sheet->setCellValue('H2', '2');
        $sheet->setCellValue('I2', 'Descripción opcional');
        $sheet->setCellValue('J2', 'activo');

        $sheet->setCellValue('A3', 'servicio');
        $sheet->setCellValue('B3', 'Ejemplo Servicio');
        $sheet->setCellValue('C3', 'SERV-001');
        $sheet->setCellValue('D3', 'Servicios técnicos');
        $sheet->setCellValue('E3', '800');
        $sheet->setCellValue('F3', '');
        $sheet->setCellValue('G3', '');
        $sheet->setCellValue('H3', '');
        $sheet->setCellValue('I3', 'Servicio opcional');
        $sheet->setCellValue('J3', 'activo');

        foreach (range('A', 'J') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $path = tempnam(sys_get_temp_dir(), 'catalog_template_') . '.xlsx';

        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        return response()->download($path, 'catalogo_template.xlsx')->deleteFileAfterSend(true);
    }

    public function upload(Request $request)
    {
        $this->authorizeBulkImport();

        $companyId = $this->getCompanyId();

        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branch,branch_id'],
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $branchId = $this->resolveBranchId(
            isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
            $companyId
        );

        if (!$branchId) {
            throw ValidationException::withMessages([
                'branch_id' => ['Debes tener una sucursal seleccionada para la carga masiva.'],
            ]);
        }

        $sheet = IOFactory::load($request->file('file')->getPathname())->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        if (count($rows) <= 1) {
            throw ValidationException::withMessages([
                'file' => ['El archivo no contiene filas de datos.'],
            ]);
        }

        $createdProducts = 0;
        $assignedProducts = 0;
        $createdServices = 0;
        $validRowsFound = 0;

        DB::transaction(function () use (
            $rows,
            $companyId,
            $branchId,
            &$createdProducts,
            &$assignedProducts,
            &$createdServices,
            &$validRowsFound
        ) {
            foreach ($rows as $rowNumber => $row) {
                if ($rowNumber === 1) {
                    continue;
                }

                $tipo = $this->normalizeText($row['A'] ?? '');
                $nombre = $this->normalizeText($row['B'] ?? '');
                $codigo = $this->normalizeText($row['C'] ?? '');
                $categoriaNombre = $this->normalizeText($row['D'] ?? '');
                $precio = $this->normalizeText($row['E'] ?? '');
                $costo = $this->normalizeText($row['F'] ?? '');
                $stockInicial = $this->normalizeText($row['G'] ?? '');
                $stockMinimo = $this->normalizeText($row['H'] ?? '');
                $descripcion = $this->normalizeText($row['I'] ?? '');
                $estado = $this->normalizeText($row['J'] ?? '');

                $isEmptyRow =
                    $tipo === '' &&
                    $nombre === '' &&
                    $codigo === '' &&
                    $categoriaNombre === '' &&
                    $precio === '' &&
                    $costo === '' &&
                    $stockInicial === '' &&
                    $stockMinimo === '' &&
                    $descripcion === '' &&
                    $estado === '';

                if ($isEmptyRow) {
                    continue;
                }

                $validRowsFound++;

                if ($tipo === '' || $nombre === '' || $codigo === '' || $categoriaNombre === '' || $precio === '' || $estado === '') {
                    throw ValidationException::withMessages([
                        'file' => ["Fila {$rowNumber}: faltan columnas obligatorias."],
                    ]);
                }

                $categoria = DB::table('category')
                    ->whereRaw('LOWER(name_category) = ?', [mb_strtolower($categoriaNombre)])
                    ->where(function ($q) use ($companyId) {
                        $q->where('company_idfk', $companyId)
                          ->orWhereNull('company_idfk');
                    })
                    ->first();

                if (!$categoria) {
                    throw ValidationException::withMessages([
                        'file' => ["Fila {$rowNumber}: la categoría '{$categoriaNombre}' no existe."],
                    ]);
                }

                if (!is_numeric($precio)) {
                    throw ValidationException::withMessages([
                        'file' => ["Fila {$rowNumber}: el precio no es válido."],
                    ]);
                }

                $status = $this->normalizeStatusValue($estado, true);
                $tipoNormalizado = mb_strtolower($tipo);

                if (in_array($tipoNormalizado, ['producto', 'product'], true)) {
                    $existingProduct = DB::table('productt')
                        ->where('company_idfk', $companyId)
                        ->where('code_product', $codigo)
                        ->first();

                    if ($existingProduct) {
                        $existingBranchStock = DB::table('branch_product_stock')
                            ->where('branch_idfk', $branchId)
                            ->where('product_idfk', $existingProduct->product_id)
                            ->first();

                        if ($existingBranchStock && (int) $existingBranchStock->status_stock === 1) {
                            throw ValidationException::withMessages([
                                'file' => ["Fila {$rowNumber}: el producto '{$codigo}' ya existe en la sucursal actual."],
                            ]);
                        }

                        if ($existingBranchStock) {
                            DB::table('branch_product_stock')
                                ->where('branch_idfk', $branchId)
                                ->where('product_idfk', $existingProduct->product_id)
                                ->update([
                                    'stocks' => $stockInicial !== '' ? (int) $stockInicial : (int) $existingBranchStock->stocks,
                                    'minimum_stock' => $stockMinimo !== '' ? (int) $stockMinimo : (int) $existingBranchStock->minimum_stock,
                                    'status_stock' => $status,
                                ]);
                        } else {
                            DB::table('branch_product_stock')->insert([
                                'branch_idfk' => $branchId,
                                'product_idfk' => $existingProduct->product_id,
                                'stocks' => $stockInicial !== '' ? (int) $stockInicial : 0,
                                'minimum_stock' => $stockMinimo !== '' ? (int) $stockMinimo : 0,
                                'status_stock' => $status,
                            ]);
                        }

                        $this->syncProductGlobalStatus((int) $existingProduct->product_id);
                        $assignedProducts++;
                    } else {
                        $productId = DB::table('productt')->insertGetId([
                            'name_product' => $nombre,
                            'code_product' => $codigo,
                            'description_product' => $descripcion !== '' ? $descripcion : null,
                            'price' => (float) $precio,
                            'cost' => $costo !== '' ? (float) $costo : null,
                            'status_product' => 1,
                            'company_idfk' => $companyId,
                            'category_idfk' => $categoria->category_id,
                        ]);

                        DB::table('branch_product_stock')->insert([
                            'branch_idfk' => $branchId,
                            'product_idfk' => $productId,
                            'stocks' => $stockInicial !== '' ? (int) $stockInicial : 0,
                            'minimum_stock' => $stockMinimo !== '' ? (int) $stockMinimo : 0,
                            'status_stock' => $status,
                        ]);

                        $this->syncProductGlobalStatus((int) $productId);
                        $createdProducts++;
                    }
                } elseif (in_array($tipoNormalizado, ['servicio', 'service'], true)) {
                    if (DB::table('servicee')
                        ->where('company_idfk', $companyId)
                        ->where('code_service', $codigo)
                        ->exists()) {
                        throw ValidationException::withMessages([
                            'file' => ["Fila {$rowNumber}: el código de servicio '{$codigo}' ya existe."],
                        ]);
                    }

                    DB::table('servicee')->insert([
                        'name_service' => $nombre,
                        'code_service' => $codigo !== '' ? $codigo : null,
                        'description_service' => $descripcion !== '' ? $descripcion : null,
                        'price' => (float) $precio,
                        'status_service' => $status,
                        'company_idfk' => $companyId,
                        'category_idfk' => $categoria->category_id,
                    ]);

                    $createdServices++;
                } else {
                    throw ValidationException::withMessages([
                        'file' => ["Fila {$rowNumber}: el tipo debe ser producto o servicio."],
                    ]);
                }
            }
        });

        if ($validRowsFound === 0) {
            throw ValidationException::withMessages([
                'file' => ['El archivo no contiene filas válidas para importar.'],
            ]);
        }

        if (($createdProducts + $assignedProducts + $createdServices) === 0) {
            throw ValidationException::withMessages([
                'file' => ['No se importó ningún elemento. Revisa que el archivo tenga datos válidos.'],
            ]);
        }

        return response()->json([
            'message' => 'Carga masiva realizada correctamente.',
            'products_created' => $createdProducts,
            'products_assigned' => $assignedProducts,
            'services_created' => $createdServices,
            'total_created' => $createdProducts + $createdServices,
            'total_processed' => $createdProducts + $assignedProducts + $createdServices,
            'valid_rows_found' => $validRowsFound,
        ]);
    }

    private function authorizeBulkImport(): void 
    {
        $user = Auth::user();

        if (!$user || !UserAccess::has($user, 'catalog.mass_import')) {
            abort(403, 'No autorizado para carga masiva.');
        }
    }
}