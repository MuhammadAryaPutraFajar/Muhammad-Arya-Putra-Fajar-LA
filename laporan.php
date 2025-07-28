<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../index.php");
    exit();
}

require_once('../../config/db.php');

// Ambil statistik dasar
$total_stok_query = "SELECT total_liter FROM total_stok_bbm WHERE id_total = 1";
$total_stok_result = mysqli_query($conn, $total_stok_query);
$total_stok = mysqli_fetch_assoc($total_stok_result)['total_liter'] ?? 0;

$total_pengisian = mysqli_query($conn, "SELECT COUNT(*) as total FROM pengisian_bbm");
$total_pengisian_count = mysqli_fetch_assoc($total_pengisian)['total'] ?? 0;

$total_liter_terpakai = mysqli_query($conn, "SELECT SUM(jumlah_liter_diisi) as total FROM pengisian_bbm");
$total_liter_count = mysqli_fetch_assoc($total_liter_terpakai)['total'] ?? 0;

$total_request = mysqli_query($conn, "SELECT COUNT(*) as total FROM request_pengisian");
$total_request_count = mysqli_fetch_assoc($total_request)['total'] ?? 0;

// Filter logic
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$filter_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$filter_applied = isset($_GET['filter']) && $_GET['filter'] == '1';

// Query untuk laporan berdasarkan filter
$where_clause = "";
if ($filter_applied) {
    $where_clause = "WHERE MONTH(pb.created_at) = '$filter_bulan' AND YEAR(pb.created_at) = '$filter_tahun'";
}

$laporan_query = "SELECT 
    pb.jumlah_liter_diisi,
    pb.created_at,
    rp.hourmeter, 
    u1.name as operator_name, 
    u2.name as operator_pengisian_name, 
    a.nama_alat, 
    ns.nomor_seri,
    DATE(pb.created_at) as tanggal_pengisian
    FROM pengisian_bbm pb 
    JOIN request_pengisian rp ON pb.id_request = rp.id_request 
    JOIN users u1 ON rp.id_user = u1.id_user 
    JOIN users u2 ON pb.id_user = u2.id_user 
    JOIN alat a ON rp.id_alat = a.id_alat 
    JOIN nomor_seri ns ON rp.id_nomor = ns.id_nomor 
    $where_clause
    ORDER BY pb.created_at DESC";

$laporan_result = mysqli_query($conn, $laporan_query);

