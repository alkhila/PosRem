<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

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


$sql_riwayat_lengkap = "
    SELECT
        p.id_pemeriksaan,
        p.tgl,
        p.tinggi_badan,
        p.berat_badan,
        p.lingkar_kepala,
        p.lingkar_perut,
        p.tekanan_darah,
        p.konsultasi, -- Konsultasi ditambahkan kembali ke SELECT
        kkt.nama_ketua AS nama_pasien_display,
        p.id_anggota,
        pk.pesan AS pesan_full,
        pt.nama_petugas
    FROM
        pemeriksaan p
    JOIN
        ketua_karang_taruna kkt ON p.id_ketua = kkt.id_ketua
    LEFT JOIN
        anggota a ON p.id_anggota = a.id_anggota
    LEFT JOIN
        pesan_kesehatan pk ON p.id_pemeriksaan = pk.id_pemeriksaan
    LEFT JOIN
        petugas_puskesmas pt ON p.id_petugas = pt.id_petugas
    WHERE
        p.id_anggota IS NULL AND p.id_ketua = ?
    ORDER BY
        p.tgl DESC, p.id_pemeriksaan DESC";

$stmt_riwayat_lengkap = $conn->prepare($sql_riwayat_lengkap);
if ($stmt_riwayat_lengkap === false) {
  die("Error preparing riwayat lengkap statement: " . $conn->error);
}
$stmt_riwayat_lengkap->bind_param("i", $id_ketua_logged_in);
$stmt_riwayat_lengkap->execute();
$result_riwayat_lengkap = $stmt_riwayat_lengkap->get_result();

