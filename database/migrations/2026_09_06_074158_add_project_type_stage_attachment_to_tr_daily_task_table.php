<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('trDailyTask', function (Blueprint $table) {
            $table->integer('intProjectType_ID')->nullable()->index();
            $table->integer('intProjectStage_ID')->nullable()->index();
            $table->string('txtAttachmentPath', 500)->nullable();
            $table->string('txtAttachmentName', 255)->nullable();
            $table->string('txtAttachmentType', 50)->nullable();

            $table->foreign('intProjectType_ID')
                ->references('intProjectType_ID')
                ->on('mProjectType')
                ->nullOnDelete();

            $table->foreign('intProjectStage_ID')
                ->references('intProjectStage_ID')
                ->on('trProjectStage')
                ->nullOnDelete();
        });

        // Backfill intProjectType_ID from mProject for existing daily tasks
        try {
            DB::statement('
                UPDATE "trDailyTask" dt
                SET "intProjectType_ID" = p."intProjectType_ID"
                FROM "mProject" p
                WHERE dt."intProject_ID" = p."intProject_ID"
                  AND dt."intProjectType_ID" IS NULL
                  AND p."intProjectType_ID" IS NOT NULL
            ');
        } catch (\Throwable $e) {
            $tasks = DB::table('trDailyTask')->whereNotNull('intProject_ID')->whereNull('intProjectType_ID')->get();
            foreach ($tasks as $task) {
                $project = DB::table('mProject')->where('intProject_ID', $task->intProject_ID)->first();
                if ($project && $project->intProjectType_ID) {
                    DB::table('trDailyTask')
                        ->where('intDailyTask_ID', $task->intDailyTask_ID)
                        ->update(['intProjectType_ID' => $project->intProjectType_ID]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trDailyTask', function (Blueprint $table) {
            $table->dropForeign(['intProjectType_ID']);
            $table->dropForeign(['intProjectStage_ID']);
            $table->dropColumn([
                'intProjectType_ID',
                'intProjectStage_ID',
                'txtAttachmentPath',
                'txtAttachmentName',
                'txtAttachmentType',
            ]);
        });
    }
};
