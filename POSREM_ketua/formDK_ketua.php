<?php
session_start(); // Mulai sesi
date_default_timezone_set('Asia/Jakarta');

// Cek apakah pengguna sudah login
if (!isset($_SESSION["id_ketua"])) {
    header("Location: login.php"); // Alihkan ke halaman login jika belum login
    exit;
}

// Koneksi ke database
$servername = "localhost"; // Sesuaikan jika host database Anda berbeda
$username = "root";        // Ganti dengan username database Anda
$password = "";            // Ganti dengan password database Anda
$dbname = "posrem";

$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil id_ketua dari sesi
$id_ketua_logged_in = $_SESSION["id_ketua"];

// Fetch data Ketua yang sedang login
$nama_ketua_form = "N/A";
$jenis_kelamin_ketua_form = "N/A";
$umur_ketua_form = "N/A";
$no_hp_ketua_form = "N/A";
$usn_ketua_logged_in = "";

$sql_ketua_info = "SELECT nama_ketua, jenis_kelamin_ketua, umur_ketua, no_hp_ketua, usn_ketua FROM ketua_karang_taruna WHERE id_ketua = ?";
$stmt_ketua_info = $conn->prepare($sql_ketua_info);
if ($stmt_ketua_info === false) {
    die("Error preparing ketua info statement: " . $conn->error);
}
$stmt_ketua_info->bind_param("i", $id_ketua_logged_in);
$stmt_ketua_info->execute();
$result_ketua_info = $stmt_ketua_info->get_result();

if ($result_ketua_info->num_rows > 0) {
    $row_ketua_info = $result_ketua_info->fetch_assoc();
    $nama_ketua_form = $row_ketua_info['nama_ketua'];
    $jenis_kelamin_ketua_form = $row_ketua_info['jenis_kelamin_ketua'];
    $umur_ketua_form = $row_ketua_info['umur_ketua'];
    $no_hp_ketua_form = $row_ketua_info['no_hp_ketua'];
    $usn_ketua_logged_in = $row_ketua_info['usn_ketua'];
} else {
    echo "<script>alert('Informasi Ketua tidak ditemukan.'); window.location.href='logout.php';</script>";
    exit;
}
$stmt_ketua_info->close();

$action_message = "";

// Logika Menyimpan Data Kesehatan (POST request)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tinggi_badan = $_POST['tinggi_badan'] ?? '';
    $berat_badan = $_POST['berat_badan'] ?? '';
    $lingkar_kepala = $_POST['lingkar_kepala'] ?? '';
    $lingkar_perut = $_POST['lingkar_perut'] ?? '';
    $tekanan_darah = $_POST['tekanan_darah'] ?? '';
    $konsultasi = $_POST['konsultasi'] ?? '';
    $selected_petugas_id = $_POST['id_petugas'] ?? '';

    if (
        empty($tinggi_badan) || empty($berat_badan) || empty($lingkar_kepala) ||
        empty($lingkar_perut) || empty($tekanan_darah) || empty($selected_petugas_id)
    ) {
        $action_message = "<p style='color: red;'>Mohon lengkapi semua data wajib, termasuk memilih Petugas.</p>";
    } else {
        $id_petugas_pemeriksaan = $selected_petugas_id;
        $tgl_pemeriksaan = date('Y-m-d H:i:s');
        $id_anggota_for_pemeriksaan = NULL;

        $sql_insert_pemeriksaan = "INSERT INTO pemeriksaan (id_anggota, id_petugas, tgl, tinggi_badan, berat_badan, lingkar_kepala, lingkar_perut, tekanan_darah, konsultasi, id_ketua) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert_pemeriksaan = $conn->prepare($sql_insert_pemeriksaan);

        if ($stmt_insert_pemeriksaan === false) {
            $action_message = "<p style='color: red;'>Error preparing statement: " . $conn->error . "</p>";
        } else {
            $stmt_insert_pemeriksaan->bind_param(
                "iisddddssi",
                $id_anggota_for_pemeriksaan,
                $id_petugas_pemeriksaan,
                $tgl_pemeriksaan,
                $tinggi_badan,
                $berat_badan,
                $lingkar_kepala,
                $lingkar_perut,
                $tekanan_darah,
                $konsultasi,
                $id_ketua_logged_in
            );

            if ($stmt_insert_pemeriksaan->execute()) {
                echo "<script>alert('Data berhasil disimpan!'); window.location.href='dataKesehatan.php';</script>";
                exit;
            } else {
                $action_message = "<p style='color: red;'>Gagal menyimpan data kesehatan: " . $stmt_insert_pemeriksaan->error . "</p>";
            }
            $stmt_insert_pemeriksaan->close();
        }
    }
}

