<?php
session_start(); // Start session to use header.php
include_once 'db_connection.php'; // Pastikan path benar

// === START AUTENTIKASI CHECK ===
// Jika pengguna belum login, arahkan ke halaman login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}
// === END AUTENTIKASI CHECK ===

// ... sisa kode halaman tersebut ...

$ticket_id = $_GET['ticket_id'] ?? '';
$tiket_details = null;

if ($ticket_id) {
    // Join tabel tiket, customer, penerbangan, dan staff untuk mendapatkan detail lengkap
    $sql = "SELECT t.ID_TIKET, t.NOMOR_KURSI, t.HARGA, t.TANGGAL_PESAN,
                   c.CUST_NAME, c.CUST_NUMBER, c.EMAIL,
                   p.CITY AS Asal_Kota, p.COUNTRY AS Asal_Negara, p.TUJUAN,
                   p.JAM_BERANGKAT, p.JAM_TIBA,
                   s.STAFF_NAME
            FROM tiket t
            JOIN customer c ON t.ID_CUST = c.ID_CUST
            JOIN penerbangan p ON t.ID_PENERBANGAN = p.ID_PENERBANGAN
            LEFT JOIN staff s ON t.ID_STAFF = s.ID_STAFF
            WHERE t.ID_TIKET = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $ticket_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $tiket_details = $result->fetch_assoc();
    } else {
        echo "<p class='error-message'>Detail tiket tidak ditemukan.</p>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pemesanan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include_once 'header.php'; ?>

    <main class="confirmation-page">
        <div class="container">
            <?php if ($tiket_details): ?>
                <div class="confirmation-message card success">
                    <h2>Pemesanan Berhasil!</h2>
                    <p>Tiket Anda dengan ID **<?php echo htmlspecialchars($tiket_details['ID_TIKET']); ?>** telah berhasil dipesan.</p>
                    <p>Detail tiket Anda telah dikirimkan ke email <strong><?php echo htmlspecialchars($tiket_details['EMAIL']); ?></strong>.</p>
                </div>

                <div class="booking-details-summary card">
                    <h3>Ringkasan Pemesanan</h3>
                    <p><strong>Nama Penumpang:</strong> <?php echo htmlspecialchars($tiket_details['CUST_NAME']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($tiket_details['EMAIL']); ?></p>
                    <p><strong>Nomor Telepon:</strong> <?php echo htmlspecialchars($tiket_details['CUST_NUMBER']); ?></p>
                    <hr>
                    <p><strong>Penerbangan:</strong> <?php echo htmlspecialchars($tiket_details['Asal_Kota']); ?> (<?php echo htmlspecialchars($tiket_details['Asal_Negara']); ?>) &rarr; <?php echo htmlspecialchars($tiket_details['TUJUAN']); ?></p>
                    <p><strong>Waktu Berangkat:</strong> <?php echo htmlspecialchars($tiket_details['JAM_BERANGKAT']); ?></p>
                    <p><strong>Waktu Tiba:</strong> <?php echo htmlspecialchars($tiket_details['JAM_TIBA']); ?></p>
                    <p><strong>Nomor Kursi:</strong> <?php echo htmlspecialchars($tiket_details['NOMOR_KURSI']); ?></p>
                    <p><strong>Harga Tiket:</strong> Rp <?php echo number_format($tiket_details['HARGA'], 0, ',', '.'); ?></p>
                    <p><strong>Tanggal Pemesanan:</strong> <?php echo htmlspecialchars($tiket_details['TANGGAL_PESAN']); ?></p>
                    <?php if ($tiket_details['STAFF_NAME']): ?>
                        <p><strong>Diproses Oleh Staf:</strong> <?php echo htmlspecialchars($tiket_details['STAFF_NAME']); ?></p>
                    <?php endif; ?>
                </div>

                <div class="action-buttons">
                    <a href="my_bookings.php?email=<?php echo urlencode($tiket_details['EMAIL']); ?>" class="btn-primary">Lihat Pemesanan Saya</a>
                    <a href="index.php" class="btn-secondary">Pesan Tiket Lain</a>
                </div>

            <?php else: ?>
                <div class="card error">
                    <p>Terjadi kesalahan atau detail pemesanan tidak ditemukan.</p>
                    <a href="index.php" class="btn-primary">Kembali ke Beranda</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 Sistem Tiket Pesawat. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
<?php $conn->close(); ?>