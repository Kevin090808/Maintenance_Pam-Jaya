<?php
$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "maintenance";

// Buat koneksi
$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Proses form jika data dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form dan lakukan validasi
    $no_wo = isset($_POST['no_wo']) ? mysqli_real_escape_string($conn, $_POST['no_wo']) : '';
    $tanggal = isset($_POST['date']) ? mysqli_real_escape_string($conn, $_POST['date']) : '';
    $plant = isset($_POST['plant']) ? mysqli_real_escape_string($conn, $_POST['plant']) : '';
    $lokasi = isset($_POST['location']) ? mysqli_real_escape_string($conn, $_POST['location']) : '';
    
    // PERBAIKAN: Ambil data frequensi dengan nama field yang benar
    $bulan = isset($_POST['bulan']) ? mysqli_real_escape_string($conn, $_POST['bulan']) : '';
    $nama = isset($_POST['nama']) ? mysqli_real_escape_string($conn, $_POST['nama']) : '';
    
    $tanggal_pengecekan = isset($_POST['tanggal_pengecekan']) ? mysqli_real_escape_string($conn, $_POST['tanggal_pengecekan']) : '';
    $lokasi = isset($_POST['lokasi']) ? mysqli_real_escape_string($conn, $_POST['lokasi']) : '';
    $no_urut = isset($_POST['no_urut']) ? mysqli_real_escape_string($conn, $_POST['no_urut']) : '';
    $nama_equipment = isset($_POST['nama_equipment']) ? mysqli_real_escape_string($conn, $_POST['nama_equipment']) : '';
    $tempeatur = isset($_POST['tempeatur']) ? mysqli_real_escape_string($conn, $_POST['tempeatur']) : '';
    $keterangan = isset($_POST['keterangan']) ? mysqli_real_escape_string($conn, $_POST['keterangan']) : '';

    // PERBAIKAN: Validasi input dengan field frequensi yang benar
    if (empty($no_wo) || empty($tanggal) || empty($plant) || empty($lokasi) || 
        empty($bulan) || empty($nama) || empty($tanggal_pengecekan) || empty($lokasi) || 
        empty($no_urut) || empty($nama_equipment) || empty($tempeatur) || empty($keterangan)) {
        echo "<script>alert('Semua field harus diisi!');</script>";
    } else {
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
            $id_date = $conn->insert_id;

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

            // PERBAIKAN: Insert data ke tabel frequensi dengan penanganan yang benar
            $sql_frequensi = "INSERT INTO frequensi (bulan, nama, id_date) VALUES (?, ?, ?)";
            $stmt_frequensi = $conn->prepare($sql_frequensi);
            if (!$stmt_frequensi) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt_frequensi->bind_param("ssi", $bulan, $nama, $id_date);
            if (!$stmt_frequensi->execute()) {
                throw new Exception("Execute failed: " . $stmt_frequensi->error);
            }

            // Insert data ke tabel preventif_themograph
            $sql_inspeksi_ac = "INSERT INTO preventif_thermograph (tanggal_pengecekan, lokasi, no_urut, nama_equipment, temperatur, keterangan, id_date) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt_preventif_thermograph = $conn->prepare($sql_inspeksi_ac);
            if (!$stmt_preventif_thermograph) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt_preventif_thermograph->bind_param("ssssssi", $tanggal_pengecekan, $lokasi, $no_urut, $nama_equipment, $tempeatur, $keterangan, $id_date);
            if (!$stmt_preventif_thermograph->execute()) {
                throw new Exception("Execute failed: " . $stmt_preventif_thermograph->error);
            }

            // Commit transaksi
            $conn->commit();

            // Notifikasi berhasil dan redirect
            echo "<script>
                    alert('Data berhasil diinput!');
                    window.location.href = 'preventif_thermograph.php';
                  </script>";
        } catch (Exception $e) {
            // Rollback transaksi jika terjadi error
            $conn->rollback();
            echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
        } finally {
            // Tutup statement
            if (isset($stmt_date)) $stmt_date->close();
            if (isset($stmt_no_wo)) $stmt_no_wo->close();
            if (isset($stmt_plant)) $stmt_plant->close();
            if (isset($stmt_lokasi)) $stmt_lokasi->close();
            if (isset($stmt_frequensi)) $stmt_frequensi->close();
            if (isset($stmt_preventif_thermograph)) $stmt_preventif_thermograph->close();
        }
    }
}

$conn->close();
?>

<!-- Bagian HTML dengan perbaikan form frequensi -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Inspeksi AC</title>
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

td input[type="radio"] {
    margin-right: 10px;
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

/* Condition Radio Button Styling */
input[type="radio"] {
    margin-right: 10px;
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
    </style>
</head>
<body>
    <!-- HTML header sama seperti sebelumnya -->
    
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
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
            <!-- PERBAIKAN: Form frequensi yang benar -->
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
        
        <!-- Tabel inspeksi AC sama seperti sebelumnya -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal Pengecekan</th>
                        <th>Lokasi</th>
                        <th>No Urut Thermograph</th>
                        <th>Nama Equipment</th>
                        <th>Tempeatur</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><input type="date" name="tanggal_pengecekan" required></td>
                        <td><input type="text" name="lokasi" required></td>
                        <td><input type="text" name="no_urut" required></td>
                        <td><input type="text" name="nama_equipment" required></td>
                        <td><input type="text" name="tempeatur" required></td>
                        <td><input type="text" name="keterangan" required></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <center><button type="submit">Submit</button></center>
    </form>
    
    <div class="button-container">
        <button onclick="window.location.href='preventif_thermograph.php'">Kembali</button>
    </div>
<footer>
    <div class="footer-bottom">
        <p>&copy; 2025 Perumda PAM Jaya. All Rights Reserved.</p>
    </div>
</footer>
</body>
</html>
<script>
     const requiredFields = document.querySelectorAll('input[required], select[required]');
            requiredFields.forEach(field => {
                field.addEventListener('invalid', function() {
                    this.classList.add('field-error');
                });
                
                field.addEventListener('input', function() {
                    this.classList.remove('field-error');
                });
            });
</script>