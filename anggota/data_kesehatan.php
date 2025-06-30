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

$loggedInUserId = $_SESSION['user_id'];

$anggotaData = []; 
$stmtAnggota = $conn->prepare("SELECT nama_anggota, jenis_kelamin_anggota, umur_anggota, no_hp_anggota FROM anggota WHERE id_anggota = ?");
$stmtAnggota->bind_param("i", $loggedInUserId);
$stmtAnggota->execute();
$resultAnggota = $stmtAnggota->get_result();

if ($resultAnggota->num_rows === 1) {
    $anggotaData = $resultAnggota->fetch_assoc();
}
$stmtAnggota->close();

$namaLengkap = $anggotaData['nama_anggota'] ?? 'N/A';
$jenisKelamin = $anggotaData['jenis_kelamin_anggota'] ?? 'N/A';
$umur = $anggotaData['umur_anggota'] ?? 'N/A';
$nomorTelepon = $anggotaData['no_hp_anggota'] ?? 'N/A';

$petugasList = [];
$stmtPetugas = $conn->prepare("SELECT id_petugas, nama_petugas FROM petugas_puskesmas ORDER BY nama_petugas ASC");
$stmtPetugas->execute();
$resultPetugas = $stmtPetugas->get_result();
while ($row = $resultPetugas->fetch_assoc()) {
    $petugasList[] = $row;
}
$stmtPetugas->close();

$message = ""; 
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_petugas_selected = $_POST['id_petugas'] ?? null;
    $tanggal = $_POST['tanggal'] ?? null;
    $tinggi_badan = $_POST['tinggi_badan'] ?? null;
    $berat_badan = $_POST['berat_badan'] ?? null;
    $lingkar_perut = $_POST['lingkar_perut'] ?? null;
    $lingkar_kepala = $_POST['lingkar_kepala'] ?? null;
    $tekanan_darah = $_POST['tekanan_darah'] ?? null;
    $konsultasi = $_POST['konsultasi'] ?? null;

    if (empty($id_petugas_selected) || empty($tanggal) || empty($tinggi_badan) || empty($berat_badan)) {
        $message = "Harap lengkapi semua data wajib (Petugas, Tanggal, Tinggi Badan, Berat Badan).";
        $messageType = "danger";
    } else {
        $stmtInsert = $conn->prepare("INSERT INTO pemeriksaan (id_anggota, id_petugas, tgl, tinggi_badan, berat_badan, lingkar_perut, lingkar_kepala, tekanan_darah, konsultasi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmtInsert->bind_param("iisddddss",
            $loggedInUserId,
            $id_petugas_selected,
            $tanggal,
            $tinggi_badan,
            $berat_badan,
            $lingkar_perut,
            $lingkar_kepala,
            $tekanan_darah,
            $konsultasi
        );

        if ($stmtInsert->execute()) {
            $message = "Data pemeriksaan berhasil disimpan!";
            $messageType = "success";
        } else {
            $message = "Gagal menyimpan data pemeriksaan: " . $stmtInsert->error;
            $messageType = "danger";
        }
        $stmtInsert->close();
    }
}

