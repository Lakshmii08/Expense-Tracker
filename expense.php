<?php
// Include database connection
require_once 'db_connect.php';

// Initialize variables for form data and errors
$errors = [];
$success_message = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if it's a delete request
    if (isset($_POST['delete_id'])) {
        $delete_id = sanitize_input($_POST['delete_id']);
        
        // Delete the expense
        $sql = "DELETE FROM expenses WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $user_id = 1; // For demo purposes
        $stmt->bind_param("ii", $delete_id, $user_id);
        
        if ($stmt->execute()) {
            $success_message = "Expense deleted successfully!";
        } else {
            $errors[] = "Error deleting expense: " . $stmt->error;
        }
        
        $stmt->close();
    } else {
        // It's an add/update request
        $expense_date = isset($_POST['expense_date']) ? sanitize_input($_POST['expense_date']) : '';
        $name = isset($_POST['name']) ? sanitize_input($_POST['name']) : '';
        $category = isset($_POST['category']) ? sanitize_input($_POST['category']) : '';
        $amount = isset($_POST['amount']) ? sanitize_input($_POST['amount']) : 0;
        $description = isset($_POST['description']) ? sanitize_input($_POST['description']) : '';
        
        // Validate required fields
        if (empty($expense_date)) {
            $errors[] = "Expense date is required.";
        }
        
        if (empty($category)) {
            $errors[] = "Category is required.";
        }
        
        // Validate amount
        $error = validate_numeric($amount, "Amount");
        if (!empty($error)) {
            $errors[] = $error;
        }
        
        // If no errors, save to database
        if (empty($errors)) {
            // For demo purposes, using user_id = 1
            $user_id = 1;
            
            $sql = "INSERT INTO expenses (user_id, expense_date, name, category, amount, description) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isssds", $user_id, $expense_date, $name, $category, $amount, $description);
            
            if ($stmt->execute()) {
                $success_message = "Expense saved successfully!";
            } else {
                $errors[] = "Error: " . $stmt->error;
            }
            
            $stmt->close();
        }
    }
}

// Fetch existing expenses
$expenses = [];
$user_id = 1; // For demo purposes

$sql = "SELECT * FROM expenses WHERE user_id = ? ORDER BY expense_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $expenses[] = $row;
    }
}
$stmt->close();

