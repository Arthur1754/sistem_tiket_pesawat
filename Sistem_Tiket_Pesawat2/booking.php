<?php
session_start(); // Pastikan session dimulai
include_once 'db_connection.php';

// === START AUTENTIKASI CHECK ===
// Jika pengguna belum login, arahkan ke halaman login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}
// === END AUTENTIKASI CHECK ===

// ... sisa kode halaman tersebut ...

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
        $flight_id = null;
    }
    $stmt->close();
}

// Proses form pemesanan
if ($_SERVER["REQUEST_METHOD"] == "POST" && $flight_id) {
    $cust_name = $_POST['cust_name'];
    $cust_city = $_POST['cust_city'];
    $cust_country = $_POST['cust_country'];
    $cust_number = $_POST['cust_number'];
    $email = $_POST['email']; // Email dari form
    $nomor_kursi = $_POST['nomor_kursi'];

    $harga = $tiket['HARGA'] ?? 0; // Ambil harga dari data penerbangan
    $harga = 0; // Inisialisasi harga
            switch ($penerbangan['TUJUAN']) { // Gunakan tujuan dari data penerbangan yang sudah diambil
                case 'Denpasar': $harga = 1500000; break;
                case 'Medan': $harga = 2000000; break;
                case 'Kuala Lumpur': $harga = 1200000; break;
                case 'Phuket': $harga = 850000; break;
                case 'Melbourne': $harga = 3000000; break;
                default: $harga = 1000000; // Harga default jika tujuan tidak dikenali
            }
    $id_staff = '1111'; // ID staff statis untuk contoh, ganti sesuai logika Anda

    $conn->begin_transaction(); // Mulai transaksi

    try {
        $id_cust_to_use = null;

        // Cek apakah user login memiliki ID_CUST di sesi
        if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && isset($_SESSION['ID_CUST']) && $_SESSION['ID_CUST'] !== null) {
            // Jika user login dan punya ID_CUST di sesi, gunakan itu
            $id_cust_to_use = $_SESSION['ID_CUST'];

            // Opsional: Update detail customer yang ada jika ada perubahan
            $sql_update_cust = "UPDATE customer SET CUST_NAME = ?, CUST_CITY = ?, CUST_COUNTRY = ?, CUST_NUMBER = ?, EMAIL = ? WHERE ID_CUST = ?";
            $stmt_update_cust = $conn->prepare($sql_update_cust);
            $stmt_update_cust->bind_param("ssssss", $cust_name, $cust_city, $cust_country, $cust_number, $email, $id_cust_to_use);
            $stmt_update_cust->execute();
            $stmt_update_cust->close();

        } else {
            // Jika user tidak login atau tidak punya ID_CUST di sesi, cek berdasarkan email
            $sql_check_cust = "SELECT ID_CUST FROM customer WHERE EMAIL = ?";
            $stmt_check_cust = $conn->prepare($sql_check_cust);
            $stmt_check_cust->bind_param("s", $email);
            $stmt_check_cust->execute();
            $result_check_cust = $stmt_check_cust->get_result();

            if ($result_check_cust->num_rows > 0) {
                // Jika customer dengan email ini sudah ada, gunakan ID_CUST yang ada
                $cust_row = $result_check_cust->fetch_assoc();
                $id_cust_to_use = $cust_row['ID_CUST'];

                // Opsional: Update detail customer yang ada
                $sql_update_cust = "UPDATE customer SET CUST_NAME = ?, CUST_CITY = ?, CUST_COUNTRY = ?, CUST_NUMBER = ? WHERE ID_CUST = ?";
                $stmt_update_cust = $conn->prepare($sql_update_cust);
                $stmt_update_cust->bind_param("sssss", $cust_name, $cust_city, $cust_country, $cust_number, $id_cust_to_use);
                $stmt_update_cust->execute();
                $stmt_update_cust->close();

            } else {
                // Jika customer belum ada, buat ID_CUST baru (contoh sederhana)
                $prefix = "C";
                $last_id_sql = "SELECT ID_CUST FROM customer ORDER BY ID_CUST DESC LIMIT 1";
                $last_id_result = $conn->query($last_id_sql);
                $last_numeric_id = 0;
                if ($last_id_result && $last_id_result->num_rows > 0) {
                    $last_id_row = $last_id_result->fetch_assoc();
                    $last_numeric_id = (int)substr($last_id_row['ID_CUST'], 1);
                }
                $id_cust_to_use = $prefix . str_pad($last_numeric_id + 1, 3, '0', STR_PAD_LEFT);

                // Insert customer baru
                $sql_insert_cust = "INSERT INTO customer (ID_CUST, CUST_NAME, CUST_CITY, CUST_COUNTRY, CUST_NUMBER, EMAIL) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt_insert_cust = $conn->prepare($sql_insert_cust);
                $stmt_insert_cust->bind_param("ssssss", $id_cust_to_use, $cust_name, $cust_city, $cust_country, $cust_number, $email);
                $stmt_insert_cust->execute();
                $stmt_insert_cust->close();
            }
            $stmt_check_cust->close();
        }

        if (!$id_cust_to_use) {
            throw new Exception("Gagal mendapatkan atau membuat ID Customer.");
        }

        // Generate ID_TIKET (contoh sederhana)
        $prefix_tiket = "T";
        $last_tiket_id_sql = "SELECT ID_TIKET FROM tiket ORDER BY ID_TIKET DESC LIMIT 1";
        $last_tiket_id_result = $conn->query($last_tiket_id_sql);
        $last_numeric_tiket_id = 0;
        if ($last_tiket_id_result && $last_tiket_id_result->num_rows > 0) {
            $last_tiket_id_row = $last_tiket_id_result->fetch_assoc();
            $last_numeric_tiket_id = (int)substr($last_tiket_id_row['ID_TIKET'], 1);
        }
        $id_tiket = $prefix_tiket . str_pad($last_numeric_tiket_id + 1, 3, '0', STR_PAD_LEFT);

        // Insert tiket baru
        $sql_insert_tiket = "INSERT INTO tiket (ID_TIKET, ID_PENERBANGAN, ID_STAFF, ID_CUST, NOMOR_KURSI, HARGA, TANGGAL_PESAN) VALUES (?, ?, ?, ?, ?, ?, CURDATE())";
        $stmt_insert_tiket = $conn->prepare($sql_insert_tiket);
        $stmt_insert_tiket->bind_param("sssssd", $id_tiket, $flight_id, $id_staff, $id_cust_to_use, $nomor_kursi, $harga);
        $stmt_insert_tiket->execute();
        $stmt_insert_tiket->close();

        $conn->commit(); // Commit transaksi
        header("Location: confirmation.php?ticket_id=" . urlencode($id_tiket));
        exit();

    } catch (Exception $e) {
        $conn->rollback(); // Rollback jika ada error
        echo "<p class='error-message'>Pemesanan gagal: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pemesanan Tiket</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Form Pemesanan</h1>
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

    <main class="booking-page">
        <div class="container">
            <h2>Detail Pemesanan</h2>
            <?php if ($penerbangan): ?>
                <div class="flight-details card">
                    <h3>Penerbangan: <?php echo htmlspecialchars($penerbangan['CITY']); ?> ke <?php echo htmlspecialchars($penerbangan['TUJUAN']); ?></h3>
                    <p>Waktu Berangkat: <?php echo htmlspecialchars($penerbangan['JAM_BERANGKAT']); ?></p>
                    <p>Waktu Tiba: <?php echo htmlspecialchars($penerbangan['JAM_TIBA']); ?></p>
                    <?php
// Misal harga ditentukan berdasarkan tujuan
            $tujuan = $penerbangan['TUJUAN'];
            $harga = 0;
            switch ($tujuan) {
                case 'Denpasar': $harga = 1500000; break;
                case 'Medan': $harga = 2000000; break;
                case 'Kuala Lumpur': $harga = 1200000; break;
                case 'Phuket': $harga = 850000; break;
                case 'Melbourne': $harga = 3000000; break;
                default: $harga = 1000000; // default jika tidak dikenali
            }
            ?>
<p>Harga: Rp <?= number_format($harga); ?></p>

                    <p>ID Penerbangan: <?php echo htmlspecialchars($penerbangan['ID_PENERBANGAN']); ?></p>
                </div>

                <form action="booking.php?flight_id=<?php echo htmlspecialchars($flight_id); ?>" method="POST" class="booking-form card">
                    <h3>Data Penumpang</h3>
                    <div class="form-group">
                        <label for="cust_name">Nama:</label>
                        <input type="text" id="cust_name" name="cust_name" required value="<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>">
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
                    <select id="nomor_kursi" name="nomor_kursi" required class="form-control">
                        <option value="">-- Pilih Kursi --</option>
                        <option value="A01">A01</option>
                        <option value="A02">A02</option>
                        <option value="A03">A03</option>
                        <option value="B01">B01</option>
                        <option value="B02">B02</option>
                        <option value="B03">B03</option>
                    </select>
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