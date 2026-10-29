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
                $firebaseService->sendToToken(
                    $this->target,
                    $template['title'],
                    $template['body'],
                    $this->extraData,
                    $this->extraData['role'] ?? 'customer',
                    $this->extraData['user_id'] ?? null,
                    $this->eventType
                );
            }
        } catch (Exception $e) {
            Log::error("SendFirebasePushNotificationJob Error [{$this->eventType}]: " . $e->getMessage());
            throw $e;
        }
    }
}
