<?php
include 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: barang.php");
    exit;
}

// Ambil data
$kode_barang   = mysqli_real_escape_string($conn, $_POST['kode_barang']);
$nama_barang   = mysqli_real_escape_string($conn, $_POST['nama_barang']);
$stok_awal     = (int) $_POST['stok_awal'];
$satuan        = mysqli_real_escape_string($conn, $_POST['satuan']);
$tanggal_masuk = $_POST['tanggal_masuk'];
$keterangan    = mysqli_real_escape_string($conn, $_POST['keterangan']);

// Simpan barang
$query_barang = "
  INSERT INTO barang
  (kode_barang, nama_barang, satuan, stok_awal, tanggal_masuk, keterangan)
  VALUES
  ('$kode_barang', '$nama_barang', '$satuan', $stok_awal, '$tanggal_masuk', '$keterangan')
";

if (!mysqli_query($conn, $query_barang)) {
    die("Gagal simpan barang: " . mysqli_error($conn));
}

// Ambil ID barang terakhir
$id_barang = mysqli_insert_id($conn);

// AUTO transaksi stok awal
$query_transaksi = "
  INSERT INTO transaksi_barang
  (id_barang, tanggal, jenis, jumlah, keterangan)
  VALUES
  ($id_barang, '$tanggal_masuk', 'MASUK', $stok_awal, 'Stok Awal')
";

if (!mysqli_query($conn, $query_transaksi)) {
    die("Gagal simpan transaksi stok awal: " . mysqli_error($conn));
}

// Redirect setelah SEMUA sukses
header("Location: barang.php");
exit;
