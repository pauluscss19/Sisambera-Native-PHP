<?php
require_once '../config/config.php';

// If already logged in, redirect to dashboard
if (isAdminLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = md5($_POST['password']); // MD5 hash
    
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT admin_id, nama, username, shift FROM admin WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['admin_nama'] = $admin['nama'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_shift'] = $admin['shift'];
        
        header('Location: dashboard.php');
        exit();
    } else {
        $error = 'Username atau password salah!';
    }
    
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Sambal Belut Bu Raden</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #e8e8e8;
        }

        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .login-box {
            background: white;
            padding: 60px 80px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 600px;
        }

        .logo {
            width: 100px;
            height: 100px;
            background-color: #2d7d67;
            border-radius: 50%;
            margin: 0 auto 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 40px;
        }

        .login-title {
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 40px;
        }

        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 30px;
        }

        .form-group label {
            display: block;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .form-group input {
            width: 100%;
            padding: 18px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 8px;
            outline: none;
        }

        .form-group input:focus {
            border-color: #2d7d67;
        }

        .password-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            font-size: 20px;
        }

        .login-btn {
            width: 100%;
            padding: 18px;
            background-color: #5d3a3a;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 40px;
        }

        .login-btn:hover {
            background-color: #4a2e2e;
        }

        .default-credentials {
            margin-top: 20px;
            padding: 15px;
            background-color: #e8f5e9;
            border-radius: 8px;
            font-size: 14px;
            text-align: center;
        }

        .default-credentials strong {
            color: #2d7d67;
        }

        @media (max-width: 768px) {
            .login-box {
                padding: 40px 30px;
                margin: 20px;
            }

            .login-title {
                font-size: 24px;
            }

            .form-group label {
                font-size: 18px;
            }

            .form-group input {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="logo">🍜</div>
            <h1 class="login-title">Admin Login</h1>
            
            <?php if($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" required>
                        <span class="password-toggle" onclick="togglePassword()">👁️</span>
                    </div>
                </div>
                
                <button type="submit" class="login-btn">Login</button>
            </form>
            
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggle = document.querySelector('.password-toggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggle.textContent = '👁️‍🗨️';
            } else {
                passwordInput.type = 'password';
                toggle.textContent = '👁️';
            }
        }
    </script>
</body>
</html>
