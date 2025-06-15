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

$id_ketua_to_edit = $_SESSION["id_ketua"];

$ketua_data = [
  'usn_ketua' => '',
  'nama_ketua' => '',
  'jenis_kelamin_ketua' => '',
  'umur_ketua' => '',
  'no_hp_ketua' => '',
  'tempat_tanggal_lahir' => '',
  'alamat_rumah' => '',
];
$action_message = "";

$sql_fetch_ketua = "SELECT usn_ketua, nama_ketua, jenis_kelamin_ketua, umur_ketua, no_hp_ketua, tempat_tanggal_lahir, alamat_rumah FROM ketua_karang_taruna WHERE id_ketua = ?";
$stmt_fetch_ketua = $conn->prepare($sql_fetch_ketua);
if ($stmt_fetch_ketua === false) {
  die("Error preparing fetch statement: " . $conn->error);
}
$stmt_fetch_ketua->bind_param("i", $id_ketua_to_edit);
$stmt_fetch_ketua->execute();
$result_fetch_ketua = $stmt_fetch_ketua->get_result();

if ($result_fetch_ketua->num_rows > 0) {
  $ketua_data = $result_fetch_ketua->fetch_assoc();
} else {
  echo "<script>alert('Data Ketua tidak ditemukan.'); window.location.href='dataDiri.php';</script>";
  exit;
}
$stmt_fetch_ketua->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $usn_ketua_new = trim($_POST['usn_ketua'] ?? '');
  $pass_ketua_new = $_POST['pass_ketua'] ?? '';
  $konf_pass_ketua_new = $_POST['konf_pass_ketua'] ?? '';
  $nama_ketua_new = trim($_POST['nama_ketua'] ?? '');
  $jenis_kelamin_ketua_new = $_POST['jenis_kelamin_ketua'] ?? '';
  $umur_ketua_new = (int) ($_POST['umur_ketua'] ?? 0);
  $no_hp_ketua_new = trim($_POST['no_hp_ketua'] ?? '');
  $tempat_tanggal_lahir_new = trim($_POST['tempat_tanggal_lahir'] ?? '');
  $alamat_rumah_new = trim($_POST['alamat_rumah'] ?? '');

  $ketua_data['usn_ketua'] = $usn_ketua_new;
  $ketua_data['nama_ketua'] = $nama_ketua_new;
  $ketua_data['jenis_kelamin_ketua'] = $jenis_kelamin_ketua_new;
  $ketua_data['umur_ketua'] = $umur_ketua_new;
  $ketua_data['no_hp_ketua'] = $no_hp_ketua_new;
  $ketua_data['tempat_tanggal_lahir'] = $tempat_tanggal_lahir_new;
  $ketua_data['alamat_rumah'] = $alamat_rumah_new;

  if (empty($usn_ketua_new) || empty($nama_ketua_new) || empty($jenis_kelamin_ketua_new) || empty($umur_ketua_new) || empty($no_hp_ketua_new)) {
    $action_message = "<p style='color: red;'>Field wajib (Username, Nama, Jenis Kelamin, Umur, No. HP) harus diisi.</p>";
  } elseif (!empty($pass_ketua_new) && $pass_ketua_new !== $konf_pass_ketua_new) {
    $action_message = "<p style='color: red;'>Password dan Konfirmasi Password tidak cocok.</p>";
  } else {
    $stmt_check_usn = $conn->prepare("SELECT id_ketua FROM ketua_karang_taruna WHERE usn_ketua = ? AND id_ketua != ?");
    $stmt_check_usn->bind_param("si", $usn_ketua_new, $id_ketua_to_edit);
    $stmt_check_usn->execute();
    $stmt_check_usn->store_result();
    if ($stmt_check_usn->num_rows > 0) {
      $action_message = "<p style='color: red;'>Username sudah digunakan oleh akun lain. Pilih username lain.</p>";
    }
    $stmt_check_usn->close();


    if (empty($action_message)) {
      $sql_set_parts = [];
      $bind_params_final = [];
      $bind_types_final_string = '';

      $sql_set_parts[] = "usn_ketua=?";
      $bind_params_final[] = $usn_ketua_new;
      $bind_types_final_string .= "s";
      $sql_set_parts[] = "nama_ketua=?";
      $bind_params_final[] = $nama_ketua_new;
      $bind_types_final_string .= "s";
      $sql_set_parts[] = "jenis_kelamin_ketua=?";
      $bind_params_final[] = $jenis_kelamin_ketua_new;
      $bind_types_final_string .= "s";
      $sql_set_parts[] = "umur_ketua=?";
      $bind_params_final[] = $umur_ketua_new;
      $bind_types_final_string .= "i";
      $sql_set_parts[] = "no_hp_ketua=?";
      $bind_params_final[] = $no_hp_ketua_new;
      $bind_types_final_string .= "s";
      $sql_set_parts[] = "tempat_tanggal_lahir=?";
      $bind_params_final[] = $tempat_tanggal_lahir_new;
      $bind_types_final_string .= "s";
      $sql_set_parts[] = "alamat_rumah=?";
      $bind_params_final[] = $alamat_rumah_new;
      $bind_types_final_string .= "s";

      if (!empty($pass_ketua_new)) {
        $hashed_password = password_hash($pass_ketua_new, PASSWORD_DEFAULT);
        $sql_set_parts[] = "pass_ketua=?";
        $bind_params_final[] = $hashed_password;
        $bind_types_final_string .= "s";
      }

      $sql_update_query_base = "UPDATE ketua_karang_taruna SET " . implode(', ', $sql_set_parts);

      $sql_update_query = $sql_update_query_base . " WHERE id_ketua=?";
      $bind_params_final[] = $id_ketua_to_edit;
      $bind_types_final_string .= "i";

      $stmt_update = $conn->prepare($sql_update_query);
      if ($stmt_update === false) {
        $action_message = "<p style='color: red;'>Error menyiapkan update statement: " . $conn->error . "</p>";
      } else {
        $stmt_update->bind_param($bind_types_final_string, ...$bind_params_final);

        if ($stmt_update->execute()) {
          $action_message = "<p style='color: green;'>Data diri berhasil diperbarui!</p>";
          echo "<script>alert('Data diri berhasil diperbarui!'); window.location.href='dataDiri.php';</script>";
          exit;
        } else {
          $action_message = "<p style='color: red;'>Gagal memperbarui data diri: " . $stmt_update->error . "</p>";
        }
        $stmt_update->close();
      }
    }
  }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Form Edit Data Diri</title>
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

    h3 {
      color: rgba(178, 124, 223, 1);
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
    }

    .info-card .card {
      padding: 2rem;
      border-radius: 15px;
    }

    form input.form-control,
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
      outline: none;
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

    .form-label {
      font-weight: bold;
      color: #555;
      margin-bottom: .5rem;
    }

    .button-row-form {
      display: flex;
      justify-content: space-between;
      margin-top: 30px;
    }

    .message-container {
      margin-top: 20px;
      text-align: center;
      font-weight: bold;
      color: red;
    }

    .message-container p {
      margin-bottom: 0;
    }

    .message-container.success {
      color: green;
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
                    <h3><strong>Form Edit Data Diri</strong></h3>
                    <?php if (!empty($action_message)): ?>
                      <div
                        class="message-container <?php echo strpos($action_message, 'berhasil') !== false ? 'success' : ''; ?>">
                        <?php echo $action_message; ?>
                      </div>
                    <?php endif; ?>
                    <form method="POST" action="">
                      <div class="row mb-3 mt-4">
                        <div class="col-md-6">
                          <label for="usn_ketua" class="form-label">Username:</label>
                          <input type="text" class="form-control" id="usn_ketua" name="usn_ketua"
                            value="<?php echo htmlspecialchars($ketua_data['usn_ketua']); ?>" required>
                        </div>
                        <div class="col-md-6">
                          <label for="nama_ketua" class="form-label">Nama Lengkap:</label>
                          <input type="text" class="form-control" id="nama_ketua" name="nama_ketua"
                            value="<?php echo htmlspecialchars($ketua_data['nama_ketua']); ?>" required>
                        </div>
                      </div>
                      <div class="row mb-3">
                        <div class="col-md-6">
                          <label for="pass_ketua" class="form-label">Password Baru:</label>
                          <input type="password" class="form-control" id="pass_ketua" name="pass_ketua"
                            placeholder="Kosongkan jika tidak ingin mengubah password">
                        </div>
                        <div class="col-md-6">
                          <label for="konf_pass_ketua" class="form-label">Konfirmasi Password Baru:</label>
                          <input type="password" class="form-control" id="konf_pass_ketua" name="konf_pass_ketua"
                            placeholder="Konfirmasi password baru">
                        </div>
                      </div>
                      <div class="row mb-3">
                        <div class="col-md-6">
                          <label for="jenis_kelamin_ketua" class="form-label">Jenis Kelamin:</label>
                          <select class="form-select" id="jenis_kelamin_ketua" name="jenis_kelamin_ketua" required>
                            <option value="">-- Pilih Jenis Kelamin --</option>
                            <option value="Laki-laki" <?php echo ($ketua_data['jenis_kelamin_ketua'] == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                            <option value="Perempuan" <?php echo ($ketua_data['jenis_kelamin_ketua'] == 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                          </select>
                        </div>
                        <div class="col-md-6">
                          <label for="umur_ketua" class="form-label">Umur:</label>
                          <input type="number" class="form-control" id="umur_ketua" name="umur_ketua" min="17" max="99"
                            value="<?php echo htmlspecialchars($ketua_data['umur_ketua']); ?>" required>
                        </div>
                      </div>
                      <div class="row mb-3">
                        <div class="col-md-6">
                          <label for="tempat_tanggal_lahir" class="form-label">Tempat, Tanggal Lahir:</label>
                          <input type="text" class="form-control" id="tempat_tanggal_lahir" name="tempat_tanggal_lahir"
                            placeholder="e.g., Yogyakarta, 1 Januari 1990"
                            value="<?php echo htmlspecialchars($ketua_data['tempat_tanggal_lahir']); ?>">
                        </div>
                        <div class="col-md-6">
                          <label for="no_hp_ketua" class="form-label">No. HP:</label>
                          <input type="tel" class="form-control" id="no_hp_ketua" name="no_hp_ketua"
                            placeholder="e.g., 081234567890"
                            value="<?php echo htmlspecialchars($ketua_data['no_hp_ketua']); ?>" required>
                        </div>
                      </div>
                      <div class="mb-3">
                        <label for="alamat_rumah" class="form-label">Alamat Rumah:</label>
                        <textarea class="form-control" id="alamat_rumah" name="alamat_rumah" rows="3"
                          placeholder="Masukkan alamat lengkap Anda"><?php echo htmlspecialchars($ketua_data['alamat_rumah']); ?></textarea>
                      </div>
                      <div class="button-row-form">
                        <button type="button" class="btn-view"
                          onclick="window.location.href='dataDiri.php'">Kembali</button>
                        <button type="submit" class="btn-view">Simpan</button>
                      </div>
                    </form>
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