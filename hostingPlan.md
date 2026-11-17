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
    "api/**/*.php": {
      "runtime": "vercel-php@0.7.3"
    },
    "index.php": {
      "runtime": "vercel-php@0.7.3"
    }
  },
  "routes": [
    { "src": "/assets/(.*)", "dest": "/assets/$1" },
    { "src": "/cover_buku/(.*)", "dest": "/cover_buku/$1" },
    { "src": "/(.*)", "dest": "/index.php" }
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
   git remote add origin https://github.com/USERNAME/NAMA_REPO.git
   git push -u origin main
   ```

---

### Tahap 4: Import dan Deploy di Vercel
1. Buka dashboard [vercel.com](https://vercel.com) -> Klik **Add New...** -> **Project**.
2. Pilih repository GitHub perpustakaan Anda -> Klik **Import**.
3. Di bagian **Environment Variables**, tambahkan variabel berikut sesuai kredensial cloud database:
   - `DB_HOST`: *(contoh: gateway01.ap-southeast-1.prod.aws.tidbcloud.com)*
   - `DB_PORT`: *(contoh: 4000)*
   - `DB_NAME`: `db_perpustakaan` (atau nama database di cluster)
   - `DB_USER`: *(user dari TiDB Cloud)*
   - `DB_PASS`: *(password dari TiDB Cloud)*
   - `DB_SSL`: `true`
4. Klik tombol **Deploy**.
5. Tunggu 1–2 menit hingga proses build selesai dan Vercel memberikan domain publik (contoh: `perpus-smpn1.vercel.app`).

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
