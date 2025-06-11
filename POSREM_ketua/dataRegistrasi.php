<?php
// Koneksi ke database
$host = "localhost";
$user = "root";
$pass = ""; // Sesuaikan jika pakai password
$db = "posrem";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
  die("Koneksi gagal: " . mysqli_connect_error());
}

// Cek jika data dari halaman sebelumnya belum dikirim, redirect ke registrasi.php
if (!isset($_POST['username']) || !isset($_POST['password']) || !isset($_POST['repassword'])) {
  header("Location: registrasi.php");
  exit;
}

// Ambil data dari halaman sebelumnya
$username = $_POST['username'];
$password = $_POST['password'];
$repassword = $_POST['repassword'];

// Cek konfirmasi password
if ($password !== $repassword) {
  echo "<script>alert('Password dan konfirmasi tidak sama!'); window.location.href='registrasi.php';</script>";
  exit;
}

// Enkripsi password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Jika form disubmit dari halaman ini
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['namalengkap'])) {
  $nama = $_POST['namalengkap'];
  $umur = $_POST['umur'];
  $jenis_kelamin = $_POST['jeniskelamin'];
  $no_hp = $_POST['notelp'];
  $namaKT = $_POST['namaKT'];
  $alamatKT = $_POST['alamatKT'];

  // Simpan ke database
  $sql = "INSERT INTO anggota (nama_anggota, jenis_kelamin_anggota, umur_anggota, no_hp_anggota, usn_anggota, pass_anggota)
            VALUES (?, ?, ?, ?, ?, ?)";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "ssisss", $nama, $jenis_kelamin, $umur, $no_hp, $username, $hashedPassword);

  if (mysqli_stmt_execute($stmt)) {
    echo "<script>alert('Registrasi berhasil!'); window.location.href='login.php';</script>";
  } else {
    echo "<script>alert('Terjadi kesalahan: " . mysqli_error($conn) . "');</script>";
  }

  mysqli_stmt_close($stmt);
  mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Data Registrasi</title>
  <style>
    /* Sama seperti sebelumnya */
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
    }

    input[type="text"] {
      padding: 12px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 14px;
    }

    input[type="text"]:focus {
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

    .button-container {
      display: flex;
      justify-content: flex-end;
      margin-top: 30px;
    }

    .btn {
      background-color: #c084fc;
      color: white;
      padding: 12px 30px;
      font-size: 16px;
      border: none;
      border-radius: 12px;
      cursor: pointer;
    }

    .btn:hover {
      background-color: #a855f7;
    }
  </style>
</head>

<body>
  <div class="container">
    <form method="POST">
      <input type="hidden" name="username" value="<?= htmlspecialchars($username) ?>">
      <input type="hidden" name="password" value="<?= htmlspecialchars($password) ?>">
      <input type="hidden" name="repassword" value="<?= htmlspecialchars($repassword) ?>">

      <h1>Identitas Diri</h1>
      <div class="form-row">
        <div class="form-group">
          <label for="namalengkap">Nama Lengkap</label>
          <input type="text" id="namalengkap" name="namalengkap" required />
        </div>
        <div class="form-group">
          <label for="umur">Umur</label>
          <input type="text" id="umur" name="umur" required />
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="jeniskelamin">Jenis Kelamin</label>
          <input type="text" id="jeniskelamin" name="jeniskelamin" required />
        </div>
        <div class="form-group">
          <label for="notelp">No Telepon</label>
          <input type="text" id="notelp" name="notelp" required />
        </div>
      </div>

      <h1>Identitas Karang Taruna</h1>
      <div class="form-group">
        <label for="namaKT">Nama Karang Taruna</label>
        <input type="text" id="namaKT" name="namaKT" />
      </div>

      <div class="form-group" style="margin-top: 20px;">
        <label for="alamatKT">Alamat Karang Taruna</label>
        <input type="text" id="alamatKT" name="alamatKT" />
      </div>

      <div class="form-footer">
        <input type="checkbox" id="konfirmasi" required />
        <label for="konfirmasi">Dengan ini saya menyatakan bahwa saya benar-benar ketua karang taruna dan semua data
          yang diberikan adalah benar</label>
      </div>

      <div class="button-container">
        <button type="submit" class="btn">Daftar</button>
      </div>
    </form>
  </div>
</body>

</html>