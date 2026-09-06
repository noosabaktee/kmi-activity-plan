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
        Schema::table('mProject', function (Blueprint $table) {
            if (! Schema::hasColumn('mProject', 'txtApprovalStatus')) {
                $table->string('txtApprovalStatus', 50)->default('Approved');
            }
            if (! Schema::hasColumn('mProject', 'intSupervisor_ID')) {
                $table->integer('intSupervisor_ID')->nullable();
            }
            if (! Schema::hasColumn('mProject', 'intApprovedBy_ID')) {
                $table->integer('intApprovedBy_ID')->nullable();
            }
            if (! Schema::hasColumn('mProject', 'dtmApprovedAt')) {
                $table->dateTime('dtmApprovedAt')->nullable();
            }
            if (! Schema::hasColumn('mProject', 'txtApprovalNotes')) {
                $table->text('txtApprovalNotes')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mProject', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach (['txtApprovalStatus', 'intSupervisor_ID', 'intApprovedBy_ID', 'dtmApprovedAt', 'txtApprovalNotes'] as $col) {
                if (Schema::hasColumn('mProject', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};

