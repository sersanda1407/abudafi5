<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "pos_app";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set timezone ke Indonesia
date_default_timezone_set('Asia/Jakarta');
?>