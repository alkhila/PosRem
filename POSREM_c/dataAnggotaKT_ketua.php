<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Data Anggota Karang Taruna</title>
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
    }

    .content.collapsed {
      margin-left: 5px;
    }

    .card {
      width: 100%;
      max-width: 100%;
      height: 100%;
    }

    .btn-view {
      background-color: rgba(178, 124, 223, 1);
      color: white;
      border: none;
      border-radius: 50px;
      padding: 0.5rem 1rem;
      font-size: 1rem;
      transition: all 0.3s ease;
    }

    .btn-view:hover {
      background-color: rgba(161, 75, 218, 0.64);
    }

    .button-container {
      display: flex;
      justify-content: flex-end;
      margin-top: 2rem;
      margin-right: 1rem;
    }

    .wrapper-table {
      border: 2px solid #8A70D6;
      padding: 2rem;
      padding-top: 2rem;
      margin-bottom: 2rem;
      min-height: 100px;
      max-width: 97.5%;
      margin: 0 auto;
      width: 100%;
      border-radius: 20px;
      overflow: hidden;
    }

    .table-purple {
      border-collapse: collapse;
      width: 100%;
    }

    .table-purple td,
    .table-purple th {
      border-top: 1px solid black;
      border-bottom: 1px solid black;
      border-left: none;
      border-right: none;
      padding: 5px;
      text-align: center;
    }

    .table-purple thead th {
      border: none;
      text-align: center;
      font-weight: bold;
      color: #8A70D6;
    }

    .table-purple tbody td {
      padding-top: 1rem;
      padding-bottom: 1rem;
    }

    .table-purple tbody tr:first-child td {
      border-top: none;
    }

    .table-purple tbody tr:last-child td {
      border-bottom: none;
    }
  </style>

  </style>
</head>

<body>
  <div class="d-flex">

    <div id="sidebar" class="sidebar expanded d-flex flex-column align-items-start p-3">
      <!-- Sidebar -->
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
          <a href="#" class="nav-link">
            <img src="asset/logo_data kesehatan.png" alt="" width="30px">
            <span class="sidebar-text">Data Kesehatan</span>
          </a>
        </li>
        <li class="nav-item mb-2">
          <a href="#" class="nav-link">
            <img src="asset/logo_pesan kesehatan.png" alt="" width="30px">
            <span class="sidebar-text">Pesan Kesehatan</span>
          </a>
        </li>
        <li class="nav-item mb-2">
          <a href="#" class="nav-link">
            <img src="asset/logo_data diri.png" alt="" width="30px">
            <span class="sidebar-text">Data Diri</span>
          </a>
        </li>
        <li class="nav-item mb-2">
          <a href="#" class="nav-link">
            <img src="asset/logo_cetak laporan.png" alt="" width="30px">
            <span class="sidebar-text">Cetak Laporan</span>
          </a>
        </li>
        <li class="nav-item mb-2">
          <a href="#" class="nav-link">
            <img src="asset/logo_keluar.png" alt="" width="30px">
            <span class="sidebar-text">Keluar</span>
          </a>
        </li>
      </ul>
    </div>

    <!-- Konten utama -->
    <div id="main-content" class="content">

      <div class="card">
        <div class="card-body">

          <div class="container mt-4">
            <div class="wrapper-table">
              <table class="table-purple">
                <thead>
                  <tr>
                    <th class="fw-bold text-center pb-2 border-bottom border-black" style="color: #8A70D6;">
                      Nama</th>
                    <th class="fw-bold text-center pb-2 border-bottom border-black" style="color: #8A70D6;">
                      Jenis Kelamin</th>
                    <th class="fw-bold text-center pb-2 border-bottom border-black" style="color: #8A70D6; ">
                      Umur</th>
                    <th class="fw-bold text-center pb-2 border-bottom border-black" style="color: #8A70D6; ">
                      No Telp</th>
                    <th colspan="3" class="fw-bold text-start pb-2 border-bottom border-black" style="color: #8A70D6; ">
                      Akun</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td style="width: 20%;">Levi Ackerman</td>
                    <td style="width: 20%;">L</td>
                    <td style="width: 20%;">18</td>
                    <td style="width: 22%;">0895403587100</td>
                    <td style="width: 7%;"><a href=""><img src="asset/icon_akun.png" alt=""></a></td>
                    <td style="width: 6%;"><a href=""><img src="asset/icon_delete.png" alt=""></a></td>
                    <td style="width: 5%;"><a href=""><img src="asset/icon_edit.png" alt=""></a></td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="button-container">
              <button class="btn-view">Tambah</button>
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