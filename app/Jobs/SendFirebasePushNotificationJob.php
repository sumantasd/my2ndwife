<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\FirebaseService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendFirebasePushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 60, 180];

    /**
     * Create a new job instance.
     *
     * @param string $mode 'user', 'role', or 'token'
     * @param mixed $target User ID (int), Role (string), or Token (string)
     * @param string $eventType
     * @param array $placeholders
     * @param array $extraData
     */
    public function __construct(
        public string $mode,
        public mixed $target,
        public string $eventType,
        public array $placeholders = [],
        public array $extraData = []
    ) {
        $this->afterCommit = true;
    }

    /**
     * Execute the job.
     */
    public function handle(FirebaseService $firebaseService): void
    {
        try {
            if ($this->mode === 'user') {
                $user = User::find($this->target);
                if ($user) {
                    $firebaseService->sendToUser($user, $this->eventType, $this->placeholders, $this->extraData);
                }
            } elseif ($this->mode === 'role') {
                $firebaseService->sendToRole($this->target, $this->eventType, $this->placeholders, $this->extraData);
            } elseif ($this->mode === 'token') {
                $template = $firebaseService->resolveTemplate($this->eventType, $this->placeholders);
                $res = $firebaseService->sendToToken(
                    $this->target,
                    $template['title'],
                    $template['body'],
                    $this->extraData,
                    $this->extraData['role'] ?? 'customer',
                    $this->extraData['user_id'] ?? null,
                    $this->eventType
                );

                if (!$res['success'] && in_array($res['error_code'] ?? '', ['UNAVAILABLE', 'QUOTA_EXCEEDED'])) {
                    throw new Exception($res['error'] ?? 'Transient FCM error');
                }
            }
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'UNAVAILABLE') || str_contains($e->getMessage(), 'timed out') || str_contains($e->getMessage(), 'cURL') || str_contains($e->getMessage(), 'QUOTA_EXCEEDED')) {
                Log::warning("SendFirebasePushNotificationJob Transient Error [{$this->eventType}]: " . $e->getMessage());
                throw $e;
            }
            Log::info("SendFirebasePushNotificationJob [{$this->eventType}] completed with message: " . $e->getMessage());
        }
    }
}
