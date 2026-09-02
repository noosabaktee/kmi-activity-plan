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
        Schema::create('mWeeklyPlan', function (Blueprint $table) {
            $table->integer('intWeeklyPlan_ID')->primary();
            $table->integer('intUser_ID')->index();
            $table->integer('intDepartment_ID')->index();
            $table->string('txtWeekTitle', 255); // e.g. '31 Agustus - 04 September 2026'
            $table->date('dtmWeekStartDate');
            $table->date('dtmWeekEndDate');
            $table->text('txtTargetGoals')->nullable();
            $table->string('txtStatus', 50)->default('Draft');
            $table->string('txtInsertedBy', 100);
            $table->dateTime('dtmInserted');
            $table->string('txtUpdatedBy', 100)->nullable();
            $table->dateTime('dtmUpdated')->nullable();
            $table->boolean('bitActive')->default(true);

            $table->foreign('intUser_ID')
                ->references('intUser_ID')
                ->on('mUser')
                ->onDelete('cascade');

            $table->foreign('intDepartment_ID')
                ->references('intDepartment_ID')
                ->on('mDepartment')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mWeeklyPlan');
    }
};
