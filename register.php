<?php
session_start();

// Database connection settings
$host = 'localhost';
$db   = 'myapp_db';
$user = 'root';
$pass = '';

// Create a new database connection using MySQLi
$conn = new mysqli($host, $user, $pass, $db);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$success = "";  // Initialize a variable to hold the success message

if (isset($_POST['register'])) {
    // Retrieve and trim form data
    $username         = trim($_POST['username']);
    $email            = trim($_POST['email']);
    $password         = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Server-side validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if the username or email already exists
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $checkStmt->bind_param("ss", $username, $email);
        $checkStmt->execute();
        $checkStmt->store_result();
        if ($checkStmt->num_rows > 0) {
            $error = "Username or email already exists.";
        } else {
            // Hash the password for security
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Prepare and execute the INSERT query
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashedPassword);
            
            if ($stmt->execute()) {
                // Registration successful: set the success message.
                $success = "Registration successful. Redirecting to login page...";
            } else {
                $error = "Registration failed. Please try again.";
            }
            $stmt->close();
        }
        $checkStmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <?php
    // If registration was successful, include a meta refresh tag to redirect after 3 seconds.
    if (!empty($success)) {
        echo '<meta http-equiv="refresh" content="3;url=login.php">';
    }
    ?>
    <style>
        /* Basic styling for the register form */
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
        }
        .register-container {
            width: 300px;
            margin: 80px auto;
            background: #fff;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
        }
        .register-container label {
            display: block;
            margin-top: 10px;
        }
        .register-container input[type="text"],
        .register-container input[type="email"],
        .register-container input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }
        .register-container .required {
            color: red;
        }
        .error {
            color: red;
            font-size: 0.9em;
            text-align: center;
        }
        .success {
            color: green;
            font-size: 0.9em;
            text-align: center;
        }
        .register-container button {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            margin-top: 15px;
            border: none;
            cursor: pointer;
            width: 100%;
        }
        .login-link {
            text-align: center;
            margin-top: 15px;
        }
        .login-link a {
            color: #008CBA;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Register</h2>
        <?php
            if (isset($error)) {
                echo "<p class='error'>{$error}</p>";
            }
            if (!empty($success)) {
                echo "<p class='success'>{$success}</p>";
            }
        ?>
        <form id="registerForm" method="POST" action="register.php" novalidate>
            <label for="username">
                Username: <span class="required">*</span>
            </label>
            <input type="text" id="username" name="username">
            <span id="usernameError" class="error"></span>

            <label for="email">
                Email: <span class="required">*</span>
            </label>
            <input type="email" id="email" name="email">
            <span id="emailError" class="error"></span>

            <label for="password">
                Password: <span class="required">*</span>
            </label>
            <input type="password" id="password" name="password">
            <span id="passwordError" class="error"></span>

            <label for="confirm_password">
                Confirm Password: <span class="required">*</span>
            </label>
            <input type="password" id="confirm_password" name="confirm_password">
            <span id="confirmPasswordError" class="error"></span>

            <button type="submit" name="register">Register</button>
        </form>
        <div class="login-link">
            <p>Already have an account? <a href="login.php">Login</a></p>
        </div>
    </div>

    <script>
        // Client-side validation using JavaScript
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            // Clear previous error messages
            document.getElementById('usernameError').innerText = '';
            document.getElementById('emailError').innerText = '';
            document.getElementById('passwordError').innerText = '';
            document.getElementById('confirmPasswordError').innerText = '';

            let valid = true;
            let username = document.getElementById('username').value.trim();
            let email = document.getElementById('email').value.trim();
            let password = document.getElementById('password').value.trim();
            let confirmPassword = document.getElementById('confirm_password').value.trim();

            // Validate username field
            if (username === '') {
                document.getElementById('usernameError').innerText = 'Username is required.';
                valid = false;
            }

            // Validate email field
            if (email === '') {
                document.getElementById('emailError').innerText = 'Email is required.';
                valid = false;
            } else {
                // Simple email format check
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(email)) {
                    document.getElementById('emailError').innerText = 'Enter a valid email address.';
                    valid = false;
                }
            }

            // Validate password field
            if (password === '') {
                document.getElementById('passwordError').innerText = 'Password is required.';
                valid = false;
            }

            // Validate confirm password field
            if (confirmPassword === '') {
                document.getElementById('confirmPasswordError').innerText = 'Please confirm your password.';
                valid = false;
            } else if (password !== confirmPassword) {
                document.getElementById('confirmPasswordError').innerText = 'Passwords do not match.';
                valid = false;
            }

            // Prevent form submission if any field is invalid
            if (!valid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
