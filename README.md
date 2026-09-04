# KMI Activity Plan & KPI System

Aplikasi monitoring KPI tahunan, tracking proyek bertingkat (Project, Sub-Project, Stages), Exposure S-Curve, aktivitas harian (Daily Task & Daily Plan), pelaporan bulanan, serta integrasi notifikasi WhatsApp terjadwal untuk departemen **Manufacturing Development & Planning (MDP)** PT Kalbe Morinaga Indonesia (KMI).

---

## 🚀 Fitur Utama

1. **Dashboard KPI & Performance**
   - KPI Summary Cards (Total Projects, Cumulative Exposure Actual vs Plan, Total Weight, Project Status Breakdown).
   - Monthly Exposure Plan vs Actual Chart (Interactive ApexCharts / Chart.js).
   - Sub-Department Project Distribution & Progress Table.
   - Project Scoreboard & Ranking.

2. **Project Management & Tracking**
   - Hierarki 3-level: **Project $\rightarrow$ Sub-Project $\rightarrow$ Stages**.
   - Perhitungan otomatis bobot project (*Weight*), progres aktual (*Actual Progress*), dan status ketercapaian.
   - Filter berdasarkan Sub-Departemen, Tipe Proyek, PIC (Employee), dan Status.

3. **Exposure S-Curve**
   - Visualisasi kurva S kumulatif (Januari – Desember) membandingkan target bulanan (*Plan*) dan realisasi proyek (*Actual*).
   - Tabel breakdown bulanan lengkap dengan delta variansi.

4. **Laporan & Manajemen Aktivitas**
   - **Daily Task (Handsontable Spreadsheet):** Input dan edit massal tugas harian interaktif berbasis spreadsheet excel di web, lengkap dengan fitur filter dan ekspor CSV/Excel.
   - **Daily Plan (Weekly Activity Planner):** Perencanaan aktivitas mingguan (Senin – Jumat) per employee dengan status penyelesaian (*Completed / Pending*).
   - **Monthly Report:** Rekapitulasi performa bulanan employee dan sub-departemen.

5. **Master Data Management** (Admin, Superadmin, Head)
   - Manajemen Departemen & Sub-Departemen (MD/IT, MO/PPIC, AM, MP/Project, dll).
   - Manajemen Tipe Proyek.
   - Manajemen User, NIK, Jabatan, Role, No. HP, dan Email.

6. **WhatsApp Scheduler & Notifikasi** (Superadmin)
   - Penjadwalan reminder pesan WhatsApp otomatis ke employee atau supervisor.
   - Integrasi webhook/API pengiriman WhatsApp dan log histori pengiriman.

7. **REST API v1**
   - Endpoint JSON untuk konsumsi data pihak ketiga atau aplikasi mobile:
     - `GET /api/v1/health` : Health check status API.
     - `GET /api/v1/projects` : Daftar proyek aktif beserta relasi stages dan sub-projek.
     - `GET /api/v1/exposure` : Data kurva S exposure bulanan dan kumulatif.
     - `GET /api/v1/daily-tasks` : Daftar aktivitas tugas harian (filter `employee_id`).
     - `GET /api/v1/weekly-plans` : Daftar rencana mingguan dan aktivitasnya.

---

## 👥 Role & Hak Akses

| Fitur | Employee | Supervisor | Head | Admin | Superadmin |
|---|:---:|:---:|:---:|:---:|:---:|
| **Dashboard** | ✅ (Milik Sendiri) | ✅ (Sub-Dept Terkait) | ✅ (Semua Sub-Dept) | ✅ | ✅ |
| **Project CRUD** | ✅ (Project Sendiri) | ✅ (Sub-Dept Terkait) | ✅ (Semua) | ✅ | ✅ |
| **Exposure S-Curve** | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Daily Tasks & Plans** | ✅ (Aktivitas Sendiri) | ✅ (Aktivitas Sendiri) | ✅ (Aktivitas Sendiri) | ✅ (Semua) | ✅ (Semua) |
| **Master Data** | ❌ | ❌ | ✅ | ✅ | ✅ |
| **WA Scheduler** | ❌ | ❌ | ❌ | ❌ | ✅ |

---

## 🛠️ Prasyarat & Instalasi

### Prasyarat
- **PHP 8.2** atau lebih baru (dengan ekstensi `pdo_pgsql` / `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`)
- **Composer**
- **Node.js** (v18+) & **npm**
- **PostgreSQL** atau **MySQL/MariaDB**

### Langkah Menjalankan Aplikasi di Lokal

1. **Clone Repository & Masuk ke Direktori Proyek:**
   ```bash
   git clone <url-repository>
   cd internship-monitoring
   ```

2. **Install Dependensi PHP & Node.js:**
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi File Environment:**
   Salin `.env.example` ke `.env` dan sesuaikan koneksi database Anda:
   ```bash
   cp .env.example .env
   ```
   *(Pada Windows PowerShell: `Copy-Item .env.example .env`)*

   Pastikan konfigurasi database di `.env` sudah benar:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=KMIAPMDP2026
   DB_USERNAME=postgres
   DB_PASSWORD=root
   ```

4. **Generate Application Key:**
   ```bash
   php artisan key:generate
   ```

5. **Jalankan Migrasi & Database Seeder:**
   ```bash
   php artisan migrate --seed
   ```
   *Seeder akan membuat data default departemen MDP, sub-departemen, tipe proyek, user default per role, serta dummy project & daily plan.*

6. **Build Frontend Assets & Jalankan Server:**
   ```bash
   npm run build
   ```

   Jalankan server aplikasi (bisa dijalankan bersamaan):
   ```bash
   npm run dev:all
   ```
   atau secara terpisah:
   ```bash
   # Terminal 1: Laravel Web Server
   php artisan serve

   # Terminal 2: Vite Dev Server
   npm run dev
   ```

7. Buka browser pada [http://127.0.0.1:8000](http://127.0.0.1:8000).

---

## 🔐 Akun Default Seeder

| Role | Username / NIK | Password Default |
|---|---|---|
| **Superadmin** | `superadmin@kalbe.co.id` / `ADM-001` | `123456` |
| **Dept Head MDP** | `nrs@kalbe.co.id` / `NRS` (Nareswara) | `123456` |
| **Supervisor MD/IT & AM** | `ami@kalbe.co.id` / `AMI` (Amira) | `123456` |
| **Supervisor MO/PPIC & MP** | `snh@kalbe.co.id` / `SNH` (Sania) | `123456` |
| **Employee** | `aho@kalbe.co.id` / `AHO` (Anthony) dll. | `123456` |

---

## 🧪 Pengujian (Testing)

Jalankan pengujian automated Pest/PHPUnit:
```bash
php artisan test
```

---

## 📜 Lisensi & Standar
- Standar penamaan database merujuk pada [KMI_Database_Standard.md](KMI_Database_Standard.md).
- Proprietary software untuk **PT Kalbe Morinaga Indonesia**.
