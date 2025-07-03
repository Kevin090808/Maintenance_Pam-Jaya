<?php
// Koneksi ke database - definisikan sekali saja
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'maintenance';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Variabel untuk menyimpan data yang diambil
$selected_date = false;
$id_date = isset($_GET['id_date']) ? (int)$_GET['id_date'] : null;

// Jika id_date tersedia, maka ambil data related
if ($id_date) {
    $selected_date = true;
    $sql_temp = "SELECT * FROM temperatur WHERE id_date = ?";
    $sql_plant = "SELECT * FROM plant WHERE id_date = ?";
    $sql_location = "SELECT * FROM lokasi WHERE id_date = ?";
    $sql_no_wo = "SELECT * FROM no_wo WHERE id_date = ?";
    $sql_frequensi = "SELECT * FROM frequensi WHERE id_date = ?";
    $sql_date = "SELECT * FROM date WHERE id_date = ?";

    // Prepare dan execute statements
    $stmt_plant = $conn->prepare($sql_plant);
    $stmt_plant->bind_param("i", $id_date);
    $stmt_plant->execute();
    $result_plant = $stmt_plant->get_result();

    $stmt_location = $conn->prepare($sql_location);
    $stmt_location->bind_param("i", $id_date);
    $stmt_location->execute();
    $result_location = $stmt_location->get_result();

    $stmt_no_wo = $conn->prepare($sql_no_wo);
    $stmt_no_wo->bind_param("i", $id_date);
    $stmt_no_wo->execute();
    $result_no_wo = $stmt_no_wo->get_result();

    $stmt_frequensi = $conn->prepare($sql_frequensi);
    $stmt_frequensi->bind_param("i", $id_date);
    $stmt_frequensi->execute();
    $result_frequensi = $stmt_frequensi->get_result();

    $stmt_date = $conn->prepare($sql_date);
    $stmt_date->bind_param("i", $id_date);
    $stmt_date->execute();
    $result_date = $stmt_date->get_result();
}

