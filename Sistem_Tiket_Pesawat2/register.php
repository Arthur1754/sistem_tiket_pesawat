<?php
session_start();
include_once 'db_connection.php'; // Pastikan path benar

$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate inputs
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = 'Semua field harus diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Format email tidak valid.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Konfirmasi password tidak cocok.';
    } elseif (strlen($password) < 6) {
        $error_message = 'Password harus minimal 6 karakter.';
    } else {
        // Check if username or email already exists
        $sql_check = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt_check = $conn->prepare($sql_check);
        if ($stmt_check === false) {
            $error_message = 'Error preparing statement for check: ' . $conn->error;
        } else {
            $stmt_check->bind_param("ss", $username, $email);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows > 0) {
                $error_message = 'Username atau email sudah terdaftar.';
            } else {
                // Hash the password securely
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert new user into database
                $sql_insert = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
                $stmt_insert = $conn->prepare($sql_insert);
                if ($stmt_insert === false) {
                    $error_message = 'Error preparing statement for insert: ' . $conn->error;
                } else {
                    $stmt_insert->bind_param("sss", $username, $email, $hashed_password);

                    if ($stmt_insert->execute()) {
                        $success_message = 'Pendaftaran berhasil! Anda sekarang bisa <a href="login.php">Login</a>.';
                        // Optional: Automatically log in the user after registration
                        // $_SESSION['loggedin'] = true;
                        // $_SESSION['username'] = $username;
                        // header("Location: index.php");
                        // exit();
                    } else {
                        $error_message = 'Terjadi kesalahan saat mendaftar: ' . $stmt_insert->error;
                    }
                    $stmt_insert->close();
                }
            }
            $stmt_check->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In (Daftar Akun Baru)</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include_once 'header.php'; ?>

    <main class="login-page">
        <div class="container">
            <h2>Daftar Akun Baru</h2>
            <?php if ($error_message): ?>
                <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>
            <?php if ($success_message): ?>
                <p class="success-message"><?php echo $success_message; ?></p>
            <?php endif; ?>
            <form action="register.php" method="POST" class="login-form card">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($username ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn-primary">Daftar</button>
                <p>Sudah punya akun? <a href="login.php">Login di sini</a>.</p>
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
<?php
// Close connection only if it was successfully established
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>