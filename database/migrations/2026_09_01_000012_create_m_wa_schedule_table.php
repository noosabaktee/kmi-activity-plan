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
        Schema::create('mWaSchedule', function (Blueprint $table) {
            $table->integer('intWaSchedule_ID')->primary();
            $table->string('txtScheduleTitle', 255);
            $table->string('txtCronDay', 50)->default('Friday'); // 'Friday', 'Monday', 'Everyday', etc.
            $table->string('txtScheduledTime', 10)->default('07:00'); // '07:00'
            $table->text('txtMessageTemplate');
            $table->string('txtFooterText', 100)->default('Sent via mpwa');
            $table->string('txtTargetType', 50)->default('all'); // 'all', 'subdept', 'role'
            $table->integer('intSubDepartment_ID')->nullable()->index();
            $table->string('txtTargetRole', 50)->nullable();
            $table->dateTime('dtmLastSentAt')->nullable();
            $table->string('txtInsertedBy', 100);
            $table->dateTime('dtmInserted');
            $table->string('txtUpdatedBy', 100)->nullable();
            $table->dateTime('dtmUpdated')->nullable();
            $table->boolean('bitActive')->default(true);

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
        Schema::dropIfExists('mWaSchedule');
    }
};
