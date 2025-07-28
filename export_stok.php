<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../index.php");
    exit();
}

require_once('../../config/db.php');

// Filter parameters
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$filter_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Build where clause
$where_clause_stok = "WHERE 1=1";
$where_clause_pengisian = "WHERE 1=1";

if (!empty($filter_bulan)) {
    $where_clause_stok .= " AND MONTH(tanggal) = '$filter_bulan'";
    $where_clause_pengisian .= " AND MONTH(created_at) = '$filter_bulan'";
}
if (!empty($filter_tahun)) {
    $where_clause_stok .= " AND YEAR(tanggal) = '$filter_tahun'";
    $where_clause_pengisian .= " AND YEAR(created_at) = '$filter_tahun'";
}

// Query data stok
$stok_query = "SELECT sb.*, u.name as admin_name,
               DATE(sb.tanggal) as tanggal_stok,
               TIME(sb.created_at) as waktu_input
               FROM stok_bbm sb 
               JOIN users u ON sb.id_user = u.id_user 
               $where_clause_stok
               ORDER BY sb.created_at DESC";
$stok_result = mysqli_query($conn, $stok_query);

// Statistik untuk header
$stok_masuk_periode = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(jumlah_liter), 0) as total FROM stok_bbm $where_clause_stok"))['total'];
$total_terpakai_periode = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(jumlah_liter_diisi), 0) as total FROM pengisian_bbm $where_clause_pengisian"))['total'];
$jumlah_penambahan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM stok_bbm $where_clause_stok"))['total'];
$jumlah_pengisian = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pengisian_bbm $where_clause_pengisian"))['total'];
$sisa_periode = $stok_masuk_periode - $total_terpakai_periode;

// Total stok saat ini
$total_stok_saat_ini = mysqli_fetch_assoc(mysqli_query($conn, "SELECT total_liter FROM total_stok_bbm WHERE id_total = 1"))['total_liter'] ?? 0;

// Array nama bulan
$nama_bulan = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];

// Set header untuk download Excel
$filename = "Kelola_Stok_BBM";
if (!empty($filter_bulan)) {
    $filename .= "_" . $nama_bulan[$filter_bulan];
}
$filename .= "_" . $filter_tahun . "_" . date('dmY_His') . ".xls";

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Kelola Stok BBM</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 18px; margin: 5px 0; color: #005baa; }
        .header h2 { font-size: 14px; margin: 5px 0; color: #666; }
        .stats { margin: 20px 0; }
        .stats table { border-collapse: collapse; width: 100%; }
        .stats td { padding: 8px 12px; border: 1px solid #ddd; }
        .stats .label { background-color: #f5f5f5; font-weight: bold; width: 30%; }
        .stats .value { background-color: #fff; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background-color: #005baa; color: white; font-weight: bold; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .number { text-align: right; }
        .center { text-align: center; }
        .positive { color: #28a745; font-weight: bold; }
        .negative { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN KELOLA STOK BBM</h1>
        <h2>Sistem Manajemen BBM</h2>
        <h2>
            Periode: 
            <?php 
            if (!empty($filter_bulan)) {
                echo $nama_bulan[$filter_bulan] . " ";
            }
            echo $filter_tahun;
            ?>
        </h2>
        <h2>Dicetak pada: <?php echo date('d/m/Y H:i:s'); ?></h2>
    </div>

    <div class="stats">
        <h3>RINGKASAN DATA</h3>
        <table>
            <tr>
                <td class="label">Total Stok Saat Ini:</td>
                <td class="value"><?php echo number_format($total_stok_saat_ini, 2); ?> Liter</td>
                <td class="label">Stok Masuk Periode:</td>
                <td class="value"><?php echo number_format($stok_masuk_periode, 2); ?> Liter</td>
            </tr>
            <tr>
                <td class="label">Total Terpakai Periode:</td>
                <td class="value"><?php echo number_format($total_terpakai_periode, 2); ?> Liter</td>
                <td class="label">Jumlah Pengisian:</td>
                <td class="value"><?php echo number_format($jumlah_pengisian); ?> kali</td>
            </tr>
            <tr>
                <td class="label">Sisa/Selisih Periode:</td>
                <td class="value <?php echo $sisa_periode >= 0 ? 'positive' : 'negative'; ?>">
                    <?php echo number_format($sisa_periode, 2); ?> Liter
                </td>
                <td class="label">Jumlah Penambahan Stok:</td>
                <td class="value"><?php echo number_format($jumlah_penambahan); ?> kali</td>
            </tr>
        </table>
    </div>

    <h3>DETAIL PENAMBAHAN STOK</h3>
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="12%">Tanggal</th>
                <th width="8%">Waktu Input</th>
                <th width="20%">No. Pesanan</th>
                <th width="15%">Jumlah Liter</th>
                <th width="25%">Admin</th>
                <th width="15%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if (mysqli_num_rows($stok_result) > 0) {
                $no = 1;
                $total_periode = 0;
                while ($row = mysqli_fetch_assoc($stok_result)) {
                    $total_periode += $row['jumlah_liter'];
                    echo "<tr>";
                    echo "<td class='center'>" . $no++ . "</td>";
                    echo "<td class='center'>" . date('d/m/Y', strtotime($row['tanggal_stok'])) . "</td>";
                    echo "<td class='center'>" . date('H:i', strtotime($row['waktu_input'])) . "</td>";
                    echo "<td>" . htmlspecialchars($row['no_pesanan']) . "</td>";
                    echo "<td class='number'>" . number_format($row['jumlah_liter'], 2) . "</td>";
                    echo "<td>" . htmlspecialchars($row['admin_name']) . "</td>";
                    echo "<td class='center'>Penambahan Stok</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7' class='center'>Tidak ada data untuk periode yang dipilih</td></tr>";
            }
            ?>
        </tbody>
        <?php if (mysqli_num_rows($stok_result) > 0): ?>
        <tfoot>
            <tr style="background-color: #005baa; color: white; font-weight: bold;">
                <td colspan="4" class="center">TOTAL PENAMBAHAN PERIODE</td>
                <td class="number"><?php echo number_format($stok_masuk_periode, 2); ?> L</td>
                <td colspan="2" class="center">-</td>
            </tr>
        </tfoot>
        <?php endif; ?>
    </table>

    <div style="margin-top: 30px;">
        <h3>CATATAN:</h3>
        <ul style="font-size: 11px; color: #666;">
            <li>Sisa/Selisih = Stok Masuk Periode - Total Terpakai Periode</li>
            <li>Nilai positif menunjukkan stok surplus, nilai negatif menunjukkan kekurangan stok</li>
            <li>Total Stok Saat Ini mencakup seluruh stok dari awal sistem beroperasi</li>
            <li>Data periode hanya menampilkan transaksi pada bulan/tahun yang dipilih</li>
        </ul>
    </div>

    <div style="margin-top: 20px; font-size: 10px; color: #666; border-top: 1px solid #ddd; padding-top: 10px;">
        <p>Laporan ini digenerate secara otomatis oleh Sistem Manajemen BBM</p>
        <p>Dicetak oleh: <?php echo htmlspecialchars($_SESSION['name']); ?> (<?php echo htmlspecialchars($_SESSION['role']); ?>)</p>
        <p>Tanggal Cetak: <?php echo date('d F Y, H:i:s'); ?> WIB</p>
    </div>
</body>
</html>
