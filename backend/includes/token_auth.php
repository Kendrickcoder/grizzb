<?php
/**
 * Token-based auth. The frontend logs in via api/login.php, gets a
 * token back, stores it (localStorage), and sends it on every request
 * as:  Authorization: Bearer <token>
 *
 * We use a token instead of PHP sessions/cookies because the frontend
 * and backend live on two different domains — cross-domain cookies are
 * unreliable (browsers increasingly block third-party cookies). A
 * bearer token in a header has no such problem.
 */

function bearer_token(): ?string {
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!$header && function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        $header = $headers['Authorization'] ?? '';
    }
    if (preg_match('/Bearer\s+(.+)/i', $header, $m)) {
        return trim($m[1]);
    }
    return null;
}

function current_user(PDO $conn): ?array {
    $token = bearer_token();
    if (!$token) return null;

    $stmt = $conn->prepare("SELECT u.* FROM sessions s
                             JOIN users u ON u.id = s.user_id
                             WHERE s.token = :token
                             AND s.created_at > (NOW() - INTERVAL 7 DAY)");
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $user ?: null;
}

function require_login(PDO $conn): array {
    $user = current_user($conn);
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Not logged in']);
        exit;
    }
    return $user;
}

function require_admin(PDO $conn): array {
    $user = require_login($conn);
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Admin access required']);
        exit;
    }
    return $user;
}
