<?php
include_once("../../../Config/config.php");

// Ambil ID teknisi dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data teknisi berdasarkan ID
$result = $mysqli->query("SELECT id, nama_teknisi AS nama, alamat, no_hp, photo FROM teknisi WHERE id = $id");
$teknisi = $result ? $result->fetch_assoc() : null;

if (!$teknisi) {
    echo "Teknisi tidak ditemukan.";
    exit;
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Teknisi - SIM RS</title>
    <link href="/css/app.css" rel="stylesheet">
</head>
<body>
    <?php include_once("../header.php"); ?>

    <div class="container">
        <h1>Edit Teknisi</h1>
        <form action="update.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= htmlspecialchars($teknisi['id']) ?>">
            <div class="mb-3">
                <label for="nama" class="form-label">Nama Teknisi</label>
                <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($teknisi['nama']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="alamat" class="form-label">Alamat</label>
                <input type="text" class="form-control" id="alamat" name="alamat" value="<?= htmlspecialchars($teknisi['alamat']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="no_hp" class="form-label">No HP</label>
                <input type="text" class="form-control" id="no_hp" name="no_hp" value="<?= htmlspecialchars($teknisi['no_hp']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="photo" class="form-label">Foto</label>
                <input type="file" class="form-control" id="photo" name="photo">
                <?php if ($teknisi['photo']): ?>
                    <img src="/uploads/<?= htmlspecialchars($teknisi['photo']) ?>" alt="Foto Teknisi" class="img-thumbnail mt-2" style="max-width: 150px;">
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>

    <?php include_once("../footer.php"); ?>
</body>
</html>