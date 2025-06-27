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

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'anggota' || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit; 
}

$loggedInUsername = $_SESSION['username']; 
$loggedInUserId = $_SESSION['user_id'];    

$anggotaData = null; 

$stmt = $conn->prepare("SELECT nama_anggota, usn_anggota FROM anggota WHERE id_anggota = ?");
$stmt->bind_param("i", $loggedInUserId); 
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $anggotaData = $result->fetch_assoc(); 
}
$stmt->close();
$displayName = $anggotaData['nama_anggota'] ?? $loggedInUsername;

$latestMessages = [];
$sqlLatestMessages = "
    SELECT pk.id_pesan, pk.pesan, pm.tgl AS tanggal, pp.nama_petugas
    FROM pesan_kesehatan pk
    JOIN pemeriksaan pm ON pk.id_pemeriksaan = pm.id_pemeriksaan
    JOIN petugas_puskesmas pp ON pm.id_petugas = pp.id_petugas
    WHERE pm.id_anggota = ?
    ORDER BY pk.id_pesan DESC
    LIMIT 2";

$stmtMessages = $conn->prepare($sqlLatestMessages);
$stmtMessages->bind_param("i", $loggedInUserId);
$stmtMessages->execute();
$resultMessages = $stmtMessages->get_result();
while ($row = $resultMessages->fetch_assoc()) {
    $latestMessages[] = $row;
}
$stmtMessages->close();

$historyItems = [];
$sqlHistory = "
    SELECT pk.id_pesan, pk.pesan, pm.tgl AS tanggal
    FROM pesan_kesehatan pk
    JOIN pemeriksaan pm ON pk.id_pemeriksaan = pm.id_pemeriksaan
    WHERE pm.id_anggota = ?
    ORDER BY pk.id_pesan DESC
    LIMIT 4";

$stmtHistory = $conn->prepare($sqlHistory);
$stmtHistory->bind_param("i", $loggedInUserId);
$stmtHistory->execute();
$resultHistory = $stmtHistory->get_result();
while ($row = $resultHistory->fetch_assoc()) {
    $historyItems[] = $row;
}
$stmtHistory->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Anggota - PosRem</title>
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
                <a href="dashboard.php" class="nav-link active"><i class="bi bi-grid-fill"></i> <span>Dashboard</span></a>
            </li>
            <li class="nav-item">
                <a href="data_kesehatan.php" class="nav-link"><i class="bi bi-clipboard-heart"></i> <span>Data Kesehatan</span></a>
            </li>
            <li class="nav-item">
                <a href="pesan_kesehatan.php" class="nav-link"><i class="bi bi-chat-dots"></i> <span>Pesan Kesehatan</span></a>
            </li>
            <li class="nav-item">
                <a href="data_diri_anggota.php" class="nav-link"><i class="bi bi-person-circle"></i> <span>Data Diri</span></a>
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
            <h4>Sudah cek kesehatan bulan ini?</h4>
            <h6>Jangan lupa laporan kesehatanmu ya!</h6>
            <a href="data_kesehatan.php" class="btn" style="background-color: #CE9CFF; border: none; padding: 0.5rem 1.5rem; color: white; border-radius: 20px; font-weight: 600; margin-top: 1rem; text-decoration: none;">Lapor</a>
        </div>

        <div class="history-box mt-4">
            <h6 class="mb-3" style="color: #624FA2;">Pesan Terbaru</h6>

            <?php if (empty($latestMessages)): ?>
                <p>Tidak ada pesan terbaru.</p>
            <?php else: ?>
                <?php foreach ($latestMessages as $message): ?>
                    <div class="history-item">
                        <div class="row">
                            <div class="col-12">
                                <small>Oleh: <?php echo htmlspecialchars($message['nama_petugas']); ?> | Tanggal: <?php echo date('d F Y', strtotime($message['tanggal'])); ?></small>
                                <p>Pesan Singkat: “<?php echo htmlspecialchars($message['pesan']); ?>”</p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        </div>
    </div>


    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</body>
</html>