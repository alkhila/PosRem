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
    exit;
}

$loggedInUsername = $_SESSION['username'];
$loggedInUserId = $_SESSION['user_id']; 
$petugasData = null; 

// Fetch petugas_puskesmas data
$stmt = $conn->prepare("SELECT nama_petugas, usn_petugas FROM petugas_puskesmas WHERE id_petugas = ?");
$stmt->bind_param("i", $loggedInUserId); 
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $petugasData = $result->fetch_assoc();
}
$stmt->close();

$displayName = $petugasData['nama_petugas'] ?? $loggedInUsername;

$latestReports = [];
$sqlLatestReports = "
    SELECT
        pm.id_pemeriksaan,
        pm.tgl,
        a.nama_anggota,
        pk.pesan AS pesan_petugas_ke_anggota
    FROM pemeriksaan pm
    JOIN anggota a ON pm.id_anggota = a.id_anggota
    LEFT JOIN pesan_kesehatan pk ON pk.id_pemeriksaan = pm.id_pemeriksaan
    ORDER BY pm.tgl DESC
    LIMIT 4";

$stmtReports = $conn->prepare($sqlLatestReports);
$stmtReports->execute();
$resultReports = $stmtReports->get_result();
while ($row = $resultReports->fetch_assoc()) {
    $latestReports[] = $row;
}
$stmtReports->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Petugas Puskesmas - PosRem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
        }
        .card {
            border: none;
            border-radius: 16px;
        }
        .greeting-card {
            background: linear-gradient(to right, #8E6DB7, #B79EDB);
            border-radius: 16px;
            padding: 2.5rem;
            color: white;
            margin-bottom: 2rem;
        }
        .greeting-card button {
            background-color: #CE9CFF;
            border: none;
            padding: 0.5rem 1.5rem;
            color: white;
            border-radius: 20px;
            font-weight: 600;
            margin-top: 1rem;
        }
        .history-box {
            background-color: white;
            border: 2px solid #C8AFE8;
            border-radius: 16px;
            padding: 1.5rem;
        }
        .history-item {
            border-top: 1px solid #ddd;
            padding: 1rem 0;
        }
        .history-item:first-child {
            border-top: none;
        }
        .history-code {
            color: #5D3FA0;
            font-weight: 600;
        }
        .btn-pesan {
            background-color: #D3B4F0;
            color: white;
            font-size: 0.9rem;
            border: none;
            padding: 5px 15px;
            border-radius: 20px;
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
                    <a href="dashboard_petugas.php" class="nav-link active"><i class="bi bi-grid-fill"></i> <span>Dashboard</span></a>
                </li>
                <li class="nav-item">
                    <a href="data_pemeriksaan.php" class="nav-link"><i class="bi bi-file-earmark-medical"></i> <span>Data Pemeriksaan</span></a>
                </li>
                <li class="nav-item">
                    <a href="logout_confirm.php" class="nav-link"><i class="bi bi-box-arrow-left"></i> <span>Keluar</span></a>
                </li>
            </ul>
        </div>

        <div class="main-content flex-grow-1">
            <div class="card p-5 rounded-4 shadow-sm" style="background-color: white;">
                <h2 class="mb-4">Halo, <?php echo htmlspecialchars($displayName); ?>!</h2>

                <div class="greeting-card">
                    <h4>Ada pemeriksaan terbaru yang perlu ditinjau?</h4>
                    <h6>Cek data pemeriksaan dan berikan tanggapan segera!</h6>
                    <a href="data_pemeriksaan.php" class="btn" style="background-color: #CE9CFF; border: none; padding: 0.5rem 1.5rem; color: white; border-radius: 20px; font-weight: 600; margin-top: 1rem; text-decoration: none;">Tinjau Pemeriksaan</a>
                </div>

                <div class="history-box mt-4">
                    <h6 class="mb-3" style="color: #624FA2;">Riwayat Pemeriksaan Terbaru</h6>

                    <?php if (empty($latestReports)): ?>
                        <p>Tidak ada riwayat pemeriksaan terbaru.</p>
                    <?php else: ?>
                        <?php foreach ($latestReports as $report): ?>
                          <div class="history-item">
                              <div class="row">
                                  <div class="col-10">
                                      <div class="history-code">ID Pemeriksaan: <?= htmlspecialchars($report['id_pemeriksaan']) ?></div>
                                      <small>Anggota: <?= htmlspecialchars($report['nama_anggota']) ?> | Tanggal: <?= date('d F Y', strtotime($report['tgl'])) ?></small>
                                      <?php if (!empty($report['pesan_petugas_ke_anggota'])): ?>
                                          <p class="text-success mb-0">✔ Sudah ditanggapi: "<?= htmlspecialchars($report['pesan_petugas_ke_anggota']) ?>"</p>
                                      <?php else: ?>
                                          <p class="text-warning mb-0">❌ Belum ditanggapi</p>
                                      <?php endif; ?>
                                  </div>
                                  <div class="col-2 d-flex align-items-center justify-content-end">
                                      <?php if (empty($report['pesan_petugas_ke_anggota'])): ?>
                                          <div class="col-2 d-flex align-items-center justify-content-end">
                                              <a href="detail_pemeriksaan.php?id=<?php echo htmlspecialchars($report['id_pemeriksaan']); ?>" class="btn-pesan">Tanggapi</a>
                                          </div>
                                      <?php else: ?>
                                          <a href="detail_pemeriksaan.php?id=<?= $report['id_pemeriksaan'] ?>" class="btn btn-sm btn-outline-secondary">Lihat</a>
                                      <?php endif; ?>
                                  </div>
                              </div>
                          </div>
                      <?php endforeach; ?>

                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</body>
</html>