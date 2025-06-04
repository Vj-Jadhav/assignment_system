<?php
// get_new_assignments.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

if (!is_student()) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

$student_id = $_SESSION['user_id'];
$last_check = $_GET['last_check'] ?? date('Y-m-d H:i:s', strtotime('-5 minutes'));

try {
    $stmt = $pdo->prepare("
        SELECT a.id, a.title, a.description, a.due_date, c.name AS course_name
        FROM assignments a
        JOIN courses c ON a.course_id = c.id
        WHERE c.id IN (SELECT course_id FROM enrollments WHERE student_id = ?)
        AND a.created_at > ?
        ORDER BY a.created_at DESC
    ");
    $stmt->execute([$student_id, $last_check]);
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($assignments);
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Database error']);
}