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
        Schema::create('mProject', function (Blueprint $table) {
            $table->integer('intProject_ID')->primary();
            $table->integer('intDepartment_ID')->index();
            $table->integer('intSubDepartment_ID')->nullable()->index();
            $table->integer('intProjectType_ID')->index();
            $table->integer('intUser_ID')->index(); // Owner / Employee
            $table->string('txtProjectCode', 50)->nullable();
            $table->string('txtProjectName', 255);
            $table->string('txtKpiLevel', 50)->default('Individu'); // 'Department', 'Individu', 'MAR Bersama'
            $table->text('txtDeliverable')->nullable();
            $table->text('txtTargetSkalaGrade')->nullable();
            $table->integer('intScore')->nullable();
            $table->string('txtAchievement', 255)->nullable();
            $table->float('floatWeight')->default(0);
            $table->boolean('bitHasSubProject')->default(false);
            $table->text('txtDescription')->nullable();
            $table->dateTime('dtmProjectStartDate')->nullable();
            $table->dateTime('dtmProjectEndDate')->nullable();
            $table->float('floatPlan')->default(100);
            $table->float('floatActual')->default(0);
            $table->string('txtStatus', 50)->default('In Progress');
            $table->string('txtInsertedBy', 100);
            $table->dateTime('dtmInserted');
            $table->string('txtUpdatedBy', 100)->nullable();
            $table->dateTime('dtmUpdated')->nullable();
            $table->boolean('bitActive')->default(true);

            $table->foreign('intDepartment_ID')
                ->references('intDepartment_ID')
                ->on('mDepartment')
                ->onDelete('cascade');

            $table->foreign('intSubDepartment_ID')
                ->references('intSubDepartment_ID')
                ->on('mSubDepartment')
                ->nullOnDelete();

            $table->foreign('intProjectType_ID')
                ->references('intProjectType_ID')
                ->on('mProjectType')
                ->onDelete('cascade');

            $table->foreign('intUser_ID')
                ->references('intUser_ID')
                ->on('mUser')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mProject');
    }
};
