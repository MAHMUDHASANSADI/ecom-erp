{{-- AI Shopping Assistant Floating Widget --}}
<div id="ai-chat-container">
    {{-- Floating Toggle Launcher Button --}}
    <button id="ai-chat-toggle-btn" 
            type="button" 
            class="ai-chat-btn shadow-lg" 
            aria-label="Open AI Shopping Assistant"
            title="Chat with AI Shopping Assistant">
        <div class="ai-chat-btn-inner">
            <i class="fas fa-robot ai-open-icon"></i>
            <i class="fas fa-times ai-close-icon d-none"></i>
        </div>
        <span class="ai-status-pulse"></span>
    </button>

    {{-- Chat Window Modal / Drawer --}}
    <div id="ai-chat-box" class="ai-chat-card shadow-lg d-none">
        {{-- Header --}}
        <div class="ai-chat-header d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="ai-avatar-wrapper mr-2">
                    <div class="ai-avatar">
                        <i class="fas fa-robot"></i>
                    </div>
                    <span class="ai-online-dot"></span>
                </div>
                <div>
                    <h6 class="mb-0 font-weight-bold text-white ai-header-title">Store AI Assistant</h6>
                    <small class="text-white-50 ai-header-subtitle">Instant catalog & order support</small>
                </div>
            </div>
            <div class="ai-header-actions d-flex align-items-center">
                <button type="button" id="ai-clear-chat-btn" class="ai-icon-btn mr-1" title="Clear conversation" aria-label="Clear conversation">
                    <i class="fas fa-rotate-right"></i>
                </button>
                <button type="button" id="ai-close-box-btn" class="ai-icon-btn" title="Close chat" aria-label="Close chat">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        {{-- Messages Body --}}
        <div id="ai-chat-body" class="ai-chat-body">
            {{-- Welcome message --}}
            <div class="ai-msg-row ai-msg-assistant">
                <div class="ai-bubble">
                    👋 <strong>Hello!</strong> I am your personal shopping assistant.
                    <br><br>
                    How can I help you today? You can search our products, check prices, track orders, or ask about shipping!
                </div>
            </div>

            {{-- Quick Chips --}}
            <div class="ai-quick-chips my-2">
                <button type="button" class="ai-chip" data-query="Show me popular products">🔍 Popular Products</button>
                <button type="button" class="ai-chip" data-query="Track my order">📦 Track Order</button>
                <button type="button" class="ai-chip" data-query="What payment methods are supported?">💳 Payment Options</button>
                <button type="button" class="ai-chip" data-query="How long does delivery take?">🚚 Delivery Info</button>
            </div>
        </div>

        {{-- Footer / Input Form --}}
        <div class="ai-chat-footer">
            <form id="ai-chat-form" class="ai-form d-flex align-items-center mb-0">
                <input type="text" 
                       id="ai-chat-input" 
                       class="form-control ai-input" 
                       placeholder="Type your question..." 
                       maxlength="500" 
                       required 
                       autocomplete="off">
                <button type="submit" id="ai-chat-send-btn" class="btn btn-primary ai-send-btn" aria-label="Send message">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
            <div class="text-center mt-1">
                <small class="text-muted" style="font-size: 0.68rem;">Powered by AI &bull; Instant answers</small>
            </div>
        </div>
    </div>
</div>

<style>
/* AI Chatbot Styles */
#ai-chat-container {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 9999;
    font-family: inherit;
}

/* Floating Launcher Button */
.ai-chat-btn {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3182ce 0%, #2b6cb0 100%);
    border: none;
    color: #ffffff;
    cursor: pointer;
    position: relative;
    outline: none !important;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.ai-chat-btn:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 12px 28px rgba(49, 130, 206, 0.45) !important;
}

.ai-chat-btn-inner {
    font-size: 1.45rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ai-status-pulse {
    position: absolute;
    top: 2px;
    right: 2px;
    width: 14px;
    height: 14px;
    background-color: #48bb78;
    border: 2px solid #ffffff;
    border-radius: 50%;
    animation: ai-pulse 2s infinite;
}

@keyframes ai-pulse {
    0% { box-shadow: 0 0 0 0 rgba(72, 187, 120, 0.7); }
    70% { box-shadow: 0 0 0 8px rgba(72, 187, 120, 0); }
    100% { box-shadow: 0 0 0 0 rgba(72, 187, 120, 0); }
}

/* Chat Card */
.ai-chat-card {
    position: fixed;
    bottom: 96px;
    right: 24px;
    width: 380px;
    max-width: calc(100vw - 32px);
    height: 520px;
    max-height: calc(100vh - 120px);
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 20px 40px -10px rgba(0,0,0,0.18), 0 0 1px 1px rgba(0,0,0,0.05) !important;
}

/* Header */
.ai-chat-header {
    background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);
    padding: 14px 16px;
    border-bottom: 2px solid #3182ce;
}

