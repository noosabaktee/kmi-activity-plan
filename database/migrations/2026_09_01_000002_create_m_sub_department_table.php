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
        Schema::create('mSubDepartment', function (Blueprint $table) {
            $table->integer('intSubDepartment_ID')->primary();
            $table->integer('intDepartment_ID')->index();
            $table->string('txtSubDepartmentCode', 50);
            $table->string('txtSubDepartmentName', 255);
            $table->text('txtDescription')->nullable();
            $table->string('txtInsertedBy', 100);
            $table->dateTime('dtmInserted');
            $table->string('txtUpdatedBy', 100)->nullable();
            $table->dateTime('dtmUpdated')->nullable();
            $table->boolean('bitActive')->default(true);

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
        Schema::dropIfExists('mSubDepartment');
    }
};
