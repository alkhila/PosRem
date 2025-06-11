<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrasi</title>
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
      width: 1100px;
      height: 600px;
      max-width: 90%;
      display: flex;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }

    .form-section {
      flex: 1;
      padding: 50px 30px;
    }

    .form-section h1 {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 24px;
      margin-top: 5px;
      position: relative;
      top: -20px;
    }

    .form-section h1 img {
      width: 55px;
      height: 55px;
    }

    .form-group {
      margin-bottom: 20px;
      margin-left: 30px;
    }

    .form-group label {
      display: block;
      font-size: 16px;
      margin-bottom: 5px;
    }

    .form-group input {
      width: 90%;
      padding: 15px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 16px;
    }

    input:focus {
      border-color: #3b82f6;
      outline: none;
      box-shadow: 0 0 0 5px rgba(59, 130, 246, 0.3);
    }

    .form-footer {
      font-size: 14px;
      margin-bottom: 20px;
      text-align: center;
    }

    .form-footer a {
      color: #c084fc;
      text-decoration: none;
      font-weight: bold;
    }

    .btn {
      width: 90%;
      padding: 12px;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      color: white;
      background-color: #c084fc;
      cursor: pointer;
      margin-bottom: 12px;
      margin-left: 30px;
      transition: background 0.3s ease;
    }

    .btn:hover {
      background-color: #a855f7;
    }

    .image-section {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #f8f8f8;
      padding: 20px;
    }

    .image-section img {
      width: 100%;
      max-width: 400px;
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="form-section">
      <h1>
        <img src="asset/logo_posrem.png" alt="Logo PosRem">
        PosRem
      </h1>
      <form action="dataRegistrasi.php" method="POST">
        <br>
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" placeholder="exampleruby_" required>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="rubyexample123" required>
        </div>
        <div class="form-group">
          <label for="repassword">Konfirmasi Password</label>
          <input type="password" id="repassword" name="repassword" placeholder="rubyexample123" required>
        </div>
        <br>
        <div class="form-footer">
          Sudah punya akun? <a href="login.php">Masuk</a>
        </div>
        <button class="btn" type="submit">Selanjutnya</button>
      </form>
    </div>
    <div class="image-section">
      <img src="asset/logo_gradasi.png" alt="Logo Gradasi">
    </div>
  </div>
</body>

</html>