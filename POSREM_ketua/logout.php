<?php
session_start(); // Mulai sesi

// Hapus semua variabel sesi
session_unset();

// Hancurkan sesi
session_destroy();

// Alihkan pengguna kembali ke halaman login
header("Location: login.php"); // Pastikan path ini benar ke file login Anda
exit;
?>