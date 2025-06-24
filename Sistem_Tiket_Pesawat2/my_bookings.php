<?php
session_start();
include_once 'db_connection.php';

// === START AUTENTIKASI CHECK ===
// Jika pengguna belum login, arahkan ke halaman login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}
// === END AUTENTIKASI CHECK ===

// ... sisa kode halaman tersebut ...


$search_email = $_GET['email'] ?? '';
$bookings = [];

// Tambahkan filter berdasarkan ID_CUST dari sesi jika user login
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && isset($_SESSION['ID_CUST']) && $_SESSION['ID_CUST'] !== null) {
    $id_cust_filter = $_SESSION['ID_CUST'];
    // Join tabel tiket, customer, dan penerbangan untuk mendapatkan detail lengkap
    $sql = "SELECT t.ID_TIKET, t.NOMOR_KURSI, t.HARGA, t.TANGGAL_PESAN,
                   c.CUST_NAME, c.EMAIL, c.ID_CUST,
                   p.CITY AS Asal_Kota, p.COUNTRY AS Asal_Negara, p.TUJUAN,
                   p.JAM_BERANGKAT, p.JAM_TIBA
            FROM tiket t
            JOIN customer c ON t.ID_CUST = c.ID_CUST
            JOIN penerbangan p ON t.ID_PENERBANGAN = p.ID_PENERBANGAN
            WHERE c.ID_CUST = ?"; // Filter berdasarkan ID_CUST yang login
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id_cust_filter);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
    $stmt->close();
} else if ($search_email) {
    // Jika tidak login, atau ID_CUST tidak ada, fallback ke pencarian by email (bisa dihapus/dibatasi jika hanya ingin login user)
    $sql = "SELECT t.ID_TIKET, t.NOMOR_KURSI, t.HARGA, t.TANGGAL_PESAN,
                   c.CUST_NAME, c.EMAIL, c.ID_CUST,
                   p.CITY AS Asal_Kota, p.COUNTRY AS Asal_Negara, p.TUJUAN,
                   p.JAM_BERANGKAT, p.JAM_TIBA
            FROM tiket t
            JOIN customer c ON t.ID_CUST = c.ID_CUST
            JOIN penerbangan p ON t.ID_PENERBANGAN = p.ID_PENERBANGAN
            WHERE c.EMAIL = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $search_email);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemesanan Saya</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="my-bookings-page">
        <div class="container">
            <?php
            // Tampilkan pesan pembatalan jika ada
            if (isset($_SESSION['cancel_message'])) {
                $message_class = ($_SESSION['cancel_type'] === 'success') ? 'success-message' : 'error-message';
                echo "<p class='{$message_class}'>" . htmlspecialchars($_SESSION['cancel_message']) . "</p>";
                unset($_SESSION['cancel_message']); // Hapus pesan setelah ditampilkan
                unset($_SESSION['cancel_type']);
            }
            ?>
           
    <header>
        <div class="container">
            <h1>Pemesanan Saya</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Cari Penerbangan</a></li>
                    <li><a href="my_bookings.php">Pemesanan Saya</a></li>
                    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                        <li><a href="logout.php" class="btn-logout">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php" class="btn-login">Login</a></li>
                        <li><a href="register.php" class="btn-register">Sign In</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="my-bookings-page">
        <div class="container">
            <?php if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true): ?>
                <h2>Cari Pemesanan Anda</h2>
                <p>Silakan masukkan email yang Anda gunakan saat pemesanan:</p>
                <form action="my_bookings.php" method="GET" class="search-form-small">
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Masukkan email Anda" required value="<?php echo htmlspecialchars($search_email); ?>">
                        <button type="submit" class="btn-primary">Cari</button>
                    </div>
                </form>
                <hr>
                <p>Atau <a href="login.php">Login</a> untuk melihat semua pemesanan akun Anda.</p>
            <?php endif; ?>

            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                <h2>Pemesanan Akun Saya</h2>
                <?php if (count($bookings) > 0): ?>
                    <div class="booking-list">
                        <?php foreach ($bookings as $booking): ?>
                            <div class="booking-card">
                                <div class="booking-header">
                                    <h4>Tiket ID: <?php echo htmlspecialchars($booking['ID_TIKET']); ?></h4>
                                    <p class="booking-date">Tanggal Pesan: <?php echo htmlspecialchars($booking['TANGGAL_PESAN']); ?></p>
                                </div>
                                <div class="booking-details">
                                    <p><strong>Penerbangan:</strong> <?php echo htmlspecialchars($booking['Asal_Kota']); ?> (<?php echo htmlspecialchars($booking['Asal_Negara']); ?>) ke <?php echo htmlspecialchars($booking['TUJUAN']); ?></p>
                                    <p><strong>Berangkat:</strong> <?php echo htmlspecialchars($booking['JAM_BERANGKAT']); ?> - <strong>Tiba:</strong> <?php echo htmlspecialchars($booking['JAM_TIBA']); ?></p>
                                    <p><strong>Nomor Kursi:</strong> <?php echo htmlspecialchars($booking['NOMOR_KURSI']); ?></p>
                                    <p><strong>Harga:</strong> Rp <?php echo number_format($booking['HARGA'], 0, ',', '.'); ?></p>
                                    <p><strong>Nama Pemesan:</strong> <?php echo htmlspecialchars($booking['CUST_NAME']); ?></p>
                                </div>
                                <br>
                                <div class="booking-actions">
                                    <a href="cancel_booking.php?ticket_id=<?php echo htmlspecialchars($booking['ID_TIKET']); ?>"
                                       class="btn-secondary"
                                       onclick="return confirm('Apakah Anda yakin ingin membatalkan pemesanan ini? Tindakan ini tidak dapat dibatalkan.');">
                                       Batal Pemesanan
                                    </a>
                                    &puncsp;
                                    &puncsp;
                                    &puncsp;
                                    
                                    <a href="edit_booking.php?ticket_id=<?php echo htmlspecialchars($booking['ID_TIKET']); ?>" 
                                    class="btn-secondary">
                                        Ubah Pemesanan
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>Anda belum memiliki pemesanan tiket.</p>
                    <a href="index.php" class="btn-primary">Cari Penerbangan Sekarang</a>
                <?php endif; ?>
            <?php elseif ($search_email && count($bookings) > 0): ?>
                <h2>Hasil Pencarian Pemesanan untuk "<?php echo htmlspecialchars($search_email); ?>"</h2>
                <div class="booking-list">
                    <?php foreach ($bookings as $booking): ?>
                            <div class="booking-card">
                                <div class="booking-header">
                                    <h4>Tiket ID: <?php echo htmlspecialchars($booking['ID_TIKET']); ?></h4>
                                    <p class="booking-date">Tanggal Pesan: <?php echo htmlspecialchars($booking['TANGGAL_PESAN']); ?></p>
                                </div>
                                <div class="booking-details">
                                    <p><strong>Penerbangan:</strong> <?php echo htmlspecialchars($booking['Asal_Kota']); ?> (<?php echo htmlspecialchars($booking['Asal_Negara']); ?>) ke <?php echo htmlspecialchars($booking['TUJUAN']); ?></p>
                                    <p><strong>Berangkat:</strong> <?php echo htmlspecialchars($booking['JAM_BERANGKAT']); ?> - <strong>Tiba:</strong> <?php echo htmlspecialchars($booking['JAM_TIBA']); ?></p>
                                    <p><strong>Nomor Kursi:</strong> <?php echo htmlspecialchars($booking['NOMOR_KURSI']); ?></p>
                                    <p><strong>Harga:</strong> Rp <?php echo number_format($booking['HARGA'], 0, ',', '.'); ?></p>
                                    <p><strong>Nama Pemesan:</strong> <?php echo htmlspecialchars($booking['CUST_NAME']); ?></p>
                                </div>
                                <div class="booking-actions">
                                    <p><small>Login untuk membatalkan pemesanan.</small></p>
                                </div>
                            </div>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($search_email && count($bookings) == 0): ?>
                <p>Tidak ada pemesanan yang ditemukan untuk email "<?php echo htmlspecialchars($search_email); ?>".</p>
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