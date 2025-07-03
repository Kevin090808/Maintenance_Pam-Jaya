<?php
require('../fpdf186/fpdf.php');
include ('../koneksi.php');

class PDF extends PDF {
    function Header() {
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Data Maintenance', 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 0);
        $this->Cell(0, 10, 'Halaman' . $this->PageNo(), 0, 0, 'C');
    }
}

$pdf = new PDF('P', 'mm', 'A4' );
$pdf->AddPage();

$pdf->SetFont( 'Timew New Roman', 'B', '');