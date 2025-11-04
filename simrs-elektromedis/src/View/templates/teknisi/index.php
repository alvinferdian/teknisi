<?php
include_once("../../../Config/config.php");

// Fetch the list of teknisi from the database
$countRes = $mysqli->query("SELECT COUNT(*) AS c FROM teknisi");
$count = $countRes ? (int)$countRes->fetch_assoc()['c'] : 0;

$result = $mysqli->query("SELECT id, nama_teknisi AS nama, alamat, no_hp, IFNULL(photo,'') AS photo FROM teknisi ORDER BY nama_teknisi COLLATE utf8mb4_unicode_ci ASC");
if (!$result) {
    echo "Query error: " . htmlspecialchars($mysqli->error);
    exit;
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teknisi List - SIM RS</title>
    <link href="/css/app.css" rel="stylesheet">
</head>
<body>
    <?php include_once("../header.php"); ?>

    <div class="container">
        <h1>Daftar Teknisi</h1>
        <p>Total Teknisi: <?= $count; ?></p>
        <a href="add.php" class="btn btn-primary">Tambah Teknisi</a>

        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Foto</th>
                    <th>Nama</th>
                    <th>Alamat</th>
                    <th>No HP</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 1;
                while ($row = $result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . $i . '</td>';
                    echo '<td><img src="/uploads/' . htmlspecialchars($row['photo']) . '" alt="Foto" class="thumb"></td>';
                    echo '<td>' . htmlspecialchars($row['nama']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['alamat']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['no_hp']) . '</td>';
                    echo '<td>
                            <a href="edit.php?id=' . urlencode($row['id']) . '" class="btn btn-warning">Edit</a>
                            <a href="delete.php?id=' . urlencode($row['id']) . '" class="btn btn-danger" onclick="return confirm(\'Hapus data ini?\')">Hapus</a>
                          </td>';
                    echo '</tr>';
                    $i++;
                }
                $result->free();
                ?>
            </tbody>
        </table>
    </div>

    <?php include_once("../footer.php"); ?>
</body>
</html>