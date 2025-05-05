<?php
// Include database connection
require_once 'db_connect.php';

// Initialize variables for form data and errors
$errors = [];
$success_message = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize income data
    $salary = isset($_POST['salary']) ? sanitize_input($_POST['salary']) : 0;
    $other_income = isset($_POST['other_income']) ? sanitize_input($_POST['other_income']) : 0;
    
    // Validate and sanitize housing data
    $mortgage = isset($_POST['mortgage']) ? sanitize_input($_POST['mortgage']) : 0;
    $rent = isset($_POST['rent']) ? sanitize_input($_POST['rent']) : 0;
    $insurance = isset($_POST['insurance']) ? sanitize_input($_POST['insurance']) : 0;
    $repairs = isset($_POST['repairs']) ? sanitize_input($_POST['repairs']) : 0;
    $utilities = isset($_POST['Water/Gas/Electricity']) ? sanitize_input($_POST['Water/Gas/Electricity']) : 0;
    $cable = isset($_POST['Cable/tv/internet']) ? sanitize_input($_POST['Cable/tv/internet']) : 0;
    $phone = isset($_POST['Phone/Cell']) ? sanitize_input($_POST['Phone/Cell']) : 0;
    
    // Validate and sanitize transportation data
    $car_payment = isset($_POST['car-payment']) ? sanitize_input($_POST['car-payment']) : 0;
    $fuel = isset($_POST['fuel']) ? sanitize_input($_POST['fuel']) : 0;
    $car_insurance = isset($_POST['car-insurance']) ? sanitize_input($_POST['car-insurance']) : 0;
    $car_repairs = isset($_POST['car-repairs']) ? sanitize_input($_POST['car-repairs']) : 0;
    
    // Validate and sanitize education data
    $student_loan = isset($_POST['student-loan']) ? sanitize_input($_POST['student-loan']) : 0;
    $books = isset($_POST['books']) ? sanitize_input($_POST['books']) : 0;
    $college_tuition = isset($_POST['college-tuition']) ? sanitize_input($_POST['college-tuition']) : 0;
    
    // Validate and sanitize personal data
    $groceries = isset($_POST['groceries']) ? sanitize_input($_POST['groceries']) : 0;
    $entertainment = isset($_POST['entertainment']) ? sanitize_input($_POST['entertainment']) : 0;
    $clothing = isset($_POST['clothing']) ? sanitize_input($_POST['clothing']) : 0;
    $medical = isset($_POST['medical']) ? sanitize_input($_POST['medical']) : 0;
    $pet_supplies = isset($_POST['pet-supplies']) ? sanitize_input($_POST['pet-supplies']) : 0;
    $other = isset($_POST['other']) ? sanitize_input($_POST['other']) : 0;
    
    // Validate and sanitize savings data
    $retirement = isset($_POST['retirement']) ? sanitize_input($_POST['retirement']) : 0;
    $emergency_fund = isset($_POST['emergency-fund']) ? sanitize_input($_POST['emergency-fund']) : 0;
    $investments = isset($_POST['investments']) ? sanitize_input($_POST['investments']) : 0;
    
    // Validate numeric fields
    $fields_to_validate = [
        'salary' => $salary,
        'other_income' => $other_income,
        'mortgage' => $mortgage,
        'rent' => $rent,
        'insurance' => $insurance,
        'repairs' => $repairs,
        'utilities' => $utilities,
        'cable' => $cable,
        'phone' => $phone,
        'car_payment' => $car_payment,
        'fuel' => $fuel,
        'car_insurance' => $car_insurance,
        'car_repairs' => $car_repairs,
        'student_loan' => $student_loan,
        'books' => $books,
        'college_tuition' => $college_tuition,
        'groceries' => $groceries,
        'entertainment' => $entertainment,
        'clothing' => $clothing,
        'medical' => $medical,
        'pet_supplies' => $pet_supplies,
        'other' => $other,
        'retirement' => $retirement,
        'emergency_fund' => $emergency_fund,
        'investments' => $investments
    ];
    
    foreach ($fields_to_validate as $field => $value) {
        $error = validate_numeric($value, ucfirst(str_replace('_', ' ', $field)));
        if (!empty($error)) {
            $errors[] = $error;
        }
    }
    
    // Calculate totals
    $housing_total = $mortgage + $rent + $insurance + $repairs + $utilities + $cable + $phone;
    $transportation_total = $car_payment + $fuel + $car_insurance + $car_repairs;
    $education_total = $student_loan + $books + $college_tuition;
    $personal_total = $groceries + $entertainment + $clothing + $medical + $pet_supplies + $other;
    $savings_total = $retirement + $emergency_fund + $investments;
    
    // If no errors, save to database
    if (empty($errors)) {
        // For demo purposes, using user_id = 1
        $user_id = 1;
        
        $sql = "INSERT INTO budget (user_id, salary, other_income, housing, transportation, education, personal, savings) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iddddddd", $user_id, $salary, $other_income, $housing_total, $transportation_total, $education_total, $personal_total, $savings_total);
        
        if ($stmt->execute()) {
            $success_message = "Budget data saved successfully!";
        } else {
            $errors[] = "Error: " . $stmt->error;
        }
        
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Personal Budget Tool</title>
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
      <img src="https://img.icons8.com/fluency/240/financial-growth.png" alt="Financial Growth" class="w-40 h-40">
    </div>
    <h1 class="text-5xl font-extrabold relative z-10">Personal Budget Tool</h1>
    <p class="text-lg mt-2 font-light relative z-10">Plan your finances effectively</p>
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

  <!-- Main Container -->
  <div class="main-container flex flex-wrap sm:flex-nowrap justify-center items-start mt-12 px-8 gap-8">
    <!-- Pie Chart -->
    <div class="chart-container w-full sm:w-1/3 bg-purple-800 p-6 rounded-lg shadow-lg">
      <h3 class="text-2xl font-semibold text-center mb-4">Monthly Budget Overview</h3>
      <canvas id="pieChart"></canvas>
      <button id="net-income" class="mt-6 w-full py-2 bg-purple-600 rounded-lg text-white font-medium">
        NET INCOME: $<span id="net-income-amount">0</span>
      </button>
      <div class="mt-4 p-4 bg-purple-900 rounded-lg">
        <h4 class="font-semibold mb-2"><i class="fas fa-lightbulb text-yellow-300 mr-2"></i>Budget Tips</h4>
        <ul class="text-sm space-y-2">
          <li><i class="fas fa-check-circle text-green-400 mr-1"></i> Aim to save at least 20% of your income</li>
          <li><i class="fas fa-check-circle text-green-400 mr-1"></i> Keep housing costs under 30% of income</li>
          <li><i class="fas fa-check-circle text-green-400 mr-1"></i> Build an emergency fund of 3-6 months expenses</li>
        </ul>
      </div>
    </div>

    <!-- Form Container -->
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="form-content w-full sm:w-2/3 p-6 bg-purple-800 rounded-lg shadow-lg">
      <!-- Progress Bar -->
      <div class="progress-bar flex items-center justify-between mb-8">
        <div class="step flex flex-col items-center">
          <div class="circle w-10 h-10 flex items-center justify-center bg-green-500 text-white rounded-full font-bold">1</div>
          <p class="mt-2 text-sm">Income</p>
        </div>
        <div class="line flex-grow h-1 bg-gray-300 mx-2"></div>
        <div class="step flex flex-col items-center">
          <div class="circle w-10 h-10 flex items-center justify-center bg-gray-300 text-purple-700 rounded-full font-bold">2</div>
          <p class="mt-2 text-sm">Housing</p>
        </div>
        <div class="line flex-grow h-1 bg-gray-300 mx-2"></div>
        <div class="step flex flex-col items-center">
          <div class="circle w-10 h-10 flex items-center justify-center bg-gray-300 text-purple-700 rounded-full font-bold">3</div>
          <p class="mt-2 text-sm">Transportation</p>
        </div>
        <div class="line flex-grow h-1 bg-gray-300 mx-2"></div>
        <div class="step flex flex-col items-center">
          <div class="circle w-10 h-10 flex items-center justify-center bg-gray-300 text-purple-700 rounded-full font-bold">4</div>
          <p class="mt-2 text-sm">Educational</p>
        </div>
        <div class="line flex-grow h-1 bg-gray-300 mx-2"></div>
        <div class="step flex flex-col items-center">
          <div class="circle w-10 h-10 flex items-center justify-center bg-gray-300 text-purple-700 rounded-full font-bold">5</div>
          <p class="mt-2 text-sm">Personal</p>
        </div>
        <div class="line flex-grow h-1 bg-gray-300 mx-2"></div>
        <div class="step flex flex-col items-center">
          <div class="circle w-10 h-10 flex items-center justify-center bg-gray-300 text-purple-700 rounded-full font-bold">6</div>
          <p class="mt-2 text-sm">Savings</p>
        </div>
      </div>

      <!-- Forms for Each Step -->
      <div class="form-steps">
        <div class="form-step active">
          <h2 class="text-2xl font-bold mb-4"><i class="fas fa-money-bill-wave mr-2"></i>Monthly Income</h2>
          <label for="salary" class="block text-sm mb-2">Salary/Wages <span class="text-red-400">*</span></label>
          <input type="number" id="salary" name="salary" placeholder="Enter salary" class="w-full px-3 py-2 rounded-md bg-purple-900 text-white mb-4" required>
          <label for="other-income" class="block text-sm mb-2">Other Income</label>
          <input type="number" id="other-income" name="other_income" placeholder="Enter other income" class="w-full px-3 py-2 rounded-md bg-purple-900 text-white mb-4">
          <div class="form-buttons flex justify-end">
            <button type="button" class="next-step px-6 py-2 bg-purple-600 rounded-lg text-white font-medium">Next</button>
          </div>
        </div>

        <!-- Step 2: Housing -->
        <div class="form-step hidden">
          <h2 class="text-2xl font-bold mb-4"><i class="fas fa-home mr-2"></i>Housing Expenses</h2>
          <label for="mortgage" class="block text-sm mb-2">Mortgage</label>
          <input type="number" id="mortgage" name="mortgage" placeholder="Enter mortgage amount" class="w-full px-3 py-2 rounded-md bg-purple-900 text-white mb-4">
          <label for="rent" class="block text-sm mb-2">Rent</label>
          <input type="number" id="rent" name="rent" placeholder="Enter rent amount" class="w-full px-3 py-2 rounded-md bg-purple-900 text-white mb-4">
          <label for="home-insurance" class="block text-sm mb-2">Home Insurance</label>
          <input type="number" id="insurance" name="insurance" placeholder="Enter Home Insurance amount" class="w-full px-3 py-2 rounded-md bg-purple-900 text-white mb-4">
          <label for="Repairs/Maintenance" class="block text-sm mb-2">Repairs/Maintenance</label>
          <input type="number" id="repairs" name="repairs" placeholder="Enter Repairs/Maintenance amount" class="w-full px-3 py-2 rounded-md bg-purple-900 text-white mb-4">
          <label for="Water/Gas/Electricity" class="block text-sm mb-2">Water/Gas/Electricity</label>
          <input type="number" id="Water/Gas/Electricity" name="Water/Gas/Electricity" placeholder="Enter Water/Gas/Electricity amount" class="w-full px-3 py-2 rounded-md bg-purple-900 text-white mb-4">
          <label for="Cable/tv/internet" class="block text-sm mb-2">Cable/tv/internet</label>
          <input type="number" id="Cable/tv/internet" name="Cable/tv/internet" placeholder="Enter Cable/tv/internet amount" class="w-full px-3 py-2 rounded-md bg-purple-900 text-white mb-4">
          <label for="Phone/Cell" class="block text-sm mb-2">Phone/Cell</label>
          <input type="number" id="Phone/Cell" name="Phone/Cell" placeholder="Enter Phone/Cell amount" class="w-full px-3 py-2 rounded-md bg-purple-900 text-white mb-4">
          <div class="form-buttons flex justify-between">
            <button type="button" class="prev-step px-6 py-2 bg-purple-600 rounded-lg text-white font-medium">Back</button>
            <button type="button" class="next-step px-6 py-2 bg-purple-600 rounded-lg text-white font-medium">Next</button>
          </div>
        </div>

        <!-- Step 3: Transportation -->
        <div class="form-step hidden">
          <h2 class="text-2xl font-bold mb-4"><i class="fas fa-car mr-2"></i>Transportation Expenses</h2>
          <label for="car-payment" class="block text-sm mb-2">Car Payment</label>
          <input type="number" id="car-payment" name="car-payment" placeholder="Enter car payment" class="w-full px-3 py-2 rounded-md bg-purple-900 text-white mb-4">
          <label for="fuel" class="block text-sm mb-2">Gas/Fuel</label>
          <input type="number" id="fuel" name="fuel" placeholder="Enter fuel amount" class="w-full px-3 py-2 rounded-md bg-purple-900 text-white mb-4">
          <label for="Car Insurance" class="block text-sm mb-2">Car Insurance</label>
          <input type="number" id="car-insurance" name="car-insurance" placeholder="Enter Car Insurance amount" class="w-full px-3 py-2 rounded-md bg-purple-900 text-white mb-4">
          <label for="Car Repairs" class="block text-sm mb-2">Car Repairs</label>
          <input type="number" id="car-repairs" name="car-repairs" placeholder="Enter Car Repairs amount" class="w-full px-3 py-2 rounded-md bg-purple-900 text-white mb-4">
          <div class="form-buttons flex justify-between">
            <button type="button" class="prev-step px-6 py-2 bg-purple-600 rounded-lg text-white font-medium">Back</button>
            <button type="button" class="next-step px-6 py-2 bg-purple-600 rounded-lg text-white font-medium">Next</button>
          </div>
        </div>

        <!-- Step 4: Educational -->
        <div class="form-step hidden">
          <h2 class="text-2xl font-bold mb-4"><i class="fas fa-graduation-cap mr-2"></i>Educational Expenses</h2>
          <label for="student-loan" class="block text-sm mb-2">Student Loan</label>
          <input type="number" id="student-loan" name="student-loan" placeholder="Enter student loan amount" class="w-full px-3 py-2 rounded-md bg-purple-900 text-white mb-4">
          <label for="books" class="block text-sm mb-2">Books & Supplies</label>
          <input type="number" id="books" name="books" placeholder="Enter books amount" class="w-full px-3 py-2 rounded-md bg-purple-900 text-white mb-4">
          <label for="College Tuition" class="block text-sm mb-2">College Tuition</label>
          <input type="number" id="college-tuition" name="college-tuition" placeholder="Enter College Tuition amount" class="w-full px-3 py-2 rounded-md bg-purple-900 text-white mb-4">
          <div class="form-buttons flex justify-between">
            <button type="button" class="prev-step px-6 py-2 bg-purple-600 rounded-lg text-white font-medium">Back</button>
            <button type="button" class="next-step px-6 py-2 bg-purple-600 rounded-lg text-white font-medium">Next</button>
          </div>
        </div>

        <!-- Step 5: Personal -->
        <div class="form-step hidden">
          <h2 class="text-2xl font-bold mb-4"><i class="fas fa-shopping-cart mr-2"></i>Personal & Food Expenses</h2>
          <label for="groceries" class="block text-sm mb-2">Groceries</label>
          <input type="number" id="groceries" name="groceries" placeholder="Enter groceries amount" class="w-full px-3 py-2 rounded-md bg-purple-900 text-white mb-4">
          <label for="entertainment" class="block text-sm mb-2">Entertainment</label>
          <input type="number" id="entertainment" name="entertainment" placeholder="Enter entertainment amount" class="w-full px-3 py-2 rounded-md bg-purple-900 text-white mb-4">
          <label for="Clothing" class="block text-sm mb-2">Clothing</label>
          <input type="number" id="clothing" name="clothing" placeholder="Enter Clothing amount" class="w-full px-3 py-2 rounded-md bg-purple-900 text-white mb-4">
          <label for="Medical" class="block text-sm mb-2">Medical</label>
          <input type="number" id="medical" name="medical" placeholder="Enter Medical amount" class="w-full px-3 py-2 rounded-md bg-purple-900 text-white mb-4">
          <label for="Pet supplies" class="block text-sm mb-2">Pet supplies</label>
          <input type="number" id="pet-supplies" name="pet-supplies" placeholder="Enter Pet supplies amount" class="w-full px-3 py-2 rounded-md bg-purple-900 text-white mb-4">
          <label for="other" class="block text-sm mb-2">Other expenses</label>
          <input type="number" id="other" name="other" placeholder="Enter other amount" class="w-full px-3 py-2 rounded-md bg-purple-900 text-white mb-4">
          <div class="form-buttons flex justify-between">
            <button type="button" class="prev-step px-6 py-2 bg-purple-600 rounded-lg text-white font-medium">Back</button>
            <button type="button" class="next-step px-6 py-2 bg-purple-600 rounded-lg text-white font-medium">Next</button>
          </div>
        </div>

        <!-- Step 6: Savings -->
        <div class="form-step hidden">
          <h2 class="text-2xl font-bold mb-4"><i class="fas fa-piggy-bank mr-2"></i>Monthly Savings</h2>
          <label for="retirement" class="block text-sm mb-2">Retirement Savings</label>
          <input type="number" id="retirement" name="retirement" placeholder="Enter retirement savings" class="w-full px-3 py-2 rounded-md bg-purple-900 text-white mb-4">
          <label for="emergency-fund" class="block text-sm mb-2">Emergency Fund</label>
          <input type="number" id="emergency-fund" name="emergency-fund" placeholder="Enter emergency fund amount" class="w-full px-3 py-2 rounded-md bg-purple-900 text-white mb-4">
          <label for="Investments" class="block text-sm mb-2">Investments</label>
          <input type="number" id="investments" name="investments" placeholder="Enter Investments amount" class="w-full px-3 py-2 rounded-md bg-purple-900 text-white mb-4">
          <div class="form-buttons flex justify-between">
            <button type="button" class="prev-step px-6 py-2 bg-purple-600 rounded-lg text-white font-medium">Back</button>
            <button type="submit" class="px-6 py-2 bg-green-600 rounded-lg text-white font-medium">Submit</button>
          </div>
        </div>
      </div>
    </form>
  </div>

  <!-- Footer -->
  <footer class="mt-16 py-8 bg-purple-900 text-center">
    <p>© 2025 Personal Budget Tool. All rights reserved.</p>
  </footer>
  
  <script>
    const steps = document.querySelectorAll('.form-step');
    const nextButtons = document.querySelectorAll('.next-step');
    const prevButtons = document.querySelectorAll('.prev-step');
    const progressCircles = document.querySelectorAll('.circle');
    let currentStep = 0;
    
    function showStep(step) {
        steps.forEach((formStep, index) => {
        formStep.classList.toggle('hidden', index !== step);
        formStep.classList.toggle('active', index === step);
        });
        progressCircles.forEach((circle, index) => {
        circle.classList.toggle('bg-green-500', index <= step);
        circle.classList.toggle('bg-gray-300', index > step);
        });
    }
    
    // Global object to store budget data
    const budgetData = {
        income: 0,
        housing: 0,
        transportation: 0,
        education: 0,
        personal: 0,
        savings: 0
    };
    
    // Pie Chart Initialization
    const ctx = document.getElementById('pieChart').getContext('2d');
    const pieChart = new Chart(ctx, {
    type: 'pie',
    data: {
        labels: ['Income', 'Housing', 'Transportation', 'Education', 'Personal', 'Savings'],
        datasets: [{
        data: [0, 0, 0, 0, 0, 0],
        backgroundColor: ['#4CAF50', '#FF9800', '#2196F3', '#FFC107', '#E91E63', '#9C27B0'],
        borderWidth: 0 // Removes the white border around pie chart segments
        }]
    },
    options: {
        responsive: true,
        plugins: {
        legend: {
            position: 'bottom',
            labels: {
            color: 'white', // Set legend label color to white
            font: {
                size: 14 // Optional: Adjust font size for better visibility
            }
            }
        }
        }
    }
    });

    
    // Update Pie Chart
    function updateChart() {
        pieChart.data.datasets[0].data = Object.values(budgetData);
        pieChart.update();
    }
    
    // Update Budget Data and Chart on "Next" Button Click
    nextButtons.forEach(button => {
        button.addEventListener('click', () => {
        if (currentStep === 0) {
            budgetData.income = parseFloat(document.getElementById('salary').value || 0) +
                                parseFloat(document.getElementById('other-income').value || 0);
        } else if (currentStep === 1) {
            budgetData.housing = parseFloat(document.getElementById('mortgage').value || 0) +
                                parseFloat(document.getElementById('rent').value || 0) +
                                parseFloat(document.getElementById('insurance').value || 0)+
                                parseFloat(document.getElementById('repairs').value || 0)+
                                parseFloat(document.getElementById('Water/Gas/Electricity').value || 0)+
                                parseFloat(document.getElementById('Cable/tv/internet').value || 0) +
                                parseFloat(document.getElementById('Phone/Cell').value || 0);
        } else if (currentStep === 2) {
            budgetData.transportation = parseFloat(document.getElementById('fuel').value || 0) +
                                        parseFloat(document.getElementById('car-payment').value || 0) +
                                        parseFloat(document.getElementById('car-insurance').value || 0)+
                                        parseFloat(document.getElementById('car-repairs').value || 0);
        } else if (currentStep === 3) {
            budgetData.education = parseFloat(document.getElementById('student-loan').value || 0) +
                                parseFloat(document.getElementById('books').value || 0) +
                                parseFloat(document.getElementById('college-tuition').value || 0);
        } else if (currentStep === 4) {
            budgetData.personal = parseFloat(document.getElementById('groceries').value || 0) +
                                parseFloat(document.getElementById('entertainment').value || 0) +
                                parseFloat(document.getElementById('clothing').value || 0) +
                                parseFloat(document.getElementById('medical').value || 0) +
                                parseFloat(document.getElementById('pet-supplies').value || 0) +
                                parseFloat(document.getElementById('other').value || 0);
        } else if (currentStep === 5) {
            budgetData.savings = parseFloat(document.getElementById('retirement').value || 0) +
                                parseFloat(document.getElementById('emergency-fund').value || 0) +
                                parseFloat(document.getElementById('investments').value || 0);
        }
    
        // Calculate net income
        const totalExpenses = budgetData.housing + budgetData.transportation + 
                            budgetData.education + budgetData.personal + budgetData.savings;
        const netIncome = budgetData.income - totalExpenses;
        document.getElementById('net-income-amount').textContent = netIncome.toFixed(2);
        
        if (currentStep < steps.length - 1) {
            currentStep++;
            showStep(currentStep);
            updateChart();
        }
        });
    });
    
    // Go Back to Previous Step
    prevButtons.forEach(button => {
        button.addEventListener('click', () => {
        if (currentStep > 0) {
            currentStep--;
            showStep(currentStep);
        }
        });
    });
    
    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        // Basic validation
        const salary = document.getElementById('salary').value;
        if (!salary || isNaN(salary) || parseFloat(salary) <= 0) {
            alert('Please enter a valid salary amount');
            e.preventDefault();
            return false;
        }
        
        // Additional validation can be added here
        
        return true;
    });
    
    // Initialize Chart and Show First Step
    showStep(currentStep);
    updateChart();
  </script>
</body>
</html>
