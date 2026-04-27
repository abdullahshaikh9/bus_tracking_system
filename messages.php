<?php
$page_title = 'Messages';
$active_page = 'messages'; 
require_once __DIR__ . '/../layouts/functions.php';

require_login();
$current_user_id = $_SESSION['user_id'];
$recipient_id = isset($_GET['u']) ? (int)$_GET['u'] : 0;

// Handle sending a message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $msg_text = trim($_POST['message_text'] ?? '');
    $rec_id = (int)($_POST['recipient_id'] ?? 0);
    
    if (!empty($msg_text) && $rec_id > 0) {
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$current_user_id, $rec_id, $msg_text]);
        
        $sender = get_user($pdo, $current_user_id);
        $sender_name = $sender ? $sender->full_name : 'A user';
        $snippet = strlen($msg_text) > 40 ? substr($msg_text, 0, 40) . '...' : $msg_text;
        
        // Notify the receiver
        $notif_stmt = $pdo->prepare("INSERT INTO notifications (title, message, type, target_user_id) VALUES ('New Message', ?, 'info', ?)");
        $notif_stmt->execute(["{$sender_name} sent you a message: \"{$snippet}\"", $rec_id]);
        
        redirect("messages.php?u={$rec_id}");
    }
}

// Mark messages as read from the selected user
if ($recipient_id > 0) {
    $stmt = $pdo->prepare("UPDATE messages SET is_read = TRUE WHERE sender_id = ? AND receiver_id = ?");
    $stmt->execute([$recipient_id, $current_user_id]);
}

require_once __DIR__ . '/../layouts/header.php';

// Fetch users for the sidebar (Any admins for a regular user to contact)
// For users, we default to showing Admins they can talk to.
$stmt = $pdo->prepare("
    SELECT u.id, u.full_name, u.email, r.name as role_name
    FROM users u
    JOIN roles r ON u.role_id = r.id
    WHERE r.name IN ('Super Admin', 'Admin') AND u.id != ?
");
$stmt->execute([$current_user_id]);
$contact_list = $stmt->fetchAll();

// If a recipient is selected, fetch the chat history
$chat_history = [];
$recipient_name = '';
if ($recipient_id > 0) {
    // Get recipient details
    $r_stmt = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
    $r_stmt->execute([$recipient_id]);
    $rec_user = $r_stmt->fetch();
    if ($rec_user) {
        $recipient_name = $rec_user->full_name;
        
        $c_stmt = $pdo->prepare("
            SELECT * FROM messages 
            WHERE (sender_id = ? AND receiver_id = ?) 
               OR (sender_id = ? AND receiver_id = ?)
            ORDER BY created_at ASC
        ");
        $c_stmt->execute([$current_user_id, $recipient_id, $recipient_id, $current_user_id]);
        $chat_history = $c_stmt->fetchAll();
    } else {
        $recipient_id = 0; // Invalid user
    }
}
?>

<div class="max-w-6xl mx-auto h-[75vh] min-h-[500px] flex gap-6">
    <!-- Contacts Sidebar -->
    <div class="w-1/3 bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden flex flex-col">
        <div class="p-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex-shrink-0">
            <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="ph ph-users text-primary-500 text-xl"></i> Help & Support
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Message an Admin for assistance.</p>
        </div>
        
        <div class="flex-1 overflow-y-auto w-full">
            <?php foreach ($contact_list as $contact): ?>
                <a href="?u=<?= $contact->id ?>" class="flex items-center gap-3 p-4 border-b border-gray-50 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition <?= $recipient_id === $contact->id ? 'bg-primary-50 dark:bg-primary-900/10 border-l-4 border-l-primary-500' : '' ?>">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-600 flex items-center justify-center font-bold text-gray-600 dark:text-gray-300 shrink-0">
                        <?= strtoupper(substr($contact->full_name, 0, 1)) ?>
                    </div>
                    <div class="overflow-hidden">
                        <h4 class="font-semibold text-gray-900 dark:text-white text-sm truncate"><?= htmlspecialchars($contact->full_name) ?></h4>
                        <p class="text-xs text-primary-600 dark:text-primary-400 font-medium truncate"><?= htmlspecialchars($contact->role_name) ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Chat Area -->
    <div class="w-2/3 bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden flex flex-col relative">
        <?php if ($recipient_id === 0): ?>
            <!-- No Selection State -->
            <div class="flex-1 flex flex-col items-center justify-center p-8 text-center text-gray-500 dark:text-gray-400">
                <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                    <i class="ph ph-paper-plane-tilt text-3xl"></i>
                </div>
                <h3 class="text-xl font-medium text-gray-900 dark:text-white mb-2">Your Messages</h3>
                <p>Select an administrator from the left panel to start a conversation or ask for help regarding bus routes.</p>
            </div>
        <?php else: ?>
            <!-- Active Chat Header -->
            <div class="p-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex-shrink-0 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center font-bold text-primary-600 dark:text-primary-400">
                        <?= strtoupper(substr($recipient_name, 0, 1)) ?>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($recipient_name) ?></h3>
                        <p class="text-xs text-green-500 font-medium flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Online
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Chat Messages -->
            <div class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50/30 dark:bg-gray-900/30 flex flex-col" id="chatContainer">
                <?php if (empty($chat_history)): ?>
                    <div class="text-center text-gray-400 text-sm mt-10">
                        No messages yet. Send a message to start the conversation!
                    </div>
                <?php else: ?>
                    <?php foreach ($chat_history as $msg): 
                        $is_me = ($msg->sender_id === $current_user_id);
                    ?>
                        <div class="flex <?= $is_me ? 'justify-end' : 'justify-start' ?>">
                            <div class="max-w-[75%] <?= $is_me ? 'bg-primary-600 text-white rounded-l-2xl rounded-tr-2xl' : 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-r-2xl rounded-tl-2xl' ?> px-5 py-3 shadow-sm">
                                <p class="text-sm leading-relaxed"><?= nl2br(htmlspecialchars($msg->message)) ?></p>
                                <div class="text-[10px] mt-1 text-right <?= $is_me ? 'text-primary-200' : 'text-gray-500 dark:text-gray-400' ?>">
                                    <?= date('g:i A', strtotime($msg->created_at)) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Message Input Form -->
            <div class="p-4 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 flex-shrink-0">
                <form method="POST" class="flex items-end gap-3">
                    <input type="hidden" name="recipient_id" value="<?= $recipient_id ?>">
                    <div class="flex-1">
                        <textarea name="message_text" rows="2" required placeholder="Type your message here..." class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition text-gray-900 dark:text-white resize-none scrollbar-hide"></textarea>
                    </div>
                    <button type="submit" name="send_message" class="h-[52px] px-6 bg-primary-600 hover:bg-primary-700 text-white rounded-xl shadow-md transition flex items-center justify-center shrink-0">
                        <i class="ph ph-paper-plane-right text-xl"></i>
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Auto-scroll to bottom of chat
document.addEventListener('DOMContentLoaded', function() {
    const chatContainer = document.getElementById('chatContainer');
    if (chatContainer) {
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
