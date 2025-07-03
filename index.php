<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>PAMJAYA</title>
  <link rel="website icon" type="jpeg" href="image/logo.png" />
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: linear-gradient(135deg, rgb(36, 41, 49) 0%, rgb(18, 66, 150) 100%);
      color: white;
      overflow: hidden;
    }

    /* Splash Screen */
    .splash-screen {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      background: linear-gradient(135deg, rgb(18, 66, 150) 0%, rgb(36, 41, 49) 100%);
      z-index: 9999;
      transition: opacity 0.5s ease-out;
    }

    .splash-logo {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      border: 4px solid white;
      margin-bottom: 30px;
      animation: pulse 2s infinite alternate, rotateSplash 1.5s ease-in-out;
      transform: translateX(-100px) rotate(20deg) scale(0.5);
    }

    @keyframes pulse {
      from {
        transform: scale(1);
        box-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
      }
      to {
        transform: scale(1.05);
        box-shadow: 0 0 20px rgba(255, 255, 255, 0.8);
      }
    }

    @keyframes rotateSplash {
      0% {
        transform: rotate(0deg) scale(0);
      }
      100% {
        transform: rotate(360deg) scale(1);
      }
    }

    .splash-title {
      font-size: 3.5rem;
      font-weight: bold;
      letter-spacing: 3px;
      margin-bottom: 10px;
      animation: fadeInText 1.5s ease-in-out;
    }

    .splash-subtitle {
      font-size: 1.5rem;
      opacity: 0.8;
      animation: fadeInText 2s ease-in-out;
    }

    @keyframes fadeInText {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .loading-bar {
      width: 200px;
      height: 6px;
      background-color: rgba(255, 255, 255, 0.2);
      border-radius: 3px;
      margin-top: 30px;
      overflow: hidden;
    }

    .loading-progress {
      height: 100%;
      width: 0%;
      background-color: white;
      animation: loading 3s ease-in-out forwards;
    }

    @keyframes loading {
      0% { width: 0%; }
      100% { width: 100%; }
    }

    /* Main Content */
    .container {
      display: none;
      text-align: center;
      background:linear-gradient(135deg, rgb(18, 66, 150) 0%, rgb(36, 41, 49) 100%);
      padding: 40px;
      border-radius: 15px;
      box-shadow: 0 4px 30px rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(10px);
      animation: fadeIn 1s ease-in-out;
      width: 100%;
      max-width: 400px;
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translate(-50%, -60%);
      }
      to {
        opacity: 1;
        transform: translate(-50%, -50%);
      }
    }

    .logo {
      width: 100px;
      height: 100px;
      margin-bottom: 20px;
      border-radius: 50%;
      border: 3px solid white;
      opacity: 0;
      transform: scaleY(0);
      transform-origin: bottom;
      animation: appearFromBottom 0.8s ease-out forwards;
      animation-delay: 0.3s;
    }

    @keyframes appearFromBottom {
      to {
        opacity: 1;
        transform: scaleY(1);
      }
    }

    h1 {
      font-size: 2.5rem;
      margin-bottom: 10px;
      animation: slideIn 1s ease-in-out;
    }

    p {
      font-size: 1.2rem;
      margin-bottom: 20px;
      animation: slideIn 1.2s ease-in-out;
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateX(-20px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    .buttons {
      display: flex;
      flex-direction: column;
      align-items: stretch;
      margin-top: 20px;
      animation: fadeInButtons 1.5s ease-in-out;
    }

    @keyframes fadeInButtons {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    button {
      padding: 12px 24px;
      margin: 10px 0;
      font-size: 16px;
      cursor: pointer;
      border: none;
      color: white;
      border-radius: 5px;
      transition: all 0.3s ease;
      width: 100%;
    }

    button:hover {
      transform: scale(1.05);
    }

    .login {
      background-color: rgb(45, 57, 219);
    }

    .login:hover {
      background-color: darkblue;
    }

    .register {
      background-color: #4CAF50;
    }

    .register:hover {
      background-color: darkgreen;
    }

    .footer {
      margin-top: 40px;
      animation: fadeInFooter 2s ease-in-out;
    }

    .footer img {
      width: 200px;
      height: auto;
      border-radius: 5px;
    }

    @keyframes fadeInFooter {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
  </style>
</head>
<body>

  <!-- Splash Screen -->
  <div class="splash-screen" id="splashScreen">
    <img src="image/logoputihpam.png" alt="PAM JAYA Logo" class="splash-logo">
    <div class="splash-title">PAM JAYA</div>
    <div class="splash-subtitle">Perusahaan Air Minum Jakarta</div>
    <div class="loading-bar">
      <div class="loading-progress"></div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="container" id="mainContent">
    <img src="image/logoputihpam.png" alt="Logo" class="logo">
    <h1>Maintenance</h1>
    <p>Selamat Datang Di Website Input Data Maintenance</p>
    <div class="buttons">
      <button class="login" onclick="window.location.href='login.php'">Login</button>
      <button class="register" onclick="window.location.href='register.php'">Daftar</button>
    </div>
    <div class="footer">
      <img src="image/new_logo_pam.png" alt="Footer Image">
    </div>
  </div>

  <script>
    // Splash Screen Script
    document.addEventListener("DOMContentLoaded", function () {
      const splashScreen = document.getElementById("splashScreen");
      const mainContent = document.getElementById("mainContent");

      const splashDuration = 3500;

      setTimeout(function () {
        splashScreen.style.opacity = "0";
        mainContent.style.display = "block";

        setTimeout(function () {
          splashScreen.style.display = "none";
        }, 500);
      }, splashDuration);
    });
  </script>

</body>
</html>
