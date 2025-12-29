# 📦 Warehouse Inventory System - PT Muara Mitra Mandiri

Sistem manajemen inventaris berbasis web yang dirancang khusus untuk mengelola stok material konstruksi dan operasional gudang di PT Muara Mitra Mandiri secara real-time dan akurat.

## 🚀 Fitur Utama

### 🔐 Manajemen Akses Multi-Role (RBAC)
Sistem membedakan hak akses untuk menjaga keamanan data:
* **Admin Gudang**: Akses penuh untuk manajemen barang (CRUD), input transaksi masuk/keluar, dan melihat seluruh laporan.
* **Staff Gudang**: Akses terbatas (Read-only) hanya untuk memantau Dashboard dan melihat/mencetak laporan inventaris.

### 📋 Inventaris & Kategori
* **Pengelompokan Barang**: Mendukung kategori spesifik seperti Material Listrik, Plumbing, Besi, K3 Proyek, hingga Kendaraan Konstruksi.
* **Logika Penyesuaian Otomatis**: Setiap perubahan manual pada master barang akan otomatis tercatat sebagai transaksi "Penyesuaian Stok" untuk menjaga validitas laporan.

### 🔄 Arus Barang & Transaksi
* Pencatatan transaksi masuk dan keluar dengan sistem *automated stock update*.
* **Keamanan Transaksi**: Menggunakan *database transaction (commit/rollback)* untuk mencegah data korup saat terjadi error sistem.

### 📊 Pelaporan & Cetak (Print Optimized)
* **Laporan Stok Akhir**: Dilengkapi filter tanggal histori dan kategori.
* **Kartu Gudang**: Histori mendetail per jenis barang.
* **Optimasi Cetak**: Laporan didesain rapat dan profesional untuk penghematan kertas saat diprint.

## 🛠️ Tech Stack
* **Backend**: PHP (Procedural)
* **Frontend**: Tailwind CSS (Responsive Design)
* **Database**: MySQL
* **Server/Hosting**: InfinityFree (Environment Produksi)

## 📁 Struktur Folder Utama
```text
├── auth/               # Sistem Autentikasi (Login & Register)
├── config/             # Konfigurasi Database
├── img/                # Aset Gambar & Logo Perusahaan
├── templates/          # Header & Sidebar (Reusable Components)
├── barang.php          # Manajemen Master Barang (Admin Only)
├── transaksi.php       # Pencatatan Arus Barang (Admin Only)
└── laporan_*.php       # Berbagai Modul Pelaporan (Staff & Admin)

⚙️ Instalasi Lokal (Development)
Clone repositori:

Bash

git clone [https://github.com/username/inventory-mmm.git](https://github.com/username/inventory-mmm.git)
Pastikan server lokal (XAMPP/Laragon) aktif.

Import database db_gudang.sql (jika tersedia) melalui phpMyAdmin.

Sesuaikan kredensial database di file config/database.php.

Akses melalui browser: http://localhost/inventory-mmm

👥 Kontributor
Dewangga - Lead Developer & Integrasi Sistem

© 2025 PT Muara Mitra Mandiri - Warehouse Management System.
