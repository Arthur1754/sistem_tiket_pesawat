<?php
session_start();
include_once 'db_connection.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['ID_CUST'])) {
    header("Location: login.php");
    exit();
}

$ticket_id = $_GET['ticket_id'] ?? '';
$user_id_cust = $_SESSION['ID_CUST'];
$booking_details = null;
$flights = [];
$error_message = '';

// Ambil detail pemesanan
if ($ticket_id) {
    $sql = "SELECT t.*, p.*, c.* 
            FROM tiket t
            JOIN penerbangan p ON t.ID_PENERBANGAN = p.ID_PENERBANGAN
            JOIN customer c ON t.ID_CUST = c.ID_CUST
            WHERE t.ID_TIKET = ? AND t.ID_CUST = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $ticket_id, $user_id_cust);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $booking_details = $result->fetch_assoc();
    } else {
        $error_message = "Tiket tidak ditemukan atau Anda tidak memiliki akses.";
    }
    $stmt->close();
}

// Ambil daftar penerbangan untuk dropdown
$sql_flights = "SELECT ID_PENERBANGAN, CITY, TUJUAN, JAM_BERANGKAT, JAM_TIBA FROM penerbangan";
$result_flights = $conn->query($sql_flights);
if ($result_flights && $result_flights->num_rows > 0) {
    while ($row = $result_flights->fetch_assoc()) {
        $flights[] = $row;
    }
}

// Proses form update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_booking'])) {
    $new_flight_id = $_POST['flight_id'];
    $new_seat_number = $_POST['nomor_kursi'];
    
    $sql_update = "UPDATE tiket SET ID_PENERBANGAN = ?, NOMOR_KURSI = ? WHERE ID_TIKET = ? AND ID_CUST = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ssss", $new_flight_id, $new_seat_number, $ticket_id, $user_id_cust);
    
    if ($stmt_update->execute()) {
        $_SESSION['update_message'] = "Pemesanan berhasil diperbarui!";
        $_SESSION['update_type'] = "success";
        header("Location: my_bookings.php");
        exit();
    } else {
        $error_message = "Gagal memperbarui pemesanan: " . $stmt_update->error;
    }
    $stmt_update->close();
}
?>
    <link rel="stylesheet" href="style.css">
    <style>
       .seat-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-top: 10px;
    }
    
    .seat-option {
        display: flex;
        align-items: center;
        padding: 8px;
        background: #f5f5f5;
        border-radius: 4px;
    }
    
    .seat-option input[type="radio"] {
        margin-right: 8px;
    }
    
    .seat-option label {
        cursor: pointer;
        margin: 0;
    }
    
    .seat-option:hover {
        background: #e9e9e9;
    }
    
    .seat-option input[type="radio"]:checked + label {
        font-weight: bold;
        color: #0066cc;
    }
    </style>
</head>
<body>
    <?php include_once 'header.php'; ?>

    <main class="edit-booking-page">
        <div class="container">
            <h2>Ubah Pemesanan Tiket</h2>
            
            <?php if ($error_message): ?>
                <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>
            
            <?php if ($booking_details): ?>
                <form action="edit_booking.php?ticket_id=<?php echo htmlspecialchars($ticket_id); ?>" method="POST" class="booking-form card">
                    <div class="form-group">
                        <label for="flight_id">Penerbangan:</label>
                        <select id="flight_id" name="flight_id" required>
                            <?php foreach ($flights as $flight): ?>
                                <option value="<?php echo htmlspecialchars($flight['ID_PENERBANGAN']); ?>"
                                    <?php if ($flight['ID_PENERBANGAN'] == $booking_details['ID_PENERBANGAN']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($flight['CITY']); ?> ke <?php echo htmlspecialchars($flight['TUJUAN']); ?> 
                                    (<?php echo htmlspecialchars($flight['JAM_BERANGKAT']); ?> - <?php echo htmlspecialchars($flight['JAM_TIBA']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Nomor Kursi:</label>
                        <div class="seat-options">
                            <?php
                            $seat_options = ['A01', 'A02', 'A03', 'B01', 'B02', 'B03'];
                            foreach ($seat_options as $seat): ?>
                                <div class="seat-option">
                                    <input type="radio" id="seat_<?php echo $seat; ?>" name="nomor_kursi" 
                                           value="<?php echo $seat; ?>" 
                                           <?php if ($booking_details['NOMOR_KURSI'] == $seat) echo 'checked'; ?> required>
                                    <label for="seat_<?php echo $seat; ?>"><?php echo $seat; ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <button type="submit" name="update_booking" class="btn-primary">Simpan Perubahan</button>
                    <a href="my_bookings.php" class="btn-secondary">Batal</a>
                </form>
            <?php else: ?>
                <p>Tidak dapat memuat detail pemesanan.</p>
                <a href="my_bookings.php" class="btn-primary">Kembali ke Daftar Pemesanan</a>
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