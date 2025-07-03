<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['session_username'])) {
    header("Location: login.php"); 
    exit();
}

// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'maintenance';
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get date from GET parameter
$selected_date = $_GET['date'] ?? '';
$message = '';
$has_data = false;

// Base query for summary statistics
$stats_query = "SELECT 
                COUNT(*) as total_units, 
                SUM(CASE WHEN dev_0 <= 0.5 AND dev_40 <= 0.5 AND dev_60 <= 0.5 AND dev_100 <= 0.5 THEN 1 ELSE 0 END) as good_units,
                SUM(CASE WHEN dev_0 > 0.5 OR dev_40 > 0.5 OR dev_60 > 0.5 OR dev_100 > 0.5 THEN 1 ELSE 0 END) as not_good_units
                FROM pressure_indikator";

// Apply date filter if date is selected
if (!empty($selected_date)) {
    // First get the id_date for the selected date
    $sql_date = "SELECT id_date FROM date WHERE tanggal = ?";
    $stmt_date = $conn->prepare($sql_date);
    $stmt_date->bind_param('s', $selected_date);
    $stmt_date->execute();
    $result_date = $stmt_date->get_result();
    
    if ($result_date->num_rows > 0) {
        $row = $result_date->fetch_assoc();
        $id_date = $row['id_date'];
        $has_data = true;
        
        // Update stats query with date filter
        $stats_query = "SELECT 
                        COUNT(*) as total_units, 
                        SUM(CASE WHEN dev_0 <= 0.5 AND dev_40 <= 0.5 AND dev_60 <= 0.5 AND dev_100 <= 0.5 THEN 1 ELSE 0 END) as good_units,
                        SUM(CASE WHEN dev_0 > 0.5 OR dev_40 > 0.5 OR dev_60 > 0.5 OR dev_100 > 0.5 THEN 1 ELSE 0 END) as not_good_units
                        FROM pressure_indikator
                        WHERE id_date = ?";
        
        // Prepare and execute stats query with date filter
        $stmt_stats = $conn->prepare($stats_query);
        $stmt_stats->bind_param('i', $id_date);
        $stmt_stats->execute();
        $stats_result = $stmt_stats->get_result();
        $stats = $stats_result->fetch_assoc();
        
        // Query for pressure indicator data with proper joins
        $sql_pressure = "SELECT pi.*, d.tanggal, nw.nomer as no_wo, p.nama as plant, 
                        l.nama as lokasi, f.nama as frequensi
                        FROM pressure_indikator pi
                        JOIN date d ON pi.id_date = d.id_date
                        JOIN no_wo nw ON pi.id_date = nw.id_date
                        JOIN plant p ON pi.id_date = p.id_date
                        JOIN lokasi l ON pi.id_date = l.id_date
                        JOIN frequensi f ON pi.id_date = f.id_date
                        WHERE d.tanggal = ?";
        $stmt_pressure = $conn->prepare($sql_pressure);
        $stmt_pressure->bind_param('s', $selected_date);
        $stmt_pressure->execute();
        $result_pressure = $stmt_pressure->get_result();

        // Query for maintenance data
        $maintenance_query = "SELECT nw.nomer as no_wo, d.tanggal, p.nama as plant, 
                            l.nama as location, f.bulan, f.nama as nama
                            FROM no_wo nw
                            JOIN date d ON nw.id_date = d.id_date
                            JOIN plant p ON nw.id_date = p.id_date
                            JOIN lokasi l ON nw.id_date = l.id_date
                            JOIN frequensi f ON nw.id_date = f.id_date
                            WHERE d.tanggal = ?";
        $stmt_maintenance = $conn->prepare($maintenance_query);
        $stmt_maintenance->bind_param('s', $selected_date);
        $stmt_maintenance->execute();
        $result_maintenance = $stmt_maintenance->get_result();

    } else {
        $message = "No data found for selected date.";
    }
} else {
    // Get all data if no date is selected
    $sql_pressure = "SELECT pi.*, d.tanggal, nw.nomer as no_wo, p.nama as plant, 
                    l.nama as lokasi, f.nama as frequensi
                    FROM pressure_indikator pi
                    JOIN date d ON pi.id_date = d.id_date
                    JOIN no_wo nw ON pi.id_date = nw.id_date
                    JOIN plant p ON pi.id_date = p.id_date
                    JOIN lokasi l ON pi.id_date = l.id_date
                    JOIN frequensi f ON pi.id_date = f.id_date";
    $result_pressure = $conn->query($sql_pressure);
    $has_data = $result_pressure->num_rows > 0;

    // Get stats for all data
    $stats_result = $conn->query($stats_query);
    $stats = $stats_result->fetch_assoc();

    // Get ALL maintenance data when no date is selected
    $maintenance_query = "SELECT nw.nomer as no_wo, d.tanggal, p.nama as plant, 
                        l.nama as location, f.bulan, f.nama as nama
                        FROM no_wo nw
                        JOIN date d ON nw.id_date = d.id_date
                        JOIN plant p ON nw.id_date = p.id_date
                        JOIN lokasi l ON nw.id_date = l.id_date
                        JOIN frequensi f ON nw.id_date = f.id_date";
    $result_maintenance = $conn->query($maintenance_query);
}

