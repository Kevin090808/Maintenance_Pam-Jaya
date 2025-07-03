<?php
require('../fpdf186/fpdf.php');
include('../koneksi.php');

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'Data Maintenance', 0, 1, 'C');
        $this->Ln(10);
        
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Halaman ' . $this->PageNo(), 0, 0, 'C');
    }
}


$pdf = new PDF('P', 'mm', 'A4');
$pdf->AddPage();


$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 10, 'ID Plant', 1, 0, 'C');
$pdf->Cell(70, 10, 'Nama', 1, 0, 'C');
$pdf->Cell(70, 10, 'Jurusan', 1, 1, 'C');

$kelasQuery = "SELECT * FROM setup_kelas";
$kelasResult = mysqli_query($conn, $kelasQuery);

$pdf->SetFont('Arial', '', 12);
while ($kelas = mysqli_fetch_assoc($kelasResult)) {
    $pdf->Cell(40, 10, $kelas['id_kelas'], 1, 0, 'C');
    $pdf->Cell(70, 10, $kelas['nama_kelas'], 1, 0, 'C');
    $pdf->Cell(70, 10, $kelas['jurusan'], 1, 1, 'C');
}


$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 10, 'ID Pelajaran', 1, 0, 'C');
$pdf->Cell(120, 10, 'Nama Pelajaran', 1, 0, 'C');
$pdf->Cell(40, 10, 'KKM', 1, 1, 'C');

$pelajaranQuery = "SELECT * FROM setup_pelajaran";
$pelajaranResult = mysqli_query($conn, $pelajaranQuery);

$pdf->SetFont('Arial', '', 12);
while ($pelajaran = mysqli_fetch_assoc($pelajaranResult)) {
    $pdf->Cell(40, 10, $pelajaran['id_pelajaran'], 1, 0, 'C');
    $pdf->Cell(120, 10, $pelajaran['nama_pelajaran'], 1, 0, 'C');
    $pdf->Cell(40, 10, $pelajaran['kkm'], 1, 1, 'C');
}


$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(25, 10, 'ID Jadwal', 1, 0, 'C');
$pdf->Cell(40, 10, 'Pelajaran', 1, 0, 'C');
$pdf->Cell(35, 10, 'Nama Guru', 1, 0, 'C');
$pdf->Cell(20, 10, 'Kelas', 1, 0, 'C');
$pdf->Cell(25, 10, 'Hari', 1, 0, 'C');
$pdf->Cell(25, 10, 'Waktu', 1, 1, 'C');

$jadwalQuery = "SELECT * FROM tbl_jadwal";
$jadwalResult = mysqli_query($conn, $jadwalQuery);

$pdf->SetFont('Arial', '', 10);
while ($jadwal = mysqli_fetch_assoc($jadwalResult)) {
    $guruQuery = "SELECT nama_guru FROM data_guru WHERE id_guru = '".$jadwal['id_guru']."'";
    $guruResult = mysqli_query($conn, $guruQuery);
    $guru = mysqli_fetch_assoc($guruResult);

    $pdf->Cell(25, 10, $jadwal['id_jadwal'], 1, 0, 'C');
    $pdf->Cell(40, 10, $jadwal['nama_pelajaran'], 1, 0, 'C');
    $pdf->Cell(35, 10, $guru['nama_guru'], 1, 0, 'C');
    $pdf->Cell(20, 10, $jadwal['kelas'], 1, 0, 'C');
    $pdf->Cell(25, 10, $jadwal['hari'], 1, 0, 'C');
    $pdf->Cell(25, 10, $jadwal['waktu_mulai'] . ' - ' . $jadwal['waktu_selesai'], 1, 1, 'C');


    if ($pdf->GetY() > 250) {  
        $pdf->AddPage();
    }
}

$pdf->Output('D', 'Data_Sekolah.pdf');
?>