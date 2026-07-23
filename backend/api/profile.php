<?php
require 'includes/cors.php';
require 'includes/db.php';
require 'includes/token_auth.php';

$user = require_admin($conn);
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    echo json_encode([
        'id'         => $user['id'],
        'fullname'   => $user['fullname'],
        'username'   => $user['username'],
        'created_at' => $user['created_at'],
    ]);
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    // Always act on the logged-in user's own id — never trust a posted id.
    $id           = $user['id'];
    $fullname     = trim($data['fullname'] ?? '');
    $username     = trim($data['username'] ?? '');
    $new_password = $data['new_password'] ?? '';

    try {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username AND id != :id");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            http_response_code(409);
            echo json_encode(['error' => 'Username already taken by another user']);
            exit;
        }

        if (!empty($new_password)) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET fullname=:fullname, username=:username, password=:password WHERE id=:id");
            $stmt->bindParam(':password', $hashed);
        } else {
            $stmt = $conn->prepare("UPDATE users SET fullname=:fullname, username=:username WHERE id=:id");
        }
        $stmt->bindParam(':fullname', $fullname);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