.ai-avatar-wrapper {
    position: relative;
}

.ai-avatar {
    width: 36px;
    height: 36px;
    background: #3182ce;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 1.05rem;
}

.ai-online-dot {
    position: absolute;
    bottom: -1px;
    right: -1px;
    width: 10px;
    height: 10px;
    background-color: #48bb78;
    border: 2px solid #1a202c;
    border-radius: 50%;
}

.ai-header-title {
    font-size: 0.96rem;
    line-height: 1.2;
}

.ai-header-subtitle {
    font-size: 0.72rem;
}

.ai-icon-btn {
    background: rgba(255, 255, 255, 0.12);
    border: none;
    color: #edf2f7;
    width: 30px;
    height: 30px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.82rem;
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
}

.ai-icon-btn:hover {
    background: rgba(255, 255, 255, 0.25);
    color: #ffffff;
}

/* Chat Body */
.ai-chat-body {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    background-color: #f7fafc;
    scroll-behavior: smooth;
}

.ai-msg-row {
    margin-bottom: 14px;
    display: flex;
    flex-direction: column;
}

.ai-msg-assistant {
    align-items: flex-start;
}

.ai-msg-user {
    align-items: flex-end;
}

.ai-bubble {
    max-width: 86%;
    padding: 11px 15px;
    border-radius: 14px;
    font-size: 0.88rem;
    line-height: 1.45;
    word-break: break-word;
}

.ai-msg-assistant .ai-bubble {
    background-color: #ffffff;
    color: #2d3748;
    border: 1px solid #e2e8f0;
    border-bottom-left-radius: 4px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
}

