<?php

require_once('config.php');

$conn = new PDO("mysql:host=$hostname;dbname=$dbname;charset=utf8mb4", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $conn->prepare("SELECT DISTINCT lon, lat FROM visits");
$stmt->execute();
$lonlat = $stmt->fetchAll();

echo json_encode($lonlat);
