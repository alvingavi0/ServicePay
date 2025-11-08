<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/FirebaseService.php';

if (empty($_SESSION['user'])) {
  header('Location: login.php');
  exit;
}

$user = $_SESSION['user'];
$firebase = new FirebaseService();

$balance = 0;
$transactions = [];

try {
    // Fetch user document from Firestore
    $userData = $firebase->getUser($user['id']);
    if ($userData && isset($userData['balance'])) {
        $balance = $userData['balance'];
    }

    // Fetch user transactions
    $transactions = $firebase->getUserTransactions($user['id']);
} catch (Exception $e) {
    error_log("Firestore Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ServicePay Wallet</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col">
  <!-- Navbar -->
  <header class="bg-white shadow-sm">
    <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <img src="assets/logo.svg" alt="logo" class="h-8">
        <span class="font-semibold text-lg text-slate-700">ServicePay</span>
      </div>
      <div class="flex items-center gap-3">
        <span class="text-sm text-gray-600">Hi, <?= htmlspecialchars($user['name']) ?></span>
        <a href="logout.php" class="text-sm text-red-500 hover:underline">Logout</a>
      </div>
    </div>
  </header>

  <!-- Main -->
  <main class="flex-grow max-w-6xl mx-auto px-4 py-6">
    <!-- Wallet Summary -->
    <section class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-2xl p-6 shadow-lg flex justify-between items-center">
      <div>
        <p class="text-sm text-indigo-100">Current Balance</p>
        <h1 class="text-4xl font-bold mt-1">KES <?= number_format($balance, 2) ?></h1>
      </div>
      <div class="flex gap-3">
        <button class="bg-white text-indigo-700 font-semibold px-4 py-2 rounded-xl hover:bg-indigo-50" onclick="openDeposit()">Deposit</button>
        <button class="bg-emerald-500 text-white font-semibold px-4 py-2 rounded-xl hover:bg-emerald-600" onclick="openWithdraw()">Withdraw</button>
      </div>
    </section>

    <!-- Transaction History -->
    <section class="mt-8 bg-white rounded-2xl shadow p-5">
      <h2 class="text-lg font-semibold mb-3 text-slate-700">Transaction History</h2>

      <?php if (empty($transactions)): ?>
        <p class="text-gray-500 text-sm">No transactions yet.</p>
      <?php else: ?>
      <table class="w-full text-sm border-collapse">
        <thead>
          <tr class="text-gray-500 border-b">
            <th class="py-2 text-left">Type</th>
            <th class="py-2 text-left">Phone</th>
            <th class="py-2 text-left">Amount</th>
            <th class="py-2 text-left">Status</th>
            <th class="py-2 text-left">Date</th>
          </tr>
        </thead>
        <tbody id="transactionTable">
          <?php foreach ($transactions as $t): ?>
          <tr class="border-b hover:bg-slate-50">
            <td class="py-2"><?= htmlspecialchars($t['type'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($t['phone'] ?? '-') ?></td>
            <td>KES <?= number_format($t['amount'] ?? 0, 2) ?></td>
            <td class="<?= ($t['status'] ?? '') === 'Success' ? 'text-green-600' : 'text-yellow-600' ?> font-medium">
              <?= htmlspecialchars($t['status'] ?? 'Pending') ?>
            </td>
            <td><?= htmlspecialchars($t['created_at'] ?? '-') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </section>
  </main>

  <!-- Deposit Modal -->
  <div id="depositModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-sm shadow-lg">
      <h3 class="text-lg font-semibold mb-4">Deposit Funds</h3>
      <form method="POST" action="servicepay.php" class="space-y-4">
        <input type="hidden" name="action" value="pay">
        <label class="block">
          <span class="text-sm">Phone Number</span>
          <input name="phone" type="text" required class="mt-1 block w-full border-gray-300 rounded-md p-2">
        </label>
        <label class="block">
          <span class="text-sm">Amount (KES)</span>
          <input name="amount" type="number" required class="mt-1 block w-full border-gray-300 rounded-md p-2">
        </label>
        <div class="flex justify-end gap-3 mt-4">
          <button type="button" class="text-gray-500" onclick="closeDeposit()">Cancel</button>
          <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Confirm</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Withdraw Modal -->
  <div id="withdrawModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-sm shadow-lg">
      <h3 class="text-lg font-semibold mb-4">Withdraw Funds</h3>
      <form method="POST" action="servicepay.php" class="space-y-4">
        <input type="hidden" name="action" value="withdraw">
        <label class="block">
          <span class="text-sm">Phone Number</span>
          <input name="wphone" type="text" required class="mt-1 block w-full border-gray-300 rounded-md p-2">
        </label>
        <label class="block">
          <span class="text-sm">Amount (KES)</span>
          <input name="wamount" type="number" required class="mt-1 block w-full border-gray-300 rounded-md p-2">
        </label>
        <div class="flex justify-end gap-3 mt-4">
          <button type="button" class="text-gray-500" onclick="closeWithdraw()">Cancel</button>
          <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded-md hover:bg-emerald-700">Confirm</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function openDeposit() {
      document.getElementById('depositModal').classList.remove('hidden');
    }
    function closeDeposit() {
      document.getElementById('depositModal').classList.add('hidden');
    }
    function openWithdraw() {
      document.getElementById('withdrawModal').classList.remove('hidden');
    }
    function closeWithdraw() {
      document.getElementById('withdrawModal').classList.add('hidden');
    }
  </script>
</body>
</html>
