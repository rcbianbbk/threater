<!-- Main Footer -->
<footer class="main-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <div class="brand-logo" style="margin-bottom: 14px;">
                    <i class="fa-solid fa-film" style="color: #e50914;"></i>
                    <span>CINEMA</span> WORLD
                </div>
                <p style="color: var(--text-muted); font-size: 14px; line-height: 1.6; margin-bottom: 20px;">
                    Experience the magic of cinema with cutting-edge 4K laser projection, Dolby Atmos 360° immersive audio, and premium recliner seating.
                </p>
                <div style="display: flex; gap: 12px;">
                    <a href="https://www.facebook.com/rcbian.bbk18" target="_blank" class="btn btn-outline btn-sm" style="width:36px; height:36px; padding:0; display:flex; align-items:center; justify-content:center;" title="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="https://www.instagram.com/rcbian.bbk/" target="_blank" class="btn btn-outline btn-sm" style="width:36px; height:36px; padding:0; display:flex; align-items:center; justify-content:center;" title="Instagram (Bibek)"><i class="fa-brands fa-instagram"></i></a>
                    <a href="https://www.instagram.com/sameerthapa403/" target="_blank" class="btn btn-outline btn-sm" style="width:36px; height:36px; padding:0; display:flex; align-items:center; justify-content:center;" title="Instagram (Sameer)"><i class="fa-brands fa-instagram"></i></a>
                    <a href="https://x.com/rcbianbbk" target="_blank" class="btn btn-outline btn-sm" style="width:36px; height:36px; padding:0; display:flex; align-items:center; justify-content:center;" title="Twitter/X"><i class="fa-brands fa-x-twitter"></i></a>
                    <a href="https://www.youtube.com/@rcbian_bbk" target="_blank" class="btn btn-outline btn-sm" style="width:36px; height:36px; padding:0; display:flex; align-items:center; justify-content:center;" title="YouTube"><i class="fa-brands fa-youtube"></i></a>
                    <a href="https://github.com/rcbianbbk" target="_blank" class="btn btn-outline btn-sm" style="width:36px; height:36px; padding:0; display:flex; align-items:center; justify-content:center;" title="GitHub"><i class="fa-brands fa-github"></i></a>
                </div>
            </div>

            <div class="footer-col">
                <h4>Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="index.php"><i class="fa-solid fa-chevron-right" style="font-size: 11px;"></i> Now Showing</a></li>
                    <li><a href="index.php#upcoming"><i class="fa-solid fa-chevron-right" style="font-size: 11px;"></i> Coming Soon</a></li>
                    <li><a href="my_bookings.php"><i class="fa-solid fa-chevron-right" style="font-size: 11px;"></i> My Tickets</a></li>
                    <li><a href="profile.php"><i class="fa-solid fa-chevron-right" style="font-size: 11px;"></i> Profile Account</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Experiences</h4>
                <ul class="footer-links">
                    <li><a href="#"><i class="fa-solid fa-chevron-right" style="font-size: 11px;"></i> IMAX 3D Experience</a></li>
                    <li><a href="#"><i class="fa-solid fa-chevron-right" style="font-size: 11px;"></i> Dolby Atmos Hall</a></li>
                    <li><a href="#"><i class="fa-solid fa-chevron-right" style="font-size: 11px;"></i> VIP Recliner Lounge</a></li>
                    <li><a href="#"><i class="fa-solid fa-chevron-right" style="font-size: 11px;"></i> Gourmet Concessions</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Customer Care</h4>
                <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 8px;">
                    <i class="fa-solid fa-location-dot" style="color: var(--accent); margin-right: 6px;"></i> Durbar Marg, Kathmandu, Nepal
                </p>
                <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 8px;">
                    <i class="fa-solid fa-phone" style="color: var(--accent); margin-right: 6px;"></i> +977 01-4455667 / 9801234567
                </p>
                <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 16px;">
                    <i class="fa-solid fa-envelope" style="color: var(--accent); margin-right: 6px;"></i> support@cinemaworld.com
                </p>
                <span style="display:inline-block; font-size:11px; padding:4px 10px; background:rgba(229,9,20,0.15); color:#ff4d4f; border-radius:20px; border:1px solid rgba(229,9,20,0.3);">
                    <i class="fa-solid fa-ticket"></i> VIP Cinema Box Office
                </span>
            </div>
        </div>

        <div class="footer-bottom" style="text-align: center; margin-top: 30px; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 20px;">
            <p>&copy; <?= date('Y') ?> Cinema World Entertainment Ltd. All rights reserved.</p>
            <p style="font-size: 13px; margin-top: 6px;">
                Designed & Developed with <i class="fa-solid fa-heart" style="color: #e50914;"></i> by 
                <a href="https://www.instagram.com/sameerthapa403/" target="_blank" style="color: var(--accent); text-decoration: none; font-weight: 600;">Sameer Thapa</a> & 
                <a href="https://github.com/rcbianbbk" target="_blank" style="color: var(--accent); text-decoration: none; font-weight: 600;">Bibek Pathak</a>
            </p>
        </div>
    </div>
