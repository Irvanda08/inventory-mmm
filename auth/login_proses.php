<?php
session_start(); // Pastikan session dimulai
include '../config/database.php';

$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = $_POST['password'];

$query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
$user = mysqli_fetch_assoc($query);

if ($user && password_verify($password, $user['password'])) {
    session_regenerate_id(true);

    $_SESSION['user_id']  = $user['id_user'];
    $_SESSION['username'] = $user['username']; // Tambahkan ini agar di sidebar tidak muncul 'A' saja
    $_SESSION['nama']     = $user['nama'];
    $_SESSION['role']     = $user['role']; // SIMPAN ROLE KE SESSION

    header("Location: ../dashboard.php");
    exit;
} else {
    header("Location: login.php?error=1");
    exit;
}