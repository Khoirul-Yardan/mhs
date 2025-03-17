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

// Menambahkan jadwal kuliah baru
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah_jadwal'])) {
    $hari = $_POST['hari'];
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];
    $matkul = $_POST['matkul'];
    $dosen = $_POST['dosen'];
    $tanggal = $_POST['tanggal'];

    // Query untuk menambahkan jadwal
    $sql = "INSERT INTO jadwal_kuliah (user_id, hari, jam_mulai, jam_selesai, matkul, dosen, tanggal) 
            VALUES ('$user_id', '$hari', '$jam_mulai', '$jam_selesai', '$matkul', '$dosen', '$tanggal')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>Swal.fire('Berhasil!', 'Jadwal berhasil ditambahkan!', 'success'); window.location.href='atur_jadwal.php';</script>";
    } else {
        echo "<script>Swal.fire('Gagal!', 'Terjadi kesalahan saat menambahkan jadwal.', 'error');</script>";
    }
}

// Mengupdate jadwal kuliah
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['update'])) {
    $id = $_GET['id'];
    $hari = $_GET['hari'];
    $jam_mulai = $_GET['jam_mulai'];
    $jam_selesai = $_GET['jam_selesai'];
    $matkul = $_GET['matkul'];
    $dosen = $_GET['dosen'];
    $tanggal = $_GET['tanggal'];

    // Query untuk mengupdate jadwal
    $sql = "UPDATE jadwal_kuliah SET hari='$hari', jam_mulai='$jam_mulai', jam_selesai='$jam_selesai', matkul='$matkul', dosen='$dosen', tanggal='$tanggal' WHERE id='$id' AND user_id='$user_id'";

    if ($conn->query($sql) === TRUE) {
        echo "<script>Swal.fire('Berhasil!', 'Jadwal berhasil diperbarui!', 'success'); window.location.href='atur_jadwal.php';</script>";
    } else {
        echo "<script>Swal.fire('Gagal!', 'Terjadi kesalahan saat memperbarui jadwal.', 'error');</script>";
    }
}

// Menghapus jadwal kuliah
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    $sql = "DELETE FROM jadwal_kuliah WHERE id='$id' AND user_id='$user_id'";

    if ($conn->query($sql) === TRUE) {
        echo "<script>Swal.fire('Berhasil!', 'Jadwal berhasil dihapus!', 'success'); window.location.href='atur_jadwal.php';</script>";
    } else {
        echo "<script>Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus jadwal.', 'error');</script>";
    }
}

