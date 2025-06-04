<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (is_logged_in()) {
    $redirect = is_student() ? 'student/dashboard.php' : 'teacher/dashboard.php';
    redirect($redirect);
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($username) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                login_user($user['id'], $user['username'], $user['role']);
                session_regenerate_id(true);
                
                $redirect = ($user['role'] === 'student') ? 'student/dashboard.php' : 'teacher/dashboard.php';
                redirect($redirect);
                exit;
            } else {
                $error = "Invalid credentials";
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $error = "System error. Please try again later.";
        }
    } else {
        $error = "Please fill in all fields";
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Academic Portal</title>
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

        /* Fixed Navbar */
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

        /* Login Container */
        .login-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: calc(100vh - 80px);
            padding: 2rem;
        }

        .login-card {
            background: var(--glass-bg);
            border-radius: 1.5rem;
            padding: 2rem;
            width: 100%;
            max-width: 400px;
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

        .form-group {
            position: relative;
            margin-bottom: 1.5rem;
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
            left: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--accent-400);
        }

        .btn-login {
            background: var(--accent-400);
            color: var(--primary-900);
            padding: 1rem;
            border-radius: 0.75rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(76, 201, 240, 0.3);
        }

        .alert-danger {
            animation: shake 0.4s cubic-bezier(0.36, 0.07, 0.19, 0.97);
        }

        @keyframes shake {
            10%, 90% { transform: translateX(-2px); }
            20%, 80% { transform: translateX(4px); }
            30%, 50%, 70% { transform: translateX(-8px); }
            40%, 60% { transform: translateX(8px); }
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
            .login-card {
                padding: 1.5rem;
            }
            
            .brand-logo {
                width: 60px;
                height: 60px;
            }
            
            h2 {
                font-size: 1.5rem;
            }
            
            .navbar-brand {
                font-size: 1.5rem;
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

    <!-- Wave Background -->
    <div class="wave-bg"></div>

    <!-- Login Container -->
    <div class="login-container">
        <div class="login-card">
            <div class="brand-wrapper">
                <div class="brand-logo">
                    <i class="fas fa-graduation-cap fa-2x" style="color: var(--accent-400);"></i>
                </div>
                <h2 class="mb-1">Welcome Back</h2>
                <p class="text-muted">Sign in to continue</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <form method="POST" class="needs-validation" novalidate>
                <div class="form-group mb-4">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" 
                           class="form-control ps-4" 
                           name="username" 
                           placeholder="Username"
                           required
                           value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="form-group mb-4">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" 
                           class="form-control ps-4" 
                           name="password" 
                           placeholder="Password"
                           required>
                </div>

                <button type="submit" class="btn btn-login w-100 mb-3">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </button>

                <div class="text-center mt-3">
                    <a href="forgot-password.php" class="text-decoration-none text-muted small">
                        <i class="fas fa-lock me-2"></i>Forgot Password?
                    </a>
                </div>
            </form>

            <div class="text-center mt-4 pt-3 border-top">
                <p class="text-muted mb-0 small">
                    New here? 
                    <a href="register.php" class="text-decoration-none fw-bold" style="color: var(--accent-400);">
                        Create Account
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (() => {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation')
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>
</html>