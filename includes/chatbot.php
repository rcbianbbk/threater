<!-- Floating Cinema Chatbot Widget -->
<div id="cinemaChatWidget" style="position: fixed; bottom: 25px; right: 25px; z-index: 9999; font-family: inherit;">
    <!-- Chat Toggle Button -->
    <button id="chatToggleBtn" onclick="toggleChatWindow()" style="width: 55px; height: 55px; border-radius: 50%; background: var(--accent, #6366f1); border: none; color: #fff; font-size: 22px; cursor: pointer; box-shadow: 0 4px 20px rgba(99,102,241,0.4); display: flex; align-items: center; justify-content: center; transition: transform 0.3s;">
        <i class="fa-solid fa-comments" id="chatIcon"></i>
    </button>

    <!-- Chat Window (Hidden by default) -->
    <div id="chatWindow" style="display: none; width: 340px; height: 460px; background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.15); border-radius: 16px; flex-direction: column; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.5); position: absolute; bottom: 70px; right: 0;">
        
        <!-- Chat Header -->
        <div style="padding: 15px; background: rgba(99,102,241,0.2); border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 32px; height: 32px; background: var(--accent); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 14px;">
                    <i class="fa-solid fa-robot"></i>
                </div>
                <div>
                    <h4 style="font-size: 14px; color: #fff; margin: 0;">CineBot</h4>
                    <span style="font-size: 10px; color: #34d399;"><i class="fa-solid fa-circle" style="font-size: 8px;"></i> Online</span>
                </div>
            </div>
            <button onclick="toggleChatWindow()" style="background: transparent; border: none; color: #fff; font-size: 16px; cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <!-- Chat Messages Area -->
In        <div id="chatMessages" style="flex: 1; padding: 15px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; font-size: 13px; color: #fff;">
            <div style="background: rgba(255,255,255,0.08); padding: 10px 14px; border-radius: 12px; max-width: 80%; align-self: flex-start;">
                नमस्ते! 👋 म CineBot हुँ। सिनेमा वर्ल्डमा तपाईंलाई कसरी सहयोग गर्न सक्छु?
            </div>
        </div>

        <!-- Quick Suggestion Chips -->
        <div style="padding: 8px 12px; display: flex; gap: 6px; overflow-x: auto; background: rgba(0,0,0,0.2); border-top: 1px solid rgba(255,255,255,0.05);">
            <button onclick="sendQuickReply('Now Showing movies')" style="background: rgba(255,255,255,0.1); border: none; color: #fff; padding: 4px 10px; border-radius: 12px; font-size: 11px; cursor: pointer; white-space: nowrap;">🎬 Now Showing</button>
            <button onclick="sendQuickReply('Ticket prices')" style="background: rgba(255,255,255,0.1); border: none; color: #fff; padding: 4px 10px; border-radius: 12px; font-size: 11px; cursor: pointer; white-space: nowrap;">🎫 Prices</button>
            <button onclick="sendQuickReply('Location')" style="background: rgba(255,255,255,0.1); border: none; color: #fff; padding: 4px 10px; border-radius: 12px; font-size: 11px; cursor: pointer; white-space: nowrap;">📍 Location</button>
        </div>

        <!-- Chat Input Form -->
        <div style="padding: 10px; background: rgba(0,0,0,0.3); border-top: 1px solid rgba(255,255,255,0.1); display: flex; gap: 8px;">
            <input type="text" id="chatInput" placeholder="Type a message..." style="flex: 1; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); border-radius: 20px; padding: 8px 14px; color: #fff; font-size: 13px; outline: none;" onkeypress="handleChatKeyPress(event)">
            <button onclick="sendUserMessage()" style="background: var(--accent); border: none; width: 36px; height: 36px; border-radius: 50%; color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-paper-plane" style="font-size: 12px;"></i></button>
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
        icon.className = 'fa-solid fa-comments';
    }
}

function handleChatKeyPress(e) {
    if (e.key === 'Enter') {
        sendUserMessage();
    }
}

function sendQuickReply(text) {
    document.getElementById('chatInput').value = text;
    sendUserMessage();
}

function sendUserMessage() {
    let input = document.getElementById('chatInput');
    let msg = input.value.trim();
    if (!msg) return;

    let container = document.getElementById('chatMessages');

    // Append user message
    container.innerHTML += `<div style="background: var(--accent); padding: 10px 14px; border-radius: 12px; max-width: 80%; align-self: flex-end;">${escapeHtml(msg)}</div>`;
    input.value = '';
    container.scrollTop = container.scrollHeight;

    // Simulate Bot Response after a small delay
    setTimeout(() => {
        let botReply = getBotResponse(msg);
        container.innerHTML += `<div style="background: rgba(255,255,255,0.08); padding: 10px 14px; border-radius: 12px; max-width: 80%; align-self: flex-start;">${botReply}</div>`;
        container.scrollTop = container.scrollHeight;
    }, 600);
}

function getBotResponse(input) {
    let text = input.toLowerCase();
    if (text.includes('now showing') || text.includes('movie') || text.includes('film')) {
        return "हामीसँग अहिले थुप्रै नयाँ ब्लकबस्टर मुभीहरू उपलब्ध छन्! गृहपृष्ठ (Home) मा गएर 'Now Showing' सेक्सन चेक गर्नुहोस् र आफ्नो मनपर्ने मुभी बुक गर्नुहोस्।";
    } else if (text.includes('price') || text.includes('ticket') || text.includes('cost')) {
        return "टिकटको मूल्य मुभी र सिटको प्रकार (Normal/VIP Recliner) अनुसार फरक पर्छ (सामान्यतया रु. २५० देखि रु. ६०० सम्म)।";
    } else if (text.includes('location') || text.includes('where') || text.includes('address')) {
        return "हामी दरबारमार्ग, काठमाडौं, नेपालमा अवस्थित छौं! 📍";
    } else if (text.includes('hello') || text.includes('hi') || text.includes('namaste')) {
        return "नमस्ते! सिनेमा वर्ल्डमा तपाईंलाई स्वागत छ। आज कुन मुभी हेर्ने विचार छ?";
    } else {
        return "तपाईंको प्रश्नको लागि धन्यवाद! थप सहायताको लागि कृपया हाम्रो कस्टमर केयर नम्बर +977 01-4455667 मा सम्पर्क गर्नुहोस्।";
    }
}

function escapeHtml(text) {
    return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}
</script>