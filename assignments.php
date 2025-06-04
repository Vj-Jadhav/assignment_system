<?php
// assignments.php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

// Get assignment ID from URL
$assignment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$assignment_id) {
    header('Location: dashboard.php');
    exit;
}

// Get assignment details
try {
    $stmt = $pdo->prepare("
        SELECT a.*, c.name AS course_name, c.code AS course_code, 
               u.username AS teacher_name
        FROM assignments a
        JOIN courses c ON a.course_id = c.id
        JOIN users u ON a.teacher_id = u.id
        WHERE a.id = ?
    ");
    $stmt->execute([$assignment_id]);
    $assignment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$assignment) {
        throw new Exception("Assignment not found");
    }
    
    // Check if current user is the teacher or enrolled student
    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['role'];
    
    if ($user_role === 'teacher' && $assignment['teacher_id'] !== $user_id) {
        throw new Exception("You don't have permission to view this assignment");
    }
    
    if ($user_role === 'student') {
        $stmt = $pdo->prepare("
            SELECT 1 FROM enrollments 
            WHERE course_id = ? AND student_id = ?
        ");
        $stmt->execute([$assignment['course_id'], $user_id]);
        if (!$stmt->fetch()) {
            throw new Exception("You are not enrolled in this course");
        }
    }
    
    // Format dates
    $due_date = new DateTime($assignment['due_date']);
    $created_at = new DateTime($assignment['created_at']);
    
    // Get submissions (for teacher)
    $submissions = [];
    if ($user_role === 'teacher') {
        $stmt = $pdo->prepare("
            SELECT s.*, u.username AS student_name
            FROM submissions s
            JOIN users u ON s.student_id = u.id
            WHERE s.assignment_id = ?
            ORDER BY s.submitted_at DESC
        ");
        $stmt->execute([$assignment_id]);
        $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get student submission (for student)
    $student_submission = null;
    if ($user_role === 'student') {
        $stmt = $pdo->prepare("
            SELECT * FROM submissions 
            WHERE assignment_id = ? AND student_id = ?
            LIMIT 1
        ");
        $stmt->execute([$assignment_id, $user_id]);
        $student_submission = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignment Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .assignment-header {
            background: linear-gradient(135deg, #4e73df, #224abe);
            color: white;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .file-download-box {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
        }
        .submission-card {
            transition: all 0.3s;
        }
        .submission-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .status-badge {
            font-size: 0.85rem;
            padding: 0.5em 0.8em;
        }
    </style>
</head>
<body>
    <?php 
    if ($_SESSION['role'] === 'teacher') {
        include __DIR__ . '/includes/teacher_nav.php';

    } else {
        include __DIR__ . '/../includes/student_nav.php';
    }
    ?>
    
    <div class="container py-4">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
            <div class="text-center mt-4">
                <a href="dashboard.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        <?php else: ?>
            <!-- Assignment Header -->
            <div class="assignment-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="display-5 fw-bold"><?= htmlspecialchars($assignment['title']) ?></h1>
                        <p class="lead mb-0"><?= htmlspecialchars($assignment['course_name']) ?> (<?= $assignment['course_code'] ?>)</p>
                        <p class="mb-0">Created by: <?= htmlspecialchars($assignment['teacher_name']) ?></p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="bg-white text-dark p-3 rounded">
                            <h5 class="mb-1">Due Date</h5>
                            <p class="mb-0 fw-bold"><?= $due_date->format('F j, Y g:i A') ?></p>
                            <p class="mb-0 small">Created: <?= $created_at->format('M j, Y') ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Assignment Content -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h3 class="mb-0"><i class="fas fa-info-circle me-2"></i>Instructions</h3>
                        </div>
                        <div class="card-body">
                            <div class="assignment-content">
                                <?= nl2br(htmlspecialchars($assignment['description'])) ?>
                            </div>
                            
                            <?php if (($assignment['file_path'])): ?>
                                <div class="mt-4">
                                    <h5><i class="fas fa-paperclip me-2"></i>Attached File</h5>
                                    <div class="file-download-box">
                                        <i class="fas fa-file-pdf fa-3x text-danger mb-3"></i>
                                        <p class="mb-2"><?= basename($assignment['file_path']) ?></p>
                                        <a href="../uploads/assignments/<?= $assignment['file_path'] ?>" 
                                           class="btn btn-primary" download>
                                            <i class="fas fa-download me-2"></i>Download File
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Submission Area for Students -->
                    <?php if ($_SESSION['role'] === 'student'): ?>
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h3 class="mb-0"><i class="fas fa-upload me-2"></i>Your Submission</h3>
                            </div>
                            <div class="card-body">
                                <?php if (($student_submission)): ?>
                                    <div class="alert alert-success">
                                        <h5><i class="fas fa-check-circle me-2"></i>Submitted</h5>
                                        <p class="mb-1">On: <?= date('M j, Y g:i A', strtotime($student_submission['submitted_at'])) ?></p>
                                        
                                        <?php if (($student_submission['file_path'])): ?>
                                            <p class="mb-2">File: <?= basename($student_submission['file_path']) ?></p>
                                            <a href="../uploads/submissions/<?= $student_submission['file_path'] ?>" 
                                               class="btn btn-sm btn-outline-primary" download>
                                                Download Submission
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($student_submission['grade'] !== null): ?>
                                            <div class="mt-3 p-3 bg-light rounded">
                                                <h5>Grading Results:</h5>
                                                <p class="display-6 mb-1">
                                                    Grade: <strong><?= $student_submission['grade'] ?>/<?= $assignment['max_score'] ?></strong>
                                                </p>
                                                <?php if (($student_submission['feedback'])): ?>
                                                    <div class="mt-2">
                                                        <h6>Feedback:</h6>
                                                        <p><?= nl2br(htmlspecialchars($student_submission['feedback'])) ?></p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <p class="mt-3 text-info">
                                                <i class="fas fa-clock me-2"></i>Your submission is awaiting grading
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($assignment['allow_resubmit']): ?>
                                        <div class="mt-4">
                                            <a href="submit.php?assignment_id=<?= $assignment_id ?>" 
                                               class="btn btn-warning">
                                                <i class="fas fa-redo me-2"></i>Resubmit Assignment
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <p class="mb-3">You haven't submitted this assignment yet</p>
                                        <a href="submit.php?assignment_id=<?= $assignment_id ?>" 
                                           class="btn btn-primary">
                                            <i class="fas fa-upload me-2"></i>Submit Assignment
                                        </a>
                                        
                                        <?php if ($due_date < new DateTime()): ?>
                                            <div class="alert alert-danger mt-3">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                This assignment is past the due date
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="col-lg-4">
                    <!-- Assignment Metadata -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h3 class="mb-0"><i class="fas fa-cog me-2"></i>Assignment Details</h3>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Status:</span>
                                    <span class="fw-bold">
                                        <?php if ($due_date < new DateTime()): ?>
                                            <span class="badge bg-danger">Closed</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Open</span>
                                        <?php endif; ?>
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Max Score:</span>
                                    <span class="fw-bold"><?= $assignment['max_score'] ?> points</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Late Submissions:</span>
                                    <span class="fw-bold">
                                        <?= $assignment['allow_late'] ? 'Allowed' : 'Not Allowed' ?>
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Resubmissions:</span>
                                    <span class="fw-bold">
                                        <?= $assignment['allow_resubmit'] ? 'Allowed' : 'Not Allowed' ?>
                                    </span>
                                </li>
                                <li class="list-group-item">
                                    <small class="text-muted">Assignment ID: <?= $assignment['id'] ?></small>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Teacher Actions -->
                    <?php if ($_SESSION['role'] === 'teacher'): ?>
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h3 class="mb-0"><i class="fas fa-tasks me-2"></i>Assignment Management</h3>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="teacher/submissions.php?assignment_id=<?= $assignment_id ?>"
 
                                       class="btn btn-primary">
                                        <i class="fas fa-eye me-2"></i>View Submissions
                                        <span class="badge bg-light text-dark ms-2">
                                            <?= count($submissions) ?>
                                        </span>
                                    </a>
                                    <a href="teacher/edit_submission.php?id=<?= $assignment_id ?>" 
                                       class="btn btn-outline-secondary">
                                        <i class="fas fa-edit me-2"></i>Edit Assignment
                                    </a>
                                </div>
                                
                                <hr>
                                
                                <h5>Quick Stats:</h5>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Total Submissions:</span>
                                        <span class="fw-bold"><?= count($submissions) ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Graded:</span>
                                        <span class="fw-bold">
                                            <?= count(array_filter($submissions, fn($s) => $s['grade'] !== null)) ?>
                                        </span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Average Grade:</span>
                                        <span class="fw-bold">
                                            <?php
                                            $graded = array_filter($submissions, fn($s) => $s['grade'] !== null);
                                            $avg = $graded ? array_sum(array_column($graded, 'grade')) / count($graded) : 0;
                                            echo number_format($avg, 1) . '/' . $assignment['max_score'];
                                            ?>
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Success Alert for Newly Created Assignment -->
            <?php if (isset($_GET['new']) && $_GET['new'] == 1): ?>
                <div class="alert alert-success alert-dismissible fade show mt-4">
                    <h4><i class="fas fa-check-circle me-2"></i>Assignment Published Successfully!</h4>
                    <p class="mb-0">Your assignment has been published to students in real-time.</p>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Back Button -->
            <div class="text-center mt-4">
                <a href="teacher/dashboard.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>