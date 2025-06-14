<?php
session_start(); // Mulai sesi

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

// Ambil id_ketua dari sesi (ini adalah ID Ketua yang sedang login)
$id_ketua_logged_in = $_SESSION["id_ketua"];

// 1. Fetch data Ketua yang sedang login untuk ditampilkan di "Informasi Diri"
$nama_ketua_display = "N/A";
$jenis_kelamin_ketua_display = "N/A";
$umur_ketua_display = "N/A";
$no_hp_ketua_display = "N/A";

$sql_ketua_info = "SELECT nama_ketua, jenis_kelamin_ketua, umur_ketua, no_hp_ketua FROM ketua_karang_taruna WHERE id_ketua = ?";
$stmt_ketua_info = $conn->prepare($sql_ketua_info);
if ($stmt_ketua_info === false) {
  die("Error preparing ketua info statement: " . $conn->error);
}
$stmt_ketua_info->bind_param("i", $id_ketua_logged_in);
$stmt_ketua_info->execute();
$result_ketua_info = $stmt_ketua_info->get_result();

if ($result_ketua_info->num_rows > 0) {
  $row_ketua_info = $result_ketua_info->fetch_assoc();
  $nama_ketua_display = $row_ketua_info['nama_ketua'];
  $jenis_kelamin_ketua_display = $row_ketua_info['jenis_kelamin_ketua'];
  $umur_ketua_display = $row_ketua_info['umur_ketua'];
  $no_hp_ketua_display = $row_ketua_info['no_hp_ketua'];
}
$stmt_ketua_info->close();

// 2. Fetch data kesehatan Ketua dari tabel 'pemeriksaan'
// Mencari data di mana id_anggota adalah NULL dan id_ketua cocok dengan Ketua yang login
$health_data = null; // Akan menyimpan data kesehatan terbaru jika ditemukan
$sql_health_data = "
    SELECT
        p.tinggi_badan,
        p.berat_badan,
        p.lingkar_kepala,
        p.lingkar_perut,
        p.tekanan_darah,
        p.konsultasi,
        pt.nama_petugas
    FROM
        pemeriksaan p
    LEFT JOIN
        petugas_puskesmas pt ON p.id_petugas = pt.id_petugas
    WHERE
        p.id_anggota IS NULL AND p.id_ketua = ?
    ORDER BY
        p.tgl DESC
    LIMIT 1"; // Ambil data paling baru

$stmt_health_data = $conn->prepare($sql_health_data);
if ($stmt_health_data === false) {
  die("Error preparing health data statement: " . $conn->error);
}
$stmt_health_data->bind_param("i", $id_ketua_logged_in);
$stmt_health_data->execute();
$result_health_data = $stmt_health_data->get_result();

if ($result_health_data->num_rows > 0) {
  $health_data = $result_health_data->fetch_assoc();
}
$stmt_health_data->close();

$conn->close(); // Tutup koneksi database
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Data Kesehatan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    html,
    body {
      height: 100%;
      /* Penting: html dan body harus mengambil tinggi penuh */
      margin: 0;
      padding: 0;
      overflow-x: hidden;
      background: #F2EBEF;
    }

    .d-flex {
      min-height: 100vh;
      /* Pastikan kontainer flex utama minimal setinggi viewport */
      /* Flex items di dalamnya akan memanjang secara default */
    }

    .sidebar {
      /* Hapus height: 100vh; agar sidebar memanjang mengikuti konten sibling */
      background-color: white;
      transition: all 0.4s cubic-bezier(0.25, 0.1, 0.25, 1);
      color: black;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      overflow-y: auto;
      /* Tetap izinkan scroll internal jika konten sidebar sendiri terlalu panjang */
      min-height: 100%;
      /* Agar sidebar tidak collapse jika kontennya pendek, tapi tetap mengikuti sibling */
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
      margin-right: 25px;
    }

    .btn-view:hover {
      background-color: rgba(161, 75, 218, 0.64);
    }

    .card {
      width: 100%;
      max-width: 100%;
      height: 100%;
    }

    .info-card {
      max-width: 1200px;
      padding: 2rem;
      width: 100%;
      margin: auto;
    }

    .info-card .card {
      padding: 2rem;
      border-radius: 15px;
    }

    .info-card .row>div {
      font-size: 1rem;
      color: #000;
    }

    .no-data-message {
      text-align: center;
      font-size: 1.2rem;
      color: #666;
      margin-top: 50px;
      padding: 20px;
      border: 1px dashed #8A70D6;
      border-radius: 10px;
      background-color: #f8f8f8;
    }

    .no-data-message a {
      color: #8A70D6;
      text-decoration: none;
      font-weight: bold;
    }

    .no-data-message a:hover {
      text-decoration: underline;
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
          <a href="dataKesehatan.php" class="nav-link active">
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
                <div class="info-card">
                  <div class="card p-3">
                    <div class="row">
                      <h5><strong>Informasi Diri</strong></h5>
                      <div class="col-6">
                        <p>Nama Lengkap</p>
                        <p>Jenis Kelamin</p>
                        <p>Umur</p>
                        <p>Nomor Telp</p>
                      </div>
                      <div class="col-6">
                        <p><?php echo htmlspecialchars($nama_ketua_display); ?></p>
                        <p><?php echo htmlspecialchars($jenis_kelamin_ketua_display); ?></p>
                        <p><?php echo htmlspecialchars($umur_ketua_display); ?></p>
                        <p><?php echo htmlspecialchars($no_hp_ketua_display); ?></p>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="info-card">
                  <div class="card p-3">
                    <h5><strong>Data Kesehatan</strong></h5>
                    <?php if ($health_data): // Jika ada data kesehatan ?>
                      <div class="row mt-3">
                        <div class="col-6">
                          <p>Tinggi Badan</p>
                          <p>Berat Badan</p>
                          <p>Lingkar Kepala</p>
                          <p>Lingkar Perut</p>
                          <p>Tekanan Darah</p>
                          <p>Nama Petugas yang Memeriksa</p>
                          <p>Konsultasi Lainnya</P>
                        </div>
                        <div class="col-6">
                          <p><?php echo htmlspecialchars($health_data['tinggi_badan']); ?> cm</p>
                          <p><?php echo htmlspecialchars($health_data['berat_badan']); ?> kg</p>
                          <p><?php echo htmlspecialchars($health_data['lingkar_kepala']); ?> cm</p>
                          <p><?php echo htmlspecialchars($health_data['lingkar_perut']); ?> cm</p>
                          <p><?php echo htmlspecialchars($health_data['tekanan_darah']); ?> mmHg</p>
                          <p><?php echo htmlspecialchars($health_data['nama_petugas'] ?: 'N/A'); ?></p>
                          <p><?php echo nl2br(htmlspecialchars($health_data['konsultasi'] ?: 'Tidak ada konsultasi.')); ?>
                          </p>
                        </div>
                      </div>
                      <br>
                    <?php else: // Jika tidak ada data kesehatan ?>
                      <div class="no-data-message">
                        <p>Belum ada data kesehatan tercatat.</p>
                        <p>Silakan <a href="formDK_ketua.php">mengisi data kesehatan</a> Anda.</p>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="text-end">
                  <a href="formDK_ketua.php"><button type="submit" class="btn-view">Edit Data Kesehatan</button></a>
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