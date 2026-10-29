@php
    $paperWidth = request('width', $settings['receipt_paper_width'] ?? '80mm');
    if (!in_array($paperWidth, ['58mm', '80mm'])) {
        $paperWidth = '80mm';
    }

    $showLogo = $settings['receipt_show_logo'] ?? true;
    $logoSourceKey = $settings['receipt_logo_source'] ?? 'restaurant_logo';
    $logoPath = $settings[$logoSourceKey] ?? ($settings['restaurant_logo'] ?? null);

    $showRestName = $settings['receipt_show_restaurant_name'] ?? true;
    $showAddress = $settings['receipt_show_address'] ?? true;
    $showPhone = $settings['receipt_show_phone'] ?? true;
    $showOrderNumber = $settings['receipt_show_order_number'] ?? true;
    $showOrderDate = $settings['receipt_show_order_date'] ?? true;
    $showCustomerName = $settings['receipt_show_customer_name'] ?? true;
    $showCustomerPhone = $settings['receipt_show_customer_phone'] ?? true;
    $showDeliveryAddress = $settings['receipt_show_delivery_address'] ?? true;
    $showUnitPrices = $settings['receipt_show_unit_prices'] ?? true;
    $showLineTotals = $settings['receipt_show_line_totals'] ?? true;
    $showSubtotal = $settings['receipt_show_subtotal'] ?? true;
    $showDiscounts = $settings['receipt_show_discounts'] ?? true;
    $showDeliveryCharge = $settings['receipt_show_delivery_charge'] ?? true;
    $showPackagingCharge = $settings['receipt_show_packaging_charge'] ?? true;
    $showTax = $settings['receipt_show_tax'] ?? true;
    $showGrandTotal = $settings['receipt_show_grand_total'] ?? true;
    $showPaymentDetails = $settings['receipt_show_payment_details'] ?? true;
    $showNotes = $settings['receipt_show_notes'] ?? true;
    $showFooter = $settings['receipt_show_footer_message'] ?? true;
    $footerMessage = $settings['receipt_footer_message'] ?? "Thank you for ordering with My 2nd Wife!\nPlease visit us again.";
    $copies = max(1, (int) ($settings['receipt_copies'] ?? 1));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Receipt #{{ $order->order_number }} | {{ $paperWidth }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        /* Base Thermal Styling */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Courier New', Courier, monospace, system-ui, -apple-system, sans-serif;
            color: #000;
            background-color: #f3f4f6;
            line-height: 1.25;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .screen-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 24px 16px;
            min-height: 100vh;
        }

        .toolbar {
            width: 100%;
            max-width: 600px;
            background: #ffffff;
            border-radius: 16px;
            padding: 14px 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            border: 1px solid #e5e7eb;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            font-family: system-ui, -apple-system, sans-serif;
        }

        .toolbar a, .toolbar button {
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            border-radius: 9999px;
            padding: 8px 16px;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }

        .btn-back {
            color: #4b5563;
            background: #f3f4f6;
        }
        .btn-back:hover {
            background: #e5e7eb;
            color: #111827;
        }

        .btn-print {
            color: #ffffff;
            background: #f50070;
            box-shadow: 0 4px 10px rgba(245, 0, 112, 0.25);
        }
        .btn-print:hover {
            background: #d80063;
        }

        .width-selector {
            display: flex;
            align-items: center;
            background: #f3f4f6;
            border-radius: 9999px;
            padding: 3px;
            gap: 4px;
        }
        .width-btn {
            font-size: 11px;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 9999px;
            border: none;
            cursor: pointer;
            background: transparent;
            color: #6b7280;
            text-decoration: none;
        }
        .width-btn.active {
            background: #25232a;
            color: #ffffff;
        }

        .instructions-note {
            max-width: 600px;
            width: 100%;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 12px;
            padding: 10px 14px;
            font-size: 11px;
            color: #1e40af;
            font-family: system-ui, sans-serif;
            margin-bottom: 20px;
            line-height: 1.4;
        }

        /* Thermal Sheet container */
        .thermal-receipt-card {
            background: #ffffff;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-radius: 4px;
            border: 1px solid #d1d5db;
            padding: {{ $paperWidth === '58mm' ? '12px 8px' : '18px 14px' }};
            width: {{ $paperWidth === '58mm' ? '58mm' : '80mm' }};
            max-width: 100%;
            font-size: {{ $paperWidth === '58mm' ? '11px' : '12px' }};
            color: #000000;
        }

        /* Receipt Internals */
        .receipt-header {
            text-align: center;
            margin-bottom: 8px;
        }
        .receipt-logo {
            max-height: 44px;
            max-width: 120px;
            margin: 0 auto 6px auto;
            display: block;
            filter: grayscale(100%) contrast(200%);
        }
        .receipt-title {
            font-size: {{ $paperWidth === '58mm' ? '14px' : '16px' }};
            font-weight: 900;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .receipt-sub {
            font-size: {{ $paperWidth === '58mm' ? '9px' : '10px' }};
            color: #111;
            margin-bottom: 2px;
            word-break: break-word;
        }

        .divider {
            border: none;
            border-top: 1px dashed #000;
            margin: 6px 0;
        }
        .divider-double {
            border: none;
            border-top: 2px solid #000;
            margin: 8px 0;
        }

        .meta-grid {
            font-size: {{ $paperWidth === '58mm' ? '10px' : '11px' }};
            margin-bottom: 6px;
        }
        .meta-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }
        .meta-label {
            font-weight: 600;
        }
        .meta-val {
            font-weight: 800;
            text-align: right;
        }

        .order-badge-row {
            text-align: center;
            margin: 4px 0;
            font-weight: 900;
            font-size: {{ $paperWidth === '58mm' ? '12px' : '13px' }};
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 3px 0;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
        }

        /* Items Section */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0;
            font-size: {{ $paperWidth === '58mm' ? '10px' : '11px' }};
        }
        .items-table th {
            text-transform: uppercase;
            font-weight: 800;
            border-bottom: 1px dashed #000;
            padding: 3px 0;
            font-size: 9px;
        }
        .items-table td {
            padding: 3px 0;
            vertical-align: top;
        }
        .col-item {
            text-align: left;
            word-break: break-word;
        }
        .col-qty {
            text-align: center;
            width: 24px;
            font-weight: bold;
        }
        .col-rate {
            text-align: right;
            width: 44px;
        }
        .col-total {
            text-align: right;
            width: 48px;
            font-weight: bold;
        }
        .item-variation {
            font-size: 9px;
            display: block;
            color: #222;
        }
        .item-addon {
            font-size: 8px;
            display: block;
            color: #333;
        }
        .item-note {
            font-size: 8px;
            font-style: italic;
            display: block;
        }

        /* Totals */
        .totals-block {
            font-size: {{ $paperWidth === '58mm' ? '10px' : '11px' }};
            margin-top: 4px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }
        .grand-total-row {
            display: flex;
            justify-content: space-between;
            font-size: {{ $paperWidth === '58mm' ? '13px' : '15px' }};
            font-weight: 900;
            margin: 5px 0;
            padding: 4px 0;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
        }

        /* Payment & Instructions */
        .info-box {
            font-size: {{ $paperWidth === '58mm' ? '9px' : '10px' }};
            margin: 6px 0;
            padding: 4px;
            border: 1px solid #000;
            border-radius: 2px;
        }

        .receipt-footer {
            text-align: center;
            margin-top: 10px;
            font-size: 9px;
            white-space: pre-line;
        }

        /* Print Media Specific Overrides */
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                color: #000000 !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .screen-container {
                padding: 0 !important;
                min-height: 0 !important;
            }
            .thermal-receipt-card {
                border: none !important;
                box-shadow: none !important;
                padding: {{ $paperWidth === '58mm' ? '3mm 1mm' : '4mm 2mm' }} !important;
                width: {{ $paperWidth === '58mm' ? '58mm' : '80mm' }} !important;
                max-width: 100% !important;
                page-break-inside: avoid;
            }
            @page {
                size: {{ $paperWidth }} auto;
                margin: 0mm;
            }
        }
    </style>
