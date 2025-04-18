<?php
// Database connection file

$host = 'localhost';
$dbname = 'twincool';
$username = 'root';
$password = '';

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
// Connection successful
?>