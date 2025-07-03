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
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PAM JAYA</title>
    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="website icon" type="png" href="../image/logo.png">
    <style>
        /* Base Styles */
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
            margin-left: 250px;
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

        h1, h4 {
            margin-top: 30px;
            margin-bottom: 30px;
            font-weight: bold;
            color: #003366;
            text-align: center;
        }

        h1 {
            font-size: 30px;
        }

        h4 {
            font-size: 24px;
        }

        /* Plant Info Table */
        .plant {
            max-width: 600px;
            margin: 20px auto;
            border-collapse: collapse;
        }

        .table-row td {
            padding: 8px 30px;
            border-bottom: 1px solid #ddd;
        }

        .table-row td:first-child {
            font-weight: bold;
            color: #003366;
            width: 120px;
        }

        /* Table Container Styles */
        .table-container {
            max-width: 1200px;
            margin: 0 auto 30px;
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

            h1, h4 {
                font-size: 24px;
            }

            .table th, .table td {
                padding: 8px;
                font-size: 13px;
            }
            
            .table-container {
                overflow-x: auto;
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
            <li><a href="view_data.php"><i class="fas fa-eye me-2"></i>Lihat Data</a></li>
            <li><a href="input_motorpompa.php"><i class="fas fa-plus-circle me-2"></i>Tambah Data</a></li>
            <li><a href="../dashboard.php"><i class="fas fa-arrow-left me-2"></i>Kembali</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Logo Container -->
        <div class="logo-container">
            <img src="../image/logo.png" alt="Logo PAM Jaya" class="logo">
            <div class="judul">PERUMDA AIR MINUM JAYA</div>
            <img src="../image/Jakarta.png" alt="Logo Jakarta" class="logo">
        </div>

        <h4>INSPEKSI MOTOR DAN POMPA</h4>

        <!-- Plant Information -->
        <div class="table-container">
            <table class="plant">
                <tr class="table-row">
                    <td>Plant:</td>
                    <td><span class="span   "></span></td>
                </tr>
                <tr class="table-row">
                    <td>Location:</td>
                    <td><span class="span"></span></td>
                </tr>
                <tr class="table-row">
                    <td>Type:</td>
                    <td><span class="span"></span></td>
                </tr>
                <tr class="table-row">
                    <td>Machine no:</td>
                    <td><span class="span"></span></td>
                </tr>
                <tr class="table-row">
                    <td>Date:</td>
                    <td><span class="span"></span></td>
                </tr>
            </table>
        </div>

        <!-- Table Motor -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Motor Vibrator</th>
                        <th>Point</th>
                        <th>Result</th>
                        <th>Standar Max</th>
                        <th>Comment</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Motor inboard Horizontal</td>
                        <td>MIH</td>
                        <td>............</td>
                        <td>6,5 mms(.....)</td>
                        <td>.............................................</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Motor Inboard Vertical</td>
                        <td>MIV</td>
                        <td>............</td>
                        <td>7,6 mms(.....)</td>
                        <td>.............................................</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Motor Inboard Axial</td>
                        <td>MIA</td>
                        <td>............</td>
                        <td>8,5 mms(.....)</td>
                        <td>.............................................</td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>Motor Outboard Horizontal</td>
                        <td>MOH</td>
                        <td>............</td>
                        <td>9,5 mms(.....)</td>
                        <td>.............................................</td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>Motor Outboard Vertical</td>
                        <td>MOV</td>
                        <td>............</td>
                        <td>............</td>
                        <td>.............................................</td>
                    </tr>
                    <tr>
                        <td>6</td>
                        <td>Motor Outboard Axial</td>
                        <td>MOA</td>
                        <td>............</td>
                        <td>............</td>
                        <td>.............................................</td>
                    </tr>
                    <tr>
                        <td>7</td>
                        <td>Ampere</td>
                        <td>............</td>
                        <td>............</td>
                        <td>............</td>
                        <td>.............................................</td>
                    </tr>
                    <tr>
                        <td>8</td>
                        <td>Hour Meter</td>
                        <td>............</td>
                        <td>............</td>
                        <td>............</td>
                        <td>.............................................</td>
                    </tr>
                    <tr>
                        <td>9</td>
                        <td>Rpm</td>
                        <td>............</td>
                        <td>............</td>
                        <td>............</td>
                        <td>.............................................</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Table Pump -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Pump/Blower Vibration</th>
                        <th>Point</th>
                        <th>Result</th>
                        <th>Standar Max</th>
                        <th>Comment</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Pompa inboard Horizontal</td>
                        <td>PIH</td>
                        <td>............</td>
                        <td>6,5 mms(.....)</td>
                        <td>.............................................</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Pompa Inboard Vertical</td>
                        <td>PIV</td>
                        <td>............</td>
                        <td>7,6 mms(.....)</td>
                        <td>.............................................</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Pompa Inboard Axial</td>
                        <td>PIA</td>
                        <td>............</td>
                        <td>8,5 mms(.....)</td>
                        <td>.............................................</td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>Pompa Outboard Horizontal</td>
                        <td>POH</td>
                        <td>............</td>
                        <td>9,5 mms(.....)</td>
                        <td>.............................................</td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>Pompa Outboard Vertical</td>
                        <td>POV</td>
                        <td>............</td>
                        <td>............</td>
                        <td>.............................................</td>
                    </tr>
                    <tr>
                        <td>6</td>
                        <td>Pompa Outboard Axial</td>
                        <td>POA</td>
                        <td>............</td>
                        <td>............</td>
                        <td>.............................................</td>
                    </tr>
                    <tr>
                        <td>7</td>
                        <td>Pressure</td>
                        <td>............</td>
                        <td>............</td>
                        <td>............</td>
                        <td>.............................................</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Table Temperature -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Temperature</th>
                        <th>Point</th>
                        <th>Result</th>
                        <th>Standar Max</th>
                        <th>Comment</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Temperature Ruangan</td>
                        <td>T0</td>
                        <td>............</td>
                        <td>80°C</td>
                        <td>.............................................</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Temperature Casing Motor</td>
                        <td>T1</td>
                        <td>............</td>
                        <td>............</td>
                        <td>.............................................</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Temperature Bearing Motor</td>
                        <td>T2</td>
                        <td>............</td>
                        <td>............</td>
                        <td>.............................................</td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>Temperature Bearing Atas</td>
                        <td>T3 (IN)</td>
                        <td>............</td>
                        <td>............</td>
                        <td>.............................................</td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>Temperature Bearing Bawah</td>
                        <td>T4 (IN)</td>
                        <td>............</td>
                        <td>............</td>
                        <td>.............................................</td>
                    </tr>
                    <tr>
                        <td>6</td>
                        <td>Temperature Bearing Atas</td>
                        <td>T5 (OUT)</td>
                        <td>............</td>
                        <td>............</td>
                        <td>.............................................</td>
                    </tr>
                    <tr>
                        <td>7</td>
                        <td>Temperature Bearing Bawah</td>
                        <td>T6 (OUT)</td>
                        <td>............</td>
                        <td>............</td>
                        <td>.............................................</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Table Condition -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Condition</th>
                        <th>Status 1</th>
                        <th>Checkbox 1</th>
                        <th>Status 2</th>
                        <th>Checkbox 2</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Oli dan Grease</td>
                        <td>Cukup</td>
                        <td><span></span></td>
                        <td>Tidak Cukup/Kosong</td>
                        <td><span></span></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Gland Packing/Mech Seal</td>
                        <td>Tidak Bocor/Bocor Kecil</td>
                        <td><span></span></td>
                        <td>Bocor Besar</td>
                        <td><span></span></td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Baut2 Dudukan/Grouting</td>
                        <td>Kuat/Tidak Longgar</td>
                        <td><span></span></td>
                        <td>Longgar</td>
                        <td><span></span></td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>Seal Kopling</td>
                        <td>Tidak Bocor/Robek</td>
                        <td><span></span></td>
                        <td>Bocor/Tidak Robek</td>
                        <td><span></span></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer -->
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

    <!-- JavaScript Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz4fnFO9gybD2vY9hh6b37d4QbHVqHZmE9D23tYVwVtWqM1oypB6+g8t5iJ" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <!-- Mobile Toggle Script -->
    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("responsive");
            document.querySelector(".main-content").classList.toggle("shifted");
        }
    </script>
</body>
</html>