<?php
session_start();

$host = "sql303.infinityfree.com";
$user = "if0_39241783";
$pass = "4WgW6ZbgMpbG";
$db = "if0_39241783_posrem";

$conn = new mysqli($host, $user, $pass, $db);

// Cek koneksi database
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'anggota' || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit; 
}

$loggedInUserId = $_SESSION['user_id'];

$anggotaData = [];

$stmt = $conn->prepare("
    SELECT
        a.nama_anggota,
        a.jenis_kelamin_anggota,
        a.umur_anggota,
        a.no_hp_anggota,
        kt.nama_kt     -- PERBAIKAN DI SINI: Menggunakan nama_kt
    FROM anggota a
    LEFT JOIN karang_taruna kt ON a.id_kt = kt.id_kt
    WHERE a.id_anggota = ?
");
$stmt->bind_param("i", $loggedInUserId); 
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $anggotaData = $result->fetch_assoc(); 
}
$stmt->close();
$conn->close(); 

$namaLengkap = $anggotaData['nama_anggota'] ?? 'N/A';
$jenisKelamin = $anggotaData['jenis_kelamin_anggota'] ?? 'N/A';
$umur = $anggotaData['umur_anggota'] ?? 'N/A';
$nomorTelepon = $anggotaData['no_hp_anggota'] ?? 'N/A';
$namaKarangTaruna = $anggotaData['nama_kt'] ?? 'N/A'; 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Diri Anggota - PosRem</title>
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
            overflow-x: hidden;
        }
        .card {
            border: none;
            border-radius: 16px;
        }
        .history-box {
            background-color: white;
            border: 2px solid #C8AFE8;
            border-radius: 16px;
            padding: 1.5rem;
        }
        .table {
            margin-top: 1rem;
        }
        .table td {
            padding: 0.75rem;
            vertical-align: top;
            border-top: 1px solid #dee2e6;
        }
        .btn-custom-purple {
            background-color: #624FA2;
            color: white;
            border: none;
        }
        .btn-custom-purple:hover {
            background-color: #523F8A;
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
                    <a href="dashboard.php" class="nav-link"><i class="bi bi-grid-fill"></i> <span>Dashboard</span></a>
                </li>
                <li class="nav-item">
                    <a href="data_kesehatan.php" class="nav-link"><i class="bi bi-clipboard-heart"></i> <span>Data Kesehatan</span></a>
                </li>
                <li class="nav-item">
                    <a href="pesan_kesehatan.php" class="nav-link"><i class="bi bi-chat-dots"></i> <span>Pesan Kesehatan</span></a>
                </li>
                <li class="nav-item">
                    <a href="data_diri_anggota.php" class="nav-link active"><i class="bi bi-person-circle"></i> <span>Data Diri</span></a>
                </li>
                <li class="nav-item">
                <a href="logout_confirm.php" class="nav-link"><i class="bi bi-box-arrow-left"></i> <span>Keluar</span></a>
            </li>
            </ul>
        </div>

        <div class="main-content flex-grow-1">
            <div class="card p-5 rounded-4 shadow-sm" style="background-color: white;">
                <div class="history-box mt-4">
                    <h6 class="mb-3" style="color: #624FA2;">Data Diri</h6>

                    <table class="table">
                        <tbody>
                            <tr>
                                <td>Nama Lengkap</td>
                                <td><?php echo htmlspecialchars($namaLengkap); ?></td>
                            </tr>
                            <tr>
                                <td>Jenis Kelamin</td>
                                <td><?php echo htmlspecialchars($jenisKelamin); ?></td>
                            </tr>
                            <tr>
                                <td>Umur</td>
                                <td><?php echo htmlspecialchars($umur); ?></td>
                            </tr>
                            <tr>
                                <td>Karang Taruna</td>
                                <td><?php echo htmlspecialchars($namaKarangTaruna); ?></td>
                            </tr>
                            <tr>
                                <td>Nomor Telepon</td>
                                <td><?php echo htmlspecialchars($nomorTelepon); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="text-end p-3">
                    <a href="edit_data_anggota.php?id_anggota=<?php echo htmlspecialchars($loggedInUserId); ?>" 
                    class="btn btn-custom-purple px-4 rounded-pill">
                        Edit
                    </a>
                </div>

            </div>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</body>
</html>