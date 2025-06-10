<?php include 'db.php'; ?>
<?php
$id = $_GET['id'];
$sql = "SELECT * FROM STAFF WHERE ID_STAFF='$id'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html>
<head><title>Edit Staff</title></head>
<body>
<h2>Edit Staff</h2>
<form method="post">
    Nama: <input type="text" name="name" value="<?= $row['STAFF_NAME'] ?>"><br>
    Nomor: <input type="text" name="number" value="<?= $row['STAFF_NUMBER'] ?>"><br>
    Negara: <input type="text" name="country" value="<?= $row['STAFF_COUNTRY'] ?>"><br>
    Email: <input type="text" name="email" value="<?= $row['EMAIL'] ?>"><br>
    <input type="submit" name="submit" value="Update">
</form>

<?php
if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $number = $_POST['number'];
    $country = $_POST['country'];
    $email = $_POST['email'];

    $sql = "UPDATE STAFF SET STAFF_NAME='$name', STAFF_NUMBER='$number', STAFF_COUNTRY='$country', EMAIL='$email' WHERE ID_STAFF='$id'";
    if (mysqli_query($conn, $sql)) {
        echo "Data berhasil diupdate <br><a href='index.php'>Kembali</a>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
</body>
</html>