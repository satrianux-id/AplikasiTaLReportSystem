<?php
error_reporting(E_ALL);
require_once __DIR__ . '/../includes/config.php';

// Inisialisasi variabel
$error = '';
$username = '';

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Validasi input
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi!';
    } else {
        // Cek user di database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Login berhasil
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect ke halaman utama
            header('Location: index.php');
            exit;
        } else {
            $error = 'Username atau password salah!';
        }
    }
}

// Jika tabel users belum ada, buat secara otomatis
try {
    $pdo->query("SELECT COUNT(*) FROM users");
} catch (PDOException $e) {
    // Buat tabel users jika belum ada
    $createTableSQL = "
    CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100),
        role ENUM('admin', 'user', 'supervisor') DEFAULT 'user',
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($createTableSQL);
    
    // Buat user admin default
    $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $insertUserSQL = "
    INSERT INTO users (username, password, full_name, email, role) 
    VALUES ('admin', '$defaultPassword', 'Administrator', 'admin@talreport.com', 'admin')
    ";
    
    $pdo->exec($insertUserSQL);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TAL Report System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4cc9f0;
            --gradient-bg: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            background: var(--gradient-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
            transition: all 0.3s ease;
        }
        
        .login-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .login-header {
            background: var(--gradient-bg);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        
        .login-header h1 {
            font-weight: 700;
            font-size: 1.8rem;
            margin: 0;
        }
        
        .login-header p {
            opacity: 0.9;
            margin: 10px 0 0;
        }
        
        .login-body {
            padding: 30px;
        }
        
        .form-control {
            border-radius: 10px;
            padding: 12px 20px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
        }
        
        .input-group-text {
            background: white;
            border-radius: 10px 0 0 10px;
            border: 2px solid #e9ecef;
            border-right: none;
        }
        
        .form-floating>.form-control:focus~label, 
        .form-floating>.form-control:not(:placeholder-shown)~label {
            color: var(--primary-color);
        }
        
        .btn-login {
            background: var(--gradient-bg);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.4);
        }
        
        .login-footer {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }
        
        .login-footer a {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .login-footer a:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .password-toggle {
            cursor: pointer;
            transition: color 0.3s ease;
        }
        
        .password-toggle:hover {
            color: var(--primary-color);
        }
        
        .app-info {
            text-align: center;
            color: white;
            margin-bottom: 30px;
            max-width: 400px;
        }
        
        .app-info h2 {
            font-weight: 700;
            margin-bottom: 15px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .app-info p {
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        .feature-list {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }
        
        .feature-list li {
            padding: 8px 0;
            display: flex;
            align-items: center;
        }
        
        .feature-list li i {
            color: #4ade80;
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            body {
                padding: 15px;
                background: white;
            }
            
            .app-info {
                display: none;
            }
            
            .login-container {
                box-shadow: none;
                max-width: 100%;
            }
            
            .login-container:hover {
                transform: none;
                box-shadow: none;
            }
        }
        
        @media (min-width: 769px) {
            .login-wrapper {
                display: flex;
                align-items: center;
                gap: 50px;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="app-info d-none d-md-block">
            <h2><i class="fas fa-tasks me-2"></i>TAL Report System</h2>
            <p>Sistem pelaporan dan monitoring tugas harian yang efisien</p>
            
            <ul class="feature-list">
                <li><i class="fas fa-check-circle"></i> Kelola tugas dengan mudah</li>
                <li><i class="fas fa-check-circle"></i> Pantau progress secara real-time</li>
                <li><i class="fas fa-check-circle"></i> Upload bukti pekerjaan</li>
                <li><i class="fas fa-check-circle"></i> Laporan lengkap untuk atasan</li>
                <li><i class="fas fa-check-circle"></i> Akses dari perangkat apapun</li>
            </ul>
            
            <div class="mt-4">
                <div class="d-flex align-items-center justify-content-center">
                    <div class="bg-white rounded-circle p-3 shadow me-3">
                        <i class="fas fa-mobile-alt text-primary fa-2x"></i>
                    </div>
                    <div class="bg-white rounded-circle p-3 shadow">
                        <i class="fas fa-laptop text-primary fa-2x"></i>
                    </div>
                </div>
                <p class="mt-3">Akses aplikasi dari smartphone, tablet, atau komputer</p>
            </div>
        </div>
        
        <div class="login-container">
            <div class="login-header">
                <h1><i class="fas fa-sign-in-alt me-2"></i>Login</h1>
                <p>Masuk ke akun Anda untuk melanjutkan</p>
            </div>
            
            <div class="login-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i> <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="loginForm">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?= htmlspecialchars($username) ?>" placeholder="Masukkan username" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Masukkan password" required>
                            <span class="input-group-text password-toggle" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="rememberMe" name="remember_me">
                        <label class="form-check-label" for="rememberMe">Ingat saya</label>
                    </div>
                    
                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Masuk
                    </button>
                </form>
            </div>
            
            <div class="login-footer">
                <p>Default login: <strong>admin</strong> / <strong>admin123</strong></p>
                <p class="mb-0">Butuh bantuan? <a href="#">Hubungi administrator</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Toggle icon
                const icon = this.querySelector('i');
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            });
            
            // Form validation
            const loginForm = document.getElementById('loginForm');
            loginForm.addEventListener('submit', function(e) {
                const username = document.getElementById('username').value.trim();
                const password = document.getElementById('password').value;
                
                if (!username) {
                    e.preventDefault();
                    alert('Username harus diisi!');
                    document.getElementById('username').focus();
                    return;
                }
                
                if (!password) {
                    e.preventDefault();
                    alert('Password harus diisi!');
                    document.getElementById('password').focus();
                    return;
                }
            });
            
            // Check if there's a saved username in localStorage
            const rememberMe = document.getElementById('rememberMe');
            const savedUsername = localStorage.getItem('remembered_username');
            
            if (savedUsername) {
                document.getElementById('username').value = savedUsername;
                rememberMe.checked = true;
            }
            
            // Save username if remember me is checked
            rememberMe.addEventListener('change', function() {
                const username = document.getElementById('username').value.trim();
                
                if (this.checked && username) {
                    localStorage.setItem('remembered_username', username);
                } else {
                    localStorage.removeItem('remembered_username');
                }
            });
        });
    </script>
</body>
</html>