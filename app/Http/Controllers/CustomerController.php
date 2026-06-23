<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    private function getAuthenticatedUser()
    {
        $user = Auth::user();

        abort_unless($user && $user->company_idfk, 403, 'Usuario no autorizado.');

        return $user;
    }

    private function getCompanyCustomerOrFail(int $customerId, int $companyId): Customer
    {
        return Customer::where('company_idfk', $companyId)
            ->where('customer_id', $customerId)
            ->where('status_customer', 1)
            ->firstOrFail();
    }

    public function showCustomers()
    {
        $user = $this->getAuthenticatedUser();
        $companyId = (int) $user->company_idfk;

        $salesResume = DB::table('sale')
            ->where('company_idfk', $companyId)
            ->select('customer_idfk')
            ->selectRaw('COALESCE(SUM(CASE WHEN status_sale <> "CANCELADA" THEN total ELSE 0 END), 0) AS total_spent')
            ->selectRaw('SUM(CASE WHEN status_sale <> "CANCELADA" THEN 1 ELSE 0 END) AS purchases_count')
            ->selectRaw('MAX(CASE WHEN status_sale <> "CANCELADA" THEN date_time ELSE NULL END) AS last_purchase_at')
            ->groupBy('customer_idfk');

        $customers = Customer::query()
            ->from('customer')
            ->leftJoinSub($salesResume, 'sales_resume', function ($join) {
                $join->on('customer.customer_id', '=', 'sales_resume.customer_idfk');
            })
            ->where('customer.company_idfk', $companyId)
            ->where('customer.status_customer', 1)
            ->select('customer.*')
            ->selectRaw('COALESCE(sales_resume.total_spent, 0) AS total_spent')
            ->selectRaw('COALESCE(sales_resume.purchases_count, 0) AS purchases_count')
            ->selectRaw('sales_resume.last_purchase_at AS last_purchase_at')
            ->orderBy('customer.customer_id', 'desc')
            ->get();

        $totalCustomers = $customers->count();
        $customersWithPurchases = $customers->where('purchases_count', '>', 0)->count();
        $totalValue = (float) $customers->sum('total_spent');
        $avgSpend = $totalCustomers > 0 ? ($totalValue / $totalCustomers) : 0;

        return view('customers.index', compact(
            'customers',
            'totalCustomers',
            'customersWithPurchases',
            'totalValue',
            'avgSpend'
        ));
    }

    public function storeCustomers(Request $request)
    {
        $user = $this->getAuthenticatedUser();
        $companyId = (int) $user->company_idfk;

        $validated = $request->validate([
            'name_customer' => [
                'required',
                'string',
                'max:100',
            ],

            'phone' => [
                'required',
                'string',
                'max:10',
                Rule::unique('customer', 'phone')->where(function ($query) use ($companyId) {
                    return $query
                        ->where('company_idfk', $companyId)
                        ->where('status_customer', 1);
                }),
            ],

            'email' => [
                'required',
                'email',
                'max:320',
                Rule::unique('customer', 'email')->where(function ($query) use ($companyId) {
                    return $query
                        ->where('company_idfk', $companyId)
                        ->where('status_customer', 1);
                }),
            ],
        ], [
            'name_customer.required' => 'El nombre del cliente es obligatorio.',
            'phone.required' => 'El teléfono del cliente es obligatorio.',
            'phone.unique' => 'Este número de teléfono ya está registrado en otro cliente.',
            'phone.max' => 'El teléfono no debe tener más de 10 caracteres.',
            'email.required' => 'El correo del cliente es obligatorio.',
            'email.email' => 'Ingresa un correo válido.',
            'email.unique' => 'Este correo ya está registrado en otro cliente.',
        ]);

        try {
            Customer::create([
                'name_customer' => $validated['name_customer'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'company_idfk' => $companyId,
                'status_customer' => 1,
            ]);

            return redirect()
                ->route('customers')
                ->with('success', 'Cliente registrado correctamente.');
        } catch (\Exception $e) {
            return back()
                ->withErrors([
                    'error' => 'Ocurrió un error al registrar el cliente: ' . $e->getMessage(),
                ])
                ->withInput();
        }
    }

    public function showCustomerHistory($id)
    {
        $user = $this->getAuthenticatedUser();
        $companyId = (int) $user->company_idfk;

        $customer = $this->getCompanyCustomerOrFail((int) $id, $companyId);

        $paymentsResume = DB::table('payments')
            ->select('sale_idfk')
            ->selectRaw("GROUP_CONCAT(DISTINCT payment_method ORDER BY payment_method SEPARATOR ', ') AS payment_methods")
            ->groupBy('sale_idfk');

        $sales = DB::table('sale as s')
            ->leftJoinSub($paymentsResume, 'pay', function ($join) {
                $join->on('pay.sale_idfk', '=', 's.sale_id');
            })
            ->where('s.company_idfk', $companyId)
            ->where('s.customer_idfk', $customer->customer_id)
            ->orderByDesc('s.date_time')
            ->select(
                's.sale_id',
                's.date_time',
                's.total',
                's.status_sale'
            )
            ->selectRaw("COALESCE(pay.payment_methods, '-') AS payment_methods")
            ->get();

        $totals = DB::table('sale')
            ->where('company_idfk', $companyId)
            ->where('customer_idfk', $customer->customer_id)
            ->where('status_sale', '<>', 'CANCELADA')
            ->selectRaw('COALESCE(SUM(total), 0) AS total_spent')
            ->selectRaw('COUNT(*) AS purchases_count')
            ->selectRaw('MAX(date_time) AS last_purchase_at')
            ->first();

        $totalSpent = (float) ($totals->total_spent ?? 0);
        $purchasesCount = (int) ($totals->purchases_count ?? 0);
        $avgTicket = $purchasesCount > 0 ? ($totalSpent / $purchasesCount) : 0;
        $lastPurchaseAt = $totals->last_purchase_at ?? null;

        return view('customers.history', compact(
            'customer',
            'sales',
            'totalSpent',
            'purchasesCount',
            'avgTicket',
            'lastPurchaseAt'
        ));
    }

    public function showCustomerSaleDetail($customerId, $saleId)
    {
        $user = $this->getAuthenticatedUser();
        $companyId = (int) $user->company_idfk;

        $customer = $this->getCompanyCustomerOrFail((int) $customerId, $companyId);

        $paymentsResume = DB::table('payments')
            ->select('sale_idfk')
            ->selectRaw("GROUP_CONCAT(DISTINCT payment_method ORDER BY payment_method SEPARATOR ', ') AS payment_methods")
            ->selectRaw('MAX(amount_paid) AS amount_paid')
            ->selectRaw('MAX(change_given) AS change_given')
            ->selectRaw('MAX(reference_payment) AS reference_payment')
            ->groupBy('sale_idfk');

        $sale = DB::table('sale as s')
            ->join('branch as b', 'b.branch_id', '=', 's.branch_idfk')
            ->join('userr as u', 'u.userr_id', '=', 's.cashier_userr_idfk')
            ->join('company as c', 'c.company_id', '=', 's.company_idfk')
            ->leftJoinSub($paymentsResume, 'pay', function ($join) {
                $join->on('pay.sale_idfk', '=', 's.sale_id');
            })
            ->where('s.company_idfk', $companyId)
            ->where('s.customer_idfk', $customer->customer_id)
            ->where('s.sale_id', (int) $saleId)
            ->select(
                's.sale_id',
                's.date_time',
                's.subtotal',
                's.discount',
                's.total',
                's.status_sale',
                'b.name_branch',
                'u.name_user as cashier_name',
                'c.name_company'
            )
            ->selectRaw("COALESCE(pay.payment_methods, '-') AS payment_methods")
            ->selectRaw('COALESCE(pay.amount_paid, 0) AS amount_paid')
            ->selectRaw('COALESCE(pay.change_given, 0) AS change_given')
            ->selectRaw('pay.reference_payment AS reference_payment')
            ->first();

        abort_unless($sale, 404);

        $items = DB::table('saleitem as si')
            ->leftJoin('productt as p', 'p.product_id', '=', 'si.product_idfk')
            ->leftJoin('servicee as sv', 'sv.service_id', '=', 'si.service_idfk')
            ->where('si.sale_idfk', $sale->sale_id)
            ->select(
                'si.item_type',
                'si.amount',
                'si.unit_price',
                'si.discount',
                'si.total_line'
            )
            ->selectRaw('COALESCE(p.name_product, sv.name_service) AS item_name')
            ->orderBy('si.saleitem_id')
            ->get();

        return view('customers.sale-detail', compact('customer', 'sale', 'items'));
    }

    public function editCustomer($id)
    {
        $user = $this->getAuthenticatedUser();
        $companyId = (int) $user->company_idfk;

        $customer = $this->getCompanyCustomerOrFail((int) $id, $companyId);

        return view('customers.edit', compact('customer'));
    }

    public function updateCustomer(Request $request, $id)
    {
        $user = $this->getAuthenticatedUser();
        $companyId = (int) $user->company_idfk;

        $customer = $this->getCompanyCustomerOrFail((int) $id, $companyId);

        $validated = $request->validate([
            'name_customer' => [
                'required',
                'string',
                'max:100',
            ],

            'phone' => [
                'required',
                'string',
                'max:10',
                Rule::unique('customer', 'phone')
                    ->ignore($customer->customer_id, 'customer_id')
                    ->where(function ($query) use ($companyId) {
                        return $query
                            ->where('company_idfk', $companyId)
                            ->where('status_customer', 1);
                    }),
            ],

            'email' => [
                'required',
                'email',
                'max:320',
                Rule::unique('customer', 'email')
                    ->ignore($customer->customer_id, 'customer_id')
                    ->where(function ($query) use ($companyId) {
                        return $query
                            ->where('company_idfk', $companyId)
                            ->where('status_customer', 1);
                    }),
            ],
        ], [
            'name_customer.required' => 'El nombre del cliente es obligatorio.',
            'phone.required' => 'El teléfono del cliente es obligatorio.',
            'phone.unique' => 'Este número de teléfono ya está registrado en otro cliente.',
            'phone.max' => 'El teléfono no debe tener más de 10 caracteres.',
            'email.required' => 'El correo del cliente es obligatorio.',
            'email.email' => 'Ingresa un correo válido.',
            'email.unique' => 'Este correo ya está registrado en otro cliente.',
        ]);

        try {
            $customer->update([
                'name_customer' => $validated['name_customer'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
            ]);

            return redirect()
                ->route('customers')
                ->with('success', 'Cliente actualizado correctamente.');
        } catch (\Exception $e) {
            return back()
                ->withErrors([
                    'error' => 'Ocurrió un error al actualizar el cliente: ' . $e->getMessage(),
                ])
                ->withInput();
        }
    }

    public function deleteCustomer($id)
    {
        $user = $this->getAuthenticatedUser();
        $companyId = (int) $user->company_idfk;
        $customerId = (int) $id;

        $customer = $this->getCompanyCustomerOrFail($customerId, $companyId);

        try {
            DB::table('customer')
                ->where('customer_id', $customer->customer_id)
                ->where('company_idfk', $companyId)
                ->update([
                    'status_customer' => 0,
                ]);

            return redirect()
                ->route('customers')
                ->with('success', 'Cliente eliminado correctamente.');
        } catch (\Exception $e) {
            return back()
                ->withErrors([
                    'error' => 'Ocurrió un error al eliminar el cliente: ' . $e->getMessage(),
                ]);
        }
    }
}