<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('affiliate_commissions')) {
            return;
        }

        if (!Schema::hasColumn('affiliate_commissions', 'status')) {
            Schema::table('affiliate_commissions', function (Blueprint $table) {
                $table->string('status', 20)->default('pending')->after('commission_sen');
                $table->index('status', 'idx_aff_comm_status');
            });

            // Backfill existing rows (if any) to pending
            DB::table('affiliate_commissions')
                ->whereNull('status')
                ->update(['status' => 'pending']);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('affiliate_commissions')) {
            return;
        }

        if (Schema::hasColumn('affiliate_commissions', 'status')) {
            Schema::table('affiliate_commissions', function (Blueprint $table) {
                $table->dropIndex('idx_aff_comm_status');
                $table->dropColumn('status');
            });
        }
    }
};

