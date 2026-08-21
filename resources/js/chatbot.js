/**
 * Sibiri Innovation — AI Chatbot widget.
 * A floating launcher + panel that talks to POST /chatbot/reply.
 * The bot is server-side restricted to answer only from site content
 * (see App\Services\ChatbotService) — this file is purely the UI/transport.
 */

(function () {
    'use strict';

    function uuid() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
            const r = (Math.random() * 16) | 0;
            const v = c === 'x' ? r : (r & 0x3) | 0x8;
            return v.toString(16);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const root = document.getElementById('sibiri-chatbot');
        if (!root) return;

        const launcher = root.querySelector('[data-chat-launcher]');
        const panel = root.querySelector('[data-chat-panel]');
        const closeBtn = root.querySelector('[data-chat-close]');
        const messagesEl = root.querySelector('[data-chat-messages]');
        const form = root.querySelector('[data-chat-form]');
        const input = root.querySelector('[data-chat-input]');
        const endpoint = root.dataset.endpoint;
        const csrfToken = root.dataset.csrf;

        let sessionId = sessionStorage.getItem('sibiri-chat-session');
        if (!sessionId) {
            sessionId = uuid();
            sessionStorage.setItem('sibiri-chat-session', sessionId);
        }

        function appendMessage(text, from) {
            const bubble = document.createElement('div');
            bubble.className = 'chat-bubble chat-bubble-' + from;
            bubble.textContent = text;
            messagesEl.appendChild(bubble);
            messagesEl.scrollTop = messagesEl.scrollHeight;
        }

        launcher.addEventListener('click', () => {
            panel.classList.toggle('open');
            if (panel.classList.contains('open') && !messagesEl.dataset.greeted) {
                appendMessage("Hi! I'm the Sibiri Innovation assistant. Ask me about our services, products, or how to get in touch.", 'bot');
                messagesEl.dataset.greeted = '1';
            }
        });
        if (closeBtn) closeBtn.addEventListener('click', () => panel.classList.remove('open'));

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            const message = input.value.trim();
            if (!message) return;
            appendMessage(message, 'user');
            input.value = '';
            input.disabled = true;

            const typing = document.createElement('div');
            typing.className = 'chat-bubble chat-bubble-bot chat-typing';
            typing.textContent = 'Typing…';
            messagesEl.appendChild(typing);
            messagesEl.scrollTop = messagesEl.scrollHeight;

            try {
                const res = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        Accept: 'application/json',
                    },
                    body: JSON.stringify({ message, session_id: sessionId }),
                });
                const data = await res.json();
                typing.remove();
                appendMessage(data.reply || "Sorry, I couldn't process that.", 'bot');
            } catch (err) {
                typing.remove();
                appendMessage('Something went wrong. Please try again or use the Contact page.', 'bot');
            } finally {
                input.disabled = false;
                input.focus();
            }
        });
    });
})();
