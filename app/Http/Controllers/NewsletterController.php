<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email:rfc,filter', 'max:150'],
        ]);

        $email = strtolower(trim($validated['email']));

        $existing = NewsletterSubscriber::where('email', $email)->first();

        if ($existing) {
            $msg = 'You are already subscribed to our newsletter with this email!';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'already_subscribed' => true,
                    'message' => $msg,
                ]);
            }
            return back()->with('info', $msg);
        }

        NewsletterSubscriber::create([
            'email' => $email,
            'ip_address' => $request->ip(),
            'is_active' => true,
        ]);

        $successMsg = 'Thank you for subscribing to My 2nd Wife updates and culinary deals!';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'already_subscribed' => false,
                'message' => $successMsg,
            ]);
        }

        return back()->with('success', $successMsg);
    }
}
