<?php include 'db.php'; ?>
<!DOCTYPE html>
<html>
<head><title>Tambah Staff</title></head>
<body>
<h2>Tambah Staff</h2>
<form method="post">
    ID: <input type="text" name="id"><br>
    Nama: <input type="text" name="name"><br>
    Nomor: <input type="text" name="number"><br>
    Negara: <input type="text" name="country"><br>
    Email: <input type="text" name="email"><br>
    <input type="submit" name="submit" value="Simpan">
</form>

<?php
if (isset($_POST['submit'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $number = $_POST['number'];
    $country = $_POST['country'];
    $email = $_POST['email'];

    $sql = "INSERT INTO STAFF VALUES('$id', '$name', '$number', '$country', '$email')";
    if (mysqli_query($conn, $sql)) {
        echo "Data berhasil ditambahkan <br><a href='index.php'>Kembali</a>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
</body>
</html>