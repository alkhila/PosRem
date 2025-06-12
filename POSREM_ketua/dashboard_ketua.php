<?php
session_start(); // Start the session at the very beginning of the script

// Check if the user is logged in (i.e., if id_ketua is set in the session)
if (!isset($_SESSION["id_ketua"])) {
  // If not logged in, redirect to the login page
  header("Location: login_ketua.php"); // Make sure this path is correct for your login file
  exit;
}

// Database connection details
$servername = "localhost"; // Or your database host
$username = "root";        // Your database username
$password = "";            // Your database password
$dbname = "posrem";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Koneksi gagal: " . $conn->connect_error);
}

// Get the logged-in ketua's ID from the session
$id_ketua_logged_in = $_SESSION["id_ketua"];

// Fetch ketua_karang_taruna data for the logged-in user
$sql_ketua = "SELECT nama_ketua FROM ketua_karang_taruna WHERE id_ketua = ?";
$stmt_ketua = $conn->prepare($sql_ketua);
if ($stmt_ketua === false) {
  die("Error preparing ketua statement: " . $conn->error);
}
$stmt_ketua->bind_param("i", $id_ketua_logged_in);
$stmt_ketua->execute();
$result_ketua = $stmt_ketua->get_result();

$nama_ketua = "Guest"; // Default name
if ($result_ketua->num_rows > 0) {
  $row_ketua = $result_ketua->fetch_assoc();
  $nama_ketua = $row_ketua['nama_ketua'];
}
$stmt_ketua->close();


// Query untuk mengambil semua data yang diperlukan untuk riwayat terbaru, termasuk pesan lengkap
// Kali ini mengambil pesan dari 'pesan_kesehatan.pesan' untuk tampilan singkat dan full
$sql_riwayat = "
    SELECT
        p.id_pemeriksaan,
        p.tgl,
        a.nama_anggota,
        pk.pesan AS pesan_full_dan_singkat -- Mengambil kolom 'pesan' dari pesan_kesehatan
    FROM
        pemeriksaan p
    JOIN
        anggota a ON p.id_anggota = a.id_anggota
    LEFT JOIN
        pesan_kesehatan pk ON p.id_pemeriksaan = pk.id_pemeriksaan
    WHERE
        p.id_ketua = ?
    ORDER BY
        p.tgl DESC, p.id_pemeriksaan DESC
    LIMIT 5";

$stmt_riwayat = $conn->prepare($sql_riwayat);
if ($stmt_riwayat === false) {
  die("Error preparing riwayat statement: " . $conn->error);
}
$stmt_riwayat->bind_param("i", $id_ketua_logged_in);
$stmt_riwayat->execute();
$result_riwayat = $stmt_riwayat->get_result();

