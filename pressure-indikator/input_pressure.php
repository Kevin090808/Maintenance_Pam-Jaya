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

// Initialize variables
$error_message = '';
$success_message = '';

// Function to ensure value is string
function ensureString($value) {
    if ($value === null || $value === '') {
        return null;
    }
    return is_numeric($value) ? strval($value) : $value;
}

// Process form if data is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get data from form
    $no_wo = isset($_POST['no_wo']) ? mysqli_real_escape_string($conn, $_POST['no_wo']) : '';
    $tanggal = isset($_POST['tanggal']) ? mysqli_real_escape_string($conn, $_POST['tanggal']) : '';
    $plant = isset($_POST['plant']) ? mysqli_real_escape_string($conn, $_POST['plant']) : '';
    $lokasi = isset($_POST['lokasi']) ? mysqli_real_escape_string($conn, $_POST['lokasi']) : '';
    $bulan = isset($_POST['bulan']) ? mysqli_real_escape_string($conn, $_POST['bulan']) : '';
    $nama = isset($_POST['nama']) ? mysqli_real_escape_string($conn, $_POST['nama']) : '';
    $site = isset($_POST['site']) ? mysqli_real_escape_string($conn, $_POST['site']) : '';

    // Validate only essential input
    if (empty($no_wo) || empty($tanggal)) {
        $error_message = "No. WO dan Tanggal harus diisi!";
    } else {
        // Begin transaction
        $conn->begin_transaction();

        try {
            // Insert data to date table
            $sql_date = "INSERT INTO date (tanggal) VALUES (?)";
            $stmt_date = $conn->prepare($sql_date);
            if (!$stmt_date) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt_date->bind_param("s", $tanggal);
            if (!$stmt_date->execute()) {
                throw new Exception("Execute failed: " . $stmt_date->error);
            }
            $id_date = $conn->insert_id; // Get the newly inserted ID

            // Insert data to no_wo table
            $sql_no_wo = "INSERT INTO no_wo (nomer, id_date) VALUES (?, ?)";
            $stmt_no_wo = $conn->prepare($sql_no_wo);
            if (!$stmt_no_wo) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt_no_wo->bind_param("si", $no_wo, $id_date);
            if (!$stmt_no_wo->execute()) {
                throw new Exception("Execute failed: " . $stmt_no_wo->error);
            }

            // Insert data to plant table (only if not empty)
            if (!empty($plant)) {
                $sql_plant = "INSERT INTO plant (nama, id_date) VALUES (?, ?)";
                $stmt_plant = $conn->prepare($sql_plant);
                if (!$stmt_plant) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                $stmt_plant->bind_param("si", $plant, $id_date);
                if (!$stmt_plant->execute()) {
                    throw new Exception("Execute failed: " . $stmt_plant->error);
                }
                $stmt_plant->close();
            }

            // Insert data to lokasi table (only if not empty)
            if (!empty($lokasi)) {
                $sql_lokasi = "INSERT INTO lokasi (nama, id_date) VALUES (?, ?)";
                $stmt_lokasi = $conn->prepare($sql_lokasi);
                if (!$stmt_lokasi) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                $stmt_lokasi->bind_param("si", $lokasi, $id_date);
                if (!$stmt_lokasi->execute()) {
                    throw new Exception("Execute failed: " . $stmt_lokasi->error);
                }
                $stmt_lokasi->close();
            }

            // Insert data to frequensi table (only if both bulan and nama are not empty)
            if (!empty($bulan) || !empty($nama)) {
                $sql_frequensi = "INSERT INTO frequensi (bulan, nama, id_date) VALUES (?, ?, ?)";
                $stmt_frequensi = $conn->prepare($sql_frequensi);
                if (!$stmt_frequensi) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                $stmt_frequensi->bind_param("ssi", $bulan, $nama, $id_date);
                if (!$stmt_frequensi->execute()) {
                    throw new Exception("Execute failed: " . $stmt_frequensi->error);
                }
                $stmt_frequensi->close();
            }

            // Process multiple rows of pressure indicator data
            $has_data = false;
            for ($i = 1; $i <= 8; $i++) {
                $kode = isset($_POST["kode_$i"]) ? mysqli_real_escape_string($conn, $_POST["kode_$i"]) : '';
                $deskripsi = isset($_POST["deskripsi_$i"]) ? mysqli_real_escape_string($conn, $_POST["deskripsi_$i"]) : '';
                
                // Only insert if at least kode or deskripsi is filled
                if (!empty($kode) || !empty($deskripsi)) {
                    $has_data = true;
                    
                    // Get other fields - converted to string
                    $range = isset($_POST["range_$i"]) ? mysqli_real_escape_string($conn, ensureString($_POST["range_$i"])) : null;
                    
                    $nilai_0 = isset($_POST["nilai_0_$i"]) ? mysqli_real_escape_string($conn, ensureString($_POST["nilai_0_$i"])) : null;
                    $std_0 = isset($_POST["std_0_$i"]) ? mysqli_real_escape_string($conn, ensureString($_POST["std_0_$i"])) : null;
                    $dev_0 = isset($_POST["dev_0_$i"]) ? mysqli_real_escape_string($conn, ensureString($_POST["dev_0_$i"])) : null;

                    $nilai_40 = isset($_POST["nilai_40_$i"]) ? mysqli_real_escape_string($conn, ensureString($_POST["nilai_40_$i"])) : null;
                    $std_40 = isset($_POST["std_40_$i"]) ? mysqli_real_escape_string($conn, ensureString($_POST["std_40_$i"])) : null;
                    $dev_40 = isset($_POST["dev_40_$i"]) ? mysqli_real_escape_string($conn, ensureString($_POST["dev_40_$i"])) : null;
                    
                    $nilai_60 = isset($_POST["nilai_60_$i"]) ? mysqli_real_escape_string($conn, ensureString($_POST["nilai_60_$i"])) : null;
                    $std_60 = isset($_POST["std_60_$i"]) ? mysqli_real_escape_string($conn, ensureString($_POST["std_60_$i"])) : null;
                    $dev_60 = isset($_POST["dev_60_$i"]) ? mysqli_real_escape_string($conn, ensureString($_POST["dev_60_$i"])) : null;
                    
                    $nilai_100 = isset($_POST["nilai_100_$i"]) ? mysqli_real_escape_string($conn, ensureString($_POST["nilai_100_$i"])) : null;
                    $std_100 = isset($_POST["std_100_$i"]) ? mysqli_real_escape_string($conn, ensureString($_POST["std_100_$i"])) : null;
                    $dev_100 = isset($_POST["dev_100_$i"]) ? mysqli_real_escape_string($conn, ensureString($_POST["dev_100_$i"])) : null;
                    
                    $tanggal_row = isset($_POST["tanggal_$i"]) ? mysqli_real_escape_string($conn, $_POST["tanggal_$i"]) : $tanggal;
                    $keterangan = isset($_POST["keterangan_$i"]) ? mysqli_real_escape_string($conn, $_POST["keterangan_$i"]) : null;

                    // Updated SQL query with site column
                    $sql_pressure = "INSERT INTO pressure_indikator 
                        (id_date, kode, deskripsi, range_bar, site,
                        nilai_0, std_0, dev_0, 
                        nilai_40, std_40, dev_40, 
                        nilai_60, std_60, dev_60, 
                        nilai_100, std_100, dev_100, 
                        tanggal, keterangan) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    $stmt_pressure = $conn->prepare($sql_pressure);
                    if (!$stmt_pressure) {
                        throw new Exception("Prepare failed: " . $conn->error);
                    }
                    
                    $stmt_pressure->bind_param("issssssssssssssssss", 
                        $id_date, $kode, $deskripsi, $range, $site,
                        $nilai_0, $std_0, $dev_0,
                        $nilai_40, $std_40, $dev_40,
                        $nilai_60, $std_60, $dev_60,
                        $nilai_100, $std_100, $dev_100,
                        $tanggal_row, $keterangan);
                        
                    if (!$stmt_pressure->execute()) {
                        throw new Exception("Execute failed: " . $stmt_pressure->error);
                    }
                    $stmt_pressure->close();
                }
            }

            if (!$has_data) {
                throw new Exception("Minimal satu baris data pressure indicator harus diisi (kode atau deskripsi)");
            }

            // Commit transaction
            $conn->commit();
            
            // Redirect setelah sukses untuk mencegah resubmission
            header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $error_message = "Error: " . $e->getMessage();
        } finally {
            // Close statements if they exist
            if (isset($stmt_date)) $stmt_date->close();
            if (isset($stmt_no_wo)) $stmt_no_wo->close();
        }
    }
}

