<?php
session_start();

$host = "sql303.infinityfree.com";
$user = "if0_39241783";
$pass = "4WgW6ZbgMpbG";
$db = "if0_39241783_posrem";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'petugas_puskesmas' || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$allPemeriksaan = [];
$sqlAllPemeriksaan = "
    SELECT
        pm.id_pemeriksaan,
        pm.tgl,
        a.nama_anggota,
        pk.pesan AS pesan_petugas_ke_anggota,
        pp.nama_petugas
    FROM pemeriksaan pm
    JOIN anggota a ON pm.id_anggota = a.id_anggota
    LEFT JOIN pesan_kesehatan pk ON pk.id_pemeriksaan = pm.id_pemeriksaan
    LEFT JOIN petugas_puskesmas pp ON pk.id_petugas = pp.id_petugas
    ORDER BY pm.tgl DESC";

$stmtAllPemeriksaan = $conn->prepare($sqlAllPemeriksaan);
$stmtAllPemeriksaan->execute();
$resultAllPemeriksaan = $stmtAllPemeriksaan->get_result();
while ($row = $resultAllPemeriksaan->fetch_assoc()) {
    $allPemeriksaan[] = $row;
}
$stmtAllPemeriksaan->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pemeriksaan - PosRem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            background: #F2EBEF;
            font-family: 'Segoe UI', sans-serif;
            height: 100%;
        }
        .sidebar {
            height: 100vh;
            width: 250px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding-top: 2rem;
            position: sticky;
            top: 0;
        }
        .nav-link {
            color: black;
            font-weight: 500;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }
        .nav-link.active,
        .nav-link:hover {
            color: #624FA2;
        }
        .nav-link span {
            margin-left: 10px;
        }
        .brand {
            font-weight: bold;
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
        }
        .brand-icon {
            background-color: #A28DD0;
            border-radius: 12px;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 10px;
            font-size: 1rem;
        }
        .main-content {
            padding: 2rem;
            flex-grow: 1; /* Allow content to grow */
        }
        .card {
            border: none;
            border-radius: 16px;
        }
        .container-main { /* Use a distinct container for content */
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            margin: 0 auto; /* Center it */
        }
        h2 {
            color: #624FA2;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 0.5rem;
        }
        .btn-action-purple { /* New class for purple action buttons */
            background-color: #D3B4F0;
            color: white;
            font-size: 0.9rem;
            border: none;
            padding: 5px 15px;
            border-radius: 20px;
            text-decoration: none; /* For <a> tags */
        }
        .btn-action-purple:hover {
            background-color: #A28DD0;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <div class="sidebar p-4">
            <div class="brand">
                <div class="brand-icon"><i class="bi bi-heart-fill"></i></div>
                PosRem
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="dashboard_petugas.php" class="nav-link"><i class="bi bi-grid-fill"></i> <span>Dashboard</span></a>
                </li>
                <li class="nav-item">
                    <a href="data_pemeriksaan.php" class="nav-link active"><i class="bi bi-file-earmark-medical"></i> <span>Data Pemeriksaan</span></a>
                </li>
                <li class="nav-item">
                    <a href="logout_confirm.php" class="nav-link"><i class="bi bi-box-arrow-left"></i> <span>Keluar</span></a>
                </li>
            </ul>
        </div>

        <div class="main-content">
            <div class="container-main card p-5 rounded-4 shadow-sm">
                <h2 class="mb-4">Daftar Pemeriksaan Kesehatan</h2>

                <?php if (isset($_GET['error']) && $_GET['error'] == 'no_id'): ?>
                    <div class="alert alert-warning" role="alert">
                        ID pemeriksaan tidak ditemukan. Silakan pilih pemeriksaan dari daftar.
                    </div>
                <?php endif; ?>
                <?php if (isset($_GET['error']) && $_GET['error'] == 'data_not_found'): ?>
                    <div class="alert alert-danger" role="alert">
                        Data pemeriksaan tidak ditemukan.
                    </div>
                <?php endif; ?>

                <?php if (empty($allPemeriksaan)): ?>
                    <p>Tidak ada data pemeriksaan yang tersedia.</p>
                <?php else: ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID Pem.</th>
                                <th>Tanggal</th>
                                <th>Nama Anggota</th>
                                <th>Status Tanggapan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allPemeriksaan as $pemeriksaan): ?>
                                <tr>
                                    <td><?= htmlspecialchars($pemeriksaan['id_pemeriksaan']) ?></td>
                                    <td><?= date('d F Y', strtotime($pemeriksaan['tgl'])) ?></td>
                                    <td><?= htmlspecialchars($pemeriksaan['nama_anggota']) ?></td>
                                    <td>
                                        <?php if (!empty($pemeriksaan['pesan_petugas_ke_anggota'])): ?>
                                            <span class="badge bg-success">Sudah Ditanggapi</span>
                                            <?php if (!empty($pemeriksaan['nama_petugas'])): ?>
                                                <small class="text-muted">(<?= htmlspecialchars($pemeriksaan['nama_petugas']) ?>)</small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Belum Ditanggapi</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="detail_pemeriksaan.php?id=<?= htmlspecialchars($pemeriksaan['id_pemeriksaan']) ?>" class="btn-action-purple">
                                            <?php if (empty($pemeriksaan['pesan_petugas_ke_anggota'])): ?>
                                                Tanggapi
                                            <?php else: ?>
                                                Lihat Detail
                                            <?php endif; ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>