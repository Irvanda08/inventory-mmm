<?php
include '../config/database.php';

$nama     = mysqli_real_escape_string($conn, $_POST['nama']);
$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

// REVISI: Tambahkan kolom role dan isi dengan 'staff' secara default
mysqli_query($conn, "
  INSERT INTO users (nama, username, password, role)
  VALUES ('$nama', '$username', '$password', 'staff')
");

header("Location: login.php?pendaftaran=sukses");
exit;