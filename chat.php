<?php
include 'config/database.php';
include 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$chat_id = $_GET['chat_id'] ?? null;

if (!$chat_id) {
    header("Location: dashboard.php");
    exit();
}

// Get chat details
$stmt = $pdo->prepare("
    SELECT c.*, u.username as created_by_username 
    FROM chats c 
    LEFT JOIN users u ON c.created_by = u.id 
    WHERE c.id = ?
");
$stmt->execute([$chat_id]);
$chat = $stmt->fetch();

if (!$chat) {
    header("Location: dashboard.php");
    exit();
}

// Get chat members
$stmt = $pdo->prepare("
    SELECT u.id, u.username, u.full_name, u.profile_picture, u.is_online 
    FROM chat_members cm 
    JOIN users u ON cm.user_id = u.id 
    WHERE cm.chat_id = ?
");
$stmt->execute([$chat_id]);
$members = $stmt->fetchAll();

// Check if current user is a member of this chat
$is_member = false;
foreach ($members as $member) {
    if ($member['id'] == $user_id) {
        $is_member = true;
        break;
    }
}

if (!$is_member) {
    header("Location: dashboard.php");
    exit();
}

// Get messages for this chat
$stmt = $pdo->prepare("
    SELECT m.*, u.username, u.full_name, u.profile_picture 
    FROM messages m 
    JOIN users u ON m.sender_id = u.id 
    WHERE m.chat_id = ? 
    ORDER BY m.sent_at ASC
");
$stmt->execute([$chat_id]);
$messages = $stmt->fetchAll();

// Mark messages as read
$stmt = $pdo->prepare("UPDATE messages SET is_read = TRUE WHERE chat_id = ? AND sender_id != ?");
$stmt->execute([$chat_id, $user_id]);
?>

<?php include 'includes/header.php'; ?>

<div class="chat-container">
    <!-- Chat Header -->
    <div class="chat-header">
        <div class="chat-info">
            <button class="back-button" onclick="goBack()">←</button>
            <div class="chat-avatar">
                <?php 
                if ($chat['chat_type'] == 'private') {
                    echo '👤';
                } elseif ($chat['chat_type'] == 'group') {
                    echo '👪';
                } else {
                    echo '☕';
                }
                ?>
            </div>
            <div class="chat-details">
                <h3>
                    <?php 
                    if ($chat['chat_type'] == 'private') {
                        // Show other user's name for private chats
                        foreach ($members as $member) {
                            if ($member['id'] != $user_id) {
                                echo htmlspecialchars($member['full_name']);
                                break;
                            }
                        }
                    } else {
                        echo htmlspecialchars($chat['chat_name'] ?: 'Group Chat');
                    }
                    ?>
                </h3>
                <div class="chat-status">
                    <?php if ($chat['chat_type'] == 'private'): ?>
                        <?php 
                        $other_user = null;
                        foreach ($members as $member) {
                            if ($member['id'] != $user_id) {
                                $other_user = $member;
                                break;
                            }
                        }
                        if ($other_user): ?>
                            <span class="status-dot <?php echo $other_user['is_online'] ? 'online' : 'offline'; ?>"></span>
                            <span><?php echo $other_user['is_online'] ? 'Online' : 'Offline'; ?></span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span><?php echo count($members); ?> members</span>
                    <?php endif; ?>
                    
                    <?php if ($chat['chat_type'] == 'buna_time' && $chat['expires_at']): ?>
                        <span class="buna-timer" data-expires="<?php echo $chat['expires_at']; ?>">
                            ☕ Expires in: <span id="buna-countdown">1:00:00</span>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="chat-actions">
            <button class="btn-icon" onclick="toggleMembers()" title="Group Info">
                👥
            </button>
            <button class="btn-icon" onclick="startVoiceMessage()" title="Voice Message">
                🎤
            </button>
            <button class="btn-icon" onclick="showChatSettings()" title="Settings">
                ⚙️
            </button>
        </div>
    </div>

    <!-- Members Sidebar -->
    <div id="members-sidebar" class="members-sidebar">
        <div class="sidebar-header">
            <h4>Chat Members</h4>
            <button class="close-sidebar" onclick="toggleMembers()">×</button>
        </div>
        <div class="members-list">
            <?php foreach ($members as $member): ?>
                <div class="member-item">
                    <div class="member-avatar">
                        <?php echo strtoupper(substr($member['username'], 0, 1)); ?>
                    </div>
                    <div class="member-info">
                        <strong><?php echo htmlspecialchars($member['full_name']); ?></strong>
                        <span>@<?php echo htmlspecialchars($member['username']); ?></span>
                    </div>
                    <div class="member-status">
                        <span class="status-dot <?php echo $member['is_online'] ? 'online' : 'offline'; ?>"></span>
                        <span><?php echo $member['is_online'] ? 'Online' : 'Offline'; ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if ($chat['chat_type'] == 'group' || $chat['chat_type'] == 'buna_time'): ?>
            <div class="sidebar-actions">
                <button class="btn btn-outline" onclick="inviteToChat()">Invite People</button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Messages Area -->
    <div class="messages-container" id="messages-container">
        <?php if (empty($messages)): ?>
            <div class="empty-chat">
                <h3>No messages yet</h3>
                <p>Start the conversation!</p>
            </div>
        <?php else: ?>
            <?php 
            $last_date = null;
            foreach ($messages as $message): 
                $message_date = date('Y-m-d', strtotime($message['sent_at']));
                $is_own_message = $message['sender_id'] == $user_id;
                
                // Show date separator if date changed
                if ($message_date != $last_date): 
                    $last_date = $message_date;
            ?>
                    <div class="date-separator">
                        <span><?php 
                            if ($message_date == date('Y-m-d')) {
                                echo 'Today';
                            } elseif ($message_date == date('Y-m-d', strtotime('-1 day'))) {
                                echo 'Yesterday';
                            } else {
                                echo date('M j, Y', strtotime($message_date));
                            }
                        ?></span>
                    </div>
                <?php endif; ?>
                
                <div class="message <?php echo $is_own_message ? 'own-message' : 'other-message'; ?>" data-message-id="<?php echo $message['id']; ?>">
                    <?php if (!$is_own_message && $chat['chat_type'] != 'private'): ?>
                        <div class="sender-name"><?php echo htmlspecialchars($message['full_name']); ?></div>
                    <?php endif; ?>
                    
                    <div class="message-bubble">
                        <?php if ($message['message_type'] == 'text'): ?>
                            <div class="message-text"><?php echo htmlspecialchars($message['message_text']); ?></div>
                        <?php elseif ($message['message_type'] == 'image'): ?>
                            <div class="message-image">
                                <img src="<?php echo htmlspecialchars($message['file_path']); ?>" alt="Shared image">
                            </div>
                        <?php elseif ($message['message_type'] == 'audio'): ?>
                            <div class="message-audio">
                                <audio controls>
                                    <source src="<?php echo htmlspecialchars($message['file_path']); ?>" type="audio/wav">
                                    Your browser does not support the audio element.
                                </audio>
                            </div>
                        <?php endif; ?>
                        
                        <div class="message-time">
                            <?php echo date('H:i', strtotime($message['sent_at'])); ?>
                            <?php if ($is_own_message): ?>
                                <span class="read-status"><?php echo $message['is_read'] ? '✓✓' : '✓'; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Message Input -->
    <div class="message-input-container">
        <div class="input-actions">
            <button class="btn-icon" onclick="attachFile()" title="Attach File">📎</button>
            <button class="btn-icon" onclick="showEmojiPicker()" title="Emoji">😊</button>
        </div>
        
        <div class="message-input-wrapper">
            <textarea 
                id="message-input" 
                placeholder="Type a message..." 
                rows="1"
                oninput="autoResize(this)"
                onkeypress="handleKeyPress(event)"
            ></textarea>
            
            <!-- Voice Message Recorder -->
            <div id="voice-recorder" class="voice-recorder" style="display: none;">
                <div class="recording-indicator">
                    <div class="pulse"></div>
                    <span>Recording... Click to stop</span>
                </div>
            </div>
        </div>
        
        <div class="send-actions">
            <button id="voice-toggle" class="btn-icon" onclick="toggleVoiceRecorder()" title="Voice Message">
                🎤
            </button>
            <button id="send-button" class="btn-send" onclick="sendMessage()" title="Send">
                ➤
            </button>
        </div>
    </div>

    <!-- File Attachment Modal -->
    <div id="attachmentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Attach File</h3>
                <span class="close" onclick="closeModal('attachmentModal')">&times;</span>
            </div>
            <div class="modal-body">
                <div class="attachment-options">
                    <label class="attachment-option">
                        <input type="file" accept="image/*" onchange="uploadImage(this.files[0])" hidden>
                        <div class="option-icon">🖼️</div>
                        <span>Photo</span>
                    </label>
                    <label class="attachment-option">
                        <input type="file" accept=".pdf,.doc,.docx" onchange="uploadDocument(this.files[0])" hidden>
                        <div class="option-icon">📄</div>
                        <span>Document</span>
                    </label>
                    <label class="attachment-option">
                        <input type="file" accept="video/*" onchange="uploadVideo(this.files[0])" hidden>
                        <div class="option-icon">🎥</div>
                        <span>Video</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const CHAT_ID = <?php echo $chat_id; ?>;
const USER_ID = <?php echo $user_id; ?>;
</script>

<?php include 'includes/footer.php'; ?>