// Handle success message dari redirect
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $success_message = "Data berhasil disimpan!";
}

// Get suggested values for dropdowns
$suggested_plants = array();
$suggested_lokasi = array();
$suggested_equipment = array();

$sql_plants = "SELECT DISTINCT nama FROM plant ORDER BY nama LIMIT 10";
$result_plants = $conn->query($sql_plants);
if ($result_plants && $result_plants->num_rows > 0) {
    while ($row = $result_plants->fetch_assoc()) {
        $suggested_plants[] = $row['nama'];
    }
}

$sql_lokasi = "SELECT DISTINCT nama FROM lokasi ORDER BY nama LIMIT 10";
$result_lokasi = $conn->query($sql_lokasi);
if ($result_lokasi && $result_lokasi->num_rows > 0) {
    while ($row = $result_lokasi->fetch_assoc()) {
        $suggested_lokasi[] = $row['nama'];
    }
}

$sql_equipment = "SELECT DISTINCT kode FROM pressure_indikator ORDER BY kode LIMIT 10";
$result_equipment = $conn->query($sql_equipment);
if ($result_equipment && $result_equipment->num_rows > 0) {
    while ($row = $result_equipment->fetch_assoc()) {
        $suggested_equipment[] = $row['kode'];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FORMULIR VERIFIKASI PRESSURE INDIKATOR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
       <style>
        /* CSS tetap sama seperti sebelumnya */
        :root {
            --primary: #003366;
            --secondary: #004d99;
            --accent: #007bff;
            --light: #f8f9fa;
            --medium: #e9ecef;
            --dark: #495057;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        .logo-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 40px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .logo {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s ease;
        }

        .logo:hover {
            transform: scale(1.05);
        }

        .logo i {
            font-size: 2rem;
        }

        .judul {
            font-size: 1.8rem;
            font-weight: 700;
            text-align: center;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            letter-spacing: 1px;
        }

        .main-title {
            margin: 40px 0;
            font-size: 2.2rem;
            color: var(--primary);
            text-align: center;
            position: relative;
            padding-bottom: 20px;
            font-weight: 700;
        }

        .main-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            border-radius: 2px;
        }

        .form-container {
            max-width: 1400px;
            margin: 0 auto 50px;
            padding: 0 30px;
        }

        .form-card {
            background: white;
            border-radius: 15px;
            padding: 35px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-top: 5px solid var(--primary);
            margin-top: 20px;
        }

        .nav-buttons {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .nav-buttons .btn-group {
            display: flex;
            gap: 10px;
        }

        .header-info {
            background: linear-gradient(135deg, rgba(0,51,102,0.05), rgba(0,51,102,0.02));
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 5px solid var(--primary);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .header-info table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .header-info td {
            padding: 8px 15px;
            vertical-align: middle;
        }

        .header-info .label {
            font-weight: 600;
            color: var(--primary);
            width: 120px;
        }

        .form-input {
            border: 2px solid var(--medium);
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background-color: white;
            width: 100%;
            max-width: 250px;
        }

        .form-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(0,123,255,0.15);
            outline: none;
            background-color: #fafbff;
        }

        .form-input.required {
            border-color: var(--danger);
            background-color: #fff5f5;
        }

        .form-input.valid {
            border-color: var(--success);
            background-color: #f0fff4;
        }

        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 30px;
            font-size: 0.85rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .data-table thead {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }

        .data-table th, .data-table td {
            padding: 12px 8px;
            text-align: center;
            border-bottom: 1px solid var(--medium);
            border-right: 1px solid var(--medium);
            position: relative;
        }

        .data-table th:last-child, .data-table td:last-child {
            border-right: none;
        }

        .data-table tbody tr:nth-child(even) {
            background-color: rgba(0,51,102,0.02);
        }

        .data-table tbody tr:hover {
            background-color: rgba(0,123,255,0.05);
        }

        .data-table .form-input {
            max-width: none;
            padding: 8px 10px;
            font-size: 0.85rem;
            border: 1px solid var(--medium);
        }

        .site-row {
            background: linear-gradient(135deg, rgba(0,51,102,0.1), rgba(0,51,102,0.05)) !important;
        }

        .site-row th {
            color: var(--primary);
            font-weight: bold;
        }

        .site-row .form-input {
            background: rgba(255,255,255,0.9);
            font-weight: 500;
        }

        .action-buttons {
            text-align: center;
            margin-top: 35px;
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            border: none;
            padding: 15px 35px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,51,102,0.2);
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,51,102,0.3);
            background: linear-gradient(45deg, var(--secondary), var(--primary));
            color: white;
        }

        .btn-secondary {
            background: white;
            color: var(--dark);
            border: 2px solid var(--medium);
            padding: 15px 35px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-secondary:hover {
            background: var(--light);
            color: var(--dark);
            border-color: var(--dark);
            transform: translateY(-2px);
        }

        .alert {
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 5px solid var(--accent);
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .progress-indicator {
            background: linear-gradient(135deg, var(--light), white);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            border: 1px solid var(--medium);
        }

        .progress {
            height: 10px;
            border-radius: 5px;
            background-color: var(--medium);
            overflow: hidden;
        }

        .progress-bar {
            background: linear-gradient(90deg, var(--primary), var(--accent));
            transition: width 0.6s ease;
            height: 100%;
            border-radius: 5px;
        }

        .form-section-title {
            font-size: 1.3rem;
            color: var(--primary);
            margin: 30px 0 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid var(--medium);
            font-weight: 600;
        }

        footer {
            background: linear-gradient(135deg, #1a1a1a 0%, #2c2c2c 100%);
            color: white;
            padding: 50px 20px;
            margin-top: 60px;
            border-top: 5px solid var(--primary);
        }

        .social-media a {
            color: white;
            margin: 0 10px;
            font-size: 1.5rem;
            transition: color 0.3s ease;
        }

        .social-media a:hover {
            color: var(--accent);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .logo-container {
                flex-direction: column;
                padding: 20px 15px;
            }
            
            .judul {
                font-size: 1.4rem;
                margin: 15px 0;
            }
            
            .form-container {
                padding: 0 15px;
            }
            
            .form-card {
                padding: 20px;
            }
            
            .header-info {
                padding: 20px 15px;
            }
            
            .header-info table,
            .header-info tbody,
            .header-info tr,
            .header-info td {
                display: block;
            }
            
            .header-info tr {
                margin-bottom: 15px;
                border-bottom: 1px solid var(--medium);
                padding-bottom: 15px;
            }
            
            .header-info td {
                padding: 5px 0;
            }
            
            .form-input {
                max-width: none;
                width: 100%;
            }
            
            .data-table {
                font-size: 0.75rem;
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            
            .nav-buttons {
                flex-direction: column;
                align-items: stretch;
            }
            
            .nav-buttons .btn-group {
                justify-content: center;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 15px;
            }
            
            .btn-primary, .btn-secondary {
                width: 100%;
                justify-content: center;
            }

            .main-title {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 480px) {
            .data-table th, .data-table td {
                padding: 8px 4px;
            }
            
            .data-table .form-input {
                padding: 6px 8px;
                font-size: 0.8rem;
            }
        }

        /* Loading and Animation States */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Enhanced Form Validation */
        .field-error {
            color: var(--danger);
            font-size: 0.85rem;
            margin-top: 5px;
            display: none;
        }

        .form-input.required + .field-error {
            display: block;
        }

        .required-marker {
            color: var(--danger);
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="main-content" id="mainContent">
        <div class="logo-container">
            <div class="logo">
                <i class="fas fa-tint"></i>
            </div>
            <div class="judul">PERUMDA AIR MINUM JAYA</div>
            <div class="logo">
                <i class="fas fa-city"></i>
            </div>
        </div>

        <div class="form-container">
            <div class="nav-buttons">
                <button class="btn btn-secondary" onclick="goBack()">
                    <i class="fas fa-arrow-left"></i> Kembali
                </button>
                <div class="btn-group">
                    <button class="btn btn-secondary" onclick="viewData()">
                        <i class="fas fa-eye"></i> Lihat Data
                    </button>
                    <button class="btn btn-primary" onclick="addNewData()">
                        <i class="fas fa-plus-circle"></i> Tambah Data
                    </button>
                </div>
            </div>

            <h1 class="main-title">FORMULIR VERIFIKASI PRESSURE INDIKATOR</h1>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger fade-in">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success fade-in">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <div class="form-card fade-in">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Field dengan tanda bintang (<span class="required-marker">*</span>) wajib diisi. Field lainnya boleh dikosongkan.
                </div>

                <div class="progress-indicator">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span><strong>Progress Pengisian:</strong></span>
                        <span id="progressText" class="badge bg-primary fs-6">0%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" id="progressBar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

                <form method="post" autocomplete="off" spellcheck="false" id="verificationForm" novalidate>
                    <div class="form-section-title">
                        <i class="fas fa-info-circle me-2"></i>Informasi Umum
                    </div>
                    
                    <div class="header-info">
                        <table>
                            <tr>
                                <td class="label">No. WO <span class="required-marker">*</span></td>
                                <td>: <input type="text" name="no_wo" id="no_wo" class="form-input" 
                                    value="<?php echo isset($_POST['no_wo']) ? htmlspecialchars($_POST['no_wo']) : ''; ?>" 
                                    placeholder="Masukkan No. WO" required></td>
                                <td class="label" style="padding-left: 40px;">Plant</td>
                                <td>: <select name="plant" id="plant" class="form-input">
                                    <option value="">Pilih Plant</option>
                                    <?php foreach ($suggested_plants as $plant_option): ?>
                                        <option value="<?php echo htmlspecialchars($plant_option); ?>" 
                                            <?php echo (isset($_POST['plant']) && $_POST['plant'] == $plant_option) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($plant_option); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select></td>
                            </tr>
                            <tr>
                                <td class="label">Tanggal <span class="required-marker">*</span></td>
                                <td>: <input type="date" name="tanggal" id="tanggal" class="form-input" 
                                    value="<?php echo isset($_POST['tanggal']) ? htmlspecialchars($_POST['tanggal']) : date('Y-m-d'); ?>" 
                                    required></td>
                                <td class="label" style="padding-left: 40px;">Lokasi</td>
                                <td>: <input type="text" name="lokasi" id="lokasi" class="form-input" 
                                    value="<?php echo isset($_POST['lokasi']) ? htmlspecialchars($_POST['lokasi']) : ''; ?>" 
                                    placeholder="Masukkan Lokasi" list="lokasiList">
                                    <datalist id="lokasiList">
                                        <?php foreach ($suggested_lokasi as $lokasi_option): ?>
                                            <option value="<?php echo htmlspecialchars($lokasi_option); ?>">
                                        <?php endforeach; ?>
                                    </datalist>
                                </td>
                            </tr>
                            <tr>
                                <td class="label">Bulan</td>
                                <td>: <input type="text" name="bulan" id="bulan" class="form-input" 
                                    value="<?php echo isset($_POST['bulan']) ? htmlspecialchars($_POST['bulan']) : ''; ?>" 
                                    placeholder="Pilih Bulan" list="bulanList">
                                    <datalist id="bulanList">
                                        <option value="1 bulan">
                                        <option value="3 bulan">
                                        <option value="6 bulan">
                                        <option value="12 bulan">
                                    </datalist>
                                </td>
                                <td class="label" style="padding-left: 40px;">Nama</td>
                                <td>: <input type="text" name="nama" id="nama" class="form-input" 
                                    value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>" 
                                    placeholder="Pilih Nama" list="namaList">
                                    <datalist id="namaList">
                                        <option value="2 Tahun">
                                        <option value="3 Tahun">
                                        <option value="5 tahun">
                                    </datalist>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="form-section-title">
                        <i class="fas fa-table me-2"></i>Data Pressure Indikator
                    </div>
                    
                    <div class="table-responsive">
                        <table class="data-table" id="dataTable">
                            <thead>
                                <tr>
                                    <th rowspan="3" style="vertical-align: middle;">No</th>
                                    <th rowspan="3" style="vertical-align: middle;">Kode</th>
                                    <th rowspan="3" style="vertical-align: middle;">Deskripsi</th>
                                    <th rowspan="3" style="vertical-align: middle;">Range Bar</th>
                                    <th colspan="12">Data Verifikasi</th>
                                    <th rowspan="3" style="vertical-align: middle;">Tanggal</th>
                                    <th rowspan="3" style="vertical-align: middle;">Keterangan</th>
                                </tr>
                                <tr>
                                    <th colspan="3" style="background-color: rgba(255,255,255,0.1);">20%</th>
                                    <th colspan="3" style="background-color: rgba(255,255,255,0.1);">40%</th>
                                    <th colspan="3" style="background-color: rgba(255,255,255,0.1);">60%</th>
                                    <th colspan="3" style="background-color: rgba(255,255,255,0.1);">100%</th>
                                </tr>
                                <tr>
                                    <th>Nilai</th><th>Std</th><th>Dev</th>
                                    <th>Nilai</th><th>Std</th><th>Dev</th>
                                    <th>Nilai</th><th>Std</th><th>Dev</th>
                                    <th>Nilai</th><th>Std</th><th>Dev</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <!-- SITE row -->
                                <tr style="background-color: rgba(0,51,102,0.1);">
                                    <td></td>
                                    <th style="text-align: left; font-weight: bold; color: var(--primary);">SITE:</th>
                                    <td colspan="15">
                                        <input type="text" name="site" id="site" class="form-input" 
                                            value="<?php echo isset($_POST['site']) ? htmlspecialchars($_POST['site']) : ''; ?>" 
                                            placeholder="Masukkan Site" style="background: rgba(255,255,255,0.9); font-weight: 500;">
                                    </td>
                                </tr>
                                
                                <!-- Rows 1-8 -->
                                <?php for ($i = 1; $i <= 8; $i++): ?>
                                <tr>
                                    <td><strong><?php echo $i; ?></strong></td>
                                    <td>
                                        <input type="text" name="kode_<?php echo $i; ?>" class="form-input" 
                                            value="<?php echo isset($_POST["kode_$i"]) ? htmlspecialchars($_POST["kode_$i"]) : ''; ?>" 
                                            placeholder="Kode" list="equipmentList">
                                        <datalist id="equipmentList">
                                            <?php foreach ($suggested_equipment as $equipment): ?>
                                                <option value="<?php echo htmlspecialchars($equipment); ?>">
                                            <?php endforeach; ?>
                                        </datalist>
                                    </td>
                                    <td><input type="text" name="deskripsi_<?php echo $i; ?>" class="form-input" 
                                        value="<?php echo isset($_POST["deskripsi_$i"]) ? htmlspecialchars($_POST["deskripsi_$i"]) : ''; ?>" 
                                        placeholder="Deskripsi"></td>
                                    <td><input type="text" name="range_<?php echo $i; ?>" class="form-input" 
                                        value="<?php echo isset($_POST["range_$i"]) ? htmlspecialchars($_POST["range_$i"]) : ''; ?>" 
                                        placeholder="Range Bar"></td>
                                    <td><input type="text" name="nilai_0_<?php echo $i; ?>" class="form-input" 
                                        value="<?php echo isset($_POST["nilai_0_$i"]) ? htmlspecialchars($_POST["nilai_0_$i"]) : ''; ?>" 
                                    ></td>
                                    <td><input type="text" name="std_0_<?php echo $i; ?>" class="form-input" 
                                        value="<?php echo isset($_POST["std_0_$i"]) ? htmlspecialchars($_POST["std_0_$i"]) : ''; ?>" 
                                    ></td>
                                    <td><input type="text" name="dev_0_<?php echo $i; ?>" class="form-input" 
                                        value="<?php echo isset($_POST["dev_0_$i"]) ? htmlspecialchars($_POST["dev_0_$i"]) : ''; ?>" 
                                        ></td>
                                    <td><input type="text" name="nilai_40_<?php echo $i; ?>" class="form-input" 
                                        value="<?php echo isset($_POST["nilai_40_$i"]) ? htmlspecialchars($_POST["nilai_40_$i"]) : ''; ?>" 
                                    ></td>
                                    <td><input type="text" name="std_40_<?php echo $i; ?>" class="form-input" 
                                        value="<?php echo isset($_POST["std_40_$i"]) ? htmlspecialchars($_POST["std_40_$i"]) : ''; ?>" 
                                    ></td>
                                    <td><input type="text" name="dev_40_<?php echo $i; ?>" class="form-input" 
                                        value="<?php echo isset($_POST["dev_40_$i"]) ? htmlspecialchars($_POST["dev_40_$i"]) : ''; ?>" 
                                    ></td>
                                    <td><input type="text" name="nilai_60_<?php echo $i; ?>" class="form-input" 
                                        value="<?php echo isset($_POST["nilai_60_$i"]) ? htmlspecialchars($_POST["nilai_60_$i"]) : ''; ?>" 
                                    ></td>
                                    <td><input type="text" name="std_60_<?php echo $i; ?>" class="form-input" 
                                        value="<?php echo isset($_POST["std_60_$i"]) ? htmlspecialchars($_POST["std_60_$i"]) : ''; ?>" 
                                    ></td>
                                    <td><input type="text" name="dev_60_<?php echo $i; ?>" class="form-input" 
                                        value="<?php echo isset($_POST["dev_60_$i"]) ? htmlspecialchars($_POST["dev_60_$i"]) : ''; ?>" 
                                    ></td>
                                    <td><input type="text" name="nilai_100_<?php echo $i; ?>" class="form-input" 
                                        value="<?php echo isset($_POST["nilai_100_$i"]) ? htmlspecialchars($_POST["nilai_100_$i"]) : ''; ?>" 
                                    ></td>
                                    <td><input type="text" name="std_100_<?php echo $i; ?>" class="form-input" 
                                        value="<?php echo isset($_POST["std_100_$i"]) ? htmlspecialchars($_POST["std_100_$i"]) : ''; ?>" 
                                    ></td>
                                    <td><input type="text" name="dev_100_<?php echo $i; ?>" class="form-input" 
                                        value="<?php echo isset($_POST["dev_100_$i"]) ? htmlspecialchars($_POST["dev_100_$i"]) : ''; ?>" >
                                    </td>
                                    <td><input type="date" name="tanggal_<?php echo $i; ?>" class="form-input" 
                                        value="<?php echo isset($_POST["tanggal_$i"]) ? htmlspecialchars($_POST["tanggal_$i"]) : (isset($_POST['tanggal']) ? htmlspecialchars($_POST['tanggal']) : date('Y-m-d')); ?>"></td>
                                    <td><input type="text" name="keterangan_<?php echo $i; ?>" class="form-input" 
                                        value="<?php echo isset($_POST["keterangan_$i"]) ? htmlspecialchars($_POST["keterangan_$i"]) : ''; ?>" 
                                        placeholder="Keterangan"></td>
                                </tr>
                                <?php endfor; ?>
                            </tbody>
                        </table>

                    <div class="action-buttons">
                        <button type="reset" class="btn btn-secondary">
                            <i class="fas fa-eraser"></i> Reset Form
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Data
                        </button>
                    </div>
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
        // Set tanggal hari ini jika belum diisi
        if (!document.getElementById('tanggal').value) {
            document.getElementById('tanggal').value = new Date().toISOString().split('T')[0];
        }

        // Update progress bar
        function updateProgress() {
            const form = document.getElementById('verificationForm');
            const formData = new FormData(form);
            let filledFields = 0;
            let totalFields = 0;

            // Count required fields only
            const requiredInputs = form.querySelectorAll('[required]');
            requiredInputs.forEach(input => {
                totalFields++;
                if (input.value.trim() !== '') {
                    filledFields++;
                }
            });

            // Count at least one row of equipment data
            let hasEquipmentData = false;
            for (let i = 1; i <= 8; i++) {
                const kode = form.querySelector(`[name="kode_${i}"]`);
                const deskripsi = form.querySelector(`[name="deskripsi_${i}"]`);
                
                if (kode && deskripsi) {
                    if (kode.value.trim() !== '' || deskripsi.value.trim() !== '') {
                        hasEquipmentData = true;
                        break;
                    }
                }
            }

            // Add to count if at least one row is filled
            if (hasEquipmentData) {
                filledFields++;
            }
            totalFields++;

            const percentage = totalFields > 0 ? Math.round((filledFields / totalFields) * 100) : 0;
            document.getElementById('progressBar').style.width = percentage + '%';
            document.getElementById('progressText').textContent = percentage + '%';
            
            // Update input validation state
            requiredInputs.forEach(input => {
                if (input.value.trim() === '' && input.hasAttribute('required')) {
                    input.classList.add('required');
                } else {
                    input.classList.remove('required');
                }
            });
        }

        // Add input event listeners for progress tracking
        const inputs = document.querySelectorAll('.form-input');
        inputs.forEach(input => {
            input.addEventListener('input', updateProgress);
        });

        // Form validation before submit
        document.getElementById('verificationForm').addEventListener('submit', function(e) {
            let isValid = true;
            
            // Check required fields
            const requiredInputs = this.querySelectorAll('[required]');
            requiredInputs.forEach(input => {
                if (input.value.trim() === '') {
                    input.classList.add('required');
                    isValid = false;
                } else {
                    input.classList.remove('required');
                }
            });

            // Check at least one equipment row is filled
            let hasEquipmentData = false;
            for (let i = 1; i <= 8; i++) {
                const kode = this.querySelector(`[name="kode_${i}"]`);
                const deskripsi = this.querySelector(`[name="deskripsi_${i}"]`);
                
                if (kode && deskripsi) {
                    if (kode.value.trim() !== '' || deskripsi.value.trim() !== '') {
                        hasEquipmentData = true;
                        break;
                    }
                }
            }

            if (!hasEquipmentData) {
                alert('Minimal satu baris data pressure indicator harus diisi (kode atau deskripsi)');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
                updateProgress();
                alert('Silakan lengkapi semua field yang wajib diisi!');
            }
        });

        // Initialize progress bar
        updateProgress();

        // Navigation functions
        function goBack() {
            window.history.back();
        }

        function viewData() {
            window.location.href = 'view_data.php';
        }

        function addNewData() {
            window.location.href = window.location.href.split('?')[0];
        }
    </script>
</body>
</html>