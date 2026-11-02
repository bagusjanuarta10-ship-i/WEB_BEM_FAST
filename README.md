# Aplikasi Organisasi Mahasiswa BEM FST

Aplikasi web PHP Native dengan pola Model-View-Controller (MVC) untuk mengelola anggota, kegiatan, divisi, jabatan, dan akun pengguna organisasi mahasiswa.

## Fitur

- CRUD anggota/keanggotaan
- CRUD kegiatan
- CRUD divisi dan jabatan
- CRUD akun serta pengaturan peran pengguna
- Login, logout, registrasi, dan ganti password
- Pencarian dan pagination anggota
- Navigasi dan footer berbasis partial
- Tampilan responsif dengan Bootstrap dan CSS tema oranye gelap
- Validasi input dan perlindungan CSRF

## Persyaratan

- PHP 8 atau lebih baru
- MySQL/MariaDB
- Laragon atau server lokal lain
- Browser web

## Instalasi

1. Pindahkan atau clone repository ke folder web Laragon, misalnya `C:\laragon\www\mahasiswa`.
2. Jalankan Laragon dan aktifkan Apache serta MySQL.
3. Pastikan MySQL berjalan pada port `3307`.
4. Buat/import database melalui phpMyAdmin atau terminal MySQL:

   ```sql
   SOURCE C:/laragon/www/mahasiswa/database/organisasi_mahasiswa.sql;
   ```

   File SQL juga dapat diimpor melalui menu **Import** di phpMyAdmin.
5. Sesuaikan koneksi database jika diperlukan pada:

   ```text
   config/database.php
   ```

   Nilai bawaan:

   ```php
   host: 127.0.0.1
   port: 3307
   database: organisasi_mahasiswa
   username: root
   password: kosong
   ```

6. Buka aplikasi melalui:

   ```text
   http://localhost/mahasiswa/index.php
   ```

## Penggunaan Akun

Pilih menu **Login / Daftar** untuk membuat akun. Akun registrasi bawaan memiliki peran `user`.

Agar dapat mengelola data, akun harus memiliki peran `admin`. Ubah peran melalui tabel `users` pada phpMyAdmin atau menu **Users** jika sudah login sebagai admin.

## Struktur Folder

```text
controller/  Controller: Anggota, Kegiatan, Tentang, Divisi, Jabatan, dan autentikasi
models/      Model database sesuai nama fitur navigasi
views/       Tampilan PHP beserta partial navigasi dan footer
config/      Konfigurasi database dan autentikasi
database/    File SQL struktur dan data awal database
index.php    Router utama aplikasi
```

## Teknologi

- PHP Native
- MySQL
- PDO
- Bootstrap 5.3
- HTML5 dan CSS3
