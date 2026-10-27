{{-- Admin ERP AI Copilot Floating Widget --}}
<div id="admin-ai-container">
    {{-- Floating Launcher Button --}}
    <button id="admin-ai-toggle-btn" 
            type="button" 
            class="admin-ai-btn shadow-lg" 
            aria-label="Open ERP AI Copilot"
            title="Open ERP AI Copilot">
        <div class="admin-ai-btn-inner">
            <i class="fas fa-brain admin-ai-open-icon"></i>
            <i class="fas fa-times admin-ai-close-icon d-none"></i>
        </div>
        <span class="admin-ai-badge">AI</span>
    </button>

    {{-- Chat Drawer Window --}}
    <div id="admin-ai-box" class="admin-ai-card shadow-lg d-none">
        {{-- Header --}}
        <div class="admin-ai-header d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="admin-ai-avatar-wrapper mr-2">
                    <div class="admin-ai-avatar">
                        <i class="fas fa-brain"></i>
                    </div>
                    <span class="admin-ai-online-dot"></span>
                </div>
                <div>
                    <h6 class="mb-0 font-weight-bold text-white admin-ai-title">ERP Operations Copilot</h6>
                    <small class="text-white-50 admin-ai-subtitle">Real-time business & inventory intelligence</small>
                </div>
            </div>
            <div class="admin-ai-header-actions d-flex align-items-center">
                <button type="button" id="admin-ai-clear-btn" class="admin-ai-icon-btn mr-1" title="Clear conversation" aria-label="Clear conversation">
                    <i class="fas fa-rotate-right"></i>
                </button>
                <button type="button" id="admin-ai-close-btn" class="admin-ai-icon-btn" title="Close copilot" aria-label="Close copilot">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        {{-- Messages Body --}}
        <div id="admin-ai-body" class="admin-ai-body">
            <div class="admin-ai-msg-row admin-ai-msg-assistant">
                <div class="admin-ai-bubble">
                    👋 <strong>Hello, {{ auth()->user()->name ?? 'Admin' }}!</strong>
                    <br><br>
                    I am your <strong>ERP Operations Copilot</strong>. I have real-time access to your store's sales, stock levels, orders, and financial data.
                    <br><br>
                    What would you like to review?
                </div>
            </div>

            {{-- Operational Quick Prompt Chips --}}
            <div class="admin-ai-chips my-2">
                <button type="button" class="admin-ai-chip" data-query="Give me today's sales summary">📊 Today's Sales</button>
                <button type="button" class="admin-ai-chip" data-query="Which products are low on stock?">⚠️ Low Stock Alert</button>
                <button type="button" class="admin-ai-chip" data-query="Show me pending orders that need fulfillment">📦 Pending Orders</button>
                <button type="button" class="admin-ai-chip" data-query="What is our total expense this month?">💰 Monthly Expenses</button>
                <button type="button" class="admin-ai-chip" data-query="What is the POS in-store vs online sales ratio?">🛒 POS vs Online</button>
            </div>
        </div>

        {{-- Footer / Prompt Input --}}
        <div class="admin-ai-footer">
            <form id="admin-ai-form" class="d-flex align-items-center mb-0">
                <input type="text" 
                       id="admin-ai-input" 
                       class="form-control admin-ai-input" 
                       placeholder="Ask about sales, low stock, orders, expenses..." 
                       maxlength="1000" 
                       required 
                       autocomplete="off">
                <button type="submit" id="admin-ai-send-btn" class="btn btn-primary admin-ai-send-btn" aria-label="Send query">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
            <div class="text-center mt-1">
                <small class="text-muted" style="font-size: 0.68rem;">ERP Copilot &bull; Connected to Live Database</small>
            </div>
        </div>
    </div>
</div>

<style>
/* Admin AI Copilot Styles */
#admin-ai-container {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 1040;
    font-family: inherit;
}

/* Launcher Button */
.admin-ai-btn {
    width: 58px;
    height: 58px;
    border-radius: 50%;
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    border: 2px solid rgba(255, 255, 255, 0.2);
    color: #ffffff;
    cursor: pointer;
    position: relative;
    outline: none !important;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.admin-ai-btn:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 12px 28px rgba(30, 60, 114, 0.45) !important;
}

