<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Untuk pengujian, Anda bisa menggunakan user_id statis
    // Hapus baris ini saat sistem login sudah dibuat
    $_SESSION['user_id'] = 1; // Menggunakan ID user dari sampel data
    
    // Dalam sistem nyata, gunakan kode di bawah ini untuk redirect ke halaman login
    /*
    header("Location: login.php");
    exit();
    */
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "maintenance"; // Ganti dengan nama database Anda

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$user_id = $_SESSION['user_id'];
$success_message = "";
$error_message = "";

// Get current user data
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
} else {
    $error_message = "User not found";
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Process profile update
    if (isset($_POST['update_profile'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $bio = $_POST['bio'];
        
        // Update user data
        $update_sql = "UPDATE users SET name = ?, email = ?, bio = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sssi", $name, $email, $bio, $user_id);
        
        if ($update_stmt->execute()) {
            $success_message = "Profile updated successfully!";
            
            // Update user data after successful update
            $user_data['name'] = $name;
            $user_data['email'] = $email;
            $user_data['bio'] = $bio;
        } else {
            $error_message = "Error updating profile: " . $conn->error;
        }
    }
    
    // Process photo upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 4 * 1024 * 1024; // 2MB
        
        $file_type = $_FILES['profile_picture']['type'];
        $file_size = $_FILES['profile_picture']['size'];
        
        // Validate file type and size
        if (!in_array($file_type, $allowed_types)) {
            $error_message = "Only JPG, PNG, and GIF files are allowed";
        } elseif ($file_size > $max_size) {
            $error_message = "File size must be less than 2MB";
        } else {
            // Create upload directory if it doesn't exist
            $upload_dir = "uploads/profile_pictures/";
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate new filename
            $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $new_filename = $user_id . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                // Update profile picture in database
                $photo_sql = "UPDATE users SET profile_picture = ? WHERE id = ?";
                $photo_stmt = $conn->prepare($photo_sql);
                $photo_stmt->bind_param("si", $upload_path, $user_id);
                
                if ($photo_stmt->execute()) {
                    $success_message = "Profile picture updated successfully!";
                    $user_data['profile_picture'] = $upload_path;
                } else {
                    $error_message = "Error updating profile picture in database: " . $conn->error;
                }
            } else {
                $error_message = "Error uploading file";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .profile-picture {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid #f8f9fa;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .profile-picture-container {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-container">
            <h2 class="text-center mb-4">Edit Profile</h2>
            
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="profile-picture-container">
                        <img src="<?php echo isset($user_data['profile_picture']) && !empty($user_data['profile_picture']) ? $user_data['profile_picture'] : 'uploads/profile_pictures/default.png'; ?>" 
                             class="profile-picture mb-3" alt="Profile Picture">
                        
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="profile_picture" class="form-label">Change Profile Picture</label>
                                <input type="file" class="form-control" id="profile_picture" name="profile_picture">
                            </div>
                            <button type="submit" class="btn btn-primary">Upload Photo</button>
                        </form>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($user_data['name']) ? htmlspecialchars($user_data['name']) : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($user_data['email']) ? htmlspecialchars($user_data['email']) : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="bio" class="form-label">Bio</label>
                            <textarea class="form-control" id="bio" name="bio" rows="4"><?php echo isset($user_data['bio']) ? htmlspecialchars($user_data['bio']) : ''; ?></textarea>
                        </div>
                        
                        <input type="hidden" name="update_profile" value="1">
                        <button type="submit" class="btn btn-success">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>