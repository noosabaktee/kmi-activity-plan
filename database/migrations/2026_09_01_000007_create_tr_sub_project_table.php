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
        Schema::create('trSubProject', function (Blueprint $table) {
            $table->integer('intSubProject_ID')->primary();
            $table->integer('intProject_ID')->index();
            $table->string('txtSubProjectName', 255);
            $table->text('txtDeliverable')->nullable();
            $table->text('txtTargetSkalaGrade')->nullable();
            $table->integer('intScore')->nullable();
            $table->string('txtAchievement', 255)->nullable();
            $table->float('floatWeight')->default(0);
            $table->float('floatProgress')->default(0);
            $table->dateTime('dtmStartDate')->nullable();
            $table->dateTime('dtmEndDate')->nullable();
            $table->string('txtStatus', 50)->default('In Progress');
            $table->string('txtInsertedBy', 100);
            $table->dateTime('dtmInserted');

            $table->foreign('intProject_ID')
                ->references('intProject_ID')
                ->on('mProject')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trSubProject');
    }
};
