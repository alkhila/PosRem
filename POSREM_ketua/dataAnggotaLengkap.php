<?php
session_start();

if (!isset($_SESSION["id_ketua"])) {
  header("Location: login.php");
  exit;
}

if (!isset($_GET['id_anggota']) || !is_numeric($_GET['id_anggota'])) {
  echo "<script>alert('ID Anggota tidak valid atau tidak ditemukan.'); window.location.href='KT_ketua.php';</script>";
  exit;
}

$id_anggota_yang_dipilih = $_GET['id_anggota'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "posrem";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Koneksi gagal: " . $conn->connect_error);
}

$sql_anggota_detail = "SELECT nama_anggota, jenis_kelamin_anggota, umur_anggota, no_hp_anggota, usn_anggota, pass_anggota FROM anggota WHERE id_anggota = ?";
$stmt_anggota_detail = $conn->prepare($sql_anggota_detail);

if ($stmt_anggota_detail === false) {
  die("Error preparing statement: " . $conn->error);
}

$stmt_anggota_detail->bind_param("i", $id_anggota_yang_dipilih);
$stmt_anggota_detail->execute();
$result_anggota_detail = $stmt_anggota_detail->get_result();

$data_anggota = null;
if ($result_anggota_detail->num_rows > 0) {
  $data_anggota = $result_anggota_detail->fetch_assoc();
} else {
  echo "<script>alert('Data anggota tidak ditemukan.'); window.location.href='KT_ketua.php';</script>";
  exit;
}

$stmt_anggota_detail->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Detail Data Anggota</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      margin: 0;
      padding: 0;
      overflow-x: hidden;
      background: #F2EBEF;
    }

    .sidebar {
      height: 100vh;
      background-color: white;
      transition: all 0.4s cubic-bezier(0.25, 0.1, 0.25, 1);
      color: black;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      overflow-y: auto;
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
      height: 100%;
    }

    .info-card {
      max-width: 1200px;
      width: 100%;
      margin: auto;
      margin-bottom: 20px;
      margin-top: 10px;
    }

    .info-card .card {
      padding: 2rem;
      border-radius: 15px;
    }

    form input.form-control {
      width: 100%;
      border-radius: 15px;
    }

    .info-card .row>div {
      font-size: 1rem;
      color: #000;
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
          <a href="KT_ketua.php" class="nav-link active"> <img src="asset/logo_KT.png" alt="" width="30px">
            <span class="sidebar-text">Karang Taruna</span>
          </a>
        </li>
        <li class="nav-item mb-2">
          <a href="dataKesehatan.php" class="nav-link">
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
          <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#logoutConfirmModal"> <img
              src="asset/logo_keluar.png" alt="" width="30px">
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
                        <p><?php echo htmlspecialchars($data_anggota['nama_anggota']); ?></p>
                        <p><?php echo htmlspecialchars($data_anggota['jenis_kelamin_anggota']); ?></p>
                        <p><?php echo htmlspecialchars($data_anggota['umur_anggota']); ?></p>
                        <p><?php echo htmlspecialchars($data_anggota['no_hp_anggota']); ?></p>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="info-card">
                  <div class="card p-3">
                    <div class="row">
                      <h5><strong>Informasi Akun</strong></h5>
                      <div class="col-6">
                        <p>Username</p>
                        <p>Password</p>
                      </div>

                      <div class="col-6">
                        <p><?php echo htmlspecialchars($data_anggota['usn_anggota']); ?></p>
                        <p><?php echo htmlspecialchars($data_anggota['pass_anggota']); ?></p>
                      </div>
                    </div>
                  </div>
                  <br>
                </div>
                <div class="info-card" style="margin-top">
                  <div class="d-flex justify-content-start">
                    <a href="dataAnggotaKT_ketua.php"><button class="btn-view"
                        style="margin-top: 180px;">Kembali</button></a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
      const sidebar = document.getElementById("sidebar");
      const content = document.getElementById("main-content");

      function toggleSidebar() {
        sidebar.classList.toggle("collapsed");
        sidebar.classList.toggle("expanded");
        content.classList.toggle("collapsed");
      }
    </script>
    <?php include '_logout_modal.php'; ?>
</body>

</html>