<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('house_owners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('business_name')->nullable();
            $table->string('business_address')->nullable();
            $table->enum('subscription_plan', ['free', 'basic', 'standard', 'premium'])->default('free');
            $table->timestamp('subscription_expires_at')->nullable();
            $table->boolean('privacy_require_approval')->default(true);
            $table->boolean('privacy_notify_on_access')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('house_owners');
    }
};

