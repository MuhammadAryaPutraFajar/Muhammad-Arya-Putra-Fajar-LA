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
$filter_applied = !empty($filter_bulan) || $filter_tahun != date('Y');

// Build where clause untuk filter
$where_clause = "WHERE 1=1";
if (!empty($filter_bulan)) {
    $where_clause .= " AND MONTH(pb.created_at) = '$filter_bulan'";
}
if (!empty($filter_tahun)) {
    $where_clause .= " AND YEAR(pb.created_at) = '$filter_tahun'";
}

// Query monitoring dengan filter
$monitoring_query = "SELECT pb.*, rp.hourmeter, u1.name as operator_name, u2.name as operator_pengisian_name, 
                     a.nama_alat, ns.nomor_seri 
                     FROM pengisian_bbm pb 
                     JOIN request_pengisian rp ON pb.id_request = rp.id_request 
                     JOIN users u1 ON rp.id_user = u1.id_user 
                     JOIN users u2 ON pb.id_user = u2.id_user 
                     JOIN alat a ON rp.id_alat = a.id_alat 
                     JOIN nomor_seri ns ON rp.id_nomor = ns.id_nomor 
                     $where_clause
                     ORDER BY pb.created_at DESC";
$monitoring_result = mysqli_query($conn, $monitoring_query);

// Statistik data berdasarkan filter
$stats_where = str_replace('pb.', '', $where_clause);

// Total stok BBM saat ini
$total_stok_query = "SELECT total_liter FROM total_stok_bbm WHERE id_total = 1";
$total_stok_result = mysqli_query($conn, $total_stok_query);
$total_stok = mysqli_fetch_assoc($total_stok_result)['total_liter'] ?? 0;

// Total BBM terpakai periode
$total_terpakai_query = "SELECT COALESCE(SUM(jumlah_liter_diisi), 0) as total_terpakai 
                        FROM pengisian_bbm pb 
                        $stats_where";
$total_terpakai = mysqli_fetch_assoc(mysqli_query($conn, $total_terpakai_query))['total_terpakai'];

// Jumlah pengisian periode
$jumlah_pengisian_query = "SELECT COUNT(*) as jumlah_pengisian 
                          FROM pengisian_bbm pb 
                          $stats_where";
$jumlah_pengisian = mysqli_fetch_assoc(mysqli_query($conn, $jumlah_pengisian_query))['jumlah_pengisian'];

// Total stok BBM masuk periode (dari tabel stok_bbm)
$stok_masuk_where = str_replace(['pb.', 'WHERE 1=1'], ['', 'WHERE 1=1'], $where_clause);
if (!empty($filter_bulan)) {
    $stok_masuk_where = str_replace("MONTH(created_at)", "MONTH(tanggal)", $stok_masuk_where);
}
if (!empty($filter_tahun)) {
    $stok_masuk_where = str_replace("YEAR(created_at)", "YEAR(tanggal)", $stok_masuk_where);
}

$stok_masuk_query = "SELECT COALESCE(SUM(jumlah_liter), 0) as stok_masuk 
                    FROM stok_bbm 
                    $stok_masuk_where";
$stok_masuk = mysqli_fetch_assoc(mysqli_query($conn, $stok_masuk_query))['stok_masuk'];

// Sisa/selisih = stok masuk - terpakai
$sisa_stok = $stok_masuk - $total_terpakai;

