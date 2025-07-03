<?php 
session_start();

$host_db    = "localhost";
$user_db    = "root";
$pass_db    = "";
$nama_db    = "maintenance";
$koneksi    = mysqli_connect($host_db,$user_db,$pass_db,$nama_db);

$err        = '';
$username   = '';

if(isset($_POST['login'])){
    $username   = $_POST['username'];
    $password   = $_POST['password'];

    if($username == '' or $password == ''){
        $err.= "<li>Silakan masukkan username dan juga password.</li>";
    }else{
        $sql1 = "select * from login where username = '$username'";
        $q1   = mysqli_query($koneksi,$sql1);
        $r1   = mysqli_fetch_array($q1);

        if($r1 === null){
            $err.= "<li>Username <b>$username</b> tidak tersedia.</li>";
        }elseif(isset($r1['password']) && $r1['password']!= md5($password)){
            $err.= "<li>Password yang dimasukkan tidak sesuai.</li>";
        }        

        if(empty($err)){
            $_SESSION['session_username'] = $username;
            $_SESSION['session_password'] = md5($password);

            if(isset($_POST['ingataku']) && $_POST['ingataku'] == '1'){
                $cookie_name = "cookie_username";
                $cookie_value = $username;
                $cookie_time = time() + (60 * 60 * 24 * 30);
                setcookie($cookie_name,$cookie_value,$cookie_time,"/");

                $cookie_name = "cookie_password";
                $cookie_value = md5($password);
                $cookie_time = time() + (60 * 60 * 24 * 30);
                setcookie($cookie_name,$cookie_value,$cookie_time,"/"); 
            }
            header("location:dashboard.php");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="website icon" type="png" href="image/logo.png">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #4338ca;
            --text-light: #f8fafc;
            --text-dark: #1e293b;
            --error-color: #ef4444;
            --success-color: #22c55e;
        }
        
        body {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow-x: hidden;
        }
        
        .main-container {
            width: 100%;
            max-width: 1000px;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(12px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2), 
                        0 5px 15px rgba(0, 0, 0, 0.1),
                        inset 0 1px 1px rgba(255, 255, 255, 0.1);
            overflow: hidden;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.8s ease-out forwards;
        }
        
        .login-container {
            display: flex;
            min-height: 550px;
        }
        
        .login-form {
            flex: 1;
            padding: 40px;
            position: relative;
            background: rgba(255, 255, 255, 0.95);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .welcome-side {
            flex: 1;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            padding: 40px;
            color: var(--text-light);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            overflow: hidden;
        }
        
        .welcome-side::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiPgogIDxkZWZzPgogICAgPHBhdHRlcm4gaWQ9InBhdHRlcm4iIHg9IjAiIHk9IjAiIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgcGF0dGVyblRyYW5zZm9ybT0icm90YXRlKDQ1KSI+CiAgICAgIDxjaXJjbGUgY3g9IjIwIiBjeT0iMjAiIHI9IjEiIGZpbGw9InJnYmEoMjU1LCAyNTUsIDI1NSwgMC4xKSIgLz4KICAgIDwvcGF0dGVybj4KICA8L2RlZnM+CiAgPHJlY3QgeD0iMCIgeT0iMCIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0idXJsKCNwYXR0ZXJuKSIgLz4KPC9zdmc+') repeat;
            opacity: 0.5;
        }
        
        .logo-area {
            margin-bottom: 40px;
            position: relative;
            z-index: 5;
        }
        
        .logo-image {
            width: 180px;
            height: auto;
            filter: drop-shadow(0 5px 15px rgba(0, 0, 0, 0.2));
            animation: pulse 3s ease-in-out infinite;
        }
        
        .welcome-content {
            position: relative;
            z-index: 5;
        }
        
        .welcome-title {
            font-size: 2.4rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .welcome-text {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 40px;
            opacity: 0.9;
        }
        
        .register-btn {
            background-color: rgba(255, 255, 255, 0.15);
            border: 2px solid rgba(255, 255, 255, 0.8);
            padding: 12px 30px;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            z-index: 5;
        }
        
        .register-btn:hover {
            background-color: white;
            color: var(--primary-color);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }
        
        .register-btn::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: -100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }
        
        .register-btn:hover::after {
            left: 100%;
        }
        
        .login-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 30px;
            text-align: center;
        }
        
        .alert-danger {
            background-color: rgba(239, 68, 68, 0.1);
            border-left: 4px solid var(--error-color);
            color: var(--text-dark);
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .alert-danger ul {
            margin-bottom: 0;
            padding-left: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .form-control {
            height: 55px;
            padding-left: 55px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            transition: all 0.3s ease;
            font-size: 1rem;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
            background-color: white;
        }
        
        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus + .input-icon {
            color: var(--primary-color);
        }
        
        .remember-checkbox {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .remember-checkbox input {
            margin-right: 10px;
            width: 18px;
            height: 18px;
            accent-color: var(--primary-color);
        }
        
        .remember-checkbox label {
            color: #64748b;
            font-size: 0.95rem;
        }
        
        .login-btn {
            height: 55px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1.1rem;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            color: white;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.2);
        }
        
        .login-btn:hover {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--accent-color) 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
        }
        
        .login-btn::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: -100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }
        
        .login-btn:hover::after {
            left: 100%;
        }
        
        .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 10s ease-in-out infinite;
            z-index: 1;
        }
        
        .shape-1 {
            width: 150px;
            height: 150px;
            top: -50px;
            right: -50px;
            animation-delay: 0s;
        }
        
        .shape-2 {
            width: 100px;
            height: 100px;
            bottom: 50px;
            right: 80px;
            animation-delay: 2s;
        }
        
        .shape-3 {
            width: 80px;
            height: 80px;
            bottom: -30px;
            left: 50px;
            animation-delay: 4s;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(5deg);
            }
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @media (max-width: 991px) {
            .main-container {
                width: 90%;
                max-width: 500px;
            }
            
            .login-container {
                flex-direction: column-reverse;
            }
            
            .welcome-side {
                padding: 30px 20px;
                min-height: 200px;
            }
            
            .welcome-title {
                font-size: 1.8rem;
            }
            
            .welcome-text {
                font-size: 1rem;
                margin-bottom: 20px;
            }
            
            .logo-image {
                width: 120px;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="login-container">
            <div class="login-form">
                <h1 class="login-title">Masuk ke Akun Anda</h1>
                
                <?php if($err){ ?>
                    <div class="alert alert-danger">
                        <ul><?php echo $err ?></ul>
                    </div>
                <?php } ?>
                
                <form id="loginform" action="" method="post">
                    <div class="form-group">
                        <input id="login-username" type="text" class="form-control" name="username" value="<?php echo $username ?>" placeholder="Username" required>
                        <i class="fas fa-user input-icon"></i>
                    </div>
                    
                    <div class="form-group">
                        <input id="login-password" type="password" class="form-control" name="password" placeholder="Password" required>
                        <i class="fas fa-lock input-icon"></i>
                    </div>
                    
                    <div class="remember-checkbox">
                        <input type="checkbox" id="remember" name="ingataku" value="1">
                        <label for="remember">Ingat saya</label>
                    </div>
                    
                    <button type="submit" name="login" class="btn login-btn w-100">
                        <i class="fas fa-sign-in-alt me-2"></i> Masuk
                    </button>
                </form>
            </div>
            
            <div class="welcome-side">
                <div class="shape shape-1"></div>
                <div class="shape shape-2"></div>
                <div class="shape shape-3"></div>
                
                <div class="logo-area">
                    <img src="image/logoputihpam.png" alt="Logo" class="logo-image">
                </div>
                
                <div class="welcome-content">
                    <h2 class="welcome-title">Selamat Datang!</h2>
                    <p class="welcome-text">Belum memiliki akun? Silakan daftar untuk mengakses semua fitur yang tersedia pada sistem kami.</p>
                    <a href="register.php" class="register-btn">
                        <i class="fas fa-user-plus"></i> Daftar Akun
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Show password toggle
        document.addEventListener("DOMContentLoaded", function() {
            const passwordField = document.getElementById('login-password');
            const userField = document.getElementById('login-username');
            
            // Auto focus on username field
            setTimeout(() => {
                userField.focus();
            }, 500);
            
            // Add password visibility toggle (optional implementation)
            if(passwordField) {
                const toggleIcon = document.createElement('i');
                toggleIcon.className = 'fas fa-eye';
                toggleIcon.style.position = 'absolute';
                toggleIcon.style.right = '15px';
                toggleIcon.style.top = '50%';
                toggleIcon.style.transform = 'translateY(-50%)';
                toggleIcon.style.cursor = 'pointer';
                toggleIcon.style.color = '#94a3b8';
                toggleIcon.style.zIndex = '10';
                
                passwordField.parentNode.appendChild(toggleIcon);
                
                toggleIcon.addEventListener('click', function() {
                    if (passwordField.type === 'password') {
                        passwordField.type = 'text';
                        toggleIcon.className = 'fas fa-eye-slash';
                    } else {
                        passwordField.type = 'password';
                        toggleIcon.className = 'fas fa-eye';
                    }
                });
            }
        });
    </script>
</body>
</html>