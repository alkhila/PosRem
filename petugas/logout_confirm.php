<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'petugas_puskesmas' || !isset($_SESSION['user_id'])) {
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
        /* Reusing your existing CSS for consistency */
        body {
            margin: 0;
            background: #F2EBEF; /* Light purple background */
            font-family: 'Segoe UI', sans-serif;
            height: 100vh; /* Full viewport height */
            display: flex; /* Use flexbox for centering */
            justify-content: center; /* Center horizontally */
            align-items: center; /* Center vertically */
        }
        .confirmation-card {
            background-color: white;
            border: none;
            border-radius: 16px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1); /* Subtle shadow */
            padding: 3rem;
            width: 90%;
            max-width: 500px; /* Max width for better readability */
            text-align: center;
        }
        .confirmation-card h4 {
            color: #624FA2; /* Purple text for heading */
            margin-bottom: 1.5rem;
        }
        .confirmation-card .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            font-weight: 600;
            margin: 0 10px; /* Space between buttons */
        }
        .btn-cancel {
            background-color: #B79EDB; /* Lighter purple */
            color: white;
            border: none;
        }
        .btn-logout {
            background-color: #dc3545; /* Red for danger/logout */
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
            <a href="dashboard_petugas.php" class="btn btn-cancel">Batal</a>
            <a href="../logout.php" class="btn btn-logout">Ya, Keluar</a>
        </div>
    </div>

    </body>
</html>