</footer>

<!-- Cinematic Theater Chat Widget -->
<div id="cinemaChatWidget" style="position: fixed; bottom: 25px; right: 25px; z-index: 2147483647; font-family: inherit;">
    
    <!-- Bot Toggle Button -->
    <button id="chatToggleBtn" type="button" onclick="toggleChatWindow()" style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #e50914, #b20710); border: 2px solid rgba(255,215,0,0.4); cursor: pointer; box-shadow: 0 6px 25px rgba(229,9,20,0.6); display: flex; align-items: center; justify-content: center; pointer-events: auto; padding: 0; transition: transform 0.2s;">
        <svg viewBox="0 0 100 100" style="width: 36px; height: 36px; fill: #ffffff;">
            <circle cx="50" cy="18" r="5"></circle>
            <line x1="50" y1="23" x2="50" y2="33" stroke="#ffffff" stroke-width="4"></line>
            <path d="M 25 38 C 25 35, 75 35, 75 38 C 85 45, 88 65, 75 75 C 65 82, 35 82, 25 75 C 12 65, 15 45, 25 38 Z"></path>
            <path d="M 12 48 C 8 48, 8 62, 12 62 Z" stroke="#ffffff" stroke-width="4" fill="none"></path>
            <path d="M 88 48 C 92 48, 92 62, 88 62 Z" stroke="#ffffff" stroke-width="4" fill="none"></path>
            <path d="M 32 46 C 32 43, 68 43, 68 46 C 72 53, 72 61, 68 68 C 68 71, 32 71, 32 68 C 28 61, 28 53, 32 46 Z" fill="#0c0c0c"></path>
            <circle cx="43" cy="57" r="5" fill="#ffd700"></circle>
            <circle cx="57" cy="57" r="5" fill="#ffd700"></circle>
        </svg>
    </button>

    <!-- Chat Window -->
    <div id="chatWindow" style="display: none; width: 380px; height: 540px; background: #141414; border: 1px solid rgba(229,9,20,0.3); border-radius: 16px; flex-direction: column; overflow: hidden; box-shadow: 0 15px 40px rgba(0,0,0,0.9); position: absolute; bottom: 75px; right: 0; z-index: 2147483647;">
        
        <!-- Header: Curtain / Cinema Theme -->
        <div style="padding: 16px; background: linear-gradient(135deg, #1f1f1f, #0c0c0c); border-bottom: 2px solid #e50914; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 36px; height: 36px; background: #e50914; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 0 10px rgba(229,9,20,0.5);">
                    <svg viewBox="0 0 100 100" style="width: 20px; height: 20px; fill: #ffffff;">
                        <circle cx="50" cy="18" r="5"></circle>
                        <line x1="50" y1="23" x2="50" y2="33" stroke="#ffffff" stroke-width="4"></line>
                        <path d="M 25 38 C 25 35, 75 35, 75 38 C 85 45, 88 65, 75 75 C 65 82, 35 82, 25 75 C 12 65, 15 45, 25 38 Z"></path>
                        <path d="M 32 46 C 32 43, 68 43, 68 46 C 72 53, 72 61, 68 68 C 68 71, 32 71, 32 68 C 28 61, 28 53, 32 46 Z" fill="#141414"></path>
                        <circle cx="43" cy="57" r="5" fill="#ffd700"></circle>
                        <circle cx="57" cy="57" r="5" fill="#ffd700"></circle>
                    </svg>
                </div>
                <div>
                    <h4 style="font-size: 15px; color: #fff; margin: 0; font-weight: 700; letter-spacing: 0.5px;">CineWorld Concierge</h4>
                    <span style="font-size: 11px; color: #ffd700;"><i class="fa-solid fa-star" style="font-size: 8px;"></i> VIP Box Office Support</span>
                </div>
            </div>
            <button type="button" onclick="toggleChatWindow()" style="background: transparent; border: none; color: #aaa; font-size: 18px; cursor: pointer; pointer-events: auto; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#aaa'"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <!-- Messages Area -->
        <div id="chatMessages" style="flex: 1; padding: 16px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; font-size: 13px; color: #e5e5e5; background: #0c0c0c;">
            <div style="background: #1f1f1f; border-left: 3px solid #e50914; padding: 12px 14px; border-radius: 0 12px 12px 12px; max-width: 90%; align-self: flex-start; line-height: 1.6; box-shadow: 0 4px 12px rgba(0,0,0,0.5);">
                🎬 <strong>Welcome to Cinema World!</strong><br>नमस्ते! सिनेमा वर्ल्डको टिकट काउन्टरमा तपाईंलाई स्वागत छ।<br><small style="color:#b3b3b3;">Tap a question below for instant response / तलबाट एउटा विकल्प छान्नुहोस्:</small>
            </div>
        </div>

        <!-- Preset Interactive Buttons (Theater Red/Dark Theme) -->
        <div style="padding: 12px; background: #141414; border-top: 1px solid rgba(255,255,255,0.08); display: flex; flex-direction: column; gap: 6px; max-height: 210px; overflow-y: auto;">
            
            <button type="button" onclick="askQuestion('showing')" style="background: #1f1f1f; border: 1px solid rgba(229,9,20,0.3); color: #fff; padding: 8px 12px; border-radius: 8px; font-size: 11.5px; text-align: left; cursor: pointer; pointer-events: auto; transition: background 0.2s;" onmouseover="this.style.background='#2a2a2a'" onmouseout="this.style.background='#1f1f1f'">
                🎬 Now Showing / अहिले हलमा के चलिरहेको छ?
            </button>
            
            <button type="button" onclick="askQuestion('price')" style="background: #1f1f1f; border: 1px solid rgba(229,9,20,0.3); color: #fff; padding: 8px 12px; border-radius: 8px; font-size: 11.5px; text-align: left; cursor: pointer; pointer-events: auto; transition: background 0.2s;" onmouseover="this.style.background='#2a2a2a'" onmouseout="this.style.background='#1f1f1f'">
                🎫 Ticket Prices / टिकटको मूल्य कति हो?
            </button>
            
            <button type="button" onclick="askQuestion('booking')" style="background: #1f1f1f; border: 1px solid rgba(229,9,20,0.3); color: #fff; padding: 8px 12px; border-radius: 8px; font-size: 11.5px; text-align: left; cursor: pointer; pointer-events: auto; transition: background 0.2s;" onmouseover="this.style.background='#2a2a2a'" onmouseout="this.style.background='#1f1f1f'">
                🎟️ How to Book / अनलाइन टिकट कसरी बुक गर्ने?
            </button>

            <button type="button" onclick="askQuestion('payment')" style="background: #1f1f1f; border: 1px solid rgba(229,9,20,0.3); color: #fff; padding: 8px 12px; border-radius: 8px; font-size: 11.5px; text-align: left; cursor: pointer; pointer-events: auto; transition: background 0.2s;" onmouseover="this.style.background='#2a2a2a'" onmouseout="this.style.background='#1f1f1f'">
                💳 Payment Methods / भुक्तानी कसरी गर्ने?
            </button>

            <button type="button" onclick="askQuestion('cancel')" style="background: #1f1f1f; border: 1px solid rgba(229,9,20,0.3); color: #fff; padding: 8px 12px; border-radius: 8px; font-size: 11.5px; text-align: left; cursor: pointer; pointer-events: auto; transition: background 0.2s;" onmouseover="this.style.background='#2a2a2a'" onmouseout="this.style.background='#1f1f1f'">
                ❌ Refund & Cancellation / टिकट रद्द हुन्छ कि हुन्न?
            </button>

            <button type="button" onclick="askQuestion('snacks')" style="background: #1f1f1f; border: 1px solid rgba(229,9,20,0.3); color: #fff; padding: 8px 12px; border-radius: 8px; font-size: 11.5px; text-align: left; cursor: pointer; pointer-events: auto; transition: background 0.2s;" onmouseover="this.style.background='#2a2a2a'" onmouseout="this.style.background='#1f1f1f'">
                🍿 Food & Snacks / हलभित्र खाजा लैजान पाइन्छ?
            </button>
            
            <button type="button" onclick="askQuestion('location')" style="background: #1f1f1f; border: 1px solid rgba(229,9,20,0.3); color: #fff; padding: 8px 12px; border-radius: 8px; font-size: 11.5px; text-align: left; cursor: pointer; pointer-events: auto; transition: background 0.2s;" onmouseover="this.style.background='#2a2a2a'" onmouseout="this.style.background='#1f1f1f'">
                📍 Location / हल कहाँ अवस्थित छ?
            </button>

        </div>
    </div>
