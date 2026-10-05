<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Packing slip {{ $order->order_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111; margin: 32px; }
        h1 { font-size: 20px; margin: 0 0 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th, td { border-bottom: 1px solid #ccc; padding: 8px; text-align: left; }
        .meta { color: #555; font-size: 13px; }
        .to { margin-top: 20px; font-size: 15px; line-height: 1.5; }
        @media print { .noprint { display: none; } }
    </style>
</head>
<body>
    <p class="noprint"><button onclick="window.print()">Print</button></p>
    <h1>{{ config('app.name') }} - Packing slip</h1>
    <div class="meta">Order {{ $order->order_number }} &middot; {{ $order->created_at->format('d M Y') }}</div>

    <div class="to">
        <strong>Ship to</strong><br>
        {{ $order->customer_name }}<br>
        {{ $order->address_line1 }}<br>
        @if($order->address_line2){{ $order->address_line2 }}<br>@endif
        {{ $order->city }}@if($order->state), {{ $order->state }}@endif {{ $order->postal_code }}<br>
        {{ $order->address_country }}<br>
        Phone: {{ $order->customer_phone }}
    </div>

    <table>
        <thead><tr><th>SKU</th><th>Item</th><th>Qty</th></tr></thead>
        <tbody>
            @foreach($order->items as $item)
                <tr><td>{{ $item->sku }}</td><td>{{ $item->name }}</td><td>{{ $item->quantity }}</td></tr>
            @endforeach
        </tbody>
    </table>

    @if($order->notes)<p><strong>Customer note:</strong> {{ $order->notes }}</p>@endif
</body>
</html>
