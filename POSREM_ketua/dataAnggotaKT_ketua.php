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

// Fetch nama ketua untuk ditampilkan di sidebar (opsional, tapi baik untuk konsistensi UI)
$nama_ketua_sidebar = "Ketua"; // Default
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


// --- LOGIKA HAPUS DATA ANGGOTA ---
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
  $delete_id_anggota = $_GET['delete_id'];
  $delete_message = "";

  // Ambil id_kt yang dikelola oleh ketua yang login saat ini
  $sql_get_id_kt_ketua = "SELECT id_kt FROM ketua_karang_taruna WHERE id_ketua = ?";
  $stmt_get_id_kt_ketua = $conn->prepare($sql_get_id_kt_ketua);
  $stmt_get_id_kt_ketua->bind_param("i", $id_ketua_logged_in);
  $stmt_get_id_kt_ketua->execute();
  $result_get_id_kt_ketua = $stmt_get_id_kt_ketua->get_result();
  $id_kt_of_logged_in_ketua = null;
  if ($result_get_id_kt_ketua->num_rows > 0) {
    $id_kt_of_logged_in_ketua = $result_get_id_kt_ketua->fetch_assoc()['id_kt'];
  }
  $stmt_get_id_kt_ketua->close();

  if ($id_kt_of_logged_in_ketua === null) {
    $delete_message = "<script>alert('Error: Ketua tidak terhubung ke Karang Taruna.');</script>";
  } else {
    // Cek apakah anggota yang akan dihapus benar-benar berada di Karang Taruna yang dikelola oleh Ketua ini
    $sql_check_anggota_ownership = "SELECT id_anggota FROM anggota WHERE id_anggota = ? AND id_kt = ?";
    $stmt_check_anggota_ownership = $conn->prepare($sql_check_anggota_ownership);
    $stmt_check_anggota_ownership->bind_param("ii", $delete_id_anggota, $id_kt_of_logged_in_ketua);
    $stmt_check_anggota_ownership->execute();
    $result_check_anggota_ownership = $stmt_check_anggota_ownership->get_result();

    if ($result_check_anggota_ownership->num_rows > 0) {
      // Hapus data yang terkait di tabel 'pesan_kesehatan' jika ada foreign key dari 'pesan_kesehatan' ke 'pemeriksaan'
      // Dan 'pemeriksaan' ke 'anggota'
      // Ini akan membutuhkan query DELETE tambahan jika Anda tidak menggunakan ON DELETE CASCADE
      // Cek urutan DELETE sesuai ketergantungan foreign key Anda
      // Misalnya:
      // $sql_delete_pesan_kesehatan = "DELETE FROM pesan_kesehatan WHERE id_pemeriksaan IN (SELECT id_pemeriksaan FROM pemeriksaan WHERE id_anggota = ?)";
      // $stmt_delete_pesan_kesehatan = $conn->prepare($sql_delete_pesan_kesehatan);
      // $stmt_delete_pesan_kesehatan->bind_param("i", $delete_id_anggota);
      // $stmt_delete_pesan_kesehatan->execute();
      // $stmt_delete_pesan_kesehatan->close();

      // Hapus pemeriksaan kesehatan yang terkait dengan anggota ini
      $sql_delete_pemeriksaan = "DELETE FROM pemeriksaan WHERE id_anggota = ?";
      $stmt_delete_pemeriksaan = $conn->prepare($sql_delete_pemeriksaan);
      $stmt_delete_pemeriksaan->bind_param("i", $delete_id_anggota);
      $stmt_delete_pemeriksaan->execute();
      $stmt_delete_pemeriksaan->close();

      // Kemudian, hapus anggota
      $sql_delete_anggota = "DELETE FROM anggota WHERE id_anggota = ?";
      $stmt_delete_anggota = $conn->prepare($sql_delete_anggota);
      $stmt_delete_anggota->bind_param("i", $delete_id_anggota);

      if ($stmt_delete_anggota->execute()) {
        // REDIRECT YANG BENAR SETELAH HAPUS SUKSES
        // Mengarahkan kembali ke file ini sendiri: dataAnggotaKT_ketua.php
        $delete_message = "<script>alert('Anggota berhasil dihapus!'); window.location.href='dataAnggotaKT_ketua.php';</script>";
      } else {
        $delete_message = "<script>alert('Gagal menghapus anggota: " . $stmt_delete_anggota->error . "');</script>";
      }
      $stmt_delete_anggota->close();
    } else {
      $delete_message = "<script>alert('Anda tidak memiliki izin untuk menghapus anggota ini atau anggota tidak ditemukan.');</script>";
    }
    $stmt_check_anggota_ownership->close();
  }
  echo $delete_message; // Tampilkan alert dan/atau redirect
  exit; // Pastikan ini ada untuk menghentikan eksekusi script setelah redirect
}
// --- AKHIR LOGIKA HAPUS ---


