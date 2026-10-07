<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owner_access_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('access_request_id')->constrained('owner_access_requests')->onDelete('cascade');
            $table->foreignId('admin_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('house_owner_id')->constrained()->onDelete('cascade');
            $table->json('permissions');
            $table->timestamp('granted_at');
            $table->timestamp('expires_at');
            $table->timestamp('revoked_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_access_sessions');
    }
};

