<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Helphive - Professional Maid Services</title>
    <meta name="description" content="Book verified, experienced maids for cleaning, cooking, baby sitting, and elderly care services.">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <h2>Helphive</h2>
                <p class="tagline">The new way to hire a trusted maid</p>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="booking.php">How it Works</a></li>
                <li><a href="maids.php">Our Maids</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
            <div class="nav-buttons">
                <a href="../helphive-admin/admin.html" class="btn-outline">Admin</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="logout.php" class="btn-outline">Logout</a>
                <?php else: ?>
                    <a href="user-login.php" class="btn-outline">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <div class="hero-left">
                <div class="hero-box">
                    <h1>What type of maids are you looking for?</h1>
                    
                    <div class="service-grid">
                        <div class="service-option">
                            <div class="service-icon">🧹</div>
                            <h3>Cleaning</h3>
                        </div>
                        <div class="service-option">
                            <div class="service-icon">👩🏼‍🍳</div>
                            <h3>Cooking</h3>
                        </div>
                        <div class="service-option">
                            <div class="service-icon">👶🏻</div>
                            <h3>Baby Sitting</h3>
                        </div>
                        <div class="service-option">
                            <div class="service-icon">👵🏻</div>
                            <h3>Elderly Care</h3>
                        </div>
                    </div>
                    
                    <a href="booking.php" class="btn-primary-large">Book now</a>
                </div>
                
                <div class="testimonial-banner">
                    <p>"I found a good reliable maid on Helphive. Now I don't need to enter the kitchen again"</p>
                    <p class="author">- Sapna Jalan (Housewife)</p>
                </div>
            </div>
            
            <div class="hero-right">
                <img src="https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=500&h=600&fit=crop" alt="Professional Maid" class="hero-image">
                <div class="hero-arrow">
                    <span>← start here</span>
                </div>
            </div>
        </div>
    </section>

    <!-- How it Works -->
    <section class="how-it-works">
        <div class="container">
            <h2 class="section-title">How does it work?</h2>
            
            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <img src="https://images.unsplash.com/photo-1484480974693-6ca0a78fb36b?w=400&h=300&fit=crop" alt="Select Service">
                    <h3>Select Your Service</h3>
                    <p>Choose from cleaning, cooking, baby sitting, or elderly care services</p>
                </div>
                
                <div class="step-card">
                    <div class="step-number">2</div>
                    <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=400&h=300&fit=crop" alt="Browse Maids">
                    <h3>Browse Verified Maids</h3>
                    <p>View profiles of background-checked and experienced maids</p>
                </div>
                
                <div class="step-card">
                    <div class="step-number">3</div>
                    <img src="https://images.unsplash.com/photo-1506784983877-45594efa4cbe?w=400&h=300&fit=crop" alt="Schedule">
                    <h3>Schedule & Pay</h3>
                    <p>Pick your preferred date, time, and make a secure payment</p>
                </div>
                
                <div class="step-card">
                    <div class="step-number">4</div>
                    <img src="https://images.unsplash.com/photo-1556911220-bff31c812dba?w=400&h=300&fit=crop" alt="Service">
                    <h3>Relax & Enjoy</h3>
                    <p>Sit back while our professional maid takes care of everything</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="features-section">
        <div class="container">
            <h2 class="section-title">Why Choose Us?</h2>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">✓</div>
                    <h3>Verified Professionals</h3>
                    <p>All maids are background-checked and verified for your safety and peace of mind</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">⚡</div>
                    <h3>Quick Booking</h3>
                    <p>Book your service in minutes with our easy online booking system</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">💯</div>
                    <h3>Quality Guaranteed</h3>
                    <p>100% satisfaction guaranteed or we'll make it right at no extra cost</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <h2>10,000+</h2>
                    <p>Happy Customers</p>
                </div>
                <div class="stat-item">
                    <h2>500+</h2>
                    <p>Verified Maids</p>
                </div>
                <div class="stat-item">
                    <h2>50,000+</h2>
                    <p>Services Delivered</p>
                </div>
                <div class="stat-item">
                    <h2>4.8/5</h2>
                    <p>Average Rating</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Reviews -->
    <section class="reviews-section">
        <div class="container">
            <h2 class="section-title">What Our Customers Say</h2>
            
            <div class="reviews-grid">
                <div class="review-card">
                    <div class="review-header">
                        
                        <div class="review-info">
                            <h4>Priya Sharma</h4>
                            <div class="review-rating">⭐⭐⭐⭐⭐</div>
                        </div>
                    </div>
                    <p>"Excellent service! The maid was professional and thorough. My house has never looked better!"</p>
                </div>
                
                <div class="review-card">
                    <div class="review-header">
                        
                        <div class="review-info">
                            <h4>Rahul Verma</h4>
                            <div class="review-rating">⭐⭐⭐⭐⭐</div>
                        </div>
                    </div>
                    <p>"Very reliable and trustworthy service. The booking process was super easy and convenient."</p>
                </div>
                
                <div class="review-card">
                    <div class="review-header">
                        
                        <div class="review-info">
                            <h4>Anjali Patel</h4>
                            <div class="review-rating">⭐⭐⭐⭐⭐</div>
                        </div>
                    </div>
                    <p>"Great experience! The maid arrived on time and did an amazing job. Highly recommend!"</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h3>Helphive</h3>
                    <p>Your trusted partner for professional maid services. Making homes cleaner and lives easier.</p>
                </div>
                
                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="booking.php">Book Now</a></li>
                        <li><a href="maids.php">Our Maids</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h4>Services</h4>
                    <ul>
                        <li><a href="#">Cleaning</a></li>
                        <li><a href="#">Cooking</a></li>
                        <li><a href="#">Baby Sitting</a></li>
                        <li><a href="#">Elderly Care</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h4>Contact</h4>
                    <ul>
                        <li><a href="tel:+911234567890">+91 123-456-7890</a></li>
                        <li><a href="mailto:info@helphive.com">info@helphive.com</a></li>
                        <li><a href="#">Gujarat, India</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2024 Helphive. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>