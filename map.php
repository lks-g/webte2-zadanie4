<?php
include 'config.php';

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $conn->prepare("SELECT DISTINCT lon, lat FROM visits");
$stmt->execute();
$lonlat = $stmt->fetchAll();

echo json_encode($lonlat);
