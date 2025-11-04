<?php
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'teknisi');

// koneksi ke server (tanpa memilih DB) untuk memastikan DB ada
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS);
if ($mysqli->connect_errno) {
    die("Koneksi gagal: ({$mysqli->connect_errno}) " . htmlspecialchars($mysqli->connect_error));
}

// buat database jika belum ada, lalu gunakan
if (!$mysqli->query("CREATE DATABASE IF NOT EXISTS `".DB_NAME."` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
    die("Gagal membuat database: " . htmlspecialchars($mysqli->error));
}
$mysqli->select_db(DB_NAME);

// buat tabel jika belum ada
$createTable = "
CREATE TABLE IF NOT EXISTS teknisi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_teknisi VARCHAR(100) NOT NULL,
  alamat VARCHAR(255) NOT NULL,
  no_hp VARCHAR(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
if (!$mysqli->query($createTable)) {
    die("Gagal membuat tabel: " . htmlspecialchars($mysqli->error));
}

$mysqli->set_charset('utf8mb4');
?>