$all_riwayat_data = [];
if ($result_riwayat_lengkap->num_rows > 0) {
  while ($row = $result_riwayat_lengkap->fetch_assoc()) {
    $nama_yang_diperiksa = $row['nama_pasien_display'];

    $pesan_untuk_tabel_dan_modal_pesan_kesehatan = !empty($row['pesan_full']) ? $row['pesan_full'] : "Belum ada tanggapan dari petugas puskesmas.";

    $pesan_singkat_display = $pesan_untuk_tabel_dan_modal_pesan_kesehatan;
    if (strlen($pesan_singkat_display) > 80) {
      $pesan_singkat_display = substr(strip_tags($pesan_singkat_display), 0, 80) . '...';
    }

    $all_riwayat_data[] = [
      'id_pemeriksaan' => $row['id_pemeriksaan'],
      'nama_anggota' => htmlspecialchars($nama_yang_diperiksa),
      'tanggal_pemeriksaan' => htmlspecialchars(date('d F Y H:i', strtotime($row['tgl']))),
      'tinggi_badan' => htmlspecialchars($row['tinggi_badan']),
      'berat_badan' => htmlspecialchars($row['berat_badan']),
      'lingkar_kepala' => htmlspecialchars($row['lingkar_kepala']),
      'lingkar_perut' => htmlspecialchars($row['lingkar_perut']),
      'tekanan_darah' => htmlspecialchars($row['tekanan_darah']),
      'nama_petugas' => htmlspecialchars($row['nama_petugas'] ?: 'Tidak ada petugas'),
      'konsultasi' => nl2br(htmlspecialchars($row['konsultasi'] ?: 'Tidak ada konsultasi.')),
      'pesan_full_display' => nl2br(htmlspecialchars($pesan_untuk_tabel_dan_modal_pesan_kesehatan)),
      'pesan_singkat_tabel' => htmlspecialchars($pesan_singkat_display)
    ];
  }
}
$stmt_riwayat_lengkap->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Pesan Kesehatan</title>
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

    h2 {
      font-size: 1.5rem;
      margin-top: 1.5rem;
      margin-left: 1.5rem;
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
      text-align: left;
    }

    .table-purple thead th {
      border: none;
      text-align: left;
      font-weight: bold;
      color: #8A70D6;
    }

    .table-purple tbody tr:first-child td {
      border-top: none;
    }

    .table-purple tbody tr:last-child td {
      border-bottom: none;
    }

    .left-info {
      border-radius: 30px 0 0 30px;
      border: 1px solid #8A70D6;
      border-right: none;
    }

    .middle-info {
      border: 1px solid black;
      border-left: none;
      border-right: none;
    }

    .right-info {
      border-radius: 0 30px 30px 0;
      border: 1px solid #8A70D6;
      border-left: none;
    }

    button {
      background-color: #B57DE4;
      color: white;
      border: none;
      border-radius: 20px;
      padding: 8px 16px;
    }

    #healthDetailModal .modal-dialog {
      max-width: 50%;
    }

    #healthDetailModal .modal-content {
      background-color: white;
      border-radius: 15px;
      border: 1px solid #ced4da;
      padding: 20px;
      text-align: center;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    #healthDetailModal .modal-header {
      border-bottom: none;
      padding: 0;
      justify-content: center;
    }

    #healthDetailModal .modal-title {
      color: #8A70D6;
      font-weight: bold;
      font-size: 1.5rem;
      margin-top: 5px;
      margin-bottom: 20px;
      text-align: center;
    }

    #healthDetailModal .modal-body {
      padding: 10px 20px;
      text-align: left;
    }

    #healthDetailModal .modal-body p {
      margin-bottom: 5px;
    }

    #healthDetailModal .modal-body strong {
      display: inline-block;
      width: 150px;
      text-align: left;
      margin-right: 10px;
    }

    #healthDetailModal .modal-body span {
      display: inline-block;
      text-align: left;
      vertical-align: top;
    }

    #healthDetailModal .modal-body hr {
      margin: 15px 0;
      border-top: 1px solid rgba(0, 0, 0, .1);
    }

    #healthDetailModal .modal-footer {
      border-top: none;
      padding: 0;
      justify-content: center;
    }

    #healthDetailModal .modal-footer .btn-secondary {
      background-color: #8A70D6;
      color: white;
      border: none;
      border-radius: 50px;
      padding: 0.75rem 2rem;
      font-size: 1rem;
      transition: background-color 0.3s ease;
      margin-top: 15px;
    }

    #healthDetailModal .modal-footer .btn-secondary:hover {
      background-color: #a855f7;
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
          <a href="pesanKesehatan.php" class="nav-link active">
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
          <div class="container mt-4">
            <div class="wrapper-table">
              <table class="table-purple">
                <thead>
                  <tr>
                    <th colspan="3" class="fw-bold text-start pb-2 border-bottom border-black"
                      style="color: #8A70D6; font-size: 1.5rem;">
                      Riwayat Terbaru</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($all_riwayat_data)): ?>
                    <?php foreach ($all_riwayat_data as $riwayat): ?>
                      <tr>
                        <td class="left-info" style="width: 20%;">
                          <?php echo htmlspecialchars(date('d F Y', strtotime($riwayat['tanggal_pemeriksaan']))); ?> <br>
                          <?php echo htmlspecialchars(date('H.i', strtotime($riwayat['tanggal_pemeriksaan']))); ?> WIB
                        </td>
                        <td class="middle-info" style="width: 65%;">
                          <br><?php echo htmlspecialchars($riwayat['nama_anggota']); ?>
                          <br>Pesan Dokter : “<?php echo htmlspecialchars($riwayat['pesan_singkat_tabel']); ?>”
                        </td>
                        <td class="right-info" style="width: 15%;">
                          <br>
                          <button class="btn-view view-health-detail-btn" data-bs-toggle="modal"
                            data-bs-target="#healthDetailModal"
                            data-id-pemeriksaan="<?php echo htmlspecialchars($riwayat['id_pemeriksaan']); ?>">
                            Data Kesehatan
                          </button>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="3" style="text-align: center;">Tidak ada riwayat pesan kesehatan.</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="healthDetailModal" tabindex="-1" aria-labelledby="healthDetailModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
        </div>
        <div class="modal-body">
          <h5 class="modal-title" id="healthDetailModalLabel">Detail Data Kesehatan</h5>
          <div style="text-align: left; padding: 0 20px;">
            <p><strong>Diperiksa untuk:</strong> <span id="modalHealthNamaAnggota"></span></p>
            <p><strong>Tanggal Pemeriksaan:</strong> <span id="modalHealthTanggal"></span></p>
            <hr>
            <p><strong>Tinggi Badan:</strong> <span id="modalHealthTinggiBadan"></span></p>
            <p><strong>Berat Badan:</strong> <span id="modalHealthBeratBadan"></span></p>
            <p><strong>Lingkar Kepala:</strong> <span id="modalHealthLingkarKepala"></span></p>
            <p><strong>Lingkar Perut:</strong> <span id="modalHealthLingkarPerut"></span></p>
            <p><strong>Tekanan Darah:</strong> <span id="modalHealthTekananDarah"></span></p>
            <p><strong>Petugas Pemeriksa:</strong> <span id="modalHealthNamaPetugas"></span></p>
            <p><strong>Konsultasi:</strong> <span id="modalHealthKonsultasi"></span></p>
            <p><strong>Pesan Kesehatan:</strong> <span id="modalHealthPesanFull"></span></p>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
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

    const allRiwayatData = <?php echo json_encode($all_riwayat_data); ?>;

    document.addEventListener('DOMContentLoaded', function () {
      var healthDetailModal = document.getElementById('healthDetailModal');
      healthDetailModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var id_pemeriksaan_to_show = button.getAttribute('data-id-pemeriksaan');

        var modalHealthNamaAnggota = healthDetailModal.querySelector('#modalHealthNamaAnggota');
        var modalHealthTanggal = healthDetailModal.querySelector('#modalHealthTanggal');
        var modalHealthTinggiBadan = healthDetailModal.querySelector('#modalHealthTinggiBadan');
        var modalHealthBeratBadan = healthDetailModal.querySelector('#modalHealthBeratBadan');
        var modalHealthLingkarKepala = healthDetailModal.querySelector('#modalHealthLingkarKepala');
        var modalHealthLingkarPerut = healthDetailModal.querySelector('#modalHealthLingkarPerut');
        var modalHealthTekananDarah = healthDetailModal.querySelector('#modalHealthTekananDarah');
        var modalHealthNamaPetugas = healthDetailModal.querySelector('#modalHealthNamaPetugas');
        var modalHealthKonsultasi = healthDetailModal.querySelector('#modalHealthKonsultasi');
        var modalHealthPesanFull = healthDetailModal.querySelector('#modalHealthPesanFull');

        const foundData = allRiwayatData.find(item => item.id_pemeriksaan == id_pemeriksaan_to_show);

        if (foundData) {
          modalHealthNamaAnggota.textContent = foundData.nama_anggota;
          modalHealthTanggal.textContent = foundData.tanggal_pemeriksaan;
          modalHealthTinggiBadan.textContent = foundData.tinggi_badan + ' cm';
          modalHealthBeratBadan.textContent = foundData.berat_badan + ' kg';
          modalHealthLingkarKepala.textContent = foundData.lingkar_kepala + ' cm';
          modalHealthLingkarPerut.textContent = foundData.lingkar_perut + ' cm';
          modalHealthTekananDarah.textContent = foundData.tekanan_darah + ' mmHg';
          modalHealthNamaPetugas.textContent = foundData.nama_petugas;
          modalHealthKonsultasi.innerHTML = foundData.konsultasi;
          modalHealthPesanFull.innerHTML = foundData.pesan_full_display;
        } else {
          modalHealthNamaAnggota.textContent = 'Data tidak ditemukan.';
          modalHealthTanggal.textContent = '';
          modalHealthTinggiBadan.textContent = '';
          modalHealthBeratBadan.textContent = '';
          modalHealthLingkarKepala.textContent = '';
          modalHealthLingkarPerut.textContent = '';
          modalHealthTekananDarah.textContent = '';
          modalHealthNamaPetugas.textContent = '';
          modalHealthKonsultasi.textContent = '';
          modalHealthPesanFull.textContent = '';
        }
      });
    });
  </script>
  <?php include '_logout_modal.php'; ?>
</body>

</html>
<?php

$conn->close();
?>