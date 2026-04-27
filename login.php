<?php
require_once 'layouts/functions.php';

if (is_logged_in()) {
  redirect("admin/dashboard.php");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
  $email = clean($_POST['email']);
  $password = $_POST['password'];

  $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
  $stmt->execute([$email]);
  $user = $stmt->fetch();

  if ($user && password_verify($password, $user->password)) {
    $_SESSION['user_id'] = $user->id;
    $_SESSION['full_name'] = $user->full_name;
    $_SESSION['role_id'] = $user->role_id;

    // Fetch role name for routing
    $role_stmt = $pdo->prepare("SELECT name FROM roles WHERE id = ?");
    $role_stmt->execute([$user->role_id]);
    $role = $role_stmt->fetch();
    $_SESSION['role_name'] = $role ? $role->name : '';

    if ($_SESSION['role_name'] == 'Super Admin' || $_SESSION['role_name'] == 'Admin') {
      redirect("admin/dashboard.php");
    }
    elseif ($_SESSION['role_name'] == 'Driver') {
      // Set driver as online
      $pdo->prepare("UPDATE drivers SET is_online = 1 WHERE user_id = ?")->execute([$user->id]);
      redirect("driver/dashboard.php");
    }
    else {
      // Default to passenger
      redirect("user/dashboard.php");
    }
  }
  else {
    set_flash('error', 'Invalid email or password.');
    redirect('login.php');
  }
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>MUET BusTrack - Sign In</title>
  
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            muet: {
              primary: '#3b82f6',   // blue-500
              dark: '#1e3a8a',      // blue-900
              darker: '#172554',    // blue-950
            }
          }
        }
      }
    }
  </script>

  <!-- Optional: Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <style>
    body { font-family: 'Inter', system-ui, sans-serif; }
  </style>
</head>

