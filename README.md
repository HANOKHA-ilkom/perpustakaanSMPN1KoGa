# Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah
> **Edisi Khusus Tugas Basis Data Lanjut (Advanced Database)**  
> Hasil Re-create dan Modernisasi Skripsi S1 Ilmu Komputer - Universitas Muhammadiyah Metro (2024)  
> Penulis Asli: **Sidik Prastio Utomo** (NPM: 19430028)

---

## 📖 Ringkasan Proyek
Aplikasi Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah berbasis web yang di-recreate dan ditingkatkan mutunya untuk memenuhi kualifikasi mata kuliah **Basis Data Lanjut**. 
Sistem ini mempertahankan **100% tampilan antarmuka (UI), alur fitur, dan layout asli dari skripsi**, namun lapisan basis datanya telah dimodernisasi secara menyeluruh dengan:
1. **Relasi Foreign Key Formal (3NF)** (`id_user`, `id_buku`, `id_kategori`, `id_penerbit`).
2. **Integritas Referensial Kuat** (`ON UPDATE CASCADE`, `ON DELETE RESTRICT`).
3. **Optimasi Indeks (Performance Indexing)** pada kolom pencarian dan pengurutan kunci.
4. **Triggers Otomatis (Database-level Business Logic)** untuk mutasi penambahan dan pengurangan stok buku serta pencegahan peminjaman buku kosong.
5. **Database Views** (`v_buku_detail`, `v_peminjaman_detail`) untuk abstraksi query dan laporan.

---

## 🛠️ Spesifikasi Teknologi
* **Bahasa**: PHP (Murni Native OOP MVC, kompatibel dengan PHP 7.4 s/d PHP 8.2+)
* **Database**: MySQL / MariaDB (Database: `db_perpustakaan`)
* **Arsitektur**: Model-View-Controller (MVC)
* **UI Template**: AdminLTE 3 / Bootstrap 4 dengan navbar biru dan sidebar dark (100% identik skripsi)
* **Icons**: Font Awesome 5

---

## 🗄️ Arsitektur Basis Data Lanjut (`db_perpustakaan`)

* **Dokumentasi Lengkap Diagram & Pemodelan (ERD, Use Case, Activity, Flowchart, PK/FK)**:
  * [`diagram_perpustakaan.html`](diagram_perpustakaan.html) *(Buka langsung di browser atau melalui menu navbar aplikasi)*
* Skrip basis data siap pakai:
  * [`database/db_perpustakaan.sql`](database/db_perpustakaan.sql)
  * [`database/db_perpustakaan_advanced.sql`](database/db_perpustakaan_advanced.sql)

---

## 🚀 Alur Operasional Baru: Meja Sirkulasi Pustakawan & Scanner Kamera

Sistem telah diadaptasi dengan alur nyata perpustakaan sekolah (Front-Desk / Meja Sirkulasi Pustakawan):

### 1. Katalog Publik (OPAC - Online Public Access Catalog) untuk Siswa & Tamu
* Siswa dan pengunjung dapat langsung mengakses **Katalog Buku** di halaman depan (`/katalog`) tanpa perlu login.
* Menampilkan informasi ketersediaan stok fisik real-time (`Tersedia X Buku` atau `Stok Habis`).
* Fitur pencarian buku berdasarkan Judul, Pengarang, Penerbit, Kategori, atau nomor ISBN.
* Tombol **Login Pustakawan** di pojok kanan atas khusus untuk petugas/admin perpustakaan.

### 2. Deteksi Barcode & ISBN Kamera (Webcam Laptop & DroidCam HP)
* Menggunakan library client-side **Html5-QRCode** yang responsif dan ringan.
* Mendukung sumber video fleksibel: **Kamera Internal Laptop** maupun **Kamera HP (via DroidCam Source)** yang terhubung ke PC.
* Dilengkapi pemilih kamera (*camera switcher*) dan bunyi chime feedback saat barcode berhasil dipindai.
* Mendukung pemindaian standar internasional **EAN-13** (barcode ISBN berawalan `978`/`979`), **Code-128**, dan **ISBN-10/13**.

### 3. Sirkulasi Peminjaman Buku
* **Mode Scan & Manual**: Pindai barcode buku atau cari judul buku manual.
* **Validasi Kuota 2 Buku per Siswa**: Sistem otomatis mengecek jumlah pinjaman aktif siswa:
  * Jika siswa sudah meminjam 2 buku yang belum kembali $\rightarrow$ **Peminjaman Ditolak Otomatis**.
  * Jika pinjaman aktif $< 2$ buku $\rightarrow$ Peminjaman disetujui.
* **Trigger Pengurangan Stok**: Stok buku baik otomatis berkurang di database.
* **Cetak Slip Bukti Peminjaman (PDF)**: Tombol instan untuk mengunduh/mencetak slip resmi ber-Kop Surat SMPN 1 Kota Gajah berisi identitas siswa, buku yang dipinjam, tanggal jatuh tempo, dan tanda tangan.

