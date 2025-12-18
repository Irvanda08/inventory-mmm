<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_gudang";

$conn = mysqli_connect($host, $user, $pass, $db, 4306);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Session aman (tidak double start)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
