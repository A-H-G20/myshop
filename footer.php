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
                            <h3>My shop</h3>
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
                        
                            <li><a href="about.php">About Us</a></li>
                          
                            <li><a href="cart.php">Shopping Cart</a></li>
                        </ul>
                    </div>

                    <!-- Customer Service -->
                    <div class="footer-column">
                        <h4>Customer Service</h4>
                        <ul class="footer-links">
                         
                            <li><a href="#">Privacy Policy</a></li>
                            <li><a href="#">Terms & Conditions</a></li>
                            <li><a href="#">Customer Support</a></li>
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
                           
                            <span class="payment-icon">📱</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script>
document.getElementById('newsletterForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = this;
    const emailInput = document.getElementById('newsletterEmail');
    const email = emailInput.value.trim();
    const btnText = form.querySelector('.btn-text');
    const btnLoading = form.querySelector('.btn-loading');

    if (!email) return;

    // Show loading
    btnText.style.display = 'none';
    btnLoading.style.display = 'inline-block';

    fetch('newsletter.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'email=' + encodeURIComponent(email)
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);

        // Reset form state
        btnText.style.display = 'inline-block';
        btnLoading.style.display = 'none';
        if (data.success) emailInput.value = '';
    })
    .catch(() => {
        alert('Something went wrong. Please try again later.');
        btnText.style.display = 'inline-block';
        btnLoading.style.display = 'none';
    });
});
</script>
