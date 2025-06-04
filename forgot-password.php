<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Academic Portal</title>
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
            --animation-curve: cubic-bezier(0.25, 0.8, 0.25, 1);
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
            -webkit-backdrop-filter: blur(10px);
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
            letter-spacing: -0.5px;
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
            animation: cardEntrance 0.8s var(--animation-curve);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            position: relative;
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
            from { opacity: 0; transform: translateY(30px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .brand-wrapper {
            text-align: center;
            margin-bottom: 2.5rem;
            position: relative;
            z-index: 1;
        }

        .animated-envelope {
            animation: float 4s ease-in-out infinite, pulse 2s ease-in-out infinite;
            font-size: 3.5rem;
            color: var(--accent-400);
            display: inline-block;
            transform-origin: center;
            margin-bottom: 1.5rem;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-10px) rotate(5deg); }
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }

        .form-control {
            border-radius: 0.75rem;
            padding: 1rem 1.25rem;
            border: 2px solid #e2e8f0;
            transition: all 0.3s var(--animation-curve);
            font-size: 1rem;
            background: rgba(255, 255, 255, 0.8);
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
            letter-spacing: 0.5px;
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

        .login-link {
            color: var(--accent-400);
            font-weight: 600;
            text-decoration: none;
            position: relative;
            transition: all 0.3s var(--animation-curve);
        }

        .login-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--accent-400);
            transition: width 0.3s var(--animation-curve);
        }

        .login-link:hover {
            color: var(--accent-500);
        }

        .login-link:hover::after {
            width: 100%;
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

        /* Success message animation */
        .alert-success {
            animation: slideDown 0.5s var(--animation-curve);
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="portal-navbar fixed-top" id="navbar">
        <div class="container-fluid">
            <a class="navbar-brand text-white ms-3" href="index.php">
                <i class="fas fa-book-open me-2"></i>
                Academix
            </a>
        </div>
    </nav>

    <!-- Wave Background -->
    <div class="wave-bg"></div>

    <!-- Main Content -->
    <div class="password-container">
        <div class="password-card">
            <div class="brand-wrapper">
                <i class="fas fa-envelope animated-envelope"></i>
                <h2 class="mt-3 mb-2">Forgot Password?</h2>
                <p class="text-muted">Enter your email to receive a password reset link</p>
            </div>

            <!-- [Keep PHP error/success messages from previous implementation] -->
            <!-- Example success message (remove if not needed) -->
            <div class="alert alert-success d-none" role="alert" id="successMessage">
                <i class="fas fa-check-circle me-2"></i> Reset link sent successfully!
            </div>

            <form method="POST" id="resetForm">
                <div class="mb-4">
                    <label class="form-label fw-semibold">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input type="email" 
                               class="form-control" 
                               name="email" 
                               required
                               placeholder="student@university.edu"
                               autocomplete="email"
                               id="emailInput">
                    </div>
                    <div class="form-text text-end small mt-1">
                        <span id="emailValidation"></span>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 mt-2" id="submitBtn">
                    <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                </button>
            </form>

            <div class="text-center mt-4 pt-3 border-top">
                <p class="text-muted mb-0 small">
                    Remember your password? 
                    <a href="login.php" class="login-link">
                        Login here
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 10) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Form submission simulation (replace with actual form handling)
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show success message
            const successMessage = document.getElementById('successMessage');
            successMessage.classList.remove('d-none');
            
            // Disable button during "processing"
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
            
            // Simulate API call
            setTimeout(function() {
                submitBtn.innerHTML = '<i class="fas fa-check me-2"></i>Sent!';
                
                // Reset form after 3 seconds
                setTimeout(function() {
                    successMessage.classList.add('d-none');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Send Reset Link';
                    document.getElementById('resetForm').reset();
                }, 3000);
            }, 1500);
        });

        // Email validation
        document.getElementById('emailInput').addEventListener('input', function() {
            const email = this.value;
            const validationText = document.getElementById('emailValidation');
            
            if (email.length === 0) {
                validationText.textContent = '';
                validationText.style.color = '';
                return;
            }
            
            const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            
            if (isValid) {
                validationText.textContent = '✓ Valid email';
                validationText.style.color = '#38a169';
            } else {
                validationText.textContent = 'Please enter a valid email';
                validationText.style.color = '#e53e3e';
            }
        });
    </script>
</body>
</html>