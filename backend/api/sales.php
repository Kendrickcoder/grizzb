<?php
require 'includes/cors.php';
require 'includes/db.php';
require 'includes/token_auth.php';

require_admin($conn);

$stmt = $conn->query("SELECT * FROM sales ORDER BY sold_at DESC");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