// Fetch daftar Petugas Puskesmas (untuk dropdown)
$daftar_petugas = [];
$sql_petugas_list = "SELECT id_petugas, nama_petugas FROM petugas_puskesmas ORDER BY nama_petugas ASC";
$result_petugas_list = $conn->query($sql_petugas_list);
if ($result_petugas_list) {
    while ($row_petugas = $result_petugas_list->fetch_assoc()) {
        $daftar_petugas[] = $row_petugas;
    }
} else {
    error_log("Error fetching petugas list: " . $conn->error);
}

// Untuk mempertahankan pilihan petugas di dropdown jika ada error validasi
$selected_petugas_id_prev = isset($_POST['id_petugas']) ? $_POST['id_petugas'] : '';


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Form Data Kesehatan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            background: #F2EBEF;
        }

        .d-flex {
            min-height: 100vh;
        }

        .sidebar {
            background-color: white;
            transition: all 0.4s cubic-bezier(0.25, 0.1, 0.25, 1);
            color: black;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
            min-height: 100%;
        }

        .sidebar.expanded {
            width: 250px;
        }

        .sidebar.collapsed {
            width: 90px;
        }

        .sidebar-toggle {
            border: none;
            background: none;
            width: 100%;
            text-align: center;
            padding: 1rem;
            cursor: pointer;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
        }

        .sidebar-logo img {
            min-width: 40px;
            width: 40px;
            height: 40px;
            object-fit: contain;
            transition: transform 0.4s ease;
        }

        .sidebar-logo-text {
            margin-left: 10px;
            font-weight: bold;
            font-size: 1.2rem;
            color: black;
            white-space: nowrap;
            transition: opacity 0.4s ease, width 0.4s ease, margin 0.4s ease;
            overflow: hidden;
        }

        .sidebar.collapsed .sidebar-logo-text {
            opacity: 0;
            width: 0;
            margin-left: 0;
        }

        .sidebar.expanded .sidebar-logo-text {
            opacity: 1;
            width: auto;
            margin-left: 10px;
        }

        .nav-link {
            color: black;
            display: flex;
            align-items: center;
            transition: all 0.4s ease;
            border-radius: 8px;
            margin-bottom: 5px;
            padding: 0.5rem;
        }

        .nav-link:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .nav-link:hover .sidebar-text {
            color: rgba(98, 81, 162, 1);
        }

        .sidebar.collapsed .nav-link {
            justify-content: center;
            padding: 0.5rem;
        }

        .nav-item img {
            min-width: 30px;
            width: 30px;
            height: 30px;
            object-fit: contain;
            transition: transform 0.4s ease;
            flex-shrink: 0;
        }

        .sidebar-text {
            transition: opacity 0.4s ease, width 0.4s ease, margin 0.4s ease;
            white-space: nowrap;
            overflow: hidden;
        }

        .sidebar.collapsed .sidebar-text {
            width: 0;
            opacity: 0;
            margin-left: 0;
        }

        .sidebar.expanded .sidebar-text {
            width: auto;
            opacity: 1;
            margin-left: 0.5rem;
        }

        .nav-link.active {
            background-color: transparent !important;
            color: rgba(98, 81, 162, 1);
        }

        .nav-link.active .sidebar-text {
            color: rgba(98, 81, 162, 1);
        }

        .content {
            transition: margin-left 0.4s cubic-bezier(0.25, 0.1, 0.25, 1);
            padding: 2rem;
            margin-left: 5px;
            flex-grow: 1;
            min-height: 100vh;
        }

        .content.collapsed {
            margin-left: 5px;
        }

        .btn-view {
            background-color: rgba(178, 124, 223, 1);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 0.5rem 1.5rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .btn-view:hover {
            background-color: rgba(161, 75, 218, 0.64);
        }

        .card {
            width: 100%;
            max-width: 100%;
        }

        .info-card {
            max-width: 1200px;
            padding: 2rem;
            width: 100%;
            margin: auto;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .info-card .card {
            padding: 2rem;
            border-radius: 15px;
        }

        .form-label {
            font-weight: bold;
            color: #555;
            margin-bottom: .5rem;
        }

        input.form-control,
        textarea.form-control,
        select.form-select {
            width: 100%;
            border-radius: 15px;
            padding: 0.75rem 1rem;
            border: 1px solid #ddd;
        }

        input.form-control:focus,
        textarea.form-control:focus,
        select.form-select:focus {
            border-color: #8A70D6;
            box-shadow: 0 0 0 0.25rem rgba(138, 112, 214, 0.25);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .info-card .row>div {
            font-size: 1rem;
            color: #000;
        }

        /* PERBAIKAN: CSS untuk button row */
        .button-row-form {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        .message-container {
            margin-top: 20px;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="d-flex">

        <div id="sidebar" class="sidebar expanded d-flex flex-column align-items-start p-3">
            <button class="sidebar-toggle" onclick="toggleSidebar()">
                <div class="sidebar-logo">
                    <img src="asset/logo_posrem.png" alt="Logo PosRem" width="40px">
                    <span class="sidebar-logo-text">PosRem</span>
                </div>
            </button>
            <ul class="nav nav-pills flex-column mt-2 w-100">
                <li class="nav-item mb-2">
                    <a href="dashboard_ketua.php" class="nav-link">
                        <img src="asset/logo_dasboard.png" alt="" width="30px">
                        <span class="sidebar-text">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="KT_ketua.php" class="nav-link">
                        <img src="asset/logo_KT.png" alt="" width="30px">
                        <span class="sidebar-text">Karang Taruna</span>
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="formDK_ketua.php" class="nav-link active">
                        <img src="asset/logo_data kesehatan.png" alt="" width="30px">
                        <span class="sidebar-text">Data Kesehatan</span>
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="pesanKesehatan.php" class="nav-link">
                        <img src="asset/logo_pesan kesehatan.png" alt="" width="30px">
                        <span class="sidebar-text">Pesan Kesehatan</span>
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="dataDiri.php" class="nav-link">
                        <img src="asset/logo_data diri.png" alt="" width="30px">
                        <span class="sidebar-text">Data Diri</span>
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="cetakLaporan.php" class="nav-link">
                        <img src="asset/logo_cetak laporan.png" alt="" width="30px">
                        <span class="sidebar-text">Cetak Laporan</span>
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="logout.php" class="nav-link">
                        <img src="asset/logo_keluar.png" alt="" width="30px">
                        <span class="sidebar-text">Keluar</span>
                    </a>
                </li>
            </ul>
        </div>

        <div id="main-content" class="content">
            <div class="card">
                <div class="card-body">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-12">
                                <div class="info-card card p-3">
                                    <div class="row">
                                        <h5><strong>Informasi Diri Ketua</strong></h5>
                                        <div class="col-6">
                                            <p>Nama Lengkap</p>
                                            <p>Jenis Kelamin</p>
                                            <p>Umur</p>
                                            <p>Nomor Telp</p>
                                        </div>

                                        <div class="col-6">
                                            <p><?php echo htmlspecialchars($nama_ketua_form); ?></p>
                                            <p><?php echo htmlspecialchars($jenis_kelamin_ketua_form); ?></p>
                                            <p><?php echo htmlspecialchars($umur_ketua_form); ?></p>
                                            <p><?php echo htmlspecialchars($no_hp_ketua_form); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="info-card card p-3 mt-4">
                                    <h5 class="mb-4"><strong>Form Data Kesehatan Saya</strong></h5>
                                    <?php if (!empty($action_message)): ?>
                                        <div class="message-container">
                                            <?php echo $action_message; ?>
                                        </div>
                                    <?php endif; ?>

                                    <form method="POST" action="">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="tinggi_badan" class="form-label">Tinggi Badan (cm)</label>
                                                <input type="number" step="0.1" class="form-control" id="tinggi_badan"
                                                    name="tinggi_badan" placeholder="e.g., 170.5" required
                                                    value="<?php echo isset($_POST['tinggi_badan']) ? htmlspecialchars($_POST['tinggi_badan']) : ''; ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="berat_badan" class="form-label">Berat Badan (kg)</label>
                                                <input type="number" step="0.1" class="form-control" id="berat_badan"
                                                    name="berat_badan" placeholder="e.g., 65.2" required
                                                    value="<?php echo isset($_POST['berat_badan']) ? htmlspecialchars($_POST['berat_badan']) : ''; ?>">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="lingkar_kepala" class="form-label">Lingkar Kepala
                                                    (cm)</label>
                                                <input type="number" step="0.1" class="form-control" id="lingkar_kepala"
                                                    name="lingkar_kepala" placeholder="e.g., 55.0" required
                                                    value="<?php echo isset($_POST['lingkar_kepala']) ? htmlspecialchars($_POST['lingkar_kepala']) : ''; ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="lingkar_perut" class="form-label">Lingkar Perut (cm)</label>
                                                <input type="number" step="0.1" class="form-control" id="lingkar_perut"
                                                    name="lingkar_perut" placeholder="e.g., 80.0" required
                                                    value="<?php echo isset($_POST['lingkar_perut']) ? htmlspecialchars($_POST['lingkar_perut']) : ''; ?>">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="tekanan_darah" class="form-label">Tekanan Darah
                                                    (mmHg)</label>
                                                <input type="text" class="form-control" id="tekanan_darah"
                                                    name="tekanan_darah" placeholder="e.g., 120/80" required
                                                    value="<?php echo isset($_POST['tekanan_darah']) ? htmlspecialchars($_POST['tekanan_darah']) : ''; ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="id_petugas" class="form-label">Pilih Petugas</label>
                                                <select class="form-select" id="id_petugas" name="id_petugas" required>
                                                    <option value="">-- Pilih Petugas --</option>
                                                    <?php foreach ($daftar_petugas as $petugas): ?>
                                                        <option
                                                            value="<?php echo htmlspecialchars($petugas['id_petugas']); ?>"
                                                            <?php echo ($selected_petugas_id_prev == $petugas['id_petugas']) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($petugas['nama_petugas']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="konsultasi" class="form-label">Konsultasi Lainnya</label>
                                            <textarea class="form-control" id="konsultasi" name="konsultasi" rows="4"
                                                placeholder="Tulis catatan atau saran konsultasi di sini..."><?php echo isset($_POST['konsultasi']) ? htmlspecialchars($_POST['konsultasi']) : ''; ?></textarea>
                                        </div>
                                        <div class="button-row-form"> <button type="button" class="btn-view"
                                                onclick="location.href='dataKesehatan.php'">Kembali</button>
                                            <button class="btn-view" type="submit">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            const sidebar = document.getElementById("sidebar");
            const content = document.getElementById("main-content");

            function toggleSidebar() {
                sidebar.classList.toggle("collapsed");
                sidebar.classList.toggle("expanded");
                content.classList.toggle("collapsed");
            }
        </script>

</body>

</html>