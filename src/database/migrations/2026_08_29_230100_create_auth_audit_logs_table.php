<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabela de evidência 2.6: mesmos eventos do log JSON, consultáveis no MySQL.
     * email_hash CHAR-like 64 = SHA-256 hex; sem PII em claro.
     */
    public function up(): void
    {
        Schema::create('auth_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event', 64);
            $table->string('outcome', 32);
            $table->string('email_hash', 64)->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_audit_logs');
    }
};