// Menampilkan jadwal kuliah pengguna
$sql = "SELECT * FROM jadwal_kuliah WHERE user_id = '$user_id' ORDER BY tanggal, jam_mulai";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atur Jadwal Kuliah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script> <!-- Font Awesome Icons -->
    <style>
        /* Custom Styles */
        .background-image {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 50px 0;
        }
        .feature-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 15px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        .feature-card:hover {
            transform: scale(1.05);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }
        .card-body {
            text-align: center;
        }
        .card-icon {
            font-size: 50px;
            margin-bottom: 20px;
        }
        .btn-outline-custom {
            border: 2px solid #ffffff;
            color: #ffffff;
        }
        .btn-outline-custom:hover {
            background-color: #ffffff;
            color: #000000;
        }
        .calendar-icon {
            font-size: 100px;
            color: #ffb703;
        }
        .table {
            background-color: #ffffff;
            border-radius: 8px;
        }
        .container {
            padding-top: 30px;
        }
        .header-title {
            font-size: 2.5rem;
            font-weight: 700;
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
    <h1 class="header-title">Atur Jadwal Kuliah</h1>
    <p>Kelola jadwal kuliah Anda dengan mudah menggunakan aplikasi ini.</p>
    <i class="calendar-icon fas fa-calendar-alt"></i>
</div>

<!-- Form untuk Tambah Jadwal -->
<div class="container">
    <h3>Tambah Jadwal Kuliah</h3>
    <form method="POST" action="atur_jadwal.php">
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="hari" class="form-label">Hari</label>
                <select class="form-control" name="hari" required>
                    <option value="Senin">Senin</option>
                    <option value="Selasa">Selasa</option>
                    <option value="Rabu">Rabu</option>
                    <option value="Kamis">Kamis</option>
                    <option value="Jumat">Jumat</option>
                    <option value="Sabtu">Sabtu</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="jam_mulai" class="form-label">Jam Mulai</label>
                <input type="time" class="form-control" name="jam_mulai" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="jam_selesai" class="form-label">Jam Selesai</label>
                <input type="time" class="form-control" name="jam_selesai" required>
            </div>
            <div class="col-md-6">
                <label for="matkul" class="form-label">Mata Kuliah</label>
                <input type="text" class="form-control" name="matkul" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="dosen" class="form-label">Dosen</label>
                <input type="text" class="form-control" name="dosen">
            </div>
            <div class="col-md-6">
                <label for="tanggal" class="form-label">Tanggal</label>
                <input type="date" class="form-control" name="tanggal">
            </div>
        </div>

        <button type="submit" name="tambah_jadwal" class="btn btn-primary">Tambah Jadwal</button>
    </form>
</div>

<!-- Tabel Jadwal Kuliah -->
<div class="container mt-5">
    <h3>Jadwal Kuliah Anda</h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Hari</th>
                <th>Jam Mulai</th>
                <th>Jam Selesai</th>
                <th>Mata Kuliah</th>
                <th>Dosen</th>
                <th>Tanggal</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['hari']; ?></td>
                    <td><?php echo $row['jam_mulai']; ?></td>
                    <td><?php echo $row['jam_selesai']; ?></td>
                    <td><?php echo $row['matkul']; ?></td>
                    <td><?php echo $row['dosen']; ?></td>
                    <td><?php echo $row['tanggal']; ?></td>
                    <td>
                        <button class="btn btn-warning btn-sm" onclick="editJadwal(<?php echo $row['id']; ?>, '<?php echo $row['hari']; ?>', '<?php echo $row['jam_mulai']; ?>', '<?php echo $row['jam_selesai']; ?>', '<?php echo $row['matkul']; ?>', '<?php echo $row['dosen']; ?>', '<?php echo $row['tanggal']; ?>')">Edit</button>
                        <button class="btn btn-danger btn-sm" onclick="hapusJadwal(<?php echo $row['id']; ?>)">Hapus</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- SweetAlert2 Script -->
<script>
    
    function editJadwal(id, hari, jamMulai, jamSelesai, matkul, dosen, tanggal) {
    Swal.fire({
        title: 'Edit Jadwal',
        html: `
            <input type="text" id="hari" class="swal2-input" value="${hari}" placeholder="Hari">
            <input type="time" id="jam_mulai" class="swal2-input" value="${jamMulai}" placeholder="Jam Mulai">
            <input type="time" id="jam_selesai" class="swal2-input" value="${jamSelesai}" placeholder="Jam Selesai">
            <input type="text" id="matkul" class="swal2-input" value="${matkul}" placeholder="Mata Kuliah">
            <input type="text" id="dosen" class="swal2-input" value="${dosen}" placeholder="Dosen">
            <input type="date" id="tanggal" class="swal2-input" value="${tanggal}" placeholder="Tanggal">
        `,
        showCancelButton: true,
        confirmButtonText: 'Update',
        cancelButtonText: 'Batal',
        preConfirm: () => {
            return {
                hari: document.getElementById('hari').value,
                jam_mulai: document.getElementById('jam_mulai').value,
                jam_selesai: document.getElementById('jam_selesai').value,
                matkul: document.getElementById('matkul').value,
                dosen: document.getElementById('dosen').value,
                tanggal: document.getElementById('tanggal').value
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const data = result.value;
            window.location.href = `atur_jadwal.php?id=${id}&hari=${data.hari}&jam_mulai=${data.jam_mulai}&jam_selesai=${data.jam_selesai}&matkul=${data.matkul}&dosen=${data.dosen}&tanggal=${data.tanggal}&update=true`;
        }
    });
}

    function hapusJadwal(id) {
        Swal.fire({
            title: 'Hapus Jadwal?',
            text: "Anda yakin ingin menghapus jadwal ini?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `atur_jadwal.php?hapus=${id}`;
            }
        });
    }
</script>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

</body>
</html>