// Statistik berdasarkan filter
if ($filter_applied) {
    $filtered_pengisian = mysqli_query($conn, "SELECT COUNT(*) as total FROM pengisian_bbm WHERE MONTH(created_at) = '$filter_bulan' AND YEAR(created_at) = '$filter_tahun'");
    $filtered_pengisian_count = mysqli_fetch_assoc($filtered_pengisian)['total'] ?? 0;
    
    $filtered_liter = mysqli_query($conn, "SELECT SUM(jumlah_liter_diisi) as total FROM pengisian_bbm WHERE MONTH(created_at) = '$filter_bulan' AND YEAR(created_at) = '$filter_tahun'");
    $filtered_liter_count = mysqli_fetch_assoc($filtered_liter)['total'] ?? 0;
} else {
    $filtered_pengisian_count = $total_pengisian_count;
    $filtered_liter_count = $total_liter_count;
}

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
    <title>Laporan - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/css/bootstrap.min.css">
    <style>
        :root {
            --primary: #005baa;
            --secondary: #0074d9;
            --success: #28a745;
            --warning: #ffc107;
            --info: #17a2b8;
            --danger: #dc3545;
            --light: #f8f9fc;
            --dark: #333;
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            background: var(--light);
            margin: 0;
            padding: 0;
        }
        
        .main-content {
            margin-left: 250px;
            margin-top: 70px;
            padding: 25px;
            min-height: calc(100vh - 70px);
            background: var(--light);
            transition: all 0.3s ease;
        }
        
        .page-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        
        .page-header h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--dark);
        }
        
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        
        .filter-form {
            display: flex;
            align-items: end;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            min-width: 120px;
        }
        
        .filter-group label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 5px;
            font-size: 0.9rem;
        }
        
        .filter-group select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            min-width: 120px;
            font-size: 0.9rem;
        }
        
        .btn {
            padding: 8px 20px;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            font-size: 0.9rem;
            white-space: nowrap;
        }
        
        .btn-primary { background: var(--primary); color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-success { background: var(--success); color: white; }
        
        .btn:hover {
            transform: translateY(-1px);
            opacity: 0.9;
            color: white;
            text-decoration: none;
        }
        
        .card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            background: white;
        }
        
        .card-stats {
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .card-stats.primary { background: linear-gradient(135deg, var(--primary), var(--secondary)); }
        .card-stats.success { background: linear-gradient(135deg, #1cc88a, #17a673); }
        .card-stats.warning { background: linear-gradient(135deg, #f39c12, #e67e22); }
        .card-stats.info { background: linear-gradient(135deg, #36b9cc, var(--info)); }
        
        .card-body {
            padding: 20px;
            position: relative;
        }
        
        .stats-text {
            font-size: 0.9rem;
            font-weight: 500;
            opacity: 0.9;
            margin-bottom: 8px;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
        }
        
        .stats-icon {
            position: absolute;
            right: 15px;
            top: 15px;
            font-size: 2.5rem;
            opacity: 0.2;
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid #e3e6f0;
            padding: 20px;
            border-radius: 10px 10px 0 0;
        }
        
        .card-header h5 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark);
        }
        
        .table-responsive {
            border-radius: 0 0 10px 10px;
            overflow-x: auto;
        }
        
        .table {
            margin: 0;
            font-size: 0.9rem;
            min-width: 800px;
        }
        
        .table thead th {
            background: #f8f9fc;
            border-bottom: 2px solid #e3e6f0;
            font-weight: 600;
            color: var(--dark);
            padding: 12px;
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .table tbody td {
            padding: 12px;
            vertical-align: middle;
            border-bottom: 1px solid #f0f0f0;
            white-space: nowrap;
        }
        
        .table-hover tbody tr:hover {
            background: #f8f9fc;
        }
        
        .badge-liter {
            background: var(--primary);
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .animate-number {
            transition: all 0.3s ease;
        }
        
        .badge {
            padding: 0.5em 0.75em;
            font-size: 0.85em;
            border-radius: 0.25rem;
        }
        
        .badge-info {
            background-color: var(--info);
            color: white;
        }
        
        /* Mobile Responsive Styles */
        @media (max-width: 1200px) {
            .main-content {
                margin-left: 0;
                margin-top: 60px;
                padding: 20px;
            }
        }
        
        @media (max-width: 992px) {
            .page-header h1 {
                font-size: 1.5rem;
            }
            
            .stats-number {
                font-size: 1.8rem;
            }
            
            .stats-icon {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }
            
            .page-header {
                padding: 15px;
                margin-bottom: 20px;
            }
            
            .page-header h1 {
                font-size: 1.3rem;
                text-align: center;
            }
            
            .filter-section {
                padding: 15px;
            }
            
            .filter-form {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }
            
            .filter-group {
                margin-bottom: 10px;
                min-width: auto;
            }
            
            .filter-group select {
                width: 100%;
                min-width: auto;
            }
            
            .btn {
                width: 100%;
                text-align: center;
                padding: 12px 20px;
            }
            
            .card-body {
                padding: 15px;
            }
            
            .stats-number {
                font-size: 1.5rem;
            }
            
            .stats-text {
                font-size: 0.8rem;
            }
            
            .stats-icon {
                font-size: 1.8rem;
                right: 10px;
                top: 10px;
            }
            
            .card-header {
                padding: 15px;
            }
            
            .card-header h5 {
                font-size: 1rem;
                text-align: center;
            }
            
            .table {
                font-size: 0.8rem;
                min-width: 600px;
            }
            
            .table thead th,
            .table tbody td {
                padding: 8px 6px;
            }
            
            .badge-liter {
                font-size: 0.75rem;
                padding: 4px 8px;
            }
            
            .empty-state {
                padding: 30px 15px;
            }
            
            .empty-state i {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 10px;
            }
            
            .page-header {
                padding: 12px;
                margin-bottom: 15px;
            }
            
            .page-header h1 {
                font-size: 1.1rem;
            }
            
            .filter-section {
                padding: 12px;
            }
            
            .card-body {
                padding: 12px;
            }
            
            .card-header {
                padding: 12px;
            }
            
            .stats-number {
                font-size: 1.3rem;
            }
            
            .stats-text {
                font-size: 0.75rem;
            }
            
            .table {
                font-size: 0.7rem;
                min-width: 500px;
            }
            
            .table thead th,
            .table tbody td {
                padding: 6px 4px;
            }
            
            .badge-liter {
                font-size: 0.7rem;
                padding: 3px 6px;
            }
            
            .btn {
                padding: 10px 15px;
                font-size: 0.85rem;
            }
        }
        
        @media (max-width: 480px) {
            .page-header h1 {
                font-size: 1rem;
            }
            
            .stats-number {
                font-size: 1.2rem;
            }
            
            .table {
                min-width: 450px;
            }
            
            .card-header h5 {
                font-size: 0.9rem;
            }
        }
        
        /* Landscape orientation for mobile */
        @media (max-width: 768px) and (orientation: landscape) {
            .main-content {
                margin-top: 50px;
            }
            
            .page-header {
                padding: 10px;
                margin-bottom: 15px;
            }
            
            .filter-section {
                padding: 10px;
            }
            
            .card-body {
                padding: 10px;
            }
        }
        
        /* Fix for very small screens */
        @media (max-width: 360px) {
            .table {
                min-width: 400px;
            }
            
            .stats-number {
                font-size: 1.1rem;
            }
            
            .page-header h1 {
                font-size: 0.9rem;
            }
        }
        
        /* Ensure buttons stack properly on mobile */
        @media (max-width: 768px) {
            .filter-form .filter-group:last-child {
                margin-top: 10px;
            }
            
            .filter-form .filter-group .btn {
                margin-bottom: 5px;
            }
        }
        
        /* Improve table scrolling on mobile */
        @media (max-width: 768px) {
            .table-responsive {
                border: 1px solid #dee2e6;
                border-radius: 0.375rem;
            }
            
            .table-responsive::-webkit-scrollbar {
                height: 8px;
            }
            
            .table-responsive::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 4px;
            }
            
            .table-responsive::-webkit-scrollbar-thumb {
                background: #c1c1c1;
                border-radius: 4px;
            }
            
            .table-responsive::-webkit-scrollbar-thumb:hover {
                background: #a8a8a8;
            }
        }
    </style>
</head>
<body>
    <?php 
    include('../../includes/sidebar.php');
    include('../../includes/topbar.php');
    showSidebar('admin');
    showTopbar('admin');
    ?>
    
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-chart-bar mr-2"></i>Laporan Penggunaan BBM</h1>
        </div>
        
        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label for="bulan">Bulan:</label>
                    <select name="bulan" id="bulan">
                        <?php foreach($nama_bulan as $key => $value): ?>
                            <option value="<?php echo $key; ?>" <?php echo ($filter_bulan == $key) ? 'selected' : ''; ?>>
                                <?php echo $value; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="tahun">Tahun:</label>
                    <select name="tahun" id="tahun">
                        <?php 
                        $current_year = date('Y');
                        for($year = $current_year; $year >= ($current_year - 5); $year--): ?>
                            <option value="<?php echo $year; ?>" <?php echo ($filter_tahun == $year) ? 'selected' : ''; ?>>
                                <?php echo $year; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <input type="hidden" name="filter" value="1">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search mr-1"></i>Filter
                    </button>
                </div>
                
                <div class="filter-group">
                    <a href="laporan.php" class="btn btn-secondary">
                        <i class="fas fa-undo mr-1"></i>Reset
                    </a>
                </div>
                
                <div class="filter-group">
                    <?php 
                    $export_url = $filter_applied ? 
                        "export_excel.php?bulan=$filter_bulan&tahun=$filter_tahun" : 
                        "export_excel.php";
                    ?>
                    <a href="<?php echo $export_url; ?>" class="btn btn-success">
                        <i class="fas fa-file-excel mr-1"></i>Export Excel
                    </a>
                </div>
            </form>
            
            <?php if($filter_applied): ?>
            <div class="mt-3">
                <span class="badge badge-info px-3 py-2">
                    <i class="fas fa-filter mr-1"></i>
                    Menampilkan data bulan <?php echo $nama_bulan[$filter_bulan]; ?> <?php echo $filter_tahun; ?>
                </span>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12">
                <div class="card card-stats primary">
                    <div class="card-body">
                        <div class="stats-text">Stok BBM Saat Ini</div>
                        <div class="stats-number animate-number"><?php echo number_format($total_stok, 1); ?>L</div>
                        <i class="fas fa-gas-pump stats-icon"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12">
                <div class="card card-stats success">
                    <div class="card-body">
                        <div class="stats-text"><?php echo $filter_applied ? 'Pengisian Periode' : 'Total Pengisian'; ?></div>
                        <div class="stats-number animate-number"><?php echo number_format($filtered_pengisian_count); ?></div>
                        <i class="fas fa-check-circle stats-icon"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12">
                <div class="card card-stats warning">
                    <div class="card-body">
                        <div class="stats-text"><?php echo $filter_applied ? 'Liter Periode' : 'Total Liter Terpakai'; ?></div>
                        <div class="stats-number animate-number"><?php echo number_format($filtered_liter_count, 1); ?>L</div>
                        <i class="fas fa-tint stats-icon"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12">
                <div class="card card-stats info">
                    <div class="card-body">
                        <div class="stats-text">Total Request</div>
                        <div class="stats-number animate-number"><?php echo number_format($total_request_count); ?></div>
                        <i class="fas fa-file-alt stats-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Laporan Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>
                            <i class="fas fa-table mr-2"></i>
                            <?php if($filter_applied): ?>
                                Laporan Pengisian BBM - <?php echo $nama_bulan[$filter_bulan]; ?> <?php echo $filter_tahun; ?>
                            <?php else: ?>
                                Semua Data Pengisian BBM
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($laporan_result && mysqli_num_rows($laporan_result) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Operator Request</th>
                                            <th>Operator Pengisian</th>
                                            <th>Alat</th>
                                            <th>No. Seri</th>
                                            <th>Hourmeter</th>
                                            <th class="text-center">Jumlah Liter</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($laporan = mysqli_fetch_assoc($laporan_result)): ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y', strtotime($laporan['tanggal_pengisian'])); ?></td>
                                                <td><?php echo htmlspecialchars($laporan['operator_name']); ?></td>
                                                <td><?php echo htmlspecialchars($laporan['operator_pengisian_name']); ?></td>
                                                <td><?php echo htmlspecialchars($laporan['nama_alat']); ?></td>
                                                <td><?php echo htmlspecialchars($laporan['nomor_seri']); ?></td>
                                                <td><?php echo htmlspecialchars($laporan['hourmeter']); ?></td>
                                                <td class="text-center">
                                                    <span class="badge-liter">
                                                        <?php 
                                                        $liter = $laporan['jumlah_liter_diisi'] ?? 0;
                                                        echo number_format((float)$liter, 2); 
                                                        ?> L
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <p>
                                    <?php if($filter_applied): ?>
                                        Tidak ada data pengisian BBM untuk bulan <?php echo $nama_bulan[$filter_bulan]; ?> <?php echo $filter_tahun; ?>.
                                    <?php else: ?>
                                        Belum ada data pengisian BBM.
                                    <?php endif; ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Animate numbers on page load
            $('.animate-number').each(function() {
                const $this = $(this);
                const text = $this.text();
                const finalValue = parseFloat(text.replace(/[^0-9.-]+/g, "")) || 0;
                const isDecimal = text.includes('.');
                const suffix = text.replace(/[0-9.-]/g, '');
                
                if (finalValue > 0) {
                    $({ countNum: 0 }).animate({ countNum: finalValue }, {
                        duration: 1000,
                        easing: 'swing',
                        step: function() {
                            if (isDecimal) {
                                $this.text(number_format(this.countNum, 1) + suffix);
                            } else {
                                $this.text(number_format(Math.floor(this.countNum)) + suffix);
                            }
                        },
                        complete: function() {
                            if (isDecimal) {
                                $this.text(number_format(finalValue, 1) + suffix);
                            } else {
                                $this.text(number_format(finalValue) + suffix);
                            }
                        }
                    });
                }
            });
            
            function number_format(number, decimals = 0) {
                return number.toLocaleString('id-ID', {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals
                });
            }
            
            // Handle mobile menu toggle if exists
            $(document).on('click', '.mobile-menu-toggle', function() {
                $('.sidebar').toggleClass('active');
            });
            
            // Close mobile menu when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.sidebar, .mobile-menu-toggle').length) {
                    $('.sidebar').removeClass('active');
                }
            });
        });
    </script>
</body>
</html>
