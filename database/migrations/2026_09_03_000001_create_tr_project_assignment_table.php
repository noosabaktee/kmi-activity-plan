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
        Schema::create('trProjectAssignment', function (Blueprint $table) {
            $table->integer('intProjectAssignment_ID')->primary();
            $table->integer('intProject_ID')->index();
            $table->integer('intSubProject_ID')->nullable()->index();
            $table->integer('intUser_ID')->index();
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
        Schema::dropIfExists('trProjectAssignment');
    }
};
