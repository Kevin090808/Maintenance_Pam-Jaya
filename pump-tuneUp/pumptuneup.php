<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['session_username'])) {
    header("Location: login.php"); 
    exit();
}

$username = $_SESSION['session_username']; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INSPEKSI UNIT AIR CONDITIONER</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="website icon" type="png" href="../image/logo.png">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            line-height: 1.6;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 250px;
            background-color: #003366;
            color: white;
            height: 100vh;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1050;
            overflow-y: auto;
        }

        .user-photo-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 30px;
            margin-bottom: 20px;
        }

        .user-photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            margin-bottom: 15px;
        }

        .sidebar .user-greeting {
            font-size: 18px;
            text-align: center;
            margin-bottom: 20px;
        }

        .sidebar .inspection-list {
            list-style: none;
            padding: 0;
            margin-top: 30px;
        }

        .sidebar .inspection-list li {
            margin-bottom: 10px;
        }

        .sidebar .inspection-list li a {
            color: white;
            text-decoration: none;
            font-size: 14.5px;
            display: block;
            padding: 10px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .sidebar .inspection-list li a:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        /* Main Content Styling */
        .main-content {
            margin-left: 250px; /* Set to match sidebar width */
            flex-grow: 1;
            padding: 0;
            width: calc(100% - 250px);
        }

        /* Logo Container */
        .logo-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 40px;
            background-color: #003366;
            color: white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .logo-container .logo {
            width: 90px;
            height: 90px;
            transition: transform 0.3s ease;
        }

        .logo-container .logo:hover {
            transform: scale(1.1);
        }

        .logo-container .judul {
            font-size: 26px;
            font-weight: bold;
            text-align: center;
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h1 {
            margin-top: 30px;
            margin-bottom: 30px;
            font-size: 30px;
            font-weight: bold;
            color: #003366;
            text-align: center;
        }

        .info-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .header-info {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .header-info table {
            font-size: 17px;
            width: 100%;
            max-width: 500px;
        }

        .header-info td:first-child {
            font-weight: 600;
            color: #003366;
            width: 120px;
        }

        /* Action Buttons */
        .action-buttons {
            max-width: 1200px;
            margin: 0 auto 30px;
            padding: 0 20px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .action-buttons .btn {
            padding: 10px 20px;
            font-weight: 600;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .action-buttons .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .btn-primary {
            background-color: #003366;
            border-color: #003366;
        }

        .btn-primary:hover, .btn-primary:focus {
            background-color: #004d99;
            border-color: #004d99;
        }

        /* Table Container Styles */
        .table-container {
            max-width: 1200px;
            margin: 0 auto 50px;
            padding: 0 20px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #ddd;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
        }

        .table th, .table td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            font-size: 14px;
            color: #555;
        }

        .table th {
            background-color: #003366;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 13px;
        }

        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .table tr:hover {
            background-color: #f1f1f1;
        }

        .table td:first-child {
            font-weight: bold;
            color: #003366;
        }

        /* Modal Styling */
        .modal-content {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            background-color: #003366;
            color: white;
            border-bottom: none;
            padding: 15px 20px;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            border-top: none;
            padding: 15px 20px;
        }

        .form-control {
            border-radius: 5px;
            border: 1px solid #ced4da;
            padding: 10px 15px;
        }

        .form-control:focus {
            border-color: #003366;
            box-shadow: 0 0 0 0.25rem rgba(0, 51, 102, 0.25);
        }

        /* Footer */
        footer {
            background-color: #2c2c2c;
            color: white;
            font-family: Arial, sans-serif;
            padding: 40px 20px;
            width: 100%;
            margin-top: auto;
        }

        .footer-bottom {
            text-align: center;
            padding: 20px;
            font-size: 14px;
            color: gray;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .footer-bottom h5 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #f8f9fa;
        }

        .social-media {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 15px;
        }

        .social-icon {
            display: inline-block;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #666;
            text-align: center;
            line-height: 40px;
            color: white;
            font-size: 18px;
            transition: background-color 0.3s ease, transform 0.3s ease;
            text-decoration: none;
        }

        .social-icon:hover {
            transform: scale(1.1);
        }

        .social-icon.instagram:hover {
            background-color: #E4405F;
        }

        .social-icon.youtube:hover {
            background-color: #FF0000;
        }

        .social-icon.twitter:hover {
            background-color: #1DA1F2;
        }

        .social-icon.facebook:hover {
            background-color: #3b5998;
        }

        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .sidebar {
                width: 200px;
            }
            
            .main-content {
                margin-left: 200px;
                width: calc(100% - 200px);
            }
            
            .user-photo {
                width: 100px;
                height: 100px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                padding: 0;
                overflow: hidden;
                transition: width 0.3s ease;
            }
            
            .sidebar.responsive {
                width: 250px;
                padding: 20px;
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
                transition: margin-left 0.3s ease;
            }
            
            .main-content.shifted {
                margin-left: 250px;
                width: calc(100% - 250px);
            }
            
            .mobile-toggle {
                display: block;
                position: fixed;
                top: 20px;
                left: 20px;
                z-index: 1051;
                background-color: #003366;
                color: white;
                border: none;
                padding: 10px;
                border-radius: 5px;
                cursor: pointer;
            }
            
            .logo-container {
                flex-direction: column;
                gap: 15px;
                padding: 15px;
            }

            .logo-container .logo {
                width: 70px;
                height: 70px;
            }

            .logo-container .judul {
                font-size: 20px;
            }

            h1 {
                font-size: 24px;
            }

            .table th, .table td {
                padding: 8px;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="user-photo-container">

            <div class="user-greeting">
                <i class="fas fa-user-circle"></i> Welcome <?php echo htmlspecialchars($username); ?>
            </div>
        </div>
        <ul class="inspection-list">
            <li><a href="view_pumptuneup.php" ><i class="fas fa-eye me-2"></i>Lihat Data</a></li>
            <li><a href="input_pumptuneup.php"><i class="fas fa-plus-circle me-2"></i>Tambah Data</a></li>
            <li><a href="../dashboard.php"><i class="fas fa-arrow-left me-2"></i>Kembali</a></li>
        </ul>
    </div>

    <!-- Mobile Toggle Button (only visible on small screens) -->
    <button class="mobile-toggle d-md-none" id="mobileToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Logo Container -->
        <div class="logo-container">
            <img src="../image/logo.png" alt="Logo PAM Jaya" class="logo">
            <div class="judul">PERUMDA AIR MINUM JAYA</div>
            <img src="../image/Jakarta.png" alt="Logo Jakarta" class="logo">
        </div>

        <h1>MEKANIKAL PUMP TUNE UP</h1>
        
        <div class="info-container">
            <div class="header-info">
                <table>
                    <tr>
                        <td>No. WO</td>
                        <td>: <span id="wo-number">-</span></td>
                    </tr>
                    <tr>
                        <td>Tanggal</td>
                        <td>: <span id="inspection-date"><?php echo date('d-m-Y'); ?></span></td>
                    </tr>
                    <tr>
                        <td>Plant</td>
                        <td>: <span id="plant-name">-</span></td>
                    </tr>
                    <tr>
                        <td>Lokasi</td>
                        <td>: <span id="location">-</span></td>
                    </tr>
                    <tr>
                        <td>Frekuensi</td>
                        <td>: <span id="frequency">-</span></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>equipment</th>
                        <th colspan="4" class="text-center">Deskripsi</th>
                        <th colspan="2" class="text-center">condition</th>
                        <th>Perawatan part</th>
                        <th>penggantian part</th>
                        <th>jumlah part</th>
                        <th>remaks</th>
                    </tr>
                    <tr>
                        <th></th>
                        <th></th>
                        <th>Seal Coupling</th>
                        <th>Shaft</th>
                        <th>Bolt Mounting</th>
                        <th>Balancing</th>
                        <th>Good</th>
                        <th>Not Good</th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th> 
                    </tr>
                </thead>
                <tbody>
                    <?php for($i=1; $i<=8; $i++) { ?>
                    <tr>
                        <td><?php echo $i; ?></td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <footer>
            <div class="footer-bottom">
                <p>&copy; 2025 Perumda PAM Jaya. All Rights Reserved.</p>
                <h5>Follow Us</h5>
                <div class="social-media">
                    <a href="https://www.instagram.com" target="_blank" class="social-icon instagram"><i class="fab fa-instagram"></i></a>
                    <a href="https://www.youtube.com" target="_blank" class="social-icon youtube"><i class="fab fa-youtube"></i></a>
                    <a href="https://www.twitter.com" target="_blank" class="social-icon twitter"><i class="fab fa-twitter"></i></a>
                    <a href="https://www.facebook.com" target="_blank" class="social-icon facebook"><i class="fab fa-facebook-f"></i></a>
                </div>
            </div>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz4fnFO9gybD2vY9hh6b37d4QbHVqHZmE9D23tYVwVtWqM1oypB6+g8t5iJ" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        // Mobile toggle functionality (only for small screens)
        const mobileToggle = document.getElementById('mobileToggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        if (mobileToggle) {
            mobileToggle.addEventListener('click', () => {
                sidebar.classList.toggle('responsive');
                mainContent.classList.toggle('shifted');
            });
        }

        // Handle date form submission
        document.getElementById('dateForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const date = document.getElementById('date').value;
            if (date) {
                window.location.href = `view_ac.php?date=${date}`;
            } else {
                alert('Silahkan pilih tanggal terlebih dahulu.');
            }
        });
    </script>
</body>
</html>