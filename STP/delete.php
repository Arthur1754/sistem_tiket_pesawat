<?php
include 'db.php';
$id = $_GET['id'];
$sql = "DELETE FROM STAFF WHERE ID_STAFF='$id'";
if (mysqli_query($conn, $sql)) {
    header("Location: index.php");
} else {
    echo "Error: " . mysqli_error($conn);
}
?>