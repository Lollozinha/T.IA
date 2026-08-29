<?php

namespace App\Actions;

use App\Models\AuthAuditLog;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuditAuthEvent
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public static function record(
        string $event,
        string $outcome,
        ?string $email = null,
        array $meta = [],
    ): void {
        $emailHash = $email
            ? hash('sha256', strtolower(trim($email)))
            : null;

        $payload = [
            'timestamp' => now()->toIso8601String(),
            'event' => $event,
            'outcome' => $outcome,
            'email_hash' => $emailHash,
            'ip' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'meta' => $meta,
        ];

        Log::channel('audit')->info(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        try {
            AuthAuditLog::query()->create([
                'event' => $event,
                'outcome' => $outcome,
                'email_hash' => $emailHash,
                'ip' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'meta' => $meta ?: null,
                'created_at' => now(),
            ]);
        } catch (Throwable) {
            // Auditoria em arquivo não pode falhar o fluxo de autenticação.
        }
    }
}
