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
        Schema::create('trWaLog', function (Blueprint $table) {
            $table->integer('intWaLog_ID')->primary();
            $table->integer('intWaSchedule_ID')->nullable()->index();
            $table->integer('intUser_ID')->nullable()->index();
            $table->string('txtRecipientPhone', 50);
            $table->string('txtRecipientName', 255);
            $table->text('txtMessage');
            $table->string('txtStatus', 50)->default('PENDING'); // 'SUCCESS', 'FAILED', 'PENDING'
            $table->text('txtApiResponse')->nullable();
            $table->dateTime('dtmSentAt');
            $table->string('txtInsertedBy', 100);
            $table->dateTime('dtmInserted');

            $table->foreign('intWaSchedule_ID')
                ->references('intWaSchedule_ID')
                ->on('mWaSchedule')
                ->nullOnDelete();

            $table->foreign('intUser_ID')
                ->references('intUser_ID')
                ->on('mUser')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trWaLog');
    }
};
