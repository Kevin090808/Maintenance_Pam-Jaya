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

// Enable error reporting untuk debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Proses form jika data dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Debug: Tampilkan semua data POST yang diterima
        error_log("POST Data: " . print_r($_POST, true));
        
        // Ambil data dari form dan lakukan validasi
        $no_wo = isset($_POST['woNumber']) ? trim(mysqli_real_escape_string($conn, $_POST['woNumber'])) : '';
        $tanggal = isset($_POST['inspectionDate']) ? trim(mysqli_real_escape_string($conn, $_POST['inspectionDate'])) : '';
        $plant = isset($_POST['plantName']) ? trim(mysqli_real_escape_string($conn, $_POST['plantName'])) : '';
        $lokasi = isset($_POST['location']) ? trim(mysqli_real_escape_string($conn, $_POST['location'])) : '';
        $bulan = isset($_POST['bulan']) ? trim(mysqli_real_escape_string($conn, $_POST['bulan'])) : '';
        $nama = isset($_POST['nama']) ? trim(mysqli_real_escape_string($conn, $_POST['nama'])) : '';
        
        // Debug: Log data yang akan diinput
        error_log("Data yang akan diinput:");
        error_log("no_wo: " . $no_wo);
        error_log("tanggal: " . $tanggal);
        error_log("plant: " . $plant);
        error_log("lokasi: " . $lokasi);
        error_log("bulan: " . $bulan);
        error_log("nama: " . $nama);
        
        // Validasi input utama
        if (empty($no_wo) || empty($tanggal) || empty($plant) || empty($lokasi) || 
            empty($bulan) || empty($nama)) {
            
            $missing_fields = [];
            if (empty($no_wo)) $missing_fields[] = 'WO Number';
            if (empty($tanggal)) $missing_fields[] = 'Tanggal';
            if (empty($plant)) $missing_fields[] = 'Plant Name';
            if (empty($lokasi)) $missing_fields[] = 'Lokasi';
            if (empty($bulan)) $missing_fields[] = 'Bulan';
            if (empty($nama)) $missing_fields[] = 'Nama';
            
            throw new Exception('Field yang kosong: ' . implode(', ', $missing_fields));
        }

        // Validasi format tanggal
        if (!DateTime::createFromFormat('Y-m-d', $tanggal)) {
            throw new Exception('Format tanggal tidak valid. Gunakan format YYYY-MM-DD');
        }

        // Mulai transaksi dengan autocommit false
        $conn->autocommit(FALSE);

        // Insert data ke tabel date
        $sql_date = "INSERT INTO date (tanggal) VALUES (?)";
        $stmt_date = $conn->prepare($sql_date);
        if (!$stmt_date) {
            throw new Exception("Prepare date failed: " . $conn->error);
        }
        $stmt_date->bind_param("s", $tanggal);
        if (!$stmt_date->execute()) {
            throw new Exception("Execute date failed: " . $stmt_date->error);
        }
        $id_date = $conn->insert_id;
        
        if ($id_date == 0) {
            throw new Exception("Failed to get insert ID for date table");
        }
        
        error_log("Inserted date with ID: " . $id_date);

        // Insert data ke tabel no_wo
        $sql_no_wo = "INSERT INTO no_wo (nomer, id_date) VALUES (?, ?)";
        $stmt_no_wo = $conn->prepare($sql_no_wo);
        if (!$stmt_no_wo) {
            throw new Exception("Prepare no_wo failed: " . $conn->error);
        }
        $stmt_no_wo->bind_param("si", $no_wo, $id_date);
        if (!$stmt_no_wo->execute()) {
            throw new Exception("Execute no_wo failed: " . $stmt_no_wo->error);
        }
        error_log("Inserted no_wo successfully");

        // Insert data ke tabel plant
        $sql_plant = "INSERT INTO plant (nama, id_date) VALUES (?, ?)";
        $stmt_plant = $conn->prepare($sql_plant);
        if (!$stmt_plant) {
            throw new Exception("Prepare plant failed: " . $conn->error);
        }
        $stmt_plant->bind_param("si", $plant, $id_date);
        if (!$stmt_plant->execute()) {
            throw new Exception("Execute plant failed: " . $stmt_plant->error);
        }
        error_log("Inserted plant successfully");

        // Insert data ke tabel lokasi
        $sql_lokasi = "INSERT INTO lokasi (nama, id_date) VALUES (?, ?)";
        $stmt_lokasi = $conn->prepare($sql_lokasi);
        if (!$stmt_lokasi) {
            throw new Exception("Prepare lokasi failed: " . $conn->error);
        }
        $stmt_lokasi->bind_param("si", $lokasi, $id_date);
        if (!$stmt_lokasi->execute()) {
            throw new Exception("Execute lokasi failed: " . $stmt_lokasi->error);
        }
        error_log("Inserted lokasi successfully");

        // Insert data ke tabel frequensi
        $sql_frequensi = "INSERT INTO frequensi (bulan, nama, id_date) VALUES (?, ?, ?)";
        $stmt_frequensi = $conn->prepare($sql_frequensi);
        if (!$stmt_frequensi) {
            throw new Exception("Prepare frequensi failed: " . $conn->error);
        }
        $stmt_frequensi->bind_param("ssi", $bulan, $nama, $id_date);
        if (!$stmt_frequensi->execute()) {
            throw new Exception("Execute frequensi failed: " . $stmt_frequensi->error);
        }
        error_log("Inserted frequensi successfully");

        // Definisi measurements dengan sanitasi data
        $measurements = [
            [   
                'name' => 'Ukur tegangan R - S',
                'standard' => 'Maks 5% dari Vn, Min 10% dari Vn',
                'hasil_1' => isset($_POST['voltage_rs_1']) ? trim($_POST['voltage_rs_1']) : '',
                'hasil_2' => isset($_POST['voltage_rs_2']) ? trim($_POST['voltage_rs_2']) : '',
                'hasil_3' => isset($_POST['voltage_rs_3']) ? trim($_POST['voltage_rs_3']) : '',
                'hasil_4' => isset($_POST['voltage_rs_4']) ? trim($_POST['voltage_rs_4']) : ''
            ],
            [
                'name' => 'Ukur tegangan R - T',
                'standard' => 'Maks 5% dari Vn, Min 10% dari Vn',
                'hasil_1' => isset($_POST['voltage_rt_1']) ? trim($_POST['voltage_rt_1']) : '',
                'hasil_2' => isset($_POST['voltage_rt_2']) ? trim($_POST['voltage_rt_2']) : '',
                'hasil_3' => isset($_POST['voltage_rt_3']) ? trim($_POST['voltage_rt_3']) : '',
                'hasil_4' => isset($_POST['voltage_rt_4']) ? trim($_POST['voltage_rt_4']) : ''
            ],
            [
                'name' => 'Ukur tegangan S - T',
                'standard' => 'Maks 5% dari Vn, Min 10% dari Vn',
                'hasil_1' => isset($_POST['voltage_st_1']) ? trim($_POST['voltage_st_1']) : '',
                'hasil_2' => isset($_POST['voltage_st_2']) ? trim($_POST['voltage_st_2']) : '',
                'hasil_3' => isset($_POST['voltage_st_3']) ? trim($_POST['voltage_st_3']) : '',
                'hasil_4' => isset($_POST['voltage_st_4']) ? trim($_POST['voltage_st_4']) : ''
            ],
            [
                'name' => 'Ukur tegangan R - N',
                'standard' => 'Maks 5% dari Vn, Min 10% dari Vn',
                'hasil_1' => isset($_POST['voltage_rn_1']) ? trim($_POST['voltage_rn_1']) : '',
                'hasil_2' => isset($_POST['voltage_rn_2']) ? trim($_POST['voltage_rn_2']) : '',
                'hasil_3' => isset($_POST['voltage_rn_3']) ? trim($_POST['voltage_rn_3']) : '',
                'hasil_4' => isset($_POST['voltage_rn_4']) ? trim($_POST['voltage_rn_4']) : ''
            ],
            [
                'name' => 'Ukur tegangan S - N',
                'standard' => 'Maks 5% dari Vn, Min 10% dari Vn',
                'hasil_1' => isset($_POST['voltage_sn_1']) ? trim($_POST['voltage_sn_1']) : '',
                'hasil_2' => isset($_POST['voltage_sn_2']) ? trim($_POST['voltage_sn_2']) : '',
                'hasil_3' => isset($_POST['voltage_sn_3']) ? trim($_POST['voltage_sn_3']) : '',
                'hasil_4' => isset($_POST['voltage_sn_4']) ? trim($_POST['voltage_sn_4']) : ''
            ],
            [
                'name' => 'Ukur tegangan T - N',
                'standard' => 'Maks 5% dari Vn, Min 10% dari Vn',
                'hasil_1' => isset($_POST['voltage_tn_1']) ? trim($_POST['voltage_tn_1']) : '',
                'hasil_2' => isset($_POST['voltage_tn_2']) ? trim($_POST['voltage_tn_2']) : '',
                'hasil_3' => isset($_POST['voltage_tn_3']) ? trim($_POST['voltage_tn_3']) : '',
                'hasil_4' => isset($_POST['voltage_tn_4']) ? trim($_POST['voltage_tn_4']) : ''
            ],
            [
                'name' => 'Ukur Arus Phase R',
                'standard' => '< 100% x IS',
                'hasil_1' => isset($_POST['current_r_1']) ? trim($_POST['current_r_1']) : '',
                'hasil_2' => isset($_POST['current_r_2']) ? trim($_POST['current_r_2']) : '',
                'hasil_3' => isset($_POST['current_r_3']) ? trim($_POST['current_r_3']) : '',
                'hasil_4' => isset($_POST['current_r_4']) ? trim($_POST['current_r_4']) : ''
            ],
            [
                'name' => 'Ukur Arus Phase S',
                'standard' => '< 100% x IS',
                'hasil_1' => isset($_POST['current_s_1']) ? trim($_POST['current_s_1']) : '',
                'hasil_2' => isset($_POST['current_s_2']) ? trim($_POST['current_s_2']) : '',
                'hasil_3' => isset($_POST['current_s_3']) ? trim($_POST['current_s_3']) : '',
                'hasil_4' => isset($_POST['current_s_4']) ? trim($_POST['current_s_4']) : ''
            ],
            [
                'name' => 'Ukur Arus Phase T',
                'standard' => '< 100% x IS',
                'hasil_1' => isset($_POST['current_t_1']) ? trim($_POST['current_t_1']) : '',
                'hasil_2' => isset($_POST['current_t_2']) ? trim($_POST['current_t_2']) : '',
                'hasil_3' => isset($_POST['current_t_3']) ? trim($_POST['current_t_3']) : '',
                'hasil_4' => isset($_POST['current_t_4']) ? trim($_POST['current_t_4']) : ''
            ],
            [
                'name' => 'Periksa Exhaust Fan',
                'standard' => 'Berfungsi',
                'hasil_1' => isset($_POST['exhaust_fan_1']) ? trim($_POST['exhaust_fan_1']) : '',
                'hasil_2' => isset($_POST['exhaust_fan_2']) ? trim($_POST['exhaust_fan_2']) : '',
                'hasil_3' => isset($_POST['exhaust_fan_3']) ? trim($_POST['exhaust_fan_3']) : '',
                'hasil_4' => isset($_POST['exhaust_fan_4']) ? trim($_POST['exhaust_fan_4']) : ''
            ],
            [
                'name' => 'Periksa Lampu Dalam',
                'standard' => 'Berfungsi',
                'hasil_1' => isset($_POST['internal_light_1']) ? trim($_POST['internal_light_1']) : '',
                'hasil_2' => isset($_POST['internal_light_2']) ? trim($_POST['internal_light_2']) : '',
                'hasil_3' => isset($_POST['internal_light_3']) ? trim($_POST['internal_light_3']) : '',
                'hasil_4' => isset($_POST['internal_light_4']) ? trim($_POST['internal_light_4']) : ''
            ],
            [
                'name' => 'Periksa Lampu Indikator',
                'standard' => 'Berfungsi',
                'hasil_1' => isset($_POST['indicator_light_1']) ? trim($_POST['indicator_light_1']) : '',
                'hasil_2' => isset($_POST['indicator_light_2']) ? trim($_POST['indicator_light_2']) : '',
                'hasil_3' => isset($_POST['indicator_light_3']) ? trim($_POST['indicator_light_3']) : '',
                'hasil_4' => isset($_POST['indicator_light_4']) ? trim($_POST['indicator_light_4']) : ''
            ],
            [
                'name' => 'Periksa voltmeter/Power Meter',
                'standard' => 'Berfungsi',
                'hasil_1' => isset($_POST['voltmeter_1']) ? trim($_POST['voltmeter_1']) : '',
                'hasil_2' => isset($_POST['voltmeter_2']) ? trim($_POST['voltmeter_2']) : '',
                'hasil_3' => isset($_POST['voltmeter_3']) ? trim($_POST['voltmeter_3']) : '',
                'hasil_4' => isset($_POST['voltmeter_4']) ? trim($_POST['voltmeter_4']) : ''
            ],
            [
                'name' => 'Periksa ampermeter/Power Meter',
                'standard' => 'Berfungsi',
                'hasil_1' => isset($_POST['ampermeter_1']) ? trim($_POST['ampermeter_1']) : '',
                'hasil_2' => isset($_POST['ampermeter_2']) ? trim($_POST['ampermeter_2']) : '',
                'hasil_3' => isset($_POST['ampermeter_3']) ? trim($_POST['ampermeter_3']) : '',
                'hasil_4' => isset($_POST['ampermeter_4']) ? trim($_POST['ampermeter_4']) : ''
            ],
            [
                'name' => 'Periksa hour Meter Panel',
                'standard' => 'Berfungsi',
                'hasil_1' => isset($_POST['hour_meter_1']) ? trim($_POST['hour_meter_1']) : '',
                'hasil_2' => isset($_POST['hour_meter_2']) ? trim($_POST['hour_meter_2']) : '',
                'hasil_3' => isset($_POST['hour_meter_3']) ? trim($_POST['hour_meter_3']) : '',
                'hasil_4' => isset($_POST['hour_meter_4']) ? trim($_POST['hour_meter_4']) : ''
            ],
            [
                'name' => 'Periksa Relay, Time, Kontaktor, Push Button & lainnya',
                'standard' => 'Berfungsi',
                'hasil_1' => isset($_POST['relay_1']) ? trim($_POST['relay_1']) : '',
                'hasil_2' => isset($_POST['relay_2']) ? trim($_POST['relay_2']) : '',
                'hasil_3' => isset($_POST['relay_3']) ? trim($_POST['relay_3']) : '',
                'hasil_4' => isset($_POST['relay_4']) ? trim($_POST['relay_4']) : ''
            ],
            [
                'name' => 'Periksa kebersihan Panel',
                'standard' => 'Bersih',
                'hasil_1' => isset($_POST['cleanliness_1']) ? trim($_POST['cleanliness_1']) : '',
                'hasil_2' => isset($_POST['cleanliness_2']) ? trim($_POST['cleanliness_2']) : '',
                'hasil_3' => isset($_POST['cleanliness_3']) ? trim($_POST['cleanliness_3']) : '',
                'hasil_4' => isset($_POST['cleanliness_4']) ? trim($_POST['cleanliness_4']) : ''
            ],
            [
                'name' => 'Periksa Kerapihan Kabel',
                'standard' => 'Rapih',
                'hasil_1' => isset($_POST['cable_tidiness_1']) ? trim($_POST['cable_tidiness_1']) : '',
                'hasil_2' => isset($_POST['cable_tidiness_2']) ? trim($_POST['cable_tidiness_2']) : '',
                'hasil_3' => isset($_POST['cable_tidiness_3']) ? trim($_POST['cable_tidiness_3']) : '',
                'hasil_4' => isset($_POST['cable_tidiness_4']) ? trim($_POST['cable_tidiness_4']) : ''
            ],
            [
                'name' => 'Periksa Suhu Ruang',
                'standard' => '26 - 33°C',
                'hasil_1' => isset($_POST['room_temp_1']) ? trim($_POST['room_temp_1']) : '',
                'hasil_2' => isset($_POST['room_temp_2']) ? trim($_POST['room_temp_2']) : '',
                'hasil_3' => isset($_POST['room_temp_3']) ? trim($_POST['room_temp_3']) : '',
                'hasil_4' => isset($_POST['room_temp_4']) ? trim($_POST['room_temp_4']) : ''
            ],
            [
                'name' => 'Kekencangan Baut-Baut Terminal',
                'standard' => 'Tidak kendur',
                'hasil_1' => isset($_POST['terminal_bolts_1']) ? trim($_POST['terminal_bolts_1']) : '',
                'hasil_2' => isset($_POST['terminal_bolts_2']) ? trim($_POST['terminal_bolts_2']) : '',
                'hasil_3' => isset($_POST['terminal_bolts_3']) ? trim($_POST['terminal_bolts_3']) : '',
                'hasil_4' => isset($_POST['terminal_bolts_4']) ? trim($_POST['terminal_bolts_4']) : ''
            ],
            [
                'name' => 'Status Panel',
                'standard' => 'Layak',
                'hasil_1' => isset($_POST['status_1']) ? trim($_POST['status_1']) : '',
                'hasil_2' => isset($_POST['status_2']) ? trim($_POST['status_2']) : '',
                'hasil_3' => isset($_POST['status_3']) ? trim($_POST['status_3']) : '',
                'hasil_4' => isset($_POST['status_4']) ? trim($_POST['status_4']) : ''
            ],
            [
                'name' => 'Keterangan',
                'standard' => '',
                'hasil_1' => isset($_POST['notes_1']) ? trim($_POST['notes_1']) : '',
                'hasil_2' => isset($_POST['notes_2']) ? trim($_POST['notes_2']) : '',
                'hasil_3' => isset($_POST['notes_3']) ? trim($_POST['notes_3']) : '',
                'hasil_4' => isset($_POST['notes_4']) ? trim($_POST['notes_4']) : ''
            ]
        ];

        // Insert data ke tabel panel_listrik dengan semua kolom hasil
        $sql_panel = "INSERT INTO panel_listrik (id_date, pengukuran, standar, hasil_1, hasil_2, hasil_3, hasil_4) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_panel = $conn->prepare($sql_panel);
        if (!$stmt_panel) {
            throw new Exception("Prepare panel_listrik failed: " . $conn->error);
        }

        $measurement_count = 0;
        foreach ($measurements as $measurement) {
            // Sanitasi data sebelum insert
            $pengukuran = mysqli_real_escape_string($conn, $measurement['name']);
            $standar = mysqli_real_escape_string($conn, $measurement['standard']);
            $hasil_1 = mysqli_real_escape_string($conn, $measurement['hasil_1']);
            $hasil_2 = mysqli_real_escape_string($conn, $measurement['hasil_2']);
            $hasil_3 = mysqli_real_escape_string($conn, $measurement['hasil_3']);
            $hasil_4 = mysqli_real_escape_string($conn, $measurement['hasil_4']);
            
            $stmt_panel->bind_param("issssss", 
                $id_date, 
                $pengukuran, 
                $standar, 
                $hasil_1,
                $hasil_2,
                $hasil_3,
                $hasil_4
            );
            
            if (!$stmt_panel->execute()) {
                throw new Exception("Execute panel_listrik failed for measurement '" . $measurement['name'] . "': " . $stmt_panel->error);
            }
            
            $measurement_count++;
            error_log("Inserted measurement #" . $measurement_count . ": " . $measurement['name']);
        }

        // Commit transaksi
        $conn->commit();
        error_log("Transaction committed successfully. Total measurements inserted: " . $measurement_count);
        
        echo "<script>
                alert('Data panel listrik berhasil diinput! Total " . $measurement_count . " pengukuran disimpan.');
              </script>";
              
    } catch (Exception $e) {
        // Rollback transaksi jika terjadi error
        $conn->rollback();
        error_log("Database Error: " . $e->getMessage());
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
    } finally {
        // Tutup statement
        if (isset($stmt_date)) $stmt_date->close();
        if (isset($stmt_no_wo)) $stmt_no_wo->close();
        if (isset($stmt_plant)) $stmt_plant->close();
        if (isset($stmt_lokasi)) $stmt_lokasi->close();
        if (isset($stmt_frequensi)) $stmt_frequensi->close();
        if (isset($stmt_panel)) $stmt_panel->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INPUT DATA PREVENTIF PANEL LISTRIK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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

        /* Logo Container */
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

        /* Page Title */
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

        /* Form Container */
        .form-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 30px;
        }

        .form-section {
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

        .form-label {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border: 2px solid var(--medium-gray);
            border-radius: 8px;
            padding: 10px 15px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        /* Table Styles */
        .measurement-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: var(--white);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .measurement-table thead {
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }

        .measurement-table th {
            padding: 12px 10px;
            color: var(--white);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            border: 1px solid #ddd;
            text-align: center;
        }

        .measurement-table td {
            padding: 10px;
            font-size: 0.9rem;
            color: var(--dark-gray);
            border: 1px solid #ddd;
            text-align: center;
        }

        .measurement-table tr:nth-child(even) {
            background-color: rgba(0, 51, 102, 0.02);
        }

        .measurement-table tr:hover {
            background-color: rgba(0, 51, 102, 0.05);
        }

        .measurement-table .measurement-name {
            text-align: left;
            font-weight: 600;
            color: var(--primary-color);
            white-space: nowrap;
        }

        .measurement-input {
            width: 80px;
            padding: 5px 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
            font-size: 0.85rem;
        }

        .measurement-input:focus {
            border-color: var(--accent-color);
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }

        /* Button Styles */
        .btn-custom {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            color: var(--white);
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 51, 102, 0.3);
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 51, 102, 0.4);
            color: var(--white);
        }

        .btn-save {
            background: linear-gradient(45deg, var(--success-color), #20c997);
        }

        .btn-reset {
            background: linear-gradient(45deg, var(--warning-color), #fd7e14);
        }

        .btn-cancel {
            background: linear-gradient(45deg, var(--danger-color), #e74c3c);
        }

        .button-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        /* Alert Styles */
        .alert-custom {
            border-radius: 8px;
            border: none;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
        }

        /* Footer */
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

        /* Responsive Design */
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

            .form-container {
                padding: 0 15px;
            }

            .measurement-table {
                font-size: 0.8rem;
            }

            .measurement-input {
                width: 60px;
                padding: 3px 5px;
                font-size: 0.8rem;
            }

            .button-group {
                flex-direction: column;
                align-items: center;
            }

            .btn-custom {
                width: 200px;
            }
        }

        @media (max-width: 576px) {
            .measurement-table th,
            .measurement-table td {
                padding: 8px 5px;
                font-size: 0.75rem;
            }

            .measurement-input {
                width: 50px;
            }
        }

        /* Custom styles for frequency input */
        .frequency-container {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .frequency-container input[list] {
            flex: 1;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.95rem;
        }

        @media (max-width: 768px) {
            .frequency-container {
                flex-direction: column;
                gap: 10px;
            }
        }

        /* Field error styling */
        .field-error {
            border-color: var(--danger-color) !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
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

    <h1>INPUT DATA PERAWATAN PANEL LISTRIK</h1>

    <div class="form-container">
        <!-- Alert Success/Error -->
        <div id="alertContainer">
            <!-- Dynamic alerts will be inserted here -->
        </div>

        <form id="preventiveForm" action="" method="POST">
            <!-- General Information Section -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-info-circle me-2"></i>Informasi Umum
                </h3>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="woNumber" class="form-label">
                            <i class="fas fa-file-alt me-1"></i>No. WO
                        </label>
                        <input type="text" class="form-control" id="woNumber" name="woNumber" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="inspectionDate" class="form-label">
                            <i class="fas fa-calendar me-1"></i>Tanggal Inspeksi
                        </label>
                        <input type="date" class="form-control" id="inspectionDate" name="inspectionDate" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="plantName" class="form-label">
                            <i class="fas fa-industry me-1"></i>Plant
                        </label>
                        <select class="form-select" id="plantName" name="plantName" required>
                            <option value="">Pilih Plant</option>
                            <option value="Plant A">Plant A</option>
                            <option value="Plant B">Plant B</option>
                            <option value="Plant C">Plant C</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="location" class="form-label">
                            <i class="fas fa-map-marker-alt me-1"></i>Lokasi
                        </label>
                        <input type="text" class="form-control" id="location" name="location" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="frequency" class="form-label">
                            <i class="fas fa-clock me-1"></i>Frekuensi
                        </label>
                        <div class="frequency-container">
                            <input list="bulan" name="bulan" placeholder="Pilih Bulan" class="form-control" required>
                            <datalist id="bulan">
                                <option value="1 bulan"></option>
                                <option value="3 bulan"></option>
                                <option value="6 bulan"></option>
                                <option value="12 bulan"></option>
                            </datalist>
                            
                            <input list="nama" name="nama" placeholder="Pilih Tahun" class="form-control" required>
                            <datalist id="nama">
                                <option value="2 Tahun"></option>
                                <option value="3 Tahun"></option>
                                <option value="5 tahun"></option>
                            </datalist>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Measurement Data Section -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-clipboard-check me-2"></i>Data Pengukuran
                </h3>
                <div class="table-responsive">
                    <table class="measurement-table">
                        <thead>
                            <tr>
                                <th rowspan="2" style="width: 40%;">Pengukuran</th>
                                <th rowspan="2" style="width: 20%;">Standar</th>
                                <th colspan="4">Hasil</th>
                            </tr>
                            <tr>
                                <th>Hasil 1</th>
                                <th>Hasil 2</th>
                                <th>Hasil 3</th>
                                <th>Hasil 4</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Voltage Measurements -->
                            <tr>
                                <td class="measurement-name">Ukur tegangan R - S</td>
                                <td rowspan="6" style="vertical-align: middle;">
                                    Maks 5% dari Vn<br>
                                    Min 10% dari Vn
                                </td>
                                <td><input type="text" class="measurement-input" name="voltage_rs_1" ></td>
                                <td><input type="text" class="measurement-input" name="voltage_rs_2"></td>
                                <td><input type="text" class="measurement-input" name="voltage_rs_3"></td>
                                <td><input type="text" class="measurement-input" name="voltage_rs_4"></td>
                            </tr>
                            <tr>
                                <td class="measurement-name">Ukur tegangan R - T</td>
                                <td><input type="text" class="measurement-input" name="voltage_rt_1" ></td>
                                <td><input type="text" class="measurement-input" name="voltage_rt_2"></td>
                                <td><input type="text" class="measurement-input" name="voltage_rt_3"></td>
                                <td><input type="text" class="measurement-input" name="voltage_rt_4"></td>
                            </tr>
                            <tr>
                                <td class="measurement-name">Ukur tegangan S - T</td>
                                <td><input type="text" class="measurement-input" name="voltage_st_1" ></td>
                                <td><input type="text" class="measurement-input" name="voltage_st_2"></td>
                                <td><input type="text" class="measurement-input" name="voltage_st_3"></td>
                                <td><input type="text" class="measurement-input" name="voltage_st_4"></td>
                            </tr>
                            <tr>
                                <td class="measurement-name">Ukur tegangan R - N</td>
                                <td><input type="text" class="measurement-input" name="voltage_rn_1" ></td>
                                <td><input type="text" class="measurement-input" name="voltage_rn_2"></td>
                                <td><input type="text" class="measurement-input" name="voltage_rn_3"></td>
                                <td><input type="text" class="measurement-input" name="voltage_rn_4"></td>
                            </tr>
                            <tr>
                                <td class="measurement-name">Ukur tegangan S - N</td>
                                <td><input type="text" class="measurement-input" name="voltage_sn_1" ></td>
                                <td><input type="text" class="measurement-input" name="voltage_sn_2"></td>
                                <td><input type="text" class="measurement-input" name="voltage_sn_3"></td>
                                <td><input type="text" class="measurement-input" name="voltage_sn_4"></td>
                            </tr>
                            <tr>
                                <td class="measurement-name">Ukur tegangan T - N</td>
                                <td><input type="text" class="measurement-input" name="voltage_tn_1" ></td>
                                <td><input type="text" class="measurement-input" name="voltage_tn_2"></td>
                                <td><input type="text" class="measurement-input" name="voltage_tn_3"></td>
                                <td><input type="text" class="measurement-input" name="voltage_tn_4"></td>
                            </tr>
                                
                            <!-- Current Measurements -->
                            <tr>
                                <td class="measurement-name">Ukur Arus Phase R</td>
                                <td>&lt; 100% x IS</td>
                                <td><input type="text" class="measurement-input" name="current_r_1" ></td>
                                <td><input type="text" class="measurement-input" name="current_r_2"></td>
                                <td><input type="text" class="measurement-input" name="current_r_3"></td>
                                <td><input type="text" class="measurement-input" name="current_r_4"></td>
                            </tr>
                            <tr>
                                <td class="measurement-name">Ukur Arus Phase S</td>
                                <td>&lt; 100% x IS</td>
                                <td><input type="text" class="measurement-input" name="current_s_1" ></td>
                                <td><input type="text" class="measurement-input" name="current_s_2"></td>
                                <td><input type="text" class="measurement-input" name="current_s_3"></td>
                                <td><input type="text" class="measurement-input" name="current_s_4"></td>
                            </tr>
                            <tr>
                                <td class="measurement-name">Ukur Arus Phase T</td>
                                <td>&lt; 100% x IS</td>
                                <td><input type="text" class="measurement-input" name="current_t_1" ></td>
                                <td><input type="text" class="measurement-input" name="current_t_2"></td>
                                <td><input type="text" class="measurement-input" name="current_t_3"></td>
                                <td><input type="text" class="measurement-input" name="current_t_4"></td>
                            </tr>
                            <tr>
                                <td class="measurement-name">Periksa Exhaust Fan</td>
                                <td>Berfungsi</td>
                                <td><input type="text" class="measurement-input" name="exhaust_fan_1" ></td>
                                <td><input type="text" class="measurement-input" name="exhaust_fan_2"></td>
                                <td><input type="text" class="measurement-input" name="exhaust_fan_3"></td>
                                <td><input type="text" class="measurement-input" name="exhaust_fan_4"></td>
                            </tr>
                            <tr>
                                <td class="measurement-name">Periksa Lampu Dalam</td>
                                <td>Berfungsi</td>
                                <td><input type="text" class="measurement-input" name="internal_light_1" ></td>
                                <td><input type="text" class="measurement-input" name="internal_light_2"></td>
                                <td><input type="text" class="measurement-input" name="internal_light_3"></td>
                                <td><input type="text" class="measurement-input" name="internal_light_4"></td>
                            </tr>
                            <tr>
                                <td class="measurement-name">Periksa Lampu Indikator</td>
                                <td>Berfungsi</td>
                                <td><input type="text" class="measurement-input" name="indicator_light_1" ></td>
                                <td><input type="text" class="measurement-input" name="indicator_light_2"></td>
                                <td><input type="text" class="measurement-input" name="indicator_light_3"></td>
                                <td><input type="text" class="measurement-input" name="indicator_light_4"></td>
                            </tr>
                            <tr>
                                <td class="measurement-name">Periksa voltmeter/Power Meter</td>
                                <td>Berfungsi</td>
                                <td><input type="text" class="measurement-input" name="voltmeter_1" ></td>
                                <td><input type="text" class="measurement-input" name="voltmeter_2"></td>
                                <td><input type="text" class="measurement-input" name="voltmeter_3"></td>
                                <td><input type="text" class="measurement-input" name="voltmeter_4"></td>
                            </tr>
                            <tr>
                                <td class="measurement-name">Periksa ampermeter/Power Meter</td>
                                <td>Berfungsi</td>
                                <td><input type="text" class="measurement-input" name="ampermeter_1" ></td>
                                <td><input type="text" class="measurement-input" name="ampermeter_2"></td>
                                <td><input type="text" class="measurement-input" name="ampermeter_3"></td>
                                <td><input type="text" class="measurement-input" name="ampermeter_4"></td>
                            </tr>
                            <tr>
                                <td class="measurement-name">Periksa hour Meter Panel</td>
                                <td>Berfungsi</td>
                                <td><input type="text" class="measurement-input" name="hour_meter_1" ></td>
                                <td><input type="text" class="measurement-input" name="hour_meter_2"></td>
                                <td><input type="text" class="measurement-input" name="hour_meter_3"></td>
                                <td><input type="text" class="measurement-input" name="hour_meter_4"></td>
                            </tr>
                            <tr>
                                <td class="measurement-name">Periksa Relay, Time, Kontaktor, Push Button & lainnya</td>
                                <td>Berfungsi</td>
                                <td><input type="text" class="measurement-input" name="relay_1" ></td>
                                <td><input type="text" class="measurement-input" name="relay_2"></td>
                                <td><input type="text" class="measurement-input" name="relay_3"></td>
                                <td><input type="text" class="measurement-input" name="relay_4"></td>
                            </tr>
                            <tr>
                                <td class="measurement-name">Periksa kebersihan Panel</td>
                                <td>Bersih</td>
                                <td><input type="text" class="measurement-input" name="cleanliness_1" ></td>
                                <td><input type="text" class="measurement-input" name="cleanliness_2"></td>
                                <td><input type="text" class="measurement-input" name="cleanliness_3"></td>
                                <td><input type="text" class="measurement-input" name="cleanliness_4"></td>
                            </tr>
                            <tr>
                                <td class="measurement-name">Periksa Kerapihan Kabel</td>
                                <td>Rapih</td>
                                <td><input type="text" class="measurement-input" name="cable_tidiness_1" ></td>
                                <td><input type="text" class="measurement-input" name="cable_tidiness_2"></td>
                                <td><input type="text" class="measurement-input" name="cable_tidiness_3"></td>
                                <td><input type="text" class="measurement-input" name="cable_tidiness_4"></td>
                            </tr>
                            <tr>
                                <td class="measurement-name">Periksa Suhu Ruang</td>
                                <td>26 - 33°C</td>
                                <td><input type="text" class="measurement-input" name="room_temp_1" ></td>
                                <td><input type="text" class="measurement-input" name="room_temp_2"></td>
                                <td><input type="text" class="measurement-input" name="room_temp_3"></td>
                                <td><input type="text" class="measurement-input" name="room_temp_4"></td>
                            </tr>
                            <tr>
                                <td class="measurement-name">Kekencangan Baut-Baut Terminal</td>
                                <td>Tidak kendur</td>
                                <td><input type="text" class="measurement-input" name="terminal_bolts_1" ></td>
                                <td><input type="text" class="measurement-input" name="terminal_bolts_2"></td>
                                <td><input type="text" class="measurement-input" name="terminal_bolts_3"></td>
                                <td><input type="text" class="measurement-input" name="terminal_bolts_4"></td>
                            </tr>
                            <tr>
                                <td class="measurement-name">Status Panel</td>
                                <td>Layak</td>
                                <td><input type="text" class="measurement-input" name="status_1" ></td>
                                <td><input type="text" class="measurement-input" name="status_2"></td>
                                <td><input type="text" class="measurement-input" name="status_3"></td>
                                <td><input type="text" class="measurement-input" name="status_4"></td>
                            </tr>
                            <tr>
                                <td class="measurement-name">Keterangan</td>
                                <td></td>
                                <td><input type="text" class="measurement-input" name="notes_1"></td>
                                <td><input type="text" class="measurement-input" name="notes_2"></td>
                                <td><input type="text" class="measurement-input" name="notes_3"></td>
                                <td><input type="text" class="measurement-input" name="notes_4"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

                <!-- Button Group -->
                <div class="button-group">
                    <button type="submit" class="btn btn-custom btn-save">
                        <i class="fas fa-save me-2"></i>Simpan Data
                    </button>
                    <button type="reset" class="btn btn-custom btn-reset">
                        <i class="fas fa-undo me-2"></i>Reset Form
                    </button>
                    <button type="button" class="btn btn-custom btn-cancel" onclick="window.location.href='panel_listrik.php'">
                        <i class="fas fa-times me-2"></i>Batal
                    </button>
                </div>
            </form>
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
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        
        <!-- Custom JavaScript -->
        <script>
            // Form Validation
            document.getElementById('preventiveForm').addEventListener('submit', function(e) {
                let isValid = true;
                
                // Validate  fields
                const requiredFields = document.querySelectorAll('[]');
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add('field-error');
                    } else {
                        field.classList.remove('field-error');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    showAlert('Harap lengkapi semua field yang wajib diisi!', 'danger');
                }
            });

            // Function to show alert messages
            function showAlert(message, type) {
                const alertContainer = document.getElementById('alertContainer');
                const alert = document.createElement('div');
                alert.className = `alert alert-${type} alert-custom alert-dismissible fade show`;
                alert.innerHTML = `
                    <strong>${type === 'success' ? 'Sukses!' : 'Error!'}</strong> ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                alertContainer.appendChild(alert);
                
                // Auto dismiss after 5 seconds
                setTimeout(() => {
                    alert.remove();
                }, 5000);
            }

            // Highlight  fields on invalid
            const requiredFields = document.querySelectorAll('input[], select[]');
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