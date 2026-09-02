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
        Schema::create('trDailyPlanActivity', function (Blueprint $table) {
            $table->integer('intDailyPlanActivity_ID')->primary();
            $table->integer('intWeeklyPlan_ID')->index();
            $table->integer('intUser_ID')->index();
            $table->integer('intProject_ID')->nullable()->index();
            $table->integer('intSubProject_ID')->nullable()->index();
            $table->date('dtmActivityDate');
            $table->string('txtDayName', 20); // 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'
            $table->string('txtStartTime', 10)->default('08:00');
            $table->string('txtEndTime', 10)->default('10:00');
            $table->float('floatDuration')->default(2.0);
            $table->string('txtActivityName', 255);
            $table->string('txtLocationType', 100)->nullable(); // e.g. SENTUL, KMI, Mekor, BG Office, Cuti
            $table->text('txtRemarks')->nullable();
            $table->boolean('bitIsCompleted')->default(false);
            $table->string('txtInsertedBy', 100);
            $table->dateTime('dtmInserted');

            $table->foreign('intWeeklyPlan_ID')
                ->references('intWeeklyPlan_ID')
                ->on('mWeeklyPlan')
                ->onDelete('cascade');

            $table->foreign('intUser_ID')
                ->references('intUser_ID')
                ->on('mUser')
                ->onDelete('cascade');

            $table->foreign('intProject_ID')
                ->references('intProject_ID')
                ->on('mProject')
                ->nullOnDelete();

            $table->foreign('intSubProject_ID')
                ->references('intSubProject_ID')
                ->on('trSubProject')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trDailyPlanActivity');
    }
};
