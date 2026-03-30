<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;

class CustomerController extends Controller
{
    /*Clientes*/
    //Funcion para mostrar la pagina de clientes
    public function showCustomers()
    {
        $user = Auth::user();
        
        $customers = Customer::where('company_idfk', $user->company_idfk)
        ->orderBy('customer_id', 'desc')
        ->get();

        return view('customers.index', compact('customers'));
    }

    //Funcion para actualizar o agregar clientes
    public function storeCustomers(Request $request)
    {
        $request->validate([
            'name_customer' => 'required|string|max:100',
            'phone' => 'required|string|max:10',
            'email' => 'required|email|max:320',
        ]);

        try{
            $user = auth()->user();

            $lastCustomer = Customer::where('company_idfk', $user->company_idfk)
            ->orderBy('customer_id', 'desc')
            ->first();

            $nextNumber = 1;

            if($lastCustomer && !empty($lastCustomer->customer_code)){
                if(preg_match('/CL-(\d+)/', $lastCustomer->customer_code, $matches)){
                    $nextNumber = ((int) $matches[1]) + 1;
                }
            }

            $customerCode = 'CL-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            Customer::create([
                'customer_code' => $customerCode,
                'name_customer' => $request->name_customer,
                'phone' => $request->phone,
                'email' => $request->email,
                'company_idfk' => $user->company_idfk,
            ]);
            
            return redirect()->route('customers')->with('success', 'Cliente registrado correctamente.');
        } catch(\Exception $e){
            return back()->withErrors([
                'error' => 'Ocurrió un error al registrar el cliente: ' . $e->getMessage()
            ])->withInput();
        }
    }

    //Funcion para mostrar el historial de los clientes.
    public function showCustomerHistory($id){

    }

    //Funcion para editar al cliente
    public function editCustomer($id){

    }

    //Funcion para actualizar el cliente
    public function updateCustomer(Request $request, $id){

    }

    //Funcion para borrar el cliente
    public function deleteCustomer(Request $request, $id){

    }
}
