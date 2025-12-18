<?php
include 'config/database.php';

$id = $_POST['id_barang'];
$kode = $_POST['kode_barang'];
$nama = $_POST['nama_barang'];
$stok = (int) $_POST['stok_awal'];
$satuan = $_POST['satuan'];
$tanggal = $_POST['tanggal_masuk'];
$ket = $_POST['keterangan'];

$query = "UPDATE barang SET
  kode_barang='$kode',
  nama_barang='$nama',
  stok_awal=$stok,
  satuan='$satuan',
  tanggal_masuk='$tanggal',
  keterangan='$ket'
WHERE id_barang=$id";

mysqli_query($conn, $query);

header("Location: barang.php");
exit;