.ai-msg-user .ai-bubble {
    background: linear-gradient(135deg, #3182ce 0%, #2b6cb0 100%);
    color: #ffffff;
    border-bottom-right-radius: 4px;
}

.ai-bubble a {
    color: #2b6cb0;
    text-decoration: underline;
    font-weight: 600;
}

.ai-msg-user .ai-bubble a {
    color: #edf2f7;
}

.ai-bubble ul {
    margin: 6px 0 0 0;
    padding-left: 18px;
}

.ai-bubble li {
    margin-bottom: 4px;
}

/* Quick Chips */
.ai-quick-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.ai-chip {
    background: #edf2f7;
    border: 1px solid #cbd5e0;
    color: #4a5568;
    padding: 5px 10px;
    border-radius: 12px;
    font-size: 0.76rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    text-align: left;
}

.ai-chip:hover {
    background: #3182ce;
    border-color: #3182ce;
    color: #ffffff;
    transform: translateY(-1px);
}

/* Typing Indicator Animation */
.ai-typing-indicator {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 8px 14px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    border-bottom-left-radius: 4px;
}

.ai-typing-dot {
    width: 6px;
    height: 6px;
    background: #a0aec0;
    border-radius: 50%;
    animation: ai-dot-bounce 1.4s infinite ease-in-out both;
}

.ai-typing-dot:nth-child(1) { animation-delay: -0.32s; }
.ai-typing-dot:nth-child(2) { animation-delay: -0.16s; }

@keyframes ai-dot-bounce {
    0%, 80%, 100% { transform: scale(0); opacity: 0.4; }
    40% { transform: scale(1); opacity: 1; }
}

/* Footer & Input */
.ai-chat-footer {
    padding: 10px 14px 8px;
    background: #ffffff;
    border-top: 1px solid #edf2f7;
}

.ai-input {
    border-radius: 20px !important;
    border: 1px solid #e2e8f0;
    padding: 8px 16px;
    font-size: 0.88rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.ai-input:focus {
    border-color: #3182ce;
    box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.15) !important;
}

.ai-send-btn {
    border-radius: 50% !important;
    width: 38px;
    height: 38px;
    margin-left: 8px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background-color: #3182ce;
    border: none;
    flex-shrink: 0;
    transition: background 0.2s, transform 0.15s;
}

.ai-send-btn:hover {
    background-color: #2b6cb0;
    transform: scale(1.05);
}

@media (max-width: 480px) {
    .ai-chat-card {
        right: 12px;
        bottom: 86px;
        width: calc(100vw - 24px);
        height: calc(100vh - 110px);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('ai-chat-toggle-btn');
    const chatBox = document.getElementById('ai-chat-box');
    const closeBtn = document.getElementById('ai-close-box-btn');
    const clearBtn = document.getElementById('ai-clear-chat-btn');
    const chatForm = document.getElementById('ai-chat-form');
    const chatInput = document.getElementById('ai-chat-input');
    const chatBody = document.getElementById('ai-chat-body');
    const openIcon = toggleBtn.querySelector('.ai-open-icon');
    const closeIcon = toggleBtn.querySelector('.ai-close-icon');

    // Toggle Chat Window
    function toggleChat(show) {
        const isHidden = chatBox.classList.contains('d-none');
        const shouldShow = show !== undefined ? show : isHidden;

        if (shouldShow) {
            chatBox.classList.remove('d-none');
            openIcon.classList.add('d-none');
            closeIcon.classList.remove('d-none');
            setTimeout(() => chatInput.focus(), 150);
            scrollToBottom();
        } else {
            chatBox.classList.add('d-none');
            openIcon.classList.remove('d-none');
            closeIcon.classList.add('d-none');
        }
    }

    toggleBtn.addEventListener('click', () => toggleChat());
    closeBtn.addEventListener('click', () => toggleChat(false));

    // Scroll chat to bottom
    function scrollToBottom() {
        chatBody.scrollTop = chatBody.scrollHeight;
    }

    // Markdown Formatter helper for links, bold, bullet points
    function formatMessageText(text) {
        if (!text) return '';

        // Escape HTML
        let escaped = text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;");

        // Format Markdown links: [Title](url)
        escaped = escaped.replace(/\[([^\]]+)\]\((https?:\/\/[^\s\)]+|\/[^\s\)]+)\)/g, function (match, title, url) {
            return `<a href="${url}" target="_blank" rel="noopener noreferrer">${title} <i class="fas fa-external-link-alt small"></i></a>`;
        });

        // Format bold: **bold**
        escaped = escaped.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');

        // Format bullet lines starting with "- " or "* "
        escaped = escaped.replace(/(?:^|\n)[-*]\s+(.+)/g, '<br>&bull; $1');

        // New lines to br
        escaped = escaped.replace(/\n/g, '<br>');

        return escaped;
    }

    // Append Message to Body
    function appendMessage(role, rawContent) {
        const row = document.createElement('div');
        row.className = `ai-msg-row ai-msg-${role}`;

        const bubble = document.createElement('div');
        bubble.className = 'ai-bubble';
        bubble.innerHTML = formatMessageText(rawContent);

        row.appendChild(bubble);
        chatBody.appendChild(row);
        scrollToBottom();
    }

    // Show Typing Indicator
    function showTypingIndicator() {
        const typingRow = document.createElement('div');
        typingRow.className = 'ai-msg-row ai-msg-assistant';
        typingRow.id = 'ai-typing-indicator-row';

        typingRow.innerHTML = `
            <div class="ai-typing-indicator">
                <span class="ai-typing-dot"></span>
                <span class="ai-typing-dot"></span>
                <span class="ai-typing-dot"></span>
            </div>
        `;
        chatBody.appendChild(typingRow);
        scrollToBottom();
    }

    // Remove Typing Indicator
    function hideTypingIndicator() {
        const el = document.getElementById('ai-typing-indicator-row');
        if (el) {
            el.remove();
        }
    }

    // Send Message Handler
    function sendMessage(text) {
        const message = text || chatInput.value.trim();
        if (!message) return;

        appendMessage('user', message);
        chatInput.value = '';
        chatInput.disabled = true;

        showTypingIndicator();

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        fetch("{{ route('storefront.chat.message') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ message: message })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network error');
            }
            return response.json();
        })
        .then(data => {
            hideTypingIndicator();
            chatInput.disabled = false;
            chatInput.focus();

            const reply = data.reply || "I'm here to help! How else may I assist you?";
            appendMessage('assistant', reply);
        })
        .catch(err => {
            hideTypingIndicator();
            chatInput.disabled = false;
            appendMessage('assistant', "⚠️ Sorry, I'm currently unable to connect. Please try again in a moment.");
        });
    }

    // Handle Form Submit
    chatForm.addEventListener('submit', function (e) {
        e.preventDefault();
        sendMessage();
    });

    // Handle Quick Chips
    chatBody.addEventListener('click', function (e) {
        const chip = e.target.closest('.ai-chip');
        if (chip) {
            const query = chip.getAttribute('data-query');
            if (query) {
                sendMessage(query);
            }
        }
    });

    // Handle Clear Chat
    clearBtn.addEventListener('click', function () {
        if (!confirm('Reset current chat conversation?')) return;

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        fetch("{{ route('storefront.chat.clear') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            }
        }).then(() => {
            chatBody.innerHTML = `
                <div class="ai-msg-row ai-msg-assistant">
                    <div class="ai-bubble">
                        🔄 <strong>Conversation reset!</strong> How can I help you find products or track orders?
                    </div>
                </div>
                <div class="ai-quick-chips my-2">
                    <button type="button" class="ai-chip" data-query="Show me popular products">🔍 Popular Products</button>
                    <button type="button" class="ai-chip" data-query="Track my order">📦 Track Order</button>
                    <button type="button" class="ai-chip" data-query="What payment methods are supported?">💳 Payment Options</button>
                    <button type="button" class="ai-chip" data-query="How long does delivery take?">🚚 Delivery Info</button>
                </div>
            `;
        });
    });
});
</script>
