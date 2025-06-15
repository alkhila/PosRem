<?php
require_once 'config.php';

$selected_kt_id = null;
$selected_kt_name = '';
$message = '';
$message_type = '';

$usn_ketua_val = '';
$nama_ketua_val = '';
$jenis_kelamin_ketua_val = '';
$umur_ketua_val = '';
$no_hp_ketua_val = '';
$tempat_tanggal_lahir_val = '';
$alamat_rumah_val = '';
$agreement_val = false;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id_kt'])) {
    $selected_kt_id = (int) $_GET['id_kt'];
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_kt'])) {
    $selected_kt_id = (int) $_POST['id_kt'];
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
        $message = 'ID Karang Taruna tidak valid atau tidak ditemukan.';
        $message_type = 'error';
        $selected_kt_id = null;
    }
    $stmt->close();
} else {
    $message = 'Silakan pilih atau daftarkan Karang Taruna terlebih dahulu di halaman utama.';
    $message_type = 'error';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register_ketua') {
    $usn_ketua = trim($_POST['usn_ketua'] ?? '');
    $pass_ketua = $_POST['pass_ketua'] ?? '';
    $konf_pass_ketua = $_POST['konf_pass_ketua'] ?? '';
    $nama_ketua = trim($_POST['nama_ketua'] ?? '');
    $jenis_kelamin_ketua = $_POST['jenis_kelamin_ketua'] ?? '';
    $umur_ketua = (int) ($_POST['umur_ketua'] ?? 0);
    $no_hp_ketua = trim($_POST['no_hp_ketua'] ?? '');
    $tempat_tanggal_lahir = trim($_POST['tempat_tanggal_lahir'] ?? '');
    $alamat_rumah = trim($_POST['alamat_rumah'] ?? '');
    $agreement = isset($_POST['agreement']);

    $usn_ketua_val = $usn_ketua;
    $nama_ketua_val = $nama_ketua;
    $jenis_kelamin_ketua_val = $jenis_kelamin_ketua;
    $umur_ketua_val = $umur_ketua;
    $no_hp_ketua_val = $no_hp_ketua;
    $tempat_tanggal_lahir_val = $tempat_tanggal_lahir;
    $alamat_rumah_val = $alamat_rumah;
    $agreement_val = $agreement;

    if (!$selected_kt_id) {
        $message = 'ID Karang Taruna tidak ditemukan. Silakan ulangi proses pendaftaran.';
        $message_type = 'error';
    } else {
        if (empty($usn_ketua) || empty($pass_ketua) || empty($konf_pass_ketua) || empty($nama_ketua) || empty($jenis_kelamin_ketua) || empty($umur_ketua) || empty($no_hp_ketua)) {
            $message = 'Semua field wajib diisi (kecuali Tempat, Tanggal Lahir dan Alamat Rumah).';
            $message_type = 'error';
        } elseif ($pass_ketua !== $konf_pass_ketua) {
            $message = 'Password dan Konfirmasi Password tidak cocok.';
            $message_type = 'error';
        } elseif (!$agreement) {
            $message = 'Anda harus menyetujui pernyataan untuk melanjutkan.';
            $message_type = 'error';
        } else {
            $stmt_check_usn = $conn->prepare("SELECT id_ketua FROM ketua_karang_taruna WHERE usn_ketua = ?");
            $stmt_check_usn->bind_param("s", $usn_ketua);
            $stmt_check_usn->execute();
            $stmt_check_usn->store_result();

            if ($stmt_check_usn->num_rows > 0) {
                $message = 'Username sudah digunakan. Silakan pilih username lain.';
                $message_type = 'error';
            } else {
                $pass_ketua_to_save = password_hash($pass_ketua, PASSWORD_DEFAULT);

                $stmt_insert = $conn->prepare("INSERT INTO ketua_karang_taruna (id_kt, usn_ketua, pass_ketua, nama_ketua, jenis_kelamin_ketua, umur_ketua, no_hp_ketua, tempat_tanggal_lahir, alamat_rumah) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt_insert->bind_param("issssisss", $selected_kt_id, $usn_ketua, $pass_ketua_to_save, $nama_ketua, $jenis_kelamin_ketua, $umur_ketua, $no_hp_ketua, $tempat_tanggal_lahir, $alamat_rumah);

                if ($stmt_insert->execute()) {
                    $message = 'Pendaftaran Ketua Karang Taruna berhasil!';
                    $message_type = 'success';
                    $usn_ketua_val = $pass_ketua = $konf_pass_ketua = $nama_ketua_val = $no_hp_ketua_val = '';
                    $jenis_kelamin_ketua_val = '';
                    $umur_ketua_val = '';
                    $tempat_tanggal_lahir_val = '';
                    $alamat_rumah_val = '';
                    $agreement_val = false;

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
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to bottom, #fddde6, #cce7f5);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 20px;
        }

        .container {
            background: white;
            width: 700px;
            max-width: 95%;
            padding: 40px 50px;
            border-radius: 12px;
            margin-top: 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
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
            font-weight: bold;
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

        input:focus,
        textarea:focus,
        select:focus {
            border-color: #3b82f6;
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
        }

        .form-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            accent-color: #7c3aed;
        }

        .checkbox-label-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkbox-label-wrapper label {
            margin: 0;
            font-weight: normal;
        }

        select,
        option {
            font-family: 'Segoe UI', sans-serif;
        }

        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #c084fc;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 20px;
        }

        button[type="submit"]:hover {
            background-color: #a855f7;
        }

        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
            font-weight: bold;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .back-link,
        .login-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #6a0dad;
            text-decoration: none;
            font-weight: bold;
        }

        .back-link:hover,
        .login-link:hover {
            text-decoration: underline;
        }

        .button-like-link {
            display: block;
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 18px;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 15px;
        }

        .button-like-link:hover {
            background-color: #0056b3;
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
            <p style="text-align: center; margin-bottom: 20px;">Karang Taruna Terpilih: <strong
                    id="selected_kt_name_display"><?php echo htmlspecialchars($selected_kt_name); ?></strong></p>

            <form action="register_ketua.php" method="POST">
                <input type="hidden" name="action" value="register_ketua">
                <input type="hidden" name="id_kt" value="<?php echo htmlspecialchars($selected_kt_id); ?>">

                <div class="form-group">
                    <label for="usn_ketua">Username Ketua:</label>
                    <input type="text" id="usn_ketua" name="usn_ketua"
                        value="<?php echo htmlspecialchars($usn_ketua_val); ?>" required>
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
                    <input type="text" id="nama_ketua" name="nama_ketua"
                        value="<?php echo htmlspecialchars($nama_ketua_val); ?>" required>
                </div>
                <div class="form-group">
                    <label for="jenis_kelamin_ketua">Jenis Kelamin Ketua:</label>
                    <select id="jenis_kelamin_ketua" name="jenis_kelamin_ketua" required>
                        <option value="">Pilih</option>
                        <option value="Laki-laki" <?php echo ($jenis_kelamin_ketua_val == 'Laki-laki') ? 'selected' : ''; ?>>
                            Laki-laki</option>
                        <option value="Perempuan" <?php echo ($jenis_kelamin_ketua_val == 'Perempuan') ? 'selected' : ''; ?>>
                            Perempuan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="umur_ketua">Umur Ketua:</label>
                    <input type="number" id="umur_ketua" name="umur_ketua" min="17" max="99"
                        value="<?php echo htmlspecialchars($umur_ketua_val); ?>" required>
                </div>
                <div class="form-group">
                    <label for="no_hp_ketua">No. HP Ketua:</label>
                    <input type="tel" id="no_hp_ketua" name="no_hp_ketua" placeholder="e.g., 081234567890"
                        value="<?php echo htmlspecialchars($no_hp_ketua_val); ?>" required>
                </div>

                <div class="form-group">
                    <label for="tempat_tanggal_lahir">Tempat, Tanggal Lahir:</label>
                    <input type="text" id="tempat_tanggal_lahir" name="tempat_tanggal_lahir"
                        placeholder="e.g., Yogyakarta, 1 Januari 1990"
                        value="<?php echo htmlspecialchars($tempat_tanggal_lahir_val); ?>">
                </div>
                <div class="form-group">
                    <label for="alamat_rumah">Alamat Rumah:</label>
                    <textarea id="alamat_rumah" name="alamat_rumah" rows="3"
                        placeholder="Masukkan alamat lengkap Anda"><?php echo htmlspecialchars($alamat_rumah_val); ?></textarea>
                </div>

                <div class="form-group checkbox-label-wrapper">
                    <input type="checkbox" id="agreement" name="agreement" <?php echo ($agreement_val) ? 'checked' : ''; ?>
                        required>
                    <label for="agreement">Dengan ini saya menyatakan bahwa saya benar-benar ketua karang taruna dan semua
                        data yang diberikan adalah benar.</label>
                </div>
                <button type="submit">Daftar</button>

                <?php if ($message_type == 'success'): ?>
                    <a href="../login.php" class="button-like-link" style="background-color: #28a745;">Lanjut ke Halaman
                        Login</a>
                <?php endif; ?>
                <a href="index.php" class="back-link">Kembali ke Halaman Pendaftaran Awal</a>

            </form>
        <?php else: ?>
            <p style="text-align: center;">Tidak ada Karang Taruna yang terpilih atau ditemukan.</p>
            <a href="index.php" class="back-link">Kembali ke Halaman Pendaftaran Awal</a>
            <a href="../login.php" class="button-like-link" style="background-color: #007bff;">Kembali ke Halaman Login</a>
        <?php endif; ?>
    </div>
</body>

</html>