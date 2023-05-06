<?php

require_once('../config.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_GET['state'])) {
    $db = new PDO("mysql:host=$hostname;dbname=$dbname;charset=utf8mb4", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $db->prepare("SELECT COUNT(id), city FROM results WHERE state = :state GROUP BY city");
    $stmt->bindParam(":state", $_GET['state']);
    $stmt->execute();
    $visited = $stmt->fetchAll();
    echo json_encode($visited);
}