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
        Schema::create('trDailyTask', function (Blueprint $table) {
            $table->integer('intDailyTask_ID')->primary();
            $table->integer('intUser_ID')->index();
            $table->integer('intDepartment_ID')->index();
            $table->integer('intSubDepartment_ID')->nullable()->index();
            $table->integer('intProject_ID')->nullable()->index();
            $table->integer('intSubProject_ID')->nullable()->index();
            $table->date('dtmTaskDate');
            $table->text('txtActivityDescription');
            $table->string('txtDeliverableOutput', 255)->nullable();
            $table->float('floatDurationHours')->default(1);
            $table->float('floatProgressPercent')->default(0);
            $table->string('txtTaskStatus', 50)->default('In Progress');
            $table->text('txtNotes')->nullable();
            $table->string('txtInsertedBy', 100);
            $table->dateTime('dtmInserted');

            $table->foreign('intUser_ID')
                ->references('intUser_ID')
                ->on('mUser')
                ->onDelete('cascade');

            $table->foreign('intDepartment_ID')
                ->references('intDepartment_ID')
                ->on('mDepartment')
                ->onDelete('cascade');

            $table->foreign('intSubDepartment_ID')
                ->references('intSubDepartment_ID')
                ->on('mSubDepartment')
                ->nullOnDelete();

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
        Schema::dropIfExists('trDailyTask');
    }
};
