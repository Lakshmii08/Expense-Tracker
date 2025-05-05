<?php
// Database connection parameters
$servername = "localhost";
$username = "root"; // Change to your database username
$password = ""; // Change to your database password
$dbname = "financial_tools";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to validate numeric input
function validate_numeric($data, $field_name) {
    if (!is_numeric($data)) {
        return "$field_name must be a number.";
    }
    if ($data < 0) {
        return "$field_name cannot be negative.";
    }
    return "";
}

// Create database and tables if they don't exist
function setup_database($conn) {
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS financial_tools";
    $conn->query($sql);
    
    // Select the database
    $conn->select_db("financial_tools");
    
    // Create budget table
    $sql = "CREATE TABLE IF NOT EXISTS budget (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        salary DECIMAL(10,2) NOT NULL,
        other_income DECIMAL(10,2) DEFAULT 0,
        housing DECIMAL(10,2) DEFAULT 0,
        transportation DECIMAL(10,2) DEFAULT 0,
        education DECIMAL(10,2) DEFAULT 0,
        personal DECIMAL(10,2) DEFAULT 0,
        savings DECIMAL(10,2) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($sql);
    
    // Create debt table
    $sql = "CREATE TABLE IF NOT EXISTS debt (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        debt_type VARCHAR(100) NOT NULL,
        amount_owed DECIMAL(10,2) NOT NULL,
        interest_rate DECIMAL(5,2) NOT NULL,
        min_payment DECIMAL(10,2) NOT NULL,
        progress DECIMAL(5,2) DEFAULT 0,
        status VARCHAR(50) DEFAULT 'In Progress',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($sql);
    
    // Create expenses table
    $sql = "CREATE TABLE IF NOT EXISTS expenses (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        expense_date DATE NOT NULL,
        name VARCHAR(100) NOT NULL,
        category VARCHAR(50) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($sql);
}

// Setup the database
setup_database($conn);
?>
