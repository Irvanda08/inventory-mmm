<?php
include 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id_barang'];
    $kode = mysqli_real_escape_string($conn, $_POST['kode_barang']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $kategori      = $_POST['kategori'];
    $stok = $_POST['stok_awal']; // Nilai baru dari modal edit
    $satuan = mysqli_real_escape_string($conn, $_POST['satuan']);
    $tgl = $_POST['tanggal_masuk'];
    $ket = mysqli_real_escape_string($conn, $_POST['keterangan']);

    $query = "UPDATE barang SET 
                kode_barang = '$kode', 
                nama_barang = '$nama',
                kategori      = '$kategori', 
                stok_awal = '$stok', 
                satuan = '$satuan', 
                tanggal_masuk = '$tgl', 
                keterangan = '$ket' 
              WHERE id_barang = '$id'";

    if (mysqli_query($conn, $query)) {
        header("Location: barang.php?status=update_berhasil");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}