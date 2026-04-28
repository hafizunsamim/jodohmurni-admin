<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        if (!Schema::hasColumn('users', 'affiliate_pro_approved_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('affiliate_pro_approved_at')->nullable()->after('status_keahlian');
                $table->index('affiliate_pro_approved_at', 'idx_users_aff_pro');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        if (Schema::hasColumn('users', 'affiliate_pro_approved_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('idx_users_aff_pro');
                $table->dropColumn('affiliate_pro_approved_at');
            });
        }
    }
};

