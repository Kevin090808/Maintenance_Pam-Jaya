<?php
session_start();

// Database configuration
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

// Check if user is logged in
if (!isset($_SESSION['session_username'])) {
    header("Location: login.php"); 
    exit();
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Process form if data is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed");
    }

    // Get and validate form data
    $no_wo = isset($_POST['no_wo']) ? $conn->real_escape_string($_POST['no_wo']) : '';
    $tanggal = isset($_POST['date']) ? $conn->real_escape_string($_POST['date']) : '';
    $plant = isset($_POST['plant']) ? $conn->real_escape_string($_POST['plant']) : '';
    $lokasi = isset($_POST['location']) ? $conn->real_escape_string($_POST['location']) : '';
    $bulan = isset($_POST['bulan']) ? $conn->real_escape_string($_POST['bulan']) : '';
    $nama = isset($_POST['nama']) ? $conn->real_escape_string($_POST['nama']) : '';
    
    // Validate required fields
    if (empty($no_wo) || empty($tanggal) || empty($plant) || empty($lokasi) || 
        empty($bulan) || empty($nama)) {
        echo "<script>alert('Semua field harus diisi!');</script>";
    } else {
        // Validate date format
        if (!DateTime::createFromFormat('Y-m-d', $tanggal)) {
            echo "<script>alert('Format tanggal tidak valid!');</script>";
            exit();
        }

        // First check if the date already exists
        $id_date = null;
        $sql_check_date = "SELECT id_date FROM date WHERE tanggal = ? LIMIT 1";
        $stmt_check_date = $conn->prepare($sql_check_date);
        $stmt_check_date->bind_param("s", $tanggal);
        $stmt_check_date->execute();
        $result = $stmt_check_date->get_result();

        if ($result->num_rows > 0) {
            // Date exists, get the existing ID
            $row = $result->fetch_assoc();
            $id_date = $row['id_date'];
            $stmt_check_date->close();
            
            // Proceed with other inserts using existing date ID
            processOtherInserts($conn, $no_wo, $plant, $lokasi, $bulan, $nama, $id_date);
        } else {
            // Date doesn't exist, start transaction to insert new date and related data
            $conn->begin_transaction();

            try {
                // Insert into date table
                $sql_date = "INSERT INTO date (tanggal) VALUES (?)";
                $stmt_date = $conn->prepare($sql_date);
                $stmt_date->bind_param("s", $tanggal);
                $stmt_date->execute();
                $id_date = $conn->insert_id;
                $stmt_date->close();

                // Process all other inserts
                processOtherInserts($conn, $no_wo, $plant, $lokasi, $bulan, $nama, $id_date);

                // Commit transaction
                $conn->commit();
                
                // Success notification
                echo "<script>
                        alert('Data berhasil diinput!');
                        window.location.href = 'test_insulation.php';
                      </script>";
                exit();
            } catch (Exception $e) {
                // Rollback on error
                $conn->rollback();
                error_log($e->getMessage());
                echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
            }
        }
    }
}

/**
 * Process all other database inserts after date is handled
 */
