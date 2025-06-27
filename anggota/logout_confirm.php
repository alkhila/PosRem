<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'anggota' || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Keluar - PosRem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            background: #F2EBEF; 
            font-family: 'Segoe UI', sans-serif;
            height: 100vh; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
        }
        .confirmation-card {
            background-color: white;
            border: none;
            border-radius: 16px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1); 
            padding: 3rem;
            width: 90%;
            max-width: 500px;
            text-align: center;
        }
        .confirmation-card h4 {
            color: #624FA2; 
            margin-bottom: 1.5rem;
        }
        .confirmation-card .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            font-weight: 600;
            margin: 0 10px; 
        }
        .btn-cancel {
            background-color: #B79EDB;
            color: white;
            border: none;
        }
        .btn-logout {
            background-color: #dc3545; 
            color: white;
            border: none;
        }
    </style>
</head>
<body>
    <div class="confirmation-card">
        <h4>Apakah Anda yakin ingin keluar?</h4>
        <p>Anda akan diarahkan kembali ke halaman login.</p>
        <div>
            <a href="dashboard.php" class="btn btn-cancel">Batal</a>
            <a href="../logout.php" class="btn btn-logout">Ya, Keluar</a>
        </div>
    </div>

    </body>
</html>