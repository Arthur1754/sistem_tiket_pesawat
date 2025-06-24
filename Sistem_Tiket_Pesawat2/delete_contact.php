<?php
session_start();
include_once 'db_connection.php';

// Pastikan hanya admin yang bisa mengakses skrip ini
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['username'] !== 'admin') {
    $_SESSION['crud_message'] = "Anda tidak memiliki izin untuk melakukan tindakan ini.";
    $_SESSION['crud_type'] = "error";
    header("Location: contact.php");
    exit();
}

$message_id = $_GET['id'] ?? '';

if (empty($message_id)) {
    $_SESSION['crud_message'] = "ID Pesan tidak ditemukan.";
    $_SESSION['crud_type'] = "error";
    header("Location: contact.php");
    exit();
}

// Proses Delete
$sql_delete = "DELETE FROM contact_us WHERE id = ?";
$stmt_delete = $conn->prepare($sql_delete);
if ($stmt_delete === false) {
    $_SESSION['crud_message'] = "Error mempersiapkan statement delete: " . $conn->error;
    $_SESSION['crud_type'] = "error";
} else {
    $stmt_delete->bind_param("i", $message_id);
    if ($stmt_delete->execute()) {
        if ($stmt_delete->affected_rows > 0) {
            $_SESSION['crud_message'] = "Pesan berhasil dihapus.";
            $_SESSION['crud_type'] = "success";
        } else {
            $_SESSION['crud_message'] = "Gagal menghapus pesan atau pesan tidak ditemukan.";
            $_SESSION['crud_type'] = "error";
        }
    } else {
        $_SESSION['crud_message'] = "Terjadi kesalahan saat menghapus pesan: " . $stmt_delete->error;
        $_SESSION['crud_type'] = "error";
    }
    $stmt_delete->close();
}

$conn->close();
header("Location: contact.php");
exit();
?>