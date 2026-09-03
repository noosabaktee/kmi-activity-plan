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
        Schema::create('mSkillset', function (Blueprint $table) {
            $table->integer('intSkillset_ID')->primary();
            $table->string('txtSkillsetName', 150)->unique();
            $table->text('txtDescription')->nullable();
            $table->string('txtBadgeColor', 30)->default('#006838');
            $table->string('txtIcon', 100)->default('fa-solid fa-code');
            $table->string('txtInsertedBy', 100);
            $table->dateTime('dtmInserted');
            $table->string('txtUpdatedBy', 100)->nullable();
            $table->dateTime('dtmUpdated')->nullable();
            $table->boolean('bitActive')->default(true);
        });

        $now = now();
        $defaultSkillsets = [
            [
                'intSkillset_ID' => 1,
                'txtSkillsetName' => 'Web Development',
                'txtDescription' => 'Pengembangan web app full-stack, frontend modern, RESTful API & integrasi sistem.',
                'txtBadgeColor' => '#2563EB',
                'txtIcon' => 'fa-solid fa-globe',
                'txtInsertedBy' => 'System',
                'dtmInserted' => $now,
                'bitActive' => true,
            ],
            [
                'intSkillset_ID' => 2,
                'txtSkillsetName' => 'AI & Computer Vision',
                'txtDescription' => 'Implementasi Machine Learning, Deep Learning, Computer Vision, OCR & AI Agents.',
                'txtBadgeColor' => '#7C3AED',
                'txtIcon' => 'fa-solid fa-brain',
                'txtInsertedBy' => 'System',
                'dtmInserted' => $now,
                'bitActive' => true,
            ],
            [
                'intSkillset_ID' => 3,
                'txtSkillsetName' => 'Embedded Systems & IoT Data Acquisition',
                'txtDescription' => 'Mikrokontroler, telemetri sensor, PLC, akuisisi data mesin & automasi industri IoT.',
                'txtBadgeColor' => '#0D9488',
                'txtIcon' => 'fa-solid fa-microchip',
                'txtInsertedBy' => 'System',
                'dtmInserted' => $now,
                'bitActive' => true,
            ],
            [
                'intSkillset_ID' => 4,
                'txtSkillsetName' => 'Mobile App Development',
                'txtDescription' => 'Pengembangan aplikasi mobile berbasis Android, iOS, maupun multiplatform Flutter.',
                'txtBadgeColor' => '#0891B2',
                'txtIcon' => 'fa-solid fa-mobile-screen-button',
                'txtInsertedBy' => 'System',
                'dtmInserted' => $now,
                'bitActive' => true,
            ],
            [
                'intSkillset_ID' => 5,
                'txtSkillsetName' => 'Data Engineering & Analytics',
                'txtDescription' => 'ETL pipeline data processing, data warehousing, Business Intelligence & visualisasi data.',
                'txtBadgeColor' => '#D97706',
                'txtIcon' => 'fa-solid fa-chart-line',
                'txtInsertedBy' => 'System',
                'dtmInserted' => $now,
                'bitActive' => true,
            ],
            [
                'intSkillset_ID' => 6,
                'txtSkillsetName' => 'Automation & RPA',
                'txtDescription' => 'Robotic Process Automation, scripting Python/bash, dan otomatisasi alur kerja operasional.',
                'txtBadgeColor' => '#EA580C',
                'txtIcon' => 'fa-solid fa-robot',
                'txtInsertedBy' => 'System',
                'dtmInserted' => $now,
                'bitActive' => true,
            ],
            [
                'intSkillset_ID' => 7,
                'txtSkillsetName' => 'Cloud Infrastructure & DevOps',
                'txtDescription' => 'Server management, containerization Docker, CI/CD pipeline, dan cloud infrastructure.',
                'txtBadgeColor' => '#4F46E5',
                'txtIcon' => 'fa-solid fa-cloud',
                'txtInsertedBy' => 'System',
                'dtmInserted' => $now,
                'bitActive' => true,
            ],
            [
                'intSkillset_ID' => 8,
                'txtSkillsetName' => 'Cybersecurity & Network Systems',
                'txtDescription' => 'Keamanan jaringan, security audit, konfigurasi firewall, VPN & server hardening.',
                'txtBadgeColor' => '#DC2626',
                'txtIcon' => 'fa-solid fa-shield-halved',
                'txtInsertedBy' => 'System',
                'dtmInserted' => $now,
                'bitActive' => true,
            ],
        ];

        DB::table('mSkillset')->insert($defaultSkillsets);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mSkillset');
    }
};
