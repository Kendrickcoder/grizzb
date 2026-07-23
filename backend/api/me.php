<?php
require 'includes/cors.php';
require 'includes/db.php';
require 'includes/token_auth.php';

$user = require_login($conn);
echo json_encode([
    'id'       => $user['id'],
    'fullname' => $user['fullname'],
    'username' => $user['username'],
    'role'     => $user['role'],
]);
