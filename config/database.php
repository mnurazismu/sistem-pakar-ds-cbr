<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'db_sistem_pakar';

try {
    $conn = mysqli_connect($host, $username, $password, $database);
    if (!$conn) {
        throw new Exception("Koneksi database gagal: " . mysqli_connect_error());
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}