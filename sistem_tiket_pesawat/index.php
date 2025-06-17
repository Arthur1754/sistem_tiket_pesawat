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
                    <select 
                        name="kota asal" id="kota asal">
                        <option value="pekanbaru">Jakarta</option>

                    </select>
                </div>
                <div class="form-group">
                    <label for="destination_city">Kota Tujuan:</label>
                    <input type="text" id="destination_city" name="destination_city" placeholder="e.g., Denpasar" required>
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
</body>
</html>