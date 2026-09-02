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
        Schema::create('mUser', function (Blueprint $table) {
            $table->integer('intUser_ID')->primary();
            $table->integer('intDepartment_ID')->nullable()->index();
            $table->integer('intSubDepartment_ID')->nullable()->index();
            $table->string('txtEmployeeCode', 50)->nullable();
            $table->string('txtEmployeeName', 255);
            $table->string('txtEmail', 255)->unique();
            $table->string('txtPassword', 255);
            $table->string('txtPhone', 50)->nullable();
            $table->string('txtRole', 50); // 'Head', 'Supervisor', 'Employee', 'Superadmin'
            $table->string('txtPosition', 100)->nullable();
            $table->string('txtProfilePhoto', 255)->nullable();
            $table->string('txtInsertedBy', 100);
            $table->dateTime('dtmInserted');
            $table->string('txtUpdatedBy', 100)->nullable();
            $table->dateTime('dtmUpdated')->nullable();
            $table->boolean('bitActive')->default(true);

            $table->foreign('intDepartment_ID')
                ->references('intDepartment_ID')
                ->on('mDepartment')
                ->nullOnDelete();

            $table->foreign('intSubDepartment_ID')
                ->references('intSubDepartment_ID')
                ->on('mSubDepartment')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mUser');
    }
};
