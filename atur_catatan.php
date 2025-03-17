<?php
session_start();
include 'db.php'; // Hubungkan ke database

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil user_id dari session
$user_id = $_SESSION['user_id'];

// Menambahkan catatan baru
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_note'])) {
    $judul = $_POST['judul'];
    $catatan = $_POST['catatan'];
    $tanggal = date("Y-m-d");  // Mengambil tanggal sekarang secara otomatis

    // Menangani tanda kutip dan karakter khusus pada input teks
    $judul = mysqli_real_escape_string($conn, $judul); // Mengamankan judul
    $catatan = mysqli_real_escape_string($conn, $catatan); // Mengamankan catatan

    // Menyimpan catatan ke database
    $sql = "INSERT INTO catatan (user_id, tanggal, judul, catatan) VALUES ('$user_id', '$tanggal', '$judul', '$catatan')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>Swal.fire('Berhasil!', 'Catatan berhasil disimpan!', 'success'); window.location.href='atur_catatan.php';</script>";
    } else {
        echo "<script>Swal.fire('Gagal!', 'Terjadi kesalahan saat menyimpan catatan.', 'error');</script>";
    }
}

// Menghapus catatan
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    $sql = "DELETE FROM catatan WHERE id='$id' AND user_id='$user_id'";

    if ($conn->query($sql) === TRUE) {
        echo "<script>Swal.fire('Berhasil!', 'Catatan berhasil dihapus!', 'success'); window.location.href='atur_catatan.php';</script>";
    } else {
        echo "<script>Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus catatan.', 'error');</script>";
    }
}

// Ambil query pencarian dari URL (jika ada)
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// Menampilkan catatan berdasarkan user_id dan tanggal dengan filter pencarian
if ($search_query) {
    $sql = "SELECT * FROM catatan WHERE user_id = '$user_id' AND (judul LIKE '%$search_query%' OR catatan LIKE '%$search_query%') ORDER BY tanggal DESC";
} else {
    $sql = "SELECT * FROM catatan WHERE user_id = '$user_id' ORDER BY tanggal DESC";
}
$result = $conn->query($sql);

// Daftar kata-kata yang menggambarkan perasaan
$feeling_keywords = [
    'sedih' => '“Jangan pernah menyerah, kebahagiaan akan datang setelah kesulitan.”',
    'senang' => '“Jangan lupa untuk selalu bersyukur atas kebahagiaan yang kamu miliki.”',
    'bahagia' => '“Kebahagiaan sejati adalah saat kita bisa berbagi kebahagiaan dengan orang lain.”',
    'marah' => '“Ambil nafas dalam-dalam, dan biarkan kemarahanmu hilang dengan waktu.”',
    'frustrasi' => '“Terkadang kita harus jatuh untuk bisa bangkit kembali lebih kuat.”'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catatan Interaktif</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script> <!-- Font Awesome Icons -->
    <style>
        .background-image {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 50px 0;
        }
        .container {
            padding-top: 30px;
        }
        .header-title {
            font-size: 2.5rem;
            font-weight: 700;
        }
        .note-card {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 10px;
        }
        .note-card:hover {
            background-color: #e9ecef;
        }
        .col-history {
            background-color: #f0f4f7;
            border-radius: 10px;
            padding: 20px;
        }
        .col-note {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px;
        }
        .current-time {
            font-size: 1.5rem;
            font-weight: 600;
        }
        .delete-btn {
            background-color: #e63946;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .delete-btn:hover {
            background-color: #d72e3b;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">Aplikasi Mahasiswa</a>
        <div class="d-flex">
            <span class="navbar-text">Selamat datang, <?php echo $_SESSION['username']; ?></span>
            <a href="logout.php" class="btn btn-outline-danger ms-3">Logout</a>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<div class="background-image text-center text-white">
    <h1 class="header-title">Catatan Harian Anda</h1>
    <p>Catat perasaan Anda hari ini dan temukan kata-kata semangat untuk membantu Anda.</p>
</div>

<!-- Menampilkan Jam Realtime dan Tanggal -->
<div class="container text-center">
    <div id="current-time" class="current-time"></div>
    <div id="current-date"></div>
</div>

<!-- Layout: Kanan untuk Menulis Catatan, Kiri untuk History -->
<div class="container mt-5">
    <div class="row">
        <!-- Kolom untuk History Catatan (Kiri) -->
        <div class="col-md-6 col-history">
            <h3>History Catatan</h3>

            <!-- Form Pencarian -->
            <form method="GET" action="atur_catatan.php" class="mb-3">
                <input type="text" name="search" class="form-control" placeholder="Cari catatan..." value="<?php echo $search_query; ?>">
            </form>

            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="note-card">
                    <h5><?php echo date("l, d F Y", strtotime($row['tanggal'])); ?></h5>
                    <h6><strong>Judul:</strong> <?php echo $row['judul']; ?></h6>
                    <p><?php echo nl2br($row['catatan']); ?></p>
                    
                    <?php
                    // Mencari kata-kata yang menggambarkan perasaan
                    foreach ($feeling_keywords as $keyword => $quote) {
                        if (strpos(strtolower($row['catatan']), $keyword) !== false) {
                            echo "<p><strong>Semangat:</strong> $quote</p>";
                            break;
                        }
                    }
                    ?>
                    <!-- Tombol Hapus -->
                    <a href="?hapus=<?php echo $row['id']; ?>" class="delete-btn">Hapus Catatan</a>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Kolom untuk Menulis Catatan (Kanan) -->
        <div class="col-md-6 col-note">
            <h3>Tambah Catatan Anda</h3>
            <form method="POST" action="atur_catatan.php">
                <div class="mb-3">
                    <label for="judul" class="form-label">Judul Catatan</label>
                    <input type="text" class="form-control" name="judul" required>
                </div>
                <div class="mb-3">
                    <label for="catatan" class="form-label">Catatan Anda</label>
                    <textarea class="form-control" name="catatan" rows="7" required></textarea>
                </div>
                <button type="submit" name="submit_note" class="btn btn-primary">Simpan Catatan</button>
            </form>
        </div>
    </div>
</div>

<!-- SweetAlert2 Script -->
<script>
    // Menampilkan jam dan tanggal realtime
    function updateTime() {
        const now = new Date();
        const hours = now.getHours().toString().padStart(2, '0');
        const minutes = now.getMinutes().toString().padStart(2, '0');
        const seconds = now.getSeconds().toString().padStart(2, '0');
        const time = `${hours}:${minutes}:${seconds}`;
        document.getElementById("current-time").textContent = `Jam Sekarang: ${time}`;

        const date = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        document.getElementById("current-date").textContent = `Tanggal: ${date}`;
    }

    // Update jam setiap detik
    setInterval(updateTime, 1000);
</script>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

</body>
</html>
