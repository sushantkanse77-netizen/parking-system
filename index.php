<?php
session_start();
// If user is already logged in, redirect to dashboard
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParkEase - Smart Parking Slot Sharing System</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Global Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
            background: #ffffff;
        }
        
        /* Navbar */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: #ffffff;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
            z-index: 1000;
            padding: 15px 0;
        }
        
        .nav-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: 800;
            color: #1a202c;
        }
        
        .logo i {
            color: #667eea;
            margin-right: 10px;
        }
        
        .nav-links {
            display: flex;
            gap: 35px;
            align-items: center;
        }
        
        .nav-links a {
            text-decoration: none;
            color: #1a202c;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .nav-links a:hover {
            color: #667eea;
        }
        
        .nav-buttons {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .btn-login, .btn-register {
            padding: 10px 28px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-login {
            color: #667eea;
            border: 2px solid #667eea;
            background: transparent;
        }
        
        .btn-login:hover {
            background: #667eea;
            color: #ffffff;
            transform: translateY(-2px);
        }
        
        .btn-register {
            background: #667eea;
            color: #ffffff;
            border: none;
        }
        
        .btn-register:hover {
            background: #5a67d8;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .mobile-menu-btn {
            display: none;
            font-size: 1.8rem;
            cursor: pointer;
            color: #1a202c;
        }
        
        .mobile-menu {
            display: none;
            position: fixed;
            top: 70px;
            left: 0;
            width: 100%;
            background: #ffffff;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            z-index: 999;
        }
        
        .mobile-menu a {
            display: block;
            padding: 12px 20px;
            text-decoration: none;
            color: #1a202c;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 500;
        }
        
        .mobile-buttons {
            display: flex;
            gap: 15px;
            padding: 15px 20px 5px;
        }
        
        .mobile-buttons a {
            flex: 1;
            text-align: center;
            border: none;
        }
        
        /* Hero Section */
        .hero {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            padding-top: 80px;
        }
        
        .hero-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 60px 40px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }
        
        .hero-content {
            text-align: left;
        }
        
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            padding: 8px 20px;
            border-radius: 50px;
            color: #ffffff;
            font-size: 0.9rem;
            margin-bottom: 25px;
        }
        
        .hero-title {
            font-size: 3.8rem;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.2;
            margin-bottom: 20px;
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #ffffff, #fbbf24);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .hero-description {
            color: rgba(255,255,255,0.95);
            font-size: 1.1rem;
            margin-bottom: 35px;
            line-height: 1.7;
        }
        
        .hero-buttons {
            display: flex;
            gap: 20px;
            margin-bottom: 50px;
            flex-wrap: wrap;
        }
        
        .btn-primary, .btn-secondary {
            padding: 14px 35px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-primary {
            background: #ffffff;
            color: #667eea;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        
        .btn-secondary {
            background: transparent;
            color: #ffffff;
            border: 2px solid #ffffff;
        }
        
        .btn-secondary:hover {
            background: #ffffff;
            color: #667eea;
            transform: translateY(-3px);
        }
        
        .hero-stats {
            display: flex;
            gap: 50px;
        }
        
        .stat {
            text-align: left;
        }
        
        .stat-number {
            display: block;
            font-size: 2.2rem;
            font-weight: 800;
            color: #ffffff;
        }
        
        .stat-label {
            color: rgba(255,255,255,0.85);
            font-size: 0.9rem;
        }
        
        .hero-image {
            position: relative;
            text-align: center;
        }
        
        .hero-image img {
            width: 100%;
            max-width: 500px;
            border-radius: 25px;
            box-shadow: 0 25px 45px rgba(0,0,0,0.2);
        }
        
        .floating-card {
            position: absolute;
            background: #ffffff;
            padding: 12px 24px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            font-weight: 500;
            color: #1a202c;
        }
        
        .floating-card i {
            color: #667eea;
            font-size: 1.2rem;
        }
        
        .card-1 {
            top: 5%;
            left: -5%;
            animation: float 3s ease-in-out infinite;
        }
        
        .card-2 {
            bottom: 20%;
            right: -8%;
            animation: float 3s ease-in-out infinite 1s;
        }
        
        .card-3 {
            bottom: 5%;
            left: 0%;
            animation: float 3s ease-in-out infinite 2s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-12px); }
        }
        
        /* Container */
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 40px;
        }
        
        /* Sections */
        .features, .how-it-works, .benefits, .testimonials {
            padding: 80px 0;
            background: #ffffff;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .section-tag {
            display: inline-block;
            background: linear-gradient(135deg, #667eea15, #764ba215);
            padding: 6px 18px;
            border-radius: 30px;
            color: #667eea;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .section-title {
            font-size: 2.8rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 15px;
        }
        
        .section-subtitle {
            color: #4a5568;
            font-size: 1.1rem;
        }
        
        /* Features Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 35px;
        }
        
        .feature-card {
            background: #ffffff;
            padding: 35px 25px;
            border-radius: 20px;
            text-align: center;
            transition: all 0.3s;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }
        
        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 35px rgba(0,0,0,0.1);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
        }
        
        .feature-icon i {
            font-size: 2.2rem;
            color: #ffffff;
        }
        
        .feature-card h3 {
            font-size: 1.4rem;
            margin-bottom: 15px;
            color: #1a202c;
        }
        
        .feature-card p {
            color: #4a5568;
            line-height: 1.7;
        }
        
        /* Steps */
        .steps-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 30px;
            flex-wrap: wrap;
        }
        
        .step {
            flex: 1;
            text-align: center;
            min-width: 220px;
        }
        
        .step-number {
            width: 55px;
            height: 55px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            font-weight: bold;
            margin: 0 auto 20px;
        }
        
        .step-icon {
            width: 90px;
            height: 90px;
            background: #f7fafc;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            border: 1px solid #e2e8f0;
        }
        
        .step-icon i {
            font-size: 2.5rem;
            color: #667eea;
        }
        
        .step h3 {
            font-size: 1.3rem;
            margin-bottom: 12px;
            color: #1a202c;
        }
        
        .step p {
            color: #4a5568;
            line-height: 1.6;
        }
        
        .step-arrow {
            font-size: 2rem;
            color: #cbd5e0;
        }
        
        /* Benefits */
        .benefits {
            background: #f7fafc;
        }
        
        .benefits-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }
        
        .benefits-content .section-title {
            text-align: left;
        }
        
        .benefits-list {
            list-style: none;
            margin-top: 35px;
        }
        
        .benefits-list li {
            display: flex;
            gap: 18px;
            margin-bottom: 30px;
            align-items: flex-start;
        }
        
        .benefits-list li i {
            font-size: 1.5rem;
            color: #48bb78;
            margin-top: 3px;
        }
        
        .benefits-list li strong {
            display: block;
            font-size: 1.1rem;
            margin-bottom: 6px;
            color: #1a202c;
        }
        
        .benefits-list li p {
            color: #4a5568;
            line-height: 1.6;
        }
        
        .benefits-image img {
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        
        /* Testimonials */
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 35px;
        }
        
        .testimonial-card {
            background: #ffffff;
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            transition: transform 0.3s;
        }
        
        .testimonial-card:hover {
            transform: translateY(-5px);
        }
        
        .testimonial-rating {
            color: #fbbf24;
            margin-bottom: 20px;
            font-size: 1.1rem;
        }
        
        .testimonial-card p {
            color: #4a5568;
            line-height: 1.7;
            margin-bottom: 25px;
            font-style: italic;
        }
        
        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .testimonial-author img {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .testimonial-author strong {
            display: block;
            color: #1a202c;
            font-size: 1rem;
        }
        
        .testimonial-author span {
            font-size: 0.85rem;
            color: #718096;
        }
        
        /* CTA Section */
        .cta {
            background: linear-gradient(135deg, #667eea, #764ba2);
            padding: 80px 0;
            text-align: center;
        }
        
        .cta-content h2 {
            font-size: 2.8rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 20px;
        }
        
        .cta-content p {
            color: rgba(255,255,255,0.95);
            font-size: 1.1rem;
            margin-bottom: 35px;
        }
        
        .cta-buttons {
            display: flex;
            gap: 25px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-large {
            padding: 16px 40px;
            font-size: 1.1rem;
        }
        
        /* Footer */
        .footer {
            background: #1a202c;
            color: #ffffff;
            padding: 60px 0 25px;
        }
        
        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 45px;
            margin-bottom: 45px;
        }
        
        .footer-logo {
            font-size: 1.6rem;
            font-weight: 800;
            margin-bottom: 18px;
            color: #ffffff;
        }
        
        .footer-logo i {
            margin-right: 10px;
            color: #667eea;
        }
        
        .footer-col p {
            color: #a0aec0;
            line-height: 1.7;
        }
        
        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 22px;
        }
        
        .social-links a {
            width: 38px;
            height: 38px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .social-links a:hover {
            background: #667eea;
            transform: translateY(-3px);
        }
        
        .footer-col h4 {
            margin-bottom: 22px;
            font-size: 1.2rem;
            font-weight: 600;
            color: #ffffff;
        }
        
        .footer-col ul {
            list-style: none;
        }
        
        .footer-col ul li {
            margin-bottom: 12px;
            color: #a0aec0;
        }
        
        .footer-col ul li a {
            color: #a0aec0;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-col ul li a:hover {
            color: #ffffff;
        }
        
        .footer-col ul li i {
            margin-right: 10px;
            color: #667eea;
            width: 20px;
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 25px;
            border-top: 1px solid rgba(255,255,255,0.1);
            color: #a0aec0;
        }
        
        /* Mobile Responsive */
        @media (max-width: 1024px) {
            .hero-title {
                font-size: 3rem;
            }
            
            .hero-container {
                gap: 40px;
            }
        }
        
        @media (max-width: 768px) {
            .nav-links, .nav-buttons {
                display: none;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .nav-container {
                padding: 0 20px;
            }
            
            .hero-container {
                grid-template-columns: 1fr;
                padding: 40px 20px;
                text-align: center;
            }
            
            .hero-content {
                text-align: center;
            }
            
            .hero-title {
                font-size: 2.2rem;
            }
            
            .hero-buttons {
                justify-content: center;
            }
            
            .hero-stats {
                justify-content: center;
            }
            
            .stat {
                text-align: center;
            }
            
            .floating-card {
                display: none;
            }
            
            .container {
                padding: 0 20px;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .steps-container {
                flex-direction: column;
            }
            
            .step-arrow {
                transform: rotate(90deg);
            }
            
            .benefits-wrapper {
                grid-template-columns: 1fr;
            }
            
            .benefits-content .section-title {
                text-align: center;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .testimonials-grid {
                grid-template-columns: 1fr;
            }
            
            .footer-grid {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .social-links {
                justify-content: center;
            }
            
            .cta-content h2 {
                font-size: 1.8rem;
            }
            
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
        }
        
        @media (max-width: 480px) {
            .hero-title {
                font-size: 1.8rem;
            }
            
            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn-primary, .btn-secondary {
                width: 100%;
                justify-content: center;
            }
            
            .hero-stats {
                flex-direction: column;
                gap: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <i class="fas fa-parking"></i>
                <span>ParkEase</span>
            </div>
            <div class="nav-links">
                <a href="#home">Home</a>
                <a href="#features">Features</a>
                <a href="#how-it-works">How It Works</a>
                <a href="#benefits">Benefits</a>
                <a href="#contact">Contact</a>
            </div>
            <div class="nav-buttons">
                <a href="login.php" class="btn-login">Login</a>
                <a href="register.php" class="btn-register">Sign Up</a>
            </div>
            <div class="mobile-menu-btn" onclick="toggleMobileMenu()">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <a href="#home">Home</a>
        <a href="#features">Features</a>
        <a href="#how-it-works">How It Works</a>
        <a href="#benefits">Benefits</a>
        <a href="#contact">Contact</a>
        <div class="mobile-buttons">
            <a href="login.php" class="btn-login">Login</a>
            <a href="register.php" class="btn-register">Sign Up</a>
        </div>
    </div>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <div class="hero-badge">
                    <i class="fas fa-shield-alt"></i> Safe & Secure Parking
                </div>
                <h1 class="hero-title">
                    Find Your Perfect<br>
                    <span class="gradient-text">Parking Space</span>
                </h1>
                <p class="hero-description">
                    Join thousands of users sharing and finding parking spaces effortlessly. 
                    Save time, reduce stress, and park with confidence using ParkEase.
                </p>
                <div class="hero-buttons">
                    <a href="register.php" class="btn-primary">
                        <i class="fas fa-user-plus"></i> Get Started Free
                    </a>
                    <a href="#how-it-works" class="btn-secondary">
                        <i class="fas fa-play"></i> How It Works
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="stat">
                        <span class="stat-number">500+</span>
                        <span class="stat-label">Parking Slots</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">1,000+</span>
                        <span class="stat-label">Happy Users</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">50+</span>
                        <span class="stat-label">Locations</span>
                    </div>
                </div>
            </div>
            <div class="hero-image">
                <div class="floating-card card-1">
                    <i class="fas fa-check-circle"></i>
                    <span>Available Now</span>
                </div>
                <div class="floating-card card-2">
                    <i class="fas fa-clock"></i>
                    <span>24/7 Access</span>
                </div>
                <div class="floating-card card-3">
                    <i class="fas fa-tag"></i>
                    <span>Best Prices</span>
                </div>
                <img src="park.png" alt="Parking Illustration">
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Why Choose Us</span>
                <h2 class="section-title">Amazing Features You'll Love</h2>
                <p class="section-subtitle">Everything you need for hassle-free parking management</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h3>Real-time Location</h3>
                    <p>Find available parking spots near you with live updates and Google Maps integration.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-camera"></i>
                    </div>
                    <h3>Photo Verification</h3>
                    <p>Upload and view actual parking space photos before you arrive.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-vote-yea"></i>
                    </div>
                    <h3>Community Voting</h3>
                    <p>Real-time status updates through community votes - "Parked" or "Full".</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <h3>Transparent Pricing</h3>
                    <p>Clear hourly rates with no hidden charges or surprises.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Mobile Friendly</h3>
                    <p>Fully responsive design works perfectly on all devices.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Secure & Safe</h3>
                    <p>Verified users and secure system for peace of mind.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="how-it-works">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Simple Process</span>
                <h2 class="section-title">How ParkEase Works</h2>
                <p class="section-subtitle">Get started in just 3 easy steps</p>
            </div>
            <div class="steps-container">
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h3>Create Account</h3>
                    <p>Sign up for free in seconds with your email address.</p>
                </div>
                <div class="step-arrow">
                    <i class="fas fa-arrow-right"></i>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Find Parking</h3>
                    <p>Search for available slots by location and vehicle type.</p>
                </div>
                <div class="step-arrow">
                    <i class="fas fa-arrow-right"></i>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3>Park & Vote</h3>
                    <p>Park your vehicle and update the status to help others.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section id="benefits" class="benefits">
        <div class="container">
            <div class="benefits-wrapper">
                <div class="benefits-content">
                    <span class="section-tag">Benefits</span>
                    <h2 class="section-title">Why Park With Us?</h2>
                    <ul class="benefits-list">
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <strong>Save Time</strong>
                                <p>No more circling around looking for parking</p>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <strong>Save Money</strong>
                                <p>Compare prices and choose the best deals</p>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <strong>Community Driven</strong>
                                <p>Real-time updates from actual users</p>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <strong>Earn Money</strong>
                                <p>List your unused parking space and earn</p>
                            </div>
                        </li>
                    </ul>
                    <a href="register.php" class="btn-primary">Start Earning Today →</a>
                </div>
                <div class="benefits-image">
                    <img src="https://images.unsplash.com/photo-1590674899484-d5640e854abe?w=500&h=400&fit=crop" alt="Parking Benefits">
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Testimonials</span>
                <h2 class="section-title">What Our Users Say</h2>
            </div>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p>"Amazing platform! Found parking near my office instantly. The voting system is very accurate."</p>
                    <div class="testimonial-author">
                        <img src="https://randomuser.me/api/portraits/men/1.jpg" alt="User">
                        <div>
                            <strong>Rahul Sharma</strong>
                            <span>Daily Commuter</span>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p>"I'm earning extra money by renting out my parking space. Best decision ever!"</p>
                    <div class="testimonial-author">
                        <img src="https://randomuser.me/api/portraits/women/2.jpg" alt="User">
                        <div>
                            <strong>Priya Patel</strong>
                            <span>Space Provider</span>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                    <p>"Real-time updates work perfectly. Never had an issue finding parking since using ParkEase."</p>
                    <div class="testimonial-author">
                        <img src="https://randomuser.me/api/portraits/men/3.jpg" alt="User">
                        <div>
                            <strong>Amit Kumar</strong>
                            <span>Business Owner</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Find Your Perfect Parking Spot?</h2>
                <p>Join ParkEase today and never waste time searching for parking again.</p>
                <div class="cta-buttons">
                    <a href="register.php" class="btn-primary btn-large">
                        <i class="fas fa-user-plus"></i> Create Free Account
                    </a>
                    <a href="login.php" class="btn-secondary btn-large">
                        <i class="fas fa-sign-in-alt"></i> Login Now
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <div class="footer-logo">
                        <i class="fas fa-parking"></i>
                        <span>ParkEase</span>
                    </div>
                    <p>Making parking simple, smart, and accessible for everyone.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="#home">Home</a></li>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#how-it-works">How It Works</a></li>
                        <li><a href="#benefits">Benefits</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Support</h4>
                    <ul>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Contact Us</h4>
                    <ul>
                        <li><i class="fas fa-envelope"></i> support@parkease.com</li>
                        <li><i class="fas fa-phone"></i> +1 234 567 890</li>
                        <li><i class="fas fa-map-marker-alt"></i> 123 Parking St, City</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 ParkEase. All rights reserved. | Smart Parking Sharing System</p>
            </div>
        </div>
    </footer>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            if(menu.style.display === 'block') {
                menu.style.display = 'none';
            } else {
                menu.style.display = 'block';
            }
        }

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if(target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                    const mobileMenu = document.getElementById('mobileMenu');
                    if(mobileMenu.style.display === 'block') {
                        mobileMenu.style.display = 'none';
                    }
                }
            });
        });

        // Close mobile menu on window resize
        window.addEventListener('resize', function() {
            if(window.innerWidth > 768) {
                document.getElementById('mobileMenu').style.display = 'none';
            }
        });
    </script>
</body>
</html>