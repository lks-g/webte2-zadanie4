<?php

require_once('../config.php');

$db = new PDO("mysql:host=$hostname;dbname=$dbname;charset=utf8mb4", $username, $password);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $db->prepare("SELECT DISTINCT lon, lat FROM visits");
$stmt->execute();
$lonlat = $stmt->fetchAll();

echo json_encode($lonlat);
