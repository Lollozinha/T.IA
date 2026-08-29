<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
