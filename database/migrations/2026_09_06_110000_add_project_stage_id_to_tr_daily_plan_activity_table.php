<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('trDailyPlanActivity', 'intProjectStage_ID')) {
            Schema::table('trDailyPlanActivity', function (Blueprint $table) {
                $table->integer('intProjectStage_ID')->nullable()->index();

                $table->foreign('intProjectStage_ID')
                    ->references('intProjectStage_ID')
                    ->on('trProjectStage')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('trDailyPlanActivity', 'intProjectStage_ID')) {
            Schema::table('trDailyPlanActivity', function (Blueprint $table) {
                $table->dropForeign(['intProjectStage_ID']);
                $table->dropColumn('intProjectStage_ID');
            });
        }
    }
};
