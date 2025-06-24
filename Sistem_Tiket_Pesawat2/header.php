<header>
    <div class="container">
        <h1>Flight Booking</h1>
        <nav>
            <ul>
                <li><a href="index.php">Cari Penerbangan</a></li>
                <li><a href="my_bookings.php">Pemesanan Saya</a></li>
                <?php
                // Ini adalah bagian kritis. Periksa dengan teliti.
                // Pastikan 'loggedin' dan 'username' sesuai.
                $is_admin = (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && $_SESSION['username'] === 'admin');

                if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true):
                ?>
                    <?php if ($is_admin): // Ini yang mengontrol tampilan tombol Staff ?>
                        <li><a href="staff.php">Staff</a></li>
                    <?php endif; ?>
                    <li><span>Selamat Datang, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span></li>
                    <li><a href="logout.php" class="btn-logout">Logout</a></li>
                <?php else: ?>
                    <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>