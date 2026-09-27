<?php
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method !== 'POST' && $method !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed.'
    ]);
    exit;
}

$rawBody = file_get_contents('php://input');
$body = [];

if ($rawBody !== false && $rawBody !== '') {
    $decoded = json_decode($rawBody, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $body = $decoded;
    } else {
        parse_str($rawBody, $body);
    }
}

if (empty($body)) {
    $body = $_POST;
}

$response = [
    'status' => 'ok',
    'method' => $method,
    'timestamp' => gmdate('c'),
    'query' => $_GET,
    'body' => $body,
    'message' => 'Public callback endpoint reached successfully.'
];

http_response_code(200);
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
