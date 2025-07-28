<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../index.php");
    exit();
}

require_once('../../config/db.php');

// Set header untuk download Excel
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=laporan_bbm_".date('Y-m-d_H-i-s').".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Filter logic
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$filter_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : '';
$filter_applied = !empty($filter_bulan) && !empty($filter_tahun);

// Query untuk laporan berdasarkan filter
if ($filter_applied) {
    $where_clause = "WHERE MONTH(pb.created_at) = '$filter_bulan' AND YEAR(pb.created_at) = '$filter_tahun'";
    $periode_text = "Bulan " . date('F Y', mktime(0, 0, 0, $filter_bulan, 1, $filter_tahun));
} else {
    $where_clause = "";
    $periode_text = "Semua Periode";
}

$laporan_query = "SELECT pb.*, rp.hourmeter, u1.name as operator_name, u2.name as operator_pengisian_name, 
                  a.nama_alat, ns.nomor_seri, DATE(pb.created_at) as tanggal_pengisian,
                  TIME(pb.created_at) as waktu_pengisian
                  FROM pengisian_bbm pb 
                  JOIN request_pengisian rp ON pb.id_request = rp.id_request 
                  JOIN users u1 ON rp.id_user = u1.id_user
                  JOIN users u2 ON pb.id_user = u2.id_user
                  JOIN alat a ON rp.id_alat = a.id_alat 
                  JOIN nomor_seri ns ON rp.id_nomor = ns.id_nomor 
                  $where_clause
                  ORDER BY pb.created_at DESC";

$laporan_result = mysqli_query($conn, $laporan_query);

// Statistik untuk header
$total_pengisian = mysqli_num_rows($laporan_result);
$total_liter = 0;
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan BBM</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .report-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .period {
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .info-section {
            margin-bottom: 20px;
        }
        
        .info-row {
            margin-bottom: 5px;
        }
        
        .info-label {
            display: inline-block;
            width: 150px;
            font-weight: bold;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .summary {
            margin-top: 20px;
            border-top: 2px solid #000;
            padding-top: 10px;
        }
        
        .summary-row {
            margin-bottom: 5px;
        }
        
        .footer {
            margin-top: 30px;
            text-align: right;
        }
        
        .signature {
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">SISTEM MANAJEMEN BBM</div>
        <div class="report-title">LAPORAN PENGISIAN BAHAN BAKAR MINYAK</div>
        <div class="period"><?php echo $periode_text; ?></div>
    </div>
    
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Tanggal Cetak:</span>
            <?php echo date('d F Y H:i:s'); ?>
        </div>
        <div class="info-row">
            <span class="info-label">Dicetak Oleh:</span>
            <?php echo $_SESSION['name']; ?>
        </div>
        <div class="info-row">
            <span class="info-label">Periode Laporan:</span>
            <?php echo $periode_text; ?>
        </div>
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
                <th width="8%">Jumlah Diisi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if (mysqli_num_rows($laporan_result) > 0):
                while ($laporan = mysqli_fetch_assoc($laporan_result)): 
                    $total_liter += $laporan['jumlah_liter_diisi'];
            ?>
                <tr>
                    <td class="text-center"><?php echo $no++; ?></td>
                    <td class="text-center"><?php echo date('d/m/Y', strtotime($laporan['tanggal_pengisian'])); ?></td>
                    <td class="text-center"><?php echo date('H:i', strtotime($laporan['waktu_pengisian'])); ?></td>
                    <td><?php echo htmlspecialchars($laporan['operator_name']); ?></td>
                    <td><?php echo htmlspecialchars($laporan['operator_pengisian_name']); ?></td>
                    <td><?php echo htmlspecialchars($laporan['nama_alat']); ?></td>
                    <td class="text-center"><?php echo htmlspecialchars($laporan['nomor_seri']); ?></td>
                    <td class="text-center"><?php echo htmlspecialchars($laporan['hourmeter']); ?></td>
                    <td class="text-right"><?php echo number_format($laporan['liter_sebelum'], 2); ?> L</td>
                    <td class="text-right"><?php echo number_format($laporan['liter_sesudah'], 2); ?> L</td>
                    <td class="text-right"><?php echo number_format($laporan['jumlah_liter_diisi'], 2); ?> L</td>
                </tr>
            <?php 
                endwhile;
            else: 
            ?>
                <tr>
                    <td colspan="11" class="text-center">Tidak ada data untuk periode yang dipilih</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <?php if ($total_liter > 0): ?>
        <tfoot>
            <tr style="background-color: #f0f0f0; font-weight: bold;">
                <td colspan="10" class="text-center">TOTAL</td>
                <td class="text-right"><?php echo number_format($total_liter, 2); ?> L</td>
            </tr>
        </tfoot>
        <?php endif; ?>
    </table>
    
    <div class="summary">
        <div class="summary-row">
            <span class="info-label">Total Transaksi:</span>
            <?php echo $total_pengisian; ?> transaksi
        </div>
        <div class="summary-row">
            <span class="info-label">Total BBM Digunakan:</span>
            <?php echo number_format($total_liter, 2); ?> Liter
        </div>
        <div class="summary-row">
            <span class="info-label">Rata-rata per Transaksi:</span>
            <?php echo $total_pengisian > 0 ? number_format($total_liter / $total_pengisian, 2) : '0.00'; ?> Liter
        </div>
    </div>
    
    <div class="footer">
        <div class="signature">
            <p>Mengetahui,</p>
            <br><br><br>
            <p>_________________________</p>
            <p><strong>Administrator</strong></p>
        </div>
    </div>
</body>
</html>
