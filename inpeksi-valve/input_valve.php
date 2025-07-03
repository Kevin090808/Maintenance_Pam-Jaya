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

// Process form if data is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get data from form and validate
    $no_wo = isset($_POST['no_wo']) ? mysqli_real_escape_string($conn, $_POST['no_wo']) : '';
    $tanggal = isset($_POST['tanggal']) ? mysqli_real_escape_string($conn, $_POST['tanggal']) : '';
    $plant = isset($_POST['plant']) ? mysqli_real_escape_string($conn, $_POST['plant']) : '';
    $lokasi = isset($_POST['lokasi']) ? mysqli_real_escape_string($conn, $_POST['lokasi']) : '';
    
    // Corrected frequensi fields
    $bulan = isset($_POST['bulan']) ? mysqli_real_escape_string($conn, $_POST['bulan']) : '';
    $nama = isset($_POST['nama']) ? mysqli_real_escape_string($conn, $_POST['nama']) : '';
    
    // Data for valve inspection
    $equipment = isset($_POST['equipment']) ? mysqli_real_escape_string($conn, $_POST['equipment']) : '';
    
    // Corrected valve fields
    $butterfly = isset($_POST['butterfly']) ? mysqli_real_escape_string($conn, $_POST['butterfly']) : '';
    $gate = isset($_POST['gate']) ? mysqli_real_escape_string($conn, $_POST['gate']) : '';
    $ball = isset($_POST['ball']) ? mysqli_real_escape_string($conn, $_POST['ball']) : '';
    $globe = isset($_POST['globe']) ? mysqli_real_escape_string($conn, $_POST['globe']) : '';
    $membran = isset($_POST['membran']) ? mysqli_real_escape_string($conn, $_POST['membran']) : '';
    $foot_valve = isset($_POST['foot_valve']) ? mysqli_real_escape_string($conn, $_POST['foot_valve']) : '';
    $swing_check = isset($_POST['swing_check']) ? mysqli_real_escape_string($conn, $_POST['swing_check']) : '';
    
    $good = isset($_POST['good']) ? '1' : '0';
    $not_good = isset($_POST['not_good']) ? '1' : '0';
    $perawatan_part = isset($_POST['perawatan_part']) ? mysqli_real_escape_string($conn, $_POST['perawatan_part']) : '';
    $penggantian_part = isset($_POST['penggantian_part']) ? mysqli_real_escape_string($conn, $_POST['penggantian_part']) : '';
    $jumlah_part = isset($_POST['jumlah_part']) ? mysqli_real_escape_string($conn, $_POST['jumlah_part']) : '';
    $remaks = isset($_POST['remaks']) ? mysqli_real_escape_string($conn, $_POST['remaks']) : '';

    // Validate input - added frequensi validation separating bulan and nama
    if (empty($no_wo) || empty($tanggal) || empty($plant) || empty($lokasi) || empty($bulan) || empty($nama)) {
        $error_message = "Semua field harus diisi!";
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

            // Insert data to plant table
            $sql_plant = "INSERT INTO plant (nama, id_date) VALUES (?, ?)";
            $stmt_plant = $conn->prepare($sql_plant);
            if (!$stmt_plant) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt_plant->bind_param("si", $plant, $id_date);
            if (!$stmt_plant->execute()) {
                throw new Exception("Execute failed: " . $stmt_plant->error);
            }

            // Insert data to lokasi table
            $sql_lokasi = "INSERT INTO lokasi (nama, id_date) VALUES (?, ?)";
            $stmt_lokasi = $conn->prepare($sql_lokasi);
            if (!$stmt_lokasi) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt_lokasi->bind_param("si", $lokasi, $id_date);
            if (!$stmt_lokasi->execute()) {
                throw new Exception("Execute failed: " . $stmt_lokasi->error);
            }

            // Insert data to frequensi table with corrected values
            $sql_frequensi = "INSERT INTO frequensi (bulan, nama, id_date) VALUES (?, ?, ?)";
            $stmt_frequensi = $conn->prepare($sql_frequensi);
            if (!$stmt_frequensi) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt_frequensi->bind_param("ssi", $bulan, $nama, $id_date);
            if (!$stmt_frequensi->execute()) {
                throw new Exception("Execute failed: " . $stmt_frequensi->error);
            }

            // Insert data to inspeksi_valve table
            $sql_inspeksi_valve = "INSERT INTO inspeksi_valve (equipment, butterfly, gate, ball, globe, membran, foot_valve, swing_check, good, not_good, perawatan_part, penggantian_part, jumlah_part, remaks, id_date) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_inspeksi_valve = $conn->prepare($sql_inspeksi_valve);
            if (!$stmt_inspeksi_valve) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt_inspeksi_valve->bind_param("ssssssssssssssi", 
                $equipment, $butterfly, $gate, $ball, $globe, $membran, 
                $foot_valve, $swing_check, $good, $not_good, $perawatan_part, $penggantian_part,
                $jumlah_part, $remaks, $id_date);
            if (!$stmt_inspeksi_valve->execute()) {
                throw new Exception("Execute failed: " . $stmt_inspeksi_valve->error);
            }

            // Commit transaction
            $conn->commit();

            // Success notification and redirect
            $success_message = "Data berhasil disimpan!";
            // Clear form data after successful submission
            $_POST = array();
        } catch (Exception $e) {
            // Rollback transaction if error occurs
            $conn->rollback();
            $error_message = "Error: " . $e->getMessage();
        } finally {
            // Close statements
            if (isset($stmt_date)) $stmt_date->close();
            if (isset($stmt_no_wo)) $stmt_no_wo->close();
            if (isset($stmt_plant)) $stmt_plant->close();
            if (isset($stmt_lokasi)) $stmt_lokasi->close();
            if (isset($stmt_frequensi)) $stmt_frequensi->close();
            if (isset($stmt_inspeksi_valve)) $stmt_inspeksi_valve->close();
        }
    }
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

