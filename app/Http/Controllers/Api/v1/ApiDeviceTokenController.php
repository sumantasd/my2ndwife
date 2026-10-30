<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiDeviceTokenController extends Controller
{
    /**
     * Register or update a device FCM token for authenticated customer or delivery rider
     */
    public function register(Request $request): JsonResponse
    {
        // Support common mobile payload parameter aliases (token, fcm_token, device_token)
        $rawToken = $request->input('token') ?? $request->input('fcm_token') ?? $request->input('device_token');
        if ($rawToken) {
            $request->merge(['token' => trim((string) $rawToken)]);
        }
        if ($request->has('device_type')) {
            $request->merge(['device_type' => strtolower(trim((string) $request->input('device_type')))]);
        }

        $validated = $request->validate([
            'token' => ['required', 'string', 'max:500'],
            'device_type' => ['nullable', 'string', 'in:android,ios,web'],
            'device_model' => ['nullable', 'string', 'max:100'],
            'app_version' => ['nullable', 'string', 'max:30'],
        ]);

        $user = $request->user();

        // Disassociate token from any other user to prevent cross-account push deliveries
        DeviceToken::where('token', $validated['token'])
            ->where('user_id', '!=', $user->id)
            ->delete();

        // Register or reactivate token for the current authenticated user
        $deviceToken = DeviceToken::updateOrCreate(
            [
                'user_id' => $user->id,
                'token' => $validated['token'],
            ],
            [
                'device_type' => $validated['device_type'] ?? 'android',
                'device_model' => $validated['device_model'] ?? null,
                'app_version' => $validated['app_version'] ?? null,
                'is_active' => true,
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Device FCM token registered successfully.',
            'device' => [
                'id' => $deviceToken->id,
                'device_type' => $deviceToken->device_type,
                'is_active' => $deviceToken->is_active,
            ],
        ]);
    }

    /**
     * Remove or invalidate a device token on logout
     */
    public function remove(Request $request): JsonResponse
    {
        $rawToken = $request->input('token') ?? $request->input('fcm_token') ?? $request->input('device_token');
        if ($rawToken) {
            $request->merge(['token' => trim((string) $rawToken)]);
        }

        $validated = $request->validate([
            'token' => ['required', 'string'],
        ]);

        $user = $request->user();

        // Enforce ownership: only allow removing token belonging to this authenticated user
        $affected = DeviceToken::where('user_id', $user->id)
            ->where('token', $validated['token'])
            ->delete();

        return response()->json([
            'success' => true,
            'message' => $affected > 0 ? 'Device token removed successfully.' : 'Token not found or already removed.',
        ]);
    }

    /**
     * List registered devices for the current authenticated user
     */
    public function myTokens(Request $request): JsonResponse
    {
        $user = $request->user();

        $tokens = $user->deviceTokens()
            ->select(['id', 'device_type', 'device_model', 'app_version', 'is_active', 'last_used_at', 'created_at'])
            ->get();

        return response()->json([
            'success' => true,
            'devices' => $tokens,
        ]);
    }
}