.admin-ai-btn-inner {
    font-size: 1.45rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.admin-ai-badge {
    position: absolute;
    top: -2px;
    right: -2px;
    background: #e53e3e;
    color: #ffffff;
    font-size: 0.62rem;
    font-weight: 800;
    padding: 2px 5px;
    border-radius: 8px;
    letter-spacing: 0.5px;
    border: 2px solid #ffffff;
}

/* Chat Card Window */
.admin-ai-card {
    position: fixed;
    bottom: 92px;
    right: 24px;
    width: 420px;
    max-width: calc(100vw - 32px);
    height: 560px;
    max-height: calc(100vh - 120px);
    background: #ffffff;
    border-radius: 14px;
    border: 1px solid #d2d6de;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 20px 45px -10px rgba(0,0,0,0.22), 0 0 1px 1px rgba(0,0,0,0.06) !important;
}

/* Header */
.admin-ai-header {
    background: linear-gradient(135deg, #1f2d3d 0%, #111a24 100%);
    padding: 13px 16px;
    border-bottom: 3px solid #007bff;
}

.admin-ai-avatar-wrapper {
    position: relative;
}

.admin-ai-avatar {
    width: 36px;
    height: 36px;
    background: #007bff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 1.05rem;
}

.admin-ai-online-dot {
    position: absolute;
    bottom: -1px;
    right: -1px;
    width: 10px;
    height: 10px;
    background-color: #28a745;
    border: 2px solid #1f2d3d;
    border-radius: 50%;
}

.admin-ai-title {
    font-size: 0.96rem;
    line-height: 1.2;
}

.admin-ai-subtitle {
    font-size: 0.72rem;
}

.admin-ai-icon-btn {
    background: rgba(255, 255, 255, 0.12);
    border: none;
    color: #edf2f7;
    width: 30px;
    height: 30px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.82rem;
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
}

.admin-ai-icon-btn:hover {
    background: rgba(255, 255, 255, 0.28);
    color: #ffffff;
}

/* Body */
.admin-ai-body {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    background-color: #f4f6f9;
    scroll-behavior: smooth;
}

.admin-ai-msg-row {
    margin-bottom: 14px;
    display: flex;
    flex-direction: column;
}

.admin-ai-msg-assistant {
    align-items: flex-start;
}

.admin-ai-msg-user {
    align-items: flex-end;
}

.admin-ai-bubble {
    max-width: 88%;
    padding: 11px 15px;
    border-radius: 12px;
    font-size: 0.88rem;
    line-height: 1.48;
    word-break: break-word;
}

.admin-ai-msg-assistant .admin-ai-bubble {
    background-color: #ffffff;
    color: #212529;
    border: 1px solid #e2e8f0;
    border-bottom-left-radius: 3px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
}

.admin-ai-msg-user .admin-ai-bubble {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    color: #ffffff;
    border-bottom-right-radius: 3px;
}

.admin-ai-bubble a {
    color: #007bff;
    font-weight: 600;
    text-decoration: underline;
}

.admin-ai-bubble code {
    background-color: #edf2f7;
    color: #d63384;
    padding: 2px 5px;
    border-radius: 4px;
    font-size: 0.82rem;
}

.admin-ai-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.admin-ai-chip {
    background: #ffffff;
    border: 1px solid #ced4da;
    color: #495057;
    padding: 5px 10px;
    border-radius: 12px;
    font-size: 0.76rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    text-align: left;
}

.admin-ai-chip:hover {
    background: #007bff;
    border-color: #007bff;
    color: #ffffff;
    transform: translateY(-1px);
}

/* Typing Indicator */
.admin-ai-typing {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 8px 14px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    border-bottom-left-radius: 3px;
}

.admin-ai-dot {
    width: 6px;
    height: 6px;
    background: #6c757d;
    border-radius: 50%;
    animation: admin-ai-bounce 1.4s infinite ease-in-out both;
}

.admin-ai-dot:nth-child(1) { animation-delay: -0.32s; }
.admin-ai-dot:nth-child(2) { animation-delay: -0.16s; }

@keyframes admin-ai-bounce {
    0%, 80%, 100% { transform: scale(0); opacity: 0.4; }
    40% { transform: scale(1); opacity: 1; }
}

/* Footer */
.admin-ai-footer {
    padding: 10px 14px 8px;
    background: #ffffff;
    border-top: 1px solid #dee2e6;
}

.admin-ai-input {
    border-radius: 20px !important;
    border: 1px solid #ced4da;
    padding: 8px 16px;
    font-size: 0.88rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.admin-ai-input:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.15) !important;
}

.admin-ai-send-btn {
    border-radius: 50% !important;
    width: 38px;
    height: 38px;
    margin-left: 8px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background-color: #007bff;
    border: none;
    flex-shrink: 0;
    transition: background 0.2s, transform 0.15s;
}

.admin-ai-send-btn:hover {
    background-color: #0056b3;
    transform: scale(1.05);
}

@media (max-width: 480px) {
    .admin-ai-card {
        right: 10px;
        bottom: 84px;
        width: calc(100vw - 20px);
        height: calc(100vh - 100px);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('admin-ai-toggle-btn');
    const navBtn = document.getElementById('admin-ai-nav-btn');
    const chatBox = document.getElementById('admin-ai-box');
    const closeBtn = document.getElementById('admin-ai-close-btn');
    const clearBtn = document.getElementById('admin-ai-clear-btn');
    const chatForm = document.getElementById('admin-ai-form');
    const chatInput = document.getElementById('admin-ai-input');
    const chatBody = document.getElementById('admin-ai-body');
    const openIcon = toggleBtn.querySelector('.admin-ai-open-icon');
    const closeIcon = toggleBtn.querySelector('.admin-ai-close-icon');

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
    if (navBtn) {
        navBtn.addEventListener('click', (e) => {
            e.preventDefault();
            toggleChat(true);
        });
    }
    closeBtn.addEventListener('click', () => toggleChat(false));

    function scrollToBottom() {
        chatBody.scrollTop = chatBody.scrollHeight;
    }

    function formatMessageText(text) {
        if (!text) return '';

        let escaped = text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;");

        // Format markdown links: [Title](url)
        escaped = escaped.replace(/\[([^\]]+)\]\((https?:\/\/[^\s\)]+|\/[^\s\)]+)\)/g, function (match, title, url) {
            return `<a href="${url}" class="font-weight-bold">${title} <i class="fas fa-external-link-alt small"></i></a>`;
        });

        // Format bold: **bold**
        escaped = escaped.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');

        // Format inline code: `code`
        escaped = escaped.replace(/`([^`]+)`/g, '<code>$1</code>');

        // Format bullet lines
        escaped = escaped.replace(/(?:^|\n)[-*]\s+(.+)/g, '<br>&bull; $1');

        // Newlines
        escaped = escaped.replace(/\n/g, '<br>');

        return escaped;
    }

    function appendMessage(role, rawContent) {
        const row = document.createElement('div');
        row.className = `admin-ai-msg-row admin-ai-msg-${role}`;

        const bubble = document.createElement('div');
        bubble.className = 'admin-ai-bubble';
        bubble.innerHTML = formatMessageText(rawContent);

        row.appendChild(bubble);
        chatBody.appendChild(row);
        scrollToBottom();
    }

    function showTyping() {
        const row = document.createElement('div');
        row.className = 'admin-ai-msg-row admin-ai-msg-assistant';
        row.id = 'admin-ai-typing-indicator';
        row.innerHTML = `
            <div class="admin-ai-typing">
                <span class="admin-ai-dot"></span>
                <span class="admin-ai-dot"></span>
                <span class="admin-ai-dot"></span>
            </div>
        `;
        chatBody.appendChild(row);
        scrollToBottom();
    }

    function hideTyping() {
        const el = document.getElementById('admin-ai-typing-indicator');
        if (el) el.remove();
    }

    function sendMessage(text) {
        const message = text || chatInput.value.trim();
        if (!message) return;

        appendMessage('user', message);
        chatInput.value = '';
        chatInput.disabled = true;
        showTyping();

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        fetch("{{ route('admin.ai.chat') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ message: message })
        })
        .then(response => {
            if (!response.ok) throw new Error('API request failed');
            return response.json();
        })
        .then(data => {
            hideTyping();
            chatInput.disabled = false;
            chatInput.focus();
            const reply = data.reply || 'Operational summary complete.';
            appendMessage('assistant', reply);
        })
        .catch(() => {
            hideTyping();
            chatInput.disabled = false;
            appendMessage('assistant', '⚠️ Unable to connect to ERP AI service. Please check server logs.');
        });
    }

    chatForm.addEventListener('submit', function (e) {
        e.preventDefault();
        sendMessage();
    });

    chatBody.addEventListener('click', function (e) {
        const chip = e.target.closest('.admin-ai-chip');
        if (chip) {
            const query = chip.getAttribute('data-query');
            if (query) sendMessage(query);
        }
    });

    clearBtn.addEventListener('click', function () {
        if (!confirm('Clear admin conversation history?')) return;

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        fetch("{{ route('admin.ai.clear') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            }
        }).then(() => {
            chatBody.innerHTML = `
                <div class="admin-ai-msg-row admin-ai-msg-assistant">
                    <div class="admin-ai-bubble">
                        🔄 <strong>Conversation reset!</strong> Ready for your next operational query.
                    </div>
                </div>
                <div class="admin-ai-chips my-2">
                    <button type="button" class="admin-ai-chip" data-query="Give me today's sales summary">📊 Today's Sales</button>
                    <button type="button" class="admin-ai-chip" data-query="Which products are low on stock?">⚠️ Low Stock Alert</button>
                    <button type="button" class="admin-ai-chip" data-query="Show me pending orders that need fulfillment">📦 Pending Orders</button>
                    <button type="button" class="admin-ai-chip" data-query="What is our total expense this month?">💰 Monthly Expenses</button>
                </div>
            `;
        });
    });
});
</script>
