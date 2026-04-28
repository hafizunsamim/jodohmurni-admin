<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affiliate_pro_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('affiliate_pro_requests', 'promotion_platform')) {
                $table->string('promotion_platform', 120)->nullable()->after('reason');
            }
        });

        Schema::table('external_affiliate_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('external_affiliate_applications', 'promotion_platform')) {
                $table->string('promotion_platform', 120)->nullable()->after('reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('affiliate_pro_requests', function (Blueprint $table) {
            if (Schema::hasColumn('affiliate_pro_requests', 'promotion_platform')) {
                $table->dropColumn('promotion_platform');
            }
        });

        Schema::table('external_affiliate_applications', function (Blueprint $table) {
            if (Schema::hasColumn('external_affiliate_applications', 'promotion_platform')) {
                $table->dropColumn('promotion_platform');
            }
        });
    }
};

