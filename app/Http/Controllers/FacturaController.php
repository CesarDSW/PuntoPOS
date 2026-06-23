<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;

class FacturaController extends Controller
{    
    public function generar($id)
    {
        $venta = Sale::with([
            'customer',
            'payment',
            'items.product',
            'branch'
        ])->findOrFail($id);
            // Calcular subtotal
            $subtotal = $venta->items->sum(function ($item) {
                return $item->quantity * $item->price;
            });
            // Calcular IVA (16%)
            $iva = $subtotal * 0.16;
            // Total
            $total = $subtotal + $iva;

            return view('factura.index', compact('venta', 'subtotal', 'iva', 'total'));   
    }
};
