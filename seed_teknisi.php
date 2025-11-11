<?php
// Script singkat untuk menambahkan 10 teknisi fiksi ke tabel `teknisi`
// Simpan di c:\xampp\htdocs\teknisi\seed_teknisi.php lalu buka di browser:
// http://localhost/teknisi/seed_teknisi.php
// atau jalankan via CLI:
// "C:\xampp\php\php.exe" c:\xampp\htdocs\teknisi\seed_teknisi.php

include_once("config.php");

$mysqli = $mysqli ?? $GLOBALS['mysqli'] ?? null;
if (!$mysqli) {
    echo "Database connection not found. Periksa config.php.";
    exit;
}

// pastikan tabel ada
$check = $mysqli->query("SHOW TABLES LIKE 'teknisi'");
if (!$check || $check->num_rows === 0) {
    die("Tabel 'teknisi' tidak ditemukan. Buat terlebih dahulu.");
}

// pastikan kolom photo ada (nullable)
$cols = [];
$res = $mysqli->query("DESCRIBE teknisi");
if ($res) {
    while ($r = $res->fetch_assoc()) $cols[] = $r['Field'];
    $res->free();
}
if (!in_array('photo', $cols)) {
    $mysqli->query("ALTER TABLE teknisi ADD COLUMN photo VARCHAR(255) NULL AFTER no_hp");
}

// cek jumlah saat ini
$resCount = $mysqli->query("SELECT COUNT(*) AS c FROM teknisi");
$count = $resCount ? (int)$resCount->fetch_assoc()['c'] : 0;

$samples = [
    ['Andi Pratama', 'Gedung A - Instalasi Elektromedis', '081234567890'],
    ['Siti Rahma', 'Gedung B - Ruang Perawatan', '081298765432'],
    ['Budi Santoso', 'Poliklinik - Lantai 1', '082112233445'],
    ['Dewi Lestari', 'Laboratorium - Lt.2', '081355577799'],
    ['Rizal Maulana', 'Radiologi - Gedung C', '081366644455'],
    ['Fajar Nugroho', 'Maintenance Unit', '082233445566'],
    ['Maya Putri', 'Unit ICU', '081477788899'],
    ['Hendra Wijaya', 'Ruangan Operasi', '082199988877'],
    ['Lina Marlina', 'Instalasi Gawat Darurat', '081233344455'],
    ['Agus Harianto', 'Gudang Peralatan Medis', '082288776655']
];

if ($count >= 10) {
    echo "Tabel sudah berisi {$count} entri. Tidak ada yang ditambahkan. (Jika ingin tambah lagi, hapus beberapa entri atau ubah skrip.)";
    exit;
}

$toInsert = [];
$i = 0;
while ($count < 10 && $i < count($samples)) {
    $toInsert[] = $samples[$i];
    $count++;
    $i++;
}

$mysqli->begin_transaction();
try {
    $stmt = $mysqli->prepare("INSERT INTO teknisi (nama_teknisi, alamat, no_hp) VALUES (?, ?, ?)");
    if (!$stmt) throw new Exception("Prepare failed: " . $mysqli->error);
    foreach ($toInsert as $row) {
        $stmt->bind_param("sss", $row[0], $row[1], $row[2]);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
    }
    $stmt->close();
    $mysqli->commit();
    echo "Berhasil menambahkan " . count($toInsert) . " teknisi contoh. Kembali ke <a href='index_teknisi.php'>daftar</a>.";
} catch (Exception $e) {
    $mysqli->rollback();
    echo "Gagal: " . htmlspecialchars($e->getMessage());
}
?>
//alvinferdianh