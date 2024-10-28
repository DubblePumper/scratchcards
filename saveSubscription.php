<?php
// Connect to database
require_once("assets/php/databaseConnection.php");

$data = json_decode(file_get_contents('php://input'), true);
$endpoint = $data['endpoint'];
$publicKey = $data['keys']['p256dh'];
$authToken = $data['keys']['auth'];

// Insert subscription details into your database
$pdo = connect_to_database();
$stmt = $pdo->prepare("REPLACE INTO subscriptions (endpoint, p256dh, auth) VALUES (?, ?, ?)");
$stmt->execute([$endpoint, $publicKey, $authToken]);
?>