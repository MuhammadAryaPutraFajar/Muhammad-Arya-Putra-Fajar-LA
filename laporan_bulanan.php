<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'supervisor') {
    header("Location: ../../index.php");
    exit();
}

require_once('../../config/db.php');

// Filter bulan dan tahun
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Query laporan bulanan
$laporan_query = "SELECT DATE(pb.created_at) as tanggal, 
                  COUNT(*) as jumlah_pengisian,
                  SUM(pb.jumlah_liter_diisi) as total_liter
                  FROM pengisian_bbm pb 
                  WHERE MONTH(pb.created_at) = '$bulan' AND YEAR(pb.created_at) = '$tahun'
                  GROUP BY DATE(pb.created_at)
                  ORDER BY tanggal ASC";
$laporan_result = mysqli_query($conn, $laporan_query);

// Total bulan ini
$total_bulan_query = "SELECT COUNT(*) as total_pengisian, SUM(jumlah_liter_diisi) as total_liter 
                      FROM pengisian_bbm 
                      WHERE MONTH(created_at) = '$bulan' AND YEAR(created_at) = '$tahun'";
$total_bulan_result = mysqli_query($conn, $total_bulan_query);
$total_bulan = mysqli_fetch_assoc($total_bulan_result);

// Laporan per alat
$alat_query = "SELECT a.nama_alat, COUNT(*) as jumlah_pengisian, SUM(pb.jumlah_liter_diisi) as total_liter
               FROM pengisian_bbm pb 
               JOIN request_pengisian rp ON pb.id_request = rp.id_request 
               JOIN alat a ON rp.id_alat = a.id_alat 
               WHERE MONTH(pb.created_at) = '$bulan' AND YEAR(pb.created_at) = '$tahun'
               GROUP BY a.id_alat, a.nama_alat
               ORDER BY total_liter DESC";
$alat_result = mysqli_query($conn, $alat_query);

