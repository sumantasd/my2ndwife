<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class EnvSyncService
{
    /**
     * Allowed environment variable keys that can be synchronized.
     */
    protected const ALLOWED_KEYS = [
        'RAZORPAY_KEY_ID',
        'RAZORPAY_KEY_SECRET',
        'RAZORPAY_WEBHOOK_SECRET',
        'GOOGLE_MAPS_API_KEY',
        'FIREBASE_PROJECT_ID',
        'FIREBASE_CLIENT_EMAIL',
        'FIREBASE_CREDENTIALS_PATH',
    ];

    /**
     * Safely synchronize allowed environment variables into the .env file.
     *
     * @param array<string, string|null> $variables Key-value pairs to sync
     * @return array{success: bool, synced: array<string, string>, message: string}
     */
    public function sync(array $variables): array
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            Log::warning("EnvSyncService: .env file does not exist at {$envPath}");
            return [
                'success' => false,
                'synced' => [],
                'message' => '.env file does not exist on the server.',
            ];
        }

        if (!is_writable($envPath)) {
            Log::warning("EnvSyncService: .env file is not writable at {$envPath}");
            return [
                'success' => false,
                'synced' => [],
                'message' => '.env file is not writable. File permissions prevent direct synchronization.',
            ];
        }

        // Filter only allowed keys
        $validUpdates = [];
        foreach ($variables as $key => $value) {
            $key = strtoupper(trim($key));
            if (in_array($key, self::ALLOWED_KEYS, true)) {
                $validUpdates[$key] = $value !== null ? trim((string) $value) : '';
            }
        }

        if (empty($validUpdates)) {
            return [
                'success' => true,
                'synced' => [],
                'message' => 'No relevant environment variables required synchronization.',
            ];
        }

        try {
            $content = file_get_contents($envPath);
            if ($content === false) {
                throw new Exception('Unable to read .env file.');
            }

            foreach ($validUpdates as $key => $value) {
                $formattedValue = $this->formatEnvValue($value);
                $pattern = "/^{$key}=.*$/m";

                if (preg_match($pattern, $content)) {
                    $content = preg_replace($pattern, "{$key}={$formattedValue}", $content);
                } else {
                    // Append under appropriate section or at bottom
                    $content = rtrim($content) . "\n{$key}={$formattedValue}\n";
                }

                // Update runtime config and environment
                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }

            // Sync Laravel runtime config keys
            if (isset($validUpdates['RAZORPAY_KEY_ID'])) {
                config(['services.razorpay.key' => $validUpdates['RAZORPAY_KEY_ID']]);
            }
            if (isset($validUpdates['RAZORPAY_KEY_SECRET'])) {
                config(['services.razorpay.secret' => $validUpdates['RAZORPAY_KEY_SECRET']]);
            }
            if (isset($validUpdates['RAZORPAY_WEBHOOK_SECRET'])) {
                config(['services.razorpay.webhook_secret' => $validUpdates['RAZORPAY_WEBHOOK_SECRET']]);
            }
            if (isset($validUpdates['GOOGLE_MAPS_API_KEY'])) {
                config(['services.google_maps.key' => $validUpdates['GOOGLE_MAPS_API_KEY']]);
            }

            // Atomic file write using temporary file and lock
            $tempFile = base_path('.env.tmp.' . uniqid('', true));
            $fp = fopen($tempFile, 'w');
            if (!$fp) {
                throw new Exception('Unable to create temporary file for atomic .env update.');
            }

            if (!flock($fp, LOCK_EX)) {
                fclose($fp);
                @unlink($tempFile);
                throw new Exception('Unable to acquire exclusive lock on temporary .env file.');
            }

            fwrite($fp, $content);
            fflush($fp);
            flock($fp, LOCK_UN);
            fclose($fp);

            // Copy permissions from original .env
            $perms = fileperms($envPath);
            if ($perms !== false) {
                @chmod($tempFile, $perms);
            }

            // Atomic rename
            if (!rename($tempFile, $envPath)) {
                // Fallback for Windows if rename fails while open
                if (!copy($tempFile, $envPath)) {
                    @unlink($tempFile);
                    throw new Exception('Failed to replace .env with synchronized content.');
                }
                @unlink($tempFile);
            }

            // Clear cached config if cached
            if (app()->configurationIsCached()) {
                Artisan::call('config:clear');
            }

            return [
                'success' => true,
                'synced' => array_keys($validUpdates),
                'message' => 'Environment configuration synchronized successfully.',
            ];
        } catch (Exception $e) {
            Log::error('EnvSyncService synchronization error: ' . $e->getMessage());
            return [
                'success' => false,
                'synced' => [],
                'message' => 'Environment synchronization failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Format environment variable value, quoting if needed.
     */
    protected function formatEnvValue(string $value): string
    {
        if ($value === '') {
            return '';
        }

        // If contains spaces, quotes, or special characters, wrap in quotes
        if (preg_match('/\s|[#$"\'\\\\]/', $value)) {
            $escaped = str_replace('"', '\"', $value);
            return "\"{$escaped}\"";
        }

        return $value;
    }
}
