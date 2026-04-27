<?php
require 'config/db.php';
try {
    // Check if columns exist
    $result = $pdo->query("SHOW COLUMNS FROM users LIKE 'reset_token'");
    if ($result->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN reset_token VARCHAR(255) NULL");
        $pdo->exec("ALTER TABLE users ADD COLUMN reset_expires_at DATETIME NULL");
        echo "Users table altered.\n";
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sender_id INT,
        receiver_id INT NULL,
        message TEXT NOT NULL,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "Messages table created.\n";

    echo "Migration successful.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
