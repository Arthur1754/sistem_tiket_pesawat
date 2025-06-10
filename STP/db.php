<?php
$host = "localhost";
$user = "root";
$password = "";
$db = "sistem_tiket_pesawat";

$conn = mysqli_connect($host, $user, $password, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>