// Array nama bulan
$nama_bulan = [
    '' => 'Semua Bulan',
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
    <title>Monitoring BBM - Supervisor</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-success: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --gradient-warning: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            --gradient-danger: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --gradient-info: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
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
            padding: 30px;
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
            border: 1px solid #e3e6f0;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 200px;
            height: 200px;
            background: var(--gradient-primary);
            border-radius: 50%;
            opacity: 0.1;
            transform: translate(50%, -50%);
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 2;
        }

        .filter-section {
            background: white;
            padding: 25px;
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
            margin-bottom: 30px;
            border: 1px solid #e3e6f0;
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
            min-width: 150px;
        }

        .filter-group label {
            font-weight: 600;
            color: var(--ipc-text-dark);
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .filter-group select {
            padding: 12px 15px;
            border: 2px solid #e3e6f0;
            border-radius: 10px;
            background: white;
            color: var(--ipc-text-dark);
            font-weight: 500;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .filter-group select:focus {
            border-color: var(--ipc-primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 91, 170, 0.1);
        }

        .btn {
            border-radius: 10px;
            font-weight: 600;
            padding: 12px 20px;
            transition: all 0.3s ease;
            border: none;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            box-shadow: var(--shadow-md);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-success {
            background: var(--gradient-success);
            color: white;
        }

        .btn-info {
            background: var(--gradient-info);
            color: white;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: var(--shadow-lg);
            border: 1px solid #e3e6f0;
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-primary);
        }

        .stat-card.success::before { background: var(--gradient-success); }
        .stat-card.warning::before { background: var(--gradient-warning); }
        .stat-card.danger::before { background: var(--gradient-danger); }
        .stat-card.info::before { background: var(--gradient-info); }

        .stat-card-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stat-card-info h3 {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--ipc-text-dark);
            margin-bottom: 0.5rem;
            line-height: 1;
        }

        .stat-card-info p {
            color: #718096;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.8rem;
            margin: 0;
        }

        .stat-card-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            background: var(--gradient-primary);
            box-shadow: var(--shadow-md);
        }

        .stat-card.success .stat-card-icon { background: var(--gradient-success); }
        .stat-card.warning .stat-card-icon { background: var(--gradient-warning); }
        .stat-card.danger .stat-card-icon { background: var(--gradient-danger); }
        .stat-card.info .stat-card-icon { background: var(--gradient-info); }

        .card {
            border-radius: 20px;
            border: none;
            box-shadow: var(--shadow-lg);
            border: 1px solid #e3e6f0;
            overflow: hidden;
            margin-bottom: 30px;
        }

        .card-header {
            background: linear-gradient(135deg, #f8f9fc 0%, #e6f0f9 100%);
            padding: 20px 25px;
            border-bottom: 1px solid #e3e6f0;
        }

        .card-header h5 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--ipc-text-dark);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-body {
            padding: 25px;
        }

        .table {
            margin-bottom: 0;
            font-size: 0.9rem;
        }

        .table thead th {
            background-color: #f8f9fc;
            border-bottom: 2px solid #e3e6f0;
            font-weight: 600;
            color: var(--ipc-text-dark);
            padding: 15px 12px;
            font-size: 0.85rem;
            vertical-align: middle;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 15px 12px;
            vertical-align: middle;
            border-bottom: 1px solid #f0f0f0;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 91, 170, 0.05);
        }

        .image-preview {
            max-width: 80px;
            max-height: 60px;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s ease;
            border: 2px solid #e3e6f0;
        }

        .image-preview:hover {
            transform: scale(1.05);
            border-color: var(--ipc-primary);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #718096;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: var(--shadow-xl);
        }

        .modal-header {
            background: var(--gradient-primary);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 20px 25px;
        }

        .modal-body {
            padding: 25px;
        }

        .badge-info {
            background: var(--gradient-info);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
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
                padding: 15px;
            }

            .page-header {
                padding: 20px;
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-group {
                margin-bottom: 15px;
                min-width: auto;
            }

            .btn {
                width: 100%;
                margin-bottom: 10px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .stat-card {
                padding: 20px;
            }

            .stat-card-info h3 {
                font-size: 1.8rem;
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
        <div class="page-header">
            <h1><i class="fas fa-chart-line mr-3"></i>Monitoring BBM</h1>
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
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search mr-2"></i>Filter Data
                    </button>
                </div>
                
                <div class="filter-group">
                    <a href="monitoring_bbm.php" class="btn btn-secondary">
                        <i class="fas fa-undo mr-2"></i>Reset
                    </a>
                </div>
                
                <div class="filter-group">
                    <?php 
                    $export_params = [];
                    if (!empty($filter_bulan)) $export_params[] = "bulan=$filter_bulan";
                    if (!empty($filter_tahun)) $export_params[] = "tahun=$filter_tahun";
                    $export_url = "export_monitoring.php" . (!empty($export_params) ? '?' . implode('&', $export_params) : '');
                    ?>
                    <a href="<?php echo $export_url; ?>" class="btn btn-success">
                        <i class="fas fa-file-excel mr-2"></i>Export Excel
                    </a>
                </div>
            </form>
            
            <?php if($filter_applied): ?>
            <div class="mt-3">
                <span class="badge-info">
                    <i class="fas fa-filter mr-2"></i>
                    Periode: <?php echo !empty($filter_bulan) ? $nama_bulan[$filter_bulan] . ' ' : ''; ?><?php echo $filter_tahun; ?>
                </span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-content">
                    <div class="stat-card-info">
                        <h3><?php echo number_format($total_stok, 1); ?></h3>
                        <p>Total Stok Saat Ini (L)</p>
                    </div>
                    <div class="stat-card-icon">
                        <i class="fas fa-gas-pump"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card success">
                <div class="stat-card-content">
                    <div class="stat-card-info">
                        <h3><?php echo number_format($stok_masuk, 1); ?></h3>
                        <p>Stok Masuk Periode (L)</p>
                    </div>
                    <div class="stat-card-icon">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card warning">
                <div class="stat-card-content">
                    <div class="stat-card-info">
                        <h3><?php echo number_format($total_terpakai, 1); ?></h3>
                        <p>Total Terpakai (L)</p>
                    </div>
                    <div class="stat-card-icon">
                        <i class="fas fa-tint"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card info">
                <div class="stat-card-content">
                    <div class="stat-card-info">
                        <h3><?php echo $jumlah_pengisian; ?></h3>
                        <p>Jumlah Pengisian</p>
                    </div>
                    <div class="stat-card-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card <?php echo $sisa_stok >= 0 ? 'success' : 'danger'; ?>">
                <div class="stat-card-content">
                    <div class="stat-card-info">
                        <h3><?php echo number_format($sisa_stok, 1); ?></h3>
                        <p>Sisa/Selisih (L)</p>
                    </div>
                    <div class="stat-card-icon">
                        <i class="fas fa-<?php echo $sisa_stok >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monitoring Table -->
        <div class="card">
            <div class="card-header">
                <h5>
                    <i class="fas fa-list mr-2"></i>
                    Data Pengisian BBM
                    <?php if($filter_applied): ?>
                        - <?php echo !empty($filter_bulan) ? $nama_bulan[$filter_bulan] . ' ' : ''; ?><?php echo $filter_tahun; ?>
                    <?php endif; ?>
                </h5>
            </div>
            <div class="card-body">
                <?php if (mysqli_num_rows($monitoring_result) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Operator Request</th>
                                    <th>Operator Pengisian</th>
                                    <th>Alat</th>
                                    <th>No. Seri</th>
                                    <th>Hourmeter</th>
                                    <th>Jumlah Liter</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                mysqli_data_seek($monitoring_result, 0);
                                while ($monitoring = mysqli_fetch_assoc($monitoring_result)): 
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($monitoring['created_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($monitoring['operator_name']); ?></td>
                                        <td><?php echo htmlspecialchars($monitoring['operator_pengisian_name']); ?></td>
                                        <td><?php echo htmlspecialchars($monitoring['nama_alat']); ?></td>
                                        <td><?php echo htmlspecialchars($monitoring['nomor_seri']); ?></td>
                                        <td><?php echo htmlspecialchars($monitoring['hourmeter']); ?></td>
                                        <td><strong><?php echo number_format($monitoring['jumlah_liter_diisi'], 2); ?> L</strong></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#detailModal<?php echo $monitoring['id_pengisian']; ?>">
                                                <i class="fas fa-eye"></i> Detail
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h5>Tidak ada data</h5>
                        <p>
                            <?php if($filter_applied): ?>
                                Tidak ada data pengisian BBM untuk periode <?php echo !empty($filter_bulan) ? $nama_bulan[$filter_bulan] . ' ' : ''; ?><?php echo $filter_tahun; ?>.
                            <?php else: ?>
                                Belum ada data pengisian BBM yang tercatat.
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Detail -->
    <?php 
    mysqli_data_seek($monitoring_result, 0);
    while ($monitoring = mysqli_fetch_assoc($monitoring_result)): 
    ?>
    <div class="modal fade" id="detailModal<?php echo $monitoring['id_pengisian']; ?>" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-info-circle mr-2"></i>Detail Pengisian BBM #<?php echo $monitoring['id_pengisian']; ?></h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3"><i class="fas fa-info-circle"></i> Informasi Pengisian</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Tanggal:</strong></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($monitoring['created_at'])); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Operator Request:</strong></td>
                                    <td><?php echo htmlspecialchars($monitoring['operator_name']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Operator Pengisian:</strong></td>
                                    <td><?php echo htmlspecialchars($monitoring['operator_pengisian_name']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Alat:</strong></td>
                                    <td><?php echo htmlspecialchars($monitoring['nama_alat']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>No. Seri:</strong></td>
                                    <td><?php echo htmlspecialchars($monitoring['nomor_seri']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Hourmeter:</strong></td>
                                    <td><?php echo htmlspecialchars($monitoring['hourmeter']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Liter Sebelum:</strong></td>
                                    <td><?php echo number_format($monitoring['liter_sebelum'], 2); ?> L</td>
                                </tr>
                                <tr>
                                    <td><strong>Liter Sesudah:</strong></td>
                                    <td><?php echo number_format($monitoring['liter_sesudah'], 2); ?> L</td>
                                </tr>
                                <tr>
                                    <td><strong>Jumlah Diisi:</strong></td>
                                    <td><strong><?php echo number_format($monitoring['jumlah_liter_diisi'], 2); ?> L</strong></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3"><i class="fas fa-images"></i> Dokumentasi</h6>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <p><strong>Foto Sebelum:</strong></p>
                                    <img src="../../uploads/<?php echo htmlspecialchars($monitoring['foto_sebelum']); ?>" 
                                         class="img-fluid image-preview"
                                         onclick="showImageModal('../../uploads/<?php echo htmlspecialchars($monitoring['foto_sebelum']); ?>')">
                                </div>
                                <div class="col-6 mb-3">
                                    <p><strong>Foto Sesudah:</strong></p>
                                    <img src="../../uploads/<?php echo htmlspecialchars($monitoring['foto_sesudah']); ?>" 
                                         class="img-fluid image-preview"
                                         onclick="showImageModal('../../uploads/<?php echo htmlspecialchars($monitoring['foto_sesudah']); ?>')">
                                </div>
                            </div>
                            <div class="mt-3">
                                <p><strong>Tanda Tangan:</strong></p>
                                <img src="<?php echo htmlspecialchars($monitoring['tanda_tangan']); ?>" 
                                     class="img-fluid"
                                     style="max-height: 100px; border: 2px solid #e3e6f0; border-radius: 10px; padding: 10px;"
                                     onclick="showImageModal('<?php echo htmlspecialchars($monitoring['tanda_tangan']); ?>')">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endwhile; ?>

    <!-- Modal untuk preview gambar -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-image mr-2"></i>Preview Gambar</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" class="img-fluid" style="border-radius: 10px;">
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function showImageModal(src) {
            $('#modalImage').attr('src', src);
            $('#imageModal').modal('show');
        }

        $(document).ready(function() {
            // Auto hide alerts if any
            $('.alert').delay(5000).fadeOut('slow');
        });
    </script>
</body>
</html>
