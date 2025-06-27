<?php
session_start();

$host = "sql303.infinityfree.com";
$user = "if0_39241783";
$pass = "4WgW6ZbgMpbG";
$db = "if0_39241783_posrem";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'anggota' || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$loggedInUserId = $_SESSION['user_id']; 

$idToEdit = $_GET['id_anggota'] ?? null;

if (!$idToEdit || $idToEdit != $loggedInUserId) {
    header("Location: data_diri_anggota.php"); 
    exit;
}

$anggotaData = []; 
$message = "";
$messageType = "";

$stmt = $conn->prepare("
    SELECT
        a.nama_anggota,
        a.jenis_kelamin_anggota,
        a.umur_anggota,
        a.no_hp_anggota,
        a.id_kt,
        kt.nama_kt AS nama_karang_taruna
    FROM anggota a
    LEFT JOIN karang_taruna kt ON a.id_kt = kt.id_kt
    WHERE a.id_anggota = ?
");
$stmt->bind_param("i", $idToEdit);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $anggotaData = $result->fetch_assoc();
} else {
    header("Location: data_diri_anggota.php"); 
    exit;
}
$stmt->close();

$karangTarunaList = [];
$stmtKt = $conn->prepare("SELECT id_kt, nama_kt FROM karang_taruna ORDER BY nama_kt ASC");
$stmtKt->execute();
$resultKt = $stmtKt->get_result();
while ($row = $resultKt->fetch_assoc()) {
    $karangTarunaList[] = $row;
}
$stmtKt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_lengkap = $_POST['nama_lengkap'] ?? '';
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $umur = $_POST['umur'] ?? 0;
    $id_karang_taruna = $_POST['id_karang_taruna'] ?? null;
    $no_telepon = $_POST['no_telepon'] ?? '';

    $stmtUpdate = $conn->prepare("
        UPDATE anggota SET
            nama_anggota = ?,
            jenis_kelamin_anggota = ?,
            umur_anggota = ?,
            id_kt = ?,
            no_hp_anggota = ?
        WHERE id_anggota = ?
    ");

    $stmtUpdate->bind_param("ssiisi",
        $nama_lengkap,
        $jenis_kelamin,
        $umur,
        $id_karang_taruna,
        $no_telepon,
        $idToEdit
    );

    if ($stmtUpdate->execute()) {
        $message = "Data anggota berhasil diperbarui!";
        $messageType = "success";
        $anggotaData['nama_anggota'] = $nama_lengkap;
        $anggotaData['jenis_kelamin_anggota'] = $jenis_kelamin;
        $anggotaData['umur_anggota'] = $umur;
        $anggotaData['id_kt'] = $id_karang_taruna;
        foreach ($karangTarunaList as $kt) {
            if ($kt['id_kt'] == $id_karang_taruna) {
                $anggotaData['nama_karang_taruna'] = $kt['nama_kt'];
                break;
            }
        }
        $anggotaData['no_hp_anggota'] = $no_telepon;

    } else {
        $message = "Gagal memperbarui data anggota: " . $stmtUpdate->error;
        $messageType = "danger";
    }
    $stmtUpdate->close();
}

$conn->close();

$currentNamaLengkap = $anggotaData['nama_anggota'] ?? '';
$currentJenisKelamin = $anggotaData['jenis_kelamin_anggota'] ?? '';
$currentUmur = $anggotaData['umur_anggota'] ?? '';
$currentIdKt = $anggotaData['id_kt'] ?? '';
$currentNoTelepon = $anggotaData['no_hp_anggota'] ?? '';

?>

