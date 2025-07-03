<?php
session_start();

// Database configuration
$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "maintenance";

// Inisialisasi variabel
$total_records = 0;
$total_pages = 1;
$page = 1;
$result = null;
$success_message = '';
$error_message = '';
$plants = [];
$search = $date_from = $date_to = $plant_filter = '';

// Aktifkan error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Koneksi ke database
$conn = null;
try {
    $conn = new mysqli($servername, $username_db, $password_db, $dbname);
    $conn->set_charset("utf8mb4");

    // Handle aksi delete
    if (isset($_POST['delete_id']) && is_numeric($_POST['delete_id'])) {
        $delete_id = (int)$_POST['delete_id'];

        // Cek apakah record ada dan ambil id_date
        $check_sql = "SELECT id_date FROM insulation WHERE id = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        $check_result = $stmt->get_result();

        if ($check_result->num_rows > 0) {
            $row = $check_result->fetch_assoc();
            $id_date = $row['id_date'];

            try {
                $conn->begin_transaction();

                $queries = [
                    ["DELETE FROM insulation WHERE id = ?", "i", [$delete_id]],
                    ["DELETE FROM resistensi WHERE id_date = ?", "i", [$id_date]],
                    ["DELETE FROM frequensi WHERE id_date = ?", "i", [$id_date]],
                    ["DELETE FROM lokasi WHERE id_date = ?", "i", [$id_date]],
                    ["DELETE FROM plant WHERE id_date = ?", "i", [$id_date]],
                    ["DELETE FROM no_wo WHERE id_date = ?", "i", [$id_date]],
                    ["DELETE FROM date WHERE id_date = ?", "i", [$id_date]],
                ];

                foreach ($queries as $data) {
                    list($sql, $types, $params) = $data;
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param($types, ...$params);
                    $stmt->execute();
                    $stmt->close();
                }

                $conn->commit();
                $success_message = "Data berhasil dihapus!";
            } catch (Exception $e) {
                $conn->rollback();
                $error_message = "Gagal menghapus data: " . $e->getMessage();
            }
        } else {
            $error_message = "Data tidak ditemukan.";
        }

        // Redirect untuk hindari resubmit form
        $params = $_GET;
        unset($params['delete_id']);
        $redirect_url = $_SERVER['PHP_SELF'] . '?' . http_build_query($params);
        header("Location: $redirect_url");
        exit();
    }

    // Pengaturan pagination
    $records_per_page = 10;
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    $page = max(1, $page);
    $offset = ($page - 1) * $records_per_page;

    // Query filter
    $where_conditions = [];
    $params = [];
    $types = '';

    // Pencarian
    if (!empty($_GET['search'])) {
        $search = trim(htmlspecialchars_decode($_GET['search']));
        $like = "%{$search}%";
        $where_conditions[] = "(nw.nomer LIKE ? OR i.equipment LIKE ? OR l.nama LIKE ?)";
        array_push($params, $like, $like, $like);
        $types .= 'sss';
    }

    // Tanggal mulai
    if (!empty($_GET['date_from'])) {
        $date_from = $_GET['date_from'];
        if (DateTime::createFromFormat('Y-m-d', $date_from)) {
            $where_conditions[] = "d.tanggal >= ?";
            $params[] = $date_from;
            $types .= 's';
        }
    }

    // Tanggal akhir
    if (!empty($_GET['date_to'])) {
        $date_to = $_GET['date_to'];
        if (DateTime::createFromFormat('Y-m-d', $date_to)) {
            $where_conditions[] = "d.tanggal <= ?";
            $params[] = $date_to;
            $types .= 's';
        }
    }

    // Filter plant
    if (!empty($_GET['plant_filter'])) {
        $plant_filter = $_GET['plant_filter'];
        $where_conditions[] = "p.nama = ?";
        $params[] = $plant_filter;
        $types .= 's';
    }

    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

    // Hitung total data
    $count_sql = "
        SELECT COUNT(DISTINCT d.id_date) AS total 
        FROM date d
        JOIN no_wo nw ON d.id_date = nw.id_date
        JOIN plant p ON d.id_date = p.id_date
        JOIN lokasi l ON d.id_date = l.id_date
        JOIN frequensi f ON d.id_date = f.id_date
        LEFT JOIN insulation i ON d.id_date = i.id_date
        LEFT JOIN resistensi r ON d.id_date = r.id_date
        $where_clause
    ";
    $stmt_count = $conn->prepare($count_sql);
    if (!empty($params)) {
        $stmt_count->bind_param($types, ...$params);
    }
    $stmt_count->execute();
    $total_records = $stmt_count->get_result()->fetch_assoc()['total'];
    $total_pages = max(1, ceil($total_records / $records_per_page));

    // Jika halaman melebihi total halaman
    if ($page > $total_pages) {
        $page = $total_pages;
        $offset = ($page - 1) * $records_per_page;
    }

    // Ambil data utama (grouped by date)
    $sql = "
        SELECT 
            d.id_date,
            d.tanggal,
            nw.nomer AS no_wo,
            p.nama AS plant,
            l.nama AS lokasi,
            f.bulan,
            f.nama AS frequensi_nama
        FROM date d
        JOIN no_wo nw ON d.id_date = nw.id_date
        JOIN plant p ON d.id_date = p.id_date
        JOIN lokasi l ON d.id_date = l.id_date
        JOIN frequensi f ON d.id_date = f.id_date
        $where_clause
        GROUP BY d.id_date
        ORDER BY d.tanggal DESC, d.id_date DESC
        LIMIT ? OFFSET ?
    ";

    $final_params = $params;
    $final_params[] = $records_per_page;
    $final_params[] = $offset;
    $final_types = $types . 'ii';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($final_types, ...$final_params);
    $stmt->execute();
    $main_result = $stmt->get_result();
    $stmt->close();

    // Ambil daftar plant untuk dropdown filter
    $plants_result = $conn->query("SELECT DISTINCT nama FROM plant ORDER BY nama");
    while ($row = $plants_result->fetch_assoc()) {
        $plants[] = $row['nama'];
    }

} catch (Exception $e) {
    $error_message = "Terjadi kesalahan: " . $e->getMessage();
    error_log("Error pada insulation_view.php: " . $e->getMessage());
} finally {
    // Pastikan semua variable tetap ada walaupun error
    $total_records = $total_records ?? 0;
    $total_pages = max(1, $total_pages ?? 1);
    $page = max(1, $page ?? 1);
    $main_result = $main_result ?? null;
    $plants = $plants ?? [];

    if ($conn instanceof mysqli) {
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Data Insulation Test</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }

        .header .logo {
            font-size: 3rem;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .content {
            padding: 40px;
        }

        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
        }

        .stat-card i {
            font-size: 2.5rem;
            color: #667eea;
            margin-bottom: 15px;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #6b7280;
            font-weight: 500;
        }

        .controls-section {
            background: #f8fafc;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-control {
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5a67d8;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-sm {
            padding: 8px 12px;
            font-size: 12px;
        }

        .table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1200px;
        }

        .table th {
            background: #f8fafc;
            padding: 16px 12px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
            white-space: nowrap;
        }

        .table td.wrap-text {
            white-space: normal;
            max-width: 200px;
            word-wrap: break-word;
        }

        .table tr:hover {
            background: #f8fafc;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin: 2px;
            display: inline-block;
        }

        .badge-good {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-fair {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-poor {
            background: #fee2e2;
            color: #991b1b;
        }

        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        .no-data i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .no-data h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .no-data p {
            font-size: 1rem;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 30px;
        }

        .pagination a {
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            text-decoration: none;
            color: #374151;
            transition: all 0.3s ease;
        }

        .pagination a:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .pagination a.current {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .pagination span {
            padding: 8px 4px;
            color: #6b7280;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: white;
            margin: 10% auto;
            padding: 0;
            border-radius: 16px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }

        .modal-header {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            padding: 20px 30px;
            border-radius: 16px 16px 0 0;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .modal-header i {
            font-size: 1.5rem;
        }

        .modal-header h3 {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .modal-body {
            padding: 30px;
        }

        .modal-actions {
            padding: 20px 30px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        @media (max-width: 768px) {
            .page-container {
                margin: 10px;
            }
            
            .content {
                padding: 20px;
            }
            
            .header {
                padding: 30px 20px;
            }
            
            .header h1 {
                font-size: 1.8rem;
            }
            
            .filters-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                justify-content: center;
            }
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            background-color: #f9f9f9;
        }
        
        .details-table th {
            background-color: #e5e7eb;
            padding: 10px;
            text-align: left;
        }
        
        .details-table td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .header-row {
            cursor: pointer;
        }
        
        .header-row:hover {
            background-color: #f0f4f8 !important;
        }
        
        .toggle-details {
            margin-left: 5px;
        }
        
        .details-row {
            background-color: #f8fafc;
        }

        .test-section {
            margin-bottom: 30px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            background-color: white;
        }
        
        .test-section h3 {
            color: #004c99;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="page-container">
            <div class="header">
                <div class="logo">
                    <i class="fas fa-bolt"></i>
                </div>
                <h1>Data Insulation Test</h1>
                <p>Sistem Manajemen Data Maintenance Terintegrasi</p>
            </div>
            
            <div class="content">
                <!-- Alert messages -->
                <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
                <?php endif; ?>

                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <i class="fas fa-database"></i>
                        <div class="stat-number"><?php echo isset($total_records) ? htmlspecialchars($total_records) : '0'; ?></div>
                        <div class="stat-label">Total Records</div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-calendar"></i>
                        <div class="stat-number"><?php echo date('d'); ?></div>
                        <div class="stat-label"><?php echo date('M Y'); ?></div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-file-alt"></i>
                        <div class="stat-number"><?php echo isset($page) && isset($total_pages) ? htmlspecialchars($page) . '/' . htmlspecialchars($total_pages) : '1/1'; ?></div>
                        <div class="stat-label">Current Page</div>
                    </div>
                </div>

                <!-- Controls Section -->
                <div class="controls-section">
                    <form method="GET" action="">
                        <div class="filters-grid">
                            <div class="form-group">
                                <label for="search">Search</label>
                                <input type="text" id="search" name="search" class="form-control" 
                                       placeholder="No WO, Equipment, Lokasi..." 
                                       value="<?php echo isset($search) ? htmlspecialchars($search) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="date_from">Tanggal Dari</label>
                                <input type="date" id="date_from" name="date_from" class="form-control" 
                                       value="<?php echo isset($date_from) ? htmlspecialchars($date_from) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="date_to">Tanggal Sampai</label>
                                <input type="date" id="date_to" name="date_to" class="form-control" 
                                       value="<?php echo isset($date_to) ? htmlspecialchars($date_to) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="plant_filter">Filter Plant</label>
                                <select id="plant_filter" name="plant_filter" class="form-control">
                                    <option value="">Semua Plant</option>
                                    <?php if (isset($plants) && is_array($plants)): ?>
                                        <?php foreach ($plants as $plant): ?>
                                        <option value="<?php echo htmlspecialchars($plant); ?>" 
                                                <?php echo isset($plant_filter) && $plant_filter === $plant ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($plant); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="action-buttons">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                                Filter Data
                            </button>
                            <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="btn btn-secondary">
                                <i class="fas fa-refresh"></i>
                                Reset Filter
                            </button>
                            <a href="input_insulation.php" class="btn btn-success">
                                <i class="fas fa-plus"></i>
                                Tambah Data
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Data Table -->
                <div class="table-container">
                    <?php if (isset($main_result) && $main_result && $main_result->num_rows > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>No WO</th>
                                <th>Plant</th>
                                <th>Lokasi</th>
                                <th>Frequensi</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = isset($offset) ? $offset + 1 : 1;
                            $main_result->data_seek(0); // Reset result pointer
                            while ($row = $main_result->fetch_assoc()): 
                                // Reconnect to get detailed data for each record
                                $conn = new mysqli($servername, $username_db, $password_db, $dbname);
                                $id_date = $row['id_date'];
                                
                                // Get insulation test data
                                $insulation_sql = "SELECT * FROM insulation WHERE id_date = ?";
                                $stmt_insulation = $conn->prepare($insulation_sql);
                                $stmt_insulation->bind_param("i", $id_date);
                                $stmt_insulation->execute();
                                $insulation_result = $stmt_insulation->get_result();
                                
                                // Get resistance test data
                                $resistance_sql = "SELECT * FROM resistensi WHERE id_date = ?";
                                $stmt_resistance = $conn->prepare($resistance_sql);
                                $stmt_resistance->bind_param("i", $id_date);
                                $stmt_resistance->execute();
                                $resistance_result = $stmt_resistance->get_result();
                            ?>
                            <tr class="header-row" data-id="<?php echo $row['id_date']; ?>">
                                <td><?php echo $no++; ?></td>
                                <td><?php echo isset($row['tanggal']) ? date('d/m/Y', strtotime($row['tanggal'])) : '-'; ?></td>
                                <td><?php echo isset($row['no_wo']) ? htmlspecialchars($row['no_wo']) : '-'; ?></td>
                                <td><?php echo isset($row['plant']) ? htmlspecialchars($row['plant']) : '-'; ?></td>
                                <td class="wrap-text"><?php echo isset($row['lokasi']) ? htmlspecialchars($row['lokasi']) : '-'; ?></td>
                                <td>
                                    <?php 
                                    $freq = '';
                                    if (isset($row['bulan']) && isset($row['frequensi_nama'])) {
                                        $freq = htmlspecialchars($row['bulan'] . ' - ' . $row['frequensi_nama']);
                                    } elseif (isset($row['bulan'])) {
                                        $freq = htmlspecialchars($row['bulan']);
                                    } elseif (isset($row['frequensi']) || isset($row['frequensi_nama'])) {
                                        $freq = htmlspecialchars($row['frequensi_nama'] ?? $row['frequensi'] ?? '');
                                    }
                                    echo $freq ?: '-';
                                    ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="insulation_edit.php?id=<?php echo $row['id_date']; ?>" class="btn btn-primary btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $row['id_date']; ?>" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <button class="btn btn-info btn-sm toggle-details" data-id="<?php echo $row['id_date']; ?>" title="Show Details">
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <!-- Details Row -->
                            <tr class="details-row" id="details-<?php echo $row['id_date']; ?>" style="display: none;">
                                <td colspan="7">
                                    <?php if ($insulation_result->num_rows > 0): ?>
                                    <div class="test-section">
                                        <h3>Insulation Test</h3>
                                        <table class="details-table">
                                            <thead>
                                                <tr>
                                                    <th>Equipment</th>
                                                    <th>Measurement</th>
                                                    <th>Inject Volt</th>
                                                    <th>Result Insulation</th>
                                                    <th>DAR</th>
                                                    <th>PI</th>
                                                    <th>Condition</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($insulation_row = $insulation_result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($insulation_row['equipment']); ?></td>
                                                    <td><?php echo htmlspecialchars($insulation_row['meansurement']); ?></td>
                                                    <td><?php echo htmlspecialchars($insulation_row['inject_volt']); ?></td>
                                                    <td><?php echo htmlspecialchars($insulation_row['reasult_insulation']); ?></td>
                                                    <td><?php echo htmlspecialchars($insulation_row['dar']); ?></td>
                                                    <td><?php echo htmlspecialchars($insulation_row['pi']); ?></td>
                                                    <td>
                                                        <?php if (!empty($insulation_row['condition'])): ?>
                                                            <?php 
                                                            $condition = strtolower($insulation_row['condition']);
                                                            $badge_class = 'status-badge ';
                                                            if (strpos($condition, 'good') !== false) {
                                                                $badge_class .= 'badge-good';
                                                            } elseif (strpos($condition, 'fair') !== false) {
                                                                $badge_class .= 'badge-fair';
                                                            } else {
                                                                $badge_class .= 'badge-poor';
                                                            }
                                                            ?>
                                                            <span class="<?php echo $badge_class; ?>">
                                                                <?php echo htmlspecialchars($insulation_row['condition']); ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span>-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($resistance_result->num_rows > 0): ?>
                                    <div class="test-section">
                                        <h3>Resistance Test</h3>
                                        <table class="details-table">
                                            <thead>
                                                <tr>
                                                    <th>Equipment</th>
                                                    <th>Measurement</th>
                                                    <th>Result</th>
                                                    <th>Condition</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($resistance_row = $resistance_result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($resistance_row['equipment']); ?></td>
                                                    <td><?php echo htmlspecialchars($resistance_row['meansurement']); ?></td>
                                                    <td><?php echo htmlspecialchars($resistance_row['result']); ?></td>
                                                    <td>
                                                        <?php if (!empty($resistance_row['condition'])): ?>
                                                            <?php 
                                                            $condition = strtolower($resistance_row['condition']);
                                                            $badge_class = 'status-badge ';
                                                            if (strpos($condition, 'good') !== false) {
                                                                $badge_class .= 'badge-good';
                                                            } elseif (strpos($condition, 'fair') !== false) {
                                                                $badge_class .= 'badge-fair';
                                                            } else {
                                                                $badge_class .= 'badge-poor';
                                                            }
                                                            ?>
                                                            <span class="<?php echo $badge_class; ?>">
                                                                <?php echo htmlspecialchars($resistance_row['condition']); ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span>-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php 
                                $stmt_insulation->close();
                                $stmt_resistance->close();
                                $conn->close();
                            endwhile; 
                            ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="no-data">
                        <i class="fas fa-database"></i>
                        <h3>No Data Found</h3>
                        <p>Try adjusting your search or filter to find what you're looking for.</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if (isset($total_pages) && $total_pages > 1): ?>
                <div class="pagination">
                    <?php if (isset($page) && $page > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                        <i class="fas fa-angle-left"></i>
                    </a>
                    <?php endif; ?>

                    <?php 
                    $current_page = isset($page) ? $page : 1;
                    $start = max(1, $current_page - 2);
                    $end = min($total_pages, $current_page + 2);
                    
                    if ($start > 1) {
                        echo '<span>...</span>';
                    }
                    
                    for ($i = $start; $i <= $end; $i++): 
                    ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                       class="<?php echo $i == $current_page ? 'current' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>
                    
                    <?php if ($end < $total_pages): ?>
                    <span>...</span>
                    <?php endif; ?>

                    <?php if (isset($page) && $page < $total_pages): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Konfirmasi Hapus Data</h3>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus data ini? Data yang dihapus tidak dapat dikembalikan.</p>
            </div>
            <div class="modal-actions">
                <form method="POST" action="" style="display: inline;">
                    <input type="hidden" name="delete_id" id="delete_id" value="">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>            
                </form>
            </div>
        </div>
    </div>

    <script>
        // Delete button click handler
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                document.getElementById('delete_id').value = id;
                document.getElementById('deleteModal').style.display = 'block';
            });
        });

        // Close modal function
        function closeModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === document.getElementById('deleteModal')) {
                closeModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
        
        // Add this JavaScript to handle the toggle functionality
        document.querySelectorAll('.toggle-details').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const detailsRow = document.getElementById(`details-${id}`);
                const icon = this.querySelector('i');
                
                if (detailsRow.style.display === 'none') {
                    detailsRow.style.display = 'table-row';
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                } else {
                    detailsRow.style.display = 'none';
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                }
            });
        });
        
        // Optional: Add click handler for the entire header row
        document.querySelectorAll('.header-row').forEach(row => {
            row.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const button = this.querySelector('.toggle-details');
                if (button) {
                    button.click();
                }
            });
        });
    </script>
</body>
</html>