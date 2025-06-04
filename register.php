<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (is_logged_in()) {
    redirect(is_student() ? 'student/dashboard.php' : 'teacher/dashboard.php');
    exit;
}

$error = [];
$input = [
    'username' => '',
    'email' => '',
    'role' => 'student'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = array_map('trim', $_POST);
    $username = $input['username'];
    $email = $input['email'];
    $password = $input['password'];
    $confirm_password = $input['confirm_password'];
    $role = $input['role'];

    // Validation
    if (empty($username)) {
        $error[] = 'Username is required';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{4,20}$/', $username)) {
        $error[] = 'Username must be 4-20 characters (letters, numbers, underscores)';
    }

    if (empty($email)) {
        $error[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error[] = 'Invalid email format';
    }

    if (empty($password)) {
        $error[] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $error[] = 'Password must be at least 8 characters';
    } elseif (!preg_match('/[A-Z]/', $password) || 
               !preg_match('/[a-z]/', $password) || 
               !preg_match('/[0-9]/', $password)) {
        $error[] = 'Password must contain uppercase, lowercase, and numbers';
    }

    if ($password !== $confirm_password) {
        $error[] = "Passwords don't match";
    }

    if (empty($error)) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->fetchColumn() > 0) {
                throw new Exception('Username or email already exists', 23000);
            }

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) 
                                   VALUES (:username, :email, :password, :role)");
            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => $hashed_password,
                ':role' => $role
            ]);

            $pdo->commit();

            $user_id = $pdo->lastInsertId();
            login_user($user_id, $username, $role);
            redirect($role === 'student' ? 'student/dashboard.php' : 'teacher/dashboard.php');

        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Registration error: " . $e->getMessage());
            $error[] = $e->getCode() === 23000 ? 'Username or email already exists' : 'Registration failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Academic Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-900: #0d1b2a;
            --primary-800: #1b263b;
            --accent-400: #4cc9f0;
            --glass-bg: rgba(255, 255, 255, 0.95);
        }

        body {
            background: linear-gradient(135deg, var(--primary-900) 0%, var(--primary-800) 100%);
            min-height: 100vh;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            padding-top: 80px;
        }

        .portal-navbar {
            height: 80px;
            background: var(--primary-800);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 0 2rem;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-size: 1.75rem;
            color: white !important;
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .navbar-brand i {
            color: var(--accent-400);
            margin-right: 0.75rem;
        }

        .registration-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: calc(100vh - 80px);
            padding: 2rem;
        }

        .registration-card {
            background: var(--glass-bg);
            border-radius: 1.5rem;
            padding: 2rem;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            animation: cardEntrance 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes cardEntrance {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .brand-wrapper {
            text-align: center;
            margin-bottom: 2rem;
        }

        .brand-logo {
            width: 80px;
            height: 80px;
            background: var(--primary-800);
            border-radius: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .form-control {
            border-radius: 0.75rem;
            padding: 1rem;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--accent-400);
            box-shadow: 0 0 0 3px rgba(76, 201, 240, 0.25);
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--accent-400);
        }

        .password-strength {
            height: 4px;
            background: #e9ecef;
            margin-top: 0.5rem;
            border-radius: 2px;
            overflow: hidden;
        }

        .strength-bar {
            height: 100%;
            width: 0;
            background: var(--accent-400);
            transition: width 0.3s ease;
        }

        .btn-register {
            background: var(--accent-400);
            color: var(--primary-900);
            padding: 1rem;
            border-radius: 0.75rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(76, 201, 240, 0.3);
        }

        .requirement-list {
            list-style: none;
            padding-left: 0;
            font-size: 0.9rem;
            color: #6c757d;
        }

        .requirement-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.25rem;
        }

        .requirement-icon {
            color: var(--accent-400);
        }
         .wave-bg {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            z-index: -1;
            opacity: 0.1;
            background: url('data:image/svg+xml;utf8,<svg viewBox="0 0 1440 320" xmlns="http://www.w3.org/2000/svg"><path fill="%234cc9f0" d="M0,128L48,138.7C96,149,192,171,288,170.7C384,171,480,149,576,128C672,107,768,85,864,96C960,107,1056,149,1152,160C1248,171,1344,149,1392,138.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') repeat-x;
            animation: wave 20s linear infinite;
        }

        @keyframes wave {
            0% { background-position-x: 0; }
            100% { background-position-x: 1440px; }
        }


        @media (max-width: 576px) {
            .registration-card {
                padding: 1.5rem;
            }
            
            .brand-logo {
                width: 60px;
                height: 60px;
            }
        }
    </style>
