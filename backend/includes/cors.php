<?php
/**
 * CORS — allows your frontend (hosted on a different domain) to call
 * this API. We use a Bearer token for auth (not cookies), so we can
 * safely allow any origin here without security issues around
 * credentialed cookies. If you want to lock this down to just your
 * frontend's domain, set ALLOWED_ORIGIN as an env var on this backend.
 */

$allowed = getenv('ALLOWED_ORIGIN') ?: '*';
header("Access-Control-Allow-Origin: $allowed");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Browsers send an OPTIONS preflight before PUT/DELETE/POST-with-JSON
// requests. Answer it immediately with no body.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
