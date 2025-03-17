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

// Menyimpan pengaturan alarm
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['set_alarm'])) {
    $hari = $_POST['hari'];
    $jam_alarm = $_POST['jam_alarm'];
    $ringtone = $_POST['ringtone'];

    // Query untuk menambahkan alarm
    $sql = "INSERT INTO alarm (user_id, hari, jam_alarm, ringtone) 
            VALUES ('$user_id', '$hari', '$jam_alarm', '$ringtone')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>Swal.fire('Berhasil!', 'Alarm berhasil diatur!', 'success'); window.location.href='atur_alarm.php';</script>";
    } else {
        echo "<script>Swal.fire('Gagal!', 'Terjadi kesalahan saat mengatur alarm.', 'error');</script>";
    }
}

// Mengupdate alarm
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_alarm'])) {
    $id = $_POST['id'];
    $jam_alarm = $_POST['jam_alarm'];
    $ringtone = $_POST['ringtone'];

    // Query untuk mengupdate alarm
    $sql = "UPDATE alarm SET jam_alarm='$jam_alarm', ringtone='$ringtone' WHERE id='$id' AND user_id='$user_id'";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
    exit();
}

// Menghapus alarm
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    $sql = "DELETE FROM alarm WHERE id='$id' AND user_id='$user_id'";

    if ($conn->query($sql) === TRUE) {
        echo "<script>Swal.fire('Berhasil!', 'Alarm berhasil dihapus!', 'success'); window.location.href='atur_alarm.php';</script>";
    } else {
        echo "<script>Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus alarm.', 'error');</script>";
    }
}