</div>

<script>
function toggleChatWindow() {
    let win = document.getElementById('chatWindow');
    if (win.style.display === 'none' || win.style.display === '') {
        win.style.display = 'flex';
    } else {
        win.style.display = 'none';
    }
}

function askQuestion(type) {
    let container = document.getElementById('chatMessages');
    let userText = '';
    let botReply = '';

    if (type === 'showing') {
        userText = '🎬 Now Showing / अहिले हलमा के चलिरहेको छ?';
        botReply = 'We have many blockbuster movies currently showing on our 4K Laser & Dolby Atmos screens. Please check the home page Now Showing section.<br><br>हामीसँग अहिले 4K लेजर र डल्बी एट्मस स्क्रीनहरूमा थुप्रै ब्लकबस्टर मुभीहरू चलिरहेका छन्। कृपया होमपेजको Now Showing सेक्सन हेर्नुहोस्।';
    } else if (type === 'price') {
        userText = '🎫 Ticket Prices / टिकटको मूल्य कति हो?';
        botReply = 'Ticket prices range from Rs. 250 to Rs. 600 depending on seat types (Normal, VIP Recliner, IMAX).<br><br>सिटको प्रकार (Normal, VIP Recliner, IMAX) अनुसार टिकटको मूल्य रु. २५० देखि रु. ६०० सम्म पर्नेछ।';
    } else if (type === 'booking') {
        userText = '🎟️ How to Book / अनलाइन टिकट कसरी बुक गर्ने?';
        botReply = 'Select your favorite movie, choose your preferred luxury seats, and pay online via eSewa or Khalti to get your digital e-ticket instantly.<br><br>मनपर्ने मुभी र लक्जरी सिट छनौट गरेर इसेवा वा खल्ती मार्फत अनलाइन भुक्तानी गरी तुरुन्तै डिजिटल इ-टिकट लिन सक्नुहुन्छ।';
    } else if (type === 'payment') {
        userText = '💳 Payment Methods / भुक्तानी कसरी गर्ने?';
        botReply = 'You can pay securely using eSewa, Khalti, or major Mobile Banking gateways.<br><br>तपाईंले इसेवा (eSewa), खल्ती (Khalti) वा मोबाइल बैंकिङ मार्फत सुरक्षित रूपमा भुक्तानी गर्न सक्नुहुन्छ।';
    } else if (type === 'cancel') {
        userText = '❌ Refund & Cancellation / टिकट रद्द हुन्छ कि हुन्न?';
        botReply = 'Tickets once booked cannot be cancelled or refunded as per our cinema box-office policy.<br><br>हाम्रो बक्स अफिसको नीति अनुसार एक पटक बुक भइसकेको टिकट रद्द वा फिर्ता (Refund) गर्न मिल्दैन।';
    } else if (type === 'snacks') {
        userText = '🍿 Food & Snacks / हलभित्र खाजा लैजान पाइन्छ?';
        botReply = 'Outside food is not permitted, but you can enjoy freshly popped gourmet popcorn, cold drinks, and snacks from our concession stand.<br><br>बाहिरबाट खाजा लैजान निषेध गरिएको छ, तर तपाईंले हाम्रो कन्सेसन काउन्टरबाट ताजा पपकोर्न र स्न्याक्स खरिद गर्न सक्नुहुन्छ।';
    } else if (type === 'location') {
        userText = '📍 Location / हल कहाँ अवस्थित छ?';
        botReply = 'Our flagship cinema is located at Durbar Marg, Kathmandu, Nepal.<br><br>हाम्रो मुख्य सिनेमा हल दरबारमार्ग, काठमाडौं, नेपालमा अवस्थित छ।';
    }

    container.innerHTML += `<div style="background: #e50914; padding: 10px 14px; border-radius: 12px 0 12px 12px; max-width: 90%; align-self: flex-end; line-height: 1.5; color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.5);">${userText}</div>`;
    container.scrollTop = container.scrollHeight;

    setTimeout(() => {
        container.innerHTML += `<div style="background: #1f1f1f; border-left: 3px solid #e50914; padding: 10px 14px; border-radius: 0 12px 12px 12px; max-width: 90%; align-self: flex-start; line-height: 1.5; color: #e5e5e5; box-shadow: 0 4px 12px rgba(0,0,0,0.5);">${botReply}</div>`;
        container.scrollTop = container.scrollHeight;
    }, 400);
}
</script>