$sql_equipment = "SELECT DISTINCT equipment FROM inspeksi_valve ORDER BY equipment LIMIT 10";
$result_equipment = $conn->query($sql_equipment);
if ($result_equipment && $result_equipment->num_rows > 0) {
    while ($row = $result_equipment->fetch_assoc()) {
        $suggested_equipment[] = $row['equipment'];
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Form Inspeksi Valve</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
  
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
      max-width: 1200px;
      margin: 0 auto;
    }

    .form-container {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border-radius: 24px;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .header {
      background: linear-gradient(135deg, #2D3748 0%, #4A5568 100%);
      padding: 40px;
      text-align: center;
      color: white;
      position: relative;
      overflow: hidden;
    }

    .header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
      opacity: 0.3;
    }

    .logo {
      position: relative;
      z-index: 1;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 80px;
      height: 80px;
      background: linear-gradient(135deg, #3182CE, #2B6CB0);
      border-radius: 20px;
      margin-bottom: 20px;
      box-shadow: 0 10px 30px rgba(49, 130, 206, 0.3);
    }

    .logo i {
      font-size: 36px;
      color: white;
      animation: rotate 10s linear infinite;
    }

    @keyframes rotate {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }

    .header h1 {
      position: relative;
      z-index: 1;
      font-size: 32px;
      font-weight: 700;
      margin-bottom: 8px;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .header p {
      position: relative;
      z-index: 1;
      font-size: 16px;
      opacity: 0.9;
      font-weight: 400;
    }

    .form-content {
      padding: 40px;
    }

    .alert {
      padding: 16px 20px;
      border-radius: 12px;
      margin-bottom: 24px;
      display: flex;
      align-items: center;
      gap: 12px;
      font-weight: 500;
    }

    .alert-success {
      background: linear-gradient(135deg, #10B981, #059669);
      color: white;
      box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .alert-error {
      background: linear-gradient(135deg, #EF4444, #DC2626);
      color: white;
      box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    .section {
      margin-bottom: 40px;
    }

    .section-title {
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 20px;
      font-weight: 600;
      color: #2D3748;
      margin-bottom: 24px;
      padding-bottom: 12px;
      border-bottom: 3px solid #E2E8F0;
      position: relative;
    }

    .section-title::after {
      content: '';
      position: absolute;
      bottom: -3px;
      left: 0;
      width: 60px;
      height: 3px;
      background: linear-gradient(135deg, #3182CE, #2B6CB0);
      border-radius: 2px;
    }

    .section-title i {
      color: #3182CE;
      font-size: 22px;
    }

    .form-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 24px;
    }

    .form-group {
      position: relative;
    }

    .form-group.full-width {
      grid-column: 1 / -1;
    }

    .form-group label {
      display: block;
      font-weight: 500;
      color: #374151;
      margin-bottom: 8px;
      font-size: 14px;
    }

    .form-control {
      width: 100%;
      padding: 16px 20px;
      border: 2px solid #E5E7EB;
      border-radius: 12px;
      font-size: 14px;
      font-family: inherit;
      transition: all 0.3s ease;
      background: white;
    }

    .form-control:focus {
      outline: none;
      border-color: #3182CE;
      box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
      transform: translateY(-1px);
    }

    .form-control:hover {
      border-color: #9CA3AF;
    }

    .form-control.field-error {
      border-color: #EF4444;
      box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }

    select.form-control {
      cursor: pointer;
      background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6,9 12,15 18,9'%3e%3c/polyline%3e%3c/svg%3e");
      background-repeat: no-repeat;
      background-position: right 16px center;
      background-size: 16px;
      padding-right: 50px;
    }

    .frequency-container {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }

    .checkbox-group {
      display: flex;
      gap: 24px;
      flex-wrap: wrap;
    }

    .checkbox-group label {
      display: flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
      font-weight: 400;
      padding: 12px 20px;
      background: #F8FAFC;
      border: 2px solid #E2E8F0;
      border-radius: 12px;
      transition: all 0.3s ease;
    }

    .checkbox-group label:hover {
      background: #EBF8FF;
      border-color: #3182CE;
    }

    .checkbox-group input[type="checkbox"] {
      width: 18px;
      height: 18px;
      accent-color: #3182CE;
    }

    .checkbox-group input[type="checkbox"]:checked + span {
      font-weight: 500;
      color: #3182CE;
    }

    textarea.form-control {
      resize: vertical;
      min-height: 100px;
    }

    .button-container {
      display: flex;
      gap: 16px;
      justify-content: center;
      margin-top: 40px;
      flex-wrap: wrap;
    }

    .btn {
      padding: 16px 32px;
      border-radius: 12px;
      font-size: 16px;
      font-weight: 600;
      border: none;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
      min-width: 160px;
      justify-content: center;
    }

    .btn-submit {
      background: linear-gradient(135deg, #10B981, #059669);
      color: white;
      box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .btn-submit:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
    }

    .btn-back {
      background: linear-gradient(135deg, #6B7280, #4B5563);
      color: white;
      box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3);
    }

    .btn-back:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(107, 114, 128, 0.4);
    }

    .footer {
      text-align: center;
      padding: 30px;
      background: #F8FAFC;
      color: #6B7280;
      font-size: 14px;
      border-top: 1px solid #E5E7EB;
    }

    /* Loading animation */
    .loading {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 9999;
      justify-content: center;
      align-items: center;
    }

    .spinner {
      width: 50px;
      height: 50px;
      border: 3px solid #f3f3f3;
      border-top: 3px solid #3182CE;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* Responsive design */
    @media (max-width: 768px) {
      .container {
        padding: 10px;
      }

      .form-content {
        padding: 20px;
      }

      .header {
        padding: 30px 20px;
      }

      .header h1 {
        font-size: 24px;
      }

      .form-grid {
        grid-template-columns: 1fr;
      }

      .frequency-container {
        grid-template-columns: 1fr;
      }

      .button-container {
        flex-direction: column;
      }

      .btn {
        width: 100%;
      }
    }

    /* Input animations */
    .form-group {
      position: relative;
      overflow: hidden;
    }

    .form-group::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 2px;
      background: linear-gradient(135deg, #3182CE, #2B6CB0);
      transition: left 0.3s ease;
      z-index: 1;
    }

    .form-group:focus-within::before {
      left: 0;
    }

    /* Floating labels effect */
    .floating-label {
      position: relative;
    }

    .floating-label input:focus + label,
    .floating-label input:not(:placeholder-shown) + label {
      transform: translateY(-25px) scale(0.85);
      color: #3182CE;
    }

    /* Success animation */
    @keyframes success {
      0% { transform: scale(0.8); opacity: 0; }
      50% { transform: scale(1.1); }
      100% { transform: scale(1); opacity: 1; }
    }

    .success-animation {
      animation: success 0.5s ease-out;
    }
  </style>
</head>
<body>
  <div class="loading" id="loading">
    <div class="spinner"></div>
  </div>

  <div class="container">
    <div class="form-container">
      <div class="header">
        <div class="logo">
          <i class="fas fa-cog"></i>
        </div>
        <h1>Form Inspeksi Valve</h1>
        <p>Sistem Manajemen Maintenance Terintegrasi</p>
      </div>
      
      <div class="form-content">
        <!-- Alert messages would be displayed here -->
        <?php if (isset($success_message)): ?>
        <div class="alert alert-success">
          <i class="fas fa-check-circle"></i>
          <?php echo $success_message; ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
        <div class="alert alert-error">
          <i class="fas fa-exclamation-triangle"></i>
          <?php echo $error_message; ?>
        </div>
        <?php endif; ?>
        
        <form action="" method="POST" id="inspectionForm">
          <div class="section">
            <div class="section-title">
              <i class="fas fa-info-circle"></i>
              Informasi Dasar
            </div>
            
            <div class="form-grid">
              <div class="form-group">
                <label for="no_wo">No. Work Order</label>
                <input type="text" id="no_wo" name="no_wo" class="form-control" placeholder="Masukkan nomor WO" required>
              </div>
              
              <div class="form-group">
                <label for="tanggal">Tanggal Inspeksi</label>
                <input type="date" id="tanggal" name="tanggal" class="form-control" required>
              </div>
              
              <div class="form-group">
                <label for="plant">Plant</label>
                <input type="text" id="plant" name="plant" class="form-control" placeholder="Masukkan nama plant" required>
              </div>
              
              <div class="form-group">
                <label for="lokasi">Lokasi</label>
                <input type="text" id="lokasi" name="lokasi" class="form-control" placeholder="Masukkan lokasi" required>
              </div>
              
              <div class="form-group full-width">
                <label><i class="fas fa-clock"></i> Frequensi</label>
                <div class="frequency-container">
                  <input list="bulan" name="bulan" class="form-control" placeholder="Pilih Bulan" required>
                  <datalist id="bulan">
                    <option value="1 bulan"></option>
                    <option value="3 bulan"></option>
                    <option value="6 bulan"></option>
                    <option value="12 bulan"></option>
                  </datalist>
                  
                  <input list="nama" name="nama" class="form-control" placeholder="Pilih Nama" required>
                  <datalist id="nama">
                    <option value="2 Tahun"></option>
                    <option value="3 Tahun"></option>
                    <option value="5 tahun"></option>
                  </datalist>
                </div>
              </div>
              
              <div class="form-group full-width">
                <label for="equipment">Equipment</label>
                <input type="text" id="equipment" name="equipment" class="form-control" placeholder="Nama peralatan" required>
              </div>
            </div>
          </div>

          <div class="section">
            <div class="section-title">
              <i class="fas fa-check-circle"></i>
              Jenis Valve
            </div>
            
            <div class="form-grid">
              <div class="form-group">
                <label for="butterfly">Butterfly Valve</label>
                <select class="form-control" id="butterfly" name="butterfly" required>
                  <option value="">Pilih Valve</option>
                  <option value="ada">Ada</option>
                  <option value="tidak layak">Tidak Layak</option>
                  <option value="layak">Layak</option>
                  <option value="tidak ada">Tidak Ada</option>
                </select>
              </div>
              
              <div class="form-group">
                <label for="gate">Gate Valve</label>
                <select class="form-control" id="gate" name="gate" required>
                  <option value="">Pilih Valve</option>
                  <option value="ada">Ada</option>
                  <option value="tidak layak">Tidak Layak</option>
                  <option value="layak">Layak</option>
                  <option value="tidak ada">Tidak Ada</option>
                </select>
              </div>
              
              <div class="form-group">
                <label for="ball">Ball Valve</label>
                <select class="form-control" id="ball" name="ball" required>
                  <option value="">Pilih Valve</option>
                  <option value="ada">Ada</option>
                  <option value="tidak layak">Tidak Layak</option>
                  <option value="layak">Layak</option>
                  <option value="tidak ada">Tidak Ada</option>
                </select>
              </div>
              
              <div class="form-group">
                <label for="globe">Globe Valve</label>
                <select class="form-control" id="globe" name="globe" required>
                  <option value="">Pilih Valve</option>
                  <option value="ada">Ada</option>
                  <option value="tidak layak">Tidak Layak</option>
                  <option value="layak">Layak</option>
                  <option value="tidak ada">Tidak Ada</option>
                </select>
              </div>
              
              <div class="form-group">
                <label for="membran">Membran Valve</label>
                <select class="form-control" id="membran" name="membran" required>
                  <option value="">Pilih Valve</option>
                  <option value="ada">Ada</option>
                  <option value="tidak layak">Tidak Layak</option>
                  <option value="layak">Layak</option>
                  <option value="tidak ada">Tidak Ada</option>
                </select>
              </div>
              
              <div class="form-group">
                <label for="foot_valve">Foot Valve</label>
                <select class="form-control" id="foot_valve" name="foot_valve" required>
                  <option value="">Pilih Valve</option>
                  <option value="ada">Ada</option>
                  <option value="tidak layak">Tidak Layak</option>
                  <option value="layak">Layak</option>
                  <option value="tidak ada">Tidak Ada</option>
                </select>
              </div>
              
              <div class="form-group">
                <label for="swing_check">Swing Check Valve</label>
                <select class="form-control" id="swing_check" name="swing_check" required>
                  <option value="">Pilih Valve</option>
                  <option value="ada">Ada</option>
                  <option value="tidak layak">Tidak Layak</option>
                  <option value="layak">Layak</option>
                  <option value="tidak ada">Tidak Ada</option>
                </select>
              </div>
            </div>
          </div>
          
          <div class="section">
            <div class="section-title">
              <i class="fas fa-tools"></i>
              Kondisi dan Perawatan
            </div>
            
            <div class="form-group">
              <label>Kondisi</label>
              <div class="checkbox-group">
                <label>
                  <input type="checkbox" name="good" value="1">
                  <span>Good</span>
                </label>
                <label>
                  <input type="checkbox" name="not_good" value="1">
                  <span>Not Good</span>
                </label>
              </div>
            </div>
            
            <div class="form-grid">
              <div class="form-group">
                <label for="perawatan_part">Perawatan</label>
                <input type="text" id="perawatan_part" name="perawatan_part" class="form-control" placeholder="Detail perawatan">
              </div>
              
              <div class="form-group">
                <label for="penggantian_part">Penggantian</label>
                <input type="text" id="penggantian_part" name="penggantian_part" class="form-control" placeholder="Detail penggantian">
              </div>
              
              <div class="form-group">
                <label for="jumlah_part">Jumlah</label>
                <input type="text" id="jumlah_part" name="jumlah_part" class="form-control" placeholder="Jumlah">
              </div>
              
              <div class="form-group full-width">
                <label for="remaks">Remarks</label>
                <textarea id="remaks" name="remaks" class="form-control" rows="4" placeholder="Catatan tambahan"></textarea>
              </div>
            </div>
          </div>

          <div class="button-container">
            <button type="submit" class="btn btn-submit">
              <i class="fas fa-save"></i>
              Simpan Data
            </button>
            <button type="button" class="btn btn-back" onclick="window.location.href='inspeksi_valve.php'">
              <i class="fas fa-arrow-left"></i>
              Kembali
            </button>
          </div>
        </form>
      </div>
      
      <div class="footer">
        <p>Form Inspeksi Valve &copy; 2024 | Powered by Advanced Maintenance System</p>
      </div>
    </div>
  </div>

  <script>
    // Set today's date as default
    document.getElementById('tanggal').value = new Date().toISOString().split('T')[0];
    
    // Form validation and enhancement
    document.getElementById('inspectionForm').addEventListener('submit', function(e) {
      // Show loading animation
      document.getElementById('loading').style.display = 'flex';
      
      // Add form validation
      const requiredFields = document.querySelectorAll('input[required], select[required]');
      let isValid = true;
      
      requiredFields.forEach(field => {
        if (!field.value.trim()) {
          field.classList.add('field-error');
          isValid = false;
        } else {
          field.classList.remove('field-error');
        }
      });
      
      if (!isValid) {
        e.preventDefault();
        document.getElementById('loading').style.display = 'none';
        
        // Scroll to first error
        const firstError = document.querySelector('.field-error');
        if (firstError) {
          firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      }
    });
    
    // Real-time validation
    const requiredFields = document.querySelectorAll('input[required], select[required]');
    requiredFields.forEach(field => {
      field.addEventListener('input', function() {
        if (this.value.trim()) {
          this.classList.remove('field-error');
        }
      });
      
      field.addEventListener('blur', function() {
        if (!this.value.trim()) {
          this.classList.add('field-error');
        }
      });
    });
    
    // Add smooth animations on form interactions
    const formGroups = document.querySelectorAll('.form-group');
    formGroups.forEach(group => {
      const input = group.querySelector('.form-control');
      if (input) {
        input.addEventListener('focus', function() {
          group.style.transform = 'translateY(-2px)';
          group.style.transition = 'transform 0.3s ease';
        });
        
        input.addEventListener('blur', function() {
          group.style.transform = 'translateY(0)';
        });
      }
    });
    
    // Enhanced checkbox interaction
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
      checkbox.addEventListener('change', function() {
        const label = this.closest('label');
        if (this.checked) {
          label.style.background = 'linear-gradient(135deg, #EBF8FF, #DBEAFE)';
          label.style.borderColor = '#3182CE';
          label.style.transform = 'scale(1.02)';
        } else {
          label.style.background = '#F8FAFC';
          label.style.borderColor = '#E2E8F0';
          label.style.transform = 'scale(1)';
        }
      });
    });
    
    // Auto-save draft functionality (using session storage)
    const formInputs = document.querySelectorAll('input:not([type="submit"]), select, textarea');
    formInputs.forEach(input => {
      // Load saved value
      const savedValue = sessionStorage.getItem('valve_form_' + input.name);
      if (savedValue && input.type !== 'date') {
        input.value = savedValue;
      }
      
      // Save on change
      input.addEventListener('input', function() {
        sessionStorage.setItem('valve_form_' + input.name, input.value);
      });
    });
    
    // Clear draft on successful submission
    window.addEventListener('beforeunload', function() {
      if (document.querySelector('.alert-success')) {
        formInputs.forEach(input => {
          sessionStorage.removeItem('valve_form_' + input.name);
        });
      }
    });
  </script>
</body>
</html>