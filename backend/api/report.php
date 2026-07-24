<?php
require 'includes/cors.php';
require 'includes/db.php';
require 'includes/token_auth.php';

require_admin($conn);

$near_expiry = $conn->query("SELECT * FROM medicines
    WHERE expiry_date IS NOT NULL
    AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    ORDER BY expiry_date ASC")->fetchAll(PDO::FETCH_ASSOC);

$expired = $conn->query("SELECT * FROM medicines
    WHERE expiry_date < CURDATE() AND expiry_date IS NOT NULL
    ORDER BY expiry_date ASC")->fetchAll(PDO::FETCH_ASSOC);

$low_stock = $conn->query("SELECT * FROM medicines
    WHERE quantity < 10
    ORDER BY quantity ASC")->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'near_expiry' => $near_expiry,
    'expired'     => $expired,
    'low_stock'   => $low_stock,
]);
