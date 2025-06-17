<?php
// Pastikan db_connection.php ada dan berfungsi
include_once 'db_connection.php';
session_start(); // Tambahkan session_start() jika belum ada

// Ambil daftar kota asal (CITY) yang unik dari tabel penerbangan
$origin_cities = [];
$sql_origins = "SELECT DISTINCT CITY FROM penerbangan ORDER BY CITY ASC";
$result_origins = $conn->query($sql_origins);
if ($result_origins && $result_origins->num_rows > 0) {
    while ($row = $result_origins->fetch_assoc()) {
        $origin_cities[] = $row['CITY'];
    }
}

// Ambil daftar kota tujuan (TUJUAN) yang unik dari tabel penerbangan
$destination_cities = [];
$sql_destinations = "SELECT DISTINCT TUJUAN FROM penerbangan ORDER BY TUJUAN ASC";
$result_destinations = $conn->query($sql_destinations);
if ($result_destinations && $result_destinations->num_rows > 0) {
    while ($row = $result_destinations->fetch_assoc()) {
        $destination_cities[] = $row['TUJUAN'];
    }
}

// Data untuk JavaScript (Mapping asal ke tujuan yang tersedia)
$flight_routes = [];
$sql_routes = "SELECT CITY, TUJUAN FROM penerbangan";
$result_routes = $conn->query($sql_routes);
if ($result_routes && $result_routes->num_rows > 0) {
    while ($row = $result_routes->fetch_assoc()) {
        // Buat array multidimensi: origin -> [destinations]
        if (!isset($flight_routes[$row['CITY']])) {
            $flight_routes[$row['CITY']] = [];
        }
        $flight_routes[$row['CITY']][] = $row['TUJUAN'];
    }
}
// Konversi ke JSON agar bisa diakses oleh JavaScript
$flight_routes_json = json_encode($flight_routes);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemesanan Tiket Pesawat - Traveloka Inspired</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Flight Booking</h1>
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

    <main class="hero">
        <div class="container">
            <h2>Temukan Penerbangan Terbaik untuk Petualangan Anda</h2>
            <form action="flights.php" method="GET" class="search-form">
                <div class="form-group">
                    <label for="origin_city">Kota Asal:</label>
                    <select id="origin_city" name="origin_city" required>
                        <option value="">Pilih Kota Asal</option>
                        <?php foreach ($origin_cities as $city): ?>
                            <option value="<?php echo htmlspecialchars($city); ?>"><?php echo htmlspecialchars($city); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="destination_city">Kota Tujuan:</label>
                    <select id="destination_city" name="destination_city" required>
                        <option value="">Pilih Kota Tujuan</option>
                        <?php foreach ($destination_cities as $city): ?>
                            <option value="<?php echo htmlspecialchars($city); ?>"><?php echo htmlspecialchars($city); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="departure_date">Tanggal Berangkat:</label>
                    <input type="date" id="departure_date" name="departure_date" required>
                </div>
                <div class="form-group">
                    <label for="return_date">Tanggal Kembali:</label>
                    <input type="date" id="return_date" name="return_date">
                </div>
                <div class="form-group">
                    <label for="passengers">Jumlah Penumpang:</label>
                    <input type="number" id="passengers" name="passengers" min="1" value="1" required>
                </div>
                <button type="submit" class="btn-primary">Cari Penerbangan</button>
            </form>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 Sistem Tiket Pesawat. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Data rute penerbangan dari PHP
        const flightRoutes = <?php echo $flight_routes_json; ?>;

        const originSelect = document.getElementById('origin_city');
        const destinationSelect = document.getElementById('destination_city');

        // Fungsi untuk memperbarui pilihan kota tujuan berdasarkan kota asal yang dipilih
        function updateDestinationOptions() {
            const selectedOrigin = originSelect.value;
            destinationSelect.innerHTML = '<option value="">Pilih Kota Tujuan</option>'; // Reset tujuan

            if (selectedOrigin && flightRoutes[selectedOrigin]) {
                const availableDestinations = flightRoutes[selectedOrigin];
                // Pastikan setiap tujuan unik dan urutkan
                const uniqueSortedDestinations = [...new Set(availableDestinations)].sort();

                uniqueSortedDestinations.forEach(destination => {
                    const option = document.createElement('option');
                    option.value = destination;
                    option.textContent = destination;
                    destinationSelect.appendChild(option);
                });
            } else {
                // Jika tidak ada kota asal dipilih, tampilkan semua kota tujuan yang ada
                // (Ini mengambil dari semua tujuan yang mungkin, tidak hanya yang berhubungan dengan rute tertentu)
                const allPossibleDestinations = [];
                for (const origin in flightRoutes) {
                    flightRoutes[origin].forEach(dest => {
                        allPossibleDestinations.push(dest);
                    });
                }
                const uniqueSortedAllDestinations = [...new Set(allPossibleDestinations)].sort();
                uniqueSortedAllDestinations.forEach(destination => {
                    const option = document.createElement('option');
                    option.value = destination;
                    option.textContent = destination;
                    destinationSelect.appendChild(option);
                });
            }
        }

        // Panggil fungsi saat halaman dimuat untuk mengisi tujuan awal (bisa semua tujuan atau kosong)
        // updateDestinationOptions(); // Opsi: Panggil ini untuk mengisi semua tujuan saat awal

        // Tambahkan event listener untuk perubahan pada combobox Kota Asal
        originSelect.addEventListener('change', updateDestinationOptions);
    </script>
</body>
</html>
<?php
// Tutup koneksi database setelah semua operasi selesai
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>