// Array nama bulan
$nama_bulan = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Bulanan - Supervisor</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/css/bootstrap.min.css">
    <style>
        :root {
            --ipc-primary: #005baa;
            --ipc-secondary: #0074d9;
            --ipc-accent: #003b6f;
            --ipc-light: #e6f0f9;
            --ipc-text-light: #ffffff;
            --ipc-text-dark: #333333;
            --ipc-gray-light: #f5f5f5;
            --ipc-gray: #e0e0e0;
            --ipc-success: #28a745;
            --ipc-warning: #ffc107;
            --ipc-danger: #dc3545;
            --ipc-info: #17a2b8;
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
            --topbar-height: 70px;
            --transition-speed: 0.3s;
        }
        
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fc;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--topbar-height);
            padding: 25px;
            min-height: calc(100vh - var(--topbar-height));
            transition: all var(--transition-speed) ease;
            background-color: #f8f9fc;
        }
        
        .sidebar.collapsed ~ .main-content {
            margin-left: var(--sidebar-collapsed-width);
        }
        
        .page-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }
        
        .page-header h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--ipc-text-dark);
        }
        
        .page-header .btn-toolbar {
            margin-bottom: 0;
        }
        
        .card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 35px rgba(0,0,0,0.12);
        }
        
        .stat-card {
            background: linear-gradient(135deg, var(--ipc-primary), var(--ipc-secondary));
            color: white;
        }
        
        .stat-card-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }
        
        .card-body {
            padding: 25px;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--ipc-primary), var(--ipc-secondary));
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px 25px;
        }
        
        .card-header h5 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            border-top: none;
            font-weight: 600;
            background-color: #f8f9fc;
            color: var(--ipc-text-dark);
            padding: 15px;
            border-bottom: 2px solid #e3e6f0;
        }
        
        .table td {
            padding: 15px;
            vertical-align: middle;
            border-top: 1px solid #e3e6f0;
        }
        
        .table-bordered {
            border: 1px solid #e3e6f0;
        }
        
        .table-bordered th,
        .table-bordered td {
            border: 1px solid #e3e6f0;
        }
        
        .btn {
            border-radius: 8px;
            font-weight: 600;
            padding: 12px 20px;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--ipc-primary), var(--ipc-secondary));
            border: none;
            color: white;
        }
        
        .btn-primary:hover {
            box-shadow: 0 5px 15px rgba(0, 91, 170, 0.3);
            color: white;
        }
        
        .form-control {
            border-radius: 8px;
            border: 1px solid #e3e6f0;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--ipc-primary);
            box-shadow: 0 0 0 0.2rem rgba(0, 91, 170, 0.25);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 40px;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .empty-state h5 {
            color: var(--ipc-text-dark);
            margin-bottom: 10px;
            font-size: 1.3rem;
        }
        
        .empty-state p {
            color: #666;
            margin-bottom: 20px;
            font-size: 1.1rem;
        }
        
        .report-header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .report-header h2 {
            color: var(--ipc-primary);
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .report-header h3 {
            color: var(--ipc-secondary);
            font-weight: 600;
        }
        
        .report-footer {
            margin-top: 30px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        /* Summary Print Section */
        .print-summary {
            display: none;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            border: 1px solid #e3e6f0;
        }
        
        .print-summary h4 {
            color: var(--ipc-primary);
            font-weight: 600;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .print-summary .summary-item {
            display: inline-block;
            width: 48%;
            padding: 15px;
            margin: 1%;
            background: #f8f9fc;
            border-radius: 8px;
            text-align: center;
        }
        
        .print-summary .summary-item h5 {
            color: var(--ipc-text-dark);
            margin-bottom: 10px;
            font-size: 1rem;
        }
        
        .print-summary .summary-item .summary-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--ipc-primary);
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
            
            .print-summary {
                display: block !important;
            }
            
            .main-content {
                margin-left: 0;
                margin-top: 0;
                padding: 20px;
            }
            
            .card {
                box-shadow: none;
                border: 1px solid #ddd;
                break-inside: avoid;
            }
            
            .page-header,
            .report-header,
            .report-footer {
                box-shadow: none;
                border: 1px solid #ddd;
            }
            
            .stat-card,
            .stat-card-success {
                background: #f8f9fc !important;
                color: #333 !important;
                border: 1px solid #ddd;
            }
            
            .card-header {
                background: #f8f9fc !important;
                color: #333 !important;
                border-bottom: 1px solid #ddd;
            }
            
            .table th {
                background-color: #f8f9fc !important;
                color: #333 !important;
            }
            
            body {
                background: white !important;
            }
        }
        
        @media (max-width: 992px) {
            .main-content {
                margin-left: var(--sidebar-collapsed-width);
                padding: 20px;
            }
            
            .sidebar.expanded ~ .main-content {
                margin-left: var(--sidebar-width);
            }
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                margin-top: var(--topbar-height);
                padding: 15px;
            }
            
            .page-header {
                padding: 15px;
            }
            
            .page-header h1 {
                font-size: 1.5rem;
            }
            
            .card-header,
            .card-body {
                padding: 20px 15px;
            }
            
            .stat-number {
                font-size: 2rem;
            }
            
            .table-responsive {
                font-size: 0.9rem;
            }
            
            .btn {
                padding: 10px 15px;
                font-size: 0.9rem;
            }
            
            .form-inline .form-control {
                margin-bottom: 10px;
                width: 100%;
            }
            
            .form-inline .btn {
                width: 100%;
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .card {
            animation: fadeInUp 0.6s ease-out;
        }
    </style>
</head>
<body>
    <?php 
    include('../../includes/sidebar.php');
    include('../../includes/topbar.php');
    showSidebar('supervisor');
    showTopbar('supervisor');
    ?>
    
    <div class="main-content">
        <div class="page-header no-print">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                <h1><i class="fas fa-calendar-alt mr-2"></i>Laporan Bulanan BBM</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="fas fa-print mr-2"></i>Cetak
                    </button>
                </div>
            </div>
        </div>

        <!-- Filter Bulan/Tahun -->
        <div class="card mb-4 no-print">
            <div class="card-body">
                <form method="GET" class="form-inline">
                    <label for="bulan" class="mr-2 font-weight-600">Bulan:</label>
                    <select class="form-control mr-2" id="bulan" name="bulan">
                        <?php foreach ($nama_bulan as $key => $value): ?>
                            <option value="<?php echo $key; ?>" <?php echo ($bulan == $key) ? 'selected' : ''; ?>>
                                <?php echo $value; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label for="tahun" class="mr-2 ml-3">Tahun:</label>
                    <select class="form-control mr-2" id="tahun" name="tahun">
                        <?php for ($i = date('Y'); $i >= 2020; $i--): ?>
                            <option value="<?php echo $i; ?>" <?php echo ($tahun == $i) ? 'selected' : ''; ?>>
                                <?php echo $i; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search mr-2"></i>Filter
                    </button>
                </form>
            </div>
        </div>

        <!-- Header Laporan -->
        <div class="report-header">
            <h2>LAPORAN BULANAN PENGGUNAAN BBM</h2>
            <h3>Bulan: <?php echo $nama_bulan[$bulan] . ' ' . $tahun; ?></h3>
        </div>

        <!-- Summary untuk Print -->
        <div class="print-summary">
            <h4>RINGKASAN PENGGUNAAN BBM</h4>
            <div class="summary-item">
                <h5>Total Pengisian</h5>
                <div class="summary-number"><?php echo $total_bulan['total_pengisian'] ?? 0; ?></div>
                <small>Kali</small>
            </div>
            <div class="summary-item">
                <h5>Total Liter</h5>
                <div class="summary-number"><?php echo number_format($total_bulan['total_liter'] ?? 0, 2); ?></div>
                <small>Liter</small>
            </div>
        </div>

        <!-- Summary Cards (hanya tampil di layar) -->
        <div class="row mb-4 no-print">
            <div class="col-lg-6 col-md-6 mb-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="card-title">Total Pengisian</div>
                                <div class="stat-number"><?php echo $total_bulan['total_pengisian'] ?? 0; ?></div>
                                <small class="opacity-75">Kali</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-list fa-3x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6 mb-3">
                <div class="card stat-card-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="card-title">Total Liter</div>
                                <div class="stat-number"><?php echo number_format($total_bulan['total_liter'] ?? 0, 2); ?></div>
                                <small class="opacity-75">Liter</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-gas-pump fa-3x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Laporan Harian -->
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-calendar-day mr-2"></i>Laporan Harian</h5>
            </div>
            <div class="card-body">
                <?php if (mysqli_num_rows($laporan_result) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Jumlah Pengisian</th>
                                    <th>Total Liter</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                while ($laporan = mysqli_fetch_assoc($laporan_result)): 
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo date('d F Y', strtotime($laporan['tanggal'])); ?></td>
                                        <td><?php echo $laporan['jumlah_pengisian']; ?> kali</td>
                                        <td><?php echo number_format($laporan['total_liter'], 2); ?> L</td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot>
                                <tr class="font-weight-bold">
                                    <td colspan="2" class="text-right">TOTAL:</td>
                                    <td><?php echo $total_bulan['total_pengisian'] ?? 0; ?> kali</td>
                                    <td><?php echo number_format($total_bulan['total_liter'] ?? 0, 2); ?> L</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h5>Tidak ada data</h5>
                        <p class="text-muted">Tidak ada pengisian BBM pada bulan <?php echo $nama_bulan[$bulan] . ' ' . $tahun; ?>.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Laporan Per Alat -->
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-truck mr-2"></i>Laporan Per Alat</h5>
            </div>
            <div class="card-body">
                <?php if (mysqli_num_rows($alat_result) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Alat</th>
                                    <th>Jumlah Pengisian</th>
                                    <th>Total Liter</th>
                                    <th>Persentase</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                while ($alat = mysqli_fetch_assoc($alat_result)): 
                                    $persentase = ($total_bulan['total_liter'] > 0) ? 
                                                 ($alat['total_liter'] / $total_bulan['total_liter']) * 100 : 0;
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($alat['nama_alat']); ?></td>
                                        <td><?php echo $alat['jumlah_pengisian']; ?> kali</td>
                                        <td><?php echo number_format($alat['total_liter'], 2); ?> L</td>
                                        <td><?php echo number_format($persentase, 1); ?>%</td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h5>Tidak ada data</h5>
                        <p class="text-muted">Tidak ada data pengisian per alat.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Footer Laporan -->
        <div class="report-footer">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Dibuat pada:</strong> <?php echo date('d F Y H:i'); ?></p>
                    <p><strong>Dibuat oleh:</strong> <?php echo htmlspecialchars($_SESSION['name']); ?> (Supervisor)</p>
                </div>
                <div class="col-md-6 text-right">
                    <p><strong>Tanda Tangan</strong></p>
                    <br><br>
                    <p>(_____________________)</p>
                    <p>Supervisor</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