function processOtherInserts($conn, $no_wo, $plant, $lokasi, $bulan, $nama, $id_date) {
    // Insert into no_wo table
    $sql_no_wo = "INSERT INTO no_wo (nomer, id_date) VALUES (?, ?)";
    $stmt_no_wo = $conn->prepare($sql_no_wo);
    $stmt_no_wo->bind_param("si", $no_wo, $id_date);
    $stmt_no_wo->execute();
    $stmt_no_wo->close();

    // Insert into plant table
    $sql_plant = "INSERT INTO plant (nama, id_date) VALUES (?, ?)";
    $stmt_plant = $conn->prepare($sql_plant);
    $stmt_plant->bind_param("si", $plant, $id_date);
    $stmt_plant->execute();
    $stmt_plant->close();

    // Insert into lokasi table
    $sql_lokasi = "INSERT INTO lokasi (nama, id_date) VALUES (?, ?)";
    $stmt_lokasi = $conn->prepare($sql_lokasi);
    $stmt_lokasi->bind_param("si", $lokasi, $id_date);
    $stmt_lokasi->execute();
    $stmt_lokasi->close();

    // Insert into frequensi table
    $sql_frequensi = "INSERT INTO frequensi (bulan, nama, id_date) VALUES (?, ?, ?)";
    $stmt_frequensi = $conn->prepare($sql_frequensi);
    $stmt_frequensi->bind_param("ssi", $bulan, $nama, $id_date);
    $stmt_frequensi->execute();
    $stmt_frequensi->close();

    // Process insulation test data
    if (isset($_POST['equipment'])) {
        $equipment_data = $_POST['equipment'];
        $meansurement_data = $_POST['meansurement'] ?? [];
        $inject_volt_data = $_POST['inject_volt'] ?? [];
        $result_insulation_data = $_POST['reasult_insulation'] ?? [];
        $dar_data = $_POST['dar'] ?? [];
        $pi_data = $_POST['pi'] ?? [];
        $condition_data = $_POST['condition'] ?? [];
        
        $measurement_index = 0;
        
        foreach ($equipment_data as $equipment_index => $equipment) {
            if (!empty($equipment)) {
                // Each equipment has 6 measurements
                for ($i = 0; $i < 6; $i++) {
                    if (isset($meansurement_data[$measurement_index]) && 
                        isset($inject_volt_data[$measurement_index]) && 
                        isset($result_insulation_data[$measurement_index]) &&
                        isset($dar_data[$measurement_index]) && 
                        isset($pi_data[$measurement_index]) && 
                        isset($condition_data[$measurement_index])) {
                    
                        $meansurement = $conn->real_escape_string($meansurement_data[$measurement_index]);
                        $inject_volt = $conn->real_escape_string($inject_volt_data[$measurement_index]);
                        $reasult_insulation = $conn->real_escape_string($result_insulation_data[$measurement_index]);
                        $dar = $conn->real_escape_string($dar_data[$measurement_index]);
                        $pi = $conn->real_escape_string($pi_data[$measurement_index]);
                        $condition = $conn->real_escape_string($condition_data[$measurement_index]);
                        
                        // Only insert if required fields are not empty
                        if (!empty($inject_volt) && !empty($reasult_insulation) && 
                            !empty($dar) && !empty($pi) && !empty($condition)) {
                            
                            $query_insulation = "INSERT INTO insulation (equipment, meansurement, inject_volt, 
                                               reasult_insulation, dar, pi, `condition`, id_date) 
                                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                            $stmt_insulation = $conn->prepare($query_insulation);
                            $stmt_insulation->bind_param("sssssssi", $equipment, $meansurement, $inject_volt, 
                                                      $reasult_insulation, $dar, $pi, $condition, $id_date);
                            
                            if (!$stmt_insulation->execute()) {
                                throw new Exception("Error inserting insulation data: " . $stmt_insulation->error);
                            }
                            $stmt_insulation->close();
                        }
                    }
                    $measurement_index++;
                }
            }
        }
    }
    
    // Process resistance test data
    if (isset($_POST['resistance_equipment'])) {
        $resistance_equipment_data = $_POST['resistance_equipment'];
        $resistance_meansurement_data = $_POST['resistance_meansurement'] ?? [];
        $resistance_result_data = $_POST['resistance_result'] ?? [];
        $resistance_condition_data = $_POST['resistance_condition'] ?? [];
        
        $resistance_measurement_index = 0;
        
        foreach ($resistance_equipment_data as $equipment_index => $equipment) {
            if (!empty($equipment)) {
                // Each equipment has 3 measurements
                for ($i = 0; $i < 3; $i++) {
                    if (isset($resistance_meansurement_data[$resistance_measurement_index]) && 
                        isset($resistance_result_data[$resistance_measurement_index]) && 
                        isset($resistance_condition_data[$resistance_measurement_index])) {
                    
                        $meansurement = $conn->real_escape_string($resistance_meansurement_data[$resistance_measurement_index]);
                        $result = $conn->real_escape_string($resistance_result_data[$resistance_measurement_index]);
                        $condition = $conn->real_escape_string($resistance_condition_data[$resistance_measurement_index]);
                        
                        // Only insert if required fields are not empty
                        if (!empty($result) && !empty($condition)) {
                            $query_resistance = "INSERT INTO resistensi (equipment, meansurement, result, 
                                              `condition`, id_date) VALUES (?, ?, ?, ?, ?)";
                            $stmt_resistance = $conn->prepare($query_resistance);
                            $stmt_resistance->bind_param("ssssi", $equipment, $meansurement, $result, $condition, $id_date);
                            
                            if (!$stmt_resistance->execute()) {
                                throw new Exception("Error inserting resistance data: " . $stmt_resistance->error);
                            }
                            $stmt_resistance->close();
                        }
                    }
                    $resistance_measurement_index++;
                }
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Inspeksi Insulation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Global Styles */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            color: #333;
            line-height: 1.6;
        }

        /* Logo Container */
        .logo-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 40px;
            background-color: #003366;
            color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .logo {
            width: 90px;
            height: 90px;
            transition: transform 0.3s ease;
        }

        .logo:hover {
            transform: scale(1.1);
        }

        .judul {
            font-size: 26px;
            font-weight: bold;
            text-align: center;
            flex-grow: 1;
            margin: 0;
        }

        /* Page Title */
        h1 {
            font-size: 32px;
            margin: 40px 0;
            color: #003366;
            text-align: center;
            text-transform: uppercase;
            font-weight: 700;
            position: relative;
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

        /* Form Layout */
        .form-table {
            width: 50%;
            margin: 30px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            animation: slideIn 0.5s ease-in-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-table tr {
            margin-bottom: 15px;
        }

        .form-table td {
            padding: 12px;
            vertical-align: middle;
            font-size: 16px;
        }

        input[type="text"], input[type="number"], select, input[type="date"] {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
            font-size: 14px;
            box-sizing: border-box;
            transition: border 0.3s ease;
        }

        input[type="text"]:focus, input[type="number"]:focus, select:focus, input[type="date"]:focus {
            border-color: #004c99;
            outline: none;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.5s ease-in-out;
        }

        th, td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background-color: #004c99;
            color: white;
            font-size: 16px;
            text-transform: uppercase;
        }

        td {
            background-color: #fafafa;
            font-size: 15px;
        }

        td input[type="text"], td input[type="number"], td select {
            font-size: 14px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #f1f1f1;
            width: 100%;
            transition: border 0.3s ease;
        }

        td input[type="text"]:focus, td input[type="number"]:focus, td select:focus {
            border-color: #004c99;
            outline: none;
        }

        /* Button Styling */
        .button-container {
            text-align: center;
            margin-top: 30px;
            margin-bottom: 50px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 14px 20px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 8px;
            transition: background-color 0.3s ease, transform 0.3s ease;
            margin: 0 10px;
        }

        button:hover {
            background-color: #45a049;
            transform: scale(1.05);
        }

        button:active {
            transform: translateY(0);
        }

        /* Form Section Titles */
        h2 {
            color: #004c99;
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 15px;
            border-bottom: 2px solid #004c99;
            padding-bottom: 10px;
            animation: fadeInLeft 0.5s ease-in-out;
        }

        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Section Containers */
        .table-container {
            margin: 20px 30px;
        }

        /* Footer Styling */
        footer {
            background-color: #333;
            color: #ddd;
            font-family: Cambria, Cochin, Georgia, Times, 'Times New Roman', serif;
            padding: 40px 20px;
            position: relative;
            text-align: center;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .form-table {
                width: 90%;
            }

            table {
                font-size: 14px;
            }

            th, td {
                padding: 10px;
            }

            h1 {
                font-size: 24px;
            }

            h2 {
                font-size: 20px;
            }

            button {
                width: 100%;
                font-size: 14px;
                margin: 10px 0;
            }

            .logo-container {
                flex-direction: column;
                text-align: center;
            }

            .logo {
                width: 60px;
                height: 60px;
            }

            .judul {
                font-size: 24px;
                margin-top: 10px;
            }
        }

        @media (max-width: 480px) {
            h1 {
                font-size: 24px;
            }

            h2 {
                font-size: 18px;
            }

            .form-table {
                width: 95%;
                padding: 10px;
            }

            td input[type="text"], td input[type="number"], td select {
                font-size: 12px;
                padding: 6px;
            }

            button {
                font-size: 14px;
                padding: 10px 20px;
            }
        }  

        .frequency-container {
            display: flex;
            gap: 15px;
            align-items: center;
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 5px;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-sizing: border-box;
            background-color: white;
        }

        .frequency-container input[list] {
            flex: 1;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 5px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: white;
        }

        .frequency-container input[list]:focus {
            border-color: #004c99;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.25);
            outline: none;
        }

        @media (max-width: 768px) {
            .frequency-container {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
                padding: 0;
                border: none;
            }

            .frequency-container input[list] {
                width: 100%;
            }
        }

        .field-error {
            border-color: #ff0000 !important;
            background-color: #ffeeee !important;
        }
    </style>
</head>
<body>
    <!-- Logo Container -->
    <div class="logo-container">
        <img src="../image/logo.png" alt="Logo PAM Jaya" class="logo">
        <div class="judul">PERUMDA AIR MINUM JAYA</div>
        <img src="../image/Jakarta.png" alt="Logo Jakarta" class="logo">
    </div>
    
    <h1>FORM INSULATION TEST</h1>
    
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" onsubmit="return validateForm()">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <!-- Main Form Table -->
        <table class="form-table">
            <tr>
                <td>No.Wo:</td>
                <td><input type="text" name="no_wo" class="form-control" required></td>
            </tr>
            <tr>
                <td>Date:</td>
                <td><input type="date" name="date" class="form-control" required></td>
            </tr>
            <tr>
                <td>Plant:</td>
                <td><input type="text" name="plant" class="form-control" required></td>
            </tr>
            <tr>
                <td>Location:</td>
                <td>
                    <select name="location" class="form-control" required>
                        <option value="">Pilih Lokasi</option>
                        <option value="location1">Location 1</option>
                        <option value="location2">Location 2</option>
                        <option value="location3">Location 3</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Frequensi:</td>
                <td>
                    <div class="frequency-container">
                        <input list="bulan" name="bulan" placeholder="Pilih Bulan" required>
                        <datalist id="bulan">
                            <option value="1 bulan"></option>
                            <option value="3 bulan"></option>
                            <option value="6 bulan"></option>
                            <option value="12 bulan"></option>
                        </datalist>
                        
                        <input list="nama" name="nama" placeholder="Pilih Tahun" required>
                        <datalist id="nama">
                            <option value="2 Tahun"></option>
                            <option value="3 Tahun"></option>
                            <option value="5 tahun"></option>
                        </datalist>
                    </div>
                </td>
            </tr>
        </table>
        
        <!-- Insulation Test Table -->
        <div class="table-container">
            <h2>INSULATION TEST</h2>
            <table>
                <thead>
                    <tr>
                        <th rowspan="2">Equipment</th>
                        <th rowspan="2">Measurement</th>
                        <th rowspan="2">Inject Volt</th>
                        <th rowspan="2">Result Insulation</th>
                        <th colspan="2">Index</th>
                        <th rowspan="2">Condition</th>
                    </tr>
                    <tr>
                        <th>DAR</th>
                        <th>PI</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Equipment 1 - Motor -->
                    <tr>
                        <td rowspan="7" style="vertical-align: middle;">
                            <input type="text" name="equipment[]" class="form-control" >
                        </td>
                    </tr>
                    <tr>
                        <td>R-S <input type="hidden" name="meansurement[]" value="R-S"></td>
                        <td><input type="text" name="inject_volt[]" required></td>
                        <td><input type="text" name="reasult_insulation[]" required></td>
                        <td><input type="text" name="dar[]" required></td>
                        <td><input type="text" name="pi[]" required></td>
                        <td><input type="text" name="condition[]" required></td>
                    </tr>
                    <tr>
                        <td>R-T <input type="hidden" name="meansurement[]" value="R-T"></td>
                        <td><input type="text" name="inject_volt[]" required></td>
                        <td><input type="text" name="reasult_insulation[]" required></td>
                        <td><input type="text" name="dar[]" required></td>
                        <td><input type="text" name="pi[]" required></td>
                        <td><input type="text" name="condition[]" required></td>
                    </tr>
                    <tr>
                        <td>S-T <input type="hidden" name="meansurement[]" value="S-T"></td>
                        <td><input type="text" name="inject_volt[]" required></td>
                        <td><input type="text" name="reasult_insulation[]" required></td>
                        <td><input type="text" name="dar[]" required></td>
                        <td><input type="text" name="pi[]" required></td>
                        <td><input type="text" name="condition[]" required></td>
                    </tr>
                    <tr>
                        <td>R-G <input type="hidden" name="meansurement[]" value="R-G"></td>
                        <td><input type="text" name="inject_volt[]" required></td>
                        <td><input type="text" name="reasult_insulation[]" required></td>
                        <td><input type="text" name="dar[]" required></td>
                        <td><input type="text" name="pi[]" required></td>
                        <td><input type="text" name="condition[]" required></td>
                    </tr>
                    <tr>
                        <td>S-G <input type="hidden" name="meansurement[]" value="S-G"></td>
                        <td><input type="text" name="inject_volt[]" required></td>
                        <td><input type="text" name="reasult_insulation[]" required></td>
                        <td><input type="text" name="dar[]" required></td>
                        <td><input type="text" name="pi[]" required></td>
                        <td><input type="text" name="condition[]" required></td>
                    </tr>
                    <tr>
                        <td>T-G <input type="hidden" name="meansurement[]" value="T-G"></td>
                        <td><input type="text" name="inject_volt[]" required></td>
                        <td><input type="text" name="reasult_insulation[]" required></td>
                        <td><input type="text" name="dar[]" required></td>
                        <td><input type="text" name="pi[]" required></td>
                        <td><input type="text" name="condition[]" required></td>
                    </tr>
                    
                    <!-- Equipment 2 - Compressor -->
                    <tr>
                        <td rowspan="7" style="vertical-align: middle;">
                            <input type="text" name="equipment[]"  class="form-control" >
                        </td>
                    </tr>
                    <tr>
                        <td>R-S <input type="hidden" name="meansurement[]" value="R-S"></td>
                        <td><input type="text" name="inject_volt[]" required></td>
                        <td><input type="text" name="reasult_insulation[]" required></td>
                        <td><input type="text" name="dar[]" required></td>
                        <td><input type="text" name="pi[]" required></td>
                        <td><input type="text" name="condition[]" required></td>
                    </tr>
                    <tr>
                        <td>R-T <input type="hidden" name="meansurement[]" value="R-T"></td>
                        <td><input type="text" name="inject_volt[]" required></td>
                        <td><input type="text" name="reasult_insulation[]" required></td>
                        <td><input type="text" name="dar[]" required></td>
                        <td><input type="text" name="pi[]" required></td>
                        <td><input type="text" name="condition[]" required></td>
                    </tr>
                    <tr>
                        <td>S-T <input type="hidden" name="meansurement[]" value="S-T"></td>
                        <td><input type="text" name="inject_volt[]" required></td>
                        <td><input type="text" name="reasult_insulation[]" required></td>
                        <td><input type="text" name="dar[]" required></td>
                        <td><input type="text" name="pi[]" required></td>
                        <td><input type="text" name="condition[]" required></td>
                    </tr>
                    <tr>
                        <td>R-G <input type="hidden" name="meansurement[]" value="R-G"></td>
                        <td><input type="text" name="inject_volt[]" required></td>
                        <td><input type="text" name="reasult_insulation[]" required></td>
                        <td><input type="text" name="dar[]" required></td>
                        <td><input type="text" name="pi[]" required></td>
                        <td><input type="text" name="condition[]" required></td>
                    </tr>
                    <tr>
                        <td>S-G <input type="hidden" name="meansurement[]" value="S-G"></td>
                        <td><input type="text" name="inject_volt[]" required></td>
                        <td><input type="text" name="reasult_insulation[]" required></td>
                        <td><input type="text" name="dar[]" required></td>
                        <td><input type="text" name="pi[]" required></td>
                        <td><input type="text" name="condition[]" required></td>
                    </tr>
                    <tr>
                        <td>T-G <input type="hidden" name="meansurement[]" value="T-G"></td>
                        <td><input type="text" name="inject_volt[]" required></td>
                        <td><input type="text" name="reasult_insulation[]" required></td>
                        <td><input type="text" name="dar[]" required></td>
                        <td><input type="text" name="pi[]" required></td>
                        <td><input type="text" name="condition[]" required></td>
                    </tr>
                    
                    <!-- Equipment 3 - Fan Motor -->
                    <tr>
                        <td rowspan="7" style="vertical-align: middle;">
                            <input type="text" name="equipment[]"  class="form-control" >
                        </td>
                    </tr>
                    <tr>
                        <td>U-V <input type="hidden" name="meansurement[]" value="U-V"></td>
                        <td><input type="text" name="inject_volt[]" required></td>
                        <td><input type="text" name="reasult_insulation[]" required></td>
                        <td><input type="text" name="dar[]" required></td>
                        <td><input type="text" name="pi[]" required></td>
                        <td><input type="text" name="condition[]" required></td>
                    </tr>
                    <tr>
                        <td>U-W <input type="hidden" name="meansurement[]" value="U-W"></td>
                        <td><input type="text" name="inject_volt[]" required></td>
                        <td><input type="text" name="reasult_insulation[]" required></td>
                        <td><input type="text" name="dar[]" required></td>
                        <td><input type="text" name="pi[]" required></td>
                        <td><input type="text" name="condition[]" required></td>
                    </tr>
                    <tr>
                        <td>V-W <input type="hidden" name="meansurement[]" value="V-W"></td>
                        <td><input type="text" name="inject_volt[]" required></td>
                        <td><input type="text" name="reasult_insulation[]" required></td>
                        <td><input type="text" name="dar[]" required></td>
                        <td><input type="text" name="pi[]" required></td>
                        <td><input type="text" name="condition[]" required></td>
                    </tr>
                    <tr>
                        <td>U-G <input type="hidden" name="meansurement[]" value="U-G"></td>
                        <td><input type="text" name="inject_volt[]" required></td>
                        <td><input type="text" name="reasult_insulation[]" required></td>
                        <td><input type="text" name="dar[]" required></td>
                        <td><input type="text" name="pi[]" required></td>
                        <td><input type="text" name="condition[]" required></td>
                    </tr>
                    <tr>
                        <td>V-G <input type="hidden" name="meansurement[]" value="V-G"></td>
                        <td><input type="text" name="inject_volt[]" required></td>
                        <td><input type="text" name="reasult_insulation[]" required></td>
                        <td><input type="text" name="dar[]" required></td>
                        <td><input type="text" name="pi[]" required></td>
                        <td><input type="text" name="condition[]" required></td>
                    </tr>
                    <tr>
                        <td>W-G <input type="hidden" name="meansurement[]" value="W-G"></td>
                        <td><input type="text" name="inject_volt[]" required></td>
                        <td><input type="text" name="reasult_insulation[]" required></td>
                        <td><input type="text" name="dar[]" required></td>
                        <td><input type="text" name="pi[]" required></td>
                        <td><input type="text" name="condition[]" required></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Resistance Test Table -->
        <div class="table-container">
            <h2>RESISTANCE TEST</h2>
            <table>
                <thead>
                    <tr>
                        <th>Equipment</th>
                        <th>Measurement</th>
                        <th>Result</th>
                        <th>Condition</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td rowspan="4" style="vertical-align: middle;">
                            <input type="text" name="resistance_equipment[]" value="" class="form-control">
                        </td>
                    </tr>
                    <tr>
                        <td>U-V <input type="hidden" name="resistance_meansurement[]" value="U-V"></td>
                        <td><input type="text" name="resistance_result[]" required></td>
                        <td><input type="text" name="resistance_condition[]" required></td>
                    </tr>
                    <tr>
                        <td>U-W <input type="hidden" name="resistance_meansurement[]" value="U-W"></td>
                        <td><input type="text" name="resistance_result[]" required></td>
                        <td><input type="text" name="resistance_condition[]" required></td>
                    </tr>
                    <tr>
                        <td>V-W <input type="hidden" name="resistance_meansurement[]" value="V-W"></td>
                        <td><input type="text" name="resistance_result[]" required></td>
                        <td><input type="text" name="resistance_condition[]" required></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="button-container">
            <button type="submit">Submit</button>
            <button type="button" onclick="window.location.href='test_insulation.php'">Kembali</button>
        </div>
    </form>
    
    <footer>
        <div class="footer-bottom">
            <p>&copy; 2025 Perumda PAM Jaya. All Rights Reserved.</p>
        </div>
    </footer>
    
    <script>
        // Form validation
        function validateForm() {
            let isValid = true;
            const requiredFields = document.querySelectorAll('input[required], select[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('field-error');
                    isValid = false;
                } else {
                    field.classList.remove('field-error');
                }
            });
            
            if (!isValid) {
                alert('Mohon lengkapi semua field yang wajib diisi!');
                return false;
            }
            return true;
        }

        // Highlight empty required fields on blur
        document.querySelectorAll('input[required], select[required]').forEach(field => {
            field.addEventListener('blur', function() {
                if (!this.value.trim()) {
                    this.classList.add('field-error');
                } else {
                    this.classList.remove('field-error');
                }
            });
        });
    </script>
</body>
</html>