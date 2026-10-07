<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->string('room_number');
            $table->string('room_name')->nullable();
            $table->string('floor')->nullable();
            $table->enum('room_type', ['single', 'double', 'shared', 'dormitory', 'other']);
            $table->integer('capacity')->default(1);
            $table->decimal('rental_rate', 10, 2);
            $table->text('description')->nullable();
            $table->enum('status', ['available', 'full', 'maintenance', 'inactive'])->default('available');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};

