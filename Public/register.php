<?php
require_once __DIR__ . '/../src/FirebaseService.php';
session_start();

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firebase = new FirebaseService();

    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);

    // Basic validation
    if (empty($name) || empty($phone) || empty($password)) {
        $error = "All fields are required.";
    } else {
        // Check if phone already exists
        $existing = $firebase->getUserByPhone($phone);
        if ($existing) {
            $error = "Phone number already registered.";
        } else {
            $newUser = [
                'name' => $name,
                'phone' => $phone,
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'balance' => 0,
                'created_at' => date('c'),
            ];

            $userId = $firebase->createUser($newUser);

            if ($userId) {
                $success = "Account created successfully! <a href='login.php' class='text-indigo-600 hover:underline'>Login now</a>.";
            } else {
                $error = "Failed to create account. Try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register | ServicePay Wallet</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-indigo-600 via-purple-600 to-indigo-800 min-h-screen flex items-center justify-center">
  <div class="bg-white shadow-2xl rounded-3xl w-full max-w-sm p-8">
    <div class="text-center mb-6">
      <img src="assets/logo.svg" alt="ServicePay Logo" class="h-12 mx-auto mb-2">
      <h1 class="text-2xl font-bold text-gray-800">Create Account</h1>
      <p class="text-gray-500 text-sm">Join ServicePay Wallet</p>
    </div>

    <?php if ($error): ?>
      <div class="bg-red-100 text-red-600 text-sm rounded-md p-2 mb-4"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
      <div class="bg-green-100 text-green-600 text-sm rounded-md p-2 mb-4"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-5">
      <div>
        <label class="text-sm font-medium text-gray-700">Full Name</label>
        <input type="text" name="name" required placeholder="Enter your name"
          class="w-full mt-1 border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
      </div>

      <div>
        <label class="text-sm font-medium text-gray-700">Phone Number</label>
        <input type="text" name="phone" required placeholder="+2547XXXXXXXX"
          class="w-full mt-1 border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
      </div>

      <div>
        <label class="text-sm font-medium text-gray-700">Password</label>
        <input type="password" name="password" required placeholder="Enter password"
          class="w-full mt-1 border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
      </div>

      <button type="submit" class="w-full bg-indigo-600 text-white font-semibold py-2.5 rounded-lg hover:bg-indigo-700 transition-all">
        Register
      </button>
    </form>

    <p class="text-center text-sm text-gray-500 mt-5">
      Already have an account?
      <a href="login.php" class="text-indigo-600 hover:underline">Login</a>
    </p>
  </div>
</body>
</html>