// Menampilkan alarm yang sudah diset
$sql = "SELECT * FROM alarm WHERE user_id = '$user_id' ORDER BY FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atur Alarm Bangun Tidur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <style>
        .background-image {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 50px 0;
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
    <h1 class="header-title">Atur Alarm Bangun Tidur</h1>
    <p>Atur alarm Anda untuk bangun tepat waktu berdasarkan hari.</p>
    <i class="fas fa-bell" style="font-size: 100px; color: #ffb703;"></i>
</div>

<!-- Menampilkan Jam Realtime, Tanggal, Bulan, dan Tahun -->
<div class="container text-center">
    <h3 id="current-time"></h3>
    <h4 id="current-date"></h4>
</div>

<!-- Form untuk Atur Alarm -->
<div class="container">
    <h3>Setel Alarm Anda</h3>
    <form method="POST" action="atur_alarm.php">
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
                    <option value="Minggu">Minggu</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="jam_alarm" class="form-label">Jam Alarm</label>
                <input type="time" class="form-control" name="jam_alarm" required>
            </div>
        </div>

        <div class="mb-3">
            <label for="ringtone" class="form-label">Pilih Ringtone Alarm</label>
            <select class="form-control" name="ringtone" required>
                <option value="ringtone1.mp3">Ringtone 1</option>
                <option value="ringtone2.mp3">Ringtone 2</option>
                <option value="ringtone3.mp3">Ringtone 3</option>
            </select>
        </div>

        <button type="submit" name="set_alarm" class="btn btn-primary">Setel Alarm</button>
    </form>
</div>

<!-- Menampilkan Alarm yang Sudah Diset -->
<div class="container mt-5">
    <h3>Alarm yang Sudah Diset</h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Hari</th>
                <th>Jam Alarm</th>
                <th>Ringtone</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['hari']; ?></td>
                    <td><?php echo $row['jam_alarm']; ?></td>
                    <td><?php echo $row['ringtone']; ?></td>
                    <td>
                        <button class="btn btn-warning btn-sm" onclick="editAlarm(<?php echo $row['id']; ?>, '<?php echo $row['hari']; ?>', '<?php echo $row['jam_alarm']; ?>', '<?php echo $row['ringtone']; ?>')">Edit</button>
                        <button class="btn btn-danger btn-sm" onclick="hapusAlarm(<?php echo $row['id']; ?>)">Hapus</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Pemutaran Suara Alarm -->
<audio id="alarmAudio" style="display:none;" autoplay>
    <source id="alarmSource" src="" type="audio/mpeg">
</audio>

<!-- SweetAlert2 Script -->
<script>
    // Fungsi untuk memainkan alarm otomatis jika sudah waktunya
    function playAlarm(ringtone) {
        const alarmAudio = document.getElementById("alarmAudio");
        const alarmSource = document.getElementById("alarmSource");

        alarmSource.src = ringtone; // Tentukan ringtone untuk alarm
        alarmAudio.load(); // Muat ulang sumber ringtone
        alarmAudio.play(); // Putar alarm
    }

    // Fungsi untuk memeriksa apakah sudah saatnya memutar alarm
    function checkAlarm() {
        const now = new Date();
        const currentTime = now.toTimeString().slice(0, 5); // Format HH:MM
        const currentDay = now.toLocaleString('id-ID', { weekday: 'long' }); // Hari dalam bahasa Indonesia

        // Ambil jam alarm dan hari dari database atau variabel PHP
        const alarms = <?php echo json_encode($result->fetch_all(MYSQLI_ASSOC)); ?>;

        alarms.forEach(alarm => {
            if (alarm.jam_alarm === currentTime && alarm.hari.toLowerCase() === currentDay.toLowerCase()) {
                playAlarm(alarm.ringtone); // Memutar ringtone
                Swal.fire({
                    title: 'Alarm Berbunyi!',
                    text: 'Pilih untuk Tunda atau Berhenti.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Tunda',
                    cancelButtonText: 'Berhenti',
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Tunda alarm, misalnya 5 menit
                        setTimeout(() => {
                            playAlarm(alarm.ringtone);
                        }, 300000);  // 300000ms = 5 menit
                    } else {
                        const alarmAudio = document.getElementById("alarmAudio");
                        alarmAudio.pause(); // Berhenti jika user memilih Berhenti
                    }
                });
            }
        });
    }

    // Set interval untuk memeriksa setiap detik
    setInterval(checkAlarm, 1000);  // Cek setiap detik

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

    // Update jam dan tanggal setiap detik
    setInterval(updateTime, 1000);

    function editAlarm(id, hari, jamAlarm, ringtone) {
        Swal.fire({
            title: `Edit Alarm`,
            html: `
                <input type="time" id="jam_alarm" class="swal2-input" value="${jamAlarm}" required>
                <select id="ringtone" class="swal2-input" required>
                    <option value="ringtone1.mp3" ${ringtone === 'ringtone1.mp3' ? 'selected' : ''}>Ringtone 1</option>
                    <option value="ringtone2.mp3" ${ringtone === 'ringtone2.mp3' ? 'selected' : ''}>Ringtone 2</option>
                    <option value="ringtone3.mp3" ${ringtone === 'ringtone3.mp3' ? 'selected' : ''}>Ringtone 3</option>
                </select>
            `,
            showCancelButton: true,
            confirmButtonText: 'Update',
            cancelButtonText: 'Batal',
            preConfirm: () => {
                return {
                    id: id,
                    jam_alarm: document.getElementById('jam_alarm').value,
                    ringtone: document.getElementById('ringtone').value
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const data = result.value;
                // Kirim data melalui POST untuk memperbarui alarm
                const formData = new FormData();
                formData.append('update_alarm', true);
                formData.append('id', data.id);
                formData.append('jam_alarm', data.jam_alarm);
                formData.append('ringtone', data.ringtone);

                // Kirim data ke server menggunakan fetch (AJAX)
                fetch('atur_alarm.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire('Berhasil!', 'Alarm berhasil diperbarui!', 'success');
                        window.location.reload(); // Reload halaman untuk memperbarui daftar alarm
                    } else {
                        Swal.fire('Gagal!', 'Terjadi kesalahan saat memperbarui alarm.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Gagal!', 'Terjadi kesalahan saat memperbarui alarm.', 'error');
                });
            }
        });
    }

    function hapusAlarm(id) {
        Swal.fire({
            title: 'Hapus Alarm?',
            text: "Anda yakin ingin menghapus alarm ini?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `atur_alarm.php?hapus=${id}`;
            }
        });
    }
</script>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

</body>
</html>