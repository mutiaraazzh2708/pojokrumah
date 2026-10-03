# Cafe Pojok Rumah

Website aplikasi pemesanan makanan untuk Cafe Pojok Rumah.

## Fitur
- **Autentikasi**: Login & Register untuk User dan Admin.
- **Katalog Menu**: Menampilkan menu makanan dan minuman dengan kategori.
- **Keranjang Belanja**: Sistem pemesanan yang mudah.
- **Manajemen Pesanan**: Admin dapat mengubah status pesanan (Pending, Proses, Selesai).
- **Laporan Sederhana**: Dashboard admin menampilkan ringkasan penjualan.

## Instalasi
1. Pastikan XAMPP sudah terinstall dan MySQL berjalan.
2. Buat database baru di phpMyAdmin bernama `cafe_pojok`.
3. Import file `database.sql` yang ada di folder root project ini ke dalam database tersebut.
4. Akses website di browser: `http://localhost/pbl/public/`

## Cara Membuat Akun Admin
Secara default, semua pendaftar baru adalah **Customer**. Untuk membuat akun Admin:
1. Buka halaman Register di website dan buat akun baru.
2. Buka **phpMyAdmin**, cari tabel `users`.
3. Temukan user yang baru Anda buat, dan ubah kolom `role` dari `'customer'` menjadi `'admin'`.
4. Logout dan Login kembali. Anda akan diarahkan ke Dashboard Admin.
