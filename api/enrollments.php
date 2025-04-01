<?php
require_once __DIR__.'/auth.php';
require_once __DIR__.'/../db/config.php';

header('Content-Type: application/json');

// GET /api/enrollments/{student_id}
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['student_id'])) {
    $user = authorize('student');
    $student_id = (int)$_GET['student_id'];

    // Verify student can only access their own enrollments
    if ($user['role'] === 'student' && $user['user_id'] !== $student_id) {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT e.*, s.name as subject_name, s.code as subject_code 
            FROM enrollments e
            JOIN subjects s ON e.subject_id = s.subject_id
            WHERE e.student_id = ?
        ");
        $stmt->execute([$student_id]);
        $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($enrollments);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch enrollments: ' . $e->getMessage()]);
    }
    exit;
}

// POST /api/enrollments
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = authorize('student');
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate input
    if (empty($data['subject_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Subject ID is required']);
        exit;
    }

    try {
        // Check if subject exists
        $stmt = $pdo->prepare("SELECT * FROM subjects WHERE subject_id = ?");
        $stmt->execute([$data['subject_id']]);
        $subject = $stmt->fetch();

        if (!$subject) {
            http_response_code(404);
            echo json_encode(['error' => 'Subject not found']);
            exit;
        }

        // Check if already enrolled
        $stmt = $pdo->prepare("
            SELECT * FROM enrollments 
            WHERE student_id = ? AND subject_id = ?
            AND enrollment_status != 'Dropped'
        ");
        $stmt->execute([$user['user_id'], $data['subject_id']]);
        
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['error' => 'Already enrolled in this subject']);
            exit;
        }

        // Create enrollment
        $enrollment_type = ($user['year_level'] == $subject['year_level']) ? 'Regular' : 'Irregular';
        $stmt = $pdo->prepare("
            INSERT INTO enrollments 
            (student_id, subject_id, enrollment_type, enrollment_status) 
            VALUES (?, ?, ?, 'Pending')
        ");
        $stmt->execute([$user['user_id'], $data['subject_id'], $enrollment_type]);

        // Log enrollment creation
        $enrollment_id = $pdo->lastInsertId();
        $stmt = $pdo->prepare("
            INSERT INTO enrollment_audit 
            (enrollment_id, changed_by, new_status) 
            VALUES (?, ?, 'Pending')
        ");
        $stmt->execute([$enrollment_id, $user['user_id']]);

        http_response_code(201);
        echo json_encode([
            'message' => 'Enrollment request submitted',
            'enrollment_id' => $enrollment_id,
            'subject_name' => $subject['name']
        ]);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);