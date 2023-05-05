<?php

require_once('../config.php');

if (isset($_GET['state'])) {
    $db = new PDO("mysql:host=$hostname;dbname=$dbname;charset=utf8mb4", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $db->prepare("SELECT COUNT(id), city FROM visits WHERE state = :state GROUP BY city");
    $stmt->bindParam(":state", $_GET['state']);
    $stmt->execute();
    $visited = $stmt->fetchAll();
    echo json_encode($visited);
}