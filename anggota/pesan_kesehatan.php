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

$pesanKesehatanList = [];
$stmtPesan = $conn->prepare("
    SELECT
        pk.id_pesan,
        pk.pesan,
        p.tgl,
        p.id_pemeriksaan,
        pp.nama_petugas AS nama_petugas_pengirim -- Ambil nama petugas pengirim
    FROM pesan_kesehatan pk
    JOIN pemeriksaan p ON pk.id_pemeriksaan = p.id_pemeriksaan
    JOIN petugas_puskesmas pp ON p.id_petugas = pp.id_petugas -- Join dengan tabel petugas_puskesmas
    WHERE p.id_anggota = ?
    ORDER BY p.tgl DESC, pk.id_pesan DESC
");
$stmtPesan->bind_param("i", $loggedInUserId);
$stmtPesan->execute();
$resultPesan = $stmtPesan->get_result();

while ($row = $resultPesan->fetch_assoc()) {
    $pesanKesehatanList[] = $row;
}
$stmtPesan->close();

$conn->close(); 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pesan Kesehatan - PosRem</title>
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
                    <a href="data_kesehatan.php" class="nav-link"><i class="bi bi-clipboard-heart"></i> <span>Data Kesehatan</span></a>
                </li>
                <li class="nav-item">
                    <a href="pesan_kesehatan.php" class="nav-link active"><i class="bi bi-chat-dots"></i> <span>Pesan Kesehatan</span></a>
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

                <div class="history-box mt-4">
                    <h6 class="mb-3" style="color: #624FA2;">Pesan Terbaru</h6>

                    <?php if (empty($pesanKesehatanList)): ?>
                        <p class="text-center text-muted">Tidak ada pesan kesehatan terbaru.</p>
                    <?php else: ?>
                        <?php foreach ($pesanKesehatanList as $pesan): ?>
                            <div class="history-item">
                                <div class="row">
                                    <div class="col-10">
                                        <div class="history-code">
                                            Kode Pemeriksaan: <?php echo htmlspecialchars($pesan['id_pemeriksaan']); ?>
                                        </div>
                                        <small>
                                            Oleh: <?php echo htmlspecialchars($pesan['nama_petugas_pengirim']); ?> |
                                            Tanggal: <?php echo htmlspecialchars(date('d F Y', strtotime($pesan['tgl']))); ?>
                                        </small>
                                        <p class="mb-0">Pesan Singkat: “<?php echo htmlspecialchars(substr($pesan['pesan'], 0, 100)); ?><?php echo (strlen($pesan['pesan']) > 100) ? '...' : ''; ?>”</p>
                                    </div>
                                    <div class="col-2 text-end">
                                        <button class="btn-pesan"
                                                data-bs-toggle="modal"
                                                data-bs-target="#pesanModal"
                                                data-id_pemeriksaan="<?php echo htmlspecialchars($pesan['id_pemeriksaan']); ?>"
                                                data-nama_petugas="<?php echo htmlspecialchars($pesan['nama_petugas_pengirim']); ?>"
                                                data-tanggal="<?php echo htmlspecialchars(date('d F Y', strtotime($pesan['tgl']))); ?>"
                                                data-pesan_lengkap="<?php echo htmlspecialchars($pesan['pesan']); ?>">
                                            Lihat Pesan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="pesanModal" tabindex="-1" aria-labelledby="pesanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pesanModalLabel">Detail Pesan Kesehatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Kode Pemeriksaan:</strong> <span id="modalIdPemeriksaan"></span></p>
                    <p><strong>Dari Petugas:</strong> <span id="modalNamaPetugas"></span></p>
                    <p><strong>Tanggal Pemeriksaan:</strong> <span id="modalTanggal"></span></p>
                    <hr>
                    <p><strong>Isi Pesan:</strong></p>
                    <p id="modalPesanLengkap"></p>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JavaScript untuk mengisi konten modal
        document.addEventListener('DOMContentLoaded', function() {
            var pesanModal = document.getElementById('pesanModal');
            pesanModal.addEventListener('show.bs.modal', function (event) {
                // Tombol yang memicu modal
                var button = event.relatedTarget;

                // Ambil data dari atribut data-* tombol
                var idPemeriksaan = button.getAttribute('data-id_pemeriksaan');
                var namaPetugas = button.getAttribute('data-nama_petugas');
                var tanggal = button.getAttribute('data-tanggal');
                var pesanLengkap = button.getAttribute('data-pesan_lengkap');

                // Dapatkan elemen-elemen di dalam modal
                var modalIdPemeriksaan = pesanModal.querySelector('#modalIdPemeriksaan');
                var modalNamaPetugas = pesanModal.querySelector('#modalNamaPetugas');
                var modalTanggal = pesanModal.querySelector('#modalTanggal');
                var modalPesanLengkap = pesanModal.querySelector('#modalPesanLengkap');

                // Isi konten modal dengan data yang diambil
                modalIdPemeriksaan.textContent = idPemeriksaan;
                modalNamaPetugas.textContent = namaPetugas;
                modalTanggal.textContent = tanggal;
                modalPesanLengkap.textContent = pesanLengkap; // textContent aman untuk teks biasa
            });
        });
    </script>
</body>
</html>