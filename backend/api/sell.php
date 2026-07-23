<?php
require 'includes/cors.php';
require 'includes/db.php';
require 'includes/token_auth.php';

$user = require_login($conn);
$data = json_decode(file_get_contents('php://input'), true) ?? [];
$id  = (int)($data['id'] ?? 0);
$qty = (int)($data['qty'] ?? 0);

try {
    $stmt = $conn->prepare("SELECT * FROM medicines WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $medicine = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$medicine) {
        http_response_code(404);
        echo json_encode(['error' => 'Medicine not found']);
        exit;
    }
    if ($qty <= 0 || $qty > $medicine['quantity']) {
        http_response_code(400);
        echo json_encode(['error' => 'Not enough stock']);
        exit;
    }

    $total_amount = $qty * $medicine['price'];

    $stmt = $conn->prepare("INSERT INTO sales
        (medicine_id, medicine_name, quantity_sold, price_per_unit, total_amount, staff_id, staff_name)
        VALUES (:mid, :mname, :qty, :price, :total, :sid, :sname)");
    $stmt->bindParam(':mid', $medicine['id']);
    $stmt->bindParam(':mname', $medicine['name']);
    $stmt->bindParam(':qty', $qty);
    $stmt->bindParam(':price', $medicine['price']);
    $stmt->bindParam(':total', $total_amount);
    $stmt->bindParam(':sid', $user['id']);
    $stmt->bindParam(':sname', $user['fullname']);
    $stmt->execute();

    $new_quantity = $medicine['quantity'] - $qty;
    $stmt = $conn->prepare("UPDATE medicines SET quantity = :qty WHERE id = :id");
    $stmt->bindParam(':qty', $new_quantity);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    echo json_encode(['success' => true, 'total_amount' => $total_amount]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
