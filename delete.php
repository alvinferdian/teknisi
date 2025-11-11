<?php
// include database connection file
include_once("config.php");

// pastikan id diberikan dan valid
if (!isset($_GET['id'])) {
    echo "ID tidak diberikan.";
    exit;
}

$id = (int) $_GET['id']; // cast aman ke integer
if ($id <= 0) {
    echo "ID tidak valid.";
    exit;
}

// prepared statement untuk menghindari SQL injection
$stmt = $mysqli->prepare("DELETE FROM teknisi WHERE id = ?");
if (!$stmt) {
    echo "Prepare failed: " . htmlspecialchars($mysqli->error);
    exit;
}
$stmt->bind_param("i", $id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    // berhasil dihapus
    $stmt->close();
    header("Location: index_teknisi.php");
    exit;
} else {
    // tidak ada baris terpengaruh
    $stmt->close();
    echo "Tidak ada record yang dihapus (ID mungkin tidak ada). <a href='index_teknisi.php'>Kembali</a>";
    exit;
}
?>
//2025byalvin