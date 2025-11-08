<?php
session_start();
require __DIR__ . '/../src/FirebaseService.php';

$firebase = new FirebaseService();
$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);

    // Check if user exists
    if ($firebase->getUserByPhone($phone)) {
        $error = "User with this phone already exists.";
    } else {
        $newUser = [
            'name' => $name,
            'phone' => $phone,
            'password' => $password,
            'created_at' => date('c')
        ];
        $firebase->createUser($newUser);
        $success = "Account created successfully! You can now <a href='login.php' class='text-indigo-600 hover:underline'>login</a>.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ServicePay Wallet | Register</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-indigo-600 via-purple-600 to-indigo-800 min-h-screen flex items-center justify-center">

<div class="bg-white shadow-2xl rounded-3xl w-full max-w-sm p-8">
    <div class="text-center mb-6">
        <img src="assets/logo.svg" alt="ServicePay Logo" class="h-12 mx-auto mb-2">
        <h1 class="text-2xl font-bold text-gray-800">ServicePay Wallet</h1>
        <p class="text-gray-500 text-sm">Create your account</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="bg-red-100 text-red-600 text-sm rounded-md p-2 mb-4">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="bg-green-100 text-green-600 text-sm rounded-md p-2 mb-4">
            <?= $success ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-5">
        <div>
            <label class="text-sm font-medium text-gray-700">Full Name</label>
            <input type="text" name="name" required placeholder="Your Name"
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
