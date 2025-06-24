<?php
session_start();
include_once 'db_connection.php';

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_or_email = trim($_POST['username'] ?? ''); // Bisa username atau email
    $password = $_POST['password'] ?? '';

    // Cari user di tabel 'users' berdasarkan username atau email
    $sql = "SELECT id, username, password, email FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username_or_email, $username_or_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Verify the hashed password
        if (password_verify($password, $user['password'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_id'] = $user['id']; // ID dari tabel users
            $_SESSION['user_email'] = $user['email']; // Email dari tabel users

            // Sekarang, ambil ID_CUST dari tabel customer menggunakan email
            $sql_cust_id = "SELECT ID_CUST FROM customer WHERE EMAIL = ?";
            $stmt_cust_id = $conn->prepare($sql_cust_id);
            $stmt_cust_id->bind_param("s", $_SESSION['user_email']);
            $stmt_cust_id->execute();
            $result_cust_id = $stmt_cust_id->get_result();

            if ($result_cust_id->num_rows === 1) {
                $cust_row = $result_cust_id->fetch_assoc();
                $_SESSION['ID_CUST'] = $cust_row['ID_CUST']; // Simpan ID_CUST ke sesi
            } else {
                // Opsional: Handle jika email pengguna di tabel users tidak ditemukan di customer
                // Ini bisa terjadi jika user mendaftar tapi belum pernah memesan tiket (belum ada di customer table)
                $_SESSION['ID_CUST'] = null; // Set null atau biarkan kosong
            }
            $stmt_cust_id->close();

            header("Location: index.php");
            exit();
        } else {
            $error_message = 'Username/Email atau password salah.';
        }
    } else {
        $error_message = 'Username/Email atau password salah.';
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include_once 'header.php'; ?>

    <main class="login-page">
        <div class="container">
            <h2>Login</h2>
            <?php if ($error_message): ?>
                <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>
            <form action="login.php" method="POST" class="login-form card">
                <div class="form-group">
                    <label for="username">Username atau Email:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn-primary">Login</button>
                <p>Belum punya akun? <a href="register.php">Daftar di sini</a>.</p>
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
<?php $conn->close(); ?>    