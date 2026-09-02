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
        Schema::create('mProjectType', function (Blueprint $table) {
            $table->integer('intProjectType_ID')->primary();
            $table->string('txtProjectTypeCode', 50)->unique();
            $table->string('txtProjectTypeName', 100);
            $table->text('txtDescription')->nullable();
            $table->string('txtColor', 30)->default('#006838');
            $table->string('txtIcon', 100)->default('fa-solid fa-folder-tree');
            $table->float('floatDefaultWeight')->default(0);
            $table->string('txtInsertedBy', 100);
            $table->dateTime('dtmInserted');
            $table->string('txtUpdatedBy', 100)->nullable();
            $table->dateTime('dtmUpdated')->nullable();
            $table->boolean('bitActive')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mProjectType');
    }
};
