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
        :root {
            --primary-color: #003366;
            --secondary-color: #004d99;
            --accent-color: #007bff;
            --light-gray: #f8f9fa;
            --medium-gray: #e9ecef;
            --dark-gray: #495057;
            --white: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-gray);
            margin: 0;
            padding: 0;
            line-height: 1.6;
            display: flex;
            min-height: 100vh;
            color: #333;
        }

        /* Enhanced Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, var(--primary-color) 0%, #002244 100%);
            color: var(--white);
            height: 100vh;
            padding: 25px;
            box-shadow: var(--shadow);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1050;
            overflow-y: auto;
            transition: all 0.3s ease;
        }

        .user-photo-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 30px 0;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .user-greeting {
            font-size: 1.1rem;
            text-align: center;
            margin: 15px 0;
            padding: 10px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            width: 100%;
        }

        .inspection-list {
            list-style: none;
            padding: 0;
            margin-top: 20px;
        }

        .inspection-list li {
            margin-bottom: 8px;
        }

        .inspection-list li a {
            color: var(--white);
            text-decoration: none;
            font-size: 0.95rem;
            display: block;
            padding: 12px 15px;
            border-radius: 6px;
            transition: all 0.3s ease;
            background-color: rgba(255, 255, 255, 0.05);
        }

        .inspection-list li a:hover {
            background-color: rgba(255, 255, 255, 0.15);
            transform: translateX(5px);
        }

        .inspection-list li a i {
            width: 24px;
            text-align: center;
            margin-right: 10px;
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            flex-grow: 1;
            padding: 0;
            width: calc(100% - 280px);
            transition: all 0.3s ease;
        }

        /* Enhanced Logo Container */
        .logo-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 40px;
            background: linear-gradient(90deg, var(--primary-color) 0%, #004080 100%);
            color: var(--white);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            position: relative;
            z-index: 10;
        }

        .logo {
            width: 80px;
            height: 80px;
            transition: transform 0.3s ease;
            object-fit: contain;
        }

        .logo:hover {
            transform: scale(1.1);
        }

        .judul {
            font-size: 1.6rem;
            font-weight: 700;
            text-align: center;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            letter-spacing: 0.5px;
        }

        /* Page Title */
        h1 {
            margin: 30px 0;
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            text-align: center;
            position: relative;
            padding-bottom: 15px;
        }

        h1::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            border-radius: 3px;
        }

        /* Info Container */
        .info-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 30px;
        }

        .header-info {
            background-color: var(--white);
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: var(--shadow);
            border-left: 5px solid var(--primary-color);
        }

        .header-info table {
            font-size: 1rem;
            width: 100%;
            max-width: 500px;
        }

        .header-info td {
            padding: 8px 0;
            vertical-align: top;
        }

        .header-info td:first-child {
            font-weight: 600;
            color: var(--primary-color);
            width: 140px;
        }

        /* Table Container */
        .table-container {
            max-width: 1200px;
            margin: 0 auto 50px;
            padding: 0 30px;
        }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border: none;
            text-align: center;
            box-shadow: var(--shadow);
            background-color: var(--white);
            border-radius: 10px;
            overflow: hidden;
        }

        .table thead {
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }

        .table th {
            padding: 14px 15px;
            color: var(--white);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            border: none;
            position: relative;
        }

        .table th:not(:last-child)::after {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            height: 60%;
            width: 1px;
            background-color: rgba(255, 255, 255, 0.3);
        }

        .table td {
            padding: 12px 15px;
            font-size: 0.9rem;
            color: var(--dark-gray);
            border: none;
            border-bottom: 1px solid var(--medium-gray);
        }

        .table tr:nth-child(even) {
            background-color: rgba(0, 51, 102, 0.02);
        }

        .table tr:hover {
            background-color: rgba(0, 51, 102, 0.05);
        }

        .table td:first-child {
            font-weight: 600;
            color: var(--primary-color);
        }

        /* Footer */
        footer {
            background: linear-gradient(90deg, #1a1a1a 0%, #2c2c2c 100%);
            color: var(--white);
            padding: 40px 20px;
            width: 100%;
            margin-top: 50px;
        }

        .footer-bottom {
            text-align: center;
            padding: 20px;
            font-size: 0.9rem;
            color: #aaa;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .footer-bottom h5 {
            font-size: 1.1rem;
            margin-bottom: 15px;
            color: var(--white);
        }

        .social-media {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 15px;
        }

        .social-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: #444;
            color: white;
            font-size: 16px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .social-icon:hover {
            transform: translateY(-3px);
        }

        .instagram:hover { background-color: #E4405F !important; }
        .youtube:hover { background-color: #FF0000 !important; }
        .twitter:hover { background-color: #1DA1F2 !important; }
        .facebook:hover { background-color: #3b5998 !important; }

        /* Mobile Toggle Button */
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1051;
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 12px;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .mobile-toggle:hover {
            background-color: var(--secondary-color);
            transform: scale(1.1);
        }

        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .sidebar {
                width: 240px;
                padding: 20px;
            }
            
            .main-content {
                margin-left: 240px;
                width: calc(100% - 240px);
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
                padding: 25px;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            
            .main-content.shifted {
                margin-left: 280px;
                width: calc(100% - 280px);
            }
            
            .mobile-toggle {
                display: block;
            }
            
            .logo-container {
                padding: 15px;
            }

            .logo {
                width: 60px;
                height: 60px;
            }

            .judul {
                font-size: 1.2rem;
            }

            h1 {
                font-size: 1.5rem;
                margin: 20px 0;
            }

            .info-container, .table-container {
                padding: 0 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="user-photo-container">
            <div class="user-greeting">
                <i class="fas fa-user-circle me-2"></i>Welcome <?php echo htmlspecialchars($username); ?>
            </div>
        </div>
        <ul class="inspection-list">
            <li><a href="view_ac.php"><i class="fas fa-eye"></i>Lihat Data</a></li>
            <li><a href="input_ac.php"><i class="fas fa-plus-circle"></i>Tambah Data</a></li>
            <li><a href="../dashboard.php"><i class="fas fa-arrow-left"></i>Kembali</a></li>
        </ul>
    </div>

    <!-- Mobile Toggle Button -->
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

        <h1>INSPEKSI UNIT AIR CONDITIONER</h1>
        
        <div class="info-container">
            <div class="header-info">
                <table>
                    <tr>
                        <td>No. WO</td>
                        <td>: <span id="wo-number">-</span></td>
                    </tr>
                    <tr>
                        <td>Tanggal</td>
                        <td>: <span id="inspection-date"></span></td>
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
                        <th>PK</th>
                        <th>Merek</th>
                        <th>Tipe</th>
                        <th>Freon</th>
                        <th>Suhu Ruangan</th>
                        <th>Fungsi Remot</th>
                        <th>Catatan</th>
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
        // Mobile toggle functionality
        const mobileToggle = document.getElementById('mobileToggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        mobileToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('shifted');
        });

        // Set current date
        const now = new Date();
        document.getElementById('inspection-date').textContent = now.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    </script>
</body>
</html>