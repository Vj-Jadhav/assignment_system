<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Initialize variables
$error = '';
$success = '';
$validToken = false;
$email = '';

// Validate reset token
if (isset($_GET['token']) && isset($_GET['email'])) {
    $token = $_GET['token'];
    $email = urldecode($_GET['email']);

    try {
        $stmt = $pdo->prepare("SELECT * FROM password_resets 
                              WHERE email = ? 
                              AND expires_at > NOW() 
                              AND token = ?");
        $stmt->execute([$email, hash('sha256', $token)]);
        $resetRequest = $stmt->fetch();

        if (!$resetRequest) {
            $error = 'Invalid or expired reset link. Please request a new password reset.';
        } else {
            $validToken = true;
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $error = 'A system error occurred. Please try again later.';
    }
}

// Process password reset form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate inputs
    if (empty($password)) {
        $error = 'Please enter a new password';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = 'Password must contain at least one uppercase letter';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $error = 'Password must contain at least one number';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Update password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([$hashedPassword, $email]);
            
            // Delete used reset token
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->execute([$email]);
            
            $pdo->commit();
            
            $success = 'Password updated successfully! You can now login with your new password.';
            $validToken = false;

        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Password reset error: " . $e->getMessage());
            $error = 'Failed to reset password. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Academix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-900: #0d1b2a;
            --primary-800: #1b263b;
            --primary-700: #415a77;
            --accent-400: #4cc9f0;
            --accent-500: #38b6db;
            --glass-bg: rgba(255, 255, 255, 0.97);
            --text-dark: #2d3748;
            --text-muted: #718096;
            --animation-curve: cubic-bezier(0.4, 0, 0.2, 1);
            --success-green: #38a169;
            --error-red: #e53e3e;
        }

        body {
            background: linear-gradient(135deg, var(--primary-900) 0%, var(--primary-800) 100%);
            min-height: 100vh;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            padding-top: 80px;
            color: var(--text-dark);
        }

        .portal-navbar {
            height: 80px;
            background: rgba(27, 38, 59, 0.9);
            backdrop-filter: blur(10px);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 0 2rem;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s var(--animation-curve);
        }

        .portal-navbar.scrolled {
            height: 70px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.15);
        }

        .navbar-brand {
            font-size: 1.75rem;
            font-weight: 700;
            color: white !important;
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .navbar-brand i {
            color: var(--accent-400);
            margin-right: 0.75rem;
            transition: transform 0.3s var(--animation-curve);
        }

        .navbar-brand:hover i {
            transform: rotate(-15deg);
        }

        .password-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: calc(100vh - 80px);
            padding: 2rem;
        }

        .password-card {
            background: var(--glass-bg);
            border-radius: 1.5rem;
            padding: 3rem;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            animation: cardEntrance 0.6s var(--animation-curve);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .password-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                to bottom right,
                rgba(76, 201, 240, 0.1) 0%,
                rgba(76, 201, 240, 0) 50%
            );
            transform: rotate(30deg);
            pointer-events: none;
        }

        @keyframes cardEntrance {
            from { 
                opacity: 0; 
                transform: translateY(30px) scale(0.98); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0) scale(1); 
            }
        }

        .form-control {
            border-radius: 0.75rem;
            padding: 1rem 1.25rem;
            border: 2px solid #e2e8f0;
            transition: all 0.3s var(--animation-curve);
            background: rgba(255, 255, 255, 0.9);
        }

        .form-control:focus {
            border-color: var(--accent-400);
            box-shadow: 0 0 0 3px rgba(76, 201, 240, 0.2);
            background: white;
        }

        .input-group-text {
            background: linear-gradient(135deg, var(--accent-400) 0%, var(--accent-500) 100%);
            color: white;
            border: none;
            border-radius: 0.75rem 0 0 0.75rem !important;
            padding: 1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent-400) 0%, var(--accent-500) 100%);
            border: none;
            padding: 1rem;
            border-radius: 0.75rem;
            font-weight: 600;
            transition: all 0.3s var(--animation-curve);
            position: relative;
            overflow: hidden;
        }

        .btn-primary::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent 40%, rgba(255,255,255,0.2) 100%);
            transition: all 0.3s var(--animation-curve);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(76, 201, 240, 0.3);
        }

        .btn-primary:hover::after {
            transform: translateX(100%);
        }

        .password-strength {
            height: 6px;
            background: #e2e8f0;
            border-radius: 3px;
            margin-top: 0.5rem;
            overflow: hidden;
            position: relative;
        }

        .strength-indicator {
            height: 100%;
            width: 0;
            transition: all 0.3s var(--animation-curve);
        }

        .alert {
            border: none;
            border-radius: 0.75rem;
            padding: 1rem 1.25rem;
        }

        .requirements-list {
            list-style: none;
            padding-left: 0;
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        .requirements-list li {
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
        }

        .requirement-met {
            color: var(--success-green);
        }

        .requirement-unmet {
            color: var(--text-muted);
        }

        .requirement-icon {
            margin-right: 0.5rem;
            font-size: 0.75rem;
        }

        .wave-bg {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            z-index: -1;
            opacity: 0.08;
            background: url('data:image/svg+xml;utf8,<svg viewBox="0 0 1440 320" xmlns="http://www.w3.org/2000/svg"><path fill="%234cc9f0" d="M0,128L48,138.7C96,149,192,171,288,170.7C384,171,480,149,576,128C672,107,768,85,864,96C960,107,1056,149,1152,160C1248,171,1344,149,1392,138.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') repeat-x;
            animation: wave 15s linear infinite;
        }

        @keyframes wave {
            0% { background-position-x: 0; }
            100% { background-position-x: 1440px; }
        }

        @media (max-width: 576px) {
            .password-card {
                padding: 2rem 1.5rem;
                border-radius: 1rem;
            }
            
            .portal-navbar {
                padding: 0 1rem;
            }
            
            .navbar-brand {
                font-size: 1.5rem;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            * {
                animation: none !important;
                transition: none !important;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="portal-navbar fixed-top" id="navbar">
        <div class="container-fluid">
            <a class="navbar-brand text-white" href="index.php">
                <i class="fas fa-book-open"></i>
                Academix
            </a>
        </div>
    </nav>

    <!-- Wave Background -->
    <div class="wave-bg"></div>

    <!-- Main Content -->
    <div class="password-container">
        <div class="password-card">
            <div class="text-center mb-4">
                <i class="fas fa-lock fa-3x mb-3" style="color: var(--accent-400);"></i>
                <h2>Reset Password</h2>
                <?php if ($validToken): ?>
                    <p class="text-muted">Create a strong new password</p>
                <?php endif; ?>
            </div>

            <!-- Error Message -->
            <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <!-- Success Message -->
            <?php if ($success): ?>
            <div class="alert alert-success" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i>
                    <div>
                        <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
                        <div class="mt-3">
                            <a href="login.php" class="btn btn-success w-100">
                                <i class="fas fa-sign-in-alt me-2"></i>Continue to Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Password Reset Form -->
            <?php if ($validToken): ?>
            <form method="POST" id="resetForm">
                <div class="mb-4">
                    <label class="form-label fw-semibold">New Password</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" 
                               class="form-control" 
                               name="password" 
                               required
                               minlength="8"
                               id="passwordInput"
                               autocomplete="new-password"
                               aria-describedby="passwordHelp">
                        <button class="btn btn-outline-secondary toggle-password" type="button">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength mt-2">
                        <div class="strength-indicator" id="strengthIndicator"></div>
                    </div>
                    <div id="passwordHelp" class="form-text">
                        <ul class="requirements-list mt-2">
                            <li id="req-length" class="requirement-unmet">
                                <span class="requirement-icon"><i class="fas fa-circle"></i></span>
                                At least 8 characters
                            </li>
                            <li id="req-uppercase" class="requirement-unmet">
                                <span class="requirement-icon"><i class="fas fa-circle"></i></span>
                                At least 1 uppercase letter
                            </li>
                            <li id="req-number" class="requirement-unmet">
                                <span class="requirement-icon"><i class="fas fa-circle"></i></span>
                                At least 1 number
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" 
                               class="form-control" 
                               name="confirm_password" 
                               required
                               id="confirmPassword"
                               autocomplete="new-password">
                        <button class="btn btn-outline-secondary toggle-password" type="button">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="form-text text-end small mt-1">
                        <span id="passwordMatch"></span>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 mt-3" id="submitBtn">
                    <i class="fas fa-save me-2"></i>Update Password
                </button>
            </form>
            <?php elseif (!$success): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= isset($error) ? htmlspecialchars($error, ENT_QUOTES, 'UTF-8') : 'Invalid password reset request' ?>
                    <div class="mt-3">
                        <a href="forgot-password.php" class="btn btn-outline-warning">
                            <i class="fas fa-redo me-2"></i>Request New Reset Link
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <div class="text-center mt-4 pt-3 border-top">
                <a href="login.php" class="text-decoration-none small" style="color: var(--accent-400);">
                    <i class="fas fa-arrow-left me-2"></i>Return to Login
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 10);
        });

        // Password visibility toggle
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.closest('.input-group').querySelector('input');
                const icon = this.querySelector('i');
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            });
        });

        // Password strength indicator
        document.getElementById('passwordInput').addEventListener('input', function(e) {
            const password = e.target.value;
            const strength = calculatePasswordStrength(password);
            const indicator = document.getElementById('strengthIndicator');
            
            // Update strength meter
            indicator.style.width = `${strength}%`;
            
            // Update color based on strength
            if (strength < 40) {
                indicator.style.backgroundColor = 'var(--error-red)';
            } else if (strength < 70) {
                indicator.style.backgroundColor = '#f6ad55'; // orange
            } else {
                indicator.style.backgroundColor = 'var(--success-green)';
            }
            
            // Update requirement checks
            updateRequirement('req-length', password.length >= 8);
            updateRequirement('req-uppercase', /[A-Z]/.test(password));
            updateRequirement('req-number', /[0-9]/.test(password));
        });

        function updateRequirement(id, isMet) {
            const element = document.getElementById(id);
            const icon = element.querySelector('.fa-circle');
            
            if (isMet) {
                element.classList.remove('requirement-unmet');
                element.classList.add('requirement-met');
                icon.classList.remove('fa-circle');
                icon.classList.add('fa-check-circle');
            } else {
                element.classList.remove('requirement-met');
                element.classList.add('requirement-unmet');
                icon.classList.remove('fa-check-circle');
                icon.classList.add('fa-circle');
            }
        }

        // Password match validation
        document.getElementById('confirmPassword').addEventListener('input', function(e) {
            const confirm = e.target.value;
            const password = document.getElementById('passwordInput').value;
            const matchText = document.getElementById('passwordMatch');
            
            if (confirm === '') {
                matchText.textContent = '';
                return;
            }
            
            if (password === confirm) {
                matchText.textContent = '✓ Passwords match';
                matchText.style.color = 'var(--success-green)';
            } else {
                matchText.textContent = '✗ Passwords do not match';
                matchText.style.color = 'var(--error-red)';
            }
        });

        function calculatePasswordStrength(password) {
            let strength = 0;
            if (password.length >= 8) strength += 40;
            if (/[A-Z]/.test(password)) strength += 20;
            if (/[0-9]/.test(password)) strength += 20;
            if (/[^A-Za-z0-9]/.test(password)) strength += 20;
            return Math.min(strength, 100);
        }

        // Form submission handler
        document.getElementById('resetForm')?.addEventListener('submit', function(e) {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = `<i class="fas fa-spinner fa-spin me-2"></i>Updating...`;
        });
    </script>
</body>
</html>