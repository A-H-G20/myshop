<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Shop</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #2c3e50, #3498db);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .register-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            overflow: hidden;
            width: 100%;
            max-width: 1000px;
            display: flex;
            min-height: 600px;
        }

        .register-left {
            flex: 1.2;
            padding: 50px 40px;
            overflow-y: auto;
            max-height: 600px;
        }

        .register-right {
            flex: 1;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            position: relative;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 40px;
            justify-content: center;
        }

        .logo-icon {
            width: 50px;
            height: 50px;
            background: #3498db;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }

        .form-title {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
            text-align: center;
        }

        .form-subtitle {
            color: #7f8c8d;
            margin-bottom: 40px;
            font-size: 16px;
            text-align: center;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
        }

        .form-group {
            flex: 1;
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #ecf0f1;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-input:focus {
            outline: none;
            border-color: #e74c3c;
            background: white;
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
        }

        .form-select {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #ecf0f1;
            border-radius: 10px;
            font-size: 16px;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .form-select:focus {
            outline: none;
            border-color: #e74c3c;
            background: white;
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
        }

        .checkbox-group {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 30px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            accent-color: #e74c3c;
            margin-top: 2px;
        }

        .checkbox-group label {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
        }

        .checkbox-group a {
            color: #e74c3c;
            text-decoration: none;
        }

        .checkbox-group a:hover {
            text-decoration: underline;
        }

        .register-btn {
            width: 100%;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
        }

        .divider {
            text-align: center;
            margin: 25px 0;
            position: relative;
            color: #bdc3c7;
            font-size: 14px;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #ecf0f1;
            z-index: 1;
        }

        .divider span {
            background: white;
            padding: 0 20px;
            position: relative;
            z-index: 2;
        }

        .social-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }

        .social-btn {
            flex: 1;
            padding: 12px;
            border: 2px solid #ecf0f1;
            border-radius: 10px;
            background: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .social-btn:hover {
            border-color: #e74c3c;
            transform: translateY(-1px);
        }

        .login-link {
            text-align: center;
            color: #666;
            font-size: 14px;
        }

        .login-link a {
            color: #e74c3c;
            text-decoration: none;
            font-weight: bold;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .join-text {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .join-desc {
            font-size: 16px;
            opacity: 0.9;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .benefits {
            list-style: none;
            text-align: left;
        }

        .benefits li {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .benefits li::before {
            content: '✓';
            background: white;
            color: #e74c3c;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 12px;
        }

        .decorative-elements {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
        }

        .circle1 {
            width: 80px;
            height: 80px;
            top: 15%;
            left: 15%;
            animation: float 6s ease-in-out infinite;
        }

        .circle2 {
            width: 120px;
            height: 120px;
            top: 50%;
            right: 10%;
            animation: float 4s ease-in-out infinite reverse;
        }

        .circle3 {
            width: 60px;
            height: 60px;
            bottom: 25%;
            left: 25%;
            animation: float 5s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        @media (max-width: 768px) {
            .register-container {
                flex-direction: column-reverse;
                max-width: 400px;
            }

            .register-left {
                padding: 40px 30px;
                max-height: none;
            }

            .register-right {
                padding: 40px 30px;
            }

            .form-row {
                flex-direction: column;
                gap: 0;
            }

            .join-text {
                font-size: 24px;
            }

            .form-title {
                font-size: 28px;
            }

            .social-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-left">
            <div class="logo">
                <div class="logo-icon">🛒</div>
                <span>Shop</span>
            </div>

            <h2 class="form-title">Create Account</h2>
            <p class="form-subtitle">Join us and get access to exclusive deals and fast delivery</p>

            <form>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="firstName">First Name</label>
                        <input class="form-input" type="text" id="firstName" placeholder="Enter first name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="lastName">Last Name</label>
                        <input class="form-input" type="text" id="lastName" placeholder="Enter last name" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input class="form-input" type="email" id="email" placeholder="Enter your email" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="phone">Phone Number</label>
                        <input class="form-input" type="tel" id="phone" placeholder="Enter phone number" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="birthdate">Date of Birth</label>
                        <input class="form-input" type="date" id="birthdate" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="address">Address</label>
                    <input class="form-input" type="text" id="address" placeholder="Enter your address" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="city">City</label>
                        <input class="form-input" type="text" id="city" placeholder="Enter city" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="country">Country</label>
                        <select class="form-select" id="country" required>
                            <option value="">Select country</option>
                            <option value="lebanon">Lebanon</option>
                            <option value="uae">UAE</option>
                            <option value="saudi">Saudi Arabia</option>
                            <option value="egypt">Egypt</option>
                            <option value="jordan">Jordan</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <input class="form-input" type="password" id="password" placeholder="Create password" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="confirmPassword">Confirm Password</label>
                        <input class="form-input" type="password" id="confirmPassword" placeholder="Confirm password" required>
                    </div>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="terms" required>
                    <label for="terms">
                        I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                    </label>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="newsletter">
                    <label for="newsletter">
                        Subscribe to our newsletter for exclusive offers and updates
                    </label>
                </div>

                <button type="submit" class="register-btn">Create Account</button>
            </form>

            <div class="divider">
                <span>or sign up with</span>
            </div>

            <div class="social-buttons">
                <button class="social-btn">
                    <span>📧</span> Google
                </button>
                <button class="social-btn">
                    <span>📘</span> Facebook
                </button>
            </div>

            <div class="login-link">
                Already have an account? <a href="login">Sign In</a>
            </div>
        </div>

        <div class="register-right">
            <div class="decorative-elements">
                <div class="circle circle1"></div>
                <div class="circle circle2"></div>
                <div class="circle circle3"></div>
            </div>

            <div class="join-text">Join Our Community</div>
            <div class="join-desc">
                Create your account today and unlock amazing benefits
            </div>

            <ul class="benefits">
                <li>Free delivery on orders over $50</li>
                <li>Exclusive member discounts</li>
                <li>Early access to sales</li>
                <li>Personalized recommendations</li>
                <li>Easy returns and exchanges</li>
                <li>24/7 customer support</li>
                <li>Loyalty rewards program</li>
                <li>Birthday special offers</li>
            </ul>
        </div>
    </div>
</body>
</html>