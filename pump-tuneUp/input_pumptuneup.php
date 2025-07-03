<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['session_username'])) {
    header("Location: ../login.php"); 
    exit();
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include '../koneksi.php';

// Initialize variables for form persistence
$formData = [
    'no_wo' => '',
    'tanggal' => '',
    'plant' => '',
    'lokasi' => '',
    'bulan' => '',
    'nama' => '',
    'equipment' => '',
    'seal_coupling' => '',
    'shaft' => '',
    'bolt_mounting' => '',
    'balancing' => '',
    'good' => '',
    'not_good' => '',
    'perawatan_part' => '',
    'penggantian_part' => '',
    'jumlah_part' => '',
    'remaks' => ''
];

$error = '';

// Process form if submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verify CSRF token
    if (!isset($_POST['csrf_token'])) {
        $error = "CSRF token missing!";
    } elseif (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Invalid CSRF token!";
    } else {
        // Sanitize and validate input
        $requiredFields = ['no_wo', 'tanggal', 'plant', 'lokasi', 'bulan', 'nama'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                $error = "Field $field harus diisi!";
                break;
            }
        }

        if (!$error) {
            // Store form data for persistence
            foreach ($formData as $key => $value) {
                if (isset($_POST[$key])) {
                    $formData[$key] = mysqli_real_escape_string($conn, $_POST[$key]);
                }
            }

            // Begin transaction
            $conn->begin_transaction();

            try {
                // Insert date
                $sql_date = "INSERT INTO date (tanggal) VALUES (?)";
                $stmt_date = $conn->prepare($sql_date);
                $stmt_date->bind_param("s", $formData['tanggal']);
                $stmt_date->execute();
                $id_date = $stmt_date->insert_id;
                $stmt_date->close();

                // Insert work order
                $sql_no_wo = "INSERT INTO no_wo (nomer, id_date) VALUES (?, ?)";
                $stmt_no_wo = $conn->prepare($sql_no_wo);
                $stmt_no_wo->bind_param("si", $formData['no_wo'], $id_date);
                $stmt_no_wo->execute();
                $stmt_no_wo->close();

                // Insert plant
                $sql_plant = "INSERT INTO plant (nama, id_date) VALUES (?, ?)";
                $stmt_plant = $conn->prepare($sql_plant);
                $stmt_plant->bind_param("si", $formData['plant'], $id_date);
                $stmt_plant->execute();
                $stmt_plant->close();

                // Insert location
                $sql_lokasi = "INSERT INTO lokasi (nama, id_date) VALUES (?, ?)";
                $stmt_lokasi = $conn->prepare($sql_lokasi);
                $stmt_lokasi->bind_param("si", $formData['lokasi'], $id_date);
                $stmt_lokasi->execute();
                $stmt_lokasi->close();

                // Insert frequensi
                $sql_frequensi = "INSERT INTO frequensi (bulan, nama, id_date) VALUES (?, ?, ?)";
                $stmt_frequensi = $conn->prepare($sql_frequensi);
                $stmt_frequensi->bind_param("ssi", $formData['bulan'], $formData['nama'], $id_date);
                $stmt_frequensi->execute();
                $stmt_frequensi->close();

                // Insert tune up data
                $sql_tuneup = "INSERT INTO pump_tuneup 
                    (equipment, seal_coupling, shaft, bolt_mounting, balancing, good, not_good, 
                     perawatan_part, penggantian_part, jumlah_part, remaks, id_date) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt_tuneup = $conn->prepare($sql_tuneup);
                $stmt_tuneup->bind_param(
                    "sssssssssssi", 
                    $formData['equipment'],
                    $formData['seal_coupling'],
                    $formData['shaft'],
                    $formData['bolt_mounting'],
                    $formData['balancing'],
                    $formData['good'],
                    $formData['not_good'],
                    $formData['perawatan_part'],
                    $formData['penggantian_part'],
                    $formData['jumlah_part'],
                    $formData['remaks'],
                    $id_date
                );
                $stmt_tuneup->execute();
                $stmt_tuneup->close();

                // Commit transaction
                $conn->commit();

                // Redirect on success
                $_SESSION['success_message'] = 'Data berhasil diinput!';
                header("Location: pumptuneup.php");
                exit();
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Form Pump Tune Up</title>
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
          <i class="fas fa-pump-soap"></i>
        </div>
        <h1>Form Pump Tune Up</h1>
        <p>Sistem Manajemen Maintenance Terintegrasi</p>
      </div>
      
      <div class="form-content">
        <!-- Alert messages would be displayed here -->
        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
          <i class="fas fa-check-circle"></i>
          <?php echo $_SESSION['success_message']; 
          unset($_SESSION['success_message']); ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
        <div class="alert alert-error">
          <i class="fas fa-exclamation-triangle"></i>
          <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" id="tuneupForm">
          <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
          
          <div class="section">
            <div class="section-title">
              <i class="fas fa-info-circle"></i>
              Informasi Dasar
            </div>
            
            <div class="form-grid">
              <div class="form-group">
                <label for="no_wo">No. Work Order</label>
                <input type="text" id="no_wo" name="no_wo" class="form-control" 
                       value="<?php echo htmlspecialchars($formData['no_wo']); ?>" 
                       placeholder="Masukkan nomor WO" required>
              </div>
              
              <div class="form-group">
                <label for="tanggal">Tanggal Inspeksi</label>
                <input type="date" id="tanggal" name="tanggal" class="form-control" 
                       value="<?php echo htmlspecialchars($formData['tanggal']); ?>" required>
              </div>
              
              <div class="form-group">
                <label for="plant">Plant</label>
                <input type="text" id="plant" name="plant" class="form-control" 
                       value="<?php echo htmlspecialchars($formData['plant']); ?>" 
                       placeholder="Masukkan nama plant" required>
              </div>
              
              <div class="form-group">
                <label for="lokasi">Lokasi</label>
                <input type="text" id="lokasi" name="lokasi" class="form-control" 
                       value="<?php echo htmlspecialchars($formData['lokasi']); ?>" 
                       placeholder="Masukkan lokasi" required>
              </div>
              
              <div class="form-group full-width">
                <label><i class="fas fa-clock"></i> Frequensi</label>
                <div class="frequency-container">
                  <input list="bulan" name="bulan" class="form-control" 
                         value="<?php echo htmlspecialchars($formData['bulan']); ?>" 
                         placeholder="Pilih Bulan" required>
                  <datalist id="bulan">
                    <option value="1 bulan"></option>
                    <option value="3 bulan"></option>
                    <option value="6 bulan"></option>
                    <option value="12 bulan"></option>
                  </datalist>
                  
                  <input list="nama" name="nama" class="form-control" 
                         value="<?php echo htmlspecialchars($formData['nama']); ?>" 
                         placeholder="Pilih Nama" required>
                  <datalist id="nama">
                    <option value="2 Tahun"></option>
                    <option value="3 Tahun"></option>
                    <option value="5 tahun"></option>
                  </datalist>
                </div>
              </div>
              
              <div class="form-group full-width">
                <label for="equipment">Equipment</label>
                <input type="text" id="equipment" name="equipment" class="form-control" 
                       value="<?php echo htmlspecialchars($formData['equipment']); ?>" 
                       placeholder="Nama peralatan" required>
              </div>
            </div>
          </div>

          <div class="section">
            <div class="section-title">
              <i class="fas fa-check-circle"></i>
              Detail Tune Up
            </div>
            
            <div class="form-grid">
              <div class="form-group">
                <label for="seal_coupling">Seal Coupling</label>
                <input type="text" id="seal_coupling" name="seal_coupling" class="form-control" 
                       value="<?php echo htmlspecialchars($formData['seal_coupling']); ?>" 
                       placeholder="Kondisi seal coupling">
              </div>
              
              <div class="form-group">
                <label for="shaft">Shaft</label>
                <input type="text" id="shaft" name="shaft" class="form-control" 
                       value="<?php echo htmlspecialchars($formData['shaft']); ?>" 
                       placeholder="Kondisi shaft">
              </div>
              
              <div class="form-group">
                <label for="bolt_mounting">Bolt Mounting</label>
                <input type="text" id="bolt_mounting" name="bolt_mounting" class="form-control" 
                       value="<?php echo htmlspecialchars($formData['bolt_mounting']); ?>" 
                       placeholder="Kondisi bolt mounting">
              </div>
              
              <div class="form-group">
                <label for="balancing">Balancing</label>
                <input type="text" id="balancing" name="balancing" class="form-control" 
                       value="<?php echo htmlspecialchars($formData['balancing']); ?>" 
                       placeholder="Kondisi balancing">
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
                  <input type="checkbox" name="good" value="1" <?php echo $formData['good'] ? 'checked' : ''; ?>>
                  <span>Good</span>
                </label>
                <label>
                  <input type="checkbox" name="not_good" value="1" <?php echo $formData['not_good'] ? 'checked' : ''; ?>>
                  <span>Not Good</span>
                </label>
              </div>
            </div>
            
            <div class="form-grid">
              <div class="form-group">
                <label for="perawatan_part">Perawatan</label>
                <input type="text" id="perawatan_part" name="perawatan_part" class="form-control" 
                       value="<?php echo htmlspecialchars($formData['perawatan_part']); ?>" 
                       placeholder="Detail perawatan">
              </div>
              
              <div class="form-group">
                <label for="penggantian_part">Penggantian</label>
                <input type="text" id="penggantian_part" name="penggantian_part" class="form-control" 
                       value="<?php echo htmlspecialchars($formData['penggantian_part']); ?>" 
                       placeholder="Detail penggantian">
              </div>
              
              <div class="form-group">
                <label for="jumlah_part">Jumlah</label>
                <input type="text" id="jumlah_part" name="jumlah_part" class="form-control" 
                       value="<?php echo htmlspecialchars($formData['jumlah_part']); ?>" 
                       placeholder="Jumlah">
              </div>
              
              <div class="form-group full-width">
                <label for="remaks">Remarks</label>
                <textarea id="remaks" name="remaks" class="form-control" rows="4" 
                          placeholder="Catatan tambahan"><?php echo htmlspecialchars($formData['remaks']); ?></textarea>
              </div>
            </div>
          </div>

          <div class="button-container">
            <button type="submit" class="btn btn-submit">
              <i class="fas fa-save"></i>
              Simpan Data
            </button>
            <button type="button" class="btn btn-back" onclick="window.location.href='pumptuneup.php'">
              <i class="fas fa-arrow-left"></i>
              Kembali
            </button>
          </div>
        </form>
      </div>
      
      <div class="footer">
        <p>Form Pump Tune Up &copy; 2024 | Powered by Advanced Maintenance System</p>
      </div>
    </div>
  </div>

  <script>
    // Set today's date as default if empty
    if (!document.getElementById('tanggal').value) {
      document.getElementById('tanggal').value = new Date().toISOString().split('T')[0];
    }
    
    // Form validation and enhancement
    document.getElementById('tuneupForm').addEventListener('submit', function(e) {
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
      const savedValue = sessionStorage.getItem('tuneup_form_' + input.name);
      if (savedValue && input.type !== 'date') {
        input.value = savedValue;
      }
      
      // Save on change
      input.addEventListener('input', function() {
        sessionStorage.setItem('tuneup_form_' + input.name, input.value);
      });
    });
    
    // Clear draft on successful submission
    window.addEventListener('beforeunload', function() {
      if (document.querySelector('.alert-success')) {
        formInputs.forEach(input => {
          sessionStorage.removeItem('tuneup_form_' + input.name);
        });
      }
    });
  </script>
</body>
</html>