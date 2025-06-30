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

$loggedInPetugasId = $_SESSION['user_id']; 

$id_pemeriksaan = $_GET['id'] ?? null;
if (!$id_pemeriksaan) {
    header("Location: data_pemeriksaan.php?error=no_id");
    exit();
}

$sql = "
SELECT
    p.*, -- This includes p.tgl (tanggal pemeriksaan)
    a.nama_anggota, a.jenis_kelamin_anggota AS jk_anggota, a.umur_anggota AS umur_anggota, a.no_hp_anggota AS telepon_anggota,
    k.nama_ketua, k.jenis_kelamin_ketua AS jk_ketua, k.umur_ketua AS umur_ketua, k.no_hp_ketua AS telepon_ketua,
    pk.pesan AS pesan_petugas,
    -- Removed pk.tgl AS tgl_pesan from here
    pp.nama_petugas AS nama_petugas_tanggapan
FROM pemeriksaan p
LEFT JOIN anggota a ON p.id_anggota = a.id_anggota
LEFT JOIN ketua_karang_taruna k ON p.id_ketua = k.id_ketua
LEFT JOIN pesan_kesehatan pk ON p.id_pemeriksaan = pk.id_pemeriksaan
LEFT JOIN petugas_puskesmas pp ON pk.id_petugas = pp.id_petugas
WHERE p.id_pemeriksaan = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_pemeriksaan);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close(); 

if (!$data) {
    header("Location: data_pemeriksaan.php?error=data_not_found");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pesan = trim($_POST['pesan'] ?? ''); 

    if (!empty($data['pesan_petugas'])) { 
        $stmt_update = $conn->prepare("UPDATE pesan_kesehatan SET pesan = ?, id_petugas = ? WHERE id_pemeriksaan = ?");
        $stmt_update->bind_param("sii", $pesan, $loggedInPetugasId, $id_pemeriksaan);
        $success = $stmt_update->execute();
        $stmt_update->close();
    } else {
        $stmt_insert = $conn->prepare("INSERT INTO pesan_kesehatan (id_pemeriksaan, pesan, id_petugas) VALUES (?, ?, ?)");
        $stmt_insert->bind_param("isi", $id_pemeriksaan, $pesan, $loggedInPetugasId);
        $success = $stmt_insert->execute();
        $stmt_insert->close();
    }

    if ($success) {
        header("Location: detail_pemeriksaan.php?id=$id_pemeriksaan&status=success");
    } else {
        header("Location: detail_pemeriksaan.php?id=$id_pemeriksaan&status=error");
    }
    exit();
}

$nama = $data['nama_anggota'] ?: $data['nama_ketua'];
$jk = $data['jk_anggota'] ?: $data['jk_ketua'];
$umur = $data['umur_anggota'] ?: $data['umur_ketua'];
$telepon = $data['telepon_anggota'] ?: $data['telepon_ketua'];

$displayNama = $nama ?? 'N/A';
$displayJK = $jk ?? 'N/A';
$displayUmur = $umur ?? 'N/A';
$displayTelepon = $telepon ?? 'N/A';
$displayTglPemeriksaan = $data['tgl'] ? date('d F Y', strtotime($data['tgl'])) : 'N/A';

