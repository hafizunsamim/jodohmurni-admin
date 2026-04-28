<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_pro_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('user_id', 36);
            $table->text('reason');
            $table->string('status', 20)->default('pending'); // pending|approved|rejected
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('reviewed_by_admin_id')->nullable();
            $table->text('admin_feedback')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status'], 'idx_aff_pro_user_status');
            $table->index('status', 'idx_aff_pro_status');

            $table->foreign('user_id', 'affiliate_pro_requests_fk_user')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_pro_requests');
    }
};