// Proses form jika data dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $no_wo = isset($_POST['no_wo']) ? mysqli_real_escape_string($conn, $_POST['no_wo']) : '';
    $tanggal = isset($_POST['tanggal']) ? mysqli_real_escape_string($conn, $_POST['tanggal']) : '';
    $plant = isset($_POST['plant']) ? mysqli_real_escape_string($conn, $_POST['plant']) : '';
    $lokasi = isset($_POST['lokasi']) ? mysqli_real_escape_string($conn, $_POST['lokasi']) : '';
    $bulan = isset($_POST['bulan']) ? mysqli_real_escape_string($conn, $_POST['bulan']) : '';
    $nama = isset($_POST['nama']) ? mysqli_real_escape_string($conn, $_POST['nama']) : '';
    
    // Fix: Ensure these are strings before using mysqli_real_escape_string
    $pk = isset($_POST['pk']) ? mysqli_real_escape_string($conn, (is_array($_POST['pk']) ? implode(',', $_POST['pk']) : $_POST['pk'])) : '';
    $merek = isset($_POST['merek']) ? mysqli_real_escape_string($conn, (is_array($_POST['merek']) ? implode(',', $_POST['merek']) : $_POST['merek'])) : '';
    $tipe = isset($_POST['tipe']) ? mysqli_real_escape_string($conn, (is_array($_POST['tipe']) ? implode(',', $_POST['tipe']) : $_POST['tipe'])) : '';
    $jenis_freon = isset($_POST['jenis_freon']) ? mysqli_real_escape_string($conn, (is_array($_POST['jenis_freon']) ? implode(',', $_POST['jenis_freon']) : $_POST['jenis_freon'])) : '';
    
    // Data pembersihan
    $filter = isset($_POST['filter']) ? '✔' : 'X';
    $indoor = isset($_POST['indoor']) ? '✔' : 'X';
    $outdoor = isset($_POST['outdoor']) ? '✔' : 'X';
    
  // Data tambahan
    $nilai_ampere = isset($_POST['nilai_ampere']) ? mysqli_real_escape_string($conn, (is_array($_POST['nilai_ampere']) ? implode(',', $_POST['nilai_ampere']) : $_POST['nilai_ampere'])) : '';
    $tambah_freon = isset($_POST['tambah_freon']) ? '✔' : 'X'; // Diubah dari '✓' ke '✔' untuk konsistensi
    $catatan = isset($_POST['catatan']) ? mysqli_real_escape_string($conn, (is_array($_POST['catatan']) ? implode(',', $_POST['catatan']) : $_POST['catatan'])) : '';
        // Mulai transaksi
    $conn->begin_transaction();

    try {
        // Insert data ke tabel date
        $sql_date = "INSERT INTO date (tanggal) VALUES (?)";
        $stmt_date = $conn->prepare($sql_date);
        if (!$stmt_date) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt_date->bind_param("s", $tanggal);
        if (!$stmt_date->execute()) {
            throw new Exception("Execute failed: " . $stmt_date->error);
        }
        $id_date = $conn->insert_id; // Ambil ID yang baru saja di-insert

        // Insert data ke tabel no_wo
        $sql_no_wo = "INSERT INTO no_wo (nomer, id_date) VALUES (?, ?)";
        $stmt_no_wo = $conn->prepare($sql_no_wo);
        if (!$stmt_no_wo) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt_no_wo->bind_param("si", $no_wo, $id_date);
        if (!$stmt_no_wo->execute()) {
            throw new Exception("Execute failed: " . $stmt_no_wo->error);
        }

        // Insert data ke tabel plant
        $sql_plant = "INSERT INTO plant (nama, id_date) VALUES (?, ?)";
        $stmt_plant = $conn->prepare($sql_plant);
        if (!$stmt_plant) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt_plant->bind_param("si", $plant, $id_date);
        if (!$stmt_plant->execute()) {
            throw new Exception("Execute failed: " . $stmt_plant->error);
        }

        // Insert data ke tabel lokasi
        $sql_lokasi = "INSERT INTO lokasi (nama, id_date) VALUES (?, ?)";
        $stmt_lokasi = $conn->prepare($sql_lokasi);
        if (!$stmt_lokasi) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt_lokasi->bind_param("si", $lokasi, $id_date);
        if (!$stmt_lokasi->execute()) {
            throw new Exception("Execute failed: " . $stmt_lokasi->error);
        }

        // Insert data ke tabel frequensi
        $sql_frequensi = "INSERT INTO frequensi (bulan, nama, id_date) VALUES (?, ?, ?)";
        $stmt_frequensi = $conn->prepare($sql_frequensi);
        if (!$stmt_frequensi) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt_frequensi->bind_param("ssi", $bulan, $nama, $id_date);
        if (!$stmt_frequensi->execute()) {
            throw new Exception("Execute failed: " . $stmt_frequensi->error);
        }

        // Insert data ke tabel cleaning_ac
        $sql_cleaning = "INSERT INTO cleaning_ac (pk, merek, tipe, jenis_freon, filter, indoor, outdoor, nilai_ampere, tambah_freon, catatan, id_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_cleaning = $conn->prepare($sql_cleaning);
        if (!$stmt_cleaning) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt_cleaning->bind_param("ssssssssssi", $pk, $merek, $tipe, $jenis_freon, $filter, $indoor, $outdoor, $nilai_ampere, $tambah_freon, $catatan, $id_date);
        
        if ($stmt_cleaning->execute()) {
            $conn->commit();
            $success_message = "Data berhasil disimpan";
        } else {
            throw new Exception("Execute failed: " . $stmt_cleaning->error);
        }
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Maintenance AC</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #64748b;
            --success: #10b981;
            --light: #f8fafc;
            --dark: #1e293b;
            --border-radius: 0.5rem;
            --shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        body {
            background-color: #f1f5f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark);
            margin: 0;
            padding: 20px;
        }
        
        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 2rem;
            padding: 1.5rem 0;
            border-radius: var(--border-radius);
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
            box-shadow: var(--shadow);
        }
        
        .page-header h1 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .page-header p {
            font-size: 1rem;
            opacity: 0.9;
            margin: 0;
        }
        
        .form-container {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            overflow: hidden;
        }
        
        .section-header {
            background-color: var(--primary);
            color: white;
            font-weight: 600;
            font-size: 1.2rem;
            padding: 1rem 1.5rem;
            margin: 0;
        }
        
        .section-header i {
            margin-right: 0.5rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        td {
            padding: 12px 15px;
            vertical-align: middle;
        }
        
        .label-cell {
            width: 30%;
            font-weight: 500;
            color: var(--dark);
            background-color: rgba(241, 245, 249, 0.5);
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            position: relative;
        }
        
        .label-cell i {
            margin-right: 10px;
            color: var(--primary);
        }
        
        .input-cell {
            width: 70%;
            border-bottom: 1px solid #e2e8f0;
        }
        
        input[type="text"],
        input[type="date"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        
        input[type="text"]:focus,
        input[type="date"]:focus,
        input[type="number"]:focus,
        select:focus,
        textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.25);
            outline: none;
        }
        
        .checkbox-container {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .checkbox-container:last-child {
            margin-bottom: 0;
        }
        
        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-right: 10px;
            cursor: pointer;
            accent-color: var(--primary);
        }
        
        .checkbox-label {
            font-size: 1rem;
            cursor: pointer;
        }
        
        .section-subtitle {
            color: var(--primary);
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0 0 15px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .cleaning-section {
            background-color: #f8fafc;
            border-radius: var(--border-radius);
            padding: 15px;
            margin: 10px 0;
            border-left: 4px solid var(--primary);
        }
        
        .button-container {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 20px;
        }
        
        .btn {
            padding: 12px 24px;
            font-weight: 500;
            font-size: 1rem;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .btn-secondary {
            background-color: var(--secondary);
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #475569;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: var(--border-radius);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .alert-success {
            background-color: #ecfdf5;
            color: #065f46;
            border-left: 4px solid var(--success);
        }
        
        .alert-danger {
            background-color: #fef2f2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        
        .required-field::after {
            content: "*";
            color: #ef4444;
            margin-left: 4px;
        }

      .frequency-container {
    display: flex;
    gap: 15px;
    align-items: center;
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #cbd5e1;
    border-radius: var(--border-radius);
    font-size: 1rem;
    transition: all 0.3s ease;
    box-sizing: border-box;
    background-color: white;
}

.frequency-container input[list] {
    flex: 1;
    padding: 10px 12px;
    border: 1px solid #cbd5e1;
    border-radius: var(--border-radius);
    font-size: 1rem;
    transition: all 0.3s ease;
    background-color: white;
}

.frequency-container input[list]:focus {
    border-color: var(--primary);
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

        .babi {
            background-color: #4CAF50;
            color: white;
            padding: 14px 20px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 8px;
            transition: background-color 0.3s ease, transform 0.3s ease;
            margin: 0 10px;
            left: 40px;        /* Jarak dari sisi kiri layar */
            bottom: 2px;      /* Jarak dari bawah, bisa juga gunakan top */
            z-index: 1000;     /* Supaya tidak ketutup elemen lain */   
            position: fixed;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-snowflake"></i> Form Maintenance AC</h1>
            <p>Data pemeliharaan dan perawatan unit AC</p>
        </div>

        <?php if(isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-container">
                <h3 class="section-header"><i class="fas fa-clipboard-list"></i> Informasi Dasar</h3>
                <table>
                    <tr>
                        <td class="label-cell required-field">
                            <i class="fas fa-hashtag"></i> No. WO
                        </td>
                        <td class="input-cell">
                            <input type="text" id="no_wo" name="no_wo" required>
                        </td>
                    </tr>
                    <tr>
                        <td class="label-cell required-field">
                            <i class="fas fa-calendar-alt"></i> Tanggal
                        </td>
                        <td class="input-cell">
                            <input type="date" id="tanggal" name="tanggal" required>
                        </td>
                    </tr>
                    <tr>
                        <td class="label-cell required-field">
                            <i class="fas fa-industry"></i> Plant
                        </td>
                        <td class="input-cell">
                            <input type="text" id="plant" name="plant" required>
                        </td>
                    </tr>
                    <tr>
                        <td class="label-cell required-field">
                            <i class="fas fa-map-marker-alt"></i> Lokasi
                        </td>
                        <td class="input-cell">
                            <select id="lokasi" name="lokasi" required>
                                <option value="">Pilih Lokasi</option>
                                <option value="location1">Location 1</option>
                                <option value="location2">Location 2</option>
                                <option value="location3">Location 3</option>
                                <option value="location4">Location 4</option>
                                <option value="location5">Location 5</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="label-cell">
                            <i class="fas fa-clock"></i> Frequensi
                        </td>
                        <td class="input-cell">
                            <div class="frequency-container">
                                <input list="bulan" name="bulan" placeholder="Pilih Bulan">
                                <datalist id="bulan">
                                    <option value="1 bulan"></option>
                                    <option value="3 bulan"></option>
                                    <option value="6 bulan"></option>
                                    <option value="12 bulan"></option>
                                </datalist>
                                
                                <input list="nama" name="nama" placeholder="Pilih Nama">
                                <datalist id="nama">
                                    <option value="2 Tahun"></option>
                                    <option value="3 Tahun"></option>
                                    <option value="5 tahun"></option>
                                </datalist>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="form-container">
                <h3 class="section-header"><i class="fas fa-toolbox"></i> Spesifikasi Unit AC</h3>
                <table>
                    <tr>
                        <td class="label-cell required-field">
                            <i class="fas fa-bolt"></i> PK
                        </td>
                        <td class="input-cell">
                            <input type="text" id="pk" name="pk" required>
                        </td>
                    </tr>
                    <tr>
                        <td class="label-cell required-field">
                            <i class="fas fa-tag"></i> Merek
                        </td>
                        <td class="input-cell">
                            <input type="text" id="merek" name="merek" required>
                        </td>
                    </tr>
                    <tr>
                        <td class="label-cell required-field">
                            <i class="fas fa-tools"></i> Tipe
                        </td>
                        <td class="input-cell">
                            <input type="text" id="tipe" name="tipe" required>
                        </td>
                    </tr>
                    <tr>
                        <td class="label-cell required-field">
                            <i class="fas fa-temperature-low"></i> Jenis Freon
                        </td>
                        <td class="input-cell">
                            <select id="jenis_freon" name="jenis_freon" required>
                                <option value="">Pilih Jenis Freon</option>
                                <option value="R22">R22</option>
                                <option value="R32">R32</option>
                                <option value="R410">R410</option>
                                <option value="R290">R290</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="label-cell">
                            <i class="fas fa-broom"></i> Pembersihan
                        </td>
                        <td class="input-cell">
                            <div class="cleaning-section">
                                <div class="checkbox-container">
                                    <input type="checkbox" id="filter" name="filter" value="1">
                                    <label class="checkbox-label" for="filter">
                                        <i class="fas fa-filter"></i> Filter
                                    </label>
                                </div>
                                <div class="checkbox-container">
                                    <input type="checkbox" id="indoor" name="indoor" value="1">
                                    <label class="checkbox-label" for="indoor">
                                        <i class="fas fa-home"></i> Indoor
                                    </label>
                                </div>
                                <div class="checkbox-container">
                                    <input type="checkbox" id="outdoor" name="outdoor" value="1">
                                    <label class="checkbox-label" for="outdoor">
                                        <i class="fas fa-wind"></i> Outdoor
                                    </label>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="label-cell required-field">
                            <i class="fas fa-tachometer-alt"></i> Nilai Ampere
                        </td>
                        <td class="input-cell">
                            <input type="number" step="0.01" id="nilai_ampere" name="nilai_ampere" required>
                        </td>
                    </tr>
                    <tr>
                        <td class="label-cell">
                            <i class="fas fa-plus-circle"></i> Tambah Freon
                        </td>
                        <td class="input-cell">
                            <div class="checkbox-container">
                                <input type="checkbox" id="tambah_freon" name="tambah_freon" value="1">
                                <label class="checkbox-label" for="tambah_freon">
                                    <i class="fas fa-wind"></i> YA
                                </label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="label-cell">
                            <i class="fas fa-sticky-note"></i> Catatan
                        </td>
                        <td class="input-cell">
                            <textarea id="catatan" name="catatan" rows="3"></textarea>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="button-container">
                <button type="reset" class="btn btn-secondary">
                    <i class="fas fa-undo"></i> Reset
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Data
                </button>
            </div>
        </form>
    </div>

    <div class="babi">
    <button class="babi" type="button" onclick="window.location.href='cleaning_ac.php'">Kembali</button>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set default date to today
            const dateField = document.getElementById('tanggal');
            if (dateField && !dateField.value) {
                const today = new Date();
                const yyyy = today.getFullYear();
                let mm = today.getMonth() + 1;
                let dd = today.getDate();
                
                if (dd < 10) dd = '0' + dd;
                if (mm < 10) mm = '0' + mm;
                
                dateField.value = ${yyyy}-${mm}-${dd};
            }
            
            // Highlight required fields
            const requiredFields = document.querySelectorAll('input[required], select[required]');
            requiredFields.forEach(field => {
                field.addEventListener('invalid', function() {
                    this.classList.add('field-error');
                });
                
                field.addEventListener('input', function() {
                    this.classList.remove('field-error');
                });
            });
        });
    </script>
</body>
</html>
