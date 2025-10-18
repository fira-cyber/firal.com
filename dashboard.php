<?php
include 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user's chats
$stmt = $pdo->prepare("
    SELECT c.*, 
           (SELECT message_text FROM messages WHERE chat_id = c.id ORDER BY sent_at DESC LIMIT 1) as last_message,
           (SELECT sent_at FROM messages WHERE chat_id = c.id ORDER BY sent_at DESC LIMIT 1) as last_message_time,
           COUNT(DISTINCT cm.user_id) as member_count
    FROM chats c
    LEFT JOIN chat_members cm ON c.id = cm.chat_id
    WHERE c.id IN (SELECT chat_id FROM chat_members WHERE user_id = ?)
    GROUP BY c.id
    ORDER BY last_message_time DESC
");
$stmt->execute([$user_id]);
$chats = $stmt->fetchAll();

// Get online users (except current user)
$stmt = $pdo->prepare("SELECT id, username, full_name, profile_picture FROM users WHERE is_online = TRUE AND id != ?");
$stmt->execute([$user_id]);
$online_users = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="user-profile">
                <div class="profile-picture">
                    <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                </div>
                <div class="user-info">
                    <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong>
                    <span>@<?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </div>
            </div>
        </div>

        <div class="sidebar-menu">
            <button class="menu-item active" onclick="showSection('chats')">
                <span>💬</span>
                <span>Chats</span>
            </button>
            <button class="menu-item" onclick="showSection('buna-time')">
                <span>☕</span>
                <span>Buna Time</span>
            </button>
            <button class="menu-item" onclick="showSection('contacts')">
                <span>👥</span>
                <span>Contacts</span>
            </button>
            <button class="menu-item" onclick="showSection('groups')">
                <span>👪</span>
                <span>Groups</span>
            </button>
        </div>

        <div class="online-users">
            <h4>Online Now</h4>
            <?php foreach ($online_users as $user): ?>
                <div class="online-user" onclick="startChat(<?php echo $user['id']; ?>)">
                    <div class="user-avatar"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></div>
                    <span><?php echo htmlspecialchars($user['full_name']); ?></span>
                    <div class="online-indicator"></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Chats Section -->
        <div id="chats-section" class="content-section active">
            <div class="section-header">
                <h2>Your Chats</h2>
                <button class="btn btn-primary" onclick="createNewChat()">New Chat</button>
            </div>

            <div class="chats-list">
                <?php if (empty($chats)): ?>
                    <div class="empty-state">
                        <h3>No chats yet</h3>
                        <p>Start a conversation with someone!</p>
                        <button class="btn btn-primary" onclick="createNewChat()">Start Chatting</button>
                    </div>
                <?php else: ?>
                    <?php foreach ($chats as $chat): ?>
                        <div class="chat-item" onclick="openChat(<?php echo $chat['id']; ?>)">
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
                            <div class="chat-info">
                                <div class="chat-header">
                                    <strong>
                                        <?php 
                                        if ($chat['chat_type'] == 'private') {
                                            echo 'Private Chat';
                                        } else {
                                            echo htmlspecialchars($chat['chat_name'] ?: 'Unnamed Chat');
                                        }
                                        ?>
                                    </strong>
                                    <span class="time">
                                        <?php 
                                        if ($chat['last_message_time']) {
                                            echo time_elapsed_string($chat['last_message_time']);
                                        }
                                        ?>
                                    </span>
                                </div>
                                <p class="last-message">
                                    <?php 
                                    if ($chat['last_message']) {
                                        echo htmlspecialchars(substr($chat['last_message'], 0, 50)) . 
                                             (strlen($chat['last_message']) > 50 ? '...' : '');
                                    } else {
                                        echo 'No messages yet';
                                    }
                                    ?>
                                </p>
                                <?php if ($chat['chat_type'] == 'group'): ?>
                                    <span class="member-count"><?php echo $chat['member_count']; ?> members</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Buna Time Section -->
        <div id="buna-time-section" class="content-section">
            <div class="section-header">
                <h2>☕ Buna Time Chat Rooms</h2>
                <button class="btn btn-primary" onclick="createBunaTime()">Start Buna Time</button>
            </div>

            <div class="buna-time-info">
                <p>Buna Time rooms are temporary chat rooms that expire after 1 hour, just like a traditional coffee ceremony!</p>
            </div>

            <div class="buna-rooms">
                <!-- Will be populated by JavaScript -->
                <div class="empty-state">
                    <h3>No active Buna Time rooms</h3>
                    <p>Start one and invite your friends for a quick chat!</p>
                </div>
            </div>
        </div>

        <!-- Contacts Section -->
        <div id="contacts-section" class="content-section">
            <div class="section-header">
                <h2>Contacts</h2>
                <div class="search-box">
                    <input type="text" id="contact-search" placeholder="Search contacts...">
                </div>
            </div>
            <div class="contacts-list">
                <!-- Will be populated by JavaScript -->
            </div>
        </div>

        <!-- Groups Section -->
        <div id="groups-section" class="content-section">
            <div class="section-header">
                <h2>Your Groups</h2>
                <button class="btn btn-primary" onclick="createGroup()">Create Group</button>
            </div>
            <div class="groups-list">
                <!-- Will show user's groups -->
            </div>
        </div>
    </div>
</div>

<!-- New Chat Modal -->
<div id="newChatModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Start New Chat</h3>
            <span class="close" onclick="closeModal('newChatModal')">&times;</span>
        </div>
        <div class="modal-body">
            <div class="search-box">
                <input type="text" id="user-search" placeholder="Search users...">
            </div>
            <div id="search-results" class="search-results">
                <!-- Search results will appear here -->
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>