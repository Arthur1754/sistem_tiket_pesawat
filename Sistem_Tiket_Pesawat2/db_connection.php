<?php
// Add robust error reporting directly in the connection file for thorough debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "root"; // Ganti dengan username database Anda
$password = "";     // Ganti dengan password database Anda
$dbname = "sistem_tiket_pesawat";


// Buat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if $conn is even an object
if (!($conn instanceof mysqli)) {
    die("Error: \$conn is not a mysqli object after connection attempt. This typically means the new mysqli() call failed critically.<br>");
}

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error . "<br>"); // Debug line 2
}

// IMPORTANT: Remove or comment out these debug echos and ini_set lines once connection is confirmed!
?>