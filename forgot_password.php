<?php
require_once 'layouts/functions.php';

if (is_logged_in()) {
    redirect("user/dashboard.php");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_request'])) {
    $email = clean($_POST['email']);

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        // Generate Token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $updateStmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires_at = ? WHERE email = ?");
        $updateStmt->execute([$token, $expires, $email]);

        // In a real application, send this link via mail().
        // For demonstration, we'll display the link in a success message.
        $reset_link = BASE_URL . "/reset_password.php?token=" . $token;
        
        // Simulating Email sending
        set_flash('success', "A password reset link has been generated. <br><br> <a href='{$reset_link}' class='text-blue-500 underline font-bold'>Click here to reset your password (Simulation)</a>");
    } else {
        // Generic message for security
        set_flash('success', "If an active account with that email exists, a password reset link has been sent.");
    }
    
    redirect('forgot_password.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - MUET BusTrack</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-gray-100 p-8 space-y-6">
        <div class="text-center">
            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                🔒
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Forgot Password</h2>
            <p class="text-sm text-gray-500 mt-2">Enter your email address to receive a password reset link.</p>
        </div>

        <?php display_flash(); ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <input type="email" name="email" required placeholder="you@muet.edu.pk" 
                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
            </div>

            <button type="submit" name="reset_request" 
                    class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition shadow-md hover:shadow-lg">
                Send Reset Link
            </button>
        </form>

        <div class="text-center text-sm text-gray-600 pt-2 border-t border-gray-100">
            Remember your password? <a href="login.php" class="text-blue-600 font-medium hover:underline">Sign In</a>
        </div>
    </div>

</body>
</html>
