<?php
include_once("config.php");

// cari koneksi mysqli yang tersedia
$mysqli = $mysqli ?? $GLOBALS['mysqli'] ?? null;
if (!$mysqli) {
    echo "Database connection not found. Periksa config.php. <a href='index_teknisi.php'>Kembali</a>";
    exit;
}

$errors = [];

// ambil record (GET prioritas, lalu POST)
$id = 0;
if (isset($_GET['id'])) $id = (int) $_GET['id'];
elseif (isset($_POST['id'])) $id = (int) $_POST['id'];

if ($id <= 0) { echo "Missing or invalid id. <a href='index_teknisi.php'>Back to list</a>"; exit; }

// pastikan kolom photo ada (jika belum, tambahkan)
$cols = [];
$resDesc = $mysqli->query("DESCRIBE teknisi");
if ($resDesc) {
    while ($r = $resDesc->fetch_assoc()) $cols[] = $r['Field'];
    $resDesc->free();
}
if (!in_array('photo', $cols)) {
    // tambahkan kolom photo (nullable)
    $mysqli->query("ALTER TABLE teknisi ADD COLUMN photo VARCHAR(255) NULL AFTER no_hp");
    // ignore error — next queries will fail if something wrong
}

// ambil data teknisi termasuk photo
$stmt = $mysqli->prepare("SELECT id, nama_teknisi, alamat, no_hp, IFNULL(photo,'') AS photo FROM teknisi WHERE id = ?");
if (!$stmt) { echo "Prepare failed: " . htmlspecialchars($mysqli->error); exit; }
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) { echo "Record not found. <a href='index_teknisi.php'>Kembali</a>"; $stmt->close(); exit; }
$user_data = $res->fetch_assoc();
$stmt->close();

$nama   = $user_data['nama_teknisi'];
$alamat = $user_data['alamat'];
$no_hp  = $user_data['no_hp'];
$currentPhoto = $user_data['photo'] ?: null;

