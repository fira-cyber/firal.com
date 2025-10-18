// Dashboard functionality
function showSection(sectionName) {
    // Hide all sections
    document.querySelectorAll('.content-section').forEach(section => {
        section.classList.remove('active');
    });
    
    // Show selected section
    document.getElementById(sectionName + '-section').classList.add('active');
    
    // Update menu active state
    document.querySelectorAll('.menu-item').forEach(item => {
        item.classList.remove('active');
    });
    event.currentTarget.classList.add('active');
}

// Modal functions
function openModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// New chat functionality
function createNewChat() {
    openModal('newChatModal');
    loadUsers();
}

function loadUsers() {
    // This would typically fetch from the server
    const users = [
        {id: 1, name: 'John Doe', username: 'john', online: true},
        {id: 2, name: 'Jane Smith', username: 'jane', online: false},
        {id: 3, name: 'Mike Johnson', username: 'mike', online: true}
    ];
    
    const resultsContainer = document.getElementById('search-results');
    resultsContainer.innerHTML = users.map(user => `
        <div class="user-result" onclick="startChat(${user.id})">
            <div class="user-avatar">${user.username.charAt(0).toUpperCase()}</div>
            <div class="user-details">
                <strong>${user.name}</strong>
                <span>@${user.username}</span>
            </div>
            ${user.online ? '<div class="online-indicator"></div>' : ''}
        </div>
    `).join('');
}

function startChat(userId) {
    // Create a new chat with the selected user
    fetch('api/create_chat.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({user_id: userId})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal('newChatModal');
            openChat(data.chat_id);
        }
    })
    .catch(error => console.error('Error:', error));
}

function openChat(chatId) {
    window.location.href = `chat.php?chat_id=${chatId}`;
}

function createBunaTime() {
    // Create a Buna Time chat room
    fetch('api/create_buna_time.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            openChat(data.chat_id);
        }
    });
}

function createGroup() {
    // Open group creation modal
    alert('Group creation feature coming soon!');
}

// Search functionality
document.getElementById('user-search')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    // Filter users based on search term
    const userResults = document.querySelectorAll('.user-result');
    
    userResults.forEach(user => {
        const userName = user.querySelector('strong').textContent.toLowerCase();
        const userUsername = user.querySelector('span').textContent.toLowerCase();
        
        if (userName.includes(searchTerm) || userUsername.includes(searchTerm)) {
            user.style.display = 'flex';
        } else {
            user.style.display = 'none';
        }
    });
});

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}

// Add CSS for search results
const style = document.createElement('style');
style.textContent = `
    .user-result {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: background 0.3s ease;
    }
    
    .user-result:hover {
        background: #f5f5f5;
    }
    
    .user-details {
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }
    
    .user-details span {
        color: #666;
        font-size: 0.9em;
    }
`;
document.head.appendChild(style);
// Chat functionality
function goBack() {
    window.history.back();
}

function toggleMembers() {
    const sidebar = document.getElementById('members-sidebar');
    sidebar.classList.toggle('active');
}

function startVoiceMessage() {
    alert('Voice message feature coming soon!');
}

function showChatSettings() {
    alert('Chat settings feature coming soon!');
}

function autoResize(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
}

function handleKeyPress(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendMessage();
    }
}

let isRecording = false;
let mediaRecorder = null;
let audioChunks = [];

function toggleVoiceRecorder() {
    const voiceRecorder = document.getElementById('voice-recorder');
    const messageInput = document.getElementById('message-input');
    const voiceToggle = document.getElementById('voice-toggle');
    
    if (!isRecording) {
        // Start recording
        startVoiceRecording();
        voiceRecorder.style.display = 'block';
        messageInput.style.display = 'none';
        voiceToggle.innerHTML = '⏹️';
        voiceToggle.style.color = '#c62828';
    } else {
        // Stop recording
        stopVoiceRecording();
        voiceRecorder.style.display = 'none';
        messageInput.style.display = 'block';
        voiceToggle.innerHTML = '🎤';
        voiceToggle.style.color = '';
    }
    
    isRecording = !isRecording;
}

async function startVoiceRecording() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        mediaRecorder = new MediaRecorder(stream);
        audioChunks = [];

        mediaRecorder.ondataavailable = (event) => {
            audioChunks.push(event.data);
        };

        mediaRecorder.onstop = () => {
            const audioBlob = new Blob(audioChunks, { type: 'audio/wav' });
            sendVoiceMessage(audioBlob);
            
            // Stop all tracks
            stream.getTracks().forEach(track => track.stop());
        };

        mediaRecorder.start();
    } catch (error) {
        console.error('Error starting voice recording:', error);
        alert('Could not access microphone. Please check permissions.');
    }
}

