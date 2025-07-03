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

    // Initialize variables
    $message = '';
    $error = '';
    $selected_date = '';
    $selected_plant = '';
    $selected_location = '';
    $id_date = null;

    // Get filter parameters if set
    if (isset($_GET['date'])) {
        $selected_date = $_GET['date'];
    }

    if (isset($_GET['plant'])) {
        $selected_plant = $_GET['plant'];
    }

    if (isset($_GET['location'])) {
        $selected_location = $_GET['location'];
    }

    // Helper function to verify and format image path
    function validateImagePath($imagePath) {
        // If path is null or empty, return empty string
        if (empty($imagePath)) {
            return '';
        }
        
        // Check if the path includes a leading slash and add if missing
        if (strpos($imagePath, '/') !== 0 && strpos($imagePath, 'http') !== 0 && strpos($imagePath, '../') !== 0) {
            $imagePath = '../' . $imagePath;
        }
        
        // Return the validated path
        return $imagePath;
    }

    // Get id_date if date filter is selected
    if (!empty($selected_date)) {
        $sql_date = "SELECT id_date FROM date WHERE tanggal = ?";
        $stmt_date = $conn->prepare($sql_date);
        $stmt_date->bind_param('s', $selected_date);
        $stmt_date->execute();
        $result_date = $stmt_date->get_result();
        
        if ($result_date->num_rows > 0) {
            $row = $result_date->fetch_assoc();
            $id_date = $row['id_date'];
        }
    }

    // Build WHERE conditions for all queries
    $where_conditions = [];
    $params = [];
    $types = "";
    
    if (!empty($id_date)) {
        $where_conditions[] = "t.id_date = ?";
        $params[] = $id_date;
        $types .= "i";
    }
    
    // For plant and location filters, we need to join with appropriate tables
    $join_conditions = "";
    
    if (!empty($selected_plant)) {
        $join_conditions .= " JOIN plant p ON t.id = p.id";
        $where_conditions[] = "p.nama = ?";
        $params[] = $selected_plant;
        $types .= "s";
    }
    
    if (!empty($selected_location)) {
        $join_conditions .= " JOIN lokasi l ON t.id = l.id";
        $where_conditions[] = "l.nama = ?";
        $params[] = $selected_location;
        $types .= "s";
    }

    // Query untuk mengambil data type
    $sql_type = "SELECT t.nama AS nama, t.image_path AS image_path, d.tanggal 
                FROM type t
                JOIN date d ON t.id_date = d.id_date
                $join_conditions";
                
    if (!empty($where_conditions)) {
        $sql_type .= " WHERE " . implode(" AND ", $where_conditions);
    }

    $stmt_type = $conn->prepare($sql_type);
    
    if (!empty($params)) {
        $stmt_type->bind_param($types, ...$params);
    }
    
    $stmt_type->execute();
    $result_type = $stmt_type->get_result();
    
    $data = [];
    if ($result_type->num_rows > 0) {
        while ($row = $result_type->fetch_assoc()) {
            // Ensure image path is valid
            $row['image_path'] = validateImagePath($row['image_path']);
            $data[] = $row;
        }
    }

    // Reset params for other queries
    $params = [];
    $types = "";
    $where_conditions = [];
    
    if (!empty($id_date)) {
        $where_conditions[] = "m.id_date = ?";
        $params[] = $id_date;
        $types .= "i";
    }

    // Query for vibrasi_motor with filters
    $sql_motor = "SELECT m.*, t.image_path 
                FROM vibrasi_motor m
                LEFT JOIN type t ON m.nama = t.id";
                
    if (!empty($selected_plant) || !empty($selected_location)) {
        if (!empty($selected_plant)) {
            $sql_motor .= " JOIN plant p ON m.id = p.id";
            $where_conditions[] = "p.nama = ?";
            $params[] = $selected_plant;
            $types .= "s";
        }
        
        if (!empty($selected_location)) {
            $sql_motor .= " JOIN lokasi l ON m.id = l.id";
            $where_conditions[] = "l.nama = ?";
            $params[] = $selected_location;
            $types .= "s";
        }
    }
    
    if (!empty($where_conditions)) {
        $sql_motor .= " WHERE " . implode(" AND ", $where_conditions);
    }

    $stmt_motor = $conn->prepare($sql_motor);
    
    if (!empty($params)) {
        $stmt_motor->bind_param($types, ...$params);
    }
    
    $stmt_motor->execute();
    $result_motor = $stmt_motor->get_result();

    // Query for vibrasi_pompa with filters
    $params = [];
    $types = "";
    $where_conditions = [];
    
    if (!empty($id_date)) {
        $where_conditions[] = "p.id_date = ?";
        $params[] = $id_date;
        $types .= "i";
    }

    $sql_pump = "SELECT p.* FROM vibrasi_pompa p";
    
    if (!empty($selected_plant) || !empty($selected_location)) {
        if (!empty($selected_plant)) {
            $sql_pump .= " JOIN plant pl ON p.id = pl.id";
            $where_conditions[] = "pl.nama = ?";
            $params[] = $selected_plant;
            $types .= "s";
        }
        
        if (!empty($selected_location)) {
            $sql_pump .= " JOIN lokasi l ON p.id = l.id";
            $where_conditions[] = "l.nama = ?";
            $params[] = $selected_location;
            $types .= "s";
        }
    }
    
    if (!empty($where_conditions)) {
        $sql_pump .= " WHERE " . implode(" AND ", $where_conditions);
    }

    $stmt_pump = $conn->prepare($sql_pump);
    
    if (!empty($params)) {
        $stmt_pump->bind_param($types, ...$params);
    }
    
    $stmt_pump->execute();
    $result_pump = $stmt_pump->get_result();

    // Query for temperatur with filters
    $params = [];
    $types = "";
    $where_conditions = [];
    
    if (!empty($id_date)) {
        $where_conditions[] = "t.id_date = ?";
        $params[] = $id_date;
        $types .= "i";
    }

    $sql_temp = "SELECT t.* FROM temperatur t";
    
    if (!empty($selected_plant) || !empty($selected_location)) {
        if (!empty($selected_plant)) {
            $sql_temp .= " JOIN plant p ON t.id = p.id";
            $where_conditions[] = "p.nama = ?";
            $params[] = $selected_plant;
            $types .= "s";
        }
        
        if (!empty($selected_location)) {
            $sql_temp .= " JOIN lokasi l ON t.id = l.id";
            $where_conditions[] = "l.nama = ?";
            $params[] = $selected_location;
            $types .= "s";
        }
    }
    
    if (!empty($where_conditions)) {
        $sql_temp .= " WHERE " . implode(" AND ", $where_conditions);
    }

    $stmt_temp = $conn->prepare($sql_temp);
    
    if (!empty($params)) {
        $stmt_temp->bind_param($types, ...$params);
    }
    
    $stmt_temp->execute();
    $result_temp = $stmt_temp->get_result();

    // Query for maintenance data with filters
    $params = [];
    $types = "";
    $where_conditions = [];

    // Modified query to include bulan from frequensi table
    $sql_maintenance = "SELECT d.tanggal, n.nomer AS no_wo, p.nama AS plant, l.nama AS location, 
    f.bulan AS bulan, f.nama AS nama, t.nama AS type, t.image_path AS type_image,
    m.nomer AS machine
    FROM date d
    JOIN no_wo n ON d.id_date = n.id_date
    LEFT JOIN plant p ON n.id = p.id
    LEFT JOIN lokasi l ON n.id = l.id
    LEFT JOIN frequensi f ON n.id = f.id
    LEFT JOIN type t ON n.id = t.id
    LEFT JOIN machine m ON n.id = m.id
    WHERE 1=1";

