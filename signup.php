<?php
require_once 'layouts/functions.php';

if (is_logged_in()) {
  redirect("user/dashboard.php");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
  $full_name = clean($_POST['full_name']);
  $email = clean($_POST['email']);
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $role_name = clean($_POST['role']);

  // Get role ID
  $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = ?");
  $stmt->execute([$role_name]);
  $role_id = $stmt->fetchColumn();

  if (!$role_id) {
    set_flash('error', 'Invalid role selected.');
    redirect('signup.php');
  }

  try {
    $pdo->beginTransaction();

    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
      throw new Exception("Email already registered!");
    }

    // Insert User
    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$full_name, $email, $password, $role_id]);
    $user_id = $pdo->lastInsertId();

    // If Driver, insert into drivers table
    if ($role_name == 'Driver') {
      $license = clean($_POST['license']);
      $phone = clean($_POST['phone']);
      $stmt = $pdo->prepare("INSERT INTO drivers (user_id, license_number, phone_number) VALUES (?, ?, ?)");
      $stmt->execute([$user_id, $license, $phone]);
    }

    $pdo->commit();
    set_flash('success', 'Registration successful! You can now login.');
    redirect('login.php');
  }
  catch (Exception $e) {
    $pdo->rollBack();
    set_flash('error', $e->getMessage());
    redirect('signup.php');
  }
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Sign Up - MUET BusTrack</title>
  
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

  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <style>
    body { font-family: 'Inter', system-ui, sans-serif; }
  </style>
</head>

<body class="bg-gradient-to-br from-blue-950 via-indigo-950 to-blue-900 min-h-screen text-gray-100 flex items-center justify-center p-4 md:p-6">

  <div class="w-full max-w-6xl grid md:grid-cols-2 gap-10 lg:gap-16 items-center">

    <!-- Left - Hero / Branding -->
    <div class="space-y-8 md:space-y-10 text-center md:text-left hidden md:block">

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
      </div>

      <h2 class="text-4xl md:text-3xl lg:text-3xl font-extrabold leading-tight">
        Join the <span class="text-blue-400">Smart Transit</span><br/>
        Network Today
      </h2>

      <p class="text-lg md:text-xl-2xl text-blue-200 max-w-xl mx-auto md:mx-0">
        Create an account to track buses in real-time, get live ETAs, and easily plan your campus commutes.
      </p>

    </div>

    <!-- Right - Signup Form -->
    <div class="bg-white/10 backdrop-blur-xl border border-white/10 rounded-2xl md:rounded-3xl shadow-2xl p-6 md:p-10 lg:p-12">

      <div class="text-center mb-6">
        <h2 class="text-3xl font-bold">Create Account 🚀</h2>
        <p class="text-blue-200 mt-2">Join the MUET transit network</p>
      </div>

      <?php display_flash(); ?>

      <form method="POST" id="signupForm" class="space-y-5">

        <!-- Role Toggle -->
        <div>
          <label class="block text-sm font-medium text-blue-200 mb-1.5">Register as</label>
          <div class="flex bg-white/10 p-1 rounded-xl">
            <button type="button" class="btn-toggle flex-1 py-2 text-sm font-medium rounded-lg transition-colors bg-blue-600 text-white shadow" data-role="User">Passenger</button>
            <!-- <button type="button" class="btn-toggle flex-1 py-2 text-sm font-medium rounded-lg transition-colors text-blue-200 hover:text-white" data-role="Driver">Driver</button> -->
          </div>
          <input type="hidden" name="role" id="roleInput" value="User">
        </div>

        <!-- Full Name -->
        <div>
          <label class="block text-sm font-medium text-blue-200 mb-1.5">Full Name</label>
          <input type="text" name="full_name" required placeholder="your name" class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-blue-300/50 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30 transition" />
        </div>

        <!-- Email -->
        <div>
          <label class="block text-sm font-medium text-blue-200 mb-1.5">Email Address</label>
          <input type="email" name="email" required placeholder="you@muet.edu.pk" class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-blue-300/50 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30 transition" />
        </div>

        <!-- Password -->
        <div>
          <label class="block text-sm font-medium text-blue-200 mb-1.5">Password</label>
          <input type="password" name="password" required placeholder="Create a strong password" class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-blue-300/50 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30 transition" />
        </div>

        <!-- Driver Specific Fields
        <div id="driverFields" style="display: none;" class="space-y-5 border-t border-white/10 pt-4 mt-2">
            <div>
                <label class="block text-sm font-medium text-blue-200 mb-1.5">License Number</label>
                <input type="text" name="license" placeholder="ABC-12345" class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-blue-300/50 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30 transition" />
            </div>
            <div>
                <label class="block text-sm font-medium text-blue-200 mb-1.5">Phone Number</label>
                <input type="text" name="phone" placeholder="+92 300 0000000" class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-blue-300/50 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30 transition" />
            </div>
        </div> -->

        <!-- Submit -->
        <button type="submit" name="register" class="w-full py-3.5 mt-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-semibold rounded-xl shadow-lg transform hover:scale-[1.02] transition duration-300">
          Create Account →
        </button>

      </form>

      <div class="text-center mt-6 text-blue-200 text-sm">
        Already have an account? 
        <a href="login.php" class="text-blue-300 hover:text-white font-medium transition">Sign In here</a>
      </div>

    </div>

  </div>

  <script>
      const toggles = document.querySelectorAll('.btn-toggle');
      const roleInput = document.getElementById('roleInput');
      const driverFields = document.getElementById('driverFields');
      const driverInputs = driverFields.querySelectorAll('input');

      toggles.forEach(toggle => {
          toggle.addEventListener('click', () => {
              // Reset all
              toggles.forEach(t => {
                  t.classList.remove('bg-blue-600', 'text-white', 'shadow');
                  t.classList.add('text-blue-200', 'hover:text-white');
              });
              
              // Set active
              toggle.classList.remove('text-blue-200', 'hover:text-white');
              toggle.classList.add('bg-blue-600', 'text-white', 'shadow');
              
              const role = toggle.getAttribute('data-role');
              roleInput.value = role;

              if (role === 'Driver') {
                  driverFields.style.display = 'block';
                  driverInputs.forEach(i => i.required = true);
              } else {
                  driverFields.style.display = 'none';
                  driverInputs.forEach(i => i.required = false);
              }
          });
      });
  </script>
</body>
</html>
