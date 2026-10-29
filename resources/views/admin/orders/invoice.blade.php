<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $order->order_number }} | {{ $settings['restaurant_name'] ?? 'My 2nd Wife' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
        }
    </style>
</head>
<body class="bg-gray-100 p-4 sm:p-10 font-sans text-gray-800">

    <div class="max-w-3xl mx-auto mb-4 no-print flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.orders.show', $order) }}" class="text-xs font-bold text-gray-500 hover:text-[#F50070]">
            ← Back to Order
        </a>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.orders.receipt', $order) }}" 
               class="px-4 py-2 rounded-full bg-white border border-gray-300 text-gray-700 hover:text-[#F50070] hover:border-[#F50070] text-xs font-bold transition-colors">
                🧾 Thermal Receipt (58/80mm)
            </a>
            <button onclick="window.print()" class="px-5 py-2 rounded-full bg-[#F50070] text-white text-xs font-bold flex items-center gap-1.5 shadow-md hover:bg-[#D80063]">
                <span>Print A4 Invoice</span>
            </button>
        </div>
    </div>

    <!-- Printable Invoice Sheet -->
    <div class="max-w-3xl mx-auto bg-white p-8 sm:p-12 rounded-3xl border border-gray-200 shadow-lg space-y-8 print:border-none print:shadow-none print:p-0">
        
        <!-- Header -->
        <div class="flex items-start justify-between border-b border-gray-100 pb-8">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-xl bg-[#F50070] flex items-center justify-center font-black text-white text-sm">
                        2W
                    </div>
                    <span class="text-xl font-black text-gray-900">{{ $settings['restaurant_name'] ?? 'My 2nd Wife' }}</span>
                </div>
                <p class="text-xs text-gray-500 max-w-xs">{{ $settings['restaurant_address'] ?? 'Park Street, Kolkata' }}</p>
                <p class="text-xs text-gray-500">Phone: {{ $settings['restaurant_phone'] ?? '+91 98300 12345' }}</p>
                <p class="text-[11px] text-gray-400 font-mono">GSTIN: 19AAACM1234F1Z8</p>
            </div>

            <div class="text-right space-y-1">
                <span class="inline-block px-3 py-1 rounded-full text-xs font-black uppercase bg-pink-50 text-[#F50070] border border-pink-200">
                    TAX INVOICE
                </span>
                <h2 class="text-lg font-black text-gray-900 mt-1">#{{ $order->order_number }}</h2>
                <p class="text-xs text-gray-500">Date: {{ $order->created_at->format('d M Y, h:i A') }}</p>
                <p class="text-xs font-bold text-gray-700">Timing: {{ $order->order_schedule === 'scheduled' ? ('Scheduled (' . ($order->scheduled_at ? $order->scheduled_at->format('d M, h:i A') : 'Future') . ')') : 'Instant Delivery' }}</p>
                <p class="text-xs font-bold uppercase {{ $order->isPaid() ? 'text-emerald-600' : 'text-amber-600' }}">
                    Status: {{ $order->isPaid() ? 'PAID' : 'PAYMENT PENDING' }}
                </p>
            </div>
        </div>

        <!-- Bill To -->
        <div class="grid grid-cols-2 gap-6 text-xs">
            <div>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Customer Details</span>
                <p class="font-extrabold text-sm text-gray-900">{{ $order->customer_name }}</p>
                <p class="text-gray-500">{{ $order->customer_phone }}</p>
                @if($order->customer_email) <p class="text-gray-500">{{ $order->customer_email }}</p> @endif
            </div>

            <div>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Delivery Destination</span>
                <p class="text-gray-700 leading-relaxed">{{ $order->delivery_address_text ?? 'Counter Pickup' }}</p>
                @if($order->delivery_latitude && $order->delivery_longitude)
                    <p class="text-[10px] font-mono text-gray-500 mt-0.5">GPS: {{ number_format($order->delivery_latitude, 5) }}, {{ number_format($order->delivery_longitude, 5) }}</p>
                @endif
                @if($order->delivery_instructions)
                    <p class="text-[11px] text-amber-700 italic mt-1">Note: {{ $order->delivery_instructions }}</p>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="border-y border-gray-200 text-gray-400 font-bold uppercase tracking-wider text-[10px]">
                    <th class="py-3 px-1">Item Description</th>
                    <th class="py-3 px-1 text-center">Qty</th>
                    <th class="py-3 px-1 text-right">Unit Price</th>
                    <th class="py-3 px-1 text-right">Total Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($order->items as $item)
                <tr>
                    <td class="py-3 px-1">
                        <span class="font-bold text-gray-900 text-sm block">{{ $item->product_name }}</span>
                        @if($item->variation_name)
                            <span class="text-gray-500 text-[11px]">Size: {{ $item->variation_name }}</span>
                        @endif
                        @if($item->addons->count() > 0)
                            <span class="text-[11px] text-gray-500 block">Extras: {{ $item->addons->pluck('addon_name')->implode(', ') }}</span>
                        @endif
                    </td>
                    <td class="py-3 px-1 text-center font-bold">{{ $item->quantity }}</td>
                    <td class="py-3 px-1 text-right font-mono">₹{{ number_format($item->unit_price, 2) }}</td>
                    <td class="py-3 px-1 text-right font-mono font-bold text-gray-900">₹{{ number_format($item->line_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals Calculation -->
        <div class="border-t border-gray-200 pt-4 flex justify-end">
            <div class="w-64 space-y-2 text-xs text-gray-600">
                <div class="flex justify-between">
                    <span>Items Subtotal:</span>
                    <span class="font-bold text-gray-900">₹{{ number_format($order->subtotal, 2) }}</span>
                </div>

                @if($order->discount_amount > 0)
                <div class="flex justify-between text-emerald-600 font-bold">
                    <span>Discount:</span>
                    <span>-₹{{ number_format($order->discount_amount, 2) }}</span>
                </div>
                @endif

                <div class="flex justify-between">
                    <span>Delivery Charge:</span>
                    <span>₹{{ number_format($order->delivery_charge, 2) }}</span>
                </div>

                <div class="flex justify-between">
                    <span>Packaging Charge:</span>
                    <span>₹{{ number_format($order->packaging_charge, 2) }}</span>
                </div>

                <div class="flex justify-between">
                    <span>CGST (2.5%):</span>
                    <span>₹{{ number_format($order->tax_amount / 2, 2) }}</span>
                </div>

                <div class="flex justify-between">
                    <span>SGST (2.5%):</span>
                    <span>₹{{ number_format($order->tax_amount / 2, 2) }}</span>
                </div>

                <div class="pt-2 border-t border-gray-200 flex justify-between items-baseline font-black text-gray-900 text-sm">
                    <span>Total Amount:</span>
                    <span class="text-xl text-[#F50070]">₹{{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-100 pt-6 text-center text-xs text-gray-400 space-y-1">
            <p class="font-bold text-gray-700">Thank you for ordering with {{ $settings['restaurant_name'] ?? 'My 2nd Wife' }}!</p>
            <p>For order queries, contact us at {{ $settings['restaurant_phone'] ?? '+91 98300 12345' }} or {{ $settings['restaurant_email'] ?? 'hello@my2ndwife.com' }}</p>
        </div>
    </div>

</body>
</html>
