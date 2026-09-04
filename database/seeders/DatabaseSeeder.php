<?php

namespace Database\Seeders;

use App\Models\MDepartment;
use App\Models\MProject;
use App\Models\MProjectType;
use App\Models\MSkillset;
use App\Models\MSubDepartment;
use App\Models\MUser;
use App\Models\MWaSchedule;
use App\Models\MWeeklyPlan;
use App\Models\TrDailyPlanActivity;
use App\Models\TrDailyTask;
use App\Models\TrProjectAssignment;
use App\Models\TrProjectStage;
use App\Models\TrSubProject;
use App\Models\TrSupervisorSubDept;
use App\Support\RoleAccess;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->clearTables();

            $password = Hash::make('123456');
            $now = now();

            // 1. Seed Department
            $deptMdp = MDepartment::create([
                'intDepartment_ID' => 1,
                'txtDepartmentCode' => 'MDP',
                'txtDepartmentName' => 'Manufacturing Development & Planning',
                'txtDescription' => 'Department responsible for manufacturing operational development, planning, and technical automation at Kalbe Nutritionals.',
                'txtInsertedBy' => 'seeder',
                'dtmInserted' => $now,
                'bitActive' => true,
            ]);

            // 2. Seed Sub-Departments
            $subDepts = [
                1 => ['code' => 'MD/IT', 'name' => 'Manufacturing Development (IT & Software)', 'desc' => 'Manufacturing software engineering, AI agents, and IT systems.'],
                2 => ['code' => 'MO/PPIC', 'name' => 'Manufacturing Operation & PPIC', 'desc' => 'Production planning, inventory control, and operational execution.'],
                3 => ['code' => 'AM', 'name' => 'Advanced Manufacturing', 'desc' => 'Automation, machine intelligence, and advanced IoT technologies.'],
                4 => ['code' => 'MP/Project', 'name' => 'Manufacturing Planning & Project Management', 'desc' => 'Department project orchestration and strategic planning.'],
            ];

            foreach ($subDepts as $id => $sd) {
                MSubDepartment::create([
                    'intSubDepartment_ID' => $id,
                    'intDepartment_ID' => $deptMdp->intDepartment_ID,
                    'txtSubDepartmentCode' => $sd['code'],
                    'txtSubDepartmentName' => $sd['name'],
                    'txtDescription' => $sd['desc'],
                    'txtInsertedBy' => 'seeder',
                    'dtmInserted' => $now,
                    'bitActive' => true,
                ]);
            }

            // 3. Seed Project Types
            $projectTypes = [
                1 => ['code' => 'IPP', 'name' => 'Individual Performance Plan', 'weight' => 25, 'color' => '#006838', 'icon' => 'fa-solid fa-bullseye'],
                2 => ['code' => 'IDP', 'name' => 'Individual Development Planning', 'weight' => 20, 'color' => '#2563EB', 'icon' => 'fa-solid fa-graduation-cap'],
                3 => ['code' => 'MAR BERSAMA', 'name' => 'MAR Bersama (Cross Dept Collaboration)', 'weight' => 15, 'color' => '#7C3AED', 'icon' => 'fa-solid fa-people-arrows'],
                4 => ['code' => 'Routine', 'name' => 'Routine Operations', 'weight' => 20, 'color' => '#F59E0B', 'icon' => 'fa-solid fa-rotate'],
                5 => ['code' => 'Ad Hoc', 'name' => 'Ad Hoc & Strategic Initiatives', 'weight' => 20, 'color' => '#0D9488', 'icon' => 'fa-solid fa-bolt'],
            ];

            foreach ($projectTypes as $id => $pt) {
                MProjectType::create([
                    'intProjectType_ID' => $id,
                    'txtProjectTypeCode' => $pt['code'],
                    'txtProjectTypeName' => $pt['name'],
                    'txtDescription' => $pt['name'] . ' KPI project classification',
                    'floatDefaultWeight' => $pt['weight'],
                    'txtColor' => $pt['color'],
                    'txtIcon' => $pt['icon'],
                    'txtInsertedBy' => 'seeder',
                    'dtmInserted' => $now,
                    'bitActive' => true,
                ]);
            }

            // 3b. Seed Skillsets
            $skillsets = [
                1 => ['name' => 'Web Development', 'desc' => 'Pengembangan web app full-stack, frontend modern, RESTful API & integrasi sistem.', 'color' => '#2563EB', 'icon' => 'fa-solid fa-globe'],
                2 => ['name' => 'AI & Computer Vision', 'desc' => 'Implementasi Machine Learning, Deep Learning, Computer Vision, OCR & AI Agents.', 'color' => '#7C3AED', 'icon' => 'fa-solid fa-brain'],
                3 => ['name' => 'Embedded Systems & IoT Data Acquisition', 'desc' => 'Mikrokontroler, telemetri sensor, PLC, akuisisi data mesin & automasi industri IoT.', 'color' => '#0D9488', 'icon' => 'fa-solid fa-microchip'],
                4 => ['name' => 'Mobile App Development', 'desc' => 'Pengembangan aplikasi mobile berbasis Android, iOS, maupun multiplatform Flutter.', 'color' => '#0891B2', 'icon' => 'fa-solid fa-mobile-screen-button'],
                5 => ['name' => 'Data Engineering & Analytics', 'desc' => 'ETL pipeline data processing, data warehousing, Business Intelligence & visualisasi data.', 'color' => '#D97706', 'icon' => 'fa-solid fa-chart-line'],
                6 => ['name' => 'Automation & RPA', 'desc' => 'Robotic Process Automation, scripting Python/bash, dan otomatisasi alur kerja operasional.', 'color' => '#EA580C', 'icon' => 'fa-solid fa-robot'],
                7 => ['name' => 'Cloud Infrastructure & DevOps', 'desc' => 'Server management, containerization Docker, CI/CD pipeline, dan cloud infrastructure.', 'color' => '#4F46E5', 'icon' => 'fa-solid fa-cloud'],
                8 => ['name' => 'Cybersecurity & Network Systems', 'desc' => 'Keamanan jaringan, security audit, konfigurasi firewall, VPN & server hardening.', 'color' => '#DC2626', 'icon' => 'fa-solid fa-shield-halved'],
            ];

            foreach ($skillsets as $id => $sk) {
                MSkillset::create([
                    'intSkillset_ID' => $id,
                    'txtSkillsetName' => $sk['name'],
                    'txtDescription' => $sk['desc'],
                    'txtBadgeColor' => $sk['color'],
                    'txtIcon' => $sk['icon'],
                    'txtInsertedBy' => 'seeder',
                    'dtmInserted' => $now,
                    'bitActive' => true,
                ]);
            }

            // 4. Seed Users
            // Superadmin
            $superadmin = MUser::create([
                'intUser_ID' => 1,
                'intDepartment_ID' => $deptMdp->intDepartment_ID,
                'intSubDepartment_ID' => null,
                'txtEmployeeCode' => 'ADM-001',
                'txtEmployeeName' => 'Superadmin KMI',
                'txtEmail' => 'superadmin@kalbe.co.id',
                'txtPassword' => $password,
                'txtPhone' => '6281234567800',
                'txtRole' => RoleAccess::ROLE_SUPERADMIN,
                'txtPosition' => 'System Administrator',
                'txtInsertedBy' => 'seeder',
                'dtmInserted' => $now,
                'bitActive' => true,
            ]);

            // Department Head MDP (NRS)
            $head = MUser::create([
                'intUser_ID' => 5,
                'intDepartment_ID' => $deptMdp->intDepartment_ID,
                'intSubDepartment_ID' => null,
                'txtEmployeeCode' => 'NRS',
                'txtEmployeeName' => 'Nareswara (NRS)',
                'txtEmail' => 'nrs@kalbe.co.id',
                'txtPassword' => $password,
                'txtPhone' => '6281234567810',
                'txtRole' => RoleAccess::ROLE_HEAD,
                'txtPosition' => 'Department Head MDP',
                'txtInsertedBy' => 'seeder',
                'dtmInserted' => $now,
                'bitActive' => true,
            ]);

            // Supervisors:
            // AMI - Supervisor MD/IT & AM
            $spvAmi = MUser::create([
                'intUser_ID' => 10,
                'intDepartment_ID' => $deptMdp->intDepartment_ID,
                'intSubDepartment_ID' => 1, // MD/IT
                'txtEmployeeCode' => 'AMI',
                'txtEmployeeName' => 'Amira (AMI)',
                'txtEmail' => 'ami@kalbe.co.id',
                'txtPassword' => $password,
                'txtPhone' => '6281234567815',
                'txtRole' => RoleAccess::ROLE_SUPERVISOR,
                'txtPosition' => 'Supervisor MD/IT & AM',
                'txtInsertedBy' => 'seeder',
                'dtmInserted' => $now,
                'bitActive' => true,
            ]);

            // SNH - Supervisor MO/PPIC & MP/Project
            $spvSnh = MUser::create([
                'intUser_ID' => 7,
                'intDepartment_ID' => $deptMdp->intDepartment_ID,
                'intSubDepartment_ID' => 2, // MO/PPIC
                'txtEmployeeCode' => 'SNH',
                'txtEmployeeName' => 'Sania (SNH)',
                'txtEmail' => 'snh@kalbe.co.id',
                'txtPassword' => $password,
                'txtPhone' => '6281234567812',
                'txtRole' => RoleAccess::ROLE_SUPERVISOR,
                'txtPosition' => 'Supervisor MO/PPIC & MP/Project',
                'txtInsertedBy' => 'seeder',
                'dtmInserted' => $now,
                'bitActive' => true,
            ]);

            // Map Supervisors to multiple sub-departments
            // AMI supervises MD/IT (1) and AM (3)
            TrSupervisorSubDept::create(['intSupervisorSubDept_ID' => 1, 'intUser_ID' => 10, 'intSubDepartment_ID' => 1, 'txtInsertedBy' => 'seeder', 'dtmInserted' => $now]);
            TrSupervisorSubDept::create(['intSupervisorSubDept_ID' => 2, 'intUser_ID' => 10, 'intSubDepartment_ID' => 3, 'txtInsertedBy' => 'seeder', 'dtmInserted' => $now]);
            // SNH supervises MO/PPIC (2) and MP/Project (4)
            TrSupervisorSubDept::create(['intSupervisorSubDept_ID' => 3, 'intUser_ID' => 7, 'intSubDepartment_ID' => 2, 'txtInsertedBy' => 'seeder', 'dtmInserted' => $now]);
            TrSupervisorSubDept::create(['intSupervisorSubDept_ID' => 4, 'intUser_ID' => 7, 'intSubDepartment_ID' => 4, 'txtInsertedBy' => 'seeder', 'dtmInserted' => $now]);

            // Other Employees (from PDF reference sheet codes: YJN, JHN, AHO, NTO, IHP, DDI, TSJ, WAR, YFG)
            $employeesData = [
                6 => ['code' => 'YJN', 'name' => 'Yayan (YJN)', 'email' => 'yjn@kalbe.co.id', 'subdept' => 2, 'phone' => '6281234567811', 'pos' => 'PPIC Specialist'],
                8 => ['code' => 'JHN', 'name' => 'Johan (JHN)', 'email' => 'jhn@kalbe.co.id', 'subdept' => 4, 'phone' => '6281234567813', 'pos' => 'Manufacturing Planner'],
                9 => ['code' => 'AHO', 'name' => 'Anthony (AHO)', 'email' => 'aho@kalbe.co.id', 'subdept' => 1, 'phone' => '6281234567814', 'pos' => 'AI Developer'],
                11 => ['code' => 'NTO', 'name' => 'Nanto (NTO)', 'email' => 'nto@kalbe.co.id', 'subdept' => 2, 'phone' => '6281234567816', 'pos' => 'Operations Officer'],
                12 => ['code' => 'IHP', 'name' => 'Irpan Hidayat (IHP)', 'email' => 'ihp@kalbe.co.id', 'subdept' => 4, 'phone' => '6281234567817', 'pos' => 'Project Specialist'],
                13 => ['code' => 'DDI', 'name' => 'Dedi (DDI)', 'email' => 'ddi@kalbe.co.id', 'subdept' => 1, 'phone' => '6281234567818', 'pos' => 'DevOps Engineer'],
                14 => ['code' => 'TSJ', 'name' => 'Tisya (TSJ)', 'email' => 'tsj@kalbe.co.id', 'subdept' => 3, 'phone' => '6281234567819', 'pos' => 'IoT Specialist'],
                15 => ['code' => 'WAR', 'name' => 'Wahyu Agus (WAR)', 'email' => 'war@kalbe.co.id', 'subdept' => 1, 'phone' => '6281234567820', 'pos' => 'Lead Software Engineer'],
                16 => ['code' => 'YFG', 'name' => 'Yoga (YFG)', 'email' => 'yfg@kalbe.co.id', 'subdept' => 1, 'phone' => '6281234567821', 'pos' => 'Infrastructure Engineer'],
            ];

            foreach ($employeesData as $uid => $emp) {
                MUser::create([
                    'intUser_ID' => $uid,
                    'intDepartment_ID' => $deptMdp->intDepartment_ID,
                    'intSubDepartment_ID' => $emp['subdept'],
                    'txtEmployeeCode' => $emp['code'],
                    'txtEmployeeName' => $emp['name'],
                    'txtEmail' => $emp['email'],
                    'txtPassword' => $password,
                    'txtPhone' => $emp['phone'],
                    'txtRole' => RoleAccess::ROLE_EMPLOYEE,
                    'txtPosition' => $emp['pos'],
                    'txtInsertedBy' => 'seeder',
                    'dtmInserted' => $now,
                    'bitActive' => true,
                ]);
            }

            // 5. Seed Projects (from the uploaded sheet)
            $projectsData = [
                1 => [
                    'code' => 'PRJ-2026-001',
                    'name' => 'Smart Maintenance (Predictive Maintenance Early warning notification Ampere load to WA)',
                    'kpi_level' => 'Department',
                    'deliverable' => '100% implement Machine Rank A',
                    'grade' => "1. 80%\n2. 85%\n3. 90%\n4. 95%\n5. 100%",
                    'score' => 3,
                    'achievement' => '85%',
                    'weight' => 10,
                    'type_id' => 1, // IPP
                    'user_id' => 5, // NRS
                    'subdept_id' => 1,
                    'has_sub' => false,
                    'stages' => [
                        ['Requirement & Machine Mapping', 20, 20],
                        ['Sensor Ampere Installation', 30, 30],
                        ['WA Alert Notification Integration', 30, 25],
                        ['Evaluation & Commissioning', 20, 10],
                    ],
                ],
                2 => [
                    'code' => 'PRJ-2026-002',
                    'name' => 'Increase output through productivity improvement & cost efficiency project (Reduce Electrical Consumption Chiller, Compressor, Ice water, WWTP)',
                    'kpi_level' => 'Department',
                    'deliverable' => 'Electric Cons (KWH)',
                    'grade' => "1. 300rb\n2. 400rb\n3. 500rb\n4. 600rb\n5. 700rb",
                    'score' => 3,
                    'achievement' => '500rb',
                    'weight' => 10,
                    'type_id' => 1, // IPP
                    'user_id' => 7, // SNH
                    'subdept_id' => 3,
                    'has_sub' => false,
                    'stages' => [
                        ['Baseline Energy Audit', 25, 25],
                        ['Chiller & Compressor Tuning', 35, 35],
                        ['Ice Water & WWTP Optimization', 25, 20],
                        ['Efficiency Reporting', 15, 10],
                    ],
                ],
                3 => [
                    'code' => 'PRJ-2026-003',
                    'name' => 'Smart Maintenance (Digital Twin Chiller and AHU)',
                    'kpi_level' => 'Department',
                    'deliverable' => 'Score matric',
                    'grade' => "1. 1-3\n2. 4-6\n3. 7-9\n4. 10-12\n5. 13-15",
                    'score' => 3,
                    'achievement' => '7-9',
                    'weight' => 10,
                    'type_id' => 1, // IPP
                    'user_id' => 14, // TSJ
                    'subdept_id' => 3,
                    'has_sub' => false,
                    'stages' => [
                        ['Digital Twin 3D Modeling', 30, 30],
                        ['Telemetry Sensor Linkage', 30, 30],
                        ['Dashboard Analytics Model', 25, 15],
                        ['Validation with Engineering', 15, 5],
                    ],
                ],
                4 => [
                    'code' => 'PRJ-2026-004',
                    'name' => 'Smart Maintenance (Prescriptive Energy Management Electrical)',
                    'kpi_level' => 'Department',
                    'deliverable' => 'Implement',
                    'grade' => "1. Jan 27\n2. Dec 26\n3. Nov 26\n4. Oct 26\n5. Sep 26",
                    'score' => 1,
                    'achievement' => 'Jan 27',
                    'weight' => 10,
                    'type_id' => 1, // IPP
                    'user_id' => 6, // YJN
                    'subdept_id' => 2,
                    'has_sub' => false,
                    'stages' => [
                        ['Architecture Setup', 25, 25],
                        ['Algorithm Development', 35, 10],
                        ['Plant Integration', 25, 0],
                        ['Handover', 15, 0],
                    ],
                ],
                5 => [
                    'code' => 'PRJ-2026-005',
                    'name' => 'Improve the inventory management process to make it easier and more organized (Inventory ATK and Smart Locker)',
                    'kpi_level' => 'Department',
                    'deliverable' => '% project implemented',
                    'grade' => "1. >20%\n2. 20%-40%\n3. 40%-60%\n4. 61%-70%\n5. 81%-100%",
                    'score' => 1,
                    'achievement' => '>20%',
                    'weight' => 10,
                    'type_id' => 4, // Routine
                    'user_id' => 11, // NTO
                    'subdept_id' => 2,
                    'has_sub' => false,
                    'stages' => [
                        ['Smart Locker Procurement', 40, 40],
                        ['Software Integration', 30, 10],
                        ['Inventory ATK Go-Live', 30, 0],
                    ],
                ],
                6 => [
                    'code' => 'PRJ-2026-006',
                    'name' => 'Excellent Operation for high Quality Result (New 5S Monitoring and OEE System Sachet-A)',
                    'kpi_level' => 'Department',
                    'deliverable' => '% project implemented',
                    'grade' => "1. Score <= 2\n2. 2 < Score <= 4\n3. 4 < Score <= 6\n4. 6 < Score <= 8\n5. 8 < Score <= 10",
                    'score' => 2,
                    'achievement' => '2 < Score <= 4',
                    'weight' => 10,
                    'type_id' => 1, // IPP
                    'user_id' => 8, // JHN
                    'subdept_id' => 4,
                    'has_sub' => false,
                    'stages' => [
                        ['5S Digital Audit Setup', 30, 30],
                        ['OEE Sachet-A Connectivity', 40, 20],
                        ['Genba Trial & Review', 30, 10],
                    ],
                ],
                7 => [
                    'code' => 'PRJ-2026-007',
                    'name' => 'Operating Excellent (MIS)',
                    'kpi_level' => 'Department',
                    'deliverable' => '% project implemented',
                    'grade' => "1. Score <= 8\n2. 8 < Score <= 12\n3. 12 < Score <= 16\n4. 16 < Score <= 20\n5. 20 < Score <= 24",
                    'score' => 3,
                    'achievement' => '12 < Score <= 16',
                    'weight' => 15,
                    'type_id' => 1, // IPP
                    'user_id' => 12, // IHP
                    'subdept_id' => 4,
                    'has_sub' => false,
                    'stages' => [
                        ['Data Warehouse Setup', 25, 25],
                        ['MIS Dashboard Build', 35, 30],
                        ['Report Automation', 25, 20],
                        ['Handover to Management', 15, 10],
                    ],
                ],
                8 => [
                    'code' => 'PRJ-2026-008',
                    'name' => 'AI Agent (Vibe Coding, KIMI, RPA Orange)',
                    'kpi_level' => 'Individu',
                    'deliverable' => 'Pilot Project',
                    'grade' => "1. x <= 60%\n2. 60% < x <= 70%\n3. 70% < x <= 80%\n4. 80% < x <= 90%\n5. 90% < x <= 100%",
                    'score' => 4,
                    'achievement' => '80% < x <= 90%',
                    'weight' => 10,
                    'type_id' => 2, // IDP
                    'user_id' => 9, // AHO
                    'subdept_id' => 1,
                    'has_sub' => true,
                    'sub_projects' => [
                        [
                            'name' => 'Vibe Coding AI Assistant',
                            'weight' => 35,
                            'score' => 4,
                            'achievement' => '85%',
                            'progress' => 85,
                            'stages' => [
                                ['Prompt & Model Evaluation', 30, 30],
                                ['IDE Agent Integration', 40, 35],
                                ['Developer Trial & Feedback', 30, 20],
                            ],
                        ],
                        [
                            'name' => 'KIMI AI Agent',
                            'weight' => 35,
                            'score' => 4,
                            'achievement' => '80%',
                            'progress' => 80,
                            'stages' => [
                                ['Knowledge Base Embedding', 35, 35],
                                ['Query Engine Tuning', 35, 30],
                                ['Deployment to Production', 30, 15],
                            ],
                        ],
                        [
                            'name' => 'RPA Orange Workflow Automation',
                            'weight' => 30,
                            'score' => 4,
                            'achievement' => '90%',
                            'progress' => 90,
                            'stages' => [
                                ['Process Workflow Mapping', 30, 30],
                                ['Orange Bot Scripting', 40, 40],
                                ['UAT & Production Run', 30, 20],
                            ],
                        ],
                    ],
                ],
                9 => [
                    'code' => 'PRJ-2026-009',
                    'name' => 'MOM (Manufacturing Operation Management)',
                    'kpi_level' => 'Individu',
                    'deliverable' => 'Pilot Project',
                    'grade' => "1. x <= 60%\n2. 60% < x <= 70%\n3. 70% < x <= 80%\n4. 80% < x <= 90%\n5. 90% < x <= 100%",
                    'score' => 3,
                    'achievement' => '70% < x <= 80%',
                    'weight' => 10,
                    'type_id' => 2, // IDP
                    'user_id' => 15, // WAR
                    'subdept_id' => 1,
                    'has_sub' => false,
                    'stages' => [
                        ['System Architecture Design', 25, 25],
                        ['MOM Core Modules Coding', 40, 30],
                        ['Integration with ERP Oracle', 20, 15],
                        ['Pilot User Training', 15, 5],
                    ],
                ],
                10 => [
                    'code' => 'PRJ-2026-010',
                    'name' => 'Score MAR Bersama Cross Departmen (CSI - FI)',
                    'kpi_level' => 'MAR Bersama',
                    'deliverable' => 'Matrix Bersama',
                    'grade' => "1. X <= 5\n2. 6 <= X < 11\n3. 11 <= X < 16\n4. 16 <= X < 21\n5. 21 <= X <= 25",
                    'score' => 3,
                    'achievement' => '11 <= X < 16',
                    'weight' => 5,
                    'type_id' => 3, // MAR BERSAMA
                    'user_id' => 10, // AMI
                    'subdept_id' => 1,
                    'has_sub' => false,
                    'stages' => [
                        ['CSI - FI Cross Dept Alignment', 30, 30],
                        ['Execution Collaboration Sprint', 40, 30],
                        ['Final Matrix Presentation', 30, 15],
                    ],
                ],
            ];

            $startDate = Carbon::create(2026, 1, 5);
            $endDate = Carbon::create(2026, 12, 20);

            foreach ($projectsData as $pId => $p) {
                $project = MProject::create([
                    'intProject_ID' => $pId,
                    'intDepartment_ID' => $deptMdp->intDepartment_ID,
                    'intSubDepartment_ID' => $p['subdept_id'],
                    'intProjectType_ID' => $p['type_id'],
                    'intUser_ID' => $p['user_id'],
                    'txtProjectCode' => $p['code'],
                    'txtProjectName' => $p['name'],
                    'txtKpiLevel' => $p['kpi_level'],
                    'txtDeliverable' => $p['deliverable'],
                    'txtTargetSkalaGrade' => $p['grade'],
                    'intScore' => $p['score'],
                    'txtAchievement' => $p['achievement'],
                    'floatWeight' => $p['weight'],
                    'bitHasSubProject' => $p['has_sub'],
                    'txtDescription' => $p['name'] . ' KPI activity monitoring for MDP department.',
                    'dtmProjectStartDate' => $startDate,
                    'dtmProjectEndDate' => $endDate,
                    'floatPlan' => 100,
                    'floatActual' => 0,
                    'txtStatus' => 'In Progress',
                    'txtInsertedBy' => 'seeder',
                    'dtmInserted' => $now,
                    'bitActive' => true,
                ]);

                if ($p['has_sub'] && ! empty($p['sub_projects'])) {
                    $nextSubId = ($pId * 10) + 1;
                    $nextStageId = ($pId * 100) + 1;

                    foreach ($p['sub_projects'] as $subIdx => $sub) {
                        $subProject = TrSubProject::create([
                            'intSubProject_ID' => $nextSubId,
                            'intProject_ID' => $project->intProject_ID,
                            'txtSubProjectName' => $sub['name'],
                            'txtDeliverable' => 'Deliverable for ' . $sub['name'],
                            'txtTargetSkalaGrade' => $p['grade'],
                            'intScore' => $sub['score'],
                            'txtAchievement' => $sub['achievement'],
                            'floatWeight' => $sub['weight'],
                            'floatProgress' => $sub['progress'],
                            'dtmStartDate' => $startDate,
                            'dtmEndDate' => $endDate,
                            'txtStatus' => 'In Progress',
                            'txtInsertedBy' => 'seeder',
                            'dtmInserted' => $now,
                        ]);

                        // Sub-project assignments
                        $subAssignees = match ($sub['name']) {
                            'Vibe Coding' => [5, 9], // NRS, AHO
                            'KIMI Agent' => [9, 10], // AHO, AMI
                            'RPA Orange Workflow Automation' => [6, 7], // TGR, SNH
                            default => [$p['user_id']],
                        };
                        foreach ($subAssignees as $assigneeId) {
                            TrProjectAssignment::create([
                                'intProject_ID' => $project->intProject_ID,
                                'intSubProject_ID' => $subProject->intSubProject_ID,
                                'intUser_ID' => $assigneeId,
                                'txtInsertedBy' => 'seeder',
                                'dtmInserted' => $now,
                            ]);
                        }

                        foreach ($sub['stages'] as $sIdx => [$step, $plan, $actual]) {
                            TrProjectStage::create([
                                'intProjectStage_ID' => $nextStageId,
                                'intProject_ID' => $project->intProject_ID,
                                'intSubProject_ID' => $subProject->intSubProject_ID,
                                'intProjectStageNumber' => $sIdx + 1,
                                'txtProjectStageStep' => $step,
                                'dtmProjectStageStartDate' => $startDate->copy()->addMonths($sIdx * 3),
                                'dtmProjectStageEndDate' => $startDate->copy()->addMonths(($sIdx + 1) * 3),
                                'floatProjectStagePlan' => $plan,
                                'floatProjectStageActual' => $actual,
                                'txtInsertedBy' => 'seeder',
                                'dtmInserted' => $now,
                            ]);
                            $nextStageId++;
                        }
                        $subProject->recalculateProgress();
                        $nextSubId++;
                    }
                } else {
                    // Single project assignments
                    $assignees = [$p['user_id']];
                    if ($pId === 1) {
                        $assignees[] = 9; // Also assign AHO
                    } elseif ($pId === 9) {
                        $assignees[] = 13; // Also assign DDI
                    }
                    foreach ($assignees as $assigneeId) {
                        TrProjectAssignment::create([
                            'intProject_ID' => $project->intProject_ID,
                            'intSubProject_ID' => null,
                            'intUser_ID' => $assigneeId,
                            'txtInsertedBy' => 'seeder',
                            'dtmInserted' => $now,
                        ]);
                    }

                    if (! empty($p['stages'])) {
                        $nextStageId = ($pId * 100) + 1;
                        foreach ($p['stages'] as $sIdx => [$step, $plan, $actual]) {
                            TrProjectStage::create([
                                'intProjectStage_ID' => $nextStageId,
                                'intProject_ID' => $project->intProject_ID,
                                'intSubProject_ID' => null,
                                'intProjectStageNumber' => $sIdx + 1,
                                'txtProjectStageStep' => $step,
                                'dtmProjectStageStartDate' => $startDate->copy()->addMonths($sIdx * 3),
                                'dtmProjectStageEndDate' => $startDate->copy()->addMonths(($sIdx + 1) * 3),
                                'floatProjectStagePlan' => $plan,
                                'floatProjectStageActual' => $actual,
                                'txtInsertedBy' => 'seeder',
                                'dtmInserted' => $now,
                            ]);
                            $nextStageId++;
                        }
                    }
                }

                $project->recalculateProgress();
            }

            // 6. Seed Daily Tasks (Spreadsheet samples)
            $dailyTasks = [
                ['user_id' => 5, 'project_id' => 1, 'date' => '2026-08-10', 'desc' => 'SENTUL Plant Ampere Check & Cable Measurement', 'output' => 'Report & Ampere log', 'hours' => 2.0, 'progress' => 85, 'status' => 'Completed'],
                ['user_id' => 5, 'project_id' => 1, 'date' => '2026-08-11', 'desc' => 'SENTUL WA Gateway Webhook Configuration', 'output' => 'Webhook active', 'hours' => 3.0, 'progress' => 90, 'status' => 'Completed'],
                ['user_id' => 7, 'project_id' => 2, 'date' => '2026-08-11', 'desc' => 'Baseline Energy Audit & Chiller Optimization Analysis', 'output' => 'Energy Audit Report', 'hours' => 3.0, 'progress' => 80, 'status' => 'Completed'],
                ['user_id' => 7, 'project_id' => 2, 'date' => '2026-08-12', 'desc' => 'Coordination Meeting MO & PPIC Team Schedule Sync', 'output' => 'Action Plan & MoM', 'hours' => 2.0, 'progress' => 100, 'status' => 'Completed'],
                ['user_id' => 9, 'project_id' => 8, 'sub_id' => 81, 'date' => '2026-08-03', 'desc' => 'Workshop I2MS (E-Promise) Integration test', 'output' => 'API payload mapped', 'hours' => 3.0, 'progress' => 80, 'status' => 'Completed'],
                ['user_id' => 9, 'project_id' => 8, 'sub_id' => 82, 'date' => '2026-08-04', 'desc' => 'Project Management & Prompt engineering for KIMI Agent', 'output' => 'Tested 10 prompts', 'hours' => 4.0, 'progress' => 75, 'status' => 'In Progress'],
                ['user_id' => 10, 'project_id' => 10, 'date' => '2026-08-06', 'desc' => 'Training WD & Kalventis Prepare Go Live', 'output' => 'Training log signed', 'hours' => 3.0, 'progress' => 100, 'status' => 'Completed'],
                ['user_id' => 12, 'project_id' => 7, 'date' => '2026-08-10', 'desc' => 'RENE SHE Bootcamp Preparation & Data extraction', 'output' => 'MIS Dataset', 'hours' => 2.5, 'progress' => 80, 'status' => 'Completed'],
                ['user_id' => 13, 'project_id' => 9, 'date' => '2026-08-05', 'desc' => 'DTI 2026 Server & Proxmox Configuration', 'output' => 'HA Proxmox active', 'hours' => 3.5, 'progress' => 90, 'status' => 'Completed'],
            ];

            foreach ($dailyTasks as $tIdx => $t) {
                TrDailyTask::create([
                    'intDailyTask_ID' => $tIdx + 1,
                    'intUser_ID' => $t['user_id'],
                    'intDepartment_ID' => $deptMdp->intDepartment_ID,
                    'intSubDepartment_ID' => MUser::find($t['user_id'])?->intSubDepartment_ID,
                    'intProject_ID' => $t['project_id'],
                    'intSubProject_ID' => $t['sub_id'] ?? null,
                    'dtmTaskDate' => Carbon::parse($t['date']),
                    'txtActivityDescription' => $t['desc'],
                    'txtDeliverableOutput' => $t['output'],
                    'floatDurationHours' => $t['hours'],
                    'floatProgressPercent' => $t['progress'],
                    'txtTaskStatus' => $t['status'],
                    'txtNotes' => 'Daily Task logged via KMI Activity Plan',
                    'txtInsertedBy' => 'seeder',
                    'dtmInserted' => $now,
                ]);
            }

            // 7. Seed Weekly Plan & Mon-Fri Daily Plan Activities (from PDF OCR)
            $weeklyPlans = [
                1 => ['user_id' => 5, 'title' => 'Week 1: 10 Aug - 14 Aug 2026', 'start' => '2026-08-10', 'end' => '2026-08-14'],
                2 => ['user_id' => 9, 'title' => 'Week 1: 03 Aug - 07 Aug 2026', 'start' => '2026-08-03', 'end' => '2026-08-07'],
                3 => ['user_id' => 10, 'title' => 'Week 2: 06 Jul - 10 Jul 2026', 'start' => '2026-07-06', 'end' => '2026-07-10'],
                4 => ['user_id' => 12, 'title' => 'Week 1: 10 Aug - 14 Aug 2026', 'start' => '2026-08-10', 'end' => '2026-08-14'],
                5 => ['user_id' => 15, 'title' => 'Week 2: 10 Aug - 14 Aug 2026', 'start' => '2026-08-10', 'end' => '2026-08-14'],
                6 => ['user_id' => 7, 'title' => 'Week 1: 10 Aug - 14 Aug 2026', 'start' => '2026-08-10', 'end' => '2026-08-14'],
            ];

            $actId = 1;
            foreach ($weeklyPlans as $wId => $wp) {
                $plan = MWeeklyPlan::create([
                    'intWeeklyPlan_ID' => $wId,
                    'intUser_ID' => $wp['user_id'],
                    'intDepartment_ID' => $deptMdp->intDepartment_ID,
                    'txtWeekTitle' => $wp['title'],
                    'dtmWeekStartDate' => Carbon::parse($wp['start']),
                    'dtmWeekEndDate' => Carbon::parse($wp['end']),
                    'txtTargetGoals' => 'Complete scheduled activities on time and sync with project KPI milestones.',
                    'txtStatus' => 'Submitted',
                    'txtInsertedBy' => 'seeder',
                    'dtmInserted' => $now,
                    'bitActive' => true,
                ]);

                // Daily activities for Week 1 (NRS / 5)
                if ($wId === 1) {
                    $nrsActivities = [
                        ['Senin', '2026-08-10', '08:00', '10:00', 2.0, 'SENTUL Plant Ampere Inspection', 'SENTUL', 1],
                        ['Senin', '2026-08-10', '10:00', '12:00', 2.0, 'SENTUL Machine Rank A Calibration', 'SENTUL', 1],
                        ['Senin', '2026-08-10', '13:00', '15:00', 2.0, 'SENTUL Data Gathering', 'SENTUL', 1],
                        ['Selasa', '2026-08-11', '08:00', '10:00', 2.0, 'SENTUL WA Gateway Setup', 'SENTUL', 1],
                        ['Selasa', '2026-08-11', '10:00', '11:00', 1.0, 'SENTUL Alert Testing', 'SENTUL', 1],
                        ['Rabu', '2026-08-12', '08:00', '09:00', 1.0, 'SENTUL Review Meeting', 'SENTUL', 1],
                        ['Rabu', '2026-08-12', '09:00', '12:00', 3.0, 'SENTUL Troubleshooting', 'SENTUL', 1],
                        ['Kamis', '2026-08-13', '08:00', '10:00', 2.0, 'Mekor Plant Visit', 'Mekor', 1],
                        ['Kamis', '2026-08-13', '10:00', '13:00', 3.0, 'Weekly Meeting PRD, PPIC, ENG', 'Mekor', 1],
                        ['Jumat', '2026-08-14', '08:00', '10:00', 2.0, 'KMI Factory Sync', 'KMI', 1],
                        ['Jumat', '2026-08-14', '10:00', '12:00', 2.0, 'Weekly Summary & S-Curve Reporting', 'KMI', 1],
                    ];

                    foreach ($nrsActivities as [$day, $date, $st, $et, $dur, $actName, $loc, $prjId]) {
                        TrDailyPlanActivity::create([
                            'intDailyPlanActivity_ID' => $actId++,
                            'intWeeklyPlan_ID' => $plan->intWeeklyPlan_ID,
                            'intUser_ID' => $wp['user_id'],
                            'intProject_ID' => $prjId,
                            'intSubProject_ID' => null,
                            'dtmActivityDate' => Carbon::parse($date),
                            'txtDayName' => $day,
                            'txtStartTime' => $st,
                            'txtEndTime' => $et,
                            'floatDuration' => $dur,
                            'txtActivityName' => $actName,
                            'txtLocationType' => $loc,
                            'txtRemarks' => 'Logged via Weekly Activity Plan',
                            'bitIsCompleted' => true,
                            'txtInsertedBy' => 'seeder',
                            'dtmInserted' => $now,
                        ]);
                    }
                }

                // Daily activities for Week 2 (AHO / 9)
                if ($wId === 2) {
                    $ahoActivities = [
                        ['Senin', '2026-08-03', '08:00', '10:00', 2.0, 'Workshop I2MS (E-Promise)', 'Online', 8],
                        ['Senin', '2026-08-03', '10:00', '13:00', 3.0, 'Workshop I2MS Session 2', 'Online', 8],
                        ['Senin', '2026-08-03', '13:00', '16:30', 3.5, 'Project Management & Prompting', 'Office', 8],
                        ['Selasa', '2026-08-04', '08:00', '10:00', 2.0, 'Document Project Management', 'Office', 8],
                        ['Selasa', '2026-08-04', '10:00', '14:00', 4.0, 'AI Agent Architecture Mapping', 'Office', 8],
                        ['Rabu', '2026-08-05', '08:00', '10:00', 2.0, 'Internal Meeting AI CDT', 'Meeting Room', 8],
                        ['Rabu', '2026-08-05', '10:00', '13:00', 3.0, 'Document Project Management review', 'Office', 8],
                        ['Kamis', '2026-08-06', '08:00', '10:00', 2.0, 'Expo IEAE Presentation', 'Expo Hall', 8],
                        ['Kamis', '2026-08-06', '10:00', '13:00', 3.0, 'Expo IEAE Demo', 'Expo Hall', 8],
                        ['Jumat', '2026-08-07', '08:00', '10:00', 2.0, 'Internal Meeting & Sprint review', 'Meeting Room', 8],
                        ['Jumat', '2026-08-07', '10:00', '13:00', 3.0, 'Project Monitoring & Learning AI', 'Office', 8],
                    ];

                    foreach ($ahoActivities as [$day, $date, $st, $et, $dur, $actName, $loc, $prjId]) {
                        TrDailyPlanActivity::create([
                            'intDailyPlanActivity_ID' => $actId++,
                            'intWeeklyPlan_ID' => $plan->intWeeklyPlan_ID,
                            'intUser_ID' => $wp['user_id'],
                            'intProject_ID' => $prjId,
                            'intSubProject_ID' => 81,
                            'dtmActivityDate' => Carbon::parse($date),
                            'txtDayName' => $day,
                            'txtStartTime' => $st,
                            'txtEndTime' => $et,
                            'floatDuration' => $dur,
                            'txtActivityName' => $actName,
                            'txtLocationType' => $loc,
                            'txtRemarks' => 'Logged via Weekly Activity Plan',
                            'bitIsCompleted' => true,
                            'txtInsertedBy' => 'seeder',
                            'dtmInserted' => $now,
                        ]);
                    }
                }

                // Daily activities for Week 1 (SNH / 7)
                if ($wId === 6) {
                    $snhActivities = [
                        ['Senin', '2026-08-10', '08:00', '11:00', 3.0, 'Energy Audit Plant Chiller & Compressor', 'SENTUL', 2],
                        ['Selasa', '2026-08-11', '09:00', '12:00', 3.0, 'WWTP & Ice Water Electrical Tuning', 'SENTUL', 2],
                        ['Rabu', '2026-08-12', '10:00', '12:00', 2.0, 'Coordination Meeting MO & PPIC Team', 'Meeting Room', 2],
                        ['Kamis', '2026-08-13', '08:30', '11:30', 3.0, 'Supervision MP Project & Timeline Review', 'Office', 2],
                        ['Jumat', '2026-08-14', '13:30', '15:30', 2.0, 'Weekly KPI Evaluation & S-Curve Reporting', 'Office', 2],
                    ];

                    foreach ($snhActivities as [$day, $date, $st, $et, $dur, $actName, $loc, $prjId]) {
                        TrDailyPlanActivity::create([
                            'intDailyPlanActivity_ID' => $actId++,
                            'intWeeklyPlan_ID' => $plan->intWeeklyPlan_ID,
                            'intUser_ID' => $wp['user_id'],
                            'intProject_ID' => $prjId,
                            'intSubProject_ID' => null,
                            'dtmActivityDate' => Carbon::parse($date),
                            'txtDayName' => $day,
                            'txtStartTime' => $st,
                            'txtEndTime' => $et,
                            'floatDuration' => $dur,
                            'txtActivityName' => $actName,
                            'txtLocationType' => $loc,
                            'txtRemarks' => 'Logged via Weekly Activity Plan',
                            'bitIsCompleted' => true,
                            'txtInsertedBy' => 'seeder',
                            'dtmInserted' => $now,
                        ]);
                    }
                }
            }

            // 8. Seed WhatsApp Schedules
            MWaSchedule::create([
                'intWaSchedule_ID' => 1,
                'txtScheduleTitle' => 'Friday Morning Activity Plan & Daily Task Reminder',
                'txtCronDay' => 'Friday',
                'txtScheduledTime' => '07:00',
                'txtMessageTemplate' => "Selamat Pagi {employee_name}!\n\nDiingatkan untuk mengisi dan memperbarui Daily Task serta Weekly Plan Anda di sistem KMI Activity Plan ({department}) sebelum closing jam 17:00 hari ini.\n\nTerima kasih atas dedikasi dan kerja samanya!",
                'txtFooterText' => 'Sent via KMI Activity Plan',
                'txtTargetType' => 'all',
                'intSubDepartment_ID' => null,
                'txtTargetRole' => null,
                'dtmLastSentAt' => now()->subDays(4),
                'txtInsertedBy' => 'seeder',
                'dtmInserted' => $now,
                'bitActive' => true,
            ]);

            MWaSchedule::create([
                'intWaSchedule_ID' => 2,
                'txtScheduleTitle' => 'Monday Weekly Plan Kickoff Reminder',
                'txtCronDay' => 'Monday',
                'txtScheduledTime' => '08:00',
                'txtMessageTemplate' => "Selamat Pagi {employee_name}!\n\nSemangat memulai minggu baru di {department}. Pastikan Weekly Card dan jadwal aktivitas harian (Senin-Jumat) sudah dibuat di sistem KMI Activity Plan.\n\nHave a productive week!",
                'txtFooterText' => 'Sent via KMI Activity Plan',
                'txtTargetType' => 'all',
                'intSubDepartment_ID' => null,
                'txtTargetRole' => null,
                'dtmLastSentAt' => now()->subDays(1),
                'txtInsertedBy' => 'seeder',
                'dtmInserted' => $now,
                'bitActive' => true,
            ]);
        });
    }

    private function clearTables(): void
    {
        TrDailyPlanActivity::query()->delete();
        MWeeklyPlan::query()->delete();
        TrDailyTask::query()->delete();
        TrProjectStage::query()->delete();
        TrSubProject::query()->delete();
        MProject::query()->delete();
        TrSupervisorSubDept::query()->delete();
        MUser::query()->delete();
        MSkillset::query()->delete();
        MProjectType::query()->delete();
        MSubDepartment::query()->delete();
        MDepartment::query()->delete();
        MWaSchedule::query()->delete();
    }
}
