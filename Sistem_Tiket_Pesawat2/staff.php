<?php
session_start();
include_once 'db_connection.php';

// Pastikan hanya admin yang bisa mengakses halaman ini
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['username'] !== 'admin') {
    $_SESSION['crud_message'] = "Anda tidak memiliki izin untuk mengakses halaman ini.";
    $_SESSION['crud_type'] = "error";
    header("Location: index.php"); // Atau ke halaman login
    exit();
}

$message_status = ''; // Untuk pesan sukses/error
$staff_members = []; // Untuk menyimpan data staf dari database

// --- Bagian CREATE: Proses Penambahan Staf Baru ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_staff'])) {
    $staff_name = trim($_POST['staff_name'] ?? '');
    $staff_contact = trim($_POST['staff_contact'] ?? '');
    $staff_position = trim($_POST['staff_position'] ?? '');
    $staff_email = trim($_POST['staff_email'] ?? '');

    if (empty($staff_name) || empty($staff_contact) || empty($staff_position) || empty($staff_email)) {
        $message_status = "<p class='error-message'>Semua field harus diisi.</p>";
    } elseif (!filter_var($staff_email, FILTER_VALIDATE_EMAIL)) {
        $message_status = "<p class='error-message'>Format email tidak valid.</p>";
    } else {
        // Generate ID_STAFF baru (contoh sederhana: S001, S002, dst.)
        $prefix = "S";
        $last_id_sql = "SELECT ID_STAFF FROM staff ORDER BY ID_STAFF DESC LIMIT 1";
        $last_id_result = $conn->query($last_id_sql);
        $last_numeric_id = 0;
        if ($last_id_result && $last_id_result->num_rows > 0) {
            $last_id_row = $last_id_result->fetch_assoc();
            $last_numeric_id = (int)substr($last_id_row['ID_STAFF'], 1);
        }
        $new_staff_id = $prefix . str_pad($last_numeric_id + 1, 3, '0', STR_PAD_LEFT);

        $sql = "INSERT INTO staff (ID_STAFF, STAFF_NAME, STAFF_CONTACT, STAFF_POSITION, STAFF_EMAIL) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            $message_status = "<p class='error-message'>Error mempersiapkan statement: " . $conn->error . "</p>";
        } else {
            $stmt->bind_param("sssss", $new_staff_id, $staff_name, $staff_contact, $staff_position, $staff_email);
            if ($stmt->execute()) {
                $message_status = "<p class='success-message'>Staf berhasil ditambahkan!</p>";
                // Clear form fields
                $staff_name = $staff_contact = $staff_position = $staff_email = '';
            } else {
                $message_status = "<p class='error-message'>Terjadi kesalahan saat menambahkan staf: " . $stmt->error . "</p>";
            }
            $stmt->close();
        }
    }
}

// --- Bagian READ: Ambil Semua Data Staf dari Database ---
$sql_read = "SELECT ID_STAFF, STAFF_NAME, STAFF_CONTACT, STAFF_POSITION, STAFF_EMAIL FROM staff ORDER BY ID_STAFF ASC";
$result_read = $conn->query($sql_read);
if ($result_read && $result_read->num_rows > 0) {
    while ($row = $result_read->fetch_assoc()) {
        $staff_members[] = $row;
    }
}

// Check for session messages (e.g., from edit/delete operations)
if (isset($_SESSION['crud_message'])) {
    $message_status .= "<p class='" . htmlspecialchars($_SESSION['crud_type']) . "-message'>" . htmlspecialchars($_SESSION['crud_message']) . "</p>";
    unset($_SESSION['crud_message']);
    unset($_SESSION['crud_type']);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Staff</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include_once 'header.php'; ?>

    <main class="staff-management-page">
        <div class="container">
            <h2>Manajemen Data Staff</h2>
            <?php echo $message_status; // Tampilkan pesan status form ?>

            <div class="staff-form-section card">
                <h3>Tambah Staf Baru</h3>
                <form action="staff.php" method="POST">
                    <div class="form-group">
                        <label for="staff_name">Nama Staf:</label>
                        <input type="text" id="staff_name" name="staff_name" required value="<?php echo htmlspecialchars($staff_name ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="staff_contact">Kontak Staf:</label>
                        <input type="text" id="staff_contact" name="staff_contact" required value="<?php echo htmlspecialchars($staff_contact ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="staff_position">Posisi Staf:</label>
                        <input type="text" id="staff_position" name="staff_position" required value="<?php echo htmlspecialchars($staff_position ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="staff_email">Email Staf:</label>
                        <input type="email" id="staff_email" name="staff_email" required value="<?php echo htmlspecialchars($staff_email ?? ''); ?>">
                    </div>
                    <button type="submit" name="add_staff" class="btn-primary">Tambah Staf</button>
                </form>
            </div>

            <div class="staff-list-section card">
                <h3>Daftar Staff</h3>
                <?php if (count($staff_members) > 0): ?>
                    <div class="staff-table-container">
                        <table class="staff-table">
                            <thead>
                                <tr>
                                    <th>ID Staf</th>
                                    <th>Nama</th>
                                    <th>Kontak</th>
                                    <th>Posisi</th>
                                    <th>Email</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($staff_members as $staff): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($staff['ID_STAFF']); ?></td>
                                        <td><?php echo htmlspecialchars($staff['STAFF_NAME']); ?></td>
                                        <td><?php echo htmlspecialchars($staff['STAFF_CONTACT']); ?></td>
                                        <td><?php echo htmlspecialchars($staff['STAFF_POSITION']); ?></td>
                                        <td><?php echo htmlspecialchars($staff['STAFF_EMAIL']); ?></td>
                                        <td class="action-buttons">
                                            <a href="edit_staff.php?id=<?php echo htmlspecialchars($staff['ID_STAFF']); ?>" class="btn-secondary btn-small">Edit</a>
                                            <a href="delete_staff.php?id=<?php echo htmlspecialchars($staff['ID_STAFF']); ?>" class="btn-danger btn-small"
                                               onclick="return confirm('Apakah Anda yakin ingin menghapus staf ini?');">Hapus</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>Belum ada data staf.</p>
                <?php endif; ?>
            </div>
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