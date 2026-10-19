<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->boolean('is_listed')->default(false)->after('status');
            $table->text('public_description')->nullable()->after('is_listed');
            $table->string('public_contact')->nullable()->after('public_description');
            $table->json('amenities')->nullable()->after('public_contact');
            $table->text('house_rules')->nullable()->after('amenities');
            $table->decimal('latitude', 10, 7)->nullable()->after('house_rules');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['is_listed', 'public_description', 'public_contact', 'amenities', 'house_rules', 'latitude', 'longitude']);
        });
    }
};