### 4. Sirkulasi Pengembalian Buku
* **Mode Scan & Manual**: Pindai barcode buku yang dikembalikan atau pilih dari tabel transaksi aktif.
* **Kalkulasi Denda Keterlambatan Otomatis**: Jika melewati batas jatuh tempo (7 hari), sistem menghitung denda:
  $$\text{Denda Keterlambatan} = \text{Hari Terlambat} \times \text{Rp } 5.000\text{/hari}$$
* **Pengecekan Kondisi Fisik Buku**:
  * **Kondisi Baik**: Transaksi selesai, stok buku baik bertambah $+1$ (via trigger).
  * **Kondisi Rusak**: Pustakawan memasukkan nominal denda kerusakan buku (total denda diakumulasi). Stok buku rusak bertambah $+1$ (via trigger).
* **Penanganan Buku Belum Pernah Dipinjam (Buku Tanpa Izin)**:
  * Jika buku fisik ada di katalog namun tidak memiliki catatan peminjaman aktif, muncul pop-up: *"Buku belum pernah dipinjam! Apakah siswa tersebut meminjam tanpa sepengetahuan?"*.
  * Jika Ya: Pustakawan dapat menginput denda sanksi, dan stok fisik buku bertambah kembali.

### 5. Tambah Buku Baru via Scan Barcode & ISBN
* Pustakawan cukup mengarahkan kamera ke barcode/ISBN di sampul belakang buku baru.
* Sistem otomatis melakukan *auto-lookup* ke Google Books & Open Library API untuk mengisi otomatis Judul, Pengarang, Penerbit, Tahun Terbit, dan ISBN.
* Pustakawan dapat memeriksa/mengubah detail dan menyimpan buku langsung ke database.

### 1. Relasi Formal & Integritas Referensial
* **`buku`** $\rightarrow$ `kategori`:
  * Foreign Key: `buku.id_kategori` mereferensikan `kategori.id_kategori`
  * Aturan: `ON UPDATE CASCADE`, `ON DELETE RESTRICT` (Mencegah penghapusan kategori buku yang masih digunakan).
* **`buku`** $\rightarrow$ `penerbit`:
  * Foreign Key: `buku.id_penerbit` mereferensikan `penerbit.id_penerbit`
  * Aturan: `ON UPDATE CASCADE`, `ON DELETE RESTRICT`.
* **`peminjaman`** $\rightarrow$ `user`:
  * Foreign Key: `peminjaman.id_user` mereferensikan `user.id_user`
  * Aturan: `ON UPDATE CASCADE`, `ON DELETE RESTRICT` (Mencegah penghapusan akun siswa yang memiliki riwayat pinjam).
* **`peminjaman`** $\rightarrow$ `buku`:
  * Foreign Key: `peminjaman.id_buku` mereferensikan `buku.id_buku`
  * Aturan: `ON UPDATE CASCADE`, `ON DELETE RESTRICT`.

### 2. Performance Indexing
* `idx_user_role` pada `user(role)` & `idx_user_username` pada `user(username)`.
* `idx_buku_kategori` pada `buku(id_kategori)` & `idx_buku_penerbit` pada `buku(id_penerbit)`.
* `idx_pinjam_user` pada `peminjaman(id_user)` & `idx_pinjam_buku` pada `peminjaman(id_buku)`.
* `idx_pinjam_tanggal` pada `peminjaman(tanggal_peminjaman, tanggal_pengembalian)`.

### 3. Triggers Otomatisasi Stok Buku
1. **`trg_sebelum_pinjam_cek_stok` (BEFORE INSERT ON peminjaman)**:
   Mengecek jumlah buku kondisi baik (`j_buku_baik`). Jika bernilai $\le 0$, transaksi dibatalkan seketika oleh basis data via `SIGNAL SQLSTATE '45000'`.
2. **`trg_setelah_pinjam_kurang_stok` (AFTER INSERT ON peminjaman)**:
   Secara otomatis mengurangi jumlah buku baik (`j_buku_baik = j_buku_baik - 1`) pada buku yang dipinjam.
3. **`trg_setelah_kembali_tambah_stok` (AFTER UPDATE ON peminjaman)**:
   Ketika tanggal pengembalian diisi:
   - Jika kondisi saat dikembalikan adalah **'Rusak'**, menambah jumlah stok buku rusak (`j_buku_rusak = j_buku_rusak + 1`).
   - Jika kondisi saat dikembalikan adalah **'Baik'**, menambah stok buku baik (`j_buku_baik = j_buku_baik + 1`).
4. **`trg_setelah_batal_pinjam` (AFTER DELETE ON peminjaman)**:
   Jika transaksi peminjaman aktif dihapus, stok buku dikembalikan otomatis ke kondisi semula.

### 4. Database Views
* **`v_buku_detail`**: Menggabungkan data buku, kategori, dan penerbit secara lengkap beserta kalkulasi total buku.
* **`v_peminjaman_detail`**: Menggabungkan data transaksi peminjaman dengan data peminjam (user) dan buku terkait untuk keperluan laporan dan antarmuka.

---

## 👤 Akun Pengguna untuk Presentasi & Demo