// Add filter conditions with proper parameter binding
if (!empty($selected_date)) {
$sql_maintenance .= " AND d.tanggal = ?";
$params[] = $selected_date;
$types .= "s";
}

if (!empty($selected_plant)) {
$sql_maintenance .= " AND p.nama = ?";
$params[] = $selected_plant;
$types .= "s";
}

if (!empty($selected_location)) {
$sql_maintenance .= " AND l.nama = ?";
$params[] = $selected_location;
$types .= "s";
}

$sql_maintenance .= " ORDER BY d.tanggal DESC";

$stmt_maintenance = $conn->prepare($sql_maintenance);

// Only bind parameters if there are any
if (!empty($params)) {
$stmt_maintenance->bind_param($types, ...$params);
}

$stmt_maintenance->execute();
$result_maintenance = $stmt_maintenance->get_result();

// Fix for the motor vibration query
$params = [];
$types = "";

// Construct query for vibrasi_motor with proper joins
$sql_motor = "SELECT m.*, t.image_path 
FROM vibrasi_motor m
LEFT JOIN type t ON m.nama = t.id";

if (!empty($id_date) || !empty($selected_plant) || !empty($selected_location)) {
$sql_motor .= " WHERE 1=1";

if (!empty($id_date)) {
$sql_motor .= " AND m.id_date = ?";
$params[] = $id_date;
$types .= "i";
}

if (!empty($selected_plant)) {
$sql_motor .= " JOIN plant p ON m.id = p.id AND p.nama = ?";
$params[] = $selected_plant;
$types .= "s";
}

if (!empty($selected_location)) {
$sql_motor .= " JOIN lokasi l ON m.id = l.id AND l.nama = ?";
$params[] = $selected_location;
$types .= "s";
}
}

