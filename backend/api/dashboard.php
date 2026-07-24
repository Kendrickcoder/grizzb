<?php
require 'includes/cors.php';
require 'includes/db.php';
require 'includes/token_auth.php';

require_admin($conn);

echo json_encode([
    'total'       => (int)$conn->query("SELECT COUNT(*) FROM medicines")->fetchColumn(),
    'low_stock'   => (int)$conn->query("SELECT COUNT(*) FROM medicines WHERE quantity < 10")->fetchColumn(),
    'expired'     => (int)$conn->query("SELECT COUNT(*) FROM medicines WHERE expiry_date < CURDATE() AND expiry_date IS NOT NULL")->fetchColumn(),
    'total_value' => (float)($conn->query("SELECT SUM(price * quantity) FROM medicines")->fetchColumn() ?? 0),
]);
