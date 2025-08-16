<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Information Page</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }

        .info-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .info-list {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
            margin-bottom: 30px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px 25px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 150px;
            justify-content: center;
        }

        .info-item:hover {
            background: #e9ecef;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .info-item svg {
            width: 20px;
            height: 20px;
            fill: #666;
        }

        .info-item span {
            font-size: 14px;
            color: #333;
            font-weight: 500;
        }

        /* Offcanvas Styles */
        .offcanvas-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .offcanvas-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .offcanvas {
            position: fixed;
            top: 0;
            right: -400px;
            width: 400px;
            height: 100%;
            background: white;
            z-index: 1001;
            transition: right 0.3s ease;
            box-shadow: -2px 0 10px rgba(0,0,0,0.1);
        }

        .offcanvas.active {
            right: 0;
        }

        .offcanvas-header {
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .offcanvas-tabs {
            display: flex;
            gap: 20px;
        }

        .offcanvas-tab {
            padding: 10px 0;
            border-bottom: 2px solid transparent;
            cursor: pointer;
            font-weight: 500;
            color: #666;
            transition: all 0.3s ease;
        }

        .offcanvas-tab.active {
            color: #333;
            border-bottom-color: #007bff;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
            padding: 5px;
        }

        .offcanvas-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .form-group select,
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            background: white;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .checkbox-group input[type="checkbox"] {
            width: auto;
        }

        .calculate-btn {
            width: 100%;
            padding: 15px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .calculate-btn:hover {
            background: #5a6268;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        @media (max-width: 768px) {
            .info-list {
                flex-direction: column;
                align-items: center;
            }

            .info-item {
                width: 100%;
                max-width: 300px;
            }

            .offcanvas {
                width: 100%;
                right: -100%;
            }
        }
    </style>
</head>
<body>
    <div class="info-container">
        <div class="info-list">
            <div class="info-item" onclick="openOffcanvas('shipping')">
                <svg viewBox="0 0 24 24">
                    <path d="M3,4A2,2 0 0,0 1,6V17H3A3,3 0 0,0 6,20A3,3 0 0,0 9,17H15A3,3 0 0,0 18,20A3,3 0 0,0 21,17H23V12L20,8H17V4M3,6H15V15H9.17C8.5,14.4 7.79,14 7,14C6.21,14 5.5,14.4 4.83,15H3M17,10H19.5L21.47,12H17M6,15.5A1.5,1.5 0 0,1 7.5,17A1.5,1.5 0 0,1 6,18.5A1.5,1.5 0 0,1 4.5,17A1.5,1.5 0 0,1 6,15.5M18,15.5A1.5,1.5 0 0,1 19.5,17A1.5,1.5 0 0,1 18,18.5A1.5,1.5 0 0,1 16.5,17A1.5,1.5 0 0,1 18,15.5Z"/>
                </svg>
                <span>Shipping</span>
            </div>

            <div class="info-item" onclick="openOffcanvas('returns')">
                <svg viewBox="0 0 24 24">
                    <path d="M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4M12,6A6,6 0 0,1 18,12A6,6 0 0,1 12,18A6,6 0 0,1 6,12A6,6 0 0,1 12,6M10,8V10L6,12L10,14V16L4,12L10,8Z"/>
                </svg>
                <span>Returns</span>
            </div>

            <div class="info-item" onclick="openOffcanvas('faq')">
                <svg viewBox="0 0 24 24">
                    <path d="M11,18H13V16H11V18M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,20C7.59,20 4,16.41 4,12C4,7.59 7.59,4 12,4C16.41,4 20,7.59 20,12C20,16.41 16.41,20 12,20M12,6A4,4 0 0,0 8,10H10A2,2 0 0,1 12,8A2,2 0 0,1 14,10C14,12 11,11.75 11,15H13C13,12.75 16,12.5 16,10A4,4 0 0,0 12,6Z"/>
                </svg>
                <span>FAQ</span>
            </div>

            <div class="info-item" onclick="openOffcanvas('support')">
                <svg viewBox="0 0 24 24">
                    <path d="M12,2A3,3 0 0,1 15,5V11A3,3 0 0,1 12,14A3,3 0 0,1 9,11V5A3,3 0 0,1 12,2M19,11C19,14.53 16.39,17.44 13,17.93V21H11V17.93C7.61,17.44 5,14.53 5,11H7A5,5 0 0,0 12,16A5,5 0 0,0 17,11H19Z"/>
                </svg>
                <span>Support</span>
            </div>

            <div class="info-item" onclick="openOffcanvas('warranty')">
                <svg viewBox="0 0 24 24">
                    <path d="M12,1L3,5V11C3,16.55 6.84,21.74 12,23C17.16,21.74 21,16.55 21,11V5L12,1M12,7C13.4,7 14.8,8.6 14.8,10V11.5C15.4,11.5 16,12.1 16,12.7V16.2C16,16.8 15.4,17.3 14.8,17.3H9.2C8.6,17.3 8,16.8 8,16.2V12.7C8,12.1 8.6,11.5 9.2,11.5V10C9.2,8.6 10.6,7 12,7M12,8.2C11.2,8.2 10.5,8.7 10.5,10V11.5H13.5V10C13.5,8.7 12.8,8.2 12,8.2Z"/>
                </svg>
                <span>Warranty</span>
            </div>
        </div>
    </div>

    <!-- Offcanvas -->
    <div class="offcanvas-overlay" id="offcanvasOverlay" onclick="closeOffcanvas()"></div>
    <div class="offcanvas" id="offcanvas">
        <div class="offcanvas-header">
            <div class="offcanvas-tabs">
                <div class="offcanvas-tab active" data-tab="shipping">Shipping</div>
                <div class="offcanvas-tab" data-tab="returns">Returns</div>
                <div class="offcanvas-tab" data-tab="faq">FAQ's</div>
            </div>
            <button class="close-btn" onclick="closeOffcanvas()">&times;</button>
        </div>
        <div class="offcanvas-body">
            <div class="tab-content active" id="shipping-content">
                <div class="form-group">
                    <label for="country">Country</label>
                    <select id="country">
                        <option value="">Country</option>
                        <option value="us">United States</option>
                        <option value="ca">Canada</option>
                        <option value="uk">United Kingdom</option>
                        <option value="au">Australia</option>
                        <option value="id">Indonesia</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="city">Town/City</label>
                    <select id="city">
                        <option value="">City</option>
                        <option value="new-york">New York</option>
                        <option value="los-angeles">Los Angeles</option>
                        <option value="chicago">Chicago</option>
                        <option value="houston">Houston</option>
                        <option value="jakarta">Jakarta</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="postcode">Postcode/Zip</label>
                    <select id="postcode">
                        <option value="">3820</option>
                        <option value="10001">10001</option>
                        <option value="90210">90210</option>
                        <option value="60601">60601</option>
                        <option value="77001">77001</option>
                        <option value="12345">12345</option>
                    </select>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="localPickup">
                    <label for="localPickup">Local pickup - There is 2 store with 1 stock close to your location</label>
                </div>

                <button class="calculate-btn">CALCULATE SHIPPING</button>
            </div>

            <div class="tab-content" id="returns-content">
                <h3>Returns Policy</h3>
                <p>Our returns policy allows you to return items within 30 days of purchase. Items must be in original condition with tags attached.</p>
                <ul>
                    <li>Free returns within 30 days</li>
                    <li>Items must be unworn and in original packaging</li>
                    <li>Refund processed within 5-7 business days</li>
                </ul>
            </div>

            <div class="tab-content" id="faq-content">
                <h3>Frequently Asked Questions</h3>
                <div style="margin-bottom: 15px;">
                    <strong>Q: How long does shipping take?</strong>
                    <p>A: Standard shipping takes 3-5 business days, express shipping takes 1-2 business days.</p>
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>Q: Do you ship internationally?</strong>
                    <p>A: Yes, we ship to most countries worldwide. Shipping costs vary by location.</p>
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>Q: What payment methods do you accept?</strong>
                    <p>A: We accept all major credit cards, PayPal, and bank transfers.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openOffcanvas(tab) {
            const overlay = document.getElementById('offcanvasOverlay');
            const offcanvas = document.getElementById('offcanvas');
            
            overlay.classList.add('active');
            offcanvas.classList.add('active');
            
            // Switch to the appropriate tab
            switchTab(tab);
        }

        function closeOffcanvas() {
            const overlay = document.getElementById('offcanvasOverlay');
            const offcanvas = document.getElementById('offcanvas');
            
            overlay.classList.remove('active');
            offcanvas.classList.remove('active');
        }

        function switchTab(tabName) {
            // Remove active class from all tabs and contents
            const tabs = document.querySelectorAll('.offcanvas-tab');
            const contents = document.querySelectorAll('.tab-content');
            
            tabs.forEach(tab => tab.classList.remove('active'));
            contents.forEach(content => content.classList.remove('active'));
            
            // Add active class to selected tab and content
            const selectedTab = document.querySelector(`[data-tab="${tabName}"]`);
            const selectedContent = document.getElementById(`${tabName}-content`);
            
            if (selectedTab) selectedTab.classList.add('active');
            if (selectedContent) selectedContent.classList.add('active');
        }

        // Add click event listeners to tabs
        document.querySelectorAll('.offcanvas-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const tabName = this.getAttribute('data-tab');
                switchTab(tabName);
            });
        });

        // Close offcanvas when pressing Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeOffcanvas();
            }
        });
    </script>
</body>
</html>
