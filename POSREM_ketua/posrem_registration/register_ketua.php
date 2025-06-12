<?php
// register_ketua.php
require_once 'config.php';

$selected_kt_id = null;
$selected_kt_name = '';
$message = '';
$message_type = '';

// Ambil ID Karang Taruna dari GET parameter atau POST
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id_kt'])) {
    $selected_kt_id = (int)$_GET['id_kt'];
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_kt'])) {
    // Ini untuk kasus jika redirect dari process_registration.php setelah pendaftaran KT baru
    $selected_kt_id = (int)$_POST['id_kt'];
    // Juga bisa ada pesan sukses dari pendaftaran KT sebelumnya
    if (isset($_POST['status']) && $_POST['status'] === 'success') {
        $message = 'Karang Taruna berhasil didaftarkan. Lanjutkan pendaftaran Ketua.';
        $message_type = 'success';
    }
}

if ($selected_kt_id) {
    $stmt = $conn->prepare("SELECT nama_kt FROM karang_taruna WHERE id_kt = ?");
    $stmt->bind_param("i", $selected_kt_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $selected_kt_name = $row['nama_kt'];
    } else {
        // Jika ID KT tidak valid atau tidak ditemukan
        $message = 'ID Karang Taruna tidak valid atau tidak ditemukan.';
        $message_type = 'error';
        $selected_kt_id = null; // Reset ID agar form tidak tampil
    }
    $stmt->close();
} else {
    $message = 'Silakan pilih atau daftarkan Karang Taruna terlebih dahulu di halaman utama.';
    $message_type = 'error';
}

