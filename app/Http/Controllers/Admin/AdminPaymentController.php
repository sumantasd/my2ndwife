<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminPaymentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Payment::with(['order'])->latest();

        if ($request->filled('method') && $request->method !== 'all') {
            $query->where('payment_method', $request->method);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                    ->orWhere('gateway_order_id', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($oq) use ($search) {
                        $oq->where('order_number', 'like', "%{$search}%")
                            ->orWhere('customer_name', 'like', "%{$search}%");
                    });
            });
        }

        $payments = $query->paginate(20)->withQueryString();

        $metrics = [
            'total_collected' => Payment::where('status', Payment::STATUS_COMPLETED)->sum('amount'),
            'pending_cod' => Payment::where('payment_method', 'cash_on_delivery')->where('status', Payment::STATUS_PENDING)->sum('amount'),
            'online_success' => Payment::where('payment_method', 'razorpay')->where('status', Payment::STATUS_COMPLETED)->count(),
            'failed' => Payment::where('status', Payment::STATUS_FAILED)->count(),
        ];

        return view('admin.payments.index', compact('payments', 'metrics'));
    }

    public function markAsPaid(Request $request, Payment $payment): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => ['required', 'string', 'max:255'],
        ]);

        $payment->update([
            'status' => Payment::STATUS_COMPLETED,
            'paid_at' => now(),
            'gateway_response' => array_merge($payment->gateway_response ?? [], [
                'admin_verification' => true,
                'verified_by_user_id' => Auth::id(),
                'admin_notes' => $validated['notes'],
                'verified_at' => now()->toIso8601String(),
            ]),
        ]);

        if ($payment->order && $payment->order->order_status === \App\Models\Order::STATUS_PENDING_PAYMENT) {
            $payment->order->update(['order_status' => \App\Models\Order::STATUS_PLACED]);
            \App\Models\OrderStatusHistory::create([
                'order_id' => $payment->order->id,
                'from_status' => \App\Models\Order::STATUS_PENDING_PAYMENT,
                'to_status' => \App\Models\Order::STATUS_PLACED,
                'comment' => "Payment verified manually by administrator (" . (Auth::user()?->name ?? 'Admin') . "): {$validated['notes']}",
                'changed_by_user_id' => Auth::id(),
                'created_at' => now(),
            ]);
        }

        AuditLog::record(
            action: 'payment_manually_verified',
            targetType: 'Payment',
            targetId: $payment->id,
            details: [
                'order_number' => $payment->order?->order_number,
                'amount' => $payment->amount,
                'notes' => $validated['notes'],
            ]
        );

        return back()->with('success', "Payment for order #{$payment->order?->order_number} marked as completed.");
    }
}
