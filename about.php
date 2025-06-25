<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Myshop</title>
    <link rel="stylesheet" href="css/index.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="css/about.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="container">


        <div class="about-container">
            <!-- Hero Section -->
            <div class="hero-section">
                <h1>About Myshop</h1>
                <p>Welcome to Myshop, your trusted partner for quality products and exceptional shopping experiences. We are committed to serving the Lebanese community with excellence, innovation, and care.</p>
            </div>

            <!-- Main Content Section -->
            <div class="content-section">
                <div class="content-card">
                    <h2>
                        <span class="icon">🏢</span>
                        Our Story
                    </h2>
                    <p>Myshop has been serving the Lebanese market for years, bringing international quality standards to local communities. We started with a vision to make quality products accessible to everyone, and today we continue to grow while staying true to our commitment to customer satisfaction and community service.</p>
                </div>

                <div class="content-card">
                    <h2>
                        <span class="icon">🎯</span>
                        Our Mission
                    </h2>
                    <p>To provide our customers with the best shopping experience through quality products, competitive prices, and exceptional service. We strive to be the preferred destination for families across Lebanon, offering everything they need under one roof.</p>
                </div>
            </div>

            <!-- Values Section -->
            <div class="values-section">
                <h2>Our Core Values</h2>
                <div class="values-grid">
                    <div class="value-item">
                        <div class="value-icon">✨</div>
                        <h3>Quality First</h3>
                        <p>We ensure every product meets our high standards of quality and freshness.</p>
                    </div>
                    <div class="value-item">
                        <div class="value-icon">🤝</div>
                        <h3>Customer Focus</h3>
                        <p>Our customers are at the heart of everything we do, driving our continuous improvement.</p>
                    </div>
                    <div class="value-item">
                        <div class="value-icon">🌱</div>
                        <h3>Sustainability</h3>
                        <p>We are committed to environmental responsibility and sustainable business practices.</p>
                    </div>
                    <div class="value-item">
                        <div class="value-icon">💎</div>
                        <h3>Integrity</h3>
                        <p>We conduct business with honesty, transparency, and ethical standards.</p>
                    </div>
                </div>
            </div>


            <!-- Team Section -->
            <div class="team-section">
                <h2>Meet Our Leadership Team</h2>
                <div class="team-grid">
                    <div class="team-member">
                        <div class="avatar">👨‍💼</div>
                        <h3>Ahmad Khalil</h3>
                        <div class="role">General Manager</div>
                        <p>Leading our operations with over 15 years of retail experience in the Lebanese market.</p>
                    </div>
                    <div class="team-member">
                        <div class="avatar">👩‍💼</div>
                        <h3>Rima Nassar</h3>
                        <div class="role">Operations Director</div>
                        <p>Ensuring smooth operations and excellent customer service across all departments.</p>
                    </div>
                    <div class="team-member">
                        <div class="avatar">👨‍💻</div>
                        <h3>Omar Haddad</h3>
                        <div class="role">IT Director</div>
                        <p>Driving digital innovation and maintaining our modern shopping platform.</p>
                    </div>
                    <div class="team-member">
                        <div class="avatar">👩‍🍳</div>
                        <h3>Layla Mansour</h3>
                        <div class="role">Quality Assurance Manager</div>
                        <p>Maintaining the highest standards of product quality and food safety.</p>
                    </div>
                </div>
            </div>

            <!-- Contact Section -->
            <div class="contact-section">
                <h2>Get In Touch</h2>
                <div class="contact-grid">
                    <div class="contact-item">
                        <div class="contact-icon">📍</div>
                        <div class="contact-info">
                            <h3>Visit Our Store</h3>
                            <p>Main Street, Zahlé<br>Béqaa Governorate, Lebanon</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">📞</div>
                        <div class="contact-info">
                            <h3>Call Us</h3>
                            <p>+961 8 123 456<br>Monday - Sunday: 8AM - 10PM</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">✉️</div>
                        <div class="contact-info">
                            <h3>Email Us</h3>
                            <p>info@myshop.com<br>support@myshop.com</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">🌐</div>
                        <div class="contact-info">
                            <h3>Follow Us</h3>
                            <p>@Myshop<br>Stay updated with our latest offers</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-content">
            <!-- Newsletter Section -->
            <div class="newsletter-section">
                <div class="newsletter-container">
                    <div class="newsletter-info">
                        <h3>Stay Updated with Our Latest Offers</h3>
                        <p>Subscribe to our newsletter and be the first to know about new products, special discounts, and exclusive deals!</p>
                    </div>
                    <div class="newsletter-form">
                        <form id="newsletterForm">
                            <div class="form-group">
                                <input type="email" id="newsletterEmail" placeholder="Enter your email address" required>
                                <button type="submit" class="newsletter-btn">
                                    <span class="btn-text">Subscribe</span>
                                    <span class="btn-loading" style="display: none;">
                                        <span class="spinner"></span>
                                    </span>
                                </button>
                            </div>
                            <div class="newsletter-privacy">
                                <small>By subscribing, you agree to our Privacy Policy. Unsubscribe at any time.</small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Main Footer Content -->
            <div class="footer-main">
                <div class="footer-grid">
                    <!-- Company Info -->
                    <div class="footer-column">
                        <div class="footer-logo">
                            <h3>Myshop</h3>
                            <p>Your trusted partner for quality products and exceptional shopping experiences in Lebanon.</p>
                        </div>
                        <div class="social-links">
                            <a href="#" class="social-link" title="Facebook">📘</a>
                            <a href="#" class="social-link" title="Instagram">📷</a>
                            <a href="#" class="social-link" title="Twitter">🐦</a>
                            <a href="#" class="social-link" title="WhatsApp">💬</a>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="footer-column">
                        <h4>Quick Links</h4>
                        <ul class="footer-links">
                            <li><a href="index.php">Home</a></li>
                            <li><a href="products.php">Products</a></li>
                            <li><a href="categories.php">Categories</a></li>
                            <li><a href="about.php">About Us</a></li>
                            <li><a href="contact.php">Contact</a></li>
                            <li><a href="cart.php">Shopping Cart</a></li>
                        </ul>
                    </div>

                    <!-- Customer Service -->
                    <div class="footer-column">
                        <h4>Customer Service</h4>
                        <ul class="footer-links">
                            <li><a href="faq.php">FAQ</a></li>
                            <li><a href="shipping.php">Shipping Info</a></li>
                            <li><a href="returns.php">Returns & Exchanges</a></li>
                            <li><a href="privacy.php">Privacy Policy</a></li>
                            <li><a href="terms.php">Terms & Conditions</a></li>
                            <li><a href="support.php">Customer Support</a></li>
                        </ul>
                    </div>

                    <!-- Contact Info -->
                    <div class="footer-column">
                        <h4>Contact Information</h4>
                        <div class="contact-details">
                            <div class="contact-item-footer">
                                <span class="contact-icon-footer">📍</span>
                                <div>
                                    <strong>Address:</strong><br>
                                    Main Street, Zahlé<br>
                                    Béqaa, Lebanon
                                </div>
                            </div>
                            <div class="contact-item-footer">
                                <span class="contact-icon-footer">📞</span>
                                <div>
                                    <strong>Phone:</strong><br>
                                    +961 8 123 456
                                </div>
                            </div>
                            <div class="contact-item-footer">
                                <span class="contact-icon-footer">✉️</span>
                                <div>
                                    <strong>Email:</strong><br>
                                    info@myshop.com
                                </div>
                            </div>
                            <div class="contact-item-footer">
                                <span class="contact-icon-footer">🕒</span>
                                <div>
                                    <strong>Hours:</strong><br>
                                    Mon-Sun: 8AM - 10PM
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p>&copy; 2025 Myshop. All rights reserved.</p>
                    <div class="payment-methods">
                        <span>We Accept:</span>
                        <div class="payment-icons">
                            <span class="payment-icon">💳</span>
                            <span class="payment-icon">💰</span>
                            <span class="payment-icon">📱</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scrolling for any internal links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add animation on scroll
        function animateOnScroll() {
            const elements = document.querySelectorAll('.content-card, .value-item, .team-member, .contact-item');

            elements.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                const elementVisible = 150;

                if (elementTop < window.innerHeight - elementVisible) {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }
            });
        }

        // Initialize animations
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.content-card, .value-item, .team-member, .contact-item');
            elements.forEach(element => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
                element.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            });

            animateOnScroll();
        });

        window.addEventListener('scroll', animateOnScroll);

        // Counter animation for statistics
        function animateCounters() {
            const counters = document.querySelectorAll('.stat-item h3');

            counters.forEach(counter => {
                const target = parseInt(counter.textContent.replace(/[^\d]/g, ''));
                let current = 0;
                const increment = target / 100;
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        counter.textContent = counter.textContent.replace(/\d+/, target.toLocaleString());
                        clearInterval(timer);
                    } else {
                        counter.textContent = counter.textContent.replace(/\d+/, Math.floor(current).toLocaleString());
                    }
                }, 20);
            });
        }

        // Trigger counter animation when stats section is visible
        const statsSection = document.querySelector('.stats-section');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    observer.unobserve(entry.target);
                }
            });
        });

        if (statsSection) {
            observer.observe(statsSection);
        }

        // Newsletter Form Handling
        document.getElementById('newsletterForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const form = this;
            const emailInput = document.getElementById('newsletterEmail');
            const submitBtn = form.querySelector('.newsletter-btn');
            const btnText = submitBtn.querySelector('.btn-text');
            const btnLoading = submitBtn.querySelector('.btn-loading');

            // Validate email
            const email = emailInput.value.trim();
            if (!email || !isValidEmail(email)) {
                showNewsletterMessage('Please enter a valid email address', 'error');
                return;
            }

            // Show loading state
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline-block';
            submitBtn.disabled = true;

            // Simulate API call (replace with actual newsletter signup)
            setTimeout(() => {
                // Success simulation
                showNewsletterMessage('Thank you for subscribing! Check your email for confirmation.', 'success');
                emailInput.value = '';

                // Reset button
                btnText.style.display = 'inline-block';
                btnLoading.style.display = 'none';
                submitBtn.disabled = false;

            }, 2000);
        });

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        function showNewsletterMessage(message, type) {
            // Remove existing messages
            const existingMessages = document.querySelectorAll('.newsletter-message');
            existingMessages.forEach(msg => {
                msg.style.animation = 'slideOut 0.3s ease-in';
                setTimeout(() => msg.remove(), 300);
            });

            // Create new message
            const messageDiv = document.createElement('div');
            messageDiv.className = 'newsletter-message';
            messageDiv.style.background = type === 'success' ? '#27ae60' : '#e74c3c';
            messageDiv.textContent = message;

            document.body.appendChild(messageDiv);

            // Auto remove after 5 seconds
            setTimeout(() => {
                if (document.body.contains(messageDiv)) {
                    messageDiv.style.animation = 'slideOut 0.3s ease-in';
                    setTimeout(() => messageDiv.remove(), 300);
                }
            }, 5000);
        }

        // Smooth scroll to top functionality
        function addScrollToTop() {
            const scrollBtn = document.createElement('button');
            scrollBtn.innerHTML = '↑';
            scrollBtn.className = 'scroll-to-top';
            scrollBtn.style.cssText = `
                position: fixed;
                bottom: 30px;
                right: 30px;
                width: 50px;
                height: 50px;
                background: #3498db;
                color: white;
                border: none;
                border-radius: 50%;
                font-size: 20px;
                cursor: pointer;
                display: none;
                z-index: 1000;
                transition: all 0.3s ease;
                box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
            `;

            scrollBtn.addEventListener('click', () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });

            scrollBtn.addEventListener('mouseenter', () => {
                scrollBtn.style.background = '#2980b9';
                scrollBtn.style.transform = 'translateY(-2px)';
            });

            scrollBtn.addEventListener('mouseleave', () => {
                scrollBtn.style.background = '#3498db';
                scrollBtn.style.transform = 'translateY(0)';
            });

            document.body.appendChild(scrollBtn);

            // Show/hide scroll button
            window.addEventListener('scroll', () => {
                if (window.pageYOffset > 300) {
                    scrollBtn.style.display = 'block';
                } else {
                    scrollBtn.style.display = 'none';
                }
            });
        }

        // Initialize scroll to top button
        addScrollToTop();
    </script>
</body>

</html>