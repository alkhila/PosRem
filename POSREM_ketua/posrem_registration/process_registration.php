<?php
// process_registration.php
require_once 'config.php';

// Aktifkan pelaporan error untuk debugging (HANYA SAAT PENGEMBANGAN)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {
        case 'register_new_kt':
            if (isset($_POST['nama_kt'], $_POST['alamat_kt'])) {
                $nama_kt = $conn->real_escape_string($_POST['nama_kt']);
                $alamat_kt = $conn->real_escape_string($_POST['alamat_kt']);

                // Cek apakah nama_kt sudah ada
                $stmt_check = $conn->prepare("SELECT id_kt FROM karang_taruna WHERE nama_kt = ?");
                $stmt_check->bind_param("s", $nama_kt);
                $stmt_check->execute();
                $stmt_check->store_result();

                if ($stmt_check->num_rows > 0) {
                    // Jika sudah terdaftar, redirect kembali ke index.php dengan pesan error
                    header("Location: index.php?status=error&message=" . urlencode('Nama Karang Taruna ini sudah terdaftar.'));
                    exit();
                } else {
                    $stmt_insert = $conn->prepare("INSERT INTO karang_taruna (nama_kt, alamat) VALUES (?, ?)");
                    $stmt_insert->bind_param("ss", $nama_kt, $alamat_kt);

                    if ($stmt_insert->execute()) {
                        $new_id_kt = $conn->insert_id;
                        // Redirect ke register_ketua.php dengan ID Karang Taruna baru
                        header("Location: register_ketua.php?status=success&id_kt=" . $new_id_kt . "&message=" . urlencode('Karang Taruna berhasil didaftarkan. Lanjutkan pendaftaran Ketua.'));
                        exit();
                    } else {
                        // Gagal insert, redirect kembali ke index.php dengan pesan error
                        header("Location: index.php?status=error&message=" . urlencode('Gagal mendaftarkan Karang Taruna: ' . $stmt_insert->error));
                        exit();
                    }
                    $stmt_insert->close();
                }
                $stmt_check->close();
            } else {
                // Data tidak lengkap, redirect kembali ke index.php dengan pesan error
                header("Location: index.php?status=error&message=" . urlencode('Data Karang Taruna tidak lengkap.'));
                exit();
            }
            break;

        default:
            // Aksi tidak dikenal, redirect kembali ke index.php dengan pesan error
            header("Location: index.php?status=error&message=" . urlencode('Aksi tidak dikenal.'));
            exit();
            break;
    }
} else {
    // Jika bukan POST request atau tidak ada action
    header("Location: index.php");
    exit();
}

$conn->close();
?>