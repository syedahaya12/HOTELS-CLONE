<?php
$host = 'localhost';
$dbname = 'dbmwx32ogslj6e';
$username = 'uannmukxu07nw';
$password = 'nhh1divf0d2c';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
