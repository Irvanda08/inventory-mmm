<?php
include '../config/database.php';

$nama     = mysqli_real_escape_string($conn, $_POST['nama']);
$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

mysqli_query($conn, "
  INSERT INTO users (nama, username, password)
  VALUES ('$nama', '$username', '$password')
");

header("Location: login.php");
exit;
