<?php
include 'config/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_transaksi = $_POST['id_transaksi'];
    $jenis_baru   = $_POST['jenis'];
    $jumlah_baru  = $_POST['jumlah'];
    $tanggal      = $_POST['tanggal'];
    $keterangan   = $_POST['keterangan'];

    // 1. Ambil data lama untuk perbandingan
    $query_lama = mysqli_query($conn, "SELECT * FROM transaksi_barang WHERE id_transaksi = '$id_transaksi'");
    $data_lama  = mysqli_fetch_assoc($query_lama);
    
    $id_barang   = $data_lama['id_barang'];
    $jumlah_lama = $data_lama['jumlah'];
    $jenis_lama  = trim(strtolower($data_lama['jenis']));

    // 2. Netralkan stok (kembalikan stok ke posisi sebelum transaksi lama terjadi)
    if ($jenis_lama == 'masuk') {
        mysqli_query($conn, "UPDATE barang SET stok_awal = stok_awal - $jumlah_lama WHERE id_barang = '$id_barang'");
    } else {
        mysqli_query($conn, "UPDATE barang SET stok_awal = stok_awal + $jumlah_lama WHERE id_barang = '$id_barang'");
    }

    // 3. Update data transaksi
    $update_transaksi = "UPDATE transaksi_barang SET 
                        jenis = '$jenis_baru', 
                        jumlah = '$jumlah_baru', 
                        tanggal = '$tanggal', 
                        keterangan = '$keterangan' 
                        WHERE id_transaksi = '$id_transaksi'";
    mysqli_query($conn, $update_transaksi);

    // 4. Terapkan stok baru
    if ($jenis_baru == 'masuk') {
        mysqli_query($conn, "UPDATE barang SET stok_awal = stok_awal + $jumlah_baru WHERE id_barang = '$id_barang'");
    } else {
        mysqli_query($conn, "UPDATE barang SET stok_awal = stok_awal - $jumlah_baru WHERE id_barang = '$id_barang'");
    }

    header("Location: transaksi.php?pesan=edit_berhasil");
    exit;
}