<?php
session_start(); // Start session to use header.php
include_once 'db_connection.php'; // Pastikan path benar

$flight_id = $_GET['flight_id'] ?? '';
$penerbangan = null;

if ($flight_id) {
    $sql = "SELECT * FROM penerbangan WHERE ID_PENERBANGAN = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $flight_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $penerbangan = $result->fetch_assoc();
    } else {
        echo "<p>Penerbangan tidak ditemukan.</p>";
        $flight_id = null; // Reset flight_id jika tidak ditemukan
    }
    $stmt->close();
}

// Proses form pemesanan
if ($_SERVER["REQUEST_METHOD"] == "POST" && $flight_id) {
    $cust_name = $_POST['cust_name'];
    $cust_city = $_POST['cust_city'];
    $cust_country = $_POST['cust_country'];
    $cust_number = $_POST['cust_number'];
    $email = $_POST['email'];
    $nomor_kursi = $_POST['nomor_kursi'];
    // Asumsi harga dan ID_STAFF diambil dari logic/konfigurasi atau input lain
    // Untuk contoh ini, harga diambil statis atau dari data penerbangan (jika ada kolom harga di penerbangan)
    // ID_STAFF bisa di-random atau diambil dari staf yang login (jika ada sistem login staf)
    $harga = 1500000; // Contoh harga statis, sebaiknya dinamis
    $id_staff = '1111'; // Contoh ID Staff statis

    // 1. Cek apakah customer sudah ada berdasarkan email
    $sql_check_cust = "SELECT ID_CUST FROM customer WHERE EMAIL = ?";
    $stmt_check_cust = $conn->prepare($sql_check_cust);
    $stmt_check_cust->bind_param("s", $email);
    $stmt_check_cust->execute();
    $result_check_cust = $stmt_check_cust->get_result();
    $id_cust = null;

    if ($result_check_cust->num_rows > 0) {
        $row_cust = $result_check_cust->fetch_assoc();
        $id_cust = $row_cust['ID_CUST'];
    } else {
        // Jika customer belum ada, insert customer baru
        // Generate ID_CUST sederhana (misal: 4 digit angka random)
        $id_cust_new = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $sql_insert_cust = "INSERT INTO customer (ID_CUST, CUST_NAME, CUST_CITY, CUST_COUNTRY, CUST_NUMBER, EMAIL) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_insert_cust = $conn->prepare($sql_insert_cust);
        $stmt_insert_cust->bind_param("ssssss", $id_cust_new, $cust_name, $cust_city, $cust_country, $cust_number, $email);
        if ($stmt_insert_cust->execute()) {
            $id_cust = $id_cust_new;
        } else {
            echo "<p class='error-message'>Error menambahkan customer: " . $stmt_insert_cust->error . "</p>";
        }
        $stmt_insert_cust->close();
    }
    $stmt_check_cust->close();

    if ($id_cust) {
        // 2. Insert data tiket
        // Generate ID_TIKET sederhana (misal: TXXX)
        $sql_max_ticket = "SELECT MAX(CAST(SUBSTRING(ID_TIKET, 2) AS UNSIGNED)) AS max_id FROM tiket";
        $result_max_ticket = $conn->query($sql_max_ticket);
        $max_id = 0;
        if ($result_max_ticket && $result_max_ticket->num_rows > 0) {
            $row_max = $result_max_ticket->fetch_assoc();
            $max_id = $row_max['max_id'];
        }
        $new_ticket_id_num = $max_id + 1;
        $id_tiket = 'T' . str_pad($new_ticket_id_num, 3, '0', STR_PAD_LEFT);
        $tanggal_pesan = date("Y-m-d"); // Tanggal hari ini

        $sql_insert_tiket = "INSERT INTO tiket (ID_TIKET, ID_STAFF, ID_CUST, ID_PENERBANGAN, NOMOR_KURSI, HARGA, TANGGAL_PESAN) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert_tiket = $conn->prepare($sql_insert_tiket);
        $stmt_insert_tiket->bind_param("sssssis", $id_tiket, $id_staff, $id_cust, $flight_id, $nomor_kursi, $harga, $tanggal_pesan);

        if ($stmt_insert_tiket->execute()) {
            header("Location: confirmation.php?ticket_id=" . urlencode($id_tiket));
            exit();
        } else {
            echo "<p class='error-message'>Error saat memesan tiket: " . $stmt_insert_tiket->error . "</p>";
        }
        $stmt_insert_tiket->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Tiket</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include_once 'header.php'; ?>

    <main class="booking-page">
        <div class="container">
            <?php if ($penerbangan): ?>
                <h2>Detail Penerbangan</h2>
                <div class="flight-details-summary card">
                    <p><strong>Dari:</strong> <?php echo htmlspecialchars($penerbangan['CITY'] . ', ' . $penerbangan['COUNTRY']); ?></p>
                    <p><strong>Ke:</strong> <?php echo htmlspecialchars($penerbangan['TUJUAN']); ?></p>
                    <p><strong>Berangkat:</strong> <?php echo htmlspecialchars($penerbangan['JAM_BERANGKAT']); ?></p>
                    <p><strong>Tiba:</strong> <?php echo htmlspecialchars($penerbangan['JAM_TIBA']); ?></p>
                    <p><strong>ID Penerbangan:</strong> <?php echo htmlspecialchars($penerbangan['ID_PENERBANGAN']); ?></p>
                    </div>

                <h2>Data Penumpang</h2>
                <form action="booking.php?flight_id=<?php echo htmlspecialchars($flight_id); ?>" method="POST" class="booking-form card">
                    <div class="form-group">
                        <label for="cust_name">Nama Lengkap:</label>
                        <input type="text" id="cust_name" name="cust_name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="cust_number">Nomor Telepon:</label>
                        <input type="text" id="cust_number" name="cust_number" required>
                    </div>
                    <div class="form-group">
                        <label for="cust_city">Kota Tinggal:</label>
                        <input type="text" id="cust_city" name="cust_city">
                    </div>
                    <div class="form-group">
                        <label for="cust_country">Negara Tinggal:</label>
                        <input type="text" id="cust_country" name="cust_country">
                    </div>
                    <div class="form-group">
                        <label for="nomor_kursi">Nomor Kursi:</label>
                        <input type="text" id="nomor_kursi" name="nomor_kursi" placeholder="e.g., A01" required>
                        <small>Contoh: A01, B10, C05</small>
                    </div>
                    <button type="submit" class="btn-primary">Konfirmasi Pemesanan</button>
                </form>
            <?php else: ?>
                <p>Silakan pilih penerbangan dari halaman pencarian.</p>
                <a href="index.php" class="btn-secondary">Cari Penerbangan</a>
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