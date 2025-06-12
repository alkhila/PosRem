<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Ketua Karang Taruna</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to bottom, #fddde6, #cce7f5);
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
            font-weight: 500;
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

        input:focus {
            border-color: #3b82f6;
            outline: none;
            box-shadow: 0 0 0 5px rgba(59, 130, 246, 0.3);
        }

        button[type="submit"],
        .button-link {
            width: 100%;
            padding: 12px;
            background-color: #c084fc;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-align: center;
            text-decoration: none;
            display: block;
            margin-top: 15px;
        }

        button[type="submit"]:hover,
        .button-link:hover {
            background-color: #a855f7;
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

        .form-section {
            border: 1px solid #eee;
            padding: 20px;
            margin-top: 20px;
            border-radius: 5px;
            background-color: #fdfdfd;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Pendaftaran Ketua Karang Taruna</h2>

        <?php
        require_once 'config.php';

        $message = '';
        $message_type = '';
        $show_new_kt_form = false;
        $show_existing_kt_dropdown = false;
        $existing_kts = [];
        $nama_kt_check_val = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'check_kt') {
            $nama_kt_check_val = trim($_POST['nama_kt_check']);
            if (empty($nama_kt_check_val)) {
                $message = 'Nama Karang Taruna tidak boleh kosong.';
                $message_type = 'error';
            } else {
                $stmt = $conn->prepare("SELECT id_kt, nama_kt FROM karang_taruna WHERE nama_kt LIKE ?");
                $search_param = '%' . $nama_kt_check_val . '%';
                $stmt->bind_param("s", $search_param);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $message = 'Karang Taruna ditemukan. Silakan pilih dan lanjutkan pendaftaran Ketua.';
                    $message_type = 'success';
                    $show_existing_kt_dropdown = true;
                    while ($row = $result->fetch_assoc()) {
                        $existing_kts[] = $row;
                    }
                } else {
                    $message = 'Karang Taruna "' . htmlspecialchars($nama_kt_check_val) . '" belum terdaftar. Silakan daftarkan di bawah.';
                    $message_type = 'error';
                    $show_new_kt_form = true;
                }
                $stmt->close();
            }
        }
        ?>

        <form action="index.php" method="POST">
            <input type="hidden" name="action" value="check_kt">
            <div class="form-group">
                <label for="nama_kt_check">Nama Karang Taruna:</label>
                <input type="text" id="nama_kt_check" name="nama_kt_check"
                    placeholder="Masukkan Nama Karang Taruna Anda"
                    value="<?php echo htmlspecialchars($nama_kt_check_val); ?>">
            </div>
            <button type="submit">Cek Karang Taruna</button>
            <?php if (!empty($message)): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
        </form>

        <?php if ($show_new_kt_form): ?>
            <div id="kt_new_registration_section" class="form-section">
                <h3>Daftarkan Karang Taruna Baru</h3>
                <form action="process_registration.php" method="POST">
                    <input type="hidden" name="action" value="register_new_kt">
                    <div class="form-group">
                        <label for="nama_kt_new">Nama Karang Taruna:</label>
                        <input type="text" id="nama_kt_new" name="nama_kt"
                            value="<?php echo htmlspecialchars($nama_kt_check_val); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="alamat_kt_new">Alamat Karang Taruna:</label>
                        <textarea id="alamat_kt_new" name="alamat_kt" rows="3" required></textarea>
                    </div>
                    <button type="submit">Daftarkan Karang Taruna & Lanjutkan</button>
                </form>
            </div>
        <?php endif; ?>

        <?php if ($show_existing_kt_dropdown): ?>
            <div id="kt_existing_selection_section" class="form-section">
                <h3>Pilih Karang Taruna</h3>
                <form action="register_ketua.php" method="GET">
                    <div class="form-group">
                        <label for="existing_kt_dropdown">Karang Taruna Tersedia:</label>
                        <select id="existing_kt_dropdown" name="id_kt" required>
                            <?php foreach ($existing_kts as $kt): ?>
                                <option value="<?php echo htmlspecialchars($kt['id_kt']); ?>">
                                    <?php echo htmlspecialchars($kt['nama_kt']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit">Lanjutkan Pendaftaran Ketua</button>
                </form>
            </div>
        <?php endif; ?>

    </div>
</body>

</html>