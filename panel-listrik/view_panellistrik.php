<?php
$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "maintenance";

// Create connection
$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to avoid encoding issues
$conn->set_charset("utf8");

// Initialize filter variables for work orders
$search_wo = isset($_GET['search_wo']) ? trim($_GET['search_wo']) : '';
$search_plant = isset($_GET['search_plant']) ? trim($_GET['search_plant']) : '';
$search_date_from = isset($_GET['search_date_from']) ? trim($_GET['search_date_from']) : '';
$search_date_to = isset($_GET['search_date_to']) ? trim($_GET['search_date_to']) : '';

// Initialize filter variables for panel measurements
$panel_search = isset($_GET['panel_search']) ? trim($_GET['panel_search']) : '';
$panel_date_filter = isset($_GET['panel_date_filter']) ? trim($_GET['panel_date_filter']) : '';

// Query to get work order data with JOIN
$sql = "SELECT 
    d.id_date as id_date,
    d.tanggal,
    w.nomer as no_wo,
    p.nama as plant_name,
    l.nama as lokasi,
    f.bulan,
    f.nama as nama_frequensi
FROM date d
LEFT JOIN no_wo w ON d.id_date = w.id_date
LEFT JOIN plant p ON d.id_date = p.id_date
LEFT JOIN lokasi l ON d.id_date = l.id_date
LEFT JOIN frequensi f ON d.id_date = f.id_date
WHERE 1=1";

// Add search conditions for work orders
$params = array();
$types = "";

if (!empty($search_wo)) {
    $sql .= " AND w.nomer LIKE ?";
    $params[] = "%" . $search_wo . "%";
    $types .= "s";
}

if (!empty($search_plant)) {
    $sql .= " AND p.nama LIKE ?";
    $params[] = "%" . $search_plant . "%";
    $types .= "s";
}

if (!empty($search_date_from)) {
    $sql .= " AND d.tanggal >= ?";
    $params[] = $search_date_from;
    $types .= "s";
}

if (!empty($search_date_to)) {
    $sql .= " AND d.tanggal <= ?";
    $params[] = $search_date_to;
    $types .= "s";
}

$sql .= " ORDER BY d.tanggal DESC";

// Prepare and execute work order query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$has_data = $result->num_rows > 0;

// Query to get panel measurements with filtering
$sql_panel = "SELECT pl.*, d.tanggal 
              FROM panel_listrik pl
              JOIN date d ON pl.id_date = d.id_date
              WHERE 1=1";
$panel_params = array();
$panel_types = "";

if (!empty($panel_search)) {
    $sql_panel .= " AND (pl.pengukuran LIKE ? OR pl.standar LIKE ? OR pl.hasil_1 LIKE ? OR pl.hasil_2 LIKE ? OR pl.hasil_3 LIKE ? OR pl.hasil_4 LIKE ?)";
    $search_term = "%" . $panel_search . "%";
    $panel_params = array_fill(0, 6, $search_term);
    $panel_types = str_repeat("s", 6);
}

if (!empty($panel_date_filter)) {
    $sql_panel .= " AND d.tanggal = ?";
    $panel_params[] = $panel_date_filter;
    $panel_types .= "s";
}

$sql_panel .= " ORDER BY d.tanggal DESC, pl.id";

$stmt_panel = $conn->prepare($sql_panel);
if (!empty($panel_params)) {
    $stmt_panel->bind_param($panel_types, ...$panel_params);
}
$stmt_panel->execute();
$result_panel = $stmt_panel->get_result();
$has_panel_data = $result_panel->num_rows > 0;

