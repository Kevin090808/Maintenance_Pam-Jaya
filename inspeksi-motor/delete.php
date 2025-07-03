<?php
 
 $host = "localhost";
 $username = "root";
 $password = "";
 $dbname= "maintenance";

 $conn = new mysqli($host, $username, $password, $dbname);

 if ($conn->connect_error) {
    die ("Koneksi Error:" . $conn->connect_error);
 }

 if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $table = [
        "vibrasi_motor",
        "vibrasi_pompa",
        "temperatur",
        "cek_kondisi",
        "plant",
        "lokasi",
        "type",
        "machine",
        "date"
    ];

    foreach ($table as $table){
        $sql = "DELETE FROM $table";
        
        if ($conn->query($sql) === TRUE) {
            echo "Data Berhasil Dihapus Dari table $table <br>";
        } else {
            echo "Gagal Menghapus Data Dari Table $table:" .$conn->error . "<br>";
        }
    }
 }
 ?>