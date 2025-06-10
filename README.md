# NewCity-BE - Backend Aplikasi Pelaporan Warga

Proyek ini adalah backend yang dibangun menggunakan Laravel 11 untuk aplikasi "NewCity", sebuah platform yang dirancang untuk menjembatani komunikasi antara masyarakat dan pemerintah melalui sistem pelaporan dan penyebaran informasi.

## Deskripsi Umum

Aplikasi ini memiliki tiga peran pengguna utama:
1.  **Masyarakat**: Pengguna umum yang dapat membuat laporan mengenai isu-isu di lingkungan mereka, berinteraksi dengan laporan, dan mendapatkan berita terbaru.
2.  **Pemerintah**: Entitas pemerintah atau institusi terkait yang bertugas menanggapi dan mengelola laporan yang masuk dari masyarakat.
3.  **Admin**: Super-user yang mengelola data master seperti akun pemerintah, institusi, kategori, dan konten berita.

Backend ini menyediakan RESTful API yang aman untuk digunakan oleh aplikasi frontend (web atau mobile).

---

## Fitur Utama

### Untuk Masyarakat
* **Autentikasi**: Registrasi, login, dan logout yang aman.
* **Manajemen Laporan**:
    * Membuat laporan baru lengkap dengan judul, deskripsi, lokasi, dan foto.
    * Melihat daftar laporan yang telah dibuat.
    * Memperbarui dan menghapus laporan milik sendiri.
    * Memberikan "like" pada laporan.
    * Menambahkan laporan ke daftar "bookmark".
* **Interaksi**:
    * Berpartisipasi dalam ruang diskusi di setiap laporan.
    * Melihat berita dan pengumuman yang dipublikasikan oleh admin.
* **Notifikasi**: Menerima pemberitahuan terkait status laporan, diskusi baru, dan "like" yang diterima.
* **Profil**: Memperbarui data profil pribadi, termasuk foto.

### Untuk Pemerintah
* **Manajemen Laporan**:
    * Melihat daftar laporan yang masuk.
    * Mengubah status laporan (contoh: 'Menunggu', 'Dalam Proses', 'Selesai', 'Ditolak').
    * Berpartisipasi dalam diskusi pada laporan untuk berkomunikasi dengan pelapor.
* **Profil**: Memperbarui data profil dan informasi institusi.

### Untuk Admin
* **Manajemen Pengguna**:
    * Membuat, melihat, memperbarui, dan menghapus akun untuk `Pemerintah`.
    * Mencari akun `Pemerintah` dan `Masyarakat`.
    * Mereset kata sandi pengguna.
* **Manajemen Data Master**:
    * CRUD (Create, Read, Update, Delete) untuk `Institusi` pemerintah.
    * Membuat kategori untuk `Laporan` dan `Berita`.
* **Manajemen Konten**:
    * Membuat, memperbarui, dan menghapus `Berita`.

---

## Teknologi yang Digunakan

* **Framework**: Laravel 11
* **Bahasa**: PHP 8.2+
* **API**: RESTful API dengan otentikasi berbasis token menggunakan Laravel Sanctum.
* **Database**: Konfigurasi standar Laravel yang mendukung MySQL, PostgreSQL, SQLite, dll.
* **Manajemen Gambar**: `intervention/image-laravel` untuk memproses unggahan gambar dan membuat thumbnail.

---

## Panduan Instalasi (Development)

1.  **Clone Repositori**
    ```bash
    git clone https://github.com/proting-newcity/NewCity-BE.git
    cd NewCity-BE
    ```

2.  **Instal Dependensi**
    ```bash
    composer install
    ```

3.  **Konfigurasi Environment**
    Salin file `.env.example` menjadi `.env`.
    ```bash
    cp .env.example .env
    ```

4.  **Generate Kunci Aplikasi**
    Perintah ini wajib dijalankan untuk mengenkripsi data sesi dan data sensitif lainnya.
    ```bash
    php artisan key:generate
    ```

