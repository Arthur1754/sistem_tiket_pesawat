<?php
session_start();
include_once 'db_connection.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['ID_CUST'])) {
    // Redirect ke halaman login atau tampilkan pesan error
    header("Location: login.php?error=unauthorized_cancellation");
    exit();
}

$ticket_id = $_GET['ticket_id'] ?? '';
$user_id_cust = $_SESSION['ID_CUST']; // ID_CUST pengguna yang login

if (empty($ticket_id)) {
    $_SESSION['cancel_message'] = "ID Tiket tidak ditemukan.";
    $_SESSION['cancel_type'] = "error";
    header("Location: my_bookings.php");
    exit();
}

$conn->begin_transaction(); // Mulai transaksi

try {
    // 1. Verifikasi kepemilikan tiket dan ambil ID_CUST dari tiket tersebut
    $sql_verify = "SELECT ID_CUST FROM tiket WHERE ID_TIKET = ?";
    $stmt_verify = $conn->prepare($sql_verify);
    if ($stmt_verify === false) {
        throw new Exception("Error preparing verification statement: " . $conn->error);
    }
    $stmt_verify->bind_param("s", $ticket_id);
    $stmt_verify->execute();
    $result_verify = $stmt_verify->get_result();

    if ($result_verify->num_rows === 0) {
        throw new Exception("Tiket tidak ditemukan.");
    }

    $row_verify = $result_verify->fetch_assoc();
    $ticket_owner_id_cust = $row_verify['ID_CUST'];
    $stmt_verify->close();

    // Pastikan tiket ini milik pengguna yang sedang login
    if ($ticket_owner_id_cust !== $user_id_cust) {
        throw new Exception("Anda tidak memiliki izin untuk membatalkan tiket ini.");
    }

    // 2. Hapus tiket dari tabel `tiket`
    $sql_delete_tiket = "DELETE FROM tiket WHERE ID_TIKET = ?";
    $stmt_delete_tiket = $conn->prepare($sql_delete_tiket);
    if ($stmt_delete_tiket === false) {
        throw new Exception("Error preparing delete tiket statement: " . $conn->error);
    }
    $stmt_delete_tiket->bind_param("s", $ticket_id);
    $stmt_delete_tiket->execute();
    $rows_deleted_tiket = $stmt_delete_tiket->affected_rows;
    $stmt_delete_tiket->close();

    if ($rows_deleted_tiket === 0) {
        throw new Exception("Gagal menghapus tiket atau tiket sudah tidak ada.");
    }

    // 3. (Opsional) Hapus entri customer jika ini adalah satu-satunya tiket mereka
    //    Ini hanya jika Anda tidak ingin menyimpan data customer yang tidak memiliki tiket aktif
    //    Namun, jika customer bisa memiliki banyak tiket dan Anda ingin melacak mereka,
    //    maka bagian ini TIDAK boleh dilakukan. Saya akan komentari untuk lebih aman.
    /*
    $sql_check_customer_tickets = "SELECT COUNT(*) AS total_tickets FROM tiket WHERE ID_CUST = ?";
    $stmt_check_customer_tickets = $conn->prepare($sql_check_customer_tickets);
    if ($stmt_check_customer_tickets === false) {
        throw new Exception("Error preparing check customer tickets statement: " . $conn->error);
    }
    $stmt_check_customer_tickets->bind_param("s", $ticket_owner_id_cust);
    $stmt_check_customer_tickets->execute();
    $result_check_customer_tickets = $stmt_check_customer_tickets->get_result();
    $row_check_customer_tickets = $result_check_customer_tickets->fetch_assoc();
    $total_tickets_for_customer = $row_check_customer_tickets['total_tickets'];
    $stmt_check_customer_tickets->close();

    if ($total_tickets_for_customer == 0) {
        // Jika tidak ada tiket lain untuk customer ini, hapus dari tabel customer
        $sql_delete_customer = "DELETE FROM customer WHERE ID_CUST = ?";
        $stmt_delete_customer = $conn->prepare($sql_delete_customer);
        if ($stmt_delete_customer === false) {
            throw new Exception("Error preparing delete customer statement: " . $conn->error);
        }
        $stmt_delete_customer->bind_param("s", $ticket_owner_id_cust);
        $stmt_delete_customer->execute();
        $stmt_delete_customer->close();
    }
    */

    $conn->commit(); // Commit transaksi jika semua berhasil
    $_SESSION['cancel_message'] = "Pemesanan tiket ID " . htmlspecialchars($ticket_id) . " berhasil dibatalkan.";
    $_SESSION['cancel_type'] = "success";
    header("Location: my_bookings.php");
    exit();

} catch (Exception $e) {
    $conn->rollback(); // Rollback transaksi jika ada error
    $_SESSION['cancel_message'] = "Pembatalan gagal: " . htmlspecialchars($e->getMessage());
    $_SESSION['cancel_type'] = "error";
    header("Location: my_bookings.php");
    exit();
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>