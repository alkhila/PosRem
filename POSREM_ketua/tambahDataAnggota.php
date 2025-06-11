<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Tambah Data Anggota</title>
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

    .button-row {
      display: flex;
      justify-content: space-between;
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
      transition: background 0.3s ease;
    }

    .btn:hover {
      background-color: #a855f7;
    }
  </style>
</head>

<body>
  <div class="container">
    <form action="#">
      <h1>Identitas Diri</h1>
      <div class="form-row">
        <div class="form-group">
          <label for="namalengkap">Nama Lengkap</label>
          <input type="text" id="namalengkap" placeholder="exampleruby" />
        </div>
        <div class="form-group">
          <label for="umur">Umur</label>
          <input type="text" id="umur" placeholder="019" />
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="jeniskelamin">Jenis Kelamin</label>
          <input type="text" id="jeniskelamin" placeholder="exampleperempuan" />
        </div>
        <div class="form-group">
          <label for="notelp">No Telepon</label>
          <input type="text" id="notelp" placeholder="08xxx" />
        </div>
      </div>

      <h1>Username dan Password</h1>
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" />
      </div>

      <div class="form-group" style="margin-top: 20px;">
        <label for="pass">Password</label>
        <input type="text" id="pass" />
      </div>

      <div class="form-group" style="margin-top: 20px;">
        <label for="repass">Konfirmasi Password</label>
        <input type="text" id="repass" />
      </div>

      <div class="button-row">
        <button type="button" class="btn" onclick="location.href='dataAnggotaKT_ketua.php'">Kembali</button>
        <button type="submit" class="btn">Daftar</button>
      </div>
    </form>

  </div>
</body>

</html>