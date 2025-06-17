<?php
include_once 'db_connection.php'; // Pastikan path benar

$search_email = $_GET['email'] ?? '';
$bookings = [];

if ($search_email) {
    // Join tabel tiket, customer, dan penerbangan untuk mendapatkan detail lengkap
    $sql = "SELECT t.ID_TIKET, t.NOMOR_KURSI, t.HARGA, t.TANGGAL_PESAN,
                   c.CUST_NAME, c.EMAIL,
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
    <header>
        <div class="container">
            <h1>Pemesanan Saya</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Cari Penerbangan</a></li>
                    <li><a href="my_bookings.php">Pemesanan Saya</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="my-bookings-page">
        <div class="container">
            <h2>Cari Pemesanan Anda</h2>
            <form action="my_bookings.php" method="GET" class="search-form small-form card">
                <div class="form-group">
                    <label for="email_search">Masukkan Email Anda:</label>
                    <input type="email" id="email_search" name="email" value="<?php echo htmlspecialchars($search_email); ?>" required>
                </div>
                <button type="submit" class="btn-primary">Cari Pemesanan</button>
            </form>

            <?php if ($search_email && count($bookings) > 0): ?>
                <h3>Daftar Pemesanan untuk <?php echo htmlspecialchars($search_email); ?></h3>
                <div class="booking-list">
                    <?php foreach ($bookings as $booking): ?>
                        <div class="booking-card card">
                            <div class="booking-header">
                                <h4>Tiket ID: <?php echo htmlspecialchars($booking['ID_TIKET']); ?></h4>
                                <span class="booking-date">Tanggal Pesan: <?php echo htmlspecialchars($booking['TANGGAL_PESAN']); ?></span>
                            </div>
                            <div class="booking-details">
                                <p><strong>Penerbangan:</strong> <?php echo htmlspecialchars($booking['Asal_Kota']); ?> (<?php echo htmlspecialchars($booking['Asal_Negara']); ?>) ke <?php echo htmlspecialchars($booking['TUJUAN']); ?></p>
                                <p><strong>Berangkat:</strong> <?php echo htmlspecialchars($booking['JAM_BERANGKAT']); ?> - <strong>Tiba:</strong> <?php echo htmlspecialchars($booking['JAM_TIBA']); ?></p>
                                <p><strong>Nomor Kursi:</strong> <?php echo htmlspecialchars($booking['NOMOR_KURSI']); ?></p>
                                <p><strong>Harga:</strong> Rp <?php echo number_format($booking['HARGA'], 0, ',', '.'); ?></p>
                                <p><strong>Nama Pemesan:</strong> <?php echo htmlspecialchars($booking['CUST_NAME']); ?></p>
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