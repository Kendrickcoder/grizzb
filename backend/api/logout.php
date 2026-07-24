<?php
require 'includes/cors.php';
require 'includes/db.php';
require 'includes/token_auth.php';

$token = bearer_token();
if ($token) {
    $stmt = $conn->prepare("DELETE FROM sessions WHERE token = :token");
    $stmt->bindParam(':token', $token);
    $stmt->execute();
}
echo json_encode(['success' => true]);