$displayTinggiBadan = $data['tinggi_badan'] ?? 'N/A';
$displayBeratBadan = $data['berat_badan'] ?? 'N/A';
$displayLingkarPerut = $data['lingkar_perut'] ?? 'N/A';
$displayLingkarKepala = $data['lingkar_kepala'] ?? 'N/A';
$displayTekananDarah = $data['tekanan_darah'] ?? 'N/A';
$displayKonsultasiLainnya = $data['konsultasi'] ?? 'Tidak ada';
$displayPesanPetugas = $data['pesan_petugas'] ?? ''; 
$displayNamaPetugasTanggapan = $data['nama_petugas_tanggapan'] ?? '';

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
            overflow-x: hidden;
            flex-grow: 1; /* Added flex-grow to ensure it takes available space */
        }
        .card {
            border: none;
            border-radius: 16px;
            background-color: white; /* Explicitly set background for clarity */
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
        .history-box { /* Renamed for clarity, it's essentially a card content block */
            background-color: white; /* This will be within .card, so it might be redundant depending on specific design */
            border: 2px solid #C8AFE8;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem; /* Add margin to separate sections */
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
        .btn-submit { /* Consolidated button style */
            background-color: #624FA2; /* Changed to a primary purple */
            color: white;
            font-size: 0.9rem;
            border: none;
            padding: 10px 20px; /* Adjusted padding */
            border-radius: 8px; /* Slightly less rounded for a modern look */
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .btn-submit:hover {
            background-color: #533B8E; /* Darker shade on hover */
        }
        .form-control {
            border-radius: 8px; /* Match button radius */
            border: 1px solid #C8AFE8; /* Consistent border color */
            padding: 10px 15px;
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

        <div class="main-content flex-grow-1">
            <div class="card p-5 rounded-4 shadow-sm">
                <?php if (isset($_GET['status'])): ?>
                    <?php if ($_GET['status'] == 'success'): ?>
                        <div class="alert alert-success" role="alert">
                            Tanggapan berhasil disimpan!
                        </div>
                    <?php elseif ($_GET['status'] == 'error'): ?>
                        <div class="alert alert-danger" role="alert">
                            Terjadi kesalahan saat menyimpan tanggapan.
                        </div>
                    <?php elseif ($_GET['status'] == 'empty_message'): ?>
                        <div class="alert alert-warning" role="alert">
                            Pesan tanggapan tidak boleh kosong.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <div class="history-box">
                    <h6 class="mb-3" style="color: #624FA2;">Informasi Diri</h6>
                    <table class="table">
                        <tbody>
                            <tr><td>Nama Lengkap</td><td><?= htmlspecialchars($displayNama) ?></td></tr>
                            <tr><td>Jenis Kelamin</td><td><?= htmlspecialchars($displayJK) ?></td></tr>
                            <tr><td>Umur</td><td><?= htmlspecialchars($displayUmur) ?></td></tr>
                            <tr><td>Nomor Telepon</td><td><?= htmlspecialchars($displayTelepon) ?></td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="history-box">
                    <h6 class="mb-3" style="color: #624FA2;">Data Kesehatan</h6>
                    <table class="table">
                        <tbody>
                            <tr><td>Tanggal Pemeriksaan</td><td><?= $displayTglPemeriksaan ?></td></tr>
                            <tr><td>Tinggi Badan</td><td><?= htmlspecialchars($displayTinggiBadan) ?> cm</td></tr>
                            <tr><td>Berat Badan</td><td><?= htmlspecialchars($displayBeratBadan) ?> kg</td></tr>
                            <tr><td>Lingkar Perut</td><td><?= htmlspecialchars($displayLingkarPerut) ?> cm</td></tr>
                            <tr><td>Lingkar Kepala</td><td><?= htmlspecialchars($displayLingkarKepala) ?> cm</td></tr>
                            <tr><td>Tekanan Darah</td><td><?= htmlspecialchars($displayTekananDarah) ?></td></tr>
                            <tr><td>Konsultasi Lainnya</td><td><?= htmlspecialchars($displayKonsultasiLainnya) ?></td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="history-box">
                    <h6 class="mb-3" style="color: #624FA2;">Tanggapan Petugas</h6>
                    <form method="POST">
                        <div class="mb-3">
                            <textarea class="form-control" id="pesan" name="pesan" rows="4" placeholder="Berikan tanggapan atau saran kesehatan..."><?= htmlspecialchars($displayPesanPetugas) ?></textarea>
                        </div>
                        <div class="d-flex justify-content-between align-items-center"> <?php if ($displayPesanPetugas && $displayNamaPetugasTanggapan): ?>
                                <small class="text-muted">Terakhir ditanggapi oleh: <?= htmlspecialchars($displayNamaPetugasTanggapan) ?></small>
                            <?php endif; ?>
                            <button type="submit" class="btn-submit">Kirim Tanggapan</button>
                        </div>
                    </form>
                </div>

                <div class="mt-4"> <a href="data_pemeriksaan.php" class="btn btn-submit px-4">Kembali</a>
                </div>
            </div>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</body>
</html>