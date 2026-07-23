<?php
require 'includes/cors.php';
require 'includes/db.php';
require 'includes/token_auth.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$method = $_SERVER['REQUEST_METHOD'];

if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing id']);
    exit;
}

if ($method === 'GET') {
    require_login($conn);
    $stmt = $conn->prepare("SELECT * FROM medicines WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $medicine = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$medicine) {
        http_response_code(404);
        echo json_encode(['error' => 'Medicine not found']);
        exit;
    }
    echo json_encode($medicine);
    exit;
}

if ($method === 'PUT') {
    require_admin($conn);
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $name        = trim($data['name'] ?? '');
    $price       = $data['price'] ?? 0;
    $quantity    = $data['quantity'] ?? 0;
    $expiry_date = $data['expiry_date'] ?? null;

    try {
        $stmt = $conn->prepare("UPDATE medicines SET name=:name, price=:price, quantity=:quantity, expiry_date=:expiry_date WHERE id=:id");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':expiry_date', $expiry_date);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

if ($method === 'DELETE') {
    require_admin($conn);
    try {
        $stmt = $conn->prepare("DELETE FROM medicines WHERE id = :id");
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
