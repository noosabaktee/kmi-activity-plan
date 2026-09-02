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
        // 1. Update mWaSchedule table
        Schema::table('mWaSchedule', function (Blueprint $table) {
            if (!Schema::hasColumn('mWaSchedule', 'intDepartment_ID')) {
                $table->integer('intDepartment_ID')->nullable()->after('txtTargetType')->index();
                $table->foreign('intDepartment_ID')
                    ->references('intDepartment_ID')
                    ->on('mDepartment')
                    ->nullOnDelete();
            }
            if (!Schema::hasColumn('mWaSchedule', 'intUser_ID')) {
                $table->integer('intUser_ID')->nullable()->after('intSubDepartment_ID')->index();
                $table->foreign('intUser_ID')
                    ->references('intUser_ID')
                    ->on('mUser')
                    ->nullOnDelete();
            }
            if (!Schema::hasColumn('mWaSchedule', 'txtCronExpression')) {
                $table->string('txtCronExpression', 100)->nullable()->after('txtScheduleTitle');
            }
        });

        // 2. Create mSetting table for key-value system settings
        if (!Schema::hasTable('mSetting')) {
            Schema::create('mSetting', function (Blueprint $table) {
                $table->integer('intSetting_ID')->primary();
                $table->string('txtSettingKey', 100)->unique();
                $table->text('txtSettingValue')->nullable();
                $table->string('txtSettingGroup', 50)->default('general');
                $table->string('txtDescription', 255)->nullable();
                $table->string('txtInsertedBy', 100)->nullable();
                $table->dateTime('dtmInserted')->nullable();
                $table->string('txtUpdatedBy', 100)->nullable();
                $table->dateTime('dtmUpdated')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mWaSchedule', function (Blueprint $table) {
            if (Schema::hasColumn('mWaSchedule', 'intDepartment_ID')) {
                $table->dropForeign(['intDepartment_ID']);
                $table->dropColumn('intDepartment_ID');
            }
            if (Schema::hasColumn('mWaSchedule', 'intUser_ID')) {
                $table->dropForeign(['intUser_ID']);
                $table->dropColumn('intUser_ID');
            }
            if (Schema::hasColumn('mWaSchedule', 'txtCronExpression')) {
                $table->dropColumn('txtCronExpression');
            }
        });

        Schema::dropIfExists('mSetting');
    }
};
