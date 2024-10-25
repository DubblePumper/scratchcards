<?php
require_once("databaseConnection.php");

function getScratchItems() {
    $pdo = connect_to_database();
    $stmt = $pdo->prepare("SELECT * FROM scratings");
    $stmt->execute();
    return $stmt->fetchAll();
}

// Fetch and output as JSON
header('Content-Type: application/json');
echo json_encode(getScratchItems());
?>