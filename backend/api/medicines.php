<?php
require 'includes/cors.php';
require 'includes/db.php';
require 'includes/token_auth.php';

$user = require_login($conn);
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $search = trim($_GET['search'] ?? '');
    try {
        if ($search !== '') {
            $stmt = $conn->prepare("SELECT * FROM medicines WHERE name LIKE :search ORDER BY id DESC");
            $term = "%$search%";
            $stmt->bindParam(':search', $term);
            $stmt->execute();
        } else {
            $stmt = $conn->query("SELECT * FROM medicines ORDER BY id DESC");
        }
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $name        = trim($data['name'] ?? '');
    $price       = $data['price'] ?? 0;
    $quantity    = $data['quantity'] ?? 0;
    $expiry_date = $data['expiry_date'] ?? null;

    if ($name === '' || $price <= 0 || $quantity < 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Please fill all fields correctly']);
        exit;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO medicines (name, price, quantity, expiry_date) VALUES (:name, :price, :quantity, :expiry_date)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':expiry_date', $expiry_date);
        $stmt->execute();
        echo json_encode(['success' => true, 'id' => $conn->lastInsertId()]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