5.  **Konfigurasi Database**
    Buka file `.env` dan sesuaikan variabel koneksi database (`DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).

6.  **Jalankan Migrasi Database**
    Perintah ini akan membuat semua tabel yang dibutuhkan dalam database Anda.
    ```bash
    php artisan migrate
    ```

7.  **Hubungkan Folder Penyimpanan**
    Agar file yang diunggah dapat diakses secara publik, buat symbolic link dari `public/storage` ke `storage/app/public`.
    ```bash
    php artisan storage:link
    ```

8.  **Jalankan Server**
    ```bash
    php artisan serve
    ```
    Server Development akan berjalan di `http://127.0.0.1:8000`.

---

## Daftar Endpoint API

Berikut adalah daftar endpoint API yang tersedia beserta deskripsi dan hak aksesnya.

| Method & Route | Deskripsi | Akses Role |
| :--- | :--- | :--- |
| **Autentikasi** | | |
| `POST /api/register` | Mendaftarkan pengguna baru sebagai Masyarakat atau Pemerintah. | Publik |
| `POST /api/login` | Login pengguna untuk mendapatkan token API. | Publik |
| `POST /api/logout` | Logout pengguna dan membatalkan token saat ini. | Terautentikasi |
| `POST /api/reset-password` | Mereset kata sandi pengguna (dikelola oleh Admin). | Admin |
| `GET /api/user` | Mendapatkan detail pengguna yang sedang login. | Terautentikasi |
| `GET /api/notification` | Mendapatkan notifikasi untuk laporan pengguna. | Masyarakat |
| **Laporan (Report)** | | |
| `GET /api/report` | Mendapatkan daftar laporan yang telah dipaginasi. | Publik |
| `POST /api/report` | Membuat laporan baru. | Masyarakat |
| `GET /api/report/{id}` | Mendapatkan detail satu laporan. | Publik |
| `POST /api/report/{id}` | Memperbarui laporan (hanya pemilik). | Pemilik Laporan |
| `DELETE /api/report/{id}` | Menghapus laporan (hanya pemilik). | Pemilik Laporan |
| `GET /api/report/my` | Mendapatkan daftar laporan milik pengguna yang login. | Masyarakat |
| `POST /api/report/status/{id}` | Menambahkan/memperbarui status sebuah laporan. | Admin, Pemerintah |
| `POST /api/report/like` | Memberikan atau menarik "like" pada laporan. | Terautentikasi |
| `POST /api/report/bookmark` | Menambah atau menghapus bookmark pada laporan. | Masyarakat |
| `GET /api/report/bookmark` | Melihat daftar laporan yang di-bookmark. | Masyarakat |
| `GET /api/report/liked` | Melihat daftar laporan yang di-"like". | Terautentikasi |
| `POST /api/report/diskusi/{id}`| Menambahkan komentar/diskusi baru pada laporan. | Terautentikasi |
| `GET /api/report/diskusi/{id}` | Melihat semua diskusi pada sebuah laporan. | Terautentikasi |
| `GET /api/report/search` | Mencari laporan berdasarkan kata kunci. | Publik |
| **Berita (Berita)** | | |
| `GET /api/berita` | Mendapatkan daftar berita yang telah dipaginasi. | Publik |
| `POST /api/berita` | Membuat berita baru. | Admin |
| `GET /api/berita/{id}` | Mendapatkan detail satu berita. | Publik |
| `POST /api/berita/{id}` | Memperbarui berita. | Admin |
| `DELETE /api/berita/{id}` | Menghapus berita. | Admin |
| `POST /api/berita/like` | Memberikan "like" pada berita. | Terautentikasi |
| `GET /api/berita/search` | Mencari berita berdasarkan kata kunci. | Publik |
| **Manajemen Admin** | | |
| `GET /api/pemerintah` | Mendapatkan daftar semua pengguna Pemerintah. | Admin |
| `POST /api/pemerintah` | Mendaftarkan pengguna Pemerintah baru. | Admin |
| `GET /api/pemerintah/{id}`| Melihat detail satu pengguna Pemerintah. | Admin |
| `POST /api/pemerintah/{id}`| Memperbarui data pengguna Pemerintah. | Admin |
| `DELETE /api/pemerintah/{id}`| Menghapus pengguna Pemerintah. | Admin |
| `GET /api/pemerintah/search`| Mencari pengguna Pemerintah. | Admin |
| `GET /api/masyarakat/search`| Mencari pengguna Masyarakat berdasarkan nomor telepon. | Admin |
| **Data Master** | | |
| `GET /api/institusi` | Mendapatkan daftar semua institusi. | Publik |
| `POST /api/institusi` | Membuat institusi baru. | Admin |
| `PUT /api/institusi/{id}` | Memperbarui nama institusi. | Admin |
| `DELETE /api/institusi/{id}`| Menghapus institusi. | Admin |
| `GET /api/kategori/report` | Mendapatkan semua kategori laporan. | Publik |
| `POST /api/kategori/report`| Membuat kategori laporan baru. | Admin |
| `GET /api/kategori/berita` | Mendapatkan semua kategori berita. | Publik |
| `POST /api/kategori/berita`| Membuat kategori berita baru. | Admin |
