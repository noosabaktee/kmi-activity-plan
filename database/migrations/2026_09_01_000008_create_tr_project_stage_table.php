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
        Schema::create('trProjectStage', function (Blueprint $table) {
            $table->integer('intProjectStage_ID')->primary();
            $table->integer('intProject_ID')->index();
            $table->integer('intSubProject_ID')->nullable()->index();
            $table->integer('intProjectStageNumber');
            $table->string('txtProjectStageStep', 255);
            $table->dateTime('dtmProjectStageStartDate')->nullable();
            $table->dateTime('dtmProjectStageEndDate')->nullable();
            $table->float('floatProjectStagePlan')->default(0);
            $table->float('floatProjectStageActual')->default(0);
            $table->string('txtInsertedBy', 100);
            $table->dateTime('dtmInserted');

            $table->foreign('intProject_ID')
                ->references('intProject_ID')
                ->on('mProject')
                ->onDelete('cascade');

            $table->foreign('intSubProject_ID')
                ->references('intSubProject_ID')
                ->on('trSubProject')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trProjectStage');
    }
};
