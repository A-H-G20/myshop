<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - Carrefour Lebanon</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }

        /* Scheduled Delivery Banner */
        .scheduled-delivery {
            background: #1e5ba8;
            color: white;
            padding: 8px 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .delivery-icon {
            background: white;
            color: #1e5ba8;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 10px;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #1e5ba8, #2d6cb8);
            color: white;
            padding: 15px 0;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 20px;
            font-weight: bold;
        }

        .logo-icon {
            width: 35px;
            height: 35px;
            background: #e74c3c;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
        }

        .arabic-text {
            font-size: 14px;
            margin-left: 10px;
        }

        .search-bar {
            flex: 1;
            max-width: 400px;
            margin: 0 30px;
            position: relative;
        }

        .search-bar input {
            width: 100%;
            padding: 10px 40px 10px 15px;
            border: none;
            border-radius: 25px;
            font-size: 14px;
            background: rgba(255,255,255,0.9);
        }

        .search-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            cursor: pointer;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .delivery-info {
            text-align: left;
            font-size: 12px;
        }

        .login-btn {
            padding: 8px 16px;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 5px;
            color: white;
            text-decoration: none;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .flag {
            width: 24px;
            height: 16px;
            background: linear-gradient(to bottom, #ff0000 33%, #ffffff 33%, #ffffff 66%, #00ff00 66%);
            border: 1px solid #ccc;
        }

        .cart-icon {
            background: rgba(255,255,255,0.1);
            padding: 8px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 18px;
        }

        /* Navigation */
        .navigation {
            background: #1e5ba8;
            padding: 0;
        }

        .nav-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            overflow-x: auto;
        }

        .nav-item {
            padding: 12px 16px;
            color: white;
            text-decoration: none;
            font-size: 12px;
            border-right: 1px solid rgba(255,255,255,0.1);
            transition: background 0.3s;
            white-space: nowrap;
        }

        .nav-item:hover {
            background: rgba(255,255,255,0.1);
        }

        .nav-item.categories {
            background: rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Breadcrumb */
        .breadcrumb {
            max-width: 1200px;
            margin: 0 auto;
            padding: 15px 20px;
            font-size: 14px;
            color: #666;
        }

        .breadcrumb a {
            color: #3498db;
            text-decoration: none;
        }

        /* Main Content */
        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Empty Cart */
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .empty-cart-icon {
            width: 200px;
            height: 200px;
            margin: 0 auto 30px;
            position: relative;
            background: linear-gradient(135deg, #e8f4fd, #f0f8ff);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .shopping-cart {
            width: 120px;
            height: 100px;
            position: relative;
        }

        .cart-body {
            width: 80px;
            height: 60px;
            background: #1e5ba8;
            border-radius: 8px;
            position: relative;
            margin: 0 auto;
        }

        .cart-handle {
            width: 30px;
            height: 3px;
            background: #333;
            position: absolute;
            top: -8px;
            left: 10px;
            border-radius: 2px;
        }

        .cart-handle::before {
            content: '';
            width: 3px;
            height: 15px;
            background: #333;
            position: absolute;
            left: 0;
            top: -6px;
            border-radius: 2px;
        }

        .cart-handle::after {
            content: '';
            width: 3px;
            height: 15px;
            background: #333;
            position: absolute;
            right: 0;
            top: -6px;
            border-radius: 2px;
        }

        .cart-wheels {
            display: flex;
            justify-content: space-between;
            width: 60px;
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
        }

        .wheel {
            width: 12px;
            height: 12px;
            background: #333;
            border-radius: 50%;
        }

        .carrefour-logo {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 12px;
            font-weight: bold;
        }

        .zero-badge {
            position: absolute;
            top: -10px;
            right: -10px;
            background: #27ae60;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }

        .empty-cart h2 {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .empty-cart p {
            color: #666;
            font-size: 16px;
            margin-bottom: 30px;
        }

        .start-shopping-btn {
            background: transparent;
            color: #3498db;
            border: 2px solid #3498db;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }

        .start-shopping-btn:hover {
            background: #3498db;
            color: white;
        }

        /* Cart with Items */
        .cart-container {
            display: none;
            grid-template-columns: 1fr 300px;
            gap: 30px;
        }

        .cart-section {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .delivery-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
            position: relative;
        }

        .delivery-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .collapse-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            color: #666;
        }

        .delivery-time {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .progress-container {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .progress-icon {
            width: 30px;
            height: 30px;
            background: #3498db;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
        }

        .progress-bar {
            flex: 1;
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #1e5ba8, #3498db);
            width: 15%;
            border-radius: 4px;
        }

        .progress-values {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .checkout-section {
            padding: 20px;
            text-align: center;
        }

        .checkout-title {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .checkout-subtitle {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .add-items-btn {
            background: transparent;
            color: #3498db;
            border: 2px solid #3498db;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }

        .add-items-btn:hover {
            background: #3498db;
            color: white;
        }

        .cart-items {
            padding: 20px;
        }

        .cart-title {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .cart-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .item-image {
            width: 80px;
            height: 80px;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: #666;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .item-price {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
        }

        .item-controls {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .qty-btn {
            width: 30px;
            height: 30px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qty-btn:hover {
            background: #f8f9fa;
        }

        .delete-btn {
            color: #e74c3c;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            padding: 5px;
        }

        .subtotal {
            padding: 20px;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
        }

        /* Order Summary */
        .order-summary {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: fit-content;
        }

        .summary-title {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            color: #666;
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            padding-top: 15px;
            border-top: 1px solid #e9ecef;
        }

        .vat-note {
            font-size: 12px;
            color: #999;
        }

        /* Toggle Button */
        .toggle-cart {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #3498db;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 1000;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            
            .search-bar {
                margin: 0;
                max-width: none;
            }
            
            .nav-content {
                overflow-x: auto;
            }
            
            .cart-container {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .main-content {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Scheduled Delivery Banner -->
    <div class="scheduled-delivery">
        <div class="delivery-icon">🗓️</div>
        <span>Scheduled Delivery</span>
    </div>

    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <div class="logo-icon">🛒</div>
                <span>Carrefour</span>
                <span class="arabic-text">كارفور</span>
            </div>
            
            <div class="search-bar">
                <input type="text" placeholder="Search for products">
                <div class="search-icon">🔍</div>
            </div>
            
            <div class="header-right">
                <div class="delivery-info">
                    <div style="font-size: 10px;">Delivery Time Today 7 PM, Delivery Fee 134...</div>
                    <div style="font-weight: bold;">Hazmieh - Baabda</div>
                </div>
                
                <a href="#" class="login-btn">
                    <span>👤</span>
                    <span>Login & Register</span>
                </a>
                
                <div class="flag"></div>
                
                <div class="cart-icon">🛒</div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="navigation">
        <div class="nav-content">
            <a href="#" class="nav-item categories">
                <span>📱</span>
                <span>All Categories</span>
            </a>
            <a href="#" class="nav-item">Food Cupboard</a>
            <a href="#" class="nav-item">Fruits & Vegetables</a>
            <a href="#" class="nav-item">Fresh Food</a>
            <a href="#" class="nav-item">Electronics & Appliances</a>
            <a href="#" class="nav-item">Smartphones, Tablets & Wearables</a>
            <a href="#" class="nav-item">Baby Products</a>
            <a href="#" class="nav-item">Beverages</a>
            <a href="#" class="nav-item">Alcohol</a>
        </div>
    </nav>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="#">Home</a> > Cart
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Empty Cart -->
        <div class="empty-cart" id="emptyCart">
            <div class="empty-cart-icon">
                <div class="shopping-cart">
                    <div class="cart-body">
                        <div class="carrefour-logo">🛒<br>Carrefour</div>
                    </div>
                    <div class="cart-handle"></div>
                    <div class="cart-wheels">
                        <div class="wheel"></div>
                        <div class="wheel"></div>
                    </div>
                </div>
                <div class="zero-badge">0</div>
            </div>
            
            <h2>Looking for something?</h2>
            <p>Add your favourite items to your cart.</p>
            
            <button class="start-shopping-btn" onclick="window.location.href='#'">Start Shopping</button>
        </div>

        <!-- Cart with Items -->
        <div class="cart-container" id="cartWithItems">
            <div class="cart-section">
                <!-- Delivery Header -->
                <div class="delivery-header">
                    <div class="delivery-title">
                        <span>Scheduled Delivery (1 item)</span>
                        <button class="collapse-btn">^</button>
                    </div>
                    <div class="delivery-time">Today 7 PM - 8 PM</div>
                    
                    <div class="progress-container">
                        <div class="progress-icon">📍</div>
                        <div class="progress-bar">
                            <div class="progress-fill"></div>
                        </div>
                    </div>
                    
                    <div class="progress-values">
                        <span>Start</span>
                        <span>Min. Value<br><strong>LBP 700000</strong></span>
                        <span>Free Delivery<br><strong>LBP 4500000</strong></span>
                    </div>
                </div>

                <!-- Checkout Section -->
                <div class="checkout-section">
                    <div class="checkout-title">Ready to Checkout?</div>
                    <div class="checkout-subtitle">Spend <strong>LBP 589031</strong> to place the order</div>
                    <button class="add-items-btn">Add Items</button>
                </div>

                <!-- Cart Items -->
                <div class="cart-items">
                    <div class="cart-title">My Cart</div>
                    
                    <div class="cart-item">
                        <div class="item-image">
                            <div style="text-align: center; color: #666;">
                                Winchester<br>Silver<br>Product<br>Image
                            </div>
                        </div>
                        
                        <div class="item-details">
                            <div class="item-name">Winchester Silver Carton</div>
                            <div class="item-price">110,969<span style="font-size: 12px; color: #666;">LBP</span></div>
                        </div>
                        
                        <div class="item-controls">
                            <button class="delete-btn">🗑️</button>
                            <div class="quantity-control">
                                <button class="qty-btn" onclick="changeQuantity(-1)">-</button>
                                <span id="quantity">1</span>
                                <button class="qty-btn" onclick="changeQuantity(1)">+</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subtotal -->
                <div class="subtotal">
                    <span>Subtotal</span>
                    <span>LBP 110,969.00</span>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="order-summary">
                <div class="summary-title">Order Summary</div>
                
                <div class="summary-row">
                    <span>LBP</span>
                    <span>110,969.00</span>
                </div>
                
                <div class="summary-row">
                    <span></span>
                    <span class="vat-note">(Incl. of VAT)</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Toggle Button -->
    <button class="toggle-cart" onclick="toggleCart()">Toggle Cart View</button>

    <script>
        let isEmptyCart = true;

        function toggleCart() {
            const emptyCart = document.getElementById('emptyCart');
            const cartWithItems = document.getElementById('cartWithItems');
            const toggleBtn = document.querySelector('.toggle-cart');
            
            if (isEmptyCart) {
                emptyCart.style.display = 'none';
                cartWithItems.style.display = 'grid';
                toggleBtn.textContent = 'Show Empty Cart';
                isEmptyCart = false;
            } else {
                emptyCart.style.display = 'block';
                cartWithItems.style.display = 'none';
                toggleBtn.textContent = 'Show Cart with Items';
                isEmptyCart = true;
            }
        }

        function changeQuantity(change) {
            const quantityElement = document.getElementById('quantity');
            let quantity = parseInt(quantityElement.textContent);
            quantity = Math.max(1, quantity + change);
            quantityElement.textContent = quantity;
            
            // Update price
            const basePrice = 110969;
            const newPrice = basePrice * quantity;
            document.querySelector('.item-price').innerHTML = `${newPrice.toLocaleString()}<span style="font-size: 12px; color: #666;">LBP</span>`;
            document.querySelector('.subtotal span:last-child').textContent = `LBP ${newPrice.toLocaleString()}.00`;
            document.querySelector('.summary-row span:last-child').textContent = `${newPrice.toLocaleString()}.00`;
        }

        // Search functionality
        document.querySelector('.search-icon').addEventListener('click', function() {
            const searchInput = document.querySelector('.search-bar input');
            if (searchInput.value.trim()) {
                alert('Searching for: ' + searchInput.value);
            }
        });

        document.querySelector('.search-bar input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                if (this.value.trim()) {
                    alert('Searching for: ' + this.value);
                }
            }
        });
    </script>
</body>
</html>