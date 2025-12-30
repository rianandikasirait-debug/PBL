<?php
/**
 * Export Data Peserta ke Excel (Format XLS)
 * File ini mengekspor seluruh data akun peserta ke format Excel
 * menggunakan tabel HTML yang dapat dibaca oleh Microsoft Excel
 */

// Mulai session dan cek login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Ambil data role user
$userId = (int) $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
$stmt->close();

// Hanya admin dan notulis yang dapat mengekspor data
if (!$userData || !in_array(strtolower($userData['role']), ['admin', 'notulis'])) {
    die("Akses ditolak. Anda tidak memiliki izin untuk mengekspor data.");
}

// Query untuk mengambil semua data peserta
$sql = "SELECT nama, nik, email, nomor_whatsapp, created_at 
        FROM users 
        WHERE LOWER(role) = 'peserta' 
        ORDER BY nama ASC";
$result = $conn->query($sql);

if (!$result) {
    die("Error mengambil data: " . $conn->error);
}

// Nama file dengan tanggal
$filename = "Data_Peserta_" . date('Y-m-d_H-i-s') . ".xls";

// Set header untuk download file Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Mulai output HTML/Excel
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <!--[if gte mso 9]>
    <xml>
        <x:ExcelWorkbook>
            <x:ExcelWorksheets>
                <x:ExcelWorksheet>
                    <x:Name>Data Peserta</x:Name>
                    <x:WorksheetOptions>
                        <x:DisplayGridlines/>
                    </x:WorksheetOptions>
                </x:ExcelWorksheet>
            </x:ExcelWorksheets>
        </x:ExcelWorkbook>
    </xml>
    <![endif]-->
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th {
            background-color: #00C853;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            padding: 12px 8px;
            border: 1px solid #000000;
            font-size: 12pt;
        }
        td {
            border: 1px solid #cccccc;
            padding: 8px;
            text-align: left;
            vertical-align: middle;
            font-size: 11pt;
        }
        td.center {
            text-align: center;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .title {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .info {
            font-size: 10pt;
            color: #666666;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="title">Data Peserta SmartNote</div>
    <div class="info">Diekspor pada: <?= date('d F Y H:i:s') ?></div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th style="width: 200px;">Nama Lengkap</th>
                <th style="width: 120px;">NIK</th>
                <th style="width: 280px;">Email</th>
                <th style="width: 150px;">Nomor WhatsApp</th>
                <th style="width: 150px;">Tanggal Terdaftar</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            while ($row = $result->fetch_assoc()):
                // Format nomor WhatsApp
                $whatsapp = !empty($row['nomor_whatsapp']) ? $row['nomor_whatsapp'] : '-';
                
                // Format tanggal dalam Bahasa Indonesia
                $tanggal = '-';
                if (!empty($row['created_at'])) {
                    $date = new DateTime($row['created_at']);
                    $tanggal = $date->format('d/m/Y');
                }
            ?>
            <tr>
                <td class="center"><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['nama']) ?></td>
                <td><?= htmlspecialchars($row['nik']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td class="center"><?= htmlspecialchars($whatsapp) ?></td>
                <td class="center"><?= $tanggal ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    
    <br>
    <div class="info">Total Peserta: <?= $result->num_rows ?> orang</div>
</body>
</html>
<?php
// Tutup koneksi database
$conn->close();
exit;
?>
