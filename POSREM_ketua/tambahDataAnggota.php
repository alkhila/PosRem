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

$is_edit_mode = false;
$data_anggota = [
  'id_anggota' => '',
  'nama_anggota' => '',
  'umur_anggota' => '',
  'jenis_kelamin_anggota' => '',
  'no_hp_anggota' => '',
  'usn_anggota' => '',
  'pass_anggota' => ''
];
$form_title = "Tambah Data Anggota";
$submit_button_text = "Daftar";
$action_message = "";

if (isset($_GET['id_anggota']) && is_numeric($_GET['id_anggota'])) {
  $is_edit_mode = true;
  $data_anggota['id_anggota'] = $_GET['id_anggota'];
  $form_title = "Edit Data Anggota";
  $submit_button_text = "Simpan Perubahan";

  $sql_fetch_anggota = "SELECT id_anggota, nama_anggota, umur_anggota, jenis_kelamin_anggota, no_hp_anggota, usn_anggota, pass_anggota FROM anggota WHERE id_anggota = ?";
  $stmt_fetch_anggota = $conn->prepare($sql_fetch_anggota);
  if ($stmt_fetch_anggota === false) {
    die("Error preparing fetch statement: " . $conn->error);
  }
  $stmt_fetch_anggota->bind_param("i", $data_anggota['id_anggota']);
  $stmt_fetch_anggota->execute();
  $result_fetch_anggota = $stmt_fetch_anggota->get_result();

  if ($result_fetch_anggota->num_rows > 0) {
    $data_anggota = $result_fetch_anggota->fetch_assoc();
  } else {
    echo "<script>alert('Data anggota tidak ditemukan.'); window.location.href='KT_ketua.php';</script>";
    exit;
  }
  $stmt_fetch_anggota->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $nama_lengkap = trim($_POST['namalengkap']);
  $umur = trim($_POST['umur']);
  $jenis_kelamin = trim($_POST['jeniskelamin']);
  $no_telp = trim($_POST['notelp']);
  $username = trim($_POST['username']);
  $password = $_POST['pass'];
  $repassword = $_POST['repass'];
  $id_anggota_post = $_POST['id_anggota'] ?? '';

  if (empty($nama_lengkap) || empty($umur) || empty($jenis_kelamin) || empty($no_telp) || empty($username)) {
    $action_message = "<p style='color: red;'>Semua field harus diisi (kecuali password jika tidak diubah pada mode edit).</p>";
  } elseif (!empty($password) && $password !== $repassword) {
    $action_message = "<p style='color: red;'>Password dan Konfirmasi Password tidak cocok.</p>";
  } elseif (!$is_edit_mode && empty($password)) {
    $action_message = "<p style='color: red;'>Password harus diisi untuk anggota baru.</p>";
  } else {
    $final_password = $password;

    if ($is_edit_mode) {
      $sql_update = "UPDATE anggota SET nama_anggota = ?, umur_anggota = ?, jenis_kelamin_anggota = ?, no_hp_anggota = ?, usn_anggota = ?";
      $bind_types = "sisss";
      $bind_params = [$nama_lengkap, $umur, $jenis_kelamin, $no_telp, $username];

      if (!empty($password)) {
        $sql_update .= ", pass_anggota = ?";
        $bind_types .= "s";
        $bind_params[] = $final_password;
      }
      $sql_update .= " WHERE id_anggota = ?";
      $bind_types .= "i";
      $bind_params[] = $id_anggota_post;

      $stmt_update = $conn->prepare($sql_update);
      if ($stmt_update === false) {
        $action_message = "<p style='color: red;'>Error preparing update statement: " . $conn->error . "</p>";
      } else {
        // Perbaikan: Buat array referensi secara eksplisit
        $bind_refs = [];
        $bind_refs[] = $bind_types; // Argumen pertama adalah string tipe data

        foreach ($bind_params as &$param) { // Gunakan '&' untuk mendapatkan referensi ke setiap elemen
          $bind_refs[] = &$param;
        }

        call_user_func_array([$stmt_update, 'bind_param'], $bind_refs);


        if ($stmt_update->execute()) {
          $action_message = "<p style='color: green;'>Data anggota berhasil diperbarui.</p>";
          // Re-fetch data to update form fields with latest data
          $sql_fetch_anggota = "SELECT id_anggota, nama_anggota, umur_anggota, jenis_kelamin_anggota, no_hp_anggota, usn_anggota, pass_anggota FROM anggota WHERE id_anggota = ?";
          $stmt_fetch_anggota_after_update = $conn->prepare($sql_fetch_anggota);
          $stmt_fetch_anggota_after_update->bind_param("i", $id_anggota_post);
          $stmt_fetch_anggota_after_update->execute();
          $result_fetch_anggota_after_update = $stmt_fetch_anggota_after_update->get_result();
          $data_anggota = $result_fetch_anggota_after_update->fetch_assoc();
          $stmt_fetch_anggota_after_update->close();

        } else {
          $action_message = "<p style='color: red;'>Gagal memperbarui data anggota: " . $stmt_update->error . "</p>";
        }
        $stmt_update->close();
      }
    } else {
      $id_ketua_logged_in = $_SESSION["id_ketua"];
      $sql_get_id_kt = "SELECT id_kt FROM ketua_karang_taruna WHERE id_ketua = ?";
      $stmt_get_id_kt = $conn->prepare($sql_get_id_kt);
      if ($stmt_get_id_kt === false) {
        die("Error preparing id_kt statement: " . $conn->error);
      }
      $stmt_get_id_kt->bind_param("i", $id_ketua_logged_in);
      $stmt_get_id_kt->execute();
      $result_get_id_kt = $stmt_get_id_kt->get_result();
      $id_kt_anggota_baru = null;
      if ($result_get_id_kt->num_rows > 0) {
        // Perbaikan: Ambil hasil fetch_assoc ke dalam variabel sementara
        // sebelum mengakses elemennya, untuk memastikan passing by reference.
        $row_temp = $result_get_id_kt->fetch_assoc();
        if ($row_temp) {
          $id_kt_anggota_baru = $row_temp['id_kt'];
        }
      }
      $stmt_get_id_kt->close();

      if ($id_kt_anggota_baru === null) {
        $action_message = "<p style='color: red;'>Gagal mendapatkan ID Karang Taruna untuk Ketua ini. Pastikan Ketua terhubung ke Karang Taruna.</p>";
      } else {
        $sql_insert = "INSERT INTO anggota (nama_anggota, umur_anggota, jenis_kelamin_anggota, no_hp_anggota, usn_anggota, pass_anggota, id_kt) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        if ($stmt_insert === false) {
          $action_message = "<p style='color: red;'>Error preparing insert statement: " . $conn->error . "</p>";
        } else {
          // Perbaikan: Buat variabel eksplisit untuk setiap parameter bind_param
          // Ini memastikan semua argumen dilewatkan dengan referensi.
          $p_nama_lengkap = $nama_lengkap;
          $p_umur = $umur;
          $p_jenis_kelamin = $jenis_kelamin;
          $p_no_telp = $no_telp;
          $p_username = $username;
          $p_final_password = $final_password;
          $p_id_kt_anggota_baru = $id_kt_anggota_baru;

          $stmt_insert->bind_param("sissssi", $p_nama_lengkap, $p_umur, $p_jenis_kelamin, $p_no_telp, $p_username, $p_final_password, $p_id_kt_anggota_baru);
          if ($stmt_insert->execute()) {
            $action_message = "<p style='color: green;'>Data anggota berhasil ditambahkan.</p>";
            // Reset form fields after successful insertion
            $data_anggota = [
              'id_anggota' => '',
              'nama_anggota' => '',
              'umur_anggota' => '',
              'jenis_kelamin_anggota' => '',
              'no_hp_anggota' => '',
              'usn_anggota' => '',
              'pass_anggota' => ''
            ];
          } else {
            $action_message = "<p style='color: red;'>Gagal menambahkan data anggota: " . $stmt_insert->error . "</p>";
          }
          $stmt_insert->close();
        }
      }
    }
  }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo $form_title; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to bottom, #fddde6, #cce7f5);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .container {
      background: white;
      width: 900px;
      max-width: 95%;
      padding: 40px 50px;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    h1 {
      font-size: 30px;
      margin-bottom: 25px;
      margin-top: 15px;
      font-weight: 450;
    }

    .form-row {
      display: flex;
      gap: 20px;
      margin-bottom: 20px;
    }

    .form-group {
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    label {
      font-size: 18px;
      margin-bottom: 6px;
      font-weight: normal;
    }

    input[type="text"],
    input[type="password"],
    select {
      padding: 12px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 14px;
    }

    input[type="text"]:focus,
    input[type="password"]:focus,
    select:focus {
      border-color: #3b82f6;
      outline: none;
      box-shadow: 0 0 0 5px rgba(59, 130, 246, 0.3);
    }

    .form-footer {
      margin-top: 30px;
      font-size: 14px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .form-footer input[type="checkbox"] {
      width: 20px;
      height: 20px;
      accent-color: #7c3aed;
    }

    .button-row {
      display: flex;
      justify-content: space-between;
      margin-top: 30px;
    }

    .btn {
      background-color: #c084fc;
      color: white;
      padding: 10px 20px;
      font-size: 16px;
      border: none;
      border-radius: 25px;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    .btn:hover {
      background-color: #a855f7;
      color: white;
    }

    .message-container {
      margin-bottom: 20px;
      text-align: center;
    }
  </style>
</head>

<body>
  <div class="container">
    <form method="POST" action="">
      <h1><?php echo $form_title; ?></h1>

      <div class="message-container">
        <?php echo $action_message; ?>
      </div>

      <?php if ($is_edit_mode): ?>
        <input type="hidden" name="id_anggota" value="<?php echo htmlspecialchars($data_anggota['id_anggota']); ?>">
      <?php endif; ?>

      <div class="form-row">
        <div class="form-group">
          <label for="namalengkap">Nama Lengkap</label>
          <input type="text" id="namalengkap" name="namalengkap" placeholder="exampleruby"
            value="<?php echo htmlspecialchars($data_anggota['nama_anggota']); ?>" required />
        </div>
        <div class="form-group">
          <label for="umur">Umur</label>
          <input type="text" id="umur" name="umur" placeholder="19"
            value="<?php echo htmlspecialchars($data_anggota['umur_anggota']); ?>" required />
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="jeniskelamin">Jenis Kelamin</label>
          <select id="jeniskelamin" name="jeniskelamin" required placeholder="Pilih jenis kelamin">
            <option value="Laki-laki" <?php echo ($data_anggota['jenis_kelamin_anggota'] == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
            <option value="Perempuan" <?php echo ($data_anggota['jenis_kelamin_anggota'] == 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
          </select>
        </div>
        <div class="form-group">
          <label for="notelp">No Telepon</label>
          <input type="text" id="notelp" name="notelp" placeholder="08xxx"
            value="<?php echo htmlspecialchars($data_anggota['no_hp_anggota']); ?>" required />
        </div>
      </div>

      <h1>Username dan Password</h1>
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username"
          value="<?php echo htmlspecialchars($data_anggota['usn_anggota']); ?>" required />
      </div>

      <div class="form-group" style="margin-top: 20px;">
        <label for="pass">Password</label>
        <input type="password" id="pass" name="pass"
          placeholder="<?php echo $is_edit_mode ? 'Kosongkan jika tidak ingin mengubah password' : 'Masukkan password'; ?>"
          <?php echo $is_edit_mode ? '' : 'required'; ?> />
      </div>

      <div class="form-group" style="margin-top: 20px;">
        <label for="repass">Konfirmasi Password</label>
        <input type="password" id="repass" name="repass"
          placeholder="<?php echo $is_edit_mode ? 'Kosongkan jika tidak ingin mengubah password' : 'Konfirmasi password'; ?>"
          <?php echo $is_edit_mode ? '' : 'required'; ?> />
      </div>

      <div class="button-row">
        <button type="button" class="btn" onclick="location.href='dataAnggotaKT_ketua.php'">Kembali</button>
        <button type="submit" class="btn"><?php echo $submit_button_text; ?></button>
      </div>
    </form>

  </div>
</body>

</html>