// Handle form submission untuk Ketua Karang Taruna
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register_ketua') {
    // Inisialisasi variabel untuk menghindari PHP Notice jika tidak ada di POST
    $usn_ketua = trim($_POST['usn_ketua'] ?? '');
    $pass_ketua = $_POST['pass_ketua'] ?? '';
    $konf_pass_ketua = $_POST['konf_pass_ketua'] ?? '';
    $nama_ketua = trim($_POST['nama_ketua'] ?? '');
    $jenis_kelamin_ketua = $_POST['jenis_kelamin_ketua'] ?? '';
    $umur_ketua = (int)($_POST['umur_ketua'] ?? 0);
    $no_hp_ketua = trim($_POST['no_hp_ketua'] ?? '');
    $agreement = isset($_POST['agreement']);

    if (!$selected_kt_id) {
        $message = 'ID Karang Taruna tidak ditemukan. Silakan ulangi proses pendaftaran.';
        $message_type = 'error';
    } else {
        // Validasi
        if (empty($usn_ketua) || empty($pass_ketua) || empty($konf_pass_ketua) || empty($nama_ketua) || empty($jenis_kelamin_ketua) || empty($umur_ketua) || empty($no_hp_ketua)) {
            $message = 'Semua field wajib diisi.';
            $message_type = 'error';
        } elseif ($pass_ketua !== $konf_pass_ketua) {
            $message = 'Password dan Konfirmasi Password tidak cocok.';
            $message_type = 'error';
        } elseif (!$agreement) {
            $message = 'Anda harus menyetujui pernyataan untuk melanjutkan.';
            $message_type = 'error';
        } else {
            // Cek apakah username sudah ada
            $stmt_check_usn = $conn->prepare("SELECT id_ketua FROM ketua_karang_taruna WHERE usn_ketua = ?");
            $stmt_check_usn->bind_param("s", $usn_ketua);
            $stmt_check_usn->execute();
            $stmt_check_usn->store_result();

            if ($stmt_check_usn->num_rows > 0) {
                $message = 'Username sudah digunakan. Silakan pilih username lain.';
                $message_type = 'error';
            } else {
                // *** PENTING: Password TIDAK di-hash sesuai permintaan Anda ***
                // *** PERINGATAN: Menyimpan password plaintext sangat tidak aman ***
                $pass_ketua_to_save = $pass_ketua;

                $stmt_insert = $conn->prepare("INSERT INTO ketua_karang_taruna (id_kt, usn_ketua, pass_ketua, nama_ketua, jenis_kelamin_ketua, umur_ketua, no_hp_ketua) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt_insert->bind_param("issssii", $selected_kt_id, $usn_ketua, $pass_ketua_to_save, $nama_ketua, $jenis_kelamin_ketua, $umur_ketua, $no_hp_ketua);

                if ($stmt_insert->execute()) {
                    $message = 'Pendaftaran Ketua Karang Taruna berhasil!';
                    $message_type = 'success';
                    // Kosongkan form setelah sukses (opsional)
                    $usn_ketua = $pass_ketua = $konf_pass_ketua = $nama_ketua = $no_hp_ketua = '';
                    $jenis_kelamin_ketua = '';
                    $umur_ketua = '';
                    $agreement = false;

                } else {
                    $message = 'Gagal mendaftarkan Ketua Karang Taruna: ' . $stmt_insert->error;
                    $message_type = 'error';
                }
                $stmt_insert->close();
            }
            $stmt_check_usn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Ketua Karang Taruna</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        input[type="text"],
        input[type="password"],
        input[type="number"],
        input[type="tel"],
        textarea,
        select {
            width: calc(100% - 20px);
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }
        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #6a0dad; /* Ungu */
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button[type="submit"]:hover {
            background-color: #5a0a9a;
        }
        .message {
            margin-top: 15px;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        .back-link, .login-link { /* Tambah class .login-link */
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #6a0dad;
            text-decoration: none;
        }
        .back-link:hover, .login-link:hover { /* Tambah class .login-link */
            text-decoration: underline;
        }
        .button-like-link { /* Style untuk link yang mirip tombol */
            display: block;
            width: 100%;
            padding: 12px;
            background-color: #4CAF50; /* Warna hijau, bisa disesuaikan */
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 18px;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 15px; /* Spasi dari elemen di atasnya */
        }
        .button-like-link:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Form Pendaftaran Ketua Karang Taruna</h2>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($selected_kt_id): ?>
            <p>Karang Taruna Terpilih: <strong id="selected_kt_name_display"><?php echo htmlspecialchars($selected_kt_name); ?></strong></p>

            <form action="register_ketua.php" method="POST">
                <input type="hidden" name="action" value="register_ketua">
                <input type="hidden" name="id_kt" value="<?php echo htmlspecialchars($selected_kt_id); ?>">

                <div class="form-group">
                    <label for="usn_ketua">Username Ketua:</label>
                    <input type="text" id="usn_ketua" name="usn_ketua" value="<?php echo htmlspecialchars($usn_ketua ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="pass_ketua">Password Ketua:</label>
                    <input type="password" id="pass_ketua" name="pass_ketua" required>
                </div>
                <div class="form-group">
                    <label for="konf_pass_ketua">Konfirmasi Password Ketua:</label>
                    <input type="password" id="konf_pass_ketua" name="konf_pass_ketua" required>
                </div>

                <div class="form-group">
                    <label for="nama_ketua">Nama Lengkap Ketua:</label>
                    <input type="text" id="nama_ketua" name="nama_ketua" value="<?php echo htmlspecialchars($nama_ketua ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="jenis_kelamin_ketua">Jenis Kelamin Ketua:</label>
                    <select id="jenis_kelamin_ketua" name="jenis_kelamin_ketua" required>
                        <option value="">Pilih</option>
                        <option value="Laki-laki" <?php echo (isset($jenis_kelamin_ketua) && $jenis_kelamin_ketua == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                        <option value="Perempuan" <?php echo (isset($jenis_kelamin_ketua) && $jenis_kelamin_ketua == 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="umur_ketua">Umur Ketua:</label>
                    <input type="number" id="umur_ketua" name="umur_ketua" min="17" max="99" value="<?php echo htmlspecialchars($umur_ketua ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="no_hp_ketua">No. HP Ketua:</label>
                    <input type="tel" id="no_hp_ketua" name="no_hp_ketua" placeholder="e.g., 081234567890" value="<?php echo htmlspecialchars($no_hp_ketua ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <input type="checkbox" id="agreement" name="agreement" <?php echo (isset($agreement) && $agreement) ? 'checked' : ''; ?> required>
                    <label for="agreement">Dengan ini saya menyatakan bahwa saya benar-benar ketua karang taruna dan semua data yang diberikan adalah benar.</label>
                </div>
                <button type="submit">Daftar</button>

                <?php if ($message_type == 'success'): ?>
                    <a href="../login.php" class="button-like-link" style="background-color: #28a745;">Lanjut ke Halaman Login</a>
                <?php endif; ?>
                <a href="index.php" class="back-link">Kembali ke Halaman Pendaftaran Awal</a>

            </form>
        <?php else: ?>
            <p>Tidak ada Karang Taruna yang terpilih atau ditemukan.</p>
            <a href="index.php" class="back-link">Kembali ke Halaman Pendaftaran Awal</a>
            <a href="../login.php" class="button-like-link" style="background-color: #007bff;">Kembali ke Halaman Login</a> <?php endif; ?>
    </div>
</body>
</html>