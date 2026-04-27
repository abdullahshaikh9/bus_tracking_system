<?php
require_once 'layouts/functions.php';

if (is_logged_in()) {
    redirect("user/dashboard.php");
}

$token = $_GET['token'] ?? '';

if (empty($token)) {
    set_flash('error', 'Invalid or missing password reset token.');
    redirect('login.php');
}

// Verify token
$stmt = $pdo->prepare("SELECT id, reset_expires_at FROM users WHERE reset_token = ?");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user || strtotime($user->reset_expires_at) < time()) {
    set_flash('error', 'The password reset link has expired or is invalid. Please request a new one.');
    redirect('forgot_password.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_password'])) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        set_flash('error', 'Passwords do not match.');
    } elseif (strlen($password) < 6) {
        set_flash('error', 'Password must be at least 6 characters.');
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $updateStmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires_at = NULL WHERE id = ?");
        $updateStmt->execute([$hashed_password, $user->id]);

        set_flash('success', 'Your password has been reset successfully. You can now login.');
        redirect('login.php');
    }
    // PRG on failure inside the token form
    redirect("reset_password.php?token={$token}");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - MUET BusTrack</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-gray-100 p-8 space-y-6">
        <div class="text-center">
            <div class="w-12 h-12 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                🔑
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Set New Password</h2>
            <p class="text-sm text-gray-500 mt-2">Enter your new password below.</p>
        </div>

        <?php display_flash(); ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                <input type="password" name="password" required placeholder="••••••••" 
                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                <input type="password" name="confirm_password" required placeholder="••••••••" 
                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
            </div>

            <button type="submit" name="reset_password" 
                    class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition shadow-md hover:shadow-lg">
                Update Password
            </button>
        </form>
    </div>

</body>
</html>
