# Panduan & Rencana Hosting Web Perpustakaan ke Vercel (Opsi A)
**Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah**  
*Mata Kuliah: Basis Data Lanjut (Advanced Database)*

---

## 1. Arsitektur Deployment (Opsi A)

Karena Vercel adalah platform *Serverless* yang tidak memiliki layanan database MySQL internal, arsitektur dibagi menjadi 2 komponen terpisah:

```
[ Klien / Browser ] 
        │
        ▼ (HTTPS)
[ Vercel Serverless Function ] ───▶ (Menjalankan PHP Native via vercel-php)
        │
        ▼ (TCP / SSL Port: 4000 / 3306)
[ Cloud Database MySQL ] ─────────▶ (TiDB Cloud Serverless / Aiven MySQL)
                                    - Menyimpan Tabel 3NF
                                    - Menjalankan 4 Active Database Triggers
                                    - Menjalankan 2 Database Views
```

---

## 2. Kebutuhan & Requirements

1. **Akun GitHub:** Untuk menyimpan repository kode sumber proyek.
2. **Akun Vercel:** Terhubung dengan akun GitHub (bebas biaya / Hobby plan).
3. **Akun Cloud MySQL (Pilih salah satu - Gratis):**
   - **Rekomendasi Utama: TiDB Cloud Serverless** ([tidbcloud.com](https://tidbcloud.com))
     - *Gratis 5GB selamanya, 100% kompatibel dengan MySQL 8.0, mendukung Triggers, Views, & InnoDB ACID.*
   - **Alternatif:** Aiven for MySQL ([aiven.io](https://aiven.io)) atau Railway ([railway.app](https://railway.app)).
4. **Git CLI / GitHub Desktop:** Untuk melakukan *commit* dan *push* kode ke GitHub.
5. **File Dump Database (`.sql`):** Berisi skema, data master, triggers, dan views.

---

## 3. Penyesuaian Berkas Proyek (Technical Adjustments)

### A. Berkas `vercel.json` (Wajib Dibuat di Root)
Menginstruksikan Vercel agar menjalankan kode PHP melalui runtime serverless dan merutekan semua URL ke `index.php`.

```json
{
  "functions": {
    "api/index.php": {
      "runtime": "vercel-php@0.7.3"
    }
  },
  "routes": [
    { "src": "/assets/(.*)", "dest": "/assets/$1" },
    { "src": "/cover_buku/(.*)", "dest": "/cover_buku/$1" },
    { "src": "/diagram_perpustakaan.html", "dest": "/diagram_perpustakaan.html" },
    { "src": "/(.*)", "dest": "/api/index.php" }
  ]
}
```

### B. Penyesuaian `config/database.php` (Mendukung Environment Variables & SSL)
Agar aplikasi dapat otomatis membaca konfigurasi cloud saat di Vercel, namun tetap berjalan normal saat dibuka di XAMPP lokal:

```php
<?php
// Konfigurasi Dinamis: Cloud (Vercel) atau Lokal (XAMPP)
return [
    'host'     => getenv('DB_HOST') ?: '127.0.0.1',
    'port'     => (int)(getenv('DB_PORT') ?: 3306),
    'username' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASS') !== false ? getenv('DB_PASS') : '',
    'database' => getenv('DB_NAME') ?: 'db_perpustakaan',
    'charset'  => 'utf8mb4',
    'ssl'      => getenv('DB_SSL') === 'true'
];
```

---

## 4. Tahapan Pelaksanaan Langkah demi Langkah

### Tahap 1: Export Database Lokal dari phpMyAdmin / MySQL
1. Buka browser: `http://localhost/phpmyadmin`.
2. Pilih database `db_perpustakaan`.
3. Klik tab **Export** -> Pilih metode **Custom**.
4. Pada bagian *Object creation options*, **centang**:
   - `Add CREATE TRIGGER statement`
   - `Add CREATE VIEW statement`
5. Klik **Go / Kirim** dan simpan file sebagai `db_perpustakaan_backup.sql`.

---

### Tahap 2: Pembuatan Database di Cloud (Contoh: TiDB Cloud)
1. Daftar / login di [tidbcloud.com](https://tidbcloud.com).
2. Klik **Create Cluster** -> Pilih **Serverless (Free $0)**.
3. Tentukan nama cluster (contoh: `perpus-smpn1`).
4. Setelah cluster aktif, klik **Connect**:
   - Catat: **Host**, **Port** (biasanya `4000`), **User**, dan buat **Password**.
   - Buka web console TiDB Cloud (tab *SQL Editor*) atau gunakan aplikasi DBeaver / Navicat / HeidiSQL.
5. Jalankan query SQL dari `db_perpustakaan_backup.sql` untuk membuat tabel, triggers, dan views di TiDB Cloud.

---

### Tahap 3: Persiapan Repository GitHub
1. Pastikan berkas `vercel.json` dan penyesuaian `config/database.php` sudah diterapkan di proyek.
2. Buat berkas `.gitignore` agar file temporer/rahasia tidak ter-upload:
   ```gitignore
   /scratch/
   *.log
   .env
   ```
3. Inisialisasi git dan push ke GitHub:
   ```bash
   git init
   git add .
   git commit -m "Siap deploy Vercel + TiDB Cloud"
   git branch -M main
   git remote add origin https://github.com/HANOKHA-ilkom/perpustakaanSMPN1KoGa.git
   git push -u origin main
   ```

---

### Tahap 4: Import dan Deploy di Vercel (Panduan Rinci Langkah demi Langkah)

1. **Navigasi ke Halaman Import Proyek:**
   - Buka browser dan login ke [vercel.com](https://vercel.com) menggunakan akun GitHub Anda.
   - Pada halaman utama Dashboard Vercel, klik tombol **"Add New..."** di pojok kanan atas, lalu pilih **"Project"**.

2. **Memilih Repositori (Import Git Repository):**
   - Pada kolom *Import Git Repository*, cari nama repositori Anda (contoh: `perpustakaanSMPN1KoGa`).
   - *(Tips jika repositori tidak muncul)*: Klik dropdown akun GitHub di samping pencarian -> pilih **Configure GitHub App** -> pastikan repositori perpustakaan sudah dicentang izin aksesnya -> klik **Save**.
   - Klik tombol **"Import"** berwarna biru di samping repositori tersebut.

3. **Konfigurasi Project Settings:**
   - **Project Name:** Nama subdomain Vercel Anda (misal: `perpus-smpn1-kotagajah`). Nama ini yang akan menjadi alamat URL Anda: `https://perpus-smpn1-kotagajah.vercel.app`.
   - **Framework Preset:** Biarkan terisi **"Other"** (Vercel akan otomatis mengenali konfigurasi dari berkas `vercel.json`).
   - **Root Directory:** Tetap `./` (default, tidak perlu diubah).
   - **Build and Output Settings:** Biarkan kosong / non-aktif (tidak perlu `Build Command` atau `Output Directory` karena ditangani langsung oleh runtime `vercel-php`).

4. **Pengisian Environment Variables (Bagian Paling Krusial):**
   - Klik / buka bagian accordion **"Environment Variables"**.
   - Masukkan pasangan **Key** dan **Value** berikut satu per satu (setiap selesai memasukkan satu baris, klik tombol **"Add"**):

     | Key | Contoh Value | Keterangan |
     |---|---|---|
     | `DB_HOST` | `gateway01.ap-southeast-1.prod.aws.tidbcloud.com` | Hostname cluster TiDB Cloud / Aiven Anda |
     | `DB_PORT` | `4000` | Port database (TiDB memakai `4000`, standar MySQL memakai `3306`) |
     | `DB_NAME` | `db_perpustakaan` | Nama database yang Anda buat di Cloud |
     | `DB_USER` | `xxxxxx.root` | Username akun cluster cloud Anda |
     | `DB_PASS` | `PasswordClusterAnda123` | Password cluster cloud Anda |
     | `DB_SSL` | `true` | **Wajib diisi `true`** agar koneksi terenkripsi SSL aktif |

     *(Catatan Alternatif: Jika penyedia cloud memberikan satu baris Connection String utuh, Anda juga bisa cukup mengisi `DATABASE_URL` dengan format `mysql://user:pass@host:port/db_perpustakaan`).*

5. **Eksekusi Deployment:**
   - Klik tombol biru **"Deploy"** di bagian bawah.
   - Anda akan dialihkan ke layar proses *Building*. Vercel akan otomatis:
     - Mengunduh dependensi runtime serverless `vercel-php@0.7.3`.
     - Mengompilasi rute `vercel.json` dan menghubungkan jembatan `api/index.php`.
   - Tunggu sekitar 1–2 menit sampai muncul pesan animasi perayaan (*Congratulations!*).

6. **Membuka Aplikasi & Menangani Troubleshooting Log:**
   - Klik gambar cuplikan tampilan web (*preview thumbnail*) atau klik tombol **"Continue to Dashboard"** lalu buka domain publik yang diberikan.
   - **Jika terjadi kendala / error 500:** Buka tab **Deployments** -> klik deployment yang aktif -> pilih tab **Functions / Runtime Logs** untuk melihat pesan error log PHP secara langsung.

---

## 5. Catatan Penting Mengenai Karakteristik Vercel

> [!IMPORTANT]
> **1. File Upload Bersifat Stateless (Read-Only):**
> Vercel Serverless tidak memiliki harddisk permanen. Cover buku hasil scan otomatis via ISBN (Google Books API) akan aman karena menggunakan URL online. Namun jika admin mengupload file gambar manual di form tambah buku, file akan hilang saat instance serverless restart.
> 
> **2. Integritas Basis Data Lanjut Tetap 100% Berjalan:**
> Seluruh fitur penilaian mata kuliah Basis Data Lanjut (Trigger pencegah stok kosong, mutasi stok otomatis saat kembali/hilang, serta Database Views) tetap dieksekusi sempurna oleh mesin database cloud.

---

## 6. Checklist Verifikasi Setelah Deploy
- [ ] Halaman login terbuka dengan normal.
- [ ] Login sebagai Administrator (`admin` / `admin123`) berhasil.
- [ ] Login sebagai Siswa (`sidik` / `siswa123`) berhasil.
- [ ] Katalog Publik OPAC menampilkan data buku, cover online, dan live search.
- [ ] Uji transaksi peminjaman & pengembalian (memastikan Trigger cloud berjalan).
- [ ] Halaman pemodelan `diagram_perpustakaan.html` dapat diakses langsung secara online.