// Handle update (termasuk upload foto)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id    = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $nama  = trim($_POST['nama'] ?? '');
    $alamat= trim($_POST['alamat'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');

    if ($id <= 0) $errors[] = "ID tidak valid.";
    if ($nama === '') $errors[] = "Nama wajib diisi.";
    if ($alamat === '') $errors[] = "Ruangan wajib diisi.";
    if ($no_hp === '') $errors[] = "No HP wajib diisi.";

    // proses upload foto bila ada
    $newPhoto = null;
    if (!empty($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $f = $_FILES['photo'];
        if ($f['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Upload error (kode {$f['error']}).";
        } else {
            // validasi ukuran 2MB
            if ($f['size'] > 2 * 1024 * 1024) {
                $errors[] = "Ukuran foto maksimal 2MB.";
            } else {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($f['tmp_name']);
                $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
                if (!isset($allowed[$mime])) {
                    $errors[] = "Tipe file tidak diperbolehkan. Gunakan JPG/PNG/GIF.";
                } else {
                    $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    $ext = $allowed[$mime];
                    try {
                        $base = bin2hex(random_bytes(8));
                    } catch (Exception $e) {
                        $base = uniqid();
                    }
                    $newPhoto = $base . '.' . $ext;
                    $dest = $uploadDir . DIRECTORY_SEPARATOR . $newPhoto;
                    if (!move_uploaded_file($f['tmp_name'], $dest)) {
                        $errors[] = "Gagal menyimpan foto.";
                        $newPhoto = null;
                    } else {
                        // optional: set permissions
                        @chmod($dest, 0644);
                    }
                }
            }
        }
    }

    if (empty($errors)) {
        // pastikan kolom photo ada sebelum update
        $cols = [];
        $resDesc2 = $mysqli->query("DESCRIBE teknisi");
        if ($resDesc2) {
            while ($r = $resDesc2->fetch_assoc()) $cols[] = $r['Field'];
            $resDesc2->free();
        }
        if (!in_array('photo', $cols)) {
            $mysqli->query("ALTER TABLE teknisi ADD COLUMN photo VARCHAR(255) NULL AFTER no_hp");
        }

        // jika upload berhasil, set nilai photo baru; jika tidak, gunakan currentPhoto
        $photoToSave = $newPhoto ?? $currentPhoto;

        $stmtUp = $mysqli->prepare("UPDATE teknisi SET nama_teknisi = ?, alamat = ?, no_hp = ?, photo = ? WHERE id = ?");
        if ($stmtUp) {
            $stmtUp->bind_param("ssssi", $nama, $alamat, $no_hp, $photoToSave, $id);
            if ($stmtUp->execute()) {
                // jika ada foto lama dan diganti, hapus file lama
                if ($newPhoto && $currentPhoto) {
                    $oldPath = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $currentPhoto;
                    if (is_file($oldPath)) @unlink($oldPath);
                }
                header("Location: index_teknisi.php");
                exit;
            } else {
                $errors[] = "Update gagal: " . htmlspecialchars($stmtUp->error);
                // hapus file baru jika gagal menyimpan ke DB
                if ($newPhoto) {
                    @unlink(__DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $newPhoto);
                }
            }
            $stmtUp->close();
        } else {
            $errors[] = "Prepare failed: " . htmlspecialchars($mysqli->error);
            if ($newPhoto) {
                @unlink(__DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $newPhoto);
            }
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Edit Teknisi — SIM RS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body{background:#f4fbfd}
    .brand { background: linear-gradient(90deg,#005b84,#00a8c6); color:#fff; padding:.6rem 1rem; border-radius:.6rem; display:inline-flex; gap:.6rem; align-items:center; }
    .card-accent{ border-left:4px solid #00a8c6; }
    .small-muted{ color:#6c757d; font-size:.95rem; }
    .preview { max-width:140px; max-height:140px; object-fit:cover; border-radius:8px; border:1px solid #e9eef1; }
  </style>
</head>
<body>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="d-flex align-items-center gap-3">
        <div class="brand"><i class="bi bi-heart-pulse-fill fs-4"></i></div>
        <div>
          <h5 class="mb-0">Edit Teknisi</h5>
          <div class="small-muted">Perbarui data teknisi Elektromedis</div>
        </div>
      </div>
      <div>
        <a href="index_teknisi.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
      </div>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <ul class="mb-0">
          <?php foreach ($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>'; ?>
        </ul>
      </div>
    <?php endif; ?>

    <div class="card card-accent shadow-sm">
      <div class="card-body">
        <form method="post" action="edit_teknisi.php" class="row g-3 needs-validation" novalidate enctype="multipart/form-data">
          <input type="hidden" name="id" value="<?php echo (int)$id; ?>">

          <div class="col-md-6">
            <label class="form-label">Nama Teknisi</label>
            <input type="text" name="nama" class="form-control" required value="<?php echo htmlspecialchars($nama); ?>">
            <div class="invalid-feedback">Nama wajib diisi.</div>
          </div>

          <div class="col-md-6">
            <label class="form-label">No HP</label>
            <input type="text" name="no_hp" class="form-control" required value="<?php echo htmlspecialchars($no_hp); ?>">
            <div class="invalid-feedback">No HP wajib diisi.</div>
          </div>

          <div class="col-12">
            <label class="form-label">Ruangan</label>
            <input type="text" name="alamat" class="form-control" required value="<?php echo htmlspecialchars($alamat); ?>">
            <div class="invalid-feedback">Ruangan wajib diisi.</div>
          </div>

          <div class="col-md-4">
            <label class="form-label">Foto Profil (opsional)</label>
            <input type="file" name="photo" id="photo" accept="image/*" class="form-control form-control-sm">
            <div class="form-text">JPG/PNG/GIF, maks 2MB. Unggah bila ingin mengganti foto.</div>
          </div>

          <div class="col-md-8 d-flex align-items-center">
            <?php if ($currentPhoto && is_file(__DIR__ . '/uploads/' . $currentPhoto)): ?>
              <img src="<?php echo 'uploads/' . rawurlencode($currentPhoto); ?>" alt="Foto" class="preview me-3" id="previewImg">
              <div>
                <div class="small text-muted">Foto saat ini</div>
                <button type="button" id="removePhoto" class="btn btn-sm btn-outline-danger mt-2">Hapus Foto</button>
              </div>
            <?php else: ?>
              <img src="" alt="Preview" class="preview me-3 d-none" id="previewImg">
              <div class="small text-muted">Belum ada foto</div>
            <?php endif; ?>
          </div>

          <div class="col-12 d-flex justify-content-end gap-2">
            <a href="index_teknisi.php" class="btn btn-outline-secondary">Batal</a>
            <button type="submit" name="update" class="btn btn-primary"><i class="bi bi-save me-1"></i> Update</button>
          </div>
        </form>
      </div>
    </div>

    <p class="text-center text-muted mt-3">© <?= date('Y'); ?> SIM RS — Elektromedis</p>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (function () {
      'use strict'
      var forms = document.querySelectorAll('.needs-validation')
      Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
          if (!form.checkValidity()) { event.preventDefault(); event.stopPropagation(); }
          form.classList.add('was-validated')
        }, false)
      })

      // preview client-side image
      const photoInput = document.getElementById('photo');
      const previewImg = document.getElementById('previewImg');
      if (photoInput && previewImg) {
        photoInput.addEventListener('change', (e) => {
          const f = e.target.files[0];
          if (!f) return;
          const reader = new FileReader();
          reader.onload = (ev) => {
            previewImg.src = ev.target.result;
            previewImg.classList.remove('d-none');
          };
          reader.readAsDataURL(f);
        });
      }

      // remove photo (client-side request: sets file input to empty and hides preview)
      const removeBtn = document.getElementById('removePhoto');
      if (removeBtn) {
        removeBtn.addEventListener('click', () => {
          if (confirm('Hapus foto profil saat ini? Setelah simpan, foto akan dihapus.')) {
            // To delete server-side, user must submit form without new photo.
            // We'll set a hidden flag by creating a hidden input named remove_photo and submit.
            let input = document.querySelector('input[name="remove_photo"]');
            if (!input) {
              input = document.createElement('input');
              input.type = 'hidden';
              input.name = 'remove_photo';
              input.value = '1';
              document.querySelector('form').appendChild(input);
            } else {
              input.value = '1';
            }
            // remove preview
            if (previewImg) previewImg.remove();
          }
        });
      }
    })()
  </script>
</body>
</html>