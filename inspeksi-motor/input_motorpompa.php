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
$image_path = ''; // Initialize image path
// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $no_wo = isset($_POST['no_wo']) ? trim($_POST['no_wo']) : '';
    $date = isset($_POST['date']) ? trim($_POST['date']) : '';
    $plant = isset($_POST['plant']) ? trim($_POST['plant']) : '';
    $location = isset($_POST['location']) ? trim($_POST['location']) : '';
    
    // FIX: Menangkap data frequensi dengan benar
    $bulan = isset($_POST['bulan']) ? mysqli_real_escape_string($conn, $_POST['bulan']) : '';
    $nama = isset($_POST['nama']) ? mysqli_real_escape_string($conn, $_POST['nama']) : '';
    
    $machine = isset($_POST['machine']) ? trim($_POST['machine']) : '';
    $type = isset($_POST['valve_type']) ? trim($_POST['valve_type']) : '';
    
    // Option to convert code to display name if desired
    $valve_type_display = [
        'hori' => 'Horizontal',
        'vertikal' => 'Vertikal',
        'ganda' => 'Horizontal Bearing Ganda'
    ];
    
    // Handle image upload or standard image selection
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/uploads/'; // Absolute path to upload directory
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                $error = 'Gagal membuat direktori upload.';
            }
        }
        // Validate file type and size
        $allowed_types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        $file_info = finfo_open(FILEINFO_MIME_TYPE);
        $file_type = finfo_file($file_info, $_FILES['image']['tmp_name']);
        finfo_close($file_info);
        if (!array_key_exists($file_type, $allowed_types)) {
            $error = 'Hanya file JPG, PNG, atau GIF yang diperbolehkan.';
        } elseif ($_FILES['image']['size'] > $max_size) {
            $error = 'Ukuran file terlalu besar. Maksimal 2MB.';
        } else {
            // Generate unique filename with proper extension
            $file_extension = $allowed_types[$file_type];
            $unique_name = 'valve_' . uniqid() . '.' . $file_extension;
            $file_path = $upload_dir . $unique_name;
            // Move uploaded file
            if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
                $image_path = 'uploads/' . $unique_name; // Store relative path for database
            } else {
                $error = 'Gagal menyimpan file upload.';
            }
        }
    } elseif (isset($_POST['valve_type']) && !empty($_POST['valve_type'])) {
        // Use standard image based on valve type
        $valve_type = trim($_POST['valve_type']);
        $standard_images = [
            'hori' => 'image/hori.jpg',
            'vertikal' => 'image/vertikal.jpg',
            'ganda' => 'image/ganda.jpg',
        ];
        if (array_key_exists($valve_type, $standard_images)) {
            $image_path = $standard_images[$valve_type]; // Store standard image path
        } else {
            $error = 'Tipe valve tidak valid.';
        }
    } else {
        $error = 'Harap upload gambar atau pilih tipe valve.';
    }
    // Only proceed if there's no error
    if (empty($error)) {
        // Start transaction
        $conn->begin_transaction();
        try {
            // Insert date and get ID
            $stmt_date = $conn->prepare("INSERT INTO date (tanggal) VALUES (?)");
            $stmt_date->bind_param("s", $date);
            $stmt_date->execute();
            $id_date = $stmt_date->insert_id;
            $stmt_date->close();
            // Insert other data with the same date ID
            $stmt_no_wo = $conn->prepare("INSERT INTO no_wo (nomer, id_date) VALUES (?, ?)");
            $stmt_no_wo->bind_param("si", $no_wo, $id_date);
            $stmt_no_wo->execute();
            $stmt_no_wo->close();
            $stmt_plant = $conn->prepare("INSERT INTO plant (nama, id_date) VALUES (?, ?)");
            $stmt_plant->bind_param("si", $plant, $id_date);
            $stmt_plant->execute();
            $stmt_plant->close();
            $stmt_location = $conn->prepare("INSERT INTO lokasi (nama, id_date) VALUES (?, ?)");
            $stmt_location->bind_param("si", $location, $id_date);
            $stmt_location->execute();
            $stmt_location->close();
            // Insert data ke tabel frequensi - Corrected with bulan and nama
            $sql_frequensi = "INSERT INTO frequensi (bulan, nama, id_date) VALUES (?, ?, ?)";
            $stmt_frequensi = $conn->prepare($sql_frequensi);
            if (!$stmt_frequensi) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt_frequensi->bind_param("ssi", $bulan, $nama, $id_date);
            if (!$stmt_frequensi->execute()) {
                throw new Exception("Execute failed: " . $stmt_frequensi->error);
            }
            $stmt_machine = $conn->prepare("INSERT INTO machine (nomer, id_date) VALUES (?, ?)");
            $stmt_machine->bind_param("si", $machine, $id_date);
            $stmt_machine->execute();
            $stmt_machine->close();
            // Insert type with image path
            $stmt_type = $conn->prepare("INSERT INTO type (nama, id_date, image_path) VALUES (?, ?, ?)");
            $stmt_type->bind_param("sis", $type, $id_date, $image_path);
            $stmt_type->execute();
            $stmt_type->close();
            // Insert motor vibration data
            $motor_data = [
                'MIH' => $_POST['result_mih'],
                'MIV' => $_POST['result_miv'],
                'MIA' => $_POST['result_mia'],
                'MOH' => $_POST['result_moh'],
                'MOV' => $_POST['result_mov'],
                'MOA' => $_POST['result_moa'],
                'ampere' => $_POST['result_ampere'],
                'hour_meter' => $_POST['result_hour_meter'],
                'rpm' => $_POST['result_rpm'],
            ];
            $motor_names = [
                'MIH' => 'Motor Inboard Horizontal',
                'MIV' => 'Motor Inboard Vertical',
                'MIA' => 'Motor Inboard Axial',
                'MOH' => 'Motor Outboard Horizontal',
                'MOV' => 'Motor Outboard Vertical',
                'MOA' => 'Motor Outboard Axial',
                'ampere' => 'Ampere',
                'hour_meter' => 'Hour Meter',
                'rpm' => 'RPM',
            ];
            $motor_points = [
                'MIH' => 'MIH',
                'MIV' => 'MIV',
                'MIA' => 'MIA',
                'MOH' => 'MOH',
                'MOV' => 'MOV',
                'MOA' => 'MOA',
                'ampere' => '............',
                'hour_meter' => '............',
                'rpm' => '............',
            ];
            $motor_standards = [
                'MIH' => '6,5 mms(.....)',
                'MIV' => '7,6 mms(.....)',
                'MIA' => '8,5 mms(.....)',
                'MOH' => '9,5 mms(.....)',
                'MOV' => '............',
                'MOA' => '............',
                'ampere' => '............',
                'hour_meter' => '............',
                'rpm' => '............',
            ];
            $motor_comments = [
                'MIH' => $_POST['comment_mih'],
                'MIV' => $_POST['comment_miv'],
                'MIA' => $_POST['comment_mia'],
                'MOH' => $_POST['comment_moh'],
                'MOV' => $_POST['comment_mov'],
                'MOA' => $_POST['comment_moa'],
                'ampere' => $_POST['comment_ampere'],
                'hour_meter' => $_POST['comment_hour_meter'],
                'rpm' => $_POST['comment_rpm'],
            ];
            $stmt_motor = $conn->prepare("INSERT INTO vibrasi_motor (id_date, nama, hasil, keterangan, point, standar_max) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($motor_data as $id => $hasil) {
                $stmt_motor->bind_param("isssss", $id_date, $motor_names[$id], $hasil, $motor_comments[$id], $motor_points[$id], $motor_standards[$id]);
                $stmt_motor->execute();
            }
            $stmt_motor->close();
            // Insert pump vibration data
            $pump_data = [
                'PIH' => $_POST['result_pih'],
                'PIV' => $_POST['result_piv'],
                'PIA' => $_POST['result_pia'],
                'POH' => $_POST['result_poh'],
                'POV' => $_POST['result_pov'],
                'POA' => $_POST['result_poa'],
                'pressure' => $_POST['result_pressure'],
            ];
            $pump_names = [
                'PIH' => 'Pump Inboard Horizontal',
                'PIV' => 'Pump Inboard Vertical',
                'PIA' => 'Pump Inboard Axial',
                'POH' => 'Pump Outboard Horizontal',
                'POV' => 'Pump Outboard Vertical',
                'POA' => 'Pump Outboard Axial',
                'pressure' => 'Pressure',
            ];
            $pump_points = [
                'PIH' => 'PIH',
                'PIV' => 'PIV',
                'PIA' => 'PIA',
                'POH' => 'POH',
                'POV' => 'POV',
                'POA' => 'POA',
                'pressure' => '............',
            ];
            $pump_standards = [
                'PIH' => '6,5 mms(.....)',
                'PIV' => '7,6 mms(.....)',
                'PIA' => '8,5 mms(.....)',
                'POH' => '9,5 mms(.....)',
                'POV' => '............',
                'POA' => '............',
                'pressure' => '............',
            ];
            $pump_comments = [
                'PIH' => $_POST['comment_pih'],
                'PIV' => $_POST['comment_piv'],
                'PIA' => $_POST['comment_pia'],
                'POH' => $_POST['comment_poh'],
                'POV' => $_POST['comment_pov'],
                'POA' => $_POST['comment_poa'],
                'pressure' => $_POST['comment_pressure'],
            ];
            $stmt_pump = $conn->prepare("INSERT INTO vibrasi_pompa (id_date, nama, hasil, keterangan, point, standar_max) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($pump_data as $id => $hasil) {
                $stmt_pump->bind_param("isssss", $id_date, $pump_names[$id], $hasil, $pump_comments[$id], $pump_points[$id], $pump_standards[$id]);
                $stmt_pump->execute();
            }
            $stmt_pump->close();
            // Insert temperature data
            $temp_data = [
                'T0' => $_POST['result_t0'],
                'T1' => $_POST['result_t1'],
                'T2' => $_POST['result_t2'],
                'T3' => $_POST['result_t3'],
                'T4' => $_POST['result_t4'],
                'T5' => $_POST['result_t5'],
                'T6' => $_POST['result_t6'],
            ];
            $temp_names = [
                'T0' => 'Temperature Ruangan',
                'T1' => 'Temperature Casing Motor',
                'T2' => 'Temperature Bearing Motor',
                'T3' => 'Temperature Bearing Atas',
                'T4' => 'Temperature Bearing Bawah',
                'T5' => 'Temperature Bearing Atas',
                'T6' => 'Temperature Bearing Bawah',
            ];
            $temp_points = [
                'T0' => 'T0',
                'T1' => 'T1',
                'T2' => 'T2',
                'T3' => 'T3(IN)',
                'T4' => 'T4(IN)',
                'T5' => 'T5(OUT)',
                'T6' => 'T6(OUT)',
            ];
            $temp_standards = [
                'T0' => '80°C',
                'T1' => '............',
                'T2' => '............',
                'T3' => '............',
                'T4' => '............',
                'T5' => '............',
                'T6' => '............',
            ];
            $temp_comments = [
                'T0' => $_POST['comment_t0'],
                'T1' => $_POST['comment_t1'],
                'T2' => $_POST['comment_t2'],
                'T3' => $_POST['comment_t3'],
                'T4' => $_POST['comment_t4'],
                'T5' => $_POST['comment_t5'],
                'T6' => $_POST['comment_t6'],
            ];
            $stmt_temp = $conn->prepare("INSERT INTO temperatur (id_date, nama, hasil, keterangan, point, standar_max) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($temp_data as $id => $hasil) {
                $stmt_temp->bind_param("isssss", $id_date, $temp_names[$id], $hasil, $temp_comments[$id], $temp_points[$id], $temp_standards[$id]);
                $stmt_temp->execute();
            }
            $stmt_temp->close();
            // Insert Cek Kondisi Data
            $conditions = [
                'Oli dan Grease' => [
                    'status_1' => 'Cukup',
                    'checkbox_1' => isset($_POST['condition_oli']) && $_POST['condition_oli'] === 'cukup',
                    'status_2' => 'Tidak Cukup/Kosong',
                    'checkbox_2' => isset($_POST['condition_oli']) && $_POST['condition_oli'] === 'kosong',
                ],
                'Gland Packing/Mech Seal' => [
                    'status_1' => 'Tidak Bocor/Bocor Kecil',
                    'checkbox_1' => isset($_POST['condition_gland']) && $_POST['condition_gland'] === 'tidak_bocor',
                    'status_2' => 'Bocor Besar',
                    'checkbox_2' => isset($_POST['condition_gland']) && $_POST['condition_gland'] === 'bocor_besar',
                ],
                'Baut2 Dudukan/Grouting' => [
                    'status_1' => 'Kuat/Tidak Longgar',
                    'checkbox_1' => isset($_POST['condition_baut']) && $_POST['condition_baut'] === 'kuat',
                    'status_2' => 'Longgar',
                    'checkbox_2' => isset($_POST['condition_baut']) && $_POST['condition_baut'] === 'longgar',
                ],
                'Seal Kopling' => [
                    'status_1' => 'Tidak Bocor/Robek',
                    'checkbox_1' => isset($_POST['condition_seal']) && $_POST['condition_seal'] === 'tidak_bocor',
                    'status_2' => 'Bocor/Tidak Robek',
                    'checkbox_2' => isset($_POST['condition_seal']) && $_POST['condition_seal'] === 'bocor',
                ],
            ];
            $stmt_condition = $conn->prepare("INSERT INTO cek_kondisi (id_date, condition_name, status_1, checkbox_1, status_2, checkbox_2) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($conditions as $condition_name => $data) {
                // Only save if one checkbox is selected for this condition
                if ((isset($_POST['condition_oli']) && $condition_name === 'Oli dan Grease') ||
                    (isset($_POST['condition_gland']) && $condition_name === 'Gland Packing/Mech Seal') ||
                    (isset($_POST['condition_baut']) && $condition_name === 'Baut2 Dudukan/Grouting') ||
                    (isset($_POST['condition_seal']) && $condition_name === 'Seal Kopling')) {
                    // Create variables for bind_param
                    $status_1 = $data['status_1'];
                    $checkbox_1 = $data['checkbox_1'] ? 'Ya' : 'Tidak'; // Convert boolean to string
                    $status_2 = $data['status_2'];
                    $checkbox_2 = $data['checkbox_2'] ? 'Ya' : 'Tidak'; // Convert boolean to string
                    // Bind parameters using variables
                    $stmt_condition->bind_param(
                        "isssss", 
                        $id_date,
                        $condition_name, 
                        $status_1,
                        $checkbox_1,
                        $status_2,
                        $checkbox_2
                    );
                    // Execute the statement
                    if (!$stmt_condition->execute()) {
                        throw new Exception("Error inserting condition data for $condition_name: " . $stmt_condition->error);
                    }
                }
            }
            $stmt_condition->close();
            // Commit transaction
            $conn->commit();
            // Show success message
            echo "<script>
                    alert('Data berhasil ditambahkan.');
                    window.location.href = 'inspeksi_motor.php';
                  </script>";
            exit();
        } catch (Exception $e) {
            // Rollback transaction if any error occurs
            $conn->rollback();
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Input</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="website icon" type="png" href="../image/logo.png">
    <style>
        /* Your existing CSS styles */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            color: #333;
            line-height: 1.6;
        }
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
        h1 {
            font-size: 32px;
            margin: 40px 0;
            color: #003366;
            text-align: center;
            text-transform: uppercase;
            font-weight: 700;
            position: relative;
        }
        .form-table {
            width: 50%;
            margin: 30px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .form-table tr {
            margin-bottom: 15px;
        }
        .form-table td {
            padding: 12px;
            vertical-align: middle;
            font-size: 16px;
        }
        input[type="text"], input[type="number"], select, input[type="date"], input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
            font-size: 14px;
            box-sizing: border-box;
            transition: border 0.3s ease;
        }
        input[type="text"]:focus, input[type="number"]:focus,
        select:focus, input[type="date"]:focus, input[type="file"]:focus {
            border-color: #004c99;
            outline: none;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
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
        .button-container {
            text-align: center;
            margin-top: 30px;
            margin-bottom: 50px;
            position: fixed;  /* Agar posisi tetap */
            right: 40px;        /* Jarak dari sisi kiri layar */
            bottom: 2px;      /* Jarak dari bawah, bisa juga gunakan top */
            z-index: 1000;     /* Supaya tidak ketutup elemen lain */   
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
        h2 {
            color: #004c99;
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 15px;
            border-bottom: 2px solid #004c99;
            padding-bottom: 10px;
        }
        .table-container {
            margin: 20px 30px;
        }
        .valve-image-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 15px 0;
            min-height: 200px;
            border: 2px dashed #ddd;
            border-radius: 8px;
            padding: 10px;
        }
        .valve-image {
            max-width: 100%;
            max-height: 250px;
            object-fit: contain;
            border-radius: 5px;
            display: none;
        }
        .error-message {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            margin: 15px auto;
            border-radius: 5px;
            text-align: center;
            width: 80%;
            font-weight: bold;
        }
        @media (max-width: 768px) {
            .form-table {
                width: 90%;
            }
            .logo-container {
                flex-direction: column;
            }
            .logo {
                width: 60px;
                height: 60px;
            }
        }
        .frequency-container {
    display: flex;
    gap: 15px;
    align-items: center;
    width: 100%;
}

.frequency-container input[list] {
    flex: 1;
    padding: 10px 12px;
    border: 2px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    background-color: #f9f9f9;
    transition: border 0.3s ease;
    box-sizing: border-box;
}

.frequency-container input[list]:focus {
    border-color: #004c99;
    outline: none;
}

@media (max-width: 768px) {
    .frequency-container {
        flex-direction: column;
        gap: 10px;
    }

    .frequency-container input[list] {
        width: 100%;
    }
}
    </style>
</head>
<body>
    <div class="logo-container">
        <img src="../image/logo.png" alt="Logo PAM Jaya" class="logo">
        <div class="judul">PERUMDA AIR MINUM JAYA</div>
        <img src="../image/Jakarta.png" alt="Logo Jakarta" class="logo">
    </div>
    <h1>Tambah Data Maintenance</h1>
    <?php if (!empty($error)): ?>
        <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>
    <form action="" method="POST" enctype="multipart/form-data">
        <!-- Basic Information Table -->
        <table class="form-table">
            <tr>
                <td>No.Wo:</td>
                <td><input type="text" name="no_wo" required></td>
            </tr>
            <tr>
                <td>Date:</td>
                <td><input type="date" name="date" required></td>
            </tr>
            <tr>
                <td>Plant:</td>
                <td><input type="text" name="plant" required></td>
            </tr>
            <tr>
                <td>Location:</td>
                <td>
                    <select name="location" required>
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
                <option value="5 Tahun"></option>
            </datalist>
        </div>
    </td>
</tr>

            <tr>
                <td>Type:</td>
                <td>
                <select id="valve_type" name="valve_type">
                    <option value="">Pilih tipe</option>
                    <option value="hori">Horizontal</option>
                    <option value="vertikal">Vertikal</option>
                    <option value="ganda">Horizontal Bearing Ganda</option>
                </select>
                </td>
            </tr>
            <tr>
                <td>Upload Gambar:</td>
                <td>
                    <input type="file" id="image" name="image">
                    <img id="valve_image" style="display: none; max-width: 300px; margin-top: 10px;" />
                </td>
            </tr>
            <tr>
                <td>Machine no:</td>
                <td><input type="text" name="machine" required></td>
            </tr>
        </table>
        <!-- Tabel Vibrasi Motor -->
        <div class="table-container">
            <h2 style="margin-left: 15px;">Motor Vibration</h2>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Motor Vibration</th>
                        <th>Point</th>
                        <th>Result</th>
                        <th>Standard Max</th>
                        <th>Comment</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Motor Inboard Horizontal</td>
                        <td>MIH</td>
                        <td><input type="text" name="result_mih" /></td>
                        <td>6,5 mms(.....)</td>
                        <td><input type="text" name="comment_mih" /></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Motor Inboard Vertical</td>
                        <td>MIV</td>
                        <td><input type="text" name="result_miv" /></td>
                        <td>7,6 mms(.....)</td>
                        <td><input type="text" name="comment_miv" /></td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Motor Inboard Axial</td>
                        <td>MIA</td>
                        <td><input type="text" name="result_mia" /></td>
                        <td>8,5 mms(.....)</td>
                        <td><input type="text" name="comment_mia" /></td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>Motor Outboard Horizontal</td>
                        <td>MOH</td>
                        <td><input type="text" name="result_moh" /></td>
                        <td>9,5 mms(.....)</td>
                        <td><input type="text" name="comment_moh" /></td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>Motor Outboard Vertical</td>
                        <td>MOV</td>
                        <td><input type="text" name="result_mov" /></td>
                        <td>............</td>
                        <td><input type="text" name="comment_mov" /></td>
                    </tr>
                    <tr>
                        <td>6</td>
                        <td>Motor Outboard Axial</td>
                        <td>MOA</td>
                        <td><input type="text" name="result_moa" /></td>
                        <td>............</td>
                        <td><input type="text" name="comment_moa" /></td>
                    </tr>
                    <tr>
                        <td>7</td>
                        <td>Ampere</td>
                        <td>............</td>
                        <td><input type="text" name="result_ampere" /></td>
                        <td>............</td>
                        <td><input type="text" name="comment_ampere" /></td>
                    </tr>
                    <tr>
                        <td>8</td>
                        <td>Hour Meter</td>
                        <td>............</td>
                        <td><input type="text" name="result_hour_meter" /></td>
                        <td>............</td>
                        <td><input type="text" name="comment_hour_meter" /></td>
                    </tr>
                    <tr>
                        <td>9</td>
                        <td>RPM</td>
                        <td>............</td>
                        <td><input type="text" name="result_rpm" /></td>
                        <td>............</td>
                        <td><input type="text" name="comment_rpm" /></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- Tabel Vibrasi Pompa -->
        <div class="table-container">
            <h2 style="margin-left: 15px;">Pump Vibration</h2>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Pump/Blower Vibration</th>
                        <th>Point</th>
                        <th>Result</th>
                        <th>Standard Max</th>
                        <th>Comment</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Pump Inboard Horizontal</td>
                        <td>PIH</td>
                        <td><input type="text" name="result_pih" /></td>
                        <td>6,5 mms(.....)</td>
                        <td><input type="text" name="comment_pih" /></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Pump Inboard Vertical</td>
                        <td>PIV</td>
                        <td><input type="text" name="result_piv" /></td>
                        <td>7,6 mms(.....)</td>
                        <td><input type="text" name="comment_piv" /></td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Pump Inboard Axial</td>
                        <td>PIA</td>
                        <td><input type="text" name="result_pia" /></td>
                        <td>8,5 mms(.....)</td>
                        <td><input type="text" name="comment_pia" /></td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>Pump Outboard Horizontal</td>
                        <td>POH</td>
                        <td><input type="text" name="result_poh" /></td>
                        <td>9,5 mms(.....)</td>
                        <td><input type="text" name="comment_poh" /></td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>Pump Outboard Vertical</td>
                        <td>POV</td>
                        <td><input type="text" name="result_pov" /></td>
                        <td>............</td>
                        <td><input type="text" name="comment_pov" /></td>
                    </tr>
                    <tr>
                        <td>6</td>
                        <td>Pump Outboard Axial</td>
                        <td>POA</td>
                        <td><input type="text" name="result_poa" /></td>
                        <td>............</td>
                        <td><input type="text" name="comment_poa" /></td>
                    </tr>
                    <tr>
                        <td>7</td>
                        <td>Pressure</td>
                        <td>............</td>
                        <td><input type="text" name="result_pressure" /></td>
                        <td>............</td>
                        <td><input type="text" name="comment_pressure" /></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- Temperature Section -->
        <div class="table-container">
            <h2 style="margin-left: 15px;">Temperature</h2>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Temperature</th>
                        <th>Point</th>
                        <th>Result</th>
                        <th>Standard Max</th>
                        <th>Comment</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Temperature Ruangan</td>
                        <td>T0</td>
                        <td><input type="text" name="result_t0" /></td>
                        <td>80°C</td>
                        <td><input type="text" name="comment_t0" /></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Temperature Casing Motor</td>
                        <td>T1</td>
                        <td><input type="text" name="result_t1" /></td>
                        <td>............</td>
                        <td><input type="text" name="comment_t1" /></td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Temperature Bearing Motor</td>
                        <td>T2</td>
                        <td><input type="text" name="result_t2" /></td>
                        <td>............</td>
                        <td><input type="text" name="comment_t2" /></td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>Temperature Bearing Atas</td>
                        <td>T3(IN)</td>
                        <td><input type="text" name="result_t3" /></td>
                        <td>............</td>
                        <td><input type="text" name="comment_t3" /></td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>Temperature Bearing Bawah</td>
                        <td>T4(IN)</td>
                        <td><input type="text" name="result_t4" /></td>
                        <td>............</td>
                        <td><input type="text" name="comment_t4" /></td>
                    </tr>
                    <tr>
                        <td>6</td>
                        <td>Temperature Bearing Atas</td>
                        <td>T5(OUT)</td>
                        <td><input type="text" name="result_t5" /></td>
                        <td>............</td>
                        <td><input type="text" name="comment_t5" /></td>
                    </tr>
                    <tr>
                        <td>7</td>
                        <td>Temperature Bearing Bawah</td>
                        <td>T6(OUT)</td>
                        <td><input type="text" name="result_t6" /></td>
                        <td>............</td>
                        <td><input type="text" name="comment_t6" /></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="table-container">
            <h2>Cek Kondisi</h2>
            <table>
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
                        <td><input type="radio" class="condition-radio" name="condition_oli" value="cukup" /></td>
                        <td>Tidak Cukup/Kosong</td>
                        <td><input type="radio" class="condition-radio" name="condition_oli" value="kosong" /></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Gland Packing/Mech Seal</td>
                        <td>Tidak Bocor/Bocor Kecil</td>
                        <td><input type="radio" class="condition-radio" name="condition_gland" value="tidak_bocor" /></td>
                        <td>Bocor Besar</td>
                        <td><input type="radio" class="condition-radio" name="condition_gland" value="bocor_besar" /></td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Baut2 Dudukan/Grouting</td>
                        <td>Kuat/Tidak Longgar</td>
                        <td><input type="radio" class="condition-radio" name="condition_baut" value="kuat" /></td>
                        <td>Longgar</td>
                        <td><input type="radio" class="condition-radio" name="condition_baut" value="longgar" /></td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>Seal Kopling</td>
                        <td>Tidak Bocor/Robek</td>
                        <td><input type="radio" class="condition-radio" name="condition_seal" value="tidak_bocor" /></td>
                        <td>Bocor/Tidak Robek</td>
                        <td><input type="radio" class="condition-radio" name="condition_seal" value="bocor" /></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <br>
        <br>
        <br>
        <br>

        <!-- Submit Buttons -->
        <div class="button-container">
            <button type="submit">Submit</button>
            <button type="button" onclick="window.location.href='inspeksi_motor.php'">Kembali</button>
        </div>
    </form>
    <script>
        // Image preview functionality
                document.getElementById('image').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const img = document.getElementById('valve_image');
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                    img.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                img.style.display = 'none';
            }
        });

        const valveImages = {
            'hori': '../image/hori.jpg',
            'vertikal': '../image/vertikal.jpg',
            'ganda': '../image/ganda.jpg'
        };

        document.getElementById('valve_type').addEventListener('change', function() {
            const selectedType = this.value;
            const img = document.getElementById('valve_image');
            const fileInput = document.getElementById('image');
            fileInput.value = ''; // Reset file input jika memilih tipe standar
            if (selectedType && valveImages[selectedType]) {
                img.src = valveImages[selectedType];
                img.style.display = 'block';
            } else {
                img.style.display = 'none';
            }
        });

        // Form submission confirmation
        document.querySelector('form').addEventListener('submit', function(event) {
            if (!confirm('Apakah Anda yakin ingin mengirim data?')) {
                event.preventDefault();
            }
        }); const requiredFields = document.querySelectorAll('input[required], select[required]');
            requiredFields.forEach(field => {
                field.addEventListener('invalid', function() {
                    this.classList.add('field-error');
                });
                
                field.addEventListener('input', function() {
                    this.classList.remove('field-error');
                });
            });
    </script>
</body>
</html>