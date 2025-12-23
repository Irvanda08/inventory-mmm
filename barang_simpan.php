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

// Simpan barang - Variabel disamakan menjadi $query_barang
$query_barang = "INSERT INTO barang (kode_barang, nama_barang, kategori, stok_awal, satuan, tanggal_masuk, keterangan) 
                 VALUES ('$kode_barang', '$nama_barang', '$kategori', '$stok_awal', '$satuan', '$tanggal_masuk', '$keterangan')";

// Eksekusi query barang
if (!mysqli_query($conn, $query_barang)) {
    die("Gagal simpan barang: " . mysqli_error($conn));
}

// Ambil ID barang yang baru saja dimasukkan
$id_barang = mysqli_insert_id($conn);

// AUTO transaksi stok awal jika stok lebih dari 0
if ($stok_awal > 0) {
    $query_transaksi = "
      INSERT INTO transaksi_barang
      (id_barang, tanggal, jenis, jumlah, keterangan)
      VALUES
      ($id_barang, '$tanggal_masuk', 'masuk', $stok_awal, 'Stok Awal')
    ";

    if (!mysqli_query($conn, $query_transaksi)) {
        die("Gagal simpan transaksi stok awal: " . mysqli_error($conn));
    }
}

// Redirect setelah SEMUA sukses
header("Location: barang.php?status=success");
exit;
?>