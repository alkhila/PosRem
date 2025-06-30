<?php
$host = "sql303.infinityfree.com";
$user = "if0_39241783";
$pass = "4WgW6ZbgMpbG";
$db = "if0_39241783_posrem";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

session_start(); 

$loginError = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $loggedIn = false;
    $role = "";
    $userId = null; 

    // 1. Cek di tabel 'ketua_karang_taruna'
    $stmt = $conn->prepare("SELECT usn_ketua, pass_ketua, id_ketua FROM ketua_karang_taruna WHERE usn_ketua = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if ($password === $user['pass_ketua']) { 
            $loggedIn = true;
            $role = "ketua";
            $userId = $user['id_ketua']; 
        }
    }
    $stmt->close();

    // 2. Jika belum login, cek di tabel 'petugas_puskesmas'
    if (!$loggedIn) {
        $stmt = $conn->prepare("SELECT usn_petugas, pass_petugas, id_petugas FROM petugas_puskesmas WHERE usn_petugas = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if ($password === $user['pass_petugas']) { 
                $loggedIn = true;
                $role = "petugas_puskesmas";
                $userId = $user['id_petugas']; 
            }
        }
        $stmt->close();
    }

    // 3. Jika belum login, cek di tabel 'anggota'
    if (!$loggedIn) {
        $stmt = $conn->prepare("SELECT usn_anggota, pass_anggota, id_anggota FROM anggota WHERE usn_anggota = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if ($password === $user['pass_anggota']) { 
                $loggedIn = true;
                $role = "anggota";
                $userId = $user['id_anggota']; 
            }
        }
        $stmt->close();
    }

    // Arahkan pengguna setelah semua pemeriksaan
    if ($loggedIn) {
        $_SESSION["username"] = $username;
        $_SESSION["role"] = $role;
        $_SESSION["user_id"] = $userId; 

        switch ($role) {
            case 'ketua':
                header("Location: dashboard_ketua.php");
                break;
            case 'petugas_puskesmas':
                header("Location: petugas/dashboard_petugas.php");
                break;
            case 'anggota':
            default:
                header("Location: anggota/dashboard.php");
                break;
        }
        exit; 
    } else {
        $loginError = "Username atau Password salah.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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

        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="form-section">
            <h1>
                PosRem
            </h1>
            <br><br>
            <form method="POST" action="login.php">
                <?php if ($loginError): ?>
                    <div class="error-message"><?php echo $loginError; ?></div>
                <?php endif; ?>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="exampleruby_" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="rubyexample123" required>
                </div>
                <br><br>
                <button class="btn" type="submit">Log In</button>
            </form>
        </div>
        <div class="image-section">
            <img src="Logo.png" alt="">
        </div>
    </div>
</body>

</html>