$all_riwayat_data = []; // Array untuk menyimpan semua data riwayat, termasuk pesan full
if ($result_riwayat->num_rows > 0) {
  while ($row_riwayat = $result_riwayat->fetch_assoc()) {
    // Tentukan pesan lengkap: menggunakan pesan dari pesan_kesehatan.pesan, atau fallback ke default
    $pesan_lengkap_untuk_modal = $row_riwayat['pesan_full_dan_singkat'] ?: "Tidak ada pesan lengkap.";

    // Tambahkan ke array untuk JavaScript
    $all_riwayat_data[] = [
      'id_pemeriksaan' => $row_riwayat['id_pemeriksaan'],
      'nama_anggota' => htmlspecialchars($row_riwayat['nama_anggota']), // Pastikan data aman untuk JS
      'tanggal_pemeriksaan' => htmlspecialchars(date('d F Y H:i', strtotime($row_riwayat['tgl']))), // Pastikan data aman
      'pesan_lengkap' => htmlspecialchars($pesan_lengkap_untuk_modal) // Pastikan data aman
    ];

    // Format untuk tampilan tabel
    $tanggal = date('d F Y', strtotime($row_riwayat['tgl']));
    $jam = date('H.i', strtotime($row_riwayat['tgl']));
    $pesan_singkat_display = $row_riwayat['pesan_full_dan_singkat'] ? $row_riwayat['pesan_full_dan_singkat'] : "Tidak ada pesan.";
    // Potong pesan singkat jika terlalu panjang (sesuaikan panjangnya)
    if (strlen($pesan_singkat_display) > 80) {
      $pesan_singkat_display = substr($pesan_singkat_display, 0, 80) . '...';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
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

    .kode {
      color: #8A70D6;
      font-weight: bold;
    }

    /* Styling untuk modal (pop-up) */
    .modal-dialog-centered {
      display: flex;
      align-items: center;
      min-height: calc(100% - (0.5rem * 2));
    }

    @media (min-width: 576px) {
      .modal-dialog-centered {
        min-height: calc(100% - (1.75rem * 2));
      }
    }

    #messageDetailModal .modal-content {
      background-color: white;
      border-radius: 15px;
      border: 1px solid #ced4da;
      padding: 20px;
      text-align: center;
    }

    .modal-header {
      border-bottom: none;
      padding: 0;
      margin-bottom: 20px;
      justify-content: center;
    }

    .modal-title {
      color: #8A70D6;
      font-weight: bold;
      font-size: 1.5rem;
      margin-top: 1px;
      /* Sesuaikan jarak atas title */
    }

    .modal-body {
      padding: 10px 0;
      /* Kurangi padding vertikal */
    }

    .modal-footer {
      border-top: none;
      padding: 10px 0 0;
      /* Kurangi padding vertikal */
      justify-content: center;
    }

    .modal-footer .btn-secondary {
      background-color: #8A70D6;
      color: white;
      border: none;
      border-radius: 50px;
      padding: 0.75rem 2rem;
      font-size: 1rem;
      transition: background-color 0.3s ease;
    }

    .modal-footer .btn-secondary:hover {
      background-color: #a855f7;
    }

    /* Notification Bell Icon */
    .modal-notification-icon {
      color: #8A70D6;
      font-size: 2rem;
      /* Ukuran icon */
      margin-bottom: 10px;
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
          <a href="dashboard_ketua.php" class="nav-link active">
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
          <a href="formDK_ketua.php" class="nav-link">
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
          <h2>Halo, <?php echo htmlspecialchars($nama_ketua); ?>!</h2> <br>

          <div class="kt-card">
            <h3>Sudah cek kesehatan bulan ini?</h3>
            <p>Jangan lupa laporan kesehatanmu ya!</p>
            <a href="cetakLaporan.php"><button class="btn-view">Lapor</button></a>
          </div>

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
                  <?php
                  // Query untuk mengambil semua data yang diperlukan untuk riwayat terbaru, termasuk pesan lengkap
                  // Mengambil pesan dari 'pesan_kesehatan.pesan' untuk tampilan singkat dan full
                  $sql_riwayat = "
                                        SELECT
                                            p.id_pemeriksaan,
                                            p.tgl,
                                            a.nama_anggota,
                                            pk.pesan AS pesan_full_dan_singkat -- Mengambil kolom 'pesan' dari pesan_kesehatan
                                        FROM
                                            pemeriksaan p
                                        JOIN
                                            anggota a ON p.id_anggota = a.id_anggota
                                        LEFT JOIN
                                            pesan_kesehatan pk ON p.id_pemeriksaan = pk.id_pemeriksaan
                                        WHERE
                                            p.id_ketua = ?
                                        ORDER BY
                                            p.tgl DESC, p.id_pemeriksaan DESC
                                        LIMIT 5";

                  $stmt_riwayat = $conn->prepare($sql_riwayat);
                  if ($stmt_riwayat === false) {
                    die("Error preparing riwayat statement: " . $conn->error);
                  }
                  $stmt_riwayat->bind_param("i", $id_ketua_logged_in);
                  $stmt_riwayat->execute();
                  $result_riwayat = $stmt_riwayat->get_result();

                  $all_riwayat_data = []; // Array untuk menyimpan semua data riwayat, termasuk pesan full
                  if ($result_riwayat->num_rows > 0) {
                    while ($row_riwayat = $result_riwayat->fetch_assoc()) {
                      // Tentukan pesan lengkap: menggunakan pesan dari pesan_kesehatan.pesan, atau fallback ke default
                      $pesan_lengkap_untuk_modal = $row_riwayat['pesan_full_dan_singkat'] ?: "Tidak ada pesan lengkap.";

                      // Tambahkan ke array untuk JavaScript
                      $all_riwayat_data[] = [
                        'id_pemeriksaan' => $row_riwayat['id_pemeriksaan'],
                        'nama_anggota' => htmlspecialchars($row_riwayat['nama_anggota']), // Pastikan data aman untuk JS
                        'tanggal_pemeriksaan' => htmlspecialchars(date('d F Y H:i', strtotime($row_riwayat['tgl']))), // Pastikan data aman
                        'pesan_lengkap' => htmlspecialchars($pesan_lengkap_untuk_modal) // Pastikan data aman
                      ];

                      // Format untuk tampilan tabel
                      $tanggal = date('d F Y', strtotime($row_riwayat['tgl']));
                      $jam = date('H.i', strtotime($row_riwayat['tgl']));
                      $pesan_singkat_display = $row_riwayat['pesan_full_dan_singkat'] ? $row_riwayat['pesan_full_dan_singkat'] : "Tidak ada pesan.";
                      // Potong pesan singkat jika terlalu panjang (sesuaikan panjangnya)
                      if (strlen($pesan_singkat_display) > 80) {
                        $pesan_singkat_display = substr($pesan_singkat_display, 0, 80) . '...';
                      }
                      ?>
                      <tr>
                        <td class="left-info" style="width: 20%;">
                          <span class="kode">Kode <?php echo htmlspecialchars($row_riwayat['id_pemeriksaan']); ?></span><br>
                          <?php echo htmlspecialchars($tanggal); ?> <br> <?php echo htmlspecialchars($jam); ?> WIB
                        </td>
                        <td class="middle-info" style="width: 65%;">
                          <br><?php echo htmlspecialchars($row_riwayat['nama_anggota']); ?>
                          <br>Pesan Dokter : “<?php echo htmlspecialchars($pesan_singkat_display); ?>”
                        </td>
                        <td class="right-info" style="width: 25%;">
                          <br>
                          <button class="btn-view view-message-btn" data-bs-toggle="modal"
                            data-bs-target="#messageDetailModal"
                            data-id-pemeriksaan="<?php echo htmlspecialchars($row_riwayat['id_pemeriksaan']); ?>">
                            Pesan
                          </button>
                        </td>
                      </tr>
                      <?php
                    }
                  } else {
                    echo '<tr><td colspan="3" style="text-align: center;">Tidak ada riwayat kesehatan terbaru.</td></tr>';
                  }
                  // PENTING: Tutup statement setelah loop while
                  $stmt_riwayat->close();
                  ?>
                </tbody>
              </table>
            </div>

          </div>
        </div>
      </div>

    </div>
  </div>

  <div class="modal fade" id="messageDetailModal" tabindex="-1" aria-labelledby="messageDetailModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
        </div>
        <h5 class="modal-title" id="messageDetailModalLabel">Detail Pesan Kesehatan</h5>
        <p class="mt-3"><strong>Anggota:</strong> <span id="modalAnggotaNama"></span></p>
        <p><strong>Tanggal:</strong> <span id="modalTanggalPemeriksaan"></span></p>
        <hr>
        <p id="modalPesanLengkap"></p>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
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

    // Data riwayat yang sudah diambil dari PHP saat halaman dimuat
    // Menggunakan JSON.parse() untuk mengubah string JSON dari PHP menjadi objek JavaScript
    const allRiwayatData = <?php echo json_encode($all_riwayat_data); ?>;

    // JavaScript untuk menangani modal pesan
    document.addEventListener('DOMContentLoaded', function () {
      var messageDetailModal = document.getElementById('messageDetailModal');
      messageDetailModal.addEventListener('show.bs.modal', function (event) {
        // Button that triggered the modal
        var button = event.relatedTarget;
        // Extract info from data-bs-* attributes
        var id_pemeriksaan_to_show = button.getAttribute('data-id-pemeriksaan');

        // Get elements to update
        var modalAnggotaNama = messageDetailModal.querySelector('#modalAnggotaNama');
        var modalTanggalPemeriksaan = messageDetailModal.querySelector('#modalTanggalPemeriksaan');
        var modalPesanLengkap = messageDetailModal.querySelector('#modalPesanLengkap');

        // Cari data yang sesuai di array allRiwayatData
        const foundData = allRiwayatData.find(item => item.id_pemeriksaan == id_pemeriksaan_to_show);

        if (foundData) {
          modalAnggotaNama.textContent = foundData.nama_anggota;
          modalTanggalPemeriksaan.textContent = foundData.tanggal_pemeriksaan;
          modalPesanLengkap.textContent = foundData.pesan_lengkap;
        } else {
          modalAnggotaNama.textContent = 'Error';
          modalTanggalPemeriksaan.textContent = '';
          modalPesanLengkap.textContent = 'Pesan tidak ditemukan.';
        }
      });
    });
  </script>
</body>

</html>
<?php
// Pindahkan penutupan koneksi ke bagian paling akhir script PHP
// setelah semua HTML dan JavaScript yang bergantung pada data PHP telah di-generate.
$conn->close();
?>