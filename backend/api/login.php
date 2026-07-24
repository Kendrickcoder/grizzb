<?php
require 'includes/cors.php';
require 'includes/db.php';

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';

if ($username === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Username and password required']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid username or password']);
        exit;
    }

    $token = bin2hex(random_bytes(32));
    $stmt = $conn->prepare("INSERT INTO sessions (token, user_id) VALUES (:token, :uid)");
    $stmt->bindParam(':token', $token);
    $stmt->bindParam(':uid', $user['id']);
    $stmt->execute();

    echo json_encode([
        'token' => $token,
        'user' => [
            'id'       => $user['id'],
            'fullname' => $user['fullname'],
            'username' => $user['username'],
            'role'     => $user['role'],
        ],
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Login failed: ' . $e->getMessage()]);
}
