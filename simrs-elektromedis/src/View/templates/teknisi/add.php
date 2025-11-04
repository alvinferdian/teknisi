<?php
include_once("../../../Config/config.php");

// Initialize variables
$nama = $alamat = $no_hp = $photo = '';
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $alamat = trim($_POST['alamat']);
    $no_hp = trim($_POST['no_hp']);
    
    // Validate inputs
    if (empty($nama)) {
        $errors['nama'] = 'Nama teknisi tidak boleh kosong.';
    }
    if (empty($alamat)) {
        $errors['alamat'] = 'Alamat tidak boleh kosong.';
    }
    if (empty($no_hp)) {
        $errors['no_hp'] = 'Nomor HP tidak boleh kosong.';
    }

    // Handle file upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $photo = $_FILES['photo']['name'];
        $targetPath = __DIR__ . '/../../../public/uploads/' . basename($photo);
        move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath);
    }

    // If no errors, insert into database
    if (empty($errors)) {
        $stmt = $mysqli->prepare("INSERT INTO teknisi (nama_teknisi, alamat, no_hp, photo) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nama, $alamat, $no_hp, $photo);
        $stmt->execute();
        $stmt->close();
        header("Location: index.php"); // Redirect to teknisi index
        exit;
    }
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Teknisi - SIM RS</title>
    <link href="../../../public/css/app.css" rel="stylesheet">
</head>
<body>
    <?php include_once("../header.php"); ?>

    <div class="container">
        <h1>Tambah Teknisi</h1>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="nama" class="form-label">Nama Teknisi</label>
                <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($nama) ?>">
                <?php if (isset($errors['nama'])): ?>
                    <div class="text-danger"><?= $errors['nama'] ?></div>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="alamat" class="form-label">Alamat</label>
                <input type="text" class="form-control" id="alamat" name="alamat" value="<?= htmlspecialchars($alamat) ?>">
                <?php if (isset($errors['alamat'])): ?>
                    <div class="text-danger"><?= $errors['alamat'] ?></div>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="no_hp" class="form-label">Nomor HP</label>
                <input type="text" class="form-control" id="no_hp" name="no_hp" value="<?= htmlspecialchars($no_hp) ?>">
                <?php if (isset($errors['no_hp'])): ?>
                    <div class="text-danger"><?= $errors['no_hp'] ?></div>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="photo" class="form-label">Foto</label>
                <input type="file" class="form-control" id="photo" name="photo">
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>

    <?php include_once("../footer.php"); ?>
</body>
</html>