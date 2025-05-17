<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>PathFinder - Find Your Future</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="landingdes.css" />
</head>
<body>

    <header class="site-header">
        <div class="container">
         <a href="landing.php" class="logo-container"> <div class="logo-image-wrapper">
                    <img src="images/logo.jpg" alt="PathFinder Logo" onerror="this.onerror=null;this.src='https://placehold.co/60x60/007bff/white?text=PF&font=Inter';">
                </div>
                <h1 class="logo-text">PathFinder</h1>
            </a>
            <nav class="main-nav" id="main-navigation"> <a href="#features">Features</a>
                <a href="#how-it-works">How It Works</a>
                <a href="loginpage.php" class="nav-button">Login</a>
                <a href="registerpage.php" class="nav-button-primary">Register</a>
            </nav>
            <button class="mobile-nav-toggle" aria-controls="main-navigation" aria-expanded="false">
                <span class="sr-only">Menu</span>
                <i class="bi bi-list"></i>
            </button>
        </div>
    </header>

    <section class="hero">
        <div class="hero-background"></div>
        <div class="hero-overlay"></div>
        <div class="container hero-content">
            <h2>Find Your Dream Job Today.</h2>
            <p>Connect with thousands of top employers and discover opportunities that match your skills and passion. Your next career move starts here.</p>
            <a href="registerpage.php" class="cta-btn">Get Started Free</a>
        </div>
    </section>

    <section id="features" class="features-section">
        <div class="container">
            <h2 class="section-title">Why Choose PathFinder?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon"><i class="bi bi-person-check-fill"></i></div>
                    <h3>For Job Seekers</h3>
                    <p>Effortlessly upload your resume, browse tailored job listings, and apply with just a few clicks. Get alerts for new opportunities.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="bi bi-building-fill-add"></i></div>
                    <h3>For Employers</h3>
                    <p>Post job openings quickly, reach a vast pool of qualified candidates, and manage applications efficiently through our streamlined dashboard.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="bi bi-hand-thumbs-up-fill"></i></div>
                    <h3>Simple & Effective</h3>
                    <p>Our platform is designed for ease of use, ensuring a smooth experience for both job seekers and employers. No hidden fees, just results.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="how-it-works" class="how-it-works-section">
        <div class="container">
            <h2 class="section-title">How It Works</h2>
            <div class="steps-container">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <div class="step-icon"><i class="bi bi-person-plus-fill"></i></div>
                    <h4>Create Account</h4>
                    <p>Sign up in minutes as a job seeker or an employer.</p>
                </div>
                <div class="step-card">
                    <div class="step-number">2</div>
                    <div class="step-icon-seeker"><i class="bi bi-file-earmark-arrow-up-fill"></i></div>
                    <div class="step-icon-employer"><i class="bi bi-megaphone-fill"></i></div>
                    <h4>Seek or Post</h4>
                    <p>Job seekers upload resumes & browse. Employers post jobs & search talent.</p>
                </div>
                <div class="step-card">
                    <div class="step-number">3</div>
                    <div class="step-icon"><i class="bi bi-link-45deg"></i></div>
                    <h4>Connect</h4>
                    <p>Apply to jobs or contact candidates. Make meaningful connections.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="site-footer">
        <div class="container">
            <p>&copy; <span id="currentYear"></span> PathFinder. All rights reserved. Your journey to success starts here.</p>
            <div class="footer-links">
                <a href="#privacy">Privacy Policy</a> | <a href="#terms">Terms of Service</a>
            </div>
        </div>
    </footer>

    <script>
        // Mobile navigation toggle
        const mobileNavToggle = document.querySelector('.mobile-nav-toggle');
        const mainNav = document.getElementById('main-navigation'); // Use ID

        mobileNavToggle.addEventListener('click', () => {
            const isExpanded = mainNav.getAttribute('aria-expanded') === 'true' || false;
            mainNav.setAttribute('aria-expanded', !isExpanded);
            mobileNavToggle.setAttribute('aria-expanded', !isExpanded);
            if (!isExpanded) {
                mobileNavToggle.innerHTML = '<span class="sr-only">Close Menu</span><i class="bi bi-x"></i>';
            } else {
                mobileNavToggle.innerHTML = '<span class="sr-only">Menu</span><i class="bi bi-list"></i>';
            }
            mainNav.classList.toggle('active');
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                // Ensure targetId is more than just "#" and the element exists
                if (targetId && targetId.length > 1 && document.querySelector(targetId)) { 
                   document.querySelector(targetId).scrollIntoView({
                       behavior: 'smooth'
                   });
                }
            });
        });
        
        // Set current year in footer
        document.getElementById('currentYear').textContent = new Date().getFullYear();
    </script>

</body>
</html>
