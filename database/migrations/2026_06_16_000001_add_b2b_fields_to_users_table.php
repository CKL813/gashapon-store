<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('name');
            $table->string('company_name')->nullable()->after('phone');
            $table->boolean('is_b2b')->default(false)->after('company_name');
            // B2B accounts must be approved by admin before wholesale prices are applied.
            $table->boolean('is_approved')->default(false)->after('is_b2b');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'company_name', 'is_b2b', 'is_approved']);
        });
    }
};
