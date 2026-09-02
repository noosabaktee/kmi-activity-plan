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
        Schema::create('trSupervisorSubDept', function (Blueprint $table) {
            $table->integer('intSupervisorSubDept_ID')->primary();
            $table->integer('intUser_ID')->index();
            $table->integer('intSubDepartment_ID')->index();
            $table->string('txtInsertedBy', 100);
            $table->dateTime('dtmInserted');

            $table->foreign('intUser_ID')
                ->references('intUser_ID')
                ->on('mUser')
                ->onDelete('cascade');

            $table->foreign('intSubDepartment_ID')
                ->references('intSubDepartment_ID')
                ->on('mSubDepartment')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trSupervisorSubDept');
    }
};
