<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'supervisor') {
    header("Location: ../../index.php");
    exit();
}

require_once('../../config/db.php');

// Filter parameters
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$filter_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Build where clause
$where_clause = "WHERE 1=1";
if (!empty($filter_bulan)) {
    $where_clause .= " AND MONTH(pb.created_at) = '$filter_bulan'";
}
if (!empty($filter_tahun)) {
    $where_clause .= " AND YEAR(pb.created_at) = '$filter_tahun'";
}

// Query data
$query = "SELECT pb.*, rp.hourmeter, u1.name as operator_name, u2.name as operator_pengisian_name, 
          a.nama_alat, ns.nomor_seri,
          DATE(pb.created_at) as tanggal_pengisian,
          TIME(pb.created_at) as waktu_pengisian
          FROM pengisian_bbm pb 
          JOIN request_pengisian rp ON pb.id_request = rp.id_request 
          JOIN users u1 ON rp.id_user = u1.id_user 
          JOIN users u2 ON pb.id_user = u2.id_user 
          JOIN alat a ON rp.id_alat = a.id_alat 
          JOIN nomor_seri ns ON rp.id_nomor = ns.id_nomor 
          $where_clause
          ORDER BY pb.created_at DESC";

$result = mysqli_query($conn, $query);

// Statistik untuk header
$stats_where = str_replace('pb.', '', $where_clause);

$total_terpakai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(jumlah_liter_diisi), 0) as total FROM pengisian_bbm pb $stats_where"))['total'];
$jumlah_pengisian = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pengisian_bbm pb $stats_where"))['total'];

// Array nama bulan
$nama_bulan = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];

// Set header untuk download Excel
$filename = "Monitoring_BBM";
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
    <title>Laporan Monitoring BBM</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 18px; margin: 5px 0; }
        .header h2 { font-size: 14px; margin: 5px 0; color: #666; }
        .stats { margin: 20px 0; }
        .stats table { border-collapse: collapse; width: 50%; }
        .stats td { padding: 5px 10px; border: 1px solid #ddd; }
        .stats .label { background-color: #f5f5f5; font-weight: bold; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background-color: #4CAF50; color: white; font-weight: bold; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .number { text-align: right; }
        .center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN MONITORING BBM</h1>
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
                <td class="label">Total Pengisian:</td>
                <td><?php echo number_format($jumlah_pengisian); ?> kali</td>
            </tr>
            <tr>
                <td class="label">Total BBM Terpakai:</td>
                <td><?php echo number_format($total_terpakai, 2); ?> Liter</td>
            </tr>
            <tr>
                <td class="label">Rata-rata per Pengisian:</td>
                <td><?php echo $jumlah_pengisian > 0 ? number_format($total_terpakai / $jumlah_pengisian, 2) : '0'; ?> Liter</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="12%">Tanggal</th>
                <th width="8%">Waktu</th>
                <th width="15%">Operator Request</th>
                <th width="15%">Operator Pengisian</th>
                <th width="12%">Alat</th>
                <th width="10%">No. Seri</th>
                <th width="10%">Hourmeter</th>
                <th width="8%">Liter Sebelum</th>
                <th width="8%">Liter Sesudah</th>
                <th width="10%">Jumlah Diisi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if (mysqli_num_rows($result) > 0) {
                $no = 1;
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td class='center'>" . $no++ . "</td>";
                    echo "<td class='center'>" . date('d/m/Y', strtotime($row['tanggal_pengisian'])) . "</td>";
                    echo "<td class='center'>" . date('H:i', strtotime($row['waktu_pengisian'])) . "</td>";
                    echo "<td>" . htmlspecialchars($row['operator_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['operator_pengisian_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['nama_alat']) . "</td>";
                    echo "<td class='center'>" . htmlspecialchars($row['nomor_seri']) . "</td>";
                    echo "<td class='center'>" . htmlspecialchars($row['hourmeter']) . "</td>";
                    echo "<td class='number'>" . number_format($row['liter_sebelum'], 2) . "</td>";
                    echo "<td class='number'>" . number_format($row['liter_sesudah'], 2) . "</td>";
                    echo "<td class='number'>" . number_format($row['jumlah_liter_diisi'], 2) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='11' class='center'>Tidak ada data untuk periode yang dipilih</td></tr>";
            }
            ?>
        </tbody>
        <?php if (mysqli_num_rows($result) > 0): ?>
        <tfoot>
            <tr style="background-color: #4CAF50; color: white; font-weight: bold;">
                <td colspan="10" class="center">TOTAL</td>
                <td class="number"><?php echo number_format($total_terpakai, 2); ?> L</td>
            </tr>
        </tfoot>
        <?php endif; ?>
    </table>

    <div style="margin-top: 30px; font-size: 10px; color: #666;">
        <p>Laporan ini digenerate secara otomatis oleh Sistem Manajemen BBM</p>
        <p>Dicetak oleh: <?php echo htmlspecialchars($_SESSION['name']); ?> (<?php echo htmlspecialchars($_SESSION['role']); ?>)</p>
    </div>
</body>
</html>
