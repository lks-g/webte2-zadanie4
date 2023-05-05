<?php

require_once('config.php');

if (isset($_GET['state'])) {
    $conn = new PDO("mysql:host=$hostname;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $conn->prepare("SELECT COUNT(id), city FROM visits WHERE state = :state GROUP BY city");
    $stmt->bindParam(":state", $_GET['state']);
    $stmt->execute();
    $D2 = $stmt->fetchAll();
    echo json_encode($D2);
}