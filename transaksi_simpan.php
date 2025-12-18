<?php
include 'config/database.php';

$id_barang = $_POST['id_barang'];
$jenis     = $_POST['jenis'];
$jumlah    = (int) $_POST['jumlah'];
$tanggal   = $_POST['tanggal'];
$ket       = $_POST['keterangan'];

// Ambil stok saat ini
$q = mysqli_query($conn, "SELECT stok_awal FROM barang WHERE id_barang='$id_barang'");
$b = mysqli_fetch_assoc($q);
$stok = $b['stok_awal'];

// Hitung stok baru
if ($jenis == 'masuk') {
  $stok_baru = $stok + $jumlah;
} else {
  if ($stok < $jumlah) {
    echo "<script>alert('Stok tidak mencukupi');history.back();</script>";
    exit;
  }
  $stok_baru = $stok - $jumlah;
}

// Simpan transaksi
mysqli_query($conn, "
  INSERT INTO transaksi_barang (id_barang, jenis, jumlah, tanggal, keterangan)
  VALUES ('$id_barang', '$jenis', '$jumlah', '$tanggal', '$ket')
");

// Update stok barang
mysqli_query($conn, "
  UPDATE barang SET stok_awal='$stok_baru'
  WHERE id_barang='$id_barang'
");

header("Location: transaksi.php");
