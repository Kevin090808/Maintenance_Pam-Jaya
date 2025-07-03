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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1e40af;
            --primary-dark: #1e3a8a;
            --primary-light: #3b82f6;
            --secondary-color: #f8fafc;
            --accent-color: #06b6d4;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --border-color: #e5e7eb;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --error-color: #ef4444;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            color: var(--text-primary);
            line-height: 1.6;
            display: flex;
            min-height: 100vh;
        }

        /* Enhanced Sidebar with Glass Morphism Effect */
        .sidebar {
            width: 280px;
            background: linear-gradient(145deg, rgba(30, 64, 175, 0.95), rgba(30, 58, 138, 0.95));
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            height: 100vh;
            padding: 24px;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1050;
            overflow-y: auto;
            box-shadow: var(--shadow-xl);
        }

        .user-photo-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 20px 0 30px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .user-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #06b6d4);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: white;
            margin-bottom: 15px;
            box-shadow: var(--shadow-lg);
            transition: transform 0.3s ease;
        }

        .user-photo:hover {
            transform: scale(1.05);
        }

        .sidebar .user-greeting {
            font-size: 16px;
            font-weight: 500;
            text-align: center;
            color: rgba(255, 255, 255, 0.9);
        }

        .sidebar .inspection-list {
            list-style: none;
            padding: 0;
            margin-top: 20px;
        }

        .sidebar .inspection-list li {
            margin-bottom: 8px;
        }

        .sidebar .inspection-list li a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            padding: 12px 16px;
            border-radius: 12px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar .inspection-list li a:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(4px);
            box-shadow: var(--shadow-md);
        }

        .sidebar .inspection-list li a i {
            margin-right: 12px;
            width: 20px;
            font-size: 16px;
        }

        /* Enhanced Main Content */
        .main-content {
            margin-left: 280px;
            flex-grow: 1;
            padding: 0;
            width: calc(100% - 280px);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Modern Header with Gradient */
        .logo-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24px 40px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .logo-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="rgba(255,255,255,0.03)"><polygon points="0,100 1000,0 1000,100"/></svg>');
            pointer-events: none;
        }

        .logo-container .logo {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            background: white;
            padding: 8px;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-md);
        }

        .logo-container .logo:hover {
            transform: scale(1.05) rotate(5deg);
            box-shadow: var(--shadow-lg);
        }

        .logo-container .judul {
            font-size: 28px;
            font-weight: 700;
            text-align: center;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            letter-spacing: 1px;
        }

        /* Enhanced Page Title */
        .page-title {
            margin: 40px 0;
            padding: 0 40px;
        }

        .page-title h1 {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary-color);
            text-align: center;
            position: relative;
            padding-bottom: 16px;
        }

        .page-title h1::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            border-radius: 2px;
        }

        /* Enhanced Info Container */
        .info-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 40px;
        }

        .header-info {
            background: white;
            border-radius: 16px;
            padding: 32px;
            margin-bottom: 32px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
        }

        .header-info::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
        }

        .header-info table {
            font-size: 16px;
            width: 100%;
            max-width: 600px;
        }

        .header-info td {
            padding: 8px 0;
            vertical-align: top;
        }

        .header-info td:first-child {
            font-weight: 600;
            color: var(--primary-color);
            width: 140px;
            padding-right: 20px;
        }

        .header-info td:last-child {
            color: var(--text-primary);
            font-weight: 500;
        }

        /* Enhanced Table Design */
        .table-container {
            max-width: 1400px;
            margin: 0 auto 50px;
            padding: 0 40px;
        }

        .table-wrapper {
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow-md);
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            font-size: 14px;
            background: white;
        }

        .table th {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
            padding: 16px 12px;
            text-align: center;
            border: none;
            position: relative;
        }

        .table th:not(:last-child)::after {
            content: '';
            position: absolute;
            right: 0;
            top: 25%;
            height: 50%;
            width: 1px;
            background: rgba(255, 255, 255, 0.2);
        }

        .table td {
            padding: 16px 12px;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
            font-weight: 500;
            transition: background-color 0.2s ease;
        }

        .table tbody tr:hover {
            background-color: #f8fafc;
        }

        .table tbody tr:hover td {
            background-color: transparent;
        }

        .table td:first-child {
            font-weight: 700;
            color: var(--primary-color);
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        }

        /* Action Buttons Enhancement */
        .action-buttons {
            max-width: 1200px;
            margin: 0 auto 32px;
            padding: 0 40px;
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .action-buttons .btn {
            padding: 12px 24px;
            font-weight: 600;
            border-radius: 12px;
            border: none;
            font-size: 14px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6b7280, #4b5563);
            color: white;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            background: linear-gradient(135deg, #4b5563, #374151);
        }

        /* Enhanced Footer */
        footer {
            background: linear-gradient(135deg, #1f2937, #111827);
            color: white;
            padding: 40px 20px 20px;
            margin-top: auto;
        }

        .footer-bottom {
            text-align: center;
            padding: 20px;
            font-size: 14px;
            color: #9ca3af;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .footer-bottom h5 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #f9fafb;
            font-weight: 600;
        }

        .social-media {
            display: flex;
            justify-content: center;
            gap: 16px;
            margin-top: 20px;
        }

        .social-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 18px;
            transition: all 0.3s ease;
            text-decoration: none;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .social-icon:hover {
            transform: translateY(-4px) scale(1.1);
            box-shadow: var(--shadow-lg);
        }

        .social-icon.instagram:hover {
            background: linear-gradient(135deg, #f56565, #e53e3e);
        }

        .social-icon.youtube:hover {
            background: linear-gradient(135deg, #ff0000, #cc0000);
        }

        .social-icon.twitter:hover {
            background: linear-gradient(135deg, #1da1f2, #0c85d0);
        }

        .social-icon.facebook:hover {
            background: linear-gradient(135deg, #3b5998, #2d4373);
        }

        /* Mobile Toggle Button */
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1051;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            border: none;
            padding: 12px;
            border-radius: 12px;
            cursor: pointer;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
        }

        .mobile-toggle:hover {
            transform: scale(1.05);
            box-shadow: var(--shadow-lg);
        }

        /* Enhanced Responsive Design */
        @media (max-width: 1200px) {
            .info-container,
            .table-container {
                padding: 0 20px;
            }
            
            .logo-container {
                padding: 20px;
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 250px;
            }
            
            .main-content {
                margin-left: 250px;
                width: calc(100% - 250px);
            }
        }

        @media (max-width: 768px) {
            .mobile-toggle {
                display: block;
            }
            
            .sidebar {
                width: 0;
                padding: 0;
                overflow: hidden;
                transition: all 0.3s ease;
                transform: translateX(-100%);
            }
            
            .sidebar.responsive {
                width: 280px;
                padding: 24px;
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
                transition: all 0.3s ease;
            }
            
            .main-content.shifted {
                margin-left: 280px;
                width: calc(100% - 280px);
            }
            
            .logo-container {
                flex-direction: column;
                gap: 16px;
                padding: 20px;
                margin-left: 60px;
            }

            .logo-container .logo {
                width: 60px;
                height: 60px;
            }

            .logo-container .judul {
                font-size: 20px;
            }

            .page-title h1 {
                font-size: 24px;
                margin: 20px 0;
            }

            .table th, .table td {
                padding: 10px 8px;
                font-size: 12px;
            }

            .header-info {
                padding: 20px;
                margin: 0 20px 20px;
            }

            .table-container {
                padding: 0 10px;
                overflow-x: auto;
            }

            .table-wrapper {
                min-width: 800px;
            }
        }

        /* Loading Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header-info,
        .table-wrapper {
            animation: fadeInUp 0.6s ease forwards;
        }

        .table-wrapper {
            animation-delay: 0.2s;
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="user-photo-container">
            <div class="user-photo">
                <i class="fas fa-user"></i>
            </div>
            <div class="user-greeting">
                Welcome, <?php echo htmlspecialchars($username); ?>
            </div>
        </div>
        <ul class="inspection-list">
            <li><a href="view_cleaning.php"><i class="fas fa-eye"></i>Lihat Data</a></li>
            <li><a href="input_cleaning.php"><i class="fas fa-plus-circle"></i>Tambah Data</a></li>
            <li><a href="../dashboard.php"><i class="fas fa-arrow-left"></i>Kembali</a></li>
        </ul>
    </div>

    <!-- Mobile Toggle Button -->
    <button class="mobile-toggle" id="mobileToggle">
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

        <div class="page-title">
            <h1>INSPEKSI UNIT AIR CONDITIONER</h1>
        </div>
        
        <div class="info-container">
            <div class="header-info">
                <table>
                    <tr>
                        <td>No. WO</td>
                        <td>: <span id="wo-number">-</span></td>
                    </tr>
                    <tr>
                        <td>Tanggal</td>
                        <td>: <span id="inspection-date"><?php echo date('d F Y'); ?></span></td>
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

        <div class="action-buttons">
            <button class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Tambah Data Baru
            </button>
            <button class="btn btn-secondary">
                <i class="fas fa-download me-2"></i>Export PDF
            </button>
        </div>

        <div class="table-container">
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No. AC</th>
                            <th>PK</th>
                            <th>Merek</th>
                            <th>Tipe</th>
                            <th>Jenis Freon</th>
                            <th colspan="3">Pembersihan</th>
                            <th>Nilai Ampere</th>
                            <th>Tambah Freon</th>
                            <th>Catatan</th>
                        </tr>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th>Filter</th>
                            <th>Indoor</th>
                            <th>Outdoor</th>
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
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <footer>
            <div class="footer-bottom">
                <p>&copy; 2025 Perumda PAM Jaya. All Rights Reserved.</p>
                <div class="mt-3">
                    <h5>Follow Us</h5>
                    <div class="social-media">
                        <a href="https://www.instagram.com" target="_blank" class="social-icon instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://www.youtube.com" target="_blank" class="social-icon youtube">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <a href="https://www.twitter.com" target="_blank" class="social-icon twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://www.facebook.com" target="_blank" class="social-icon facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz4fnFO9gybD2vY9hh6b37d4QbHVqHZmE9D23tYVwVtWqM1oypB6+g8t5iJ" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        // Enhanced mobile toggle functionality
        const mobileToggle = document.getElementById('mobileToggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        if (mobileToggle) {
            mobileToggle.addEventListener('click', () => {
                sidebar.classList.toggle('responsive');
                mainContent.classList.toggle('shifted');
                
                // Update toggle icon
                const icon = mobileToggle.querySelector('i');
                if (sidebar.classList.contains('responsive')) {
                    icon.classList.replace('fa-bars', 'fa-times');
                } else {
                    icon.classList.replace('fa-times', 'fa-bars');
                }
            });
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && !mobileToggle.contains(e.target)) {
                    sidebar.classList.remove('responsive');
                    mainContent.classList.remove('shifted');
                    const icon = mobileToggle.querySelector('i');
                    icon.classList.replace('fa-times', 'fa-bars');
                }
            }
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('responsive');
                mainContent.classList.remove('shifted');
                const icon = mobileToggle.querySelector('i');
                icon.classList.replace('fa-times', 'fa-bars');
            }
        });

        // Smooth scrolling for better UX
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add loading state to buttons
        document.querySelectorAll('.btn').forEach(button => {
            button.addEventListener('click', function() {
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
                this.disabled = true;
                
                // Simulate loading time (remove this in production)
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                }, 2000);
            });
        });
    </script>
</body>
</html>