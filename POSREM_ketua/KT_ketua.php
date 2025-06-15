<?php
session_start();

if (!isset($_SESSION["id_ketua"])) {
  header("Location: login.php");
  exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "posrem";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Koneksi gagal: " . $conn->connect_error);
}

$id_ketua_logged_in = $_SESSION["id_ketua"];

$sql_get_id_kt = "SELECT id_kt FROM ketua_karang_taruna WHERE id_ketua = ?";
$stmt_get_id_kt = $conn->prepare($sql_get_id_kt);
if ($stmt_get_id_kt === false) {
  die("Error preparing id_kt statement: " . $conn->error);
}
$stmt_get_id_kt->bind_param("i", $id_ketua_logged_in);
$stmt_get_id_kt->execute();
$result_get_id_kt = $stmt_get_id_kt->get_result();

$id_kt_ketua = null;
if ($result_get_id_kt->num_rows > 0) {
  $row_kt_ketua = $result_get_id_kt->fetch_assoc();
  $id_kt_ketua = $row_kt_ketua['id_kt'];
}
$stmt_get_id_kt->close();

$nama_karang_taruna = "N/A";
$alamat_karang_taruna = "N/A";

if ($id_kt_ketua !== null) {
  $sql_get_karang_taruna = "SELECT nama_kt, alamat FROM karang_taruna WHERE id_kt = ?";
  $stmt_get_karang_taruna = $conn->prepare($sql_get_karang_taruna);
  if ($stmt_get_karang_taruna === false) {
    die("Error preparing karang taruna statement: " . $conn->error);
  }
  $stmt_get_karang_taruna->bind_param("i", $id_kt_ketua);
  $stmt_get_karang_taruna->execute();
  $result_karang_taruna = $stmt_get_karang_taruna->get_result();

  if ($result_karang_taruna->num_rows > 0) {
    $row_karang_taruna = $result_karang_taruna->fetch_assoc();
    $nama_karang_taruna = $row_karang_taruna['nama_kt'];
    $alamat_karang_taruna = $row_karang_taruna['alamat'];
  }
  $stmt_get_karang_taruna->close();
} else {
  $nama_karang_taruna = "Belum ada Karang Taruna terkait";
  $alamat_karang_taruna = "Silakan hubungi administrator";
}

$nama_ketua_sidebar = "Ketua";
$sql_nama_ketua = "SELECT nama_ketua FROM ketua_karang_taruna WHERE id_ketua = ?";
$stmt_nama_ketua = $conn->prepare($sql_nama_ketua);
if ($stmt_nama_ketua === false) {
  die("Error preparing nama ketua statement: " . $conn->error);
}
$stmt_nama_ketua->bind_param("i", $id_ketua_logged_in);
$stmt_nama_ketua->execute();
$result_nama_ketua = $stmt_nama_ketua->get_result();
if ($result_nama_ketua->num_rows > 0) {
  $row_nama_ketua = $result_nama_ketua->fetch_assoc();
  $nama_ketua_sidebar = $row_nama_ketua['nama_ketua'];
}
$stmt_nama_ketua->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Halaman Karang Taruna</title>
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

    .card {
      width: 100%;
      max-width: 100%;
      height: 100%;
    }

    .kt-card {
      background-color: #8A70D6;
      border-radius: 15px;
      padding: 2.5rem;
      padding-top: 2.5rem;
      color: white;
      margin-bottom: 2rem;
      min-height: 100px;
      max-width: 95%;
      margin: 0 auto;
      width: 100%;
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
      /* Mengubah ini untuk konsistensi */
    }

    h2 {
      font-size: 1.5rem;
      margin-top: 1.5rem;
      margin-left: 1.5rem;
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
          <a href="KT_ketua.php" class="nav-link active">
            <img src="asset/logo_KT.png" alt="" width="30px">
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
          <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#logoutConfirmModal">
            <img src="asset/logo_keluar.png" alt="" width="30px">
            <span class="sidebar-text">Keluar</span>
          </a>
        </li>
      </ul>
    </div>

    <div id="main-content" class="content">
      <div class="card">
        <div class="card-body">
          <h2>Halo, <?php echo htmlspecialchars($nama_ketua_sidebar); ?>!</h2> <br>

          <div class="kt-card">
            <h3><?php echo htmlspecialchars($nama_karang_taruna); ?></h3>
            <p><?php echo htmlspecialchars($alamat_karang_taruna); ?></p>
            <a href="dataAnggotaKT_ketua.php"><button class="btn-view">Lihat Data</button></a>
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