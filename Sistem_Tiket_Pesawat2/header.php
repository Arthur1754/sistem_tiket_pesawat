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
                    <li><a href="register.php" class="btn-register">Sign In</a></li> <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>