<?php
// index.php - Professional Student Assignment Portal
declare(strict_types=1);

// Initialize application
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

// Secure session management
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start([
        'name' => 'ASSIGNMENT_PORTAL',
        'cookie_lifetime' => 86400,
        'cookie_secure' => true,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict',
        'use_strict_mode' => true
    ]);
}

// Redirect authenticated students
if (is_logged_in() && is_student()) {
    header('Location: /assignment_system/student/dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="University Assignment Submission Portal">
    <title>Academic Portal | Assignment Management System</title>
    
    <!-- Preload assets -->
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" as="style">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Primary CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            /* Modern Academic Color Palette */
            --primary-900: #0d1b2a;
            --primary-800: #1b263b;
            --primary-700: #415a77;
            --primary-600: #778da9;
            --primary-500: #a8c0d6;
            --accent-400: #4cc9f0;
            --accent-300: #70e1ff;
            --success-400: #38b000;
            --warning-400: #ffaa00;
            --danger-400: #ef476f;
            --light-100: #f8f9fa;
            --light-200: #e9ecef;
            --dark-800: #212529;
            --dark-700: #343a40;
            
            --header-height: 80px;
            --nav-width: 280px;
            --ease-out-quart: cubic-bezier(0.25, 1, 0.5, 1);
            --section-spacing: 6rem;
        }

        /* Base Styles */
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--light-100);
            color: var(--dark-800);
            line-height: 1.65;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, .navbar-brand {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
        }

        /* Navigation Bar */
        .navbar {
            height: var(--header-height);
            background-color: var(--primary-800);
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            padding: 0 2rem;
        }

        .navbar-brand {
            font-size: 1.75rem;
            color: white;
            display: flex;
            align-items: center;
        }

        .navbar-brand i {
            margin-right: 0.75rem;
            color: var(--accent-400);
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.85);
            font-weight: 500;
            padding: 0.5rem 1rem;
            margin: 0 0.25rem;
            border-radius: 0.5rem;
            transition: all 0.3s var(--ease-out-quart);
        }

        .nav-link:hover, .nav-link:focus {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-link.active {
            color: white;
            background-color: var(--accent-400);
        }

        .btn-portal {
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            border-radius: 0.5rem;
            transition: all 0.3s var(--ease-out-quart);
        }

        .btn-portal-primary {
            background-color: var(--accent-400);
            border: 2px solid var(--accent-400);
            color: var(--primary-900);
        }

        .btn-portal-primary:hover {
            background-color: var(--accent-300);
            border-color: var(--accent-300);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(76, 201, 240, 0.3);
        }

        .btn-portal-outline-light {
            border: 2px solid white;
            color: white;
        }

        .btn-portal-outline-light:hover {
            background-color: white;
            color: var(--primary-800);
        }

        /* Header Section */
        .portal-header {
            background: linear-gradient(135deg, var(--primary-800) 0%, var(--primary-900) 100%);
            padding: calc(var(--header-height) + 2rem) 0 6rem;
            clip-path: polygon(0 0, 100% 0, 100% 90%, 0 100%);
            margin-top: calc(-1 * var(--header-height));
            color: white;
            position: relative;
            overflow: hidden;
        }

        .portal-header::after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: -10%;
            right: -10%;
            height: 100px;
            background: var(--light-100);
            transform: rotate(-2deg);
            z-index: 1;
        }

        .display-title {
            font-weight: 700;
            letter-spacing: -0.025em;
            line-height: 1.2;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Feature Cards */
        .feature-card {
            border: 0;
            border-radius: 1rem;
            background: white;
            transition: all 0.4s var(--ease-out-quart);
            overflow: hidden;
            height: 100%;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .feature-card:hover {
            transform: translateY(-0.5rem);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(45deg, var(--primary-600), var(--primary-500));
            color: white;
            border-radius: 1rem;
            font-size: 1.75rem;
            margin-bottom: 1.5rem;
        }

        /* Steps Section */
        .step-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 3rem;
            height: 3rem;
            background-color: var(--accent-400);
            color: var(--primary-900);
            border-radius: 50%;
            font-weight: 700;
            margin-right: 1.5rem;
            flex-shrink: 0;
        }

        /* Footer */
        .portal-footer {
            background: var(--dark-800);
            color: white;
            padding-top: 4rem;
            position: relative;
        }

        .portal-footer::before {
            content: '';
            position: absolute;
            top: -50px;
            left: 0;
            right: 0;
            height: 50px;
            background: var(--light-100);
            clip-path: polygon(0 100%, 100% 0, 100% 100%);
        }

        .footer-link {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .footer-link:hover {
            color: var(--accent-400);
            padding-left: 0.25rem;
        }

        /* Utility Classes */
        .bg-accent-gradient {
            background: linear-gradient(135deg, var(--accent-400), var(--accent-300));
        }

        .text-accent {
            color: var(--accent-400);
        }

        /* Animations */
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .floating {
            animation: float 3s ease-in-out infinite;
        }

        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .navbar {
                padding: 0 1rem;
            }
            
            .portal-header {
                padding: calc(var(--header-height) + 1rem) 0 4rem;
            }
        }

        @media (max-width: 768px) {
            :root {
                --section-spacing: 4rem;
            }
            
            .display-title {
                font-size: 2.5rem;
            }
        }

         :root {
        --header-height: 80px; /* Height of the navbar */
    }

    /* Add padding to body equal to navbar height */
    body {
        padding-top: var(--header-height);
    }

    .navbar {
        height: var(--header-height);
        /* ... existing navbar styles ... */
    }

    /* Adjust header padding */
    .portal-header {
        padding: 10rem 0 6rem; /* Reduced top padding since body has padding */
        margin-top: calc(-1 * var(--header-height)); /* Compensate for body padding */
        /* ... existing header styles ... */
    }

    /* For mobile view */
    @media (max-width: 768px) {
        body {
            padding-top: 60px; /* Smaller navbar height on mobile */
        }
        .portal-header {
            margin-top: -60px;
        }
    }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-book-open"></i>
                <span>Academix</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="features.php">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="courses.php">Courses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="help.php">Help</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <a class="nav-link btn-portal btn-portal-primary" href="login.php">
                            <i class="fas fa-sign-in-alt me-1"></i> Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header Section -->
    <header class="portal-header">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10 text-center">
                    <h1 class="display-title display-4 mb-4">
                        Student Assignment Portal
                    </h1>
                    <p class="lead mb-5 fs-4 opacity-85">
                        Streamline your academic workflow with our comprehensive assignment management system
                    </p>
                    <div class="d-flex gap-3 justify-content-center">
                        <a href="login.php" class="btn btn-light btn-lg px-4 py-3">
                            <i class="fas fa-sign-in-alt me-2"></i> Login
                        </a>
                        <a href="register.php" class="btn btn-portal-outline-light btn-lg px-4 py-3">
                            <i class="fas fa-user-plus me-2"></i> Register 
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Features Section -->
    <section class="container my-5 py-5">
        <h2 class="text-center mb-5">Key Features</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card p-4 text-center h-100">
                    <div class="feature-icon floating mx-auto">
                        <i class="fas fa-upload"></i>
                    </div>
                    <h3 class="h4 mb-3">Secure Submission</h3>
                    <p class="text-muted">
                        Upload assignments with confidence using our encrypted submission system with automatic verification.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card p-4 text-center h-100">
                    <div class="feature-icon floating mx-auto">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3 class="h4 mb-3">Deadline Tracker</h3>
                    <p class="text-muted">
                        Visual deadline tracking with priority indicators to help you stay organized.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card p-4 text-center h-100">
                    <div class="feature-icon floating mx-auto">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="h4 mb-3">Performance Analytics</h3>
                    <p class="text-muted">
                        Detailed grade breakdowns and performance trends across all courses.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="bg-light py-5">
        <div class="container">
            <h2 class="text-center mb-5">Submission Process</h2>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="d-flex align-items-start mb-4 p-4 bg-white rounded-3 shadow-sm">
                        <span class="step-number">1</span>
                        <div>
                            <h4 class="h5 mb-2">Access Your Dashboard</h4>
                            <p class="text-muted mb-0">
                                Log in to view all current and upcoming assignments with clear submission requirements.
                            </p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-start mb-4 p-4 bg-white rounded-3 shadow-sm">
                        <span class="step-number">2</span>
                        <div>
                            <h4 class="h5 mb-2">Prepare Your Files</h4>
                            <p class="text-muted mb-0">
                                Ensure your files meet the specified format requirements and naming conventions.
                            </p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-start p-4 bg-white rounded-3 shadow-sm">
                        <span class="step-number">3</span>
                        <div>
                            <h4 class="h5 mb-2">Submit & Confirm</h4>
                            <p class="text-muted mb-0">
                                Upload your files and receive a digital receipt with submission timestamp.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="bg-accent-gradient text-white py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h2 class="display-5 fw-bold mb-4">Ready to get started?</h2>
                    <p class="lead mb-5">
                        Join thousands of students who trust our platform for their assignment management.
                    </p>
                    <a href="register.php" class="btn btn-dark btn-lg px-5 py-3">
                        <i class="fas fa-user-plus me-2"></i> Create Your Account
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="portal-footer pt-5 pb-4">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h5 class="text-uppercase mb-4">
                        <i class="fas fa-university me-2 text-accent"></i>Academix Portal
                    </h5>
                    <p>
                        A professional assignment management system for higher education institutions.
                    </p>
                    <div class="mt-4">
                        <a href="#" class="btn btn-outline-light btn-sm me-2">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="btn btn-outline-light btn-sm me-2">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="btn btn-outline-light btn-sm me-2">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="#" class="btn btn-outline-light btn-sm">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4 mb-md-0">
                    <h5 class="text-uppercase mb-4">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="login.php" class="footer-link">Student Login</a></li>
                        <li class="mb-2"><a href="register.php" class="footer-link">Registration</a></li>
                        <li class="mb-2"><a href="help.php" class="footer-link">Help Center</a></li>
                        <li><a href="contact.php" class="footer-link">Contact Us</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                    <h5 class="text-uppercase mb-4">Support</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-envelope me-2 text-accent"></i> support@academix.edu</li>
                        <li class="mb-2"><i class="fas fa-phone me-2 text-accent"></i> +1 (555) 123-4567</li>
                        <li><i class="fas fa-clock me-2 text-accent"></i> Mon-Fri, 9AM-5PM</li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <h5 class="text-uppercase mb-4">University</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="footer-link">Library Resources</a></li>
                        <li class="mb-2"><a href="#" class="footer-link">Academic Calendar</a></li>
                        <li class="mb-2"><a href="#" class="footer-link">IT Services</a></li>
                        <li><a href="#" class="footer-link">Student Handbook</a></li>
                    </ul>
                </div>
            </div>
            
            <hr class="my-4 bg-secondary">
            
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="small mb-0">&copy; <?= date('Y') ?> Academix Portal. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="small mb-0">
                        <a href="#" class="text-white-50">Privacy Policy</a> | 
                        <a href="#" class="text-white-50">Terms of Service</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // System preference theme detection
        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.setAttribute('data-bs-theme', 'dark');
        }

        // Navbar shadow on scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.15)';
            } else {
                navbar.style.boxShadow = '0 2px 15px rgba(0, 0, 0, 0.1)';
            }
        });

        // Floating animation for feature icons
        const featureIcons = document.querySelectorAll('.feature-icon');
        featureIcons.forEach((icon, index) => {
            icon.style.animationDelay = `${index * 0.2}s`;
        });
    </script>
</body>
</html>