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

// Ambil id_ketua dari sesi
$id_ketua_logged_in = $_SESSION["id_ketua"];

// Fetch semua data Ketua yang sedang login, termasuk nama Karang Taruna
$ketua_data = null;
$sql_ketua_data = "
    SELECT
        k.nama_ketua,
        k.jenis_kelamin_ketua,
        k.tempat_tanggal_lahir, -- Kolom baru
        k.umur_ketua,
        k.no_hp_ketua,
        k.alamat_rumah, -- Kolom baru
        kt.nama_kt -- Nama Karang Taruna
    FROM
        ketua_karang_taruna k
    LEFT JOIN
        karang_taruna kt ON k.id_kt = kt.id_kt
    WHERE
        k.id_ketua = ?";
$stmt_ketua_data = $conn->prepare($sql_ketua_data);
if ($stmt_ketua_data === false) {
  die("Error preparing ketua data statement: " . $conn->error);
}
$stmt_ketua_data->bind_param("i", $id_ketua_logged_in);
$stmt_ketua_data->execute();
$result_ketua_data = $stmt_ketua_data->get_result();

if ($result_ketua_data->num_rows > 0) {
  $ketua_data = $result_ketua_data->fetch_assoc();
} else {
  // Jika data Ketua tidak ditemukan di DB meskipun sudah login (kasus jarang)
  echo "<script>alert('Data profil Ketua tidak ditemukan.'); window.location.href='logout.php';</script>";
  exit;
}
$stmt_ketua_data->close();
$conn->close(); // Tutup koneksi database
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Data Diri</title>
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
    }

    .btn-view:hover {
      background-color: rgba(161, 75, 218, 0.64);
    }

    .card {
      width: 100%;
      max-width: 100%;
      height: 100%;
    }

    h3 {
      color: #8A70D6;
    }

    .tulisanUngu {
      color: #8A70D6;
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
          <a href="dataDiri.php" class="nav-link active">
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
          <div class="container">
            <div class="row justify-content-center">
              <div class="col-12">
                <div class="info-card">
                  <div class="card p-3">
                    <div class="row">
                      <h3><strong>Data Diri</strong></h3>
                      <br>
                      <div class="col-6">
                        <div class="tulisanUngu">
                          <p>Nama Lengkap</p>
                          <p>Jenis Kelamin</p>
                          <p>Tempat, Tanggal Lahir</p>
                          <p>Umur</p>
                          <p>Karang Taruna</p>
                          <p>Nomor Telp</p>
                          <p>Alamat Rumah</p>
                        </div>
                      </div>

                      <div class="col-6">
                        <p><?php echo htmlspecialchars($ketua_data['nama_ketua']); ?></p>
                        <p><?php echo htmlspecialchars($ketua_data['jenis_kelamin_ketua']); ?></p>
                        <p><?php echo htmlspecialchars($ketua_data['tempat_tanggal_lahir'] ?: 'Belum diisi'); ?></p>
                        <p><?php echo htmlspecialchars($ketua_data['umur_ketua']); ?></p>
                        <p><?php echo htmlspecialchars($ketua_data['nama_kt'] ?: 'Belum terdaftar'); ?></p>
                        <p><?php echo htmlspecialchars($ketua_data['no_hp_ketua']); ?></p>
                        <p><?php echo htmlspecialchars($ketua_data['alamat_rumah'] ?: 'Belum diisi'); ?></p>
                      </div>
                    </div>
                  </div>
                  <br>
                  <div class="text-end">
                    <a href="formEditDataDiri.php?id_ketua=<?php echo htmlspecialchars($id_ketua_logged_in); ?>"><button
                        class="btn-view">Edit</button></a>
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