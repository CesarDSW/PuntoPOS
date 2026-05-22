<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura</title>

    <style>
        body {
            background: #f3f4f6;
            font-family: 'Courier New', monospace;
        }

        .container {
            max-width: 420px;
            margin: 30px auto;
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .center { text-align: center; }

        .title {
            font-size: 22px;
            font-weight: bold;
        }

        .line {
            border-top: 2px dashed #333;
            margin: 15px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
        }

        .product { margin-bottom: 10px; }

        .product-name {
            font-weight: bold;
            font-size: 14px;
        }

        .total {
            font-size: 18px;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
        }

        .btn-print {
            display: block;
            width: 100%;
            margin-top: 15px;
            padding: 10px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        @media print {
            body { background: white; }
            .btn-print { display: none; }
            .container { box-shadow: none; border-radius: 0; }
        }
        @media print {
    body {
        zoom: 1.5; 
    }
}
    </style>
</head>
<body>

<div class="container">

    <div class="center">
       
         <div class="title"><p>{{ $venta->branch->name ?? 'Monny' }}</p></div>
        <div>Sistema de ventas</div>
    </div>

    <div class="line"></div>

    
    <div class="row"><span>Fecha:</span><strong>{{ $venta->date_time }}</strong></div>
    <div class="row">
        <span>Cliente:</span>
        <strong>{{ $venta->customer->name_customer ?? 'Sin cliente' }}</strong>
    </div>

    <div class="line"></div>

    <div><strong>Productos:</strong></div>

    @forelse($venta->items ?? [] as $item)
        <div class="product">

            {{-- 🔥 Nombre del producto con fallback seguro --}}
            <div class="product-name">
    {{ $item->item_name }}
</div>

            <div class="row">
                <span>
                    {{ $item->amount ?? 0 }} x ${{ number_format($item->unit_price ?? 0, 2) }}
                </span>
                <strong>
                    ${{ number_format($item->total_line ?? 0, 2) }}
                </strong>
            </div>

        </div>
    @empty
        <p style="text-align:center; color:gray;">No hay productos en esta venta</p>
    @endforelse

    <div class="line"></div>

    @php
    $subtotal = $venta->subtotal ?? 0;
    $total = $venta->total ?? 0;
    $iva = $total - $subtotal;
@endphp

<div class="row">
    <span>Subtotal:</span>
    <strong>${{ number_format($subtotal, 2) }}</strong>
</div>

<div class="row">
    <span>IVA:</span>
    <strong>${{ number_format($iva, 2) }}</strong>
</div>

<div class="row total">
    <span>Total:</span>
    <strong>${{ number_format($total, 2) }}</strong>
</div>



    <div class="line"></div>

    <div class="footer">
        ¡Gracias por tu compra!<br>
        Vuelve pronto 😊
    </div>

    <button class="btn-print" onclick="window.print()">Imprimir</button>

</div>

</body>
</html>