// Calculate total expenses by category
$category_totals = [];
$sql = "SELECT category, SUM(amount) as total FROM expenses WHERE user_id = ? GROUP BY category";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $category_totals[$row['category']] = $row['total'];
    }
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Expense Tracker</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
      <img src="https://img.icons8.com/fluency/240/receipt.png" alt="Receipt" class="w-40 h-40">
    </div>
    <h1 class="text-5xl font-extrabold relative z-10">Expense Tracker</h1>
    <p class="text-lg mt-2 font-light relative z-10">Keep track of your finances</p>
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
      <!-- Expense Table Card -->
      <div class="md:col-span-2 bg-white text-gray-800 rounded-lg shadow-lg p-6">
        <h3 class="text-2xl font-bold mb-4 text-purple-800"><i class="fas fa-list-ul mr-2"></i>Expense List</h3>
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="mb-6 bg-purple-100 p-4 rounded-lg">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label for="expense_date" class="block text-sm font-medium text-gray-700 mb-1">Date <span class="text-red-500">*</span></label>
              <input type="date" id="expense_date" name="expense_date" class="w-full px-3 py-2 rounded-md border border-gray-300" required>
            </div>
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
              <input type="text" id="name" name="name" placeholder="Expense name" class="w-full px-3 py-2 rounded-md border border-gray-300">
            </div>
            <div>
              <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
              <select id="category" name="category" class="w-full px-3 py-2 rounded-md border border-gray-300" required>
                <option value="">Select category</option>
                <option value="food">Food</option>
                <option value="travel">Travel</option>
                <option value="entertainment">Entertainment</option>
                <option value="utilities">Utilities</option>
                <option value="shopping">Shopping</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div>
              <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Amount <span class="text-red-500">*</span></label>
              <input type="number" id="amount" name="amount" placeholder="0.00" step="0.01" class="w-full px-3 py-2 rounded-md border border-gray-300" required>
            </div>
            <div class="md:col-span-2">
              <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <input type="text" id="description" name="description" placeholder="Add description" class="w-full px-3 py-2 rounded-md border border-gray-300">
            </div>
            <div class="flex items-end">
              <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md">Add Expense</button>
            </div>
          </div>
        </form>
        
        <div class="overflow-x-auto">
          <table class="min-w-full table-auto border-collapse">
            <thead>
              <tr class="bg-purple-800 text-white">
                <th class="px-4 py-2 text-left">Date</th>
                <th class="px-4 py-2 text-left">Name</th>
                <th class="px-4 py-2 text-left">Category</th>
                <th class="px-4 py-2 text-left">Amount</th>
                <th class="px-4 py-2 text-left">Description</th>
                <th class="px-4 py-2 text-left">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($expenses)): ?>
                <tr>
                  <td colspan="6" class="px-4 py-2 text-center text-gray-500">No expenses added yet. Add your first expense above.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($expenses as $expense): ?>
                  <tr class="border-b border-gray-200">
                    <td class="px-4 py-2"><?php echo date('M d, Y', strtotime($expense['expense_date'])); ?></td>
                    <td class="px-4 py-2"><?php echo htmlspecialchars($expense['name']); ?></td>
                    <td class="px-4 py-2">
                      <span class="px-2 py-1 rounded-full text-xs 
                        <?php 
                          switch($expense['category']) {
                            case 'food': echo 'bg-green-100 text-green-800'; break;
                            case 'travel': echo 'bg-blue-100 text-blue-800'; break;
                            case 'entertainment': echo 'bg-purple-100 text-purple-800'; break;
                            case 'utilities': echo 'bg-yellow-100 text-yellow-800'; break;
                            case 'shopping': echo 'bg-pink-100 text-pink-800'; break;
                            default: echo 'bg-gray-100 text-gray-800';
                          }
                        ?>">
                        <?php echo ucfirst(htmlspecialchars($expense['category'])); ?>
                      </span>
                    </td>
                    <td class="px-4 py-2">$<?php echo number_format($expense['amount'], 2); ?></td>
                    <td class="px-4 py-2"><?php echo htmlspecialchars($expense['description']); ?></td>
                    <td class="px-4 py-2">
                      <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" onsubmit="return confirm('Are you sure you want to delete this expense?');">
                        <input type="hidden" name="delete_id" value="<?php echo $expense['id']; ?>">
                        <button type="submit" class="text-red-500 hover:text-red-700">
                          <i class="fas fa-trash-alt"></i>
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
      
      <!-- Expense Summary Card -->
      <div class="bg-white text-gray-800 rounded-lg shadow-lg p-6">
        <h3 class="text-2xl font-bold mb-4 text-purple-800"><i class="fas fa-chart-pie mr-2"></i>Expense Summary</h3>
        
        <div class="mb-6">
          <canvas id="expenseChart"></canvas>
        </div>
        
        <div class="space-y-2">
          <h4 class="font-semibold text-purple-800">Category Breakdown</h4>
          <?php if (empty($category_totals)): ?>
            <p class="text-gray-500 text-sm">No expenses recorded yet.</p>
          <?php else: ?>
            <?php foreach ($category_totals as $category => $total): ?>
              <div class="flex justify-between items-center">
                <span class="text-sm"><?php echo ucfirst(htmlspecialchars($category)); ?></span>
                <span class="font-medium">$<?php echo number_format($total, 2); ?></span>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        
        <div class="mt-8">
          <img src="https://img.icons8.com/color/96/money-bag.png" alt="Money Bag" class="mx-auto">
          <p class="text-center text-sm mt-2 text-gray-600">Track your spending to improve your financial health!</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="mt-16 py-8 bg-purple-900 text-center">
    <p>© 2025 Expense Tracker. All rights reserved.</p>
  </footer>

  <script>
    // Client-side form validation
    document.querySelector('form').addEventListener('submit', function(e) {
      if (e.target.querySelector('input[name="delete_id"]')) {
        // This is a delete form, skip validation
        return true;
      }
      
      const expenseDate = document.getElementById('expense_date').value;
      const category = document.getElementById('category').value;
      const amount = document.getElementById('amount').value;
      
      if (!expenseDate || !category || !amount) {
        alert('Please fill in all required fields');
        e.preventDefault();
        return false;
      }
      
      if (parseFloat(amount) <= 0) {
        alert('Amount must be greater than zero');
        e.preventDefault();
        return false;
      }
      
      return true;
    });
    
    // Initialize expense chart
    const ctx = document.getElementById('expenseChart').getContext('2d');
    const expenseChart = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: [
          <?php 
            if (!empty($category_totals)) {
              foreach ($category_totals as $category => $total) {
                echo "'" . ucfirst($category) . "', ";
              }
            } else {
              echo "'No Data'";
            }
          ?>
        ],
        datasets: [{
          data: [
            <?php 
              if (!empty($category_totals)) {
                foreach ($category_totals as $total) {
                  echo $total . ", ";
                }
              } else {
                echo "1";
              }
            ?>
          ],
          backgroundColor: [
            '#4CAF50', // green
            '#2196F3', // blue
            '#9C27B0', // purple
            '#FFC107', // yellow
            '#E91E63', // pink
            '#607D8B'  // gray
          ],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              font: {
                size: 12
              }
            }
          }
        }
      }
    });
  </script>
</body>
</html>