// Query untuk mengambil data anggota berdasarkan id_kt dari ketua yang login
$sql_anggota = "
    SELECT
        a.id_anggota,
        a.nama_anggota,
        a.jenis_kelamin_anggota,
        a.umur_anggota,
        a.no_hp_anggota
    FROM
        anggota a
    JOIN
        karang_taruna kt ON a.id_kt = kt.id_kt
    JOIN
        ketua_karang_taruna kkt ON kt.id_kt = kkt.id_kt
    WHERE
        kkt.id_ketua = ?
    ORDER BY
        a.nama_anggota ASC";

$stmt_anggota = $conn->prepare($sql_anggota);
if ($stmt_anggota === false) {
  die("Error preparing anggota statement: " . $conn->error);
}
$stmt_anggota->bind_param("i", $id_ketua_logged_in);
$stmt_anggota->execute();
$result_anggota = $stmt_anggota->get_result();

?>

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

    /* --- PERBAIKAN CSS UNTUK BUTTON CONTAINER --- */
    .button-container {
      display: flex;
      justify-content: space-between;
      /* Membuat item di ujung-ujung */
      margin-top: 2rem;
      max-width: 97.5%;
      /* Sesuaikan dengan wrapper-table */
      margin-left: auto;
      /* Pusatkan container */
      margin-right: auto;
      /* Pusatkan container */
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

          <div class="container mt-4">
            <div class="wrapper-table">
              <table class="table-purple">
                <thead>
                  <tr>
                    <th class="fw-bold pb-2 border-bottom border-black"
                      style="color: #8A70D6; text-align: left; padding-left: 50px;">
                      Nama</th>
                    <th class="fw-bold text-center pb-2 border-bottom border-black" style="color: #8A70D6;">
                      Jenis Kelamin</th>
                    <th class="fw-bold text-center pb-2 border-bottom border-black" style="color: #8A70D6;">
                      Umur</th>
                    <th class="fw-bold text-center pb-2 border-bottom border-black" style="color: #8A70D6;">
                      No Telp</th>
                    <th colspan="3" class="fw-bold text-start pb-2 border-bottom border-black" style="color: #8A70D6;">
                      Akun</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  if ($result_anggota->num_rows > 0) {
                    while ($row = $result_anggota->fetch_assoc()) {
                      ?>
                      <tr>
                        <td style="width: 20%; text-align: left; padding-left: 35px;">
                          <?php echo htmlspecialchars($row['nama_anggota']); ?>
                        </td>
                        <td style="width: 20%;"><?php echo htmlspecialchars($row['jenis_kelamin_anggota']); ?></td>
                        <td style="width: 20%;"><?php echo htmlspecialchars($row['umur_anggota']); ?></td>
                        <td style="width: 22%;"><?php echo htmlspecialchars($row['no_hp_anggota']); ?></td>
                        <td style="width: 7%;"><a
                            href="dataAnggotaLengkap.php?id_anggota=<?php echo $row['id_anggota']; ?>"><img
                              src="asset/logo_akun.png" alt="Detail"></a>
                        </td>
                        <td style="width: 6%;"><a href="dataAnggotaKT_ketua.php?delete_id=<?php echo $row['id_anggota']; ?>"
                            onclick="return confirm('Anda yakin ingin menghapus anggota ini? Ini akan juga menghapus data pemeriksaan yang terkait!');"><img
                              src="asset/logo_delete.png" alt="Delete"></a></td>
                        <td style="width: 5%;"><a
                            href="tambahDataAnggota.php?id_anggota=<?php echo $row['id_anggota']; ?>"><img
                              src="asset/logo_edit.png" alt="Edit"></a>
                        </td>
                      </tr>
                      <?php
                    }
                  } else {
                    echo '<tr><td colspan="7" style="text-align: center;">Tidak ada data anggota karang taruna.</td></tr>';
                  }
                  ?>
                </tbody>
              </table>
            </div>
            <div class="button-container">
              <a href="KT_ketua.php"><button class="btn-view">Kembali</button></a>
              <a href="tambahDataAnggota.php"><button class="btn-view">Tambah</button></a>
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
<?php
// Tutup koneksi database setelah semua data diambil dan ditampilkan
$conn->close();
?>