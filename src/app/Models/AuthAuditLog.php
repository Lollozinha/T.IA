<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Espelho MySQL da auditoria (além do JSON em storage/logs/audit-*.log).
 *
 * email_hash = SHA-256 do e-mail; senha/OTP/token nunca entram em meta.
 * Eventos: login_failed, login_lockout, password_reset_link_sent, password_reset, two_factor_*.
 */
class AuthAuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'event',
        'outcome',
        'email_hash',
        'ip',
        'user_agent',
        'meta',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