</head>
<body>
    <!-- Fixed Navbar -->
    <nav class="portal-navbar">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-book-open"></i>
            Academix
        </a>
    </nav>
  <div class="wave-bg"></div>
    <!-- Registration Container -->
    <div class="registration-container">
        <div class="registration-card">
            <div class="brand-wrapper">
                <div class="brand-logo">
                    <i class="fas fa-user-plus fa-2x" style="color: var(--accent-400);"></i>
                </div>
                <h2 class="mb-1">Create Account</h2>
                <p class="text-muted">Join our academic community</p>
            </div>

            <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    <?php foreach ($error as $err): ?>
                    <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <form method="POST" novalidate>
                <div class="mb-4">
                    <label class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-user"></i>
                        </span>
                        <input type="text" 
                               class="form-control" 
                               name="username" 
                               value="<?= htmlspecialchars($input['username'], ENT_QUOTES, 'UTF-8') ?>"
                               required
                               pattern="[a-zA-Z0-9_]{4,20}">
                    </div>
                    <small class="text-muted">4-20 characters (letters, numbers, underscores)</small>
                </div>

                <div class="mb-4">
                    <label class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input type="email" 
                               class="form-control" 
                               name="email" 
                               value="<?= htmlspecialchars($input['email'], ENT_QUOTES, 'UTF-8') ?>"
                               required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" 
                               class="form-control" 
                               name="password" 
                               required
                               minlength="8"
                               id="password">
                    </div>
                    <div class="password-strength mt-2">
                        <div class="strength-bar" id="strengthBar"></div>
                    </div>
                    <ul class="requirement-list mt-2">
                        <li class="requirement-item">
                            <i class="fas fa-check requirement-icon" id="lengthCheck"></i>
                            Minimum 8 characters
                        </li>
                        <li class="requirement-item">
                            <i class="fas fa-check requirement-icon" id="upperCheck"></i>
                            Uppercase letter
                        </li>
                        <li class="requirement-item">
                            <i class="fas fa-check requirement-icon" id="lowerCheck"></i>
                            Lowercase letter
                        </li>
                        <li class="requirement-item">
                            <i class="fas fa-check requirement-icon" id="numberCheck"></i>
                            Contains number
                        </li>
                    </ul>
                </div>

                <div class="mb-4">
                    <label class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" 
                               class="form-control" 
                               name="confirm_password" 
                               required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Account Type</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-user-tag"></i>
                        </span>
                        <select class="form-select" name="role" required>
                            <option value="student" <?= $input['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                            <option value="teacher" <?= $input['role'] === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn-register w-100">
                    <i class="fas fa-user-plus me-2"></i>Create Account
                </button>
            </form>

            <div class="text-center mt-4 pt-3 border-top">
                <p class="text-muted mb-0 small">
                    Already have an account? 
                    <a href="login.php" class="text-decoration-none fw-bold" style="color: var(--accent-400);">
                        Login here
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('password').addEventListener('input', function(e) {
            const password = e.target.value;
            const strengthBar = document.getElementById('strengthBar');
            const checks = {
                length: document.getElementById('lengthCheck'),
                upper: document.getElementById('upperCheck'),
                lower: document.getElementById('lowerCheck'),
                number: document.getElementById('numberCheck')
            };

            // Check requirements
            const hasLength = password.length >= 8;
            const hasUpper = /[A-Z]/.test(password);
            const hasLower = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);

            // Update checks
            checks.length.style.color = hasLength ? '#4cc9f0' : '#6c757d';
            checks.upper.style.color = hasUpper ? '#4cc9f0' : '#6c757d';
            checks.lower.style.color = hasLower ? '#4cc9f0' : '#6c757d';
            checks.number.style.color = hasNumber ? '#4cc9f0' : '#6c757d';

            // Calculate strength
            const strength = [hasLength, hasUpper, hasLower, hasNumber].filter(Boolean).length;
            strengthBar.style.width = `${(strength / 4) * 100}%`;
        });

        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const hasUpper = /[A-Z]/.test(password);
            const hasLower = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);

            if (!hasUpper || !hasLower || !hasNumber) {
                e.preventDefault();
                alert('Please ensure your password meets all requirements');
            }
        });
    </script>
</body>
</html>