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
            if (! Schema::hasColumn('mProject', 'bitIsAdHoc')) {
                $table->boolean('bitIsAdHoc')->default(false);
            }
            if (! Schema::hasColumn('mProject', 'txtAdHocCategory')) {
                $table->string('txtAdHocCategory', 100)->nullable();
            }
            if (! Schema::hasColumn('mProject', 'txtPriority')) {
                $table->string('txtPriority', 50)->nullable();
            }
            if (! Schema::hasColumn('mProject', 'txtSpecialGoal')) {
                $table->text('txtSpecialGoal')->nullable();
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
            foreach (['bitIsAdHoc', 'txtAdHocCategory', 'txtPriority', 'txtSpecialGoal'] as $col) {
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

