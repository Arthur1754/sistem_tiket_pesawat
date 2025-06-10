<?php include 'db.php'; ?>
<!DOCTYPE html>
<html>
<head><title>Data Staff</title></head>
<body>
<h2>Daftar Staff</h2>
<a href="addStaff.php">+ Tambah Staff</a>
<table border="1">
    <tr>
        <th>ID</th><th>Nama</th><th>Nomor</th><th>Negara</th><th>Email</th><th>Aksi</th>
    </tr>
    <?php
    $sql = "SELECT * FROM STAFF";
    $result = mysqli_query($conn, $sql);
    while($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
            <td>{$row['ID_STAFF']}</td>
            <td>{$row['STAFF_NAME']}</td>
            <td>{$row['STAFF_NUMBER']}</td>
            <td>{$row['STAFF_COUNTRY']}</td>
            <td>{$row['EMAIL']}</td>
            <td>
                <a href='edit.php?id={$row['ID_STAFF']}'>Edit</a> |
                <a href='delete.php?id={$row['ID_STAFF']}'>Hapus</a>
            </td>
        </tr>";
    }
    ?>
</table>
</body>
</html>