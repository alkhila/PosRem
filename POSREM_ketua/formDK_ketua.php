<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Form Data Kesehatan</title>
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
      background-color: rgba(255, 255, 255, 0.5);
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
    }

    form input.form-control {
      width: 100%;
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
          <a href="KT_ketua.php" class="nav-link">
            <img src="asset/logo_KT.png" alt="" width="30px">
            <span class="sidebar-text">Karang Taruna</span>
          </a>
        </li>
        <li class="nav-item mb-2">
          <a href="#" class="nav-link active">
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
          <div class="container">
            <div class="row justify-content-center">
              <div class="col-12">
                <div class="info-card">
                  <div class="card p-3">
                    <div class="row">
                      <h5><strong>Informasi Diri</strong></h5>
                      <!-- Kolom 1: Label -->
                      <div class="col-6">
                        <p>Nama Lengkap</p>
                        <p>Jenis Kelamin</p>
                        <p>Umur</p>
                        <p>Nomor Telp</p>
                      </div>

                      <!-- Kolom 2: Nilai -->
                      <div class="col-6">
                        <p>Levi</p>
                        <p>Perempuan</p>
                        <p>18</p>
                        <p>0987654321</p>
                      </div>
                    </div>
                  </div>

                  <!-- Form Input Data Kesehatan -->
                  <br>
                  <form>
                    <div class="row mb-3">
                      <div class="col-md-6">
                        <label for="tinggi" class="form-label">Tinggi Badan</label>
                        <input type="text" class="form-control" id="tinggi" placeholder="">
                      </div>
                      <div class="col-md-6">
                        <label for="berat" class="form-label">Berat Badan</label>
                        <input type="text" class="form-control" id="berat" placeholder="">
                      </div>
                    </div>
                    <div class="row mb-3">
                      <div class="col-md-6">
                        <label for="lingkarKepala" class="form-label">Lingkar Kepala</label>
                        <input type="text" class="form-control" id="lingkarKepala" placeholder="">
                      </div>
                      <div class="col-md-6">
                        <label for="lingkarLengan" class="form-label">Lingkar Lengan</label>
                        <input type="text" class="form-control" id="lingkarLengan" placeholder="">
                      </div>
                    </div>
                    <div class="row mb-3">
                      <div class="col-md-6">
                        <label for="lingkarPerut" class="form-label">Lingkar Perut</label>
                        <input type="text" class="form-control" id="lingkarPerut" placeholder="">
                      </div>
                      <div class="col-md-6">
                        <label for="tekananDarah" class="form-label">Tekanan Darah</label>
                        <input type="text" class="form-control" id="tekananDarah" placeholder="">
                      </div>
                    </div>
                    <div class="mb-3">
                      <label for="konsultasi" class="form-label">Konsultasi Lainnya</label>
                      <input type="text" class="form-control" id="konsultasi" placeholder="">
                    </div>
                    <div class="text-end">
                      <button type="submit" class="btn btn-view">Simpan</button>
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