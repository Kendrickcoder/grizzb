<?php
require 'includes/cors.php';
require 'includes/db.php';

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$fullname = trim($data['fullname'] ?? '');
$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';
$role = 'staff'; // Registration only ever creates staff accounts (secure)

if ($fullname === '' || $username === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['error' => 'All fields are required']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        http_response_code(409);
        echo json_encode(['error' => 'Username already taken']);
        exit;
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (fullname, username, password, role) VALUES (:fullname, :username, :password, :role)");
    $stmt->bindParam(':fullname', $fullname);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $hashed);
    $stmt->bindParam(':role', $role);
    $stmt->execute();

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Registration failed: ' . $e->getMessage()]);
}