// Function to get measurement details by date ID
function getDetailPengukuran($conn, $id_date) {
    $sql_detail = "SELECT pengukuran, standar, hasil_1, hasil_2, hasil_3, hasil_4 
                   FROM panel_listrik 
                   WHERE id_date = ? 
                   ORDER BY id";
    
    $stmt_detail = $conn->prepare($sql_detail);
    $stmt_detail->bind_param("i", $id_date);
    $stmt_detail->execute();
    $result_detail = $stmt_detail->get_result();
    
    $measurements = array();
    while ($row = $result_detail->fetch_assoc()) {
        $measurements[] = $row;
    }
    
    $stmt_detail->close();
    return $measurements;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VIEW DATA PREVENTIF PANEL LISTRIK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
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
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-gray);
            margin: 0;
            padding: 0;
            line-height: 1.6;
            color: #333;
        }

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
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border-radius: 50%;
            padding: 10px;
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

        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
        }

        .filter-section {
            background-color: var(--white);
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: var(--shadow);
            border-left: 5px solid var(--primary-color);
        }

        .filter-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--medium-gray);
        }

        .data-section {
            background-color: var(--white);
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: var(--shadow);
            border-left: 5px solid var(--primary-color);
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--medium-gray);
        }

        .btn-custom {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            color: var(--white);
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 51, 102, 0.3);
            font-size: 0.85rem;
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 51, 102, 0.4);
            color: var(--white);
        }

        .btn-detail {
            background: linear-gradient(45deg, var(--success-color), #20c997);
        }

        .btn-edit {
            background: linear-gradient(45deg, var(--warning-color), #fd7e14);
        }

        .btn-delete {
            background: linear-gradient(45deg, var(--danger-color), #e74c3c);
        }

        .btn-print {
            background: linear-gradient(45deg, #6f42c1, #8b5cf6);
        }

        .btn-back {
            background: linear-gradient(45deg, #6c757d, #868e96);
        }

        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .table {
            margin-bottom: 0;
        }

        .table thead {
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }

        .table thead th {
            color: var(--white);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            border: none;
            padding: 15px 12px;
            vertical-align: middle;
        }

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background-color: rgba(0, 51, 102, 0.05);
        }

        .table tbody td {
            padding: 12px;
            vertical-align: middle;
            border-color: #e9ecef;
        }

        .modal-header {
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            border-radius: 10px 10px 0 0;
        }

        .modal-title {
            font-weight: 600;
        }

        .modal-body {
            padding: 25px;
        }

        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .detail-table th,
        .detail-table td {
            padding: 8px 12px;
            border: 1px solid #ddd;
            text-align: left;
            font-size: 0.9rem;
        }

        .detail-table th {
            background-color: var(--light-gray);
            font-weight: 600;
            color: var(--primary-color);
        }

        .detail-table tr:nth-child(even) {
            background-color: rgba(0, 51, 102, 0.02);
        }

        .alert-custom {
            border-radius: 8px;
            border: none;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
        }

        .form-control, .form-select {
            border: 2px solid var(--medium-gray);
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #adb5bd;
        }

        .empty-state h4 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .empty-state p {
            font-size: 1rem;
            margin-bottom: 20px;
        }

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

        .instagram:hover { background-color: #E4405F !important; color: white; }
        .youtube:hover { background-color: #FF0000 !important; color: white; }
        .twitter:hover { background-color: #1DA1F2 !important; color: white; }
        .facebook:hover { background-color: #3b5998 !important; color: white; }

        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .bg-warning {
            background-color: #ffc107 !important;
            padding: 0 2px;
            border-radius: 2px;
        }

        @media (max-width: 768px) {
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

            .main-container {
                padding: 0 15px;
            }

            .table-responsive {
                font-size: 0.8rem;
            }

            .btn-custom {
                padding: 6px 12px;
                font-size: 0.75rem;
            }
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            margin-top: 15px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 4px;
            margin: 0 2px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
        }
    </style>
</head>
<body>
    <!-- Logo Container -->
    <div class="logo-container">
        <div class="logo">
            <i class="fas fa-water" style="font-size: 40px; color: var(--primary-color);"></i>
        </div>
        <div class="judul">PERUMDA AIR MINUM JAYA</div>
        <div class="logo">
            <i class="fas fa-city" style="font-size: 40px; color: var(--primary-color);"></i>
        </div>
    </div>

    <h1>VIEW DATA PREVENTIF PANEL LISTRIK</h1>

    <div class="main-container">
        <!-- Filter Section for Work Orders -->
        <div class="filter-section">
            <h3 class="filter-title">
                <i class="fas fa-search me-2"></i>Filter Data Work Order
            </h3>
            <form method="GET" action="">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="search_wo" class="form-label">No. WO</label>
                        <input type="text" class="form-control" id="search_wo" name="search_wo" 
                               value="<?php echo htmlspecialchars($search_wo); ?>" placeholder="Cari No. WO">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="search_plant" class="form-label">Plant</label>
                        <input type="text" class="form-control" id="search_plant" name="search_plant" 
                               value="<?php echo htmlspecialchars($search_plant); ?>" placeholder="Cari Plant">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="search_date_from" class="form-label">Tanggal Dari</label>
                        <input type="date" class="form-control" id="search_date_from" name="search_date_from" 
                               value="<?php echo htmlspecialchars($search_date_from); ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="search_date_to" class="form-label">Tanggal Sampai</label>
                        <input type="date" class="form-control" id="search_date_to" name="search_date_to" 
                               value="<?php echo htmlspecialchars($search_date_to); ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-custom me-2">
                            <i class="fas fa-search me-1"></i>Cari
                        </button>
                        <a href="view_panellistrik.php" class="btn btn-custom btn-back">
                            <i class="fas fa-refresh me-1"></i>Reset
                        </a>
                        <a href="input_panel_listrik.php" class="btn btn-custom btn-detail ms-2">
                            <i class="fas fa-plus me-1"></i>Tambah Data Baru
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Data Table Section -->
        <div class="data-section">
            <h3 class="section-title">
                <i class="fas fa-table me-2"></i>Data Preventif Panel Listrik
            </h3>
            
            <?php if (!$has_data): ?>
                <div class="empty-state">
                    <i class="fas fa-database"></i>
                    <h4>Data Kosong</h4>
                    <p>Tidak ada data inspeksi Panel Listrik yang tersedia saat ini.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped" id="dataTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>No. WO</th>
                                <th>Plant</th>
                                <th>Lokasi</th>
                                <th>Frekuensi</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            while($row = $result->fetch_assoc()): 
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                                <td><?php echo htmlspecialchars($row['no_wo']); ?></td>
                                <td><?php echo htmlspecialchars($row['plant_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['lokasi']); ?></td>
                                <td><?php echo htmlspecialchars($row['bulan'] . ' - ' . $row['nama_frequensi']); ?></td>
                                <td>
                                    <span class="status-badge status-completed">
                                        <i class="fas fa-check-circle me-1"></i>Completed
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-custom btn-detail btn-sm me-1" 
                                            onclick="showDetail(<?php echo $row['id_date']; ?>)">
                                        <i class="fas fa-eye"></i> Detail
                                    </button>
                                    <button type="button" class="btn btn-custom btn-print btn-sm me-1" 
                                            onclick="printData(<?php echo $row['id_date']; ?>)">
                                        <i class="fas fa-print"></i> Print
                                    </button>
                                    <button type="button" class="btn btn-custom btn-delete btn-sm" 
                                            onclick="deleteData(<?php echo $row['id_date']; ?>)">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Panel Measurements Table -->
            <div class="mt-5">
                <h3 class="section-title">
                    <i class="fas fa-table me-2"></i>Detail Pengukuran Panel Listrik
                </h3>
                
                <!-- Filter Section for Panel Measurements -->
                <div class="filter-section mb-4">
                    <h4 class="filter-title">
                        <i class="fas fa-filter me-2"></i>Filter Pengukuran
                    </h4>
                    <form method="GET" action="">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="panel_search" class="form-label">Cari Pengukuran</label>
                                <input type="text" class="form-control" id="panel_search" name="panel_search" 
                                       value="<?php echo htmlspecialchars($panel_search); ?>" 
                                       placeholder="Cari berdasarkan pengukuran, standar, atau hasil">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="panel_date_filter" class="form-label">Filter Tanggal</label>
                                <input type="date" class="form-control" id="panel_date_filter" name="panel_date_filter" 
                                       value="<?php echo htmlspecialchars($panel_date_filter); ?>">
                            </div>
                            <div class="col-md-4 mb-3 d-flex align-items-end">
                                <div>
                                    <button type="submit" class="btn btn-custom me-2">
                                        <i class="fas fa-search me-1"></i> Filter
                                    </button>
                                    <a href="view_panellistrik.php?search_wo=<?php echo urlencode($search_wo); ?>&search_plant=<?php echo urlencode($search_plant); ?>&search_date_from=<?php echo urlencode($search_date_from); ?>&search_date_to=<?php echo urlencode($search_date_to); ?>" 
                                       class="btn btn-custom btn-back">
                                        <i class="fas fa-times me-1"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!-- Hidden fields to maintain work order filters -->
                        <input type="hidden" name="search_wo" value="<?php echo htmlspecialchars($search_wo); ?>">
                        <input type="hidden" name="search_plant" value="<?php echo htmlspecialchars($search_plant); ?>">
                        <input type="hidden" name="search_date_from" value="<?php echo htmlspecialchars($search_date_from); ?>">
                        <input type="hidden" name="search_date_to" value="<?php echo htmlspecialchars($search_date_to); ?>">
                    </form>
                </div>
                
                <?php if (!$has_panel_data): ?>
                    <div class="empty-state">
                        <i class="fas fa-database"></i>
                        <h4>Data Kosong</h4>
                        <p>Tidak ada data pengukuran Panel Listrik yang tersedia saat ini.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped" id="panelTable">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="10%">Tanggal</th>
                                    <th width="15%">Pengukuran</th>
                                    <th width="15%">Standar</th>
                                    <th width="15%">Hasil 1</th>
                                    <th width="15%">Hasil 2</th>
                                    <th width="15%">Hasil 3</th>
                                    <th width="10%">Hasil 4</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                while ($row = $result_panel->fetch_assoc()):
                                    // Highlight search term in results
                                    $highlight = function($text) use ($panel_search) {
                                        if (!empty($panel_search)) {
                                            return preg_replace(
                                                "/(" . preg_quote($panel_search, '/') . ")/i", 
                                                '<span class="bg-warning">$1</span>', 
                                                htmlspecialchars($text)
                                            );
                                        }
                                        return htmlspecialchars($text);
                                    };
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                                    <td><?php echo $highlight($row['pengukuran']); ?></td>
                                    <td><?php echo $highlight($row['standar']); ?></td>
                                    <td><?php echo $highlight($row['hasil_1']); ?></td>
                                    <td><?php echo $highlight($row['hasil_2']); ?></td>
                                    <td><?php echo $highlight($row['hasil_3']); ?></td>
                                    <td><?php echo $highlight($row['hasil_4']); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">
                        <i class="fas fa-clipboard-list me-2"></i>Detail Data Preventif Panel Listrik
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="detailContent">
                        <!-- Content will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-custom btn-back" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>PERUMDA AIR MINUM JAYA</h5>
                    <p>Jl. Contoh No. 123, Kota Contoh</p>
                    <p>Telp: (021) 12345678</p>
                    <p>Email: info@perumdajaya.com</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h5>Follow Us</h5>
                    <div class="social-media">
                        <a href="#" class="social-icon instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon youtube"><i class="fab fa-youtube"></i></a>
                        <a href="#" class="social-icon twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon facebook"><i class="fab fa-facebook-f"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2023 PERUMDA AIR MINUM JAYA. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <script>
        // Initialize DataTable for Work Orders
        $(document).ready(function() {
            $('#dataTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
                },
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
                "order": [[1, "desc"]], // Sort by date column
                "columnDefs": [
                    { "orderable": false, "targets": [7] } // Disable sorting for action column
                ]
            });

            // Initialize DataTable for Panel Measurements
            $('#panelTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
                },
                "pageLength": 22,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
                "order": [[1, "desc"]], // Sort by date column
                "columnDefs": [
                    { "orderable": false, "targets": [0] } // Disable sorting for No column
                ]
            });
        });

        // Show detail function
        function showDetail(idDate) {
            // Show loading
            $('#detailContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Loading...</p></div>');
            $('#detailModal').modal('show');
            
            // Load detail data via AJAX
            $.ajax({
                url: 'get_detail_panellistrik.php',
                type: 'POST',
                data: { id_date: idDate },
                success: function(response) {
                    $('#detailContent').html(response);
                },
                error: function() {
                    $('#detailContent').html('<div class="alert alert-danger">Error loading data</div>');
                }
            });
        }

        // Print function
        function printData(idDate) {
            window.open('print_panellistrik.php?id=' + idDate, '_blank');
        }

        // Delete function
        function deleteData(idDate) {
            if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                $.ajax({
                    url: 'delete_panellistrik.php',
                    type: 'POST',
                    data: { id_date: idDate },
                    success: function(response) {
                        if (response.trim() === 'success') {
                            alert('Data berhasil dihapus');
                            location.reload();
                        } else {
                            alert('Error: ' + response);
                        }
                    },
                    error: function() {
                        alert('Error deleting data');
                    }
                });
            }
        }

        // Auto-refresh every 5 minutes
        setInterval(function() {
            location.reload();
        }, 300000);
    </script>
</body>
</html>

<?php
$stmt->close();
$stmt_panel->close();
$conn->close();
?>