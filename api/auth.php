<?php
require_once __DIR__.'/../db/config.php';

function authenticate() {
    if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Authorization header missing']);
        exit;
    }

    $auth_header = $_SERVER['HTTP_AUTHORIZATION'];
    $token = str_replace('Bearer ', '', $auth_header);

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE api_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if (!$user) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid authentication token']);
            exit;
        }

        return $user;
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error during authentication']);
        exit;
    }
}

function authorize($required_role) {
    $user = authenticate();
    
    if ($user['role'] !== $required_role && $user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Insufficient permissions']);
        exit;
    }

    return $user;
}