-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 02, 2025 at 04:24 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `maintenance`
--

-- --------------------------------------------------------

--
-- Table structure for table `cek_kondisi`
--

CREATE TABLE `cek_kondisi` (
  `id` int(11) NOT NULL,
  `condition_name` varchar(255) NOT NULL,
  `status_1` varchar(255) NOT NULL,
  `checkbox_1` varchar(255) NOT NULL,
  `status_2` varchar(255) NOT NULL,
  `checkbox_2` varchar(255) NOT NULL,
  `id_date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cleaning_ac`
--

CREATE TABLE `cleaning_ac` (
  `id` int(11) NOT NULL,
  `pk` varchar(50) NOT NULL,
  `merek` varchar(100) NOT NULL,
  `tipe` varchar(100) NOT NULL,
  `jenis_freon` varchar(50) NOT NULL,
  `filter` tinyint(1) DEFAULT 0,
  `indoor` tinyint(1) DEFAULT 0,
  `outdoor` tinyint(1) DEFAULT 0,
  `nilai_ampere` decimal(10,2) NOT NULL,
  `tambah_freon` tinyint(1) DEFAULT 0,
  `catatan` text DEFAULT NULL,
  `tanggal_input` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `date`
--

CREATE TABLE `date` (
  `id_date` int(11) NOT NULL,
  `tanggal` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `frequensi`
--

CREATE TABLE `frequensi` (
  `id` int(11) NOT NULL,
  `bulan` varchar(255) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `id_date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inspeksi_ac`
--

CREATE TABLE `inspeksi_ac` (
  `id` int(11) NOT NULL,
  `no_ac` varchar(50) NOT NULL,
  `pk` varchar(255) NOT NULL,
  `merek` varchar(100) NOT NULL,
  `tipe` varchar(32) NOT NULL,
  `freon` varchar(20) NOT NULL,
  `suhu_ruangan` varchar(20) NOT NULL,
  `fungsi` varchar(50) NOT NULL,
  `catatan` varchar(255) NOT NULL,
  `id_date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inspeksi_valve`
--

CREATE TABLE `inspeksi_valve` (
  `id` int(11) NOT NULL,
  `equipment` varchar(255) NOT NULL,
  `butterfly` varchar(50) DEFAULT NULL,
  `gate` varchar(50) DEFAULT NULL,
  `ball` varchar(50) DEFAULT NULL,
  `globe` varchar(50) DEFAULT NULL,
  `membran` varchar(50) DEFAULT NULL,
  `foot_valve` varchar(50) DEFAULT NULL,
  `swing_check` varchar(50) DEFAULT NULL,
  `good` varchar(50) NOT NULL,
  `not_good` varchar(50) NOT NULL,
  `perawatan_part` text DEFAULT NULL,
  `penggantian_part` text DEFAULT NULL,
  `jumlah_part` varchar(100) DEFAULT NULL,
  `Remaks` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_date` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `insulation`
--

CREATE TABLE `insulation` (
  `id` int(11) NOT NULL,
  `id_date` int(11) NOT NULL,
  `equipment` varchar(255) NOT NULL,
  `meansurement` varchar(255) NOT NULL,
  `inject_volt` varchar(255) NOT NULL,
  `reasult_insulation` varchar(255) NOT NULL,
  `dar` varchar(255) NOT NULL,
  `pi` varchar(255) NOT NULL,
  `condition` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kegiatan`
--

CREATE TABLE `kegiatan` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login`
--

CREATE TABLE `login` (
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login`
--

INSERT INTO `login` (`username`, `password`) VALUES
('iwan', 'e10adc3949ba59abbe56e057f20f883e'),
('kevin', '202cb962ac59075b964b07152d234b70');

-- --------------------------------------------------------

--
-- Table structure for table `lokasi`
--

CREATE TABLE `lokasi` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `id_date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `machine`
--

CREATE TABLE `machine` (
  `id` int(11) NOT NULL,
  `nomer` varchar(255) NOT NULL,
  `id_date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `no_wo`
--

CREATE TABLE `no_wo` (
  `id` int(11) NOT NULL,
  `nomer` varchar(255) NOT NULL,
  `id_date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `panel_listrik`
--

CREATE TABLE `panel_listrik` (
  `id` int(11) NOT NULL,
  `id_date` int(11) DEFAULT NULL,
  `pengukuran` varchar(255) DEFAULT NULL,
  `standar` varchar(255) DEFAULT NULL,
  `hasil_1` varchar(100) DEFAULT NULL,
  `hasil_2` varchar(100) DEFAULT NULL,
  `hasil_3` varchar(100) DEFAULT NULL,
  `hasil_4` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `plant`
--

CREATE TABLE `plant` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `id_date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pressure_indikator`
--

CREATE TABLE `pressure_indikator` (
  `id` int(11) NOT NULL,
  `id_date` int(11) NOT NULL,
  `kode` varchar(50) DEFAULT NULL,
  `deskripsi` varchar(255) DEFAULT NULL,
  `range_bar` varchar(50) DEFAULT NULL,
  `site` varchar(100) DEFAULT NULL,
  `nilai_0` varchar(50) DEFAULT NULL,
  `std_0` varchar(50) DEFAULT NULL,
  `dev_0` varchar(50) DEFAULT NULL,
  `nilai_40` varchar(50) DEFAULT NULL,
  `std_40` varchar(50) DEFAULT NULL,
  `dev_40` varchar(50) DEFAULT NULL,
  `nilai_60` varchar(50) DEFAULT NULL,
  `std_60` varchar(50) DEFAULT NULL,
  `dev_60` varchar(50) DEFAULT NULL,
  `nilai_100` varchar(50) DEFAULT NULL,
  `std_100` varchar(50) DEFAULT NULL,
  `dev_100` varchar(50) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `preventif_thermograph`
--

CREATE TABLE `preventif_thermograph` (
  `id` int(255) NOT NULL,
  `tanggal_pengecekan` date NOT NULL,
  `lokasi` varchar(100) NOT NULL,
  `no_urut` int(50) NOT NULL,
  `nama_equipment` varchar(155) NOT NULL,
  `temperatur` varchar(50) NOT NULL,
  `keterangan` text NOT NULL,
  `id_date` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pump_tuneup`
--

CREATE TABLE `pump_tuneup` (
  `id` int(255) NOT NULL,
  `equipment` varchar(50) NOT NULL,
  `seal_coupling` varchar(50) NOT NULL,
  `shaft` varchar(50) NOT NULL,
  `bolt_mounting` varchar(50) NOT NULL,
  `balancing` varchar(50) NOT NULL,
  `good` varchar(50) NOT NULL,
  `not_good` varchar(50) NOT NULL,
  `perawatan_part` varchar(50) NOT NULL,
  `penggantian_part` varchar(50) NOT NULL,
  `jumlah_part` int(50) NOT NULL,
  `remaks` varchar(255) NOT NULL,
  `id_date` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resistensi`
--

CREATE TABLE `resistensi` (
  `id` int(11) NOT NULL,
  `id_date` int(11) NOT NULL,
  `equipment` varchar(255) NOT NULL,
  `meansurement` varchar(255) NOT NULL,
  `result` varchar(255) NOT NULL,
  `condition` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `temperatur`
--

CREATE TABLE `temperatur` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `point` varchar(255) NOT NULL,
  `hasil` varchar(255) NOT NULL,
  `standar_max` varchar(255) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `id_date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `type`
--

CREATE TABLE `type` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `id_date` int(11) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `valve_tuneup`
--

CREATE TABLE `valve_tuneup` (
  `id` int(255) NOT NULL,
  `equipment` varchar(255) NOT NULL,
  `deskripsi` varchar(255) NOT NULL,
  `nama_part` varchar(50) NOT NULL,
  `penggantian_part` varchar(50) NOT NULL,
  `jumlah_part` int(50) NOT NULL,
  `detail_pekerjaan` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL,
  `id_date` int(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vibrasi_motor`
--

CREATE TABLE `vibrasi_motor` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `point` varchar(255) NOT NULL,
  `hasil` varchar(255) NOT NULL,
  `standar_max` varchar(255) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `id_date` int(11) NOT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vibrasi_pompa`
--

CREATE TABLE `vibrasi_pompa` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `point` varchar(255) NOT NULL,
  `hasil` varchar(255) NOT NULL,
  `standar_max` varchar(255) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `id_date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cek_kondisi`
--
ALTER TABLE `cek_kondisi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_date` (`id_date`);

--
-- Indexes for table `cleaning_ac`
--
ALTER TABLE `cleaning_ac`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_date` (`id_date`);

--
-- Indexes for table `date`
--
ALTER TABLE `date`
  ADD PRIMARY KEY (`id_date`);

--
-- Indexes for table `frequensi`
--
ALTER TABLE `frequensi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_date` (`id_date`);

--
-- Indexes for table `inspeksi_ac`
--
ALTER TABLE `inspeksi_ac`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_date` (`id_date`);

--
-- Indexes for table `inspeksi_valve`
--
ALTER TABLE `inspeksi_valve`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_date` (`id_date`);

--
-- Indexes for table `insulation`
--
ALTER TABLE `insulation`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_date` (`id_date`);

--
-- Indexes for table `kegiatan`
--
ALTER TABLE `kegiatan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login`
--
ALTER TABLE `login`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `lokasi`
--
ALTER TABLE `lokasi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_date` (`id_date`);

--
-- Indexes for table `machine`
--
ALTER TABLE `machine`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_date` (`id_date`);

--
-- Indexes for table `no_wo`
--
ALTER TABLE `no_wo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_date` (`id_date`);

--
-- Indexes for table `panel_listrik`
--
ALTER TABLE `panel_listrik`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_date` (`id_date`);

--
-- Indexes for table `plant`
--
ALTER TABLE `plant`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_date` (`id_date`);

--
-- Indexes for table `pressure_indikator`
--
ALTER TABLE `pressure_indikator`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_date` (`id_date`);

--
-- Indexes for table `preventif_thermograph`
--
ALTER TABLE `preventif_thermograph`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_date` (`id_date`);

--
-- Indexes for table `pump_tuneup`
--
ALTER TABLE `pump_tuneup`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_date` (`id_date`);

--
-- Indexes for table `resistensi`
--
ALTER TABLE `resistensi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_date` (`id_date`);

--
-- Indexes for table `temperatur`
--
ALTER TABLE `temperatur`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_date` (`id_date`);

--
-- Indexes for table `type`
--
ALTER TABLE `type`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_date` (`id_date`);

--
-- Indexes for table `valve_tuneup`
--
ALTER TABLE `valve_tuneup`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_date` (`id_date`);

--
-- Indexes for table `vibrasi_motor`
--
ALTER TABLE `vibrasi_motor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_date` (`id_date`);

--
-- Indexes for table `vibrasi_pompa`
--
ALTER TABLE `vibrasi_pompa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_date` (`id_date`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cek_kondisi`
--
ALTER TABLE `cek_kondisi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cleaning_ac`
--
ALTER TABLE `cleaning_ac`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `date`
--
ALTER TABLE `date`
  MODIFY `id_date` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `frequensi`
--
ALTER TABLE `frequensi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inspeksi_ac`
--
ALTER TABLE `inspeksi_ac`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inspeksi_valve`
--
ALTER TABLE `inspeksi_valve`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `insulation`
--
ALTER TABLE `insulation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kegiatan`
--
ALTER TABLE `kegiatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lokasi`
--
ALTER TABLE `lokasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `machine`
--
ALTER TABLE `machine`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `no_wo`
--
ALTER TABLE `no_wo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `panel_listrik`
--
ALTER TABLE `panel_listrik`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `plant`
--
ALTER TABLE `plant`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pressure_indikator`
--
ALTER TABLE `pressure_indikator`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `preventif_thermograph`
--
ALTER TABLE `preventif_thermograph`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pump_tuneup`
--
ALTER TABLE `pump_tuneup`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resistensi`
--
ALTER TABLE `resistensi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `temperatur`
--
ALTER TABLE `temperatur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `type`
--
ALTER TABLE `type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `valve_tuneup`
--
ALTER TABLE `valve_tuneup`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vibrasi_motor`
--
ALTER TABLE `vibrasi_motor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vibrasi_pompa`
--
ALTER TABLE `vibrasi_pompa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cek_kondisi`
--
ALTER TABLE `cek_kondisi`
  ADD CONSTRAINT `cek_kondisi_ibfk_1` FOREIGN KEY (`id_date`) REFERENCES `date` (`id_date`) ON DELETE CASCADE;

--
-- Constraints for table `panel_listrik`
--
ALTER TABLE `panel_listrik`
  ADD CONSTRAINT `panel_listrik_ibfk_1` FOREIGN KEY (`id_date`) REFERENCES `date` (`id_date`);

--
-- Constraints for table `pressure_indikator`
--
ALTER TABLE `pressure_indikator`
  ADD CONSTRAINT `pressure_indikator_ibfk_1` FOREIGN KEY (`id_date`) REFERENCES `date` (`id_date`);

--
-- Constraints for table `temperatur`
--
ALTER TABLE `temperatur`
  ADD CONSTRAINT `temperatur_ibfk_1` FOREIGN KEY (`id_date`) REFERENCES `date` (`id_date`) ON DELETE CASCADE;

--
-- Constraints for table `vibrasi_motor`
--
ALTER TABLE `vibrasi_motor`
  ADD CONSTRAINT `vibrasi_motor_ibfk_1` FOREIGN KEY (`id_date`) REFERENCES `date` (`id_date`) ON DELETE CASCADE;

--
-- Constraints for table `vibrasi_pompa`
--
ALTER TABLE `vibrasi_pompa`
  ADD CONSTRAINT `vibrasi_pompa_ibfk_1` FOREIGN KEY (`id_date`) REFERENCES `date` (`id_date`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