function stopVoiceRecording() {
    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
        mediaRecorder.stop();
    }
}

function sendVoiceMessage(audioBlob) {
    const formData = new FormData();
    formData.append('audio', audioBlob);
    formData.append('chat_id', CHAT_ID);
    formData.append('user_id', USER_ID);

    fetch('api/send_voice_message.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            addMessageToChat({
                id: data.message_id,
                sender_id: USER_ID,
                message_text: '',
                message_type: 'audio',
                file_path: data.file_path,
                sent_at: new Date().toISOString(),
                is_read: false,
                username: 'You',
                full_name: 'You'
            });
        }
    })
    .catch(error => console.error('Error sending voice message:', error));
}

function sendMessage() {
    const messageInput = document.getElementById('message-input');
    const messageText = messageInput.value.trim();

    if (!messageText) return;

    // Disable input temporarily
    messageInput.disabled = true;

    fetch('api/send_message.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            chat_id: CHAT_ID,
            user_id: USER_ID,
            message_text: messageText,
            message_type: 'text'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Add message to chat immediately
            addMessageToChat({
                id: data.message_id,
                sender_id: USER_ID,
                message_text: messageText,
                message_type: 'text',
                sent_at: new Date().toISOString(),
                is_read: false,
                username: 'You',
                full_name: 'You'
            });
            
            // Clear input
            messageInput.value = '';
            messageInput.style.height = 'auto';
            messageInput.disabled = false;
            messageInput.focus();
        }
    })
    .catch(error => {
        console.error('Error sending message:', error);
        messageInput.disabled = false;
    });
}

function addMessageToChat(message) {
    const messagesContainer = document.getElementById('messages-container');
    const isOwnMessage = message.sender_id == USER_ID;
    
    // Remove empty state if it exists
    const emptyState = messagesContainer.querySelector('.empty-chat');
    if (emptyState) {
        emptyState.remove();
    }
    
    const messageElement = document.createElement('div');
    messageElement.className = `message ${isOwnMessage ? 'own-message' : 'other-message'}`;
    messageElement.setAttribute('data-message-id', message.id);
    
    let messageContent = '';
    
    if (!isOwnMessage && document.querySelector('.chat-header h3').textContent !== message.full_name) {
        messageContent += `<div class="sender-name">${message.full_name}</div>`;
    }
    
    messageContent += `
        <div class="message-bubble">
    `;
    
    if (message.message_type === 'text') {
        messageContent += `<div class="message-text">${escapeHtml(message.message_text)}</div>`;
    } else if (message.message_type === 'audio') {
        messageContent += `
            <div class="message-audio">
                <audio controls>
                    <source src="${message.file_path}" type="audio/wav">
                    Your browser does not support the audio element.
                </audio>
            </div>
        `;
    }
    
    const time = new Date(message.sent_at).toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit',
        hour12: false 
    });
    
    messageContent += `
            <div class="message-time">
                ${time}
                ${isOwnMessage ? '<span class="read-status">✓</span>' : ''}
            </div>
        </div>
    `;
    
    messageElement.innerHTML = messageContent;
    messagesContainer.appendChild(messageElement);
    
    // Scroll to bottom
    scrollToBottom();
}

function scrollToBottom() {
    const messagesContainer = document.getElementById('messages-container');
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Buna Time countdown
function updateBunaCountdown() {
    const bunaTimer = document.querySelector('.buna-timer');
    if (!bunaTimer) return;
    
    const expiresAt = new Date(bunaTimer.dataset.expires).getTime();
    const now = new Date().getTime();
    const distance = expiresAt - now;
    
    if (distance < 0) {
        document.getElementById('buna-countdown').textContent = 'Expired!';
        return;
    }
    
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
    
    document.getElementById('buna-countdown').textContent = 
        `${hours}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
}

// Update countdown every second
setInterval(updateBunaCountdown, 1000);

// File attachment functions
function attachFile() {
    openModal('attachmentModal');
}

function uploadImage(file) {
    if (file) {
        // Simulate upload
        alert(`Image "${file.name}" would be uploaded here`);
        closeModal('attachmentModal');
    }
}

function uploadDocument(file) {
    if (file) {
        alert(`Document "${file.name}" would be uploaded here`);
        closeModal('attachmentModal');
    }
}

function uploadVideo(file) {
    if (file) {
        alert(`Video "${file.name}" would be uploaded here`);
        closeModal('attachmentModal');
    }
}

function showEmojiPicker() {
    alert('Emoji picker coming soon!');
}

function inviteToChat() {
    alert('Invite feature coming soon!');
}

// Scroll to bottom when page loads
document.addEventListener('DOMContentLoaded', function() {
    scrollToBottom();
    updateBunaCountdown();
});