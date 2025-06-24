<?php
session_start(); // Pastikan session dimulai
include_once 'db_connection.php'; // Pastikan db_connection.php ada dan berfungsi

// === START AUTENTIKASI CHECK ===
// Jika pengguna belum login, arahkan ke halaman login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}
// === END AUTENTIKASI CHECK ===

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
        if (!isset($flight_routes[$row['CITY']])) {
            $flight_routes[$row['CITY']] = [];
        }
        $flight_routes[$row['CITY']][] = $row['TUJUAN'];
    }
}
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
                    <?php
                    // Tampilkan nama pengguna jika login, atau tombol login/signin jika belum
                    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true):
                    ?>
                        <li><span>Selamat Datang, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span></li>
                        <li><a href="logout.php" class="btn-logout">Logout</a></li>
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
        const flightRoutes = <?php echo $flight_routes_json; ?>;

        const originSelect = document.getElementById('origin_city');
        const destinationSelect = document.getElementById('destination_city');

        function updateDestinationOptions() {
            const selectedOrigin = originSelect.value;
            destinationSelect.innerHTML = '<option value="">Pilih Kota Tujuan</option>';

            if (selectedOrigin && flightRoutes[selectedOrigin]) {
                const availableDestinations = flightRoutes[selectedOrigin];
                const uniqueSortedDestinations = [...new Set(availableDestinations)].sort();

                uniqueSortedDestinations.forEach(destination => {
                    const option = document.createElement('option');
                    option.value = destination;
                    option.textContent = destination;
                    destinationSelect.appendChild(option);
                });
            } else {
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
        originSelect.addEventListener('change', updateDestinationOptions);

        // Panggil saat halaman dimuat agar kota tujuan terisi jika ada kota asal yang terpilih secara default (jika Anda memiliki itu)
        // Atau agar semua kota tujuan ditampilkan saat awal load jika belum ada pilihan asal
        // updateDestinationOptions(); // Uncomment if you want initial population
    </script>
</body>
</html>
<?php
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>