<?php
include_once 'db_connection.php';

$origin_city = $_GET['origin_city'] ?? '';
$destination_city = $_GET['destination_city'] ?? '';
$departure_date = $_GET['departure_date'] ?? ''; // Tanggal ini tidak ada di tabel penerbangan langsung, perlu penyesuaian atau relasi tabel

// Untuk demo, kita akan mengabaikan tanggal dan mencari berdasarkan kota saja
$sql = "SELECT * FROM penerbangan WHERE CITY LIKE ? AND TUJUAN LIKE ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $origin_city_param, $destination_city_param);
$origin_city_param = '%' . $origin_city . '%';
$destination_city_param = '%' . $destination_city . '%';
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pencarian Penerbangan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Hasil Pencarian Penerbangan</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Kembali ke Pencarian</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="flight-results">
        <div class="container">
            <h2>Penerbangan dari <?php echo htmlspecialchars($origin_city); ?> ke <?php echo htmlspecialchars($destination_city); ?></h2>
            <?php if ($result->num_rows > 0): ?>
                <div class="flight-list">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="flight-card">
                            <div class="flight-details">
                                <h3><?php echo htmlspecialchars($row['CITY']); ?> (<?php echo htmlspecialchars($row['COUNTRY']); ?>) ke <?php echo htmlspecialchars($row['TUJUAN']); ?></h3>
                                <p>Berangkat: <?php echo htmlspecialchars($row['JAM_BERANGKAT']); ?></p>
                                <p>Tiba: <?php echo htmlspecialchars($row['JAM_TIBA']); ?></p>
                                <p>ID Penerbangan: <?php echo htmlspecialchars($row['ID_PENERBANGAN']); ?></p>
                            </div>
                            <div class="flight-actions">
                                <a href="booking.php?flight_id=<?php echo htmlspecialchars($row['ID_PENERBANGAN']); ?>" class="btn-secondary">Pesan Sekarang</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>Tidak ada penerbangan yang ditemukan untuk rute tersebut.</p>
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