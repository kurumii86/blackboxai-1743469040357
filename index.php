<?php
require_once __DIR__.'/db/config.php';
require_once __DIR__.'/api/auth.php';

// Simple router
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base_path = '/';

// Authentication check for protected routes
$protected_routes = ['/dashboard', '/api/enrollments'];
if (in_array($request, $protected_routes)) {
    try {
        $user = authenticate();
    } catch (Exception $e) {
        http_response_code(401);
        header('Location: /login');
        exit;
    }
}

switch ($request) {
    case $base_path:
        header('Location: /dashboard');
        exit;
    case $base_path.'dashboard':
        require __DIR__.'/views/student-dashboard.php';
        break;
    case $base_path.'api/enrollments':
        require __DIR__.'/api/enrollments.php';
        break;
    case $base_path.'api/subjects':
        header('Content-Type: application/json');
        try {
            $year = isset($_GET['year']) ? (int)$_GET['year'] : null;
            $semester = isset($_GET['semester']) ? $_GET['semester'] : null;
            
            $query = "SELECT * FROM subjects";
            $params = [];
            
            if ($year && $semester) {
                $query .= " WHERE year_level = ? AND semester = ?";
                $params = [$year, $semester];
            }
            
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($subjects);
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch subjects']);
        }
        break;
    case $base_path.'api/auth/me':
        header('Content-Type: application/json');
        try {
            $user = authenticate();
            echo json_encode([
                'user_id' => $user['user_id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'year_level' => $user['year_level'],
                'semester' => $user['semester']
            ]);
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => 'Not authenticated']);
        }
        break;
    case $base_path.'login':
        require __DIR__.'/views/login.php';
        break;
    default:
        http_response_code(404);
        require __DIR__.'/views/404.php';
        break;
}