// Handle null values in stats
$stats['total_units'] = $stats['total_units'] ?? 0;
$stats['good_units'] = $stats['good_units'] ?? 0;
$stats['not_good_units'] = $stats['not_good_units'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Verifikasi Pressure Indikator | PERUMDA AIR MINUM JAYA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="website icon" type="png" href="../image/logo.png">
    <style>
        :root {
            --primary-color: #003366;
            --secondary-color: #004d99;
            --accent-color: #ff9800;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --success-color: #198754;
            --danger-color: #dc3545;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
            padding-top: 56px;
        }
        
        .navbar {
            background-color: var(--primary-color);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand img {
            height: 40px;
            margin-right: 10px;
        }
        
        .navbar-brand {
            font-weight: bold;
            color: white !important;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-header {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }
        
        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background-color: var(--primary-color);
        }
        
        .page-header h1 {
            color: var(--primary-color);
            font-size: 1.8rem;
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        .page-header p {
            color: #6c757d;
            margin-bottom: 0;
        }
        
        .filter-section {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            font-weight: bold;
            padding: 15px 20px;
            border-bottom: none;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            background-color: rgba(0, 51, 102, 0.05);
            color: var(--primary-color);
            font-weight: bold;
            border-bottom-width: 1px;
            text-transform: uppercase;
            font-size: 0.85rem;
            vertical-align: middle;
        }
        
        .table td {
            vertical-align: middle;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(0, 51, 102, 0.02);
        }
        
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .badge-primary {
            background-color: var(--primary-color);
        }
        
        .badge-success {
            background-color: var(--success-color);
        }
        
        .badge-warning {
            background-color: var(--accent-color);
            color: white;
        }
        
        .badge-danger {
            background-color: var(--danger-color);
        }
        
        .date-badge {
            background-color: var(--primary-color);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: normal;
            display: inline-block;
            margin-left: 10px;
        }
        
        .summary-card {
            text-align: center;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: white;
            transition: transform 0.3s;
        }
        
        .summary-card:hover {
            transform: translateY(-5px);
        }
        
        .summary-card.primary {
            background-color: var(--primary-color);
        }
        
        .summary-card.success {
            background-color: var(--success-color);
        }
        
        .summary-card.warning {
            background-color: var(--accent-color);
        }
        
        .summary-card.danger {
            background-color: var(--danger-color);
        }
        
        .summary-card h3 {
            font-size: 2rem;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .summary-card p {
            font-size: 0.9rem;
            margin-bottom: 0;
            opacity: 0.8;
        }
        
        .summary-card i {
            font-size: 2rem;
            margin-bottom: 15px;
            opacity: 0.8;
        }
        
        .back-btn {
            margin-bottom: 20px;
        }
        
        .empty-state {
            text-align: center;
            padding: 30px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #adb5bd;
        }
        
        footer {
            background-color: var(--dark-color);
            color: white;
            padding: 30px 0;
            margin-top: 30px;
        }
        
        .footer-content {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        
        .footer-logo {
            width: 100px;
            margin-bottom: 15px;
        }
        
        .footer-links h5 {
            font-size: 1.1rem;
            margin-bottom: 15px;
            color: var(--light-color);
        }
        
        .footer-links ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .footer-links li {
            margin-bottom: 8px;
        }
        
        .footer-links a {
            color: #adb5bd;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-links a:hover {
            color: white;
        }
        
        .social-links {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .social-icon {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: white;
            transition: background-color 0.3s;
        }
        
        .social-icon:hover {
            background-color: var(--accent-color);
        }
        
        .copyright {
            text-align: center;
            padding-top: 20px;
            margin-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #adb5bd;
            font-size: 0.9rem;
        }
        
        .modal-content {
            border-radius: 8px;
            border: none;
        }
        
        .modal-header {
            background-color: var(--primary-color);
            color: white;
            border-bottom: none;
        }
        
        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 1.5rem;
            }
            
            .navbar-brand span {
                display: none;
            }
            
            .summary-cards .col-md-3 {
                margin-bottom: 15px;
            }
            
            .footer-content > div {
                width: 100%;
                margin-bottom: 30px;
            }
        }
        
        /* Print styles */
        @media print {
            body {
                background-color: white;
                padding-top: 0;
            }
            
            .navbar, .filter-section, .back-btn, footer, .btn, .no-print {
                display: none !important;
            }
            
            .container {
                width: 100%;
                max-width: 100%;
                padding: 0;
            }
            
            .card {
                box-shadow: none;
                margin-bottom: 20px;
            }
            
            .card-header {
                background-color: #f8f9fa !important;
                color: black !important;
                border: 1px solid #dee2e6;
            }
            
            .table th {
                background-color: #f8f9fa !important;
                color: black !important;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="../dashboard.php">
                <img src="../image/logo.png" alt="PAM Jaya Logo"> <span>PERUMDA AIR MINUM JAYA</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Pressure Indikator</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#userModal">
                            <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['session_username']); ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav><br><br>

    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>Data Verifikasi Pressure Indikator
                <?php if(!empty($selected_date)): ?>
                <span class="date-badge">
                    <i class="far fa-calendar-alt me-1"></i>
                    <?php echo date('d F Y', strtotime($selected_date)); ?>
                </span>
                <?php endif; ?>
            </h1>
            <p>Laporan detail hasil verifikasi pressure indikator dan data maintenance.</p>
        </div>

        <!-- Action Buttons -->
        <div class="row mb-4">
            <div class="col-md-6">
                <a href="input_pressure.php" class="btn btn-secondary me-2">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
                <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                <button onclick="window.print()" class="btn btn-outline-dark">
                    <i class="fas fa-print me-1"></i> Cetak
                </button>
            </div>
            <div class="col-md-6 text-md-end">
                <button type="button" class="btn btn-success" onclick="exportToExcel()">
                    <i class="fas fa-file-excel me-1"></i> Export Excel
                </button>
            </div>
        </div>

        <!-- Display error message if any -->
        <?php if (!empty($message)): ?>
        <div class="alert alert-warning">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <!-- Summary Cards -->
        <div class="row summary-cards mb-4">
            <div class="col-md-4 col-sm-6">
                <div class="summary-card primary">
                    <i class="fas fa-tachometer-alt"></i>
                    <h3><?php echo number_format($stats['total_units']); ?></h3>
                    <p>Total Unit</p>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="summary-card success">
                    <i class="fas fa-check-circle"></i>
                    <h3><?php echo number_format($stats['good_units']); ?></h3>
                    <p>Kondisi Normal</p>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="summary-card danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <h3><?php echo number_format($stats['not_good_units']); ?></h3>
                    <p>Kondisi Tidak Normal</p>
                </div>
            </div>
        </div>

        <!-- Maintenance Data -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #003366; color: white;">
                <span><i class="fas fa-clipboard-list me-2"></i> Data Maintenance</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>No WO</th>
                                <th>Tanggal WO</th>
                                <th>Plant</th>
                                <th>Lokasi</th>
                                <th colspan="2" class="text-center">Frequensi</th>
                            </tr>
                            <tr>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th>Bulan</th>
                                <th>Tahun</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result_maintenance && $result_maintenance->num_rows > 0) {
                                while ($row = $result_maintenance->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['no_wo']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['tanggal']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['plant']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['location']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['bulan'] ?? '-') . "</td>";
                                    echo "<td>" . htmlspecialchars($row['nama'] ?? '-') . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center'>Tidak ada data maintenance ditemukan.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pressure Indicator Data -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #003366; color: white;">
                <span><i class="fas fa-tachometer-alt me-2"></i> Data Verifikasi Pressure Indikator</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th rowspan="2">No</th>
                                <th rowspan="2">Kode</th>
                                <th rowspan="2">Deskripsi</th>
                                <th rowspan="2">Range Bar</th>
                                <th colspan="3" class="text-center">0%</th>
                                <th colspan="3" class="text-center">40%</th>
                                <th colspan="3" class="text-center">60%</th>
                                <th colspan="3" class="text-center">100%</th>
                                <th rowspan="2">Keterangan</th>
                            </tr>
                            <tr>
                                <!-- 0% -->
                                <th>Nilai</th>
                                <th>Std</th>
                                <th>Dev</th>
                                <!-- 40% -->
                                <th>Nilai</th>
                                <th>Std</th>
                                <th>Dev</th>
                                <!-- 60% -->
                                <th>Nilai</th>
                                <th>Std</th>
                                <th>Dev</th>
                                <!-- 100% -->
                                <th>Nilai</th>
                                <th>Std</th>
                                <th>Dev</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($has_data && $result_pressure->num_rows > 0) {
                                $result_pressure->data_seek(0);
                                $counter = 1;
                                while ($row = $result_pressure->fetch_assoc()): 
                                    // Determine status based on deviation
                                    $status_0 = $row['dev_0'] <= 0.5 ? 'success' : 'danger';
                                    $status_40 = $row['dev_40'] <= 0.5 ? 'success' : 'danger';
                                    $status_60 = $row['dev_60'] <= 0.5 ? 'success' : 'danger';
                                    $status_100 = $row['dev_100'] <= 0.5 ? 'success' : 'danger';
                            ?>
                                <tr>
                                    <td><?= $counter++ ?></td>
                                    <td><?= htmlspecialchars($row['kode']) ?></td>
                                    <td><?= htmlspecialchars($row['deskripsi']) ?></td>
                                    <td><?= htmlspecialchars($row['range_bar']) ?></td>
                                    
                                    <!-- 0% Data -->
                                    <td><?= htmlspecialchars($row['nilai_0']) ?></td>
                                    <td><?= htmlspecialchars($row['std_0']) ?></td>
                                    <td><span class="badge bg-<?= $status_0 ?>"><?= htmlspecialchars($row['dev_0']) ?></span></td>
                                    
                                    <!-- 40% Data -->
                                    <td><?= htmlspecialchars($row['nilai_40']) ?></td>
                                    <td><?= htmlspecialchars($row['std_40']) ?></td>
                                    <td><span class="badge bg-<?= $status_40 ?>"><?= htmlspecialchars($row['dev_40']) ?></span></td>
                                    
                                    <!-- 60% Data -->
                                    <td><?= htmlspecialchars($row['nilai_60']) ?></td>
                                    <td><?= htmlspecialchars($row['std_60']) ?></td>
                                    <td><span class="badge bg-<?= $status_60 ?>"><?= htmlspecialchars($row['dev_60']) ?></span></td>
                                    
                                    <!-- 100% Data -->
                                    <td><?= htmlspecialchars($row['nilai_100']) ?></td>
                                    <td><?= htmlspecialchars($row['std_100']) ?></td>
                                    <td><span class="badge bg-<?= $status_100 ?>"><?= htmlspecialchars($row['dev_100']) ?></span></td>
                                    
                                    <td><?= htmlspecialchars($row['keterangan']) ?></td>
                                </tr>
                            <?php 
                                endwhile;
                            } else {
                            ?>
                                <tr>
                                    <td colspan="19" class="text-center">Tidak ada data pressure indikator ditemukan</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="mt-5">
        <div class="container">
            <div class="footer-content">
                <div>
                    <img src="../image/logoputihpam.png" alt="PAM Jaya Logo" class="footer-logo">
                    <p>Perumda Air Minum Jaya berkomitmen untuk menyediakan air bersih berkualitas tinggi untuk Jakarta.</p>
                </div>
                <div class="footer-links">
                    <h5>Navigasi</h5>
                    <ul>
                        <li><a href="../dashboard.php">Dashboard</a></li>
                        <li><a href="index.php">Pressure Indikator</a></li>
                        <li><a href="input_pressure.php">Tambah Data</a></li>
                        <li><a href="view_pressure.php">Lihat Data</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h5>Kontak</h5>
                    <ul>
                        <li><a href="mailto:info@pamjaya.co.id"><i class="fas fa-envelope me-2"></i>info@pamjaya.co.id</a></li>
                        <li><a href="tel:+62211234567"><i class="fas fa-phone me-2"></i>+6221-1234567</a></li>
                        <li><a href="#"><i class="fas fa-map-marker-alt me-2"></i>Jakarta, Indonesia</a></li>
                    </ul>
                    <div class="social-links">
                        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2025 Perumda Air Minum Jaya. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Filter Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="filterModalLabel">Filter Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="view_pressure.php" method="GET">
                        <div class="mb-3">
                            <label for="date" class="form-label">Pilih Tanggal:</label>
                            <input type="date" class="form-control" id="date" name="date" value="<?php echo $selected_date; ?>">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- User Profile Modal -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel">Profil Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="display-1 text-primary">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <h4 class="mt-2"><?php echo htmlspecialchars($_SESSION['session_username']); ?></h4>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="../profile.php" class="btn btn-outline-primary">
                            <i class="fas fa-user-edit me-2"></i> Edit Profil
                        </a>
                        <a href="../logout.php" class="btn btn-danger">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to export table data to Excel
        function exportToExcel() {
            // Create a URL for the Excel file
            var uri = 'data:application/vnd.ms-excel;base64,';
            
            // Get HTML table element
            var template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Data Pressure Indikator</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--><meta http-equiv="content-type" content="text/plain; charset=UTF-8"/></head><body>';
            
            // Add table content
            template += document.querySelector('.table-responsive').innerHTML;
            template += '</body></html>';
            
            // Convert to base64
            var base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) };
            
            // Create download link
            var link = document.createElement("a");
            link.download = 'Data_Pressure_Indikator_<?php echo !empty($selected_date) ? $selected_date : date('Y-m-d'); ?>.xls';
            link.href = uri + base64(template);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>