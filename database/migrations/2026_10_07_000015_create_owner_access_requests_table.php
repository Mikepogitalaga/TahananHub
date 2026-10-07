<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owner_access_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requested_by_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('house_owner_id')->constrained()->onDelete('cascade');
            $table->text('reason');
            $table->integer('requested_duration_minutes');
            $table->json('permissions');
            $table->enum('status', ['pending', 'approved', 'denied', 'expired', 'revoked'])->default('pending');
            $table->text('request_notes')->nullable();
            $table->text('owner_notes')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_access_requests');
    }
};

