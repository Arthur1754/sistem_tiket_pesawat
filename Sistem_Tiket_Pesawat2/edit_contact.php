<?php
session_start();
include_once 'db_connection.php';

// Pastikan hanya admin yang bisa mengakses halaman ini
// Sesuaikan dengan logika role admin Anda (misal: $_SESSION['user_role'] === 'admin')
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['username'] !== 'admin') {
    $_SESSION['crud_message'] = "Anda tidak memiliki izin untuk mengakses halaman ini.";
    $_SESSION['crud_type'] = "error";
    header("Location: contact.php");
    exit();
}

$message_id = $_GET['id'] ?? '';
$message_data = null;
$status_message = '';

if (empty($message_id)) {
    $_SESSION['crud_message'] = "ID Pesan tidak ditemukan.";
    $_SESSION['crud_type'] = "error";
    header("Location: contact.php");
    exit();
}

// Ambil data pesan yang akan diedit
$sql_fetch = "SELECT id, name, email, subject, message FROM contact_us WHERE id = ?";
$stmt_fetch = $conn->prepare($sql_fetch);
if ($stmt_fetch === false) {
    $status_message = "<p class='error-message'>Error mempersiapkan statement: " . $conn->error . "</p>";
} else {
    $stmt_fetch->bind_param("i", $message_id);
    $stmt_fetch->execute();
    $result_fetch = $stmt_fetch->get_result();
    if ($result_fetch->num_rows > 0) {
        $message_data = $result_fetch->fetch_assoc();
    } else {
        $status_message = "<p class='error-message'>Pesan tidak ditemukan.</p>";
    }
    $stmt_fetch->close();
}

// Proses Update Form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_contact'])) {
    $id = $_POST['id'];
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $status_message = "<p class='error-message'>Semua field harus diisi.</p>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $status_message = "<p class='error-message'>Format email tidak valid.</p>";
    } else {
        $sql_update = "UPDATE contact_us SET name = ?, email = ?, subject = ?, message = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        if ($stmt_update === false) {
            $status_message = "<p class='error-message'>Error mempersiapkan statement update: " . $conn->error . "</p>";
        } else {
            $stmt_update->bind_param("ssssi", $name, $email, $subject, $message, $id);
            if ($stmt_update->execute()) {
                $_SESSION['crud_message'] = "Pesan berhasil diperbarui.";
                $_SESSION['crud_type'] = "success";
                header("Location: contact.php");
                exit();
            } else {
                $status_message = "<p class='error-message'>Terjadi kesalahan saat memperbarui pesan: " . $stmt_update->error . "</p>";
            }
            $stmt_update->close();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pesan Kontak</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include_once 'header.php'; ?>

    <main class="edit-contact-page">
        <div class="container">
            <h2>Edit Pesan Kontak</h2>
            <?php echo $status_message; ?>

            <?php if ($message_data): ?>
                <div class="contact-form-section card">
                    <form action="edit_contact.php" method="POST">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($message_data['id']); ?>">
                        <div class="form-group">
                            <label for="name">Nama:</label>
                            <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($message_data['name']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($message_data['email']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="subject">Subjek:</label>
                            <input type="text" id="subject" name="subject" required value="<?php echo htmlspecialchars($message_data['subject']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="message">Pesan:</label>
                            <textarea id="message" name="message" rows="5" required><?php echo htmlspecialchars($message_data['message']); ?></textarea>
                        </div>
                        <button type="submit" name="update_contact" class="btn-primary">Perbarui Pesan</button>
                        <a href="contact.php" class="btn-secondary">Batal</a>
                    </form>
                </div>
            <?php else: ?>
                <p>Pesan tidak dapat dimuat atau tidak ditemukan.</p>
                <a href="contact.php" class="btn-primary">Kembali ke Daftar Pesan</a>
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