| Peran (Role) | Nama Lengkap | Username | Password | Status Pinjaman Saat Ini | Keterangan Demo |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Administrator** | Administrator Utama | `admin` | `admin` | - | Hak akses penuh admin |
| **Administrator** | Ratna Sari Dewi, S.Pd. | `ratna` | `ratna` | - | Petugas perpustakaan 2 |
| **Siswa** | Sidik Prastio Utomo | `sidik` | `sidik` | **2 Buku Aktif Dipinjam** | Akun utama skripsi |
| **Siswa** | Anisa Rahmawati | `anisa` | `anisa` | *Selesai (Ada Denda Rusak)* | Demo riwayat pengembalian rusak |
| **Siswa** | Bayu Pratama | `bayu` | `bayu` | **1 Buku Aktif Dipinjam** | Demo pengembalian mandiri |
| **Siswa** | Dewi Lestari | `dewi` | `dewi` | **1 Buku Aktif Dipinjam** | Demo peminjaman & pesan masuk |
| **Siswa** | Fajar Nugraha | `fajar` | `fajar` | *Selesai (Kondisi Baik)* | Demo riwayat tanpa denda |

---

## 🎯 Skenario Presentasi yang Disiapkan

1. **Demonstrasi Katalog Publik OPAC (Siswa / Guest)**:
   - Buka halaman depan (`/katalog`) tanpa perlu login.
   - Ketik kata kunci buku, lihat status stok fisik secara transparan dan baca sinopsis lengkap setiap buku di modal detail.
2. **Demonstrasi Peminjaman via Barcode/Kamera (Webcam & DroidCam)**:
   - Login admin $\rightarrow$ Masuk menu **Peminjaman (Scan)** $\rightarrow$ Arahkan kamera ke barcode buku.
   - Coba pilih siswa yang sudah meminjam 2 buku $\rightarrow$ Sistem otomatis menolak karena kuota batas 2 buku terpenuhi.
   - Pilih siswa dengan kuota aman $\rightarrow$ Peminjaman berhasil dicatat, stok berkurang otomatis, dan unduh/cetak **Slip Peminjaman PDF**.
3. **Demonstrasi Pengembalian & Kalkulasi Denda Realtime**:
   - Buka menu **Pengembalian (Scan)** $\rightarrow$ Pindai buku yang terlambat $\rightarrow$ Sistem menghitung denda otomatis (Rp 5.000/hari).
   - Pilih kondisi buku (Baik vs Rusak + denda kerusakan) $\rightarrow$ Stok buku bertambah kembali secara otomatis (via database trigger).
4. **Demonstrasi Tambah Buku Otomatis (Scan ISBN)**:
   - Masuk menu **Data Buku** $\rightarrow$ Scan barcode ISBN buku baru $\rightarrow$ Form terisi otomatis dari Google Books & Open Library API beserta sinopsis.
5. **Demonstrasi Peta Lokasi Leaflet & OpenStreetMap**:
   - Buka menu **Identitas Aplikasi** $\rightarrow$ Cari lokasi spesifik atau geser pin peta $\rightarrow$ Alamat dan koordinat latitude/longitude tersinkronkan otomatis.

---

## 🚀 Cara Menjalankan Aplikasi

### Langkah 1: Setup Database
1. Buka **XAMPP Control Panel**, pastikan modul **MySQL** sudah berstatus **Running** (Hijau).
2. Cukup klik ganda (*double-click*) file:
   ```cmd
   setup_database.bat
   ```
   *(Skrip akan otomatis membuat database `db_perpustakaan`, semua tabel relasional, views, data awal lengkap dengan sinopsis, dan triggers).*

### Langkah 2: Menjalankan Server Web
Cukup klik ganda (*double-click*) file:
```cmd
run_server.bat
```
Lalu buka peramban (*browser*) Anda pada tautan:
```text
http://localhost:8080
```

---

## 📋 Fitur Sistem & Kesesuaian Skripsi

1. **Meja Sirkulasi Pustakawan & Back-Office (Administrator)**:
   - Dashboard Statistik (Anggota, Buku, Peminjaman, Pengembalian) & Profil Sekolah.
   - Master Anggota (Siswa), Penerbit, Administrator.
   - Meja Sirkulasi Peminjaman (Scan Barcode/Kamera DroidCam, validasi 2 buku, cetak Slip PDF).
   - Meja Sirkulasi Pengembalian (Scan Barcode/Kamera, hitung denda otomatis Rp 5.000/hari, opsi Baik/Rusak, penanganan pinjam tanpa izin).
   - Katalog Koleksi Buku dengan nomor ISBN, stok fisik, dan sinopsis lengkap.
   - Identitas Sekolah dengan Peta Interaktif Leaflet.js & Nominatim Geocoding.
2. **Katalog Publik OPAC (Siswa & Pengunjung)**:
   - Akses publik tanpa login untuk pencarian buku dan pengecekan stok fisik.
   - Sinopsis lengkap setiap buku untuk membantu minat baca siswa.
   - Portal masuk khusus pustakawan/admin.