<body class="bg-gradient-to-br from-blue-950 via-indigo-950 to-blue-900 min-h-screen text-gray-100 flex items-center justify-center p-4 md:p-6">

  <div class="w-full max-w-6xl grid md:grid-cols-2 gap-10 lg:gap-16 items-center">

    <!-- Left - Hero / Branding -->
    <div class="space-y-8 md:space-y-10 text-center md:text-left">

      <!-- Logo + Badge -->
      <div class="flex flex-col items-center md:items-start gap-3">
        <div class="flex items-center gap-3">
          <div class="w-14 h-14 md:w-16 md:h-16 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg">
            <span class="text-3xl">🚌</span>
          </div>
          <div>
            <h1 class="text-2xl md:text-2xl font-bold tracking-tight">MUET BusTrack</h1>
            <p class="text-blue-300 text-sm md:text-base">Mehran University · Jamshoro</p>
          </div>
        </div>

        <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-green-700/30 text-green-300 text-sm font-medium rounded-full border border-green-600/40 backdrop-blur-sm">
          <span class="relative flex h-3 w-3">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
          </span>
          Live GPS Tracking Active
        </span>
      </div>

      <h2 class="text-2xl md:text-3xl lg:text-4xl font-extrabold leading-tight">
        Smart <span class="text-blue-400">Buses</span><br/>
        for <span class="text-blue-400">MUET Campus</span>
      </h2>

      <p class="text-lg md:text-xl-lxl text-blue-100 max-w-xl mx-auto md:mx-0">
        Real-time bus tracking, live ETAs, and schedule updates — all in one place for MUET students, staff, and drivers.
      </p>

      <!-- Stats -->
      <div class="flex flex-wrap justify-center md:justify-start gap-6 md:gap-10 text-center">
        <div>
          <div class="text-4xl md:text-2xl font-bold text-white">16</div>
          <div class="text-blue-300 text-sm md:text-base">Active Routes</div>
        </div>
        <div>
          <div class="text-4xl md:text-2xl font-bold text-white">5000+</div>
          <div class="text-blue-300 text-sm md:text-base">Daily Riders</div>
        </div>
        <div>
          <div class="text-4xl md:text-2xl font-bold text-white">Live</div>
          <div class="text-blue-300 text-sm md:text-base">GPS Updates</div>
        </div>
      </div>

      <!-- Fake live route preview -->
      <div class="mt-6 bg-blue-950/40 backdrop-blur-md border border-blue-800/50 rounded-2xl p-5 shadow-2xl max-w-md mx-auto md:mx-0">
        <div class="text-sm text-blue-300 mb-3 font-medium">LIVE ROUTE PREVIEW</div>
        <div class="relative h-20 flex items-center">
          <!-- Line -->
          <div class="absolute h-1.5 w-full bg-gradient-to-r from-blue-500 via-green-500 to-blue-500 rounded-full"></div>
          
          <!-- Stops -->
          <div class="absolute left-0 flex flex-col items-center -translate-y-1/2">
            <div class="w-5 h-5 rounded-full bg-blue-500 border-4 border-white shadow"></div>
            <span class="text-xs mt-1">Hyd Gate</span>
          </div>
          
          <div class="absolute left-1/4 flex flex-col items-center -translate-y-1/2">
            <div class="w-5 h-5 rounded-full bg-green-500 border-4 border-white shadow animate-pulse"></div>
            <span class="text-xs mt-1">Bypass</span>
          </div>
          
          <div class="absolute left-1/2 transform -translate-x-1/2 -translate-y-1/2">
            <div class="text-4xl animate-bounce">🚌</div>
            <div class="text-xs text-green-300 font-semibold">Bus #27</div>
          </div>
          
          <div class="absolute right-1/4 flex flex-col items-center -translate-y-1/2">
            <div class="w-5 h-5 rounded-full bg-blue-500 border-4 border-white shadow"></div>
            <span class="text-xs mt-1">MUET Gate</span>
          </div>
          
          <div class="absolute right-0 flex flex-col items-center -translate-y-1/2">
            <div class="w-5 h-5 rounded-full bg-blue-500 border-4 border-white shadow"></div>
            <span class="text-xs mt-1">New Campus</span>
          </div>
        </div>
        <div class="text-center mt-4 text-green-400 font-medium">ETA 4 min</div>
      </div>

    </div>

    <!-- Right - Login Form -->
    <div class="bg-white/10 backdrop-blur-xl border border-white/10 rounded-2xl md:rounded-3xl shadow-2xl p-8 md:p-10 lg:p-12">

      <div class="text-center mb-6">
        <h2 class="text-3xl md:text-4xl font-bold">Welcome Back 👋</h2>
        <p class="text-blue-200 mt-2">Sign in to your MUET BusTrack account</p>
      </div>

      <?php display_flash(); ?>

      <form method="POST" class="space-y-6">

        <!-- Email -->
        <div>
          <label class="block text-sm font-medium text-blue-200 mb-1.5">Email Address</label>
          <input
            type="email"
            name="email"
            required
            placeholder="you@muet.edu.pk"
            class="w-full px-5 py-3.5 bg-white/10 border border-white/20 rounded-xl text-white placeholder-blue-300 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30 transition"
          />
        </div>

        <!-- Password -->
        <div>
          <label class="block text-sm font-medium text-blue-200 mb-1.5">Password</label>
          <input
            type="password"
            name="password"
            required
            placeholder="Enter your password"
            class="w-full px-5 py-3.5 bg-white/10 border border-white/20 rounded-xl text-white placeholder-blue-300 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30 transition"
          />
        </div>

        <!-- Forgot password -->
        <div class="flex justify-end">
          <a href="forgot_password.php" class="text-sm text-blue-300 hover:text-blue-100 transition">Forgot password?</a>
        </div>

        <!-- Submit -->
        <button
          type="submit"
          name="login"
          class="w-full py-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-semibold rounded-xl shadow-lg transform hover:scale-[1.02] transition duration-300"
        >
          Sign In →
        </button>

      </form>

      <div class="text-center mt-8 text-blue-200">
        Don't have an account? 
        <a href="signup.php" class="text-blue-300 hover:text-white font-medium transition">Create a new account</a>
      </div>

    </div>

  </div>

</body>
</html>
