<!-- Floating Cinema Chatbot Widget -->
<div id="cinemaChatWidget" style="position: fixed; bottom: 25px; right: 25px; z-index: 9999; font-family: inherit;">
    <!-- Chat Toggle Button -->
    <button id="chatToggleBtn" onclick="toggleChatWindow()" style="width: 55px; height: 55px; border-radius: 50%; background: #dc2626; border: none; color: #fff; font-size: 22px; cursor: pointer; box-shadow: 0 4px 20px rgba(220,38,38,0.4); display: flex; align-items: center; justify-content: center; transition: transform 0.3s;">
        <i class="fa-solid fa-robot" id="chatIcon"></i>
    </button>

    <!-- Chat Window -->
    <div id="chatWindow" style="display: none; width: 360px; height: 480px; background: rgba(15, 15, 20, 0.98); backdrop-filter: blur(12px); border: 1px solid rgba(220,38,38,0.3); border-radius: 16px; flex-direction: column; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.8); position: absolute; bottom: 70px; right: 0;">
        
        <!-- Chat Header -->
        <div style="padding: 14px 16px; background: rgba(220,38,38,0.15); border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 32px; height: 32px; background: #dc2626; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 14px;">
                    <i class="fa-solid fa-robot"></i>
                </div>
                <div>
                    <h4 style="font-size: 14px; color: #fff; margin: 0;">CineWorld Concierge</h4>
                    <span style="font-size: 11px; color: #fbbf24;"><i class="fa-solid fa-star" style="font-size: 9px;"></i> VIP Box Office Support</span>
                </div>
            </div>
            <button onclick="toggleChatWindow()" style="background: transparent; border: none; color: #fff; font-size: 16px; cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <!-- Chat Messages Area (Scrollable Feed) -->
        <div id="chatMessages" style="flex: 1; padding: 15px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; font-size: 13px; color: #fff;">
            <div style="background: rgba(255,255,255,0.08); padding: 12px 14px; border-radius: 12px; border-left: 3px solid #dc2626; max-width: 85%; align-self: flex-start; line-height: 1.4;">
                नमस्ते! 👋 म CineWorld Concierge हुँ। सिनेमा, टिकट मूल्य वा हलको बारेमा केही सोध्नुपर्छ कि?<br><br>
                Hello! 👋 I am CineWorld Concierge. How can I help you today regarding movies or tickets?
            </div>
        </div>

        <!-- Chat Input Form (Type text and send) -->
        <div style="padding: 12px; background: rgba(0,0,0,0.85); border-top: 1px solid rgba(255,255,255,0.1); display: flex; gap: 8px; align-items: center; flex-shrink: 0;">
            <input type="text" id="chatInput" placeholder="Type your question here..." style="flex: 1; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.2); border-radius: 20px; padding: 10px 14px; color: #fff; font-size: 13px; outline: none;" onkeypress="handleChatKeyPress(event)">
            <button onclick="sendUserMessage()" style="background: #dc2626; border: none; width: 38px; height: 38px; border-radius: 50%; color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: background 0.2s;"><i class="fa-solid fa-paper-plane" style="font-size: 13px;"></i></button>
        </div>
    </div>
</div>

<script>
function toggleChatWindow() {
    let win = document.getElementById('chatWindow');
    let icon = document.getElementById('chatIcon');
    if (win.style.display === 'none' || win.style.display === '') {
        win.style.display = 'flex';
        icon.className = 'fa-solid fa-xmark';
    } else {
        win.style.display = 'none';
        icon.className = 'fa-solid fa-robot';
    }
}

function handleChatKeyPress(e) {
    if (e.key === 'Enter') {
        sendUserMessage();
    }
}

function sendUserMessage() {
    let input = document.getElementById('chatInput');
    let msg = input.value.trim();
    if (!msg) return;

    let container = document.getElementById('chatMessages');

    // 1. Append User Typed Message
    container.innerHTML += `<div style="background: #dc2626; padding: 10px 14px; border-radius: 12px; max-width: 80%; align-self: flex-end; color: #fff; word-break: break-word;">${escapeHtml(msg)}</div>`;
    input.value = '';
    container.scrollTop = container.scrollHeight;

    // 2. Show Typing Indicator
    let loadingId = 'loading_' + Date.now();
    container.innerHTML += `<div id="${loadingId}" style="background: rgba(255,255,255,0.08); padding: 10px 14px; border-radius: 12px; max-width: 80%; align-self: flex-start; color: #9ca3af; font-style: italic;">CineBot is typing...</div>`;
    container.scrollTop = container.scrollHeight;

    // 3. Send to PHP Backend (Gemini API)
    fetch('chatbot_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: msg })
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById(loadingId).remove();
        container.innerHTML += `<div style="background: rgba(255,255,255,0.08); padding: 12px 14px; border-radius: 12px; border-left: 3px solid #dc2626; max-width: 85%; align-self: flex-start; color: #fff; line-height: 1.4; word-break: break-word;">${escapeHtml(data.reply)}</div>`;
        container.scrollTop = container.scrollHeight;
    })
    .catch(error => {
        document.getElementById(loadingId).remove();
        container.innerHTML += `<div style="background: rgba(255,255,255,0.08); padding: 10px 14px; border-radius: 12px; max-width: 80%; align-self: flex-start; color: #f87171;">Connection error! Please try again.</div>`;
        container.scrollTop = container.scrollHeight;
    });
}

function escapeHtml(text) {
    return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}
</script>