<?php
// IMPORTANT: NO whitespace or HTML before this opening PHP tag!

// Start session first - before ANY output
session_start();
require_once 'config.php';

// Check if already logged in as admin
if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin_dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Admin credentials (you can change these)
    $admin_username = 'admin';
    $admin_password_hash = password_hash('admin123', PASSWORD_DEFAULT);
    
    if($username === $admin_username && password_verify($password, $admin_password_hash)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Invalid admin credentials!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Parking System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-login-container {
            max-width: 400px;
            margin: 100px auto;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .admin-login-container h2 {
            text-align: center;
            color: #667eea;
            margin-bottom: 30px;
        }
        .admin-login-container .logo {
            text-align: center;
            font-size: 3rem;
            margin-bottom: 20px;
        }
        .error {
            background: #fed7d7;
            color: #c53030;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
            display: block;
            color: #667eea;
            text-decoration: none;
        }
        /* Enhanced textbox styles */
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            margin: 8px 0;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 8px rgba(102, 126, 234, 0.3);
        }
        input[type="text"]:hover,
        input[type="password"]:hover {
            border-color: #764ba2;
        }
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        button:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="admin-login-container">
        <div class="logo">👑</div>
        <h2>Admin Panel Login</h2>
        <?php if($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Admin Username" required>
            <input type="password" name="password" placeholder="Admin Password" required>
            <button type="submit">Login to Admin Panel</button>
        </form>
        <a href="index.php" class="back-link">← Back to Website</a>
    </div>
</body>
</html>