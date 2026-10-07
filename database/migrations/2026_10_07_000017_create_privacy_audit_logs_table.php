<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('privacy_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('house_owner_id')->constrained()->onDelete('cascade');
            $table->foreignId('access_session_id')->nullable()->constrained('owner_access_sessions')->onDelete('set null');
            $table->string('action');
            $table->string('resource');
            $table->unsignedBigInteger('resource_id')->nullable();
            $table->text('description')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('privacy_audit_logs');
    }
};

