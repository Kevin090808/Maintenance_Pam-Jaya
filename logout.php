<?php
session_start();

// Unset session variables
unset($_SESSION['session_username']);
unset($_SESSION['session_password']);

// Destroy the session
session_destroy();

// Set username cookie
$cookie_name = "cookie_username";
$cookie_value = isset($username) ? $username : ""; // Assuming $username is defined elsewhere
$cookie_time = time() + (60 * 60); // Expiry time in seconds (1 hour)
setcookie($cookie_name, $cookie_value, $cookie_time, "/");

// Set password cookie
$cookie_name = "cookie_password";
$cookie_value = isset($password) ? md5($password) : ""; // Assuming $password is defined elsewhere
$cookie_time = time() + (60 * 60); // Expiry time in seconds (1 hour)
setcookie($cookie_name, $cookie_value, $cookie_time, "/");

// Redirect to login page
header("location: index.php");
exit; // Ensure no code is executed after redirection
?>