<!DOCTYPE html>
<html lang="id">
<head>
     <meta charset="UTF-8">
     <title>Edit Data Anggota - PosRem</title>
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
     <style>
     body {
    margin: 0;
    background: #F2EBEF;
    font-family: 'Segoe UI', sans-serif;
    height: 100%;
     }
     .sidebar {
     height: 100vh;
     width: 250px;
     background-color: white;
     box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
     padding-top: 2rem;
     position: sticky;
     top: 0;
     }
     .nav-link {
     color: black;
     font-weight: 500;
     margin-bottom: 1rem;
    display: flex;
     align-items: center;
     }
     .nav-link.active,
     .nav-link:hover {
     color: #624FA2;
     }
     .nav-link span {
     margin-left: 10px;
     }
     .brand {
     font-weight: bold;
    font-size: 1.5rem;
     color: #333;
     margin-bottom: 2rem;
     display: flex;
     align-items: center;
     }
     .brand-icon {
     background-color: #A28DD0;
     border-radius: 12px;
     width: 32px;
     height: 32px;
     display: flex;
     align-items: center;
     justify-content: center;
     color: white;
     margin-right: 10px;
     font-size: 1rem;
     }
     .main-content {
     padding: 2rem;
     }
     .card {
     border-radius: 16px;
     }
     .btn-submit {
     background-color: #bb86db; /* ungu lembut */
     color: white;
     padding: 6px 16px;
     font-size: 0.875rem;
     border: none;
     border-radius: 50px;
     transition: background-color 0.3s ease;
     }
     .btn-submit:hover {
     background-color: #a06cd5;
     color: white;
     }
     .custom-purple {
     color: #624FA2;
     font-weight: 500;
     }
     .btn-purple {
     background-color: #B388EB;
     color: white;
     border: none;
     }
    .label-purple {
        color: #624FA2;
        font-weight: 500;
    }
</style>
</head>
<body>
     <div class="d-flex">
     <div class="sidebar p-4">
     <div class="brand">
     <div class="brand-icon"><i class="bi bi-heart-fill"></i></div>
     PosRem
     </div>
     <ul class="nav flex-column">
    <li class="nav-item">
     <a href="dashboard.php" class="nav-link"><i class="bi bi-grid-fill"></i> <span>Dashboard</span></a>
     </li>
     <li class="nav-item">
     <a href="data_kesehatan.php" class="nav-link"><i class="bi bi-clipboard-heart"></i> <span>Data Kesehatan</span></a>
     </li>
     <li class="nav-item">
     <a href="pesan_kesehatan.php" class="nav-link"><i class="bi bi-chat-dots"></i> <span>Pesan Kesehatan</span></a>
    </li>
     <li class="nav-item">
     <a href="data_diri_anggota.php" class="nav-link active"><i class="bi bi-person-circle"></i> <span>Data Diri</span></a>
     </li>
     <li class="nav-item">
            <a href="logout_confirm.php" class="nav-link"><i class="bi bi-box-arrow-left"></i> <span>Keluar</span></a>
         </li>
         </ul>
     </div>

         <div class="main-content flex-grow-1">
         <div class="" style="max-width: 1000px;">
         <div class="card p-5 rounded-4 shadow-sm" style="background-color: white;">
         <h5 class="mb-4 label-purple">Edit Data Diri</h5>
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
             <form method="POST" action="">
                 <div class="row g-4">
                    <div class="col-md-6">
                        <label for="nama_lengkap" class="form-label label-purple">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?php echo htmlspecialchars($currentNamaLengkap); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="jenis_kelamin" class="form-label label-purple">Jenis Kelamin</label>
                        <select class="form-select" id="jenis_kelamin" name="jenis_kelamin">
                            <option value="Laki-laki" <?php echo ($currentJenisKelamin == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                            <option value="Perempuan" <?php echo ($currentJenisKelamin == 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                        </select>
                     </div>
                    <div class="col-md-6">
                         <label for="umur" class="form-label label-purple">Umur</label>
                         <input type="number" class="form-control" id="umur" name="umur" value="<?php echo htmlspecialchars($currentUmur); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="karang_taruna" class="form-label label-purple">Karang Taruna</label>
                        <select class="form-select" id="karang_taruna" name="id_karang_taruna">
                            <?php foreach ($karangTarunaList as $kt): ?>
                                <option value="<?php echo $kt['id_kt']; ?>" <?php echo ($kt['id_kt'] == $currentIdKt) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($kt['nama_kt']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                    <label for="no_telepon" class="form-label label-purple">No Telepon</label>
                     <input type="tel" class="form-control" id="no_telepon" name="no_telepon" value="<?php echo htmlspecialchars($currentNoTelepon); ?>">
                     </div>
                    <div class="col-md-6"></div> 
                    </div>

                     <div class="d-flex justify-content-between mt-5">
                     <a href="data_diri_anggota.php" class="btn btn-purple px-4 rounded-pill">
                          Kembali</a>
                     <button type="submit" class="btn btn-purple px-4 rounded-pill">Simpan</button>
                     </div>
                 </form>
                </div>
             </div>
         </div>

     </div>


     <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</body>
</html>