<?php
include_once("config.php");

// hitung jumlah teknisi
$countRes = $mysqli->query("SELECT COUNT(*) AS c FROM teknisi");
$count = $countRes ? (int)$countRes->fetch_assoc()['c'] : 0;

// ambil data teknisi termasuk nama file foto (kolom photo nullable), urutkan A→Z default
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
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>SIM RS — Teknisi Elektromedis</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">

  <style>
    :root{
      --primary: #0b7fa6;
      --accent: #00c2d1;
      --muted: #6c757d;
      --card-bg: #ffffff;
    }
    html,body{height:100%}
    body{
      font-family:'Inter',system-ui,-apple-system,'Segoe UI',Roboto,Arial;
      /* biru gradien sebagai latar belakang */
      background:
        radial-gradient(900px 300px at 10% 10%, rgba(3,127,166,0.10), transparent),
        radial-gradient(700px 240px at 90% 85%, rgba(0,194,209,0.06), transparent),
        linear-gradient(180deg, #e6f7ff 0%, #cfeef9 45%, #b0e3f8 100%);
      color:#052f3a;
      -webkit-font-smoothing:antialiased;
      margin-bottom:40px;
    }

    /* header / brand */
    .brand {
      background: var(--card-bg);
      border-left:6px solid var(--accent);
      border-radius:12px;
      padding:12px 16px;
      box-shadow: 0 8px 24px rgba(15,56,64,0.06);
      display:flex; align-items:center; gap:12px;
    }
    .brand .logo {
      width:48px; height:48px; border-radius:10px;
      background: linear-gradient(180deg,var(--primary),var(--accent));
      color:#fff; display:grid; place-items:center;
      box-shadow: 0 6px 18px rgba(11,127,166,0.12);
    }
    .brand .title { font-weight:700; margin:0; font-size:1rem; }
    .brand .sub { margin:0; color:var(--muted); font-size:.88rem; }

    /* controls */
    .controls .btn { min-width:44px; }

    /* card / hero */
    .hero {
      margin-top:16px;
      background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(255,255,255,0.95));
      padding:14px;
      border-radius:12px;
      box-shadow: 0 14px 40px rgba(12,49,61,0.04);
      display:flex; gap:12px; align-items:center; justify-content:space-between;
    }
    .hero h1 { margin:0; font-size:1.15rem; font-weight:700; }
    .hero p { margin:0; color:var(--muted); }

    /* table */
    .table-wrap { max-height:58vh; overflow:auto; }
    table.table thead th { border-bottom:0; color:var(--muted); font-weight:600; font-size:.92rem; }
    .thumb { width:56px; height:56px; object-fit:cover; border-radius:8px; border:1px solid #e9eef1; }
    .avatar-placeholder { width:56px; height:56px; display:inline-grid; place-items:center; background:#f6fbfc; color:var(--muted); border-radius:8px; }
    tr:hover { background: linear-gradient(90deg, rgba(11,127,166,0.02), transparent); }

    /* responsive */
    @media (max-width:768px) {
      .hero { flex-direction:column; align-items:flex-start; gap:10px; }
      .controls { width:100%; display:flex; gap:8px; flex-wrap:wrap; }
    }

    /* small helper */
    .muted-sm { color:var(--muted); font-size:.85rem; }
  </style>
</head>
<body>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="brand">
        <div class="logo"><i class="bi bi-hospital-fill fs-4"></i></div>
        <div>
          <p class="title">SIM RS — Daftar Teknisi Elektromedis</p>
          <p class="sub">Manajemen Teknisi & Perawatan</p>
        </div>
      </div>

      <div class="d-flex gap-2 controls">
        <a href="add_teknisi.php" class="btn btn-primary btn-sm"><i class="bi bi-person-plus me-1"></i> Tambah</a>
        <a href="index_teknisi.php" class="btn btn-outline-secondary btn-sm">Refresh</a>
      </div>
    </div>

    <div class="hero">
      <div>
        <h1>Data Teknisi Elektromedis</h1>
        <p class="muted-sm">Profesional, ringkas, dan siap dipakai di lingkungan rumah sakit.</p>
      </div>

      <div class="d-flex align-items-center gap-3">
        <div class="text-center pe-3">
          <div style="font-size:1.25rem;font-weight:700;color:var(--primary)"><?= $count; ?></div>
          <div class="muted-sm">Teknisi terdaftar</div>
        </div>

        <div class="input-group" style="min-width:260px;">
          <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
          <input id="search" class="form-control form-control-sm" type="search" placeholder="Cari nama atau ruangan">
          <button id="clearSearch" class="btn btn-outline-secondary btn-sm" type="button"><i class="bi bi-x-lg"></i></button>
        </div>

        <div class="btn-group" role="group" aria-label="sort">
          <button id="sortBtn" class="btn btn-sm btn-outline-primary" title="Urutkan A→Z"><i class="bi bi-sort-alpha-down"></i> A→Z</button>
        </div>
      </div>
    </div>

    <div class="card card-body mt-4 p-3 shadow-sm">
      <div class="table-wrap">
        <table id="teknisiTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width:5%;">#</th>
              <th style="width:72px;">Foto</th>
              <th>Nama</th>
              <th>Ruangan</th>
              <th>No HP</th>
              <th style="width:18%;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $i = 1;
            while ($row = $result->fetch_assoc()) {
                $photo = trim($row['photo'] ?? '');
                $photoPath = $photo ? __DIR__ . '/uploads/' . $photo : '';
                // for client-side sorting: add data-name attribute
                $nameAttr = htmlspecialchars(mb_strtolower($row['nama'], 'UTF-8'));
                echo '<tr data-name="' . $nameAttr . '">';
                echo '<td>' . $i . '</td>';
                echo '<td>';
                if ($photo && is_file($photoPath)) {
                    echo '<img src="uploads/' . rawurlencode($photo) . '" alt="Foto" class="thumb">';
                } else {
                    echo '<div class="avatar-placeholder"><i class="bi bi-person-circle fs-4"></i></div>';
                }
                echo '</td>';
                echo '<td><strong>' . htmlspecialchars($row['nama']) . '</strong></td>';
                echo '<td class="text-muted">' . htmlspecialchars($row['alamat']) . '</td>';
                echo '<td class="text-success">' . htmlspecialchars($row['no_hp']) . '</td>';
                echo '<td class="action-btns">';
                echo '<a class="btn btn-sm btn-outline-primary me-1" href="edit_teknisi.php?id=' . urlencode($row['id']) . '" title="Edit"><i class="bi bi-pencil"></i></a>';
                echo '<a class="btn btn-sm btn-outline-danger" href="delete.php?id=' . urlencode($row['id']) . '" onclick="return confirm(\'Hapus data ini?\')" title="Hapus"><i class="bi bi-trash"></i></a>';
                echo '</td>';
                echo '</tr>';
                $i++;
            }
            $result->free();
            ?>
          </tbody>
        </table>
      </div>
    </div>

    <footer class="text-center mt-4">
      <small class="text-muted">© <?= date('Y'); ?> SIM RS — Elektromedis</small>
    </footer>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (function(){
      const input = document.getElementById('search');
      const clearBtn = document.getElementById('clearSearch');
      const tbody = document.querySelector('#teknisiTable tbody');
      const sortBtn = document.getElementById('sortBtn');
      let asc = true; // current sort direction

      function filter() {
        const q = input ? input.value.trim().toLowerCase() : '';
        Array.from(tbody.rows).forEach(row => {
          const text = row.textContent.toLowerCase();
          row.style.display = q === '' || text.indexOf(q) !== -1 ? '' : 'none';
        });
      }

      function sortRows() {
        const rows = Array.from(tbody.querySelectorAll('tr'));
        rows.sort((a,b) => {
          const na = a.getAttribute('data-name') || a.cells[2].textContent.toLowerCase();
          const nb = b.getAttribute('data-name') || b.cells[2].textContent.toLowerCase();
          if (na < nb) return asc ? -1 : 1;
          if (na > nb) return asc ? 1 : -1;
          return 0;
        });
        rows.forEach(r => tbody.appendChild(r));
        sortBtn.innerHTML = asc ? '<i class="bi bi-sort-alpha-down"></i> A→Z' : '<i class="bi bi-sort-alpha-up"></i> Z→A';
      }

      if (input) {
        input.addEventListener('input', filter);
        clearBtn && clearBtn.addEventListener('click', () => { input.value=''; filter(); input.focus(); });
      }

      if (sortBtn) {
        sortBtn.addEventListener('click', () => { asc = !asc; sortRows(); });
      }

      // initial sort (ensure consistent order)
      sortRows();
    })();
  </script>
</body>
</html>