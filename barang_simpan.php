<?php
include 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: barang.php");
    exit;
}

// Ambil data dan proteksi dari SQL Injection
$kode_barang   = mysqli_real_escape_string($conn, $_POST['kode_barang']);
$nama_barang   = mysqli_real_escape_string($conn, $_POST['nama_barang']);
// Tambahkan proteksi string untuk kategori
$kategori      = mysqli_real_escape_string($conn, $_POST['kategori']); 
$stok_awal     = (int) $_POST['stok_awal'];
$satuan        = mysqli_real_escape_string($conn, $_POST['satuan']);
$tanggal_masuk = $_POST['tanggal_masuk'];
$keterangan    = mysqli_real_escape_string($conn, $_POST['keterangan']);

// File: barang_simpan.php

// REVISI: Paksa stok_awal di tabel barang menjadi 0 saat INSERT pertama kali
$query_barang = "INSERT INTO barang (kode_barang, nama_barang, kategori, stok_awal, satuan, tanggal_masuk, keterangan) 
                 VALUES ('$kode_barang', '$nama_barang', '$kategori', 0, '$satuan', '$tanggal_masuk', '$keterangan')";

if (!mysqli_query($conn, $query_barang)) {
    die("Gagal simpan barang: " . mysqli_error($conn));
}

$id_barang = mysqli_insert_id($conn);

// Transaksi otomatis inilah yang akan menjadi pengisi saldo di barang.php 
// karena transaksi_simpan.php Anda akan mengupdate master secara otomatis.
if ($stok_awal > 0) {
    // Panggil file transaksi_simpan secara logic atau jalankan query update manual di sini
    $query_transaksi = "INSERT INTO transaksi_barang (id_barang, tanggal, jenis, jumlah, keterangan)
                        VALUES ($id_barang, '$tanggal_masuk', 'masuk', $stok_awal, 'Stok Awal')";
    mysqli_query($conn, $query_transaksi);

    // Update Master Barang agar barang.php langsung terisi angkanya
    mysqli_query($conn, "UPDATE barang SET stok_awal = $stok_awal WHERE id_barang = $id_barang");
}

// Redirect setelah SEMUA sukses
header("Location: barang.php?status=success");
exit;
?>