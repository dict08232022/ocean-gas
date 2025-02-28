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

if (isset($_POST['login'])) {
    // Retrieve and trim form data
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Basic server-side validation
    if (empty($username) || empty($password)) {
        $error = "Please fill in all required fields.";
    } else {
        // Prepare a statement to retrieve the user by username
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        // Check if a user exists with that username
        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $dbUsername, $dbPassword);
            $stmt->fetch();

            // Verify the password using password_verify (since it's hashed)
            if (password_verify($password, $dbPassword)) {
                // Successful login: set session variable and redirect to index.html
                $_SESSION['username'] = $dbUsername;
                header("Location: index.html");
                exit();
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Invalid username or password.";
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        /* Basic styling for the login form */
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
        }
        .login-container {
            width: 300px;
            margin: 80px auto;
            background: #fff;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
        }
        .login-container label {
            display: block;
            margin-top: 10px;
        }
        .login-container input[type="text"],
        .login-container input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }
        .login-container .required {
            color: red;
        }
        .error {
            color: red;
            font-size: 0.9em;
            text-align: center;
        }
        .login-container button {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            margin-top: 15px;
            border: none;
            cursor: pointer;
            width: 100%;
        }
        .register-link, .forgot-password-link {
            text-align: center;
            margin-top: 15px;
        }
        .register-link a, .forgot-password-link a {
            color: #008CBA;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php
            if (isset($error)) {
                echo "<p class='error'>{$error}</p>";
            }
        ?>
        <form id="loginForm" method="POST" action="login.php" novalidate>
            <label for="username">
                Username: <span class="required">*</span>
            </label>
            <input type="text" id="username" name="username">
            
            <label for="password">
                Password: <span class="required">*</span>
            </label>
            <input type="password" id="password" name="password">
            
            <button type="submit" name="login">Login</button>
        </form>
        <div class="register-link">
            <p>Don't have an account? <a href="register.php">Register</a></p>
        </div>
        <div class="forgot-password-link">
            <p>Forgot your password? <a href="forgot_password.php">Click here</a> to reset it.</p>
        </div>
    </div>
</body>
</html>
