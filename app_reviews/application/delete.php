<?php
include '../db.php';

$id = $_GET['id'];

$sql = "DELETE FROM applications WHERE id=$id";
if ($conn->query($sql) === TRUE) {
    header("Location: list.php");
} else {
    echo "Error: " . $conn->error;
}

