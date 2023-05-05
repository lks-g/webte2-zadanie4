<?php
include 'config.php';

if (isset($_GET['state'])) {

    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $conn->prepare("SELECT COUNT(id), city FROM visits WHERE state = :state GROUP BY city");
    $stmt->bindParam(":state", $_GET['state']);
    $stmt->execute();
    $D2 = $stmt->fetchAll();

    echo json_encode($D2);

}