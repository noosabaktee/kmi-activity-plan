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
            $table->integer('intSkillset_ID')->nullable()->after('intProjectType_ID')->index();
            $table->foreign('intSkillset_ID')
                ->references('intSkillset_ID')
                ->on('mSkillset')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mProject', function (Blueprint $table) {
            $table->dropForeign(['intSkillset_ID']);
            $table->dropColumn('intSkillset_ID');
        });
    }
};