$stmt_motor = $conn->prepare($sql_motor);

if (!empty($params)) {
$stmt_motor->bind_param($types, ...$params);
}

$stmt_motor->execute();
$result_motor = $stmt_motor->get_result();

    // Query for cek_kondisi with filters
    $params = [];
    $types = "";
    $where_conditions = [];
    
    if (!empty($id_date)) {
        $where_conditions[] = "c.id_date = ?";
        $params[] = $id_date;
        $types .= "i";
    }

    $sql_condition = "SELECT c.* FROM cek_kondisi c";
    
    if (!empty($selected_plant) || !empty($selected_location)) {
        if (!empty($selected_plant)) {
            $sql_condition .= " JOIN plant p ON c.id = p.id";
            $where_conditions[] = "p.nama = ?";
            $params[] = $selected_plant;
            $types .= "s";
        }
        
        if (!empty($selected_location)) {
            $sql_condition .= " JOIN lokasi l ON c.id = l.id";
            $where_conditions[] = "l.nama = ?";
            $params[] = $selected_location;
            $types .= "s";
        }
    }
    
    if (!empty($where_conditions)) {
        $sql_condition .= " WHERE " . implode(" AND ", $where_conditions);
    }

    $stmt_condition = $conn->prepare($sql_condition);
    
    if (!empty($params)) {
        $stmt_condition->bind_param($types, ...$params);
    }
    
    $stmt_condition->execute();
    $result_condition = $stmt_condition->get_result();

    // Stats query with filters
    $params = [];
    $types = "";
    $where_conditions = [];
    
    if (!empty($id_date)) {
        $where_conditions[] = "vibrasi_motor.id_date = ?";
        $params[] = $id_date;
        $types .= "i";
    }

    $stats_query = "SELECT 
                    COUNT(*) as total_units, 
                    AVG(hasil) as avg_result, 
                    SUM(CASE WHEN hasil > standar_max THEN 1 ELSE 0 END) as units_over_standard 
                    FROM vibrasi_motor";
                    
    if (!empty($selected_plant) || !empty($selected_location)) {
        if (!empty($selected_plant)) {
            $stats_query .= " JOIN plant p ON vibrasi_motor.id = p.id";
            $where_conditions[] = "p.nama = ?";
            $params[] = $selected_plant;
            $types .= "s";
        }
        
        if (!empty($selected_location)) {
            $stats_query .= " JOIN lokasi l ON vibrasi_motor.id = l.id";
            $where_conditions[] = "l.nama = ?";
            $params[] = $selected_location;
            $types .= "s";
        }
    }
    
    if (!empty($where_conditions)) {
        $stats_query .= " WHERE " . implode(" AND ", $where_conditions);
    }

    $stmt_stats = $conn->prepare($stats_query);
    
    if (!empty($params)) {
        $stmt_stats->bind_param($types, ...$params);
    }
    
    $stmt_stats->execute();
    $stats_result = $stmt_stats->get_result();
    
    if (!$stats_result) {
        die("Query Error: " . $conn->error);
    }
    
    $stats = $stats_result->fetch_assoc();

    // Handle null values
    $stats['total_units'] = $stats['total_units'] ?? 0;
    $stats['avg_result'] = $stats['avg_result'] ?? 0;
    $stats['units_over_standard'] = $stats['units_over_standard'] ?? 0;

    // Count queries for different data types with filters
    $params = [];
    $types = "";
    $where_conditions = [];
    
    if (!empty($id_date)) {
        $where_conditions[] = "id_date = ?";
        $params[] = $id_date;
        $types .= "i";
    }

    $count_motor_query = "SELECT COUNT(*) as total_motors FROM vibrasi_motor";
    $count_pump_query = "SELECT COUNT(*) as total_pumps FROM vibrasi_pompa";
    $count_temp_query = "SELECT COUNT(*) as total_temps FROM temperatur";
    
    // Apply filters to count queries
    foreach ([&$count_motor_query, &$count_pump_query, &$count_temp_query] as &$query) {
        $table_name = explode(" ", explode("FROM", $query)[1])[1];
        
        if (!empty($selected_plant) || !empty($selected_location)) {
            if (!empty($selected_plant)) {
                $query .= " JOIN plant p ON $table_name.id = p.id";
            }
            
            if (!empty($selected_location)) {
                $query .= " JOIN lokasi l ON $table_name.id = l.id";
            }
            
            $query .= " WHERE 1=1";
            
            if (!empty($id_date)) {
                $query .= " AND $table_name.id_date = ?";
            }
            
            if (!empty($selected_plant)) {
                $query .= " AND p.nama = ?";
            }
            
            if (!empty($selected_location)) {
                $query .= " AND l.nama = ?";
            }
        } else if (!empty($id_date)) {
            $query .= " WHERE $table_name.id_date = ?";
        }
    }

    // Execute count queries
    $stmt_count_motor = $conn->prepare($count_motor_query);
    $stmt_count_pump = $conn->prepare($count_pump_query);
    $stmt_count_temp = $conn->prepare($count_temp_query);
    
    // Define parameter binding function to handle varying parameters
    $bindParams = function($stmt, $params, $types) {
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
    };
    
    // Create parameter arrays for each count query
    $motor_params = [];
    $pump_params = [];
    $temp_params = [];
    $count_types = "";
    
    if (!empty($id_date)) {
        $motor_params[] = $id_date;
        $pump_params[] = $id_date;
        $temp_params[] = $id_date;
        $count_types .= "i";
    }
    
    if (!empty($selected_plant)) {
        $motor_params[] = $selected_plant;
        $pump_params[] = $selected_plant;
        $temp_params[] = $selected_plant;
        $count_types .= "s";
    }
    
    if (!empty($selected_location)) {
        $motor_params[] = $selected_location;
        $pump_params[] = $selected_location;
        $temp_params[] = $selected_location;
        $count_types .= "s";
    }
    
    // Bind parameters if needed
    if (!empty($motor_params)) {
        $stmt_count_motor->bind_param(str_repeat("s", count($motor_params)), ...$motor_params);
    }
    
    if (!empty($pump_params)) {
        $stmt_count_pump->bind_param(str_repeat("s", count($pump_params)), ...$pump_params);
    }
    
    if (!empty($temp_params)) {
        $stmt_count_temp->bind_param(str_repeat("s", count($temp_params)), ...$temp_params);
    }
    
    $stmt_count_motor->execute();
    $result_count_motor = $stmt_count_motor->get_result();
    
    $stmt_count_pump->execute();
    $result_count_pump = $stmt_count_pump->get_result();
    
    $stmt_count_temp->execute();
    $result_count_temp = $stmt_count_temp->get_result();

    // Get counts
    $motor_count = ($result_count_motor) ? $result_count_motor->fetch_assoc()['total_motors'] : 0;
    $pump_count = ($result_count_pump) ? $result_count_pump->fetch_assoc()['total_pumps'] : 0;
    $temp_count = ($result_count_temp) ? $result_count_temp->fetch_assoc()['total_temps'] : 0;

    // Count inspection periods
    $period_query = "SELECT COUNT(DISTINCT tanggal) as total_periods FROM date";
    $result_period = $conn->query($period_query);
    $period_count = ($result_period) ? $result_period->fetch_assoc()['total_periods'] : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Inspeksi Vibrasi Motor | PERUMDA AIR MINUM JAYA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="website icon" type="png" href="../image/logo.png">
    <style>
        .image-container img {
            max-width: 300px;
            max-height: 300px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            object-fit: contain;
        }
        
        .image-container img:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .image-container {
            margin: 20px 0;
            text-align: center;
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    
        .type-image {
            width: 100%;
            max-height: 250px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            object-fit: contain;
        }
        
        .thumbnail-image {
            max-height: 100px; 
            max-width: 100px;
            cursor: pointer;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 2px;
            transition: transform 0.2s;
            object-fit: contain;
        }
        
        .thumbnail-image:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background-color: #003366;">
        <div class="container">
            <a class="navbar-brand" href="../dashboard.php">
                <img src="../image/logo.png" alt="PAM Jaya Logo" style="height: 40px; margin-right: 10px;"> <span>PERUMDA AIR MINUM JAYA</span>
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
                        <a class="nav-link active" href="inspeksi_motor.php">Inspeksi Motor</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#userModal">
                            <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['session_username']); ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 80px;">
        <!-- Page Header -->
        <div class="page-header bg-white rounded p-3 mb-4">
            <h1 style="color: #003366;">Data Inspeksi Vibrasi Motor
                <?php if(!empty($selected_date)): ?>
                <span class="badge bg-primary">
                    <i class="far fa-calendar-alt me-1"></i>
                    <?php echo date('d F Y', strtotime($selected_date)); ?>
                </span>
                <?php endif; ?>
            </h1>
            <p>Laporan detail hasil inspeksi vibrasi motor dan data maintenance.</p>
        </div>

        <!-- Action Buttons -->
        <div class="row mb-4">
            <div class="col-md-6">
                <a href="inspeksi_motor.php" class="btn btn-secondary me-2">
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
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-cogs mb-2" style="font-size: 2rem;"></i>
                        <h3><?php echo number_format($motor_count); ?></h3>
                        <p>Total Unit Motor</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <i class="	fas fa-fire-extinguisher" style="font-size: 2.4rem;"></i>
                        <h3><?php echo number_format($pump_count); ?></h3>
                        <p>Unit Pompa</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card text-white" style="background-color: #ff9800;">
                    <div class="card-body text-center">
                    <i class="bi bi-thermometer-half" style="font-size: 1.7rem;"></i>
                        <h3><?php echo number_format($temp_count); ?></h3>
                        <p>Data Temperatur</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <i class="far fa-calendar-check mb-2" style="font-size: 2rem;"></i>
                        <h3><?php echo !empty($selected_date) ? '1' : $period_count; ?></h3>
                        <p>Periode Inspeksi</p>
                    </div>
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

        <!-- Data Type and Image Section -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #003366; color: white;">
                <span><i class="fas fa-images me-2"></i> Data Type dan Gambar</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Type</th>
                                <th>Date</th>
                                <th>Image</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($data)): ?>
                                <?php foreach ($data as $index => $row): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= htmlspecialchars($row['nama']) ?></td>
                                        <td><?= htmlspecialchars($row['tanggal']) ?></td>
                                        <td class="text-center">
                                            <?php 
                                            $img_path = validateImagePath($row['image_path']);
                                            if (!empty($img_path)): 
                                            ?>
                                                <img src="<?= htmlspecialchars($img_path) ?>" 
                                                    alt="Type Image" 
                                                    class="thumbnail-image"
                                                    onclick="openImageModal('<?= htmlspecialchars($img_path) ?>')">
                                            <?php else: ?>
                                                <span class="badge bg-secondary">No Image</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">No data available</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Vibrasi Motor Data -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #003366; color: white;">
                <span><i class="fas fa-thermometer-half me-2"></i> Data Vibrasi Motor</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Nama Motor</th>
                                <th>Point</th>
                                <th>Hasil</th>
                                <th>Standar Max</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $motors = [];
                            if ($result_motor) {
                                while ($row = $result_motor->fetch_assoc()) {
                                    $motors[] = $row;
                                }
                            }
                            
                            if (count($motors) > 0) {
                                foreach ($motors as $row) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['point']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['hasil']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['standar_max']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['keterangan']) . "</td>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center'>Tidak ada data vibrasi motor tersedia</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Vibrasi Pompa Data -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #003366; color: white;">
                <span><i class="fas fa-water me-2"></i> Data Vibrasi Pompa</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Nama Pompa</th>
                                <th>Point</th>
                                <th>Hasil</th>
                                <th>Standar Max</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $pumps = [];
                            if ($result_pump) {
                                while ($row = $result_pump->fetch_assoc()) {
                                    $pumps[] = $row;
                                }
                            }
                            
                            if (count($pumps) > 0) {
                                foreach ($pumps as $row) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['point']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['hasil']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['standar_max']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['keterangan']) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center'>Tidak ada data vibrasi pompa tersedia</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Temperatur Data -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #003366; color: white;">
                <span><i class="fas fa-temperature-high me-2"></i> Data Temperatur</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Point</th>
                                <th>Hasil</th>
                                <th>Standar Max</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $temps = [];
                            if ($result_temp) {
                                while ($row = $result_temp->fetch_assoc()) {
                                    $temps[] = $row;
                                }
                            }
                            
                            if (count($temps) > 0) {
                                foreach ($temps as $row) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['point']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['hasil']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['standar_max']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['keterangan']) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center'>Tidak ada data temperatur tersedia</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Cek Kondisi Data -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #003366; color: white;">
                <span><i class="fas fa-clipboard-check me-2"></i> Data Cek Kondisi</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Condition</th>
                                <th>Status 1</th>
                                <th>Checkbox 1</th>
                                <th>Status 2</th>
                                <th>Checkbox 2</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result_condition && $result_condition->num_rows > 0) {
                                while ($row = $result_condition->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['condition_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['status_1']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['checkbox_1']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['status_2']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['checkbox_2']) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center'>Tidak ada data cek kondisi tersedia</td></tr>";
                            }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

            <!-- Filter Modal -->
            <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="filterModalLabel">Filter Data</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="filterForm" action="view_data.php" method="GET">
                                <div class="mb-3">
                                    <label for="filterDate" class="form-label">Pilih Tanggal:</label>
                                    <input type="date" class="form-control" id="filterDate" name="date" 
                                        value="<?php echo isset($selected_date) ? htmlspecialchars($selected_date) : ''; ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="filterPlant" class="form-label">Pilih Plant:</label>
                                    <select class="form-select" id="filterPlant" name="plant">
                                        <option value="">Semua Plant</option>
                                        <?php
                                        $plants_query = "SELECT * FROM plant GROUP BY nama";
                                        $plants_result = $conn->query($plants_query);
                                        if ($plants_result) {
                                            while ($plant = $plants_result->fetch_assoc()) {
                                                $selected = (isset($_GET['plant']) && $_GET['plant'] == $plant['id']) ? 'selected' : '';
                                                echo "<option value=\"" . htmlspecialchars($plant['nama']) . "\" $selected>" . 
                                                    htmlspecialchars($plant['nama']) . "</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="filterLocation" class="form-label">Pilih Lokasi:</label>
                                    <select class="form-select" id="filterLocation" name="location">
                                        <option value="">Semua Lokasi</option>
                                        <?php
                                        $locations_query = "SELECT * FROM lokasi GROUP BY nama";
                                        $locations_result = $conn->query($locations_query);
                                        if ($locations_result) {
                                            while ($location = $locations_result->fetch_assoc()) {
                                                $selected = (isset($_GET['location']) && $_GET['location'] == $location['id']) ? 'selected' : '';
                                                echo "<option value=\"" . htmlspecialchars($location['nama']) . "\" $selected>" . 
                                                    htmlspecialchars($location['nama']) . "</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                        <i class="fas fa-times me-1"></i> Tutup
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-check me-1"></i> Terapkan Filter
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Image Preview Modal -->
            <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Preview Gambar</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center">
                            <img id="modalImage" src="" class="img-fluid" alt="Preview Gambar">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Profile Modal -->
            <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="userModalLabel">Profil Pengguna</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-user-circle" style="font-size: 5rem; color: #003366;"></i>
                            </div>
                            <h5><?php echo htmlspecialchars($_SESSION['session_username']); ?></h5>
                            <div class="text-muted">Operator Maintenance</div>
                            <hr>
                            <div class="d-grid gap-2 mt-3">
                                <a href="profile.php" class="btn btn-outline-primary">
                                    <i class="fas fa-id-card me-1"></i> Edit Profil
                                </a>
                                <a href="../logout.php" class="btn btn-danger">
                                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bootstrap JS Bundle with Popper -->
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
            
            <script>
                // Function to open image modal
                function openImageModal(src) {
                    const modalImage = document.getElementById('modalImage');
                    modalImage.src = src;
                    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
                    modal.show();
                }

                // Initialize Bootstrap components
                document.addEventListener('DOMContentLoaded', function() {
                    // Initialize tooltips
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl);
                    });
                });

                // Function to export to Excel
                function exportToExcel() {
                    // Create a URL for the Excel file
                    const uri = 'data:application/vnd.ms-excel;base64,';
                    
                    // Get HTML table elements
                    let tablesHtml = '';
                    document.querySelectorAll('.table-responsive table').forEach(table => {
                        tablesHtml += table.outerHTML;
                    });
                    
                    const template = `
                        <html xmlns:o="urn:schemas-microsoft-com:office:office" 
                            xmlns:x="urn:schemas-microsoft-com:office:excel" 
                            xmlns="http://www.w3.org/TR/REC-html40">
                        <head>
                            <!--[if gte mso 9]>
                            <xml>
                                <x:ExcelWorkbook>
                                    <x:ExcelWorksheets>
                                        <x:ExcelWorksheet>
                                            <x:Name>Data Inspeksi</x:Name>
                                            <x:WorksheetOptions>
                                                <x:DisplayGridlines/>
                                            </x:WorksheetOptions>
                                        </x:ExcelWorksheet>
                                    </x:ExcelWorksheets>
                                </x:ExcelWorkbook>
                            </xml>
                            <![endif]-->
                            <meta http-equiv="content-type" content="text/plain; charset=UTF-8"/>
                        </head>
                        <body>
                            <h1>Data Inspeksi Vibrasi Motor</h1>
                            <p>Tanggal: <?php echo isset($selected_date) ? htmlspecialchars($selected_date) : 'Semua Tanggal'; ?></p>
                            ${tablesHtml}
                        </body>
                        </html>
                    `;
                    
                    // Convert to base64
                    const base64 = function(s) {
                        return window.btoa(unescape(encodeURIComponent(s)));
                    };
                    
                    // Create download link
                    const link = document.createElement("a");
                    link.download = 'Data_Inspeksi_Vibrasi_<?php echo isset($selected_date) ? $selected_date : date('Y-m-d'); ?>.xls';
                    link.href = uri + base64(template);
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            </script>
        </div>
    </body>
    </html>

    <?php
    $conn->close();
    ?>  