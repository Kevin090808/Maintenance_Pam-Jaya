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
    <title>FORMULIR VERIFIKASI PRESSURE INDIKATOR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="website icon" type="png" href="../image/logo.png">
    <style>
        :root {
            --primary: #003366;
            --secondary: #004d99;
            --accent: #007bff;
            --light: #f8f9fa;
            --medium: #e9ecef;
            --dark: #495057;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: var(--light);
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, var(--primary) 0%, #002244 100%);
            color: white;
            height: 100vh;
            padding: 25px;
            position: fixed;
            transition: all 0.3s;
        }

        .user-greeting {
            text-align: center;
            margin: 15px 0;
            padding: 10px;
            background-color: rgba(255,255,255,0.1);
            border-radius: 8px;
        }

        .inspection-list {
            list-style: none;
            padding: 0;
        }

        .inspection-list li a {
            color: white;
            padding: 12px 15px;
            border-radius: 6px;
            display: block;
            margin-bottom: 8px;
            transition: all 0.3s;
            background-color: rgba(255,255,255,0.05);
        }

        .inspection-list li a:hover {
            background-color: rgba(255,255,255,0.15);
            transform: translateX(5px);
        }

        .main-content {
            margin-left: 280px;
            flex-grow: 1;
            transition: all 0.3s;
        }

        .logo-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 40px;
            background: linear-gradient(90deg, var(--primary) 0%, #004080 100%);
            color: white;
        }

        .logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }

        .judul {
            font-size: 1.6rem;
            font-weight: 700;
            text-align: center;
        }

        h1 {
            margin: 30px 0;
            font-size: 2rem;
            color: var(--primary);
            text-align: center;
            position: relative;
        }

        h1::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
        }

        .form-container {
            max-width: 1400px;
            margin: 0 auto 50px;
            padding: 0 30px;
        }

        .form-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-left: 5px solid var(--primary);
        }

        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 30px;
        }

        .data-table thead {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            color: white;
        }

        .data-table th, .data-table td {
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid var(--medium);
        }

        .data-table tr:nth-child(even) {
            background-color: rgba(0,51,102,0.02);
        }

        .form-input {
            width: 100%;
            padding: 6px 8px;
            border: 1px solid var(--medium);
            border-radius: 4px;
        }

        footer {
            background: linear-gradient(90deg, #1a1a1a 0%, #2c2c2c 100%);
            color: white;
            padding: 40px 20px;
            margin-top: 50px;
        }

        .mobile-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1051;
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 12px;
            border-radius: 50%;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            
            .mobile-toggle {
                display: block;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="user-greeting">
            <i class="fas fa-user-circle me-2"></i>Welcome <?php echo htmlspecialchars($username); ?>
        </div>
        <ul class="inspection-list">
            <li><a href="view_pressure.php"><i class="fas fa-eye"></i>Lihat Data</a></li>
            <li><a href="input_pressure.php"><i class="fas fa-plus-circle"></i>Tambah Data</a></li>
            <li><a href="../dashboard.php"><i class="fas fa-arrow-left"></i>Kembali</a></li>
        </ul>
    </div>

    <!-- Mobile Toggle -->
    <button class="mobile-toggle" id="mobileToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="logo-container">
            <img src="../image/logo.png" alt="Logo PAM Jaya" class="logo">
            <div class="judul">PERUMDA AIR MINUM JAYA</div>
            <img src="../image/Jakarta.png" alt="Logo Jakarta" class="logo">
        </div>

        <h1>FORMULIR VERIFIKASI PRESSURE INDIKATOR</h1>
        
        <div class="form-container">
            <div class="form-card">
                <form autocomplete="off" spellcheck="false" id="verificationForm" method="post" action="proses_input_pressure.php">
                    <div class="header-info mb-4">
                        <table>
                            <tr><td>No. WO</td><td>: <span id="wo-number">-</span></td></tr>
                            <tr><td>Tanggal</td><td>: <?php echo date('d-m-Y'); ?></td></tr>
                            <tr><td>Plant</td><td>: <span id="plant-name">-</span></td></tr>
                            <tr><td>Lokasi</td><td>: <span id="location">-</span></td></tr>
                            <tr><td>Frekuensi</td><td>: <span id="frequency">-</span></td></tr>
                        </table>
                    </div>

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th rowspan="3">No</th>
                                <th rowspan="3">Kode</th>
                                <th rowspan="3">Deskripsi</th>
                                <th rowspan="3">Range Bar</th>
                                <th colspan="12">Data Verifikasi</th>
                                <th rowspan="3">Tanggal</th>
                                <th rowspan="3">Keterangan</th>
                            </tr>
                            <tr>
                                <th colspan="3">%</th>
                                <th colspan="3">40%</th>
                                <th colspan="3">60%</th>
                                <th colspan="3">100%</th>
                            </tr>
                            <tr>
                                <th>Nilai</th><th>Std</th><th>Dev</th>
                                <th>Nilai</th><th>Std</th><th>Dev</th>
                                <th>Nilai</th><th>Std</th><th>Dev</th>
                                <th>Nilai</th><th>Std</th><th>Dev</th>
                            </tr>
                            <tr>
                                <td></td>
                                <th>SITE:</th>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                            </tr>
                            <tr>
                                <td>2</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                            </tr>
                            <tr>
                                <td>3</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                            </tr>
                            <tr>
                                <td>4</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                            </tr>
                            <tr>
                                <td>5</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                            </tr>
                            <tr>
                                <td>6</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                            </tr>
                            <tr>
                                <td>7</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                            </tr>
                            <tr>
                                <td>8</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                            </tr>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>

        <footer>
            <div class="text-center py-3">
                <p>&copy; 2025 Perumda PAM Jaya. All Rights Reserved.</p>
                <div class="social-media">
                    <a href="#" class="text-white mx-2"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-white mx-2"><i class="fab fa-youtube"></i></a>
                    <a href="#" class="text-white mx-2"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-white mx-2"><i class="fab fa-facebook"></i></a>
                </div>
            </div>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile toggle functionality
        document.getElementById('mobileToggle').addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('mainContent').classList.toggle('shifted');
        });
    </script>
</body>
</html>