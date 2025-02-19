<?php
$host = "localhost";
$user = "root"; // Ubah jika ada user lain
$pass = ""; // Kosongkan jika pakai default
$dbname = "todolist_db";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Koneksi Gagal: " . $conn->connect_error);
}
?>