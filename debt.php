<?php
// Include database connection
require_once 'db_connect.php';

// Initialize variables for form data and errors
$errors = [];
$success_message = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize debt data
    $debt_type = isset($_POST['debt_type']) ? sanitize_input($_POST['debt_type']) : '';
    $amount_owed = isset($_POST['amount_owed']) ? sanitize_input($_POST['amount_owed']) : 0;
    $interest_rate = isset($_POST['interest_rate']) ? sanitize_input($_POST['interest_rate']) : 0;
    $min_payment = isset($_POST['min_payment']) ? sanitize_input($_POST['min_payment']) : 0;
    $progress = isset($_POST['progress']) ? sanitize_input($_POST['progress']) : 0;
    
    // Validate required fields
    if (empty($debt_type)) {
        $errors[] = "Debt type is required.";
    }
    
    // Validate numeric fields
    $numeric_fields = [
        'Amount owed' => $amount_owed,
        'Interest rate' => $interest_rate,
        'Minimum payment' => $min_payment,
        'Progress' => $progress
    ];
    
    foreach ($numeric_fields as $field_name => $value) {
        $error = validate_numeric($value, $field_name);
        if (!empty($error)) {
            $errors[] = $error;
        }
    }
    
    // If no errors, save to database
    if (empty($errors)) {
        // For demo purposes, using user_id = 1
        $user_id = 1;
        
        $sql = "INSERT INTO debt (user_id, debt_type, amount_owed, interest_rate, min_payment, progress) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isdddd", $user_id, $debt_type, $amount_owed, $interest_rate, $min_payment, $progress);
        
        if ($stmt->execute()) {
            $success_message = "Debt data saved successfully!";
        } else {
            $errors[] = "Error: " . $stmt->error;
        }
        
        $stmt->close();
    }
}

// Fetch existing debts
$debts = [];
$user_id = 1; // For demo purposes

$sql = "SELECT * FROM debt WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $debts[] = $row;
    }
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Debt Repayment Tool</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gradient-to-b from-purple-900 to-purple-700 text-white font-sans">

  <!-- Sticky Navigation -->
  <div class="bg-purple-300 shadow-md w-full py-4 px-8 flex justify-around fixed top-0 left-0 z-50">
    <a href="budget.php" class="text-black px-3 py-2 rounded-md text-sm font-medium hover:bg-purple-700 hover:text-white">
      <i class="fas fa-chart-pie mr-2"></i>Budget Tool
    </a>
    <a href="debt.php" class="text-black px-3 py-2 rounded-md text-sm font-medium hover:bg-purple-700 hover:text-white">
      <i class="fas fa-credit-card mr-2"></i>Debt Repayment
    </a>
    <a href="expense.php" class="text-black px-3 py-2 rounded-md text-sm font-medium hover:bg-purple-700 hover:text-white">
      <i class="fas fa-receipt mr-2"></i>Expense Tracker
    </a>
  </div>

  <!-- Header -->
  <header class="text-center mt-24 relative">
    <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2 opacity-30 z-0">
      <img src="https://img.icons8.com/fluency/240/debt.png" alt="Debt" class="w-40 h-40">
    </div>
    <h1 class="text-5xl font-extrabold relative z-10">Debt Repayment Tool</h1>
    <p class="text-lg mt-2 font-light relative z-10">Track and manage your debt repayment progress</p>
  </header>

  <!-- Alert Messages -->
  <?php if (!empty($errors)): ?>
    <div class="bg-red-500 text-white p-4 rounded-lg mx-auto max-w-4xl mt-4">
      <ul>
        <?php foreach ($errors as $error): ?>
          <li><?php echo $error; ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php if (!empty($success_message)): ?>
    <div class="bg-green-500 text-white p-4 rounded-lg mx-auto max-w-4xl mt-4">
      <?php echo $success_message; ?>
    </div>
  <?php endif; ?>

  <div class="container mx-auto py-8 px-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Debt Tracker Card -->
      <div class="md:col-span-2 bg-white text-gray-800 rounded-lg shadow-lg p-6">
        <h3 class="text-2xl font-bold mb-4 text-purple-800"><i class="fas fa-list-check mr-2"></i>Debt Tracker</h3>
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="mb-6 bg-purple-100 p-4 rounded-lg">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="debt_type" class="block text-sm font-medium text-gray-700 mb-1">Debt Type <span class="text-red-500">*</span></label>
              <input type="text" id="debt_type" name="debt_type" placeholder="Credit Card, Loan, etc." class="w-full px-3 py-2 rounded-md border border-gray-300" required>
            </div>
            <div>
              <label for="amount_owed" class="block text-sm font-medium text-gray-700 mb-1">Amount Owed <span class="text-red-500">*</span></label>
              <input type="number" id="amount_owed" name="amount_owed" placeholder="Total amount" step="0.01" class="w-full px-3 py-2 rounded-md border border-gray-300" required>
            </div>
            <div>
              <label for="interest_rate" class="block text-sm font-medium text-gray-700 mb-1">Interest Rate (%) <span class="text-red-500">*</span></label>
              <input type="number" id="interest_rate" name="interest_rate" placeholder="Annual interest rate" step="0.01" class="w-full px-3 py-2 rounded-md border border-gray-300" required>
            </div>
            <div>
              <label for="min_payment" class="block text-sm font-medium text-gray-700 mb-1">Minimum Payment <span class="text-red-500">*</span></label>
              <input type="number" id="min_payment" name="min_payment" placeholder="Monthly payment" step="0.01" class="w-full px-3 py-2 rounded-md border border-gray-300" required>
            </div>
            <div>
              <label for="progress" class="block text-sm font-medium text-gray-700 mb-1">Progress (%)</label>
              <input type="number" id="progress" name="progress" placeholder="Current progress" min="0" max="100" class="w-full px-3 py-2 rounded-md border border-gray-300">
            </div>
            <div class="flex items-end">
              <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md">Add Debt</button>
            </div>
          </div>
        </form>
        
        <div class="overflow-x-auto">
          <table class="min-w-full table-auto border-collapse">
            <thead>
              <tr class="bg-purple-800 text-white">
                <th class="px-4 py-2 text-left">Debt Type</th>
                <th class="px-4 py-2 text-left">Amount Owed</th>
                <th class="px-4 py-2 text-left">Interest Rate (%)</th>
                <th class="px-4 py-2 text-left">Minimum Payment</th>
                <th class="px-4 py-2 text-left">Progress (%)</th>
                <th class="px-4 py-2 text-left">Status</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($debts)): ?>
                <tr>
                  <td colspan="6" class="px-4 py-2 text-center text-gray-500">No debts added yet. Add your first debt above.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($debts as $debt): ?>
                  <tr class="border-b border-gray-200">
                    <td class="px-4 py-2"><?php echo htmlspecialchars($debt['debt_type']); ?></td>
                    <td class="px-4 py-2">$<?php echo number_format($debt['amount_owed'], 2); ?></td>
                    <td class="px-4 py-2"><?php echo number_format($debt['interest_rate'], 2); ?>%</td>
                    <td class="px-4 py-2">$<?php echo number_format($debt['min_payment'], 2); ?></td>
                    <td class="px-4 py-2">
                      <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-green-600 h-2.5 rounded-full" style="width: <?php echo $debt['progress']; ?>%"></div>
                      </div>
                      <span class="text-xs"><?php echo number_format($debt['progress'], 0); ?>%</span>
                    </td>
                    <td class="px-4 py-2">
                      <?php if ($debt['progress'] >= 100): ?>
                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Paid Off</span>
                      <?php else: ?>
                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">In Progress</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
      
      <!-- Debt Repayment Strategy Card -->
      <div class="bg-white text-gray-800 rounded-lg shadow-lg p-6">
        <h3 class="text-2xl font-bold mb-4 text-purple-800"><i class="fas fa-lightbulb mr-2"></i>Debt Strategies</h3>
        
        <div class="space-y-4">
          <div class="bg-purple-100 p-4 rounded-lg">
            <h4 class="font-bold text-purple-800">Avalanche Method</h4>
            <p class="text-sm">Pay off debts with the highest interest rates first to minimize interest payments over time.</p>
          </div>
          
          <div class="bg-purple-100 p-4 rounded-lg">
            <h4 class="font-bold text-purple-800">Snowball Method</h4>
            <p class="text-sm">Pay off smallest debts first to build momentum and motivation as you eliminate each debt.</p>
          </div>
          
          <div class="bg-purple-100 p-4 rounded-lg">
            <h4 class="font-bold text-purple-800">Debt Consolidation</h4>
            <p class="text-sm">Combine multiple debts into a single loan with a lower interest rate to simplify payments.</p>
          </div>
          
          <div class="mt-6">
            <img src="finance.jpg" alt="Financial Growth" class="mx-auto">
            <p class="text-center text-sm mt-2 text-gray-600">Track your progress and stay motivated!</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="mt-16 py-8 bg-purple-900 text-center">
    <p>© 2025 Debt Repayment Tool. All rights reserved.</p>
  </footer>

  <script>
    // Client-side form validation
    document.querySelector('form').addEventListener('submit', function(e) {
      const debtType = document.getElementById('debt_type').value;
      const amountOwed = document.getElementById('amount_owed').value;
      const interestRate = document.getElementById('interest_rate').value;
      const minPayment = document.getElementById('min_payment').value;
      
      if (!debtType || !amountOwed || !interestRate || !minPayment) {
        alert('Please fill in all required fields');
        e.preventDefault();
        return false;
      }
      
      if (parseFloat(amountOwed) <= 0) {
        alert('Amount owed must be greater than zero');
        e.preventDefault();
        return false;
      }
      
      if (parseFloat(interestRate) < 0) {
        alert('Interest rate cannot be negative');
        e.preventDefault();
        return false;
      }
      
      if (parseFloat(minPayment) <= 0) {
        alert('Minimum payment must be greater than zero');
        e.preventDefault();
        return false;
      }
      
      return true;
    });
  </script>
</body>
</html>
