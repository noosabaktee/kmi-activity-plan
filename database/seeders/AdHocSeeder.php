<?php

namespace Database\Seeders;

use App\Models\MDepartment;
use App\Models\MProject;
use App\Models\MProjectType;
use App\Models\MSkillset;
use App\Models\MSubDepartment;
use App\Models\MUser;
use App\Models\TrDailyTask;
use App\Models\TrProjectAssignment;
use App\Models\TrProjectStage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdHocSeeder extends Seeder
{
    /**
     * Run the database seeds for Ad Hoc initiatives.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $now = now();

            // 1. Ensure Department & Project Type
            $dept = MDepartment::first() ?? MDepartment::create([
                'intDepartment_ID' => 1,
                'txtDepartmentCode' => 'MDP',
                'txtDepartmentName' => 'Manufacturing Development & Planning',
                'txtDescription' => 'Department responsible for manufacturing operational development, planning, and technical automation.',
                'txtInsertedBy' => 'seeder',
                'dtmInserted' => $now,
                'bitActive' => true,
            ]);

            $adHocType = MProjectType::where('txtProjectTypeCode', 'Ad Hoc')
                ->orWhere('txtProjectTypeName', 'like', '%Ad Hoc%')
                ->first();

            if (! $adHocType) {
                $adHocType = MProjectType::create([
                    'intProjectType_ID' => 5,
                    'txtProjectTypeCode' => 'Ad Hoc',
                    'txtProjectTypeName' => 'Ad Hoc & Strategic Initiatives',
                    'txtDescription' => 'Penanganan sasaran khusus dan kebutuhan sementara di luar project rutin.',
                    'floatDefaultWeight' => 20,
                    'txtColor' => '#006838',
                    'txtIcon' => 'fa-solid fa-bolt',
                    'txtInsertedBy' => 'seeder',
                    'dtmInserted' => $now,
                    'bitActive' => true,
                ]);
            }

            // Map users by code with fallback
            $usersByCode = MUser::all()->keyBy('txtEmployeeCode');
            $defaultUser = MUser::where('bitActive', true)->first();
            $getUserId = fn (string $code) => $usersByCode->get($code)?->intUser_ID ?? $defaultUser?->intUser_ID ?? 1;

            // Map subdepartments by code
            $subdeptsByCode = MSubDepartment::all()->keyBy('txtSubDepartmentCode');
            $getSubdeptId = fn (string $code) => $subdeptsByCode->get($code)?->intSubDepartment_ID ?? 1;

            // Map skillsets
            $skillsetsByName = MSkillset::all()->keyBy('txtSkillsetName');
            $getSkillsetId = fn (string $name) => $skillsetsByName->get($name)?->intSkillset_ID ?? 1;

            // 2. Clean previous seeded Ad Hoc records for safe idempotency
            $existingAdHocIds = MProject::where('bitIsAdHoc', true)
                ->orWhere('txtProjectCode', 'like', 'ADH-%')
                ->pluck('intProject_ID')
                ->all();

            if (! empty($existingAdHocIds)) {
                TrDailyTask::whereIn('intProject_ID', $existingAdHocIds)->delete();
                TrProjectStage::whereIn('intProject_ID', $existingAdHocIds)->delete();
                TrProjectAssignment::whereIn('intProject_ID', $existingAdHocIds)->delete();
                MProject::whereIn('intProject_ID', $existingAdHocIds)->delete();
            }

            // 3. Ad Hoc Initiatives Data
            $adHocsData = [
                [
                    'code' => 'ADH-2026-001',
                    'name' => 'Penanganan Anomali Sensor Suhu Pasteurizer Line 2',
                    'category' => 'Emergency Response',
                    'priority' => 'Critical',
                    'status' => 'In Progress',
                    'pic_code' => 'TSJ', // Tisya (AM / IoT)
                    'subdept_code' => 'AM',
                    'skillset' => 'Embedded Systems & IoT Data Acquisition',
                    'team_codes' => ['WAR', 'TSJ'],
                    'start' => Carbon::now()->subDays(6)->format('Y-m-d'),
                    'end' => Carbon::now()->addDays(5)->format('Y-m-d'),
                    'weight' => 15,
                    'special_goal' => 'Menstabilkan bacaan telemetri sensor suhu pada pasteurizer line 2 agar tidak memicu false trip alarm dan mencegah downtime produksi plant Sentul.',
                    'deliverable' => 'Laporan investigasi root-cause, modul sensor terkalibrasi ulang, dan SOP monitoring sementara.',
                    'grade' => "1. False alarm tereliminasi 100%\n2. Zero downtime produksi selama 7 hari operasi\n3. Berita Acara Perbaikan disetujui Engineering & QA",
                    'stages' => [
                        ['Isolasi anomali sensor & inspeksi konektor kabel telemetry thermocouple', 25, 25, -6, -4],
                        ['Penggantian modul transmitter & kalibrasi ulang probe sensor suhu', 25, 25, -3, -1],
                        ['Monitoring continuous run 48 jam pada SCADA & validasi log sheet data', 30, 25, 0, 2],
                        ['Penyusunan Berita Acara Perbaikan & sosialisasi SOP penanganan sementara', 20, 10, 3, 5],
                    ],
                    'tasks' => [
                        [
                            'user_code' => 'TSJ',
                            'date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                            'desc' => 'Pemeriksaan fisik thermocouple & grounding kabel sensor pasteurizer Sentul',
                            'output' => 'Data log fluktuasi sinyal analog',
                            'hours' => 3.5,
                            'progress' => 100,
                            'status' => 'Completed',
                        ],
                        [
                            'user_code' => 'WAR',
                            'date' => Carbon::now()->subDays(2)->format('Y-m-d'),
                            'desc' => 'Penggantian transmitter modul dan re-komisioning kalibrasi suhu pasteurizer',
                            'output' => 'Suhu stabil di 85°C (+-0.2°C)',
                            'hours' => 4.0,
                            'progress' => 100,
                            'status' => 'Completed',
                        ],
                        [
                            'user_code' => 'TSJ',
                            'date' => Carbon::now()->subDays(1)->format('Y-m-d'),
                            'desc' => 'Pengawasan pembacaan telemetry SCADA shift pagi dan sampling data histori',
                            'output' => 'Zero false trip alarm tercapai',
                            'hours' => 2.5,
                            'progress' => 85,
                            'status' => 'In Progress',
                        ],
                    ],
                ],
                [
                    'code' => 'ADH-2026-002',
                    'name' => 'Task Force Pemenuhan CAPA Audit FSSC 22000 & ISO Food Safety',
                    'category' => 'Audit & Compliance Finding',
                    'priority' => 'High',
                    'status' => 'Completed',
                    'pic_code' => 'SNH', // Sania (Supervisor MO/PPIC)
                    'subdept_code' => 'MO/PPIC',
                    'skillset' => 'Data Engineering & Analytics',
                    'team_codes' => ['JHN', 'NTO', 'SNH'],
                    'start' => Carbon::now()->subDays(18)->format('Y-m-d'),
                    'end' => Carbon::now()->subDays(2)->format('Y-m-d'),
                    'weight' => 20,
                    'special_goal' => 'Menutup 8 temuan minor audit eksternal terkait pencatatan kalibrasi digital dan traceability bahan baku staging area.',
                    'deliverable' => 'Dokumen CAPA tertutup 100%, evidence foto perbaikan, dan verifikasi Lead Auditor QA.',
                    'grade' => "1. 8 dari 8 temuan CAPA berstatus Closed\n2. Logsheet digital terintegrasi barcode\n3. Verifikasi QA Manager disetujui tanpa catatan",
                    'stages' => [
                        ['Identifikasi matrix temuan audit & gap analysis SOP staging area', 25, 25, -18, -15],
                        ['Pembaruan log kalibrasi digital & integrasi barcode scanner bahan baku', 30, 30, -14, -10],
                        ['Simulasi pre-audit internal bersama QA Compliance', 25, 25, -9, -6],
                        ['Closing meeting temuan audit & penandatanganan dokumen pemenuhan CAPA', 20, 20, -5, -2],
                    ],
                    'tasks' => [
                        [
                            'user_code' => 'SNH',
                            'date' => Carbon::now()->subDays(16)->format('Y-m-d'),
                            'desc' => 'Penyusunan matrix tindakan korektif (CAPA) untuk 8 temuan minor audit FSSC',
                            'output' => 'Matrix CAPA ditandatangani Dept Head',
                            'hours' => 3.0,
                            'progress' => 100,
                            'status' => 'Completed',
                        ],
                        [
                            'user_code' => 'JHN',
                            'date' => Carbon::now()->subDays(12)->format('Y-m-d'),
                            'desc' => 'Implementasi barcode tagging alat ukur dan standardisasi lembar ceklis kalibrasi',
                            'output' => 'Semua alat ukur staging bertagging barcode',
                            'hours' => 4.0,
                            'progress' => 100,
                            'status' => 'Completed',
                        ],
                        [
                            'user_code' => 'NTO',
                            'date' => Carbon::now()->subDays(4)->format('Y-m-d'),
                            'desc' => 'Pendampingan verifikasi lapangan bersama tim QA Auditor',
                            'output' => 'Berita Acara Closing CAPA dinyatakan Clear',
                            'hours' => 3.0,
                            'progress' => 100,
                            'status' => 'Completed',
                        ],
                    ],
                ],
                [
                    'code' => 'ADH-2026-003',
                    'name' => 'Troubleshooting Buffer Latency Middleware Mesin Packing Sachet B ke SAP',
                    'category' => 'Troubleshooting & Problem Solving',
                    'priority' => 'High',
                    'status' => 'In Progress',
                    'pic_code' => 'WAR', // Lead Software Engineer
                    'subdept_code' => 'MD/IT',
                    'skillset' => 'Web Development',
                    'team_codes' => ['WAR', 'AHO', 'DDI'],
                    'start' => Carbon::now()->subDays(4)->format('Y-m-d'),
                    'end' => Carbon::now()->addDays(6)->format('Y-m-d'),
                    'weight' => 15,
                    'special_goal' => 'Mengatasi bottleneck antrean data batch weight dari middleware packing sachet ke SAP ERP yang menyebabkan delay closing shift.',
                    'deliverable' => 'Patch middleware v1.2.4 terpasang, antrean payload nol, dan latency sinkronisasi di bawah 2 detik.',
                    'grade' => "1. Latency transaksi SAP < 2 detik\n2. Zero dropped transaction pada 10.000 records data batch\n3. Laporan stress test diverifikasi IT Infra",
                    'stages' => [
                        ['Profiling lalu lintas network socket & log query database middleware', 30, 30, -4, -3],
                        ['Patching worker queue buffer & implementasi bulk insert async worker', 35, 25, -2, 1],
                        ['Stress test beban transaksi 50 batch produksi sachet', 20, 0, 2, 4],
                        ['Deploy ke server production & pemantauan performa 24 jam', 15, 0, 5, 6],
                    ],
                    'tasks' => [
                        [
                            'user_code' => 'WAR',
                            'date' => Carbon::now()->subDays(3)->format('Y-m-d'),
                            'desc' => 'Analisis trace latency API SAP middleware dan profiling database index',
                            'output' => 'Identifikasi lock tabel transaksi packing',
                            'hours' => 3.5,
                            'progress' => 100,
                            'status' => 'Completed',
                        ],
                        [
                            'user_code' => 'DDI',
                            'date' => Carbon::now()->subDays(1)->format('Y-m-d'),
                            'desc' => 'Optimasi Redis queue payload worker & penyesuaian chunk size sinkronisasi',
                            'output' => 'Middleware v1.2.4-rc1 siap testing',
                            'hours' => 4.0,
                            'progress' => 70,
                            'status' => 'In Progress',
                        ],
                    ],
                ],
                [
                    'code' => 'ADH-2026-004',
                    'name' => 'Gugus Tugas Pengawalan Trial Run Formula Baru (NPD Batch #3)',
                    'category' => 'Task Force Khusus',
                    'priority' => 'Medium',
                    'status' => 'Under Review',
                    'pic_code' => 'YJN', // Yayan (PPIC Specialist)
                    'subdept_code' => 'MO/PPIC',
                    'skillset' => 'Data Engineering & Analytics',
                    'team_codes' => ['YJN', 'NTO'],
                    'start' => Carbon::now()->subDays(10)->format('Y-m-d'),
                    'end' => Carbon::now()->addDays(3)->format('Y-m-d'),
                    'weight' => 10,
                    'special_goal' => 'Kawal kepatuhan parameter suhu blending, humidity, dan viskositas bubuk formula baru pada mesin mixer A selama trial run batch 3.',
                    'deliverable' => 'Laporan evaluasi trial run NPD, logging parameter mixer, dan rekomendasi standar proses komersial.',
                    'grade' => "1. Trial run 3 batch sukses tanpa off-spec parameter\n2. Laporan parameter final ditandatangani R&D dan Production Head",
                    'stages' => [
                        ['Kalibrasi pre-trial sensor mixer A & alignment prosedur sampling R&D', 25, 25, -10, -8],
                        ['Supervisi trial run batch 1 dan 2 di clean room lantai produksi', 35, 35, -7, -4],
                        ['Pengujian viskositas, moisture level, dan homogenitas batch 3 di QC Lab', 25, 20, -3, 0],
                        ['Penyusunan laporan komprehensif trial run bersama tim Formulasi R&D', 15, 0, 1, 3],
                    ],
                    'tasks' => [
                        [
                            'user_code' => 'YJN',
                            'date' => Carbon::now()->subDays(9)->format('Y-m-d'),
                            'desc' => 'Pre-check kesiapan mesin mixer A dan bahan baku formula trial NPD batch 3',
                            'output' => 'Ceklis kesiapan mesin diverifikasi QA',
                            'hours' => 2.5,
                            'progress' => 100,
                            'status' => 'Completed',
                        ],
                        [
                            'user_code' => 'YJN',
                            'date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                            'desc' => 'Pengawalan proses mixing batch 1 dan pencatatan logger ampere & suhu motor',
                            'output' => 'Data kurva blending konsisten dengan target',
                            'hours' => 4.0,
                            'progress' => 100,
                            'status' => 'Completed',
                        ],
                    ],
                ],
                [
                    'code' => 'ADH-2026-005',
                    'name' => 'Kaizen Penataan Ulang Layout Pallet Finished Goods Warehouse',
                    'category' => 'Process Improvement / Kaizen',
                    'priority' => 'Low',
                    'status' => 'In Progress',
                    'pic_code' => 'NTO', // Nanto (Operations Officer)
                    'subdept_code' => 'MO/PPIC',
                    'skillset' => 'Automation & RPA',
                    'team_codes' => ['NTO', 'YJN'],
                    'start' => Carbon::now()->subDays(7)->format('Y-m-d'),
                    'end' => Carbon::now()->addDays(8)->format('Y-m-d'),
                    'weight' => 10,
                    'special_goal' => 'Menata ulang layout staging pallet plastik di gudang finish goods untuk memangkas waktu pencarian pallet oleh operator forklift sebesar 25%.',
                    'deliverable' => 'Denah layout staging pallet terstandarisasi, penandaan visual floor marking 4 blok, dan checklist 5S harian.',
                    'grade' => "1. Floor marking selesai di 4 blok staging\n2. Waktu siklus pengambilan pallet berkurang >= 20%\n3. Audit 5S warehouse score >= 90",
                    'stages' => [
                        ['Pemetaan alur lintas forklift & pengukuran cycle time eksisting', 30, 30, -7, -5],
                        ['Pengecatan garis marka lantai & penomoran blok staging A-D', 40, 20, -4, 2],
                        ['Sosialisasi SOP penempatan pallet ke operator forklift shift 1-3', 30, 0, 3, 8],
                    ],
                    'tasks' => [
                        [
                            'user_code' => 'NTO',
                            'date' => Carbon::now()->subDays(6)->format('Y-m-d'),
                            'desc' => 'Observasi genba alur forklift dan identifikasi titik bottleneck staging pallet',
                            'output' => 'Diagram alur forklift dan spagheti chart',
                            'hours' => 3.0,
                            'progress' => 100,
                            'status' => 'Completed',
                        ],
                        [
                            'user_code' => 'NTO',
                            'date' => Carbon::now()->subDays(2)->format('Y-m-d'),
                            'desc' => 'Pemasangan floor line marking epoxy pada blok staging A dan B',
                            'output' => 'Blok A dan B selesai marka dan visual tag',
                            'hours' => 3.5,
                            'progress' => 50,
                            'status' => 'In Progress',
                        ],
                    ],
                ],
                [
                    'code' => 'ADH-2026-006',
                    'name' => 'Dashboard Monitoring Kinerja Eksekutif MDP KMI 2026',
                    'category' => 'Special Request Management',
                    'priority' => 'High',
                    'status' => 'Completed',
                    'pic_code' => 'NRS', // Nareswara (Dept Head)
                    'subdept_code' => 'MD/IT',
                    'skillset' => 'Web Development',
                    'team_codes' => ['NRS', 'WAR', 'AHO', 'IHP'],
                    'start' => Carbon::now()->subDays(25)->format('Y-m-d'),
                    'end' => Carbon::now()->subDays(5)->format('Y-m-d'),
                    'weight' => 20,
                    'special_goal' => 'Menyediakan executive dashboard digital real-time terkait pencapaian OEE mesin, konsumsi energi, dan status project MDP untuk Management Review bulanan.',
                    'deliverable' => 'Executive TV Dashboard display di meeting room lantai 2 dan web viewer responsif untuk BOD.',
                    'grade' => "1. Dashboard live 24/7 tanpa interruption\n2. Agregasi data otomatis dari 4 sub-department\n3. Di-review dan disetujui Board of Directors",
                    'stages' => [
                        ['Requirement gathering dari BOD & penyusunan wireframe visual', 20, 20, -25, -22],
                        ['Pembangunan data pipeline agregasi KPI & pembuatan grafik visual', 35, 35, -21, -14],
                        ['Pemasangan display kiosk mini PC di Ruang Meeting Utama', 25, 25, -13, -8],
                        ['UAT, rehearsal presentasi, dan sign-off Management Review', 20, 20, -7, -5],
                    ],
                    'tasks' => [
                        [
                            'user_code' => 'NRS',
                            'date' => Carbon::now()->subDays(24)->format('Y-m-d'),
                            'desc' => 'Penyelarasan indikator KPI strategis MDP untuk tampilan display BOD',
                            'output' => 'Dokumen spesifikasi metrik dashboard',
                            'hours' => 2.0,
                            'progress' => 100,
                            'status' => 'Completed',
                        ],
                        [
                            'user_code' => 'AHO',
                            'date' => Carbon::now()->subDays(18)->format('Y-m-d'),
                            'desc' => 'Pengembangan query agregasi OEE harian dan integrasi chart visualisasi',
                            'output' => 'Modul chart OEE & Efisiensi Energi live',
                            'hours' => 4.5,
                            'progress' => 100,
                            'status' => 'Completed',
                        ],
                        [
                            'user_code' => 'WAR',
                            'date' => Carbon::now()->subDays(7)->format('Y-m-d'),
                            'desc' => 'Instalasi kiosk browser autostart pada smart TV display Ruang Meeting Utama',
                            'output' => 'TV display otomatis update data setiap 5 menit',
                            'hours' => 3.0,
                            'progress' => 100,
                            'status' => 'Completed',
                        ],
                    ],
                ],
            ];

            // 4. Insert each Ad Hoc project and related entities
            foreach ($adHocsData as $data) {
                $picId = $getUserId($data['pic_code']);
                $subdeptId = $getSubdeptId($data['subdept_code']);
                $skillsetId = $getSkillsetId($data['skillset']);

                $project = MProject::create([
                    'intDepartment_ID' => $dept->intDepartment_ID,
                    'intSubDepartment_ID' => $subdeptId,
                    'intProjectType_ID' => $adHocType->intProjectType_ID,
                    'intSkillset_ID' => $skillsetId,
                    'intUser_ID' => $picId,
                    'txtProjectCode' => $data['code'],
                    'txtProjectName' => $data['name'],
                    'txtKpiLevel' => 'Ad Hoc',
                    'txtDeliverable' => $data['deliverable'],
                    'txtTargetSkalaGrade' => $data['grade'],
                    'intScore' => match($data['status']) {
                        'Completed' => 5,
                        'Under Review' => 4,
                        default => 3,
                    },
                    'txtAchievement' => match($data['status']) {
                        'Completed' => '100% Tercapai Sesuai Sasaran',
                        'Under Review' => 'Menunggu Verifikasi Stakeholder',
                        default => 'Tahapan Aksi Sedang Berjalan',
                    },
                    'floatWeight' => $data['weight'],
                    'bitHasSubProject' => false,
                    'txtDescription' => $data['special_goal'],
                    'dtmProjectStartDate' => Carbon::parse($data['start']),
                    'dtmProjectEndDate' => Carbon::parse($data['end']),
                    'floatPlan' => 100,
                    'floatActual' => 0,
                    'txtStatus' => $data['status'],
                    'txtInsertedBy' => 'seeder',
                    'dtmInserted' => $now,
                    'bitActive' => true,
                    'bitIsAdHoc' => true,
                    'txtAdHocCategory' => $data['category'],
                    'txtPriority' => $data['priority'],
                    'txtSpecialGoal' => $data['special_goal'],
                ]);

                // Team assignments
                $assignedUserIds = collect($data['team_codes'])
                    ->map(fn ($code) => $getUserId($code))
                    ->push($picId)
                    ->unique()
                    ->values();

                foreach ($assignedUserIds as $uId) {
                    TrProjectAssignment::create([
                        'intProject_ID' => $project->intProject_ID,
                        'intSubProject_ID' => null,
                        'intUser_ID' => $uId,
                        'txtInsertedBy' => 'seeder',
                        'dtmInserted' => $now,
                    ]);
                }

                // Action Plan Stages
                foreach ($data['stages'] as $sIdx => [$step, $plan, $actual, $dayOffsetStart, $dayOffsetEnd]) {
                    TrProjectStage::create([
                        'intProject_ID' => $project->intProject_ID,
                        'intSubProject_ID' => null,
                        'intProjectStageNumber' => $sIdx + 1,
                        'txtProjectStageStep' => $step,
                        'dtmProjectStageStartDate' => Carbon::now()->addDays($dayOffsetStart),
                        'dtmProjectStageEndDate' => Carbon::now()->addDays($dayOffsetEnd),
                        'floatProjectStagePlan' => $plan,
                        'floatProjectStageActual' => $actual,
                        'txtInsertedBy' => 'seeder',
                        'dtmInserted' => $now,
                    ]);
                }

                // Recalculate progress based on stages
                $project->recalculateProgress();

                // Daily Tasks associated with this Ad Hoc
                foreach ($data['tasks'] as $task) {
                    $taskUserId = $getUserId($task['user_code']);
                    $userSubdeptId = MUser::find($taskUserId)?->intSubDepartment_ID ?? $subdeptId;

                    TrDailyTask::create([
                        'intUser_ID' => $taskUserId,
                        'intDepartment_ID' => $dept->intDepartment_ID,
                        'intSubDepartment_ID' => $userSubdeptId,
                        'intProjectType_ID' => $adHocType->intProjectType_ID,
                        'intProject_ID' => $project->intProject_ID,
                        'intSubProject_ID' => null,
                        'dtmTaskDate' => Carbon::parse($task['date']),
                        'txtActivityDescription' => $task['desc'],
                        'txtDeliverableOutput' => $task['output'],
                        'floatDurationHours' => $task['hours'],
                        'floatProgressPercent' => $task['progress'],
                        'txtTaskStatus' => $task['status'],
                        'txtNotes' => 'Aktivitas dicatat untuk penanganan Ad Hoc ' . $data['code'],
                        'txtInsertedBy' => 'seeder',
                        'dtmInserted' => $now,
                    ]);
                }
            }
        });
    }
}