$conn->close(); 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Kesehatan - PosRem</title>
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
            margin-bottom: 2rem; 
        }
        .table {
            margin-top: 1rem;
        }
        .table td {
            padding: 0.75rem;
            vertical-align: top;
            border-top: 1px solid #dee2e6;
        }
        .btn-submit {
            background-color: #624FA2;
            color: white;
            border: 2px solid #624FA2;
            border-radius: 8px;
            padding: 8px 20px;
            cursor: pointer;
            margin-top: 2rem; 
        }
        .btn-submit:hover {
            background-color: #523F8A;
            border-color: #523F8A;
        }
        .dropdown-menu {
            max-height: 200px;
            overflow-y: auto;
        }
        .dropdown-item {
            cursor: pointer;
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
                    <a href="data_kesehatan.php" class="nav-link active"><i class="bi bi-clipboard-heart"></i> <span>Data Kesehatan</span></a>
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

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="history-box">
                    <h6 class="mb-3" style="color: #624FA2;">Informasi Diri Anggota</h6>
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
                                <td>Nomor Telepon</td>
                                <td><?php echo htmlspecialchars($nomorTelepon); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h6 class="mb-3 mt-5" style="color: #624FA2;">Form Data Kesehatan</h6>
                <form method="POST" action="">
                    <div class="row p-2 pt-5">
                        <div class="col-md-6">
                            <label for="dropdownMenu2" class="form-label">Pilih Petugas</label>
                            <div class="dropdown">
                                <button class="btn text-white dropdown-toggle" type="button" id="dropdownMenu2"
                                    data-bs-toggle="dropdown" aria-expanded="false"
                                    style="background-color: #624FA2; border-radius: 8px; width: 200px;" >
                                    <span id="selectedPetugasName">Pilih Petugas</span>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenu2">
                                    <?php foreach ($petugasList as $petugas): ?>
                                        <li><button class="dropdown-item" type="button" data-id="<?php echo $petugas['id_petugas']; ?>"><?php echo htmlspecialchars($petugas['nama_petugas']); ?></button></li>
                                    <?php endforeach; ?>
                                </ul>
                                <input type="hidden" name="id_petugas" id="idPetugasInput" required>
                            </div>
                        </div>
                    </div>
                    <div class="row p-2 pt-5">
                        <div class="col-md-6">
                            <label for="tinggi_badan" class="form-label">Tinggi Badan (cm)</label>
                            <input type="text" class="form-control" id="tinggi_badan" name="tinggi_badan" placeholder="Misal: 150.5" required>
                        </div>
                        <div class="col-md-6">
                            <label for="berat_badan" class="form-label">Berat Badan (kg)</label>
                            <input type="text" class="form-control" id="berat_badan" name="berat_badan" placeholder="Misal: 50.0" required>
                        </div>
                    </div>
                    <div class="row p-2 pt-5">
                        <div class="col-md-6">
                            <label for="lingkar_perut" class="form-label">Lingkar Perut (cm)</label>
                            <input type="text" class="form-control" id="lingkar_perut" name="lingkar_perut" placeholder="Misal: 65.5" required>
                        </div>
                        <div class="col-md-6">
                            <label for="lingkar_kepala" class="form-label">Lingkar Kepala (cm)</label>
                            <input type="text" class="form-control" id="lingkar_kepala" name="lingkar_kepala" placeholder="Misal: 60.0" required>
                        </div>
                    </div>
                    <div class="row p-2 pt-5">
                        <div class="col-md-6">
                            <label for="tekanan_darah" class="form-label">Tekanan Darah (mmHg)</label>
                            <input type="text" class="form-control" id="tekanan_darah" name="tekanan_darah" placeholder="Misal: 120/80" required>
                        </div>
                        <div class="col-md-6">
                            <label for="tanggal" class="form-label">Tanggal</label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" required>
                        </div>
                    </div>
                    <div class="row p-2 pt-5">
                        <div class="col">
                            <label for="konsultasi" class="form-label">Konsultasi</label>
                            <textarea class="form-control" id="konsultasi" name="konsultasi" rows="3" placeholder="Konsultasi Lainnya"></textarea>
                        </div>
                    </div>
                    <br><br>
                    <button class="btn btn-submit" type="submit">Simpan</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownItems = document.querySelectorAll('.dropdown-item');
            const selectedPetugasNameSpan = document.getElementById('selectedPetugasName');
            const idPetugasInput = document.getElementById('idPetugasInput');

            dropdownItems.forEach(item => {
                item.addEventListener('click', function() {
                    const petugasName = this.textContent;
                    const petugasId = this.getAttribute('data-id');

                    selectedPetugasNameSpan.textContent = petugasName;
                    idPetugasInput.value = petugasId;
                });
            });
        });
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</body>
</html>