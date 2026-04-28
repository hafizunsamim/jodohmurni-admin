<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_external_affiliate')) {
                $table->boolean('is_external_affiliate')->default(false)->after('affiliate_pro_approved_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_external_affiliate')) {
                $table->dropColumn('is_external_affiliate');
            }
        });
    }
};