</head>
<body>

<div class="screen-container">

    <!-- Screen Navigation & Controls (Hidden on print) -->
    <div class="toolbar no-print">
        <a href="{{ route('admin.orders.show', $order->id ?? 1) }}" class="btn-back">
            ← Back to Order
        </a>

        <div style="display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 11px; font-weight: 700; color: #6b7280;">Paper Width:</span>
            <div class="width-selector">
                <a href="?width=58mm" class="width-btn {{ $paperWidth === '58mm' ? 'active' : '' }}">58mm</a>
                <a href="?width=80mm" class="width-btn {{ $paperWidth === '80mm' ? 'active' : '' }}">80mm</a>
            </div>

            <button onclick="window.print()" class="btn-print">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                <span>Print Receipt</span>
            </button>
        </div>
    </div>

    <div class="instructions-note no-print">
        <strong>🖨️ Thermal Printing Tip:</strong> In your browser print dialog, set <em>Margins: None</em>, ensure <em>Headers and Footers: Off</em>, and select your installed <strong>{{ $paperWidth }}</strong> thermal receipt printer. Standard ESC/POS printers (Epson, TVS, Rongta, etc.) print directly via standard drivers.
    </div>

    @for($copyIndex = 1; $copyIndex <= $copies; $copyIndex++)
    <!-- Thermal Receipt Body -->
    <div class="thermal-receipt-card">
        
        <!-- Header -->
        <div class="receipt-header">
            @if($showLogo && !empty($logoPath))
                <img src="{{ asset($logoPath) }}" alt="Logo" class="receipt-logo">
            @endif

            @if($showRestName)
                <h1 class="receipt-title">{{ $settings['restaurant_name'] ?? 'My 2nd Wife' }}</h1>
            @endif

            @if($showAddress && !empty($settings['restaurant_address']))
                <p class="receipt-sub">{{ $settings['restaurant_address'] }}</p>
                @if(!empty($settings['restaurant_city']))
                    <p class="receipt-sub">{{ $settings['restaurant_city'] }} {{ $settings['restaurant_pincode'] ?? '' }}</p>
                @endif
            @endif

            @if($showPhone && !empty($settings['restaurant_phone']))
                <p class="receipt-sub">Tel: {{ $settings['restaurant_phone'] }}</p>
            @endif

            @if(!empty($settings['fssai_license']))
                <p class="receipt-sub">FSSAI: {{ $settings['fssai_license'] }}</p>
            @endif
            @if(!empty($settings['gstin']))
                <p class="receipt-sub">GSTIN: {{ $settings['gstin'] }}</p>
            @endif
        </div>

        <!-- Order Type Banner -->
        <div class="order-badge-row">
            *** {{ $order->isTakeaway() ? 'TAKEAWAY / PICKUP' : 'HOME DELIVERY' }} ***
        </div>

        <!-- Metadata -->
        <div class="meta-grid">
            @if($showOrderNumber)
            <div class="meta-row">
                <span class="meta-label">ORDER NO:</span>
                <span class="meta-val">#{{ $order->order_number }}</span>
            </div>
            @endif

            @if($showOrderDate)
            <div class="meta-row">
                <span class="meta-label">DATE/TIME:</span>
                <span class="meta-val">{{ $order->created_at ? $order->created_at->format('d/m/Y h:i A') : date('d/m/Y h:i A') }}</span>
            </div>
            @endif

            <div class="meta-row">
                <span class="meta-label">TIMING:</span>
                <span class="meta-val">{{ $order->order_schedule === 'scheduled' ? ('SCHEDULED (' . ($order->scheduled_at ? $order->scheduled_at->format('d/m h:i A') : 'LATER') . ')') : 'INSTANT' }}</span>
            </div>

            @if($showCustomerName)
            <div class="meta-row">
                <span class="meta-label">CUSTOMER:</span>
                <span class="meta-val">{{ $order->customer_name }}</span>
            </div>
            @endif

            @if($showCustomerPhone && $order->customer_phone)
            <div class="meta-row">
                <span class="meta-label">PHONE:</span>
                <span class="meta-val">{{ $order->customer_phone }}</span>
            </div>
            @endif

            @if($order->isDelivery() && $showDeliveryAddress && !empty($order->delivery_address_text))
            <div style="margin-top: 3px;">
                <span class="meta-label" style="display:block;">DELIVERY ADDRESS:</span>
                <span style="font-size: 10px; display:block; word-break: break-word;">{{ $order->delivery_address_text }}</span>
                @if($order->delivery_latitude && $order->delivery_longitude)
                <span style="font-size: 9px; display:block; color: #444;">GPS: {{ number_format($order->delivery_latitude, 4) }}, {{ number_format($order->delivery_longitude, 4) }}</span>
                @endif
            </div>
            @endif

            @if($order->isDelivery() && $order->deliveryPerson)
            <div class="meta-row" style="margin-top: 3px;">
                <span class="meta-label">RIDER:</span>
                <span class="meta-val">{{ $order->deliveryPerson->name }}</span>
            </div>
            @endif
        </div>

        <hr class="divider">

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th class="col-item">ITEM</th>
                    <th class="col-qty">QTY</th>
                    @if($showUnitPrices)
                    <th class="col-rate">RATE</th>
                    @endif
                    @if($showLineTotals)
                    <th class="col-total">AMT</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($order->items as $item)
                <tr>
                    <td class="col-item">
                        <strong>{{ $item->product_name }}</strong>
                        @if($item->variation_name)
                            <span class="item-variation">({{ $item->variation_name }})</span>
                        @endif
                        @if($item->addons && $item->addons->count() > 0)
                            <span class="item-addon">+ {{ $item->addons->pluck('addon_name')->implode(', ') }}</span>
                        @endif
                        @if($showNotes && $item->special_instructions)
                            <span class="item-note">Note: {{ $item->special_instructions }}</span>
                        @endif
                    </td>
                    <td class="col-qty">{{ $item->quantity }}</td>
                    @if($showUnitPrices)
                    <td class="col-rate">₹{{ number_format($item->unit_price, 2) }}</td>
                    @endif
                    @if($showLineTotals)
                    <td class="col-total">₹{{ number_format($item->line_total, 2) }}</td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding: 6px;">No items in order</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <hr class="divider">

        <!-- Totals Breakdown -->
        <div class="totals-block">
            @if($showSubtotal)
            <div class="total-row">
                <span>SUBTOTAL:</span>
                <span>₹{{ number_format($order->subtotal, 2) }}</span>
            </div>
            @endif

            @if($showDiscounts && $order->discount_amount > 0)
            <div class="total-row">
                <span>DISCOUNT {{ $order->coupon_code ? "({$order->coupon_code})" : '' }}:</span>
                <span>-₹{{ number_format($order->discount_amount, 2) }}</span>
            </div>
            @endif

            @if($order->isDelivery() && $showDeliveryCharge && $order->delivery_charge > 0)
            <div class="total-row">
                <span>DELIVERY CHARGE:</span>
                <span>₹{{ number_format($order->delivery_charge, 2) }}</span>
            </div>
            @endif

            @if($showPackagingCharge && $order->packaging_charge > 0)
            <div class="total-row">
                <span>PACKAGING CHARGE:</span>
                <span>₹{{ number_format($order->packaging_charge, 2) }}</span>
            </div>
            @endif

            @if($showTax && $order->tax_amount > 0)
            <div class="total-row">
                <span>TAX / GST:</span>
                <span>₹{{ number_format($order->tax_amount, 2) }}</span>
            </div>
            @endif

            @if($showGrandTotal)
            <div class="grand-total-row">
                <span>TOTAL:</span>
                <span>₹{{ number_format($order->total_amount, 2) }}</span>
            </div>
            @endif
        </div>

        <!-- Payment & Instructions -->
        @if($showPaymentDetails)
        <div class="info-box">
            <div class="meta-row">
                <span class="meta-label">PAYMENT:</span>
                <span class="meta-val" style="text-transform: uppercase;">
                    {{ str_replace('_', ' ', $order->payment?->payment_method ?? 'Cash on Delivery') }}
                </span>
            </div>
            <div class="meta-row">
                <span class="meta-label">STATUS:</span>
                <span class="meta-val" style="text-transform: uppercase;">
                    {{ $order->isPaid() ? 'PAID' : 'PAYMENT DUE' }}
                </span>
            </div>
        </div>
        @endif

        @if($showNotes)
            @if(!empty($order->delivery_instructions))
            <div style="font-size: 9px; margin-top: 4px; padding: 3px; border: 1px dashed #000;">
                <strong>DELIVERY NOTE:</strong> {{ $order->delivery_instructions }}
            </div>
            @endif

            @if(!empty($order->internal_notes))
            <div style="font-size: 9px; margin-top: 4px; padding: 3px; border: 1px dashed #000;">
                <strong>KITCHEN NOTE:</strong> {{ $order->internal_notes }}
            </div>
            @endif
        @endif

        @if($showFooter && !empty($footerMessage))
        <div class="receipt-footer">
            {{ $footerMessage }}
        </div>
        @endif

        <div style="text-align: center; margin-top: 6px; font-size: 8px;">
            Printed: {{ date('d-m-Y H:i:s') }}
            @if($copies > 1)
                (Copy {{ $copyIndex }} of {{ $copies }})
            @endif
        </div>

    </div>
    @if($copyIndex < $copies)
        <div style="page-break-after: always; height: 16px;"></div>
    @endif
    @endfor

</div>

</body>
</html>
