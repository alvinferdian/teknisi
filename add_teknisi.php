<?php
// Simple add form with bootstrap styling and server-side messages

// attempt to find mysqli instance from config.php
include_once("config.php");
$db = $mysqli ?? $GLOBALS['mysqli'] ?? null;

$errors = [];
$success = '';

$old = ['nama'=>'','alamat'=>'','no_hp'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Submit'])) {
    $old['nama'] = trim($_POST['nama'] ?? '');
    $old['alamat'] = trim($_POST['alamat'] ?? '');
    $old['no_hp'] = trim($_POST['no_hp'] ?? '');

    if (!$db) {
        $errors[] = "Database connection not found. Check config.php.";
    } else {
        if ($old['nama'] === '') $errors[] = "Nama wajib diisi.";
        if ($old['alamat'] === '') $errors[] = "Ruangan wajib diisi.";
        if ($old['no_hp'] === '') $errors[] = "No HP wajib diisi.";

        // handle uploaded profile photo (optional)
        $photoFilename = null;
        if (!empty($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $f = $_FILES['photo'];
            if ($f['error'] !== UPLOAD_ERR_OK) {
                $errors[] = "Upload error (kode {$f['error']}).";
            } else {
                // validate size (max 2MB)
                if ($f['size'] > 2 * 1024 * 1024) {
                    $errors[] = "Ukuran foto maksimal 2MB.";
                } else {
                    // validate mime/extension
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->file($f['tmp_name']);
                    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
                    if (!isset($allowed[$mime])) {
                        $errors[] = "Tipe file tidak diperbolehkan. Gunakan JPG/PNG/GIF.";
                    } else {
                        // prepare uploads dir
                        $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
                        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                        // unique filename
                        $ext = $allowed[$mime];
                        $base = bin2hex(random_bytes(8));
                        $photoFilename = $base . '.' . $ext;
                        $dest = $uploadDir . DIRECTORY_SEPARATOR . $photoFilename;
                        if (!move_uploaded_file($f['tmp_name'], $dest)) {
                            $errors[] = "Gagal menyimpan foto.";
                            $photoFilename = null;
                        }
                    }
                }
            }
        }

        if (empty($errors)) {
            // ensure 'photo' column exists; add if missing
            $cols = [];
            $res = $db->query("DESCRIBE teknisi");
            if ($res) {
                while ($r = $res->fetch_assoc()) $cols[] = $r['Field'];
                $res->free();
            } else {
                $errors[] = "Table 'teknisi' not found: " . htmlspecialchars($db->error);
            }

            if (empty($errors)) {
                if (!in_array('photo', $cols)) {
                    // add nullable photo column
                    if (!$db->query("ALTER TABLE teknisi ADD COLUMN photo VARCHAR(255) NULL AFTER no_hp")) {
                        $errors[] = "Gagal menambahkan kolom photo: " . htmlspecialchars($db->error);
                    }
                }
            }
        }

        if (empty($errors)) {
            if ($photoFilename) {
                $stmt = $db->prepare("INSERT INTO teknisi (nama_teknisi, alamat, no_hp, photo) VALUES (?, ?, ?, ?)");
            } else {
                $stmt = $db->prepare("INSERT INTO teknisi (nama_teknisi, alamat, no_hp) VALUES (?, ?, ?)");
            }

            if (!$stmt) {
                $errors[] = "Prepare failed: " . htmlspecialchars($db->error);
            } else {
                if ($photoFilename) {
                    $stmt->bind_param("ssss", $old['nama'], $old['alamat'], $old['no_hp'], $photoFilename);
                } else {
                    $stmt->bind_param("sss", $old['nama'], $old['alamat'], $old['no_hp']);
                }

                if ($stmt->execute()) {
                    $success = "Teknisi berhasil ditambahkan.";
                    $old = ['nama'=>'','alamat'=>'','no_hp'=>''];
                } else {
                    $errors[] = "Insert failed: " . htmlspecialchars($stmt->error);
                    // remove uploaded file on DB failure
                    if ($photoFilename) {
                        @unlink(__DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $photoFilename);
                    }
                }
                $stmt->close();
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
  <title>Tambah Teknisi — SIM RS</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">

  <style>
    :root{
      --bg1: #0b7fa6;
      --bg2: #00c2d1;
      --card: #ffffff;
      --muted: #6c757d;
    }

    html,body{height:100%}
    body{
      font-family:'Inter',system-ui,-apple-system,'Segoe UI',Roboto,Arial;
      background:
        radial-gradient(800px 300px at 10% 10%, rgba(11,127,166,0.10), transparent),
        linear-gradient(180deg, #e6f7ff 0%, #cfeef9 45%, #b0e3f8 100%);
      color:#042a33;
      -webkit-font-smoothing:antialiased;
      padding-bottom:40px;
    }

    .container-custom {
      max-width:980px;
      margin:0 auto;
      padding-top:28px;
    }

    .brand-bar{
      background: linear-gradient(90deg,var(--bg1),var(--bg2));
      color:#fff;
      padding:.8rem 1rem;
      border-radius:.75rem;
      display:flex;
      align-items:center;
      gap:12px;
      box-shadow: 0 10px 30px rgba(3,60,80,0.08);
    }
    .brand-bar .logo {
      width:48px; height:48px; border-radius:10px;
      background: rgba(206, 221, 211, 0.12);
      display:grid; place-items:center; color:#fff;
    }
    .card-med{
      border-radius:12px;
      background:var(--card);
      box-shadow: 0 8px 28px rgba(6,40,50,0.06);
      border-left:6px solid rgba(0,194,209,0.18);
      overflow:hidden;
    }

    .preview {
      max-width:140px; max-height:140px; object-fit:cover;
      border-radius:10px; border:1px solid #e9eef1;
      box-shadow: 0 6px 18px rgba(7,45,58,0.06);
      background:#fff;
    }

    .muted-sm { color:var(--muted); font-size:.9rem; }

    /* responsive tweaks */
    @media (max-width:576px){
      .brand-bar { padding:.6rem .8rem; gap:8px; }
      .container-custom { padding:12px; }
    }
  </style>
</head>
<body>
  <div class="container-custom">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="brand-bar">
        <div class="logo"><i class="bi bi-hospital-fill fs-4"></i></div>
        <div>
          <h5 class="mb-0">Tambah Teknisi</h5>
          <div class="muted-sm">Tambahkan teknisi baru untuk unit Elektromedis</div>
        </div>
      </div>
      <div>
        <a href="index_teknisi.php" class="btn btn-outline-light btn-sm" style="background:transparent;border:1px solid rgba(243, 232, 232, 0.08);color:#fff"><i class="bi bi-arrow-left"></i> Kembali</a>
      </div>
    </div>

    <?php if ($success): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success); ?> <a href="index_teknisi.php" class="alert-link">Lihat daftar</a></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul></div>
    <?php endif; ?>

    <div class="card-med mb-4">
      <div class="card-body">
        <form method="post" action="add_teknisi.php" class="row g-3 needs-validation" novalidate enctype="multipart/form-data">
          <div class="col-md-8">
            <label class="form-label">Nama Teknisi</label>
            <input type="text" name="nama" class="form-control form-control-lg" required value="<?php echo htmlspecialchars($old['nama']); ?>">
            <div class="invalid-feedback">Nama wajib diisi.</div>
          </div>

          <div class="col-md-4">
            <label class="form-label">Foto Profil (opsional)</label>
            <input type="file" name="photo" id="photo" accept="image/*" class="form-control form-control-sm">
            <div class="form-text">JPG/PNG/GIF, maks 2MB.</div>
          </div>

          <div class="col-12 col-md-8">
            <label class="form-label">Ruangan</label>
            <input type="text" name="alamat" class="form-control" required value="<?php echo htmlspecialchars($old['alamat']); ?>">
            <div class="invalid-feedback">Ruangan wajib diisi.</div>
          </div>
          <div class="col-12 col-md-4 d-flex flex-column align-items-start">
            <label class="form-label">Preview Foto</label>
            <div class="w-100 d-flex align-items-center gap-3">
              <img id="preview" src="" alt="Preview" class="preview d-none" />
              <div class="text-muted small">Tidak ada foto</div>
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label">No HP</label>
            <input type="text" name="no_hp" class="form-control" required value="<?php echo htmlspecialchars($old['no_hp']); ?>">
            <div class="invalid-feedback">No HP wajib diisi.</div>
          </div>

          <div class="col-12 d-flex justify-content-end gap-2">
            <a href="index_teknisi.php" class="btn btn-outline-secondary">Batal</a>
            <button type="submit" name="Submit" class="btn btn-dark" style="background:linear-gradient(90deg,var(--bg1),var(--bg2));border:0"><i class="bi bi-person-plus me-1"></i> Tambah</button>
          </div>
        </form>
      </div>
    </div>

    <p class="text-center text-muted mt-2">© <?= date('Y'); ?> SIM RS — Elektromedis</p>
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
      const previewImg = document.getElementById('preview');
      photoInput && photoInput.addEventListener('change', (e) => {
        const f = e.target.files[0];
        if (!f) {
          previewImg.src = ''; previewImg.classList.add('d-none'); return;
        }
        const reader = new FileReader();
        reader.onload = (ev) => {
          previewImg.src = ev.target.result;
          previewImg.classList.remove('d-none');
          // hide "Tidak ada foto" text
          const txt = previewImg.closest('.w-100').querySelector('.small');
          if (txt) txt.style.display = 'none';
        };
        reader.readAsDataURL(f);
      });
    })()
  </script>
</body>
</html>
//2025byalvin