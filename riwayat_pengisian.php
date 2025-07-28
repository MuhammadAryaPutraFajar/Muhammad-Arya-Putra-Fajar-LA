<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'operator_pengisian') {
    header("Location: ../../index.php");
    exit();
}

require_once('../../config/db.php');

$operator_id = $_SESSION['user_id'];

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

// Query dengan filter
$pengisian_query = "SELECT pb.*, rp.hourmeter, u.name as operator_name, a.nama_alat, ns.nomor_seri, op.name as operator_pengisian_name
                    FROM pengisian_bbm pb 
                    JOIN request_pengisian rp ON pb.id_request = rp.id_request 
                    JOIN users u ON rp.id_user = u.id_user 
                    JOIN alat a ON rp.id_alat = a.id_alat 
                    JOIN nomor_seri ns ON rp.id_nomor = ns.id_nomor 
                    JOIN users op ON pb.id_user = op.id_user
                    $where_clause
                    ORDER BY pb.created_at DESC";
$pengisian_result = mysqli_query($conn, $pengisian_query);

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
    <title>Data Pengisian BBM - Operator Pengisian</title>
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

        .btn-info {
            background: var(--gradient-info);
            color: white;
        }

        .content-header {
            background: var(--gradient-primary);
            color: white;
            padding: 20px 25px;
            border-radius: 20px 20px 0 0;
            margin-bottom: 0;
            box-shadow: var(--shadow-md);
        }

        .content-header h5 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .table-container {
            background: white;
            border-radius: 0 0 20px 20px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            border: 1px solid #e3e6f0;
            border-top: none;
        }

        .table {
            margin-bottom: 0;
            font-size: 0.9rem;
        }

        .table thead th {
            background-color: #f8f9fc;
            color: var(--ipc-text-dark);
            font-weight: 600;
            padding: 15px 12px;
            border: none;
            border-bottom: 2px solid #e3e6f0;
            text-align: center;
            vertical-align: middle;
            font-size: 0.85rem;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 15px 12px;
            vertical-align: middle;
            border-bottom: 1px solid #f0f0f0;
            text-align: center;
        }

        .table tbody tr:hover {
            background-color: rgba(0, 91, 170, 0.05);
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }

        .status-selesai {
            background: var(--gradient-success);
            color: white;
            box-shadow: var(--shadow-sm);
        }

        .empty-state {
            text-align: center;
            padding: 60px 40px;
            background: white;
            border-radius: 0 0 20px 20px;
            color: #718096;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-state h5 {
            color: var(--ipc-text-dark);
            margin-bottom: 10px;
            font-weight: 600;
        }

        .empty-state p {
            margin-bottom: 20px;
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

        .detail-section {
            background-color: #f8f9fc;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            border: 1px solid #e3e6f0;
        }

        .detail-section h6 {
            color: var(--ipc-primary);
            font-weight: 700;
            margin-bottom: 15px;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .table-sm th,
        .table-sm td {
            padding: 10px 12px;
            border: none;
        }

        .table-sm th {
            background: rgba(0, 91, 170, 0.05);
            font-weight: 600;
            color: var(--ipc-primary);
        }

        .image-preview {
            max-width: 150px;
            max-height: 100px;
            cursor: pointer;
            border-radius: 10px;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
            border: 2px solid #e3e6f0;
        }

        .image-preview:hover {
            transform: scale(1.05);
            box-shadow: var(--shadow-lg);
            border-color: var(--ipc-primary);
        }

        .signature-preview {
            max-width: 200px;
            max-height: 80px;
            border: 2px solid #e3e6f0;
            border-radius: 10px;
            padding: 10px;
            background-color: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .signature-preview:hover {
            border-color: var(--ipc-primary);
            box-shadow: var(--shadow-md);
        }

        .badge-info {
            background: var(--gradient-info);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
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

            .content-header {
                padding: 15px 20px;
            }

            .content-header h5 {
                font-size: 1.1rem;
            }

            .table thead th,
            .table tbody td {
                padding: 10px 8px;
                font-size: 0.8rem;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 10px;
            }

            .page-header {
                padding: 15px;
            }

            .filter-section {
                padding: 20px 15px;
            }

            .table thead th,
            .table tbody td {
                padding: 8px 6px;
                font-size: 0.75rem;
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

        .table-container {
            animation: fadeInUp 0.6s ease-out;
        }

        .filter-section {
            animation: fadeInUp 0.4s ease-out;
        }

        .page-header {
            animation: fadeInUp 0.2s ease-out;
        }
    </style>
</head>
<body>
    <?php 
    include('../../includes/sidebar.php');
    include('../../includes/topbar.php');
    showSidebar('operator_pengisian');
    showTopbar('operator_pengisian');
    ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-list mr-3"></i>Data Pengisian BBM</h1>
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
                    <a href="riwayat_pengisian.php" class="btn btn-secondary">
                        <i class="fas fa-undo mr-2"></i>Reset
                    </a>
                </div>
            </form>
            
            <?php if($filter_applied): ?>
            <div class="mt-3">
                <span class="badge-info">
                    <i class="fas fa-filter"></i>
                    Periode: <?php echo !empty($filter_bulan) ? $nama_bulan[$filter_bulan] . ' ' : ''; ?><?php echo $filter_tahun; ?>
                </span>
            </div>
            <?php endif; ?>
        </div>

        <div class="content-header">
            <h5>
                <i class="fas fa-database"></i>
                Semua Data Pengisian BBM
                <?php if($filter_applied): ?>
                    - <?php echo !empty($filter_bulan) ? $nama_bulan[$filter_bulan] . ' ' : ''; ?><?php echo $filter_tahun; ?>
                <?php endif; ?>
            </h5>
        </div>

        <?php if (mysqli_num_rows($pengisian_result) > 0): ?>
            <div class="table-container">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Alat</th>
                                <th>No. Seri</th>
                                <th>Hourmeter</th>
                                <th>Jumlah Liter</th>
                                <th>Status</th>
                                <th>Operator Pengisian</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            mysqli_data_seek($pengisian_result, 0);
                            while ($pengisian = mysqli_fetch_assoc($pengisian_result)): 
                            ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td>
                                        <?php echo date('d/m/Y', strtotime($pengisian['created_at'])); ?><br>
                                        <small class="text-muted"><?php echo date('H:i', strtotime($pengisian['created_at'])); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($pengisian['nama_alat']); ?></td>
                                    <td><?php echo htmlspecialchars($pengisian['nomor_seri']); ?></td>
                                    <td><?php echo number_format($pengisian['hourmeter'], 0, ',', '.'); ?></td>
                                    <td><strong><?php echo number_format($pengisian['jumlah_liter_diisi'], 2); ?> L</strong></td>
                                    <td>
                                        <span class="status-badge status-selesai">Selesai</span>
                                    </td>
                                    <td><?php echo htmlspecialchars($pengisian['operator_pengisian_name']); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#detailModal<?php echo $pengisian['id_pengisian']; ?>">
                                            <i class="fas fa-eye"></i> Detail
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h5>Tidak ada data</h5>
                <p>
                    <?php if($filter_applied): ?>
                        Tidak ada data pengisian BBM untuk periode <?php echo !empty($filter_bulan) ? $nama_bulan[$filter_bulan] . ' ' : ''; ?><?php echo $filter_tahun; ?>.
                    <?php else: ?>
                        Belum ada pengisian BBM yang tercatat.
                    <?php endif; ?>
                </p>
                <a href="request_disetujui.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Lihat Request Disetujui
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal Detail -->
    <?php 
    mysqli_data_seek($pengisian_result, 0);
    while ($pengisian = mysqli_fetch_assoc($pengisian_result)): 
    ?>
    <div class="modal fade" id="detailModal<?php echo $pengisian['id_pengisian']; ?>" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle mr-2"></i>Detail Pengisian #<?php echo $pengisian['id_pengisian']; ?>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-section">
                                <h6><i class="fas fa-info-circle"></i>Informasi Umum</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <th width="40%">ID Pengisian:</th>
                                        <td>#<?php echo $pengisian['id_pengisian']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Tanggal Pengisian:</th>
                                        <td><?php echo date('d/m/Y H:i:s', strtotime($pengisian['created_at'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Operator Request:</th>
                                        <td><?php echo htmlspecialchars($pengisian['operator_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Operator Pengisian:</th>
                                        <td><?php echo htmlspecialchars($pengisian['operator_pengisian_name']); ?></td>
                                    </tr>
                                </table>
                            </div>

                            <div class="detail-section">
                                <h6><i class="fas fa-cogs"></i>Informasi Alat</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <th width="40%">Nama Alat:</th>
                                        <td><?php echo htmlspecialchars($pengisian['nama_alat']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>No. Seri:</th>
                                        <td><?php echo htmlspecialchars($pengisian['nomor_seri']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Hourmeter:</th>
                                        <td><?php echo htmlspecialchars($pengisian['hourmeter']); ?></td>
                                    </tr>
                                </table>
                            </div>

                            <div class="detail-section">
                                <h6><i class="fas fa-gas-pump"></i>Detail Pengisian BBM</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <th width="40%">Liter Sebelum:</th>
                                        <td><?php echo number_format($pengisian['liter_sebelum'], 2); ?> L</td>
                                    </tr>
                                    <tr>
                                        <th>Liter Sesudah:</th>
                                        <td><?php echo number_format($pengisian['liter_sesudah'], 2); ?> L</td>
                                    </tr>
                                    <tr style="background-color: rgba(40, 167, 69, 0.1);">
                                        <th>Jumlah Diisi:</th>
                                        <td><strong><?php echo number_format($pengisian['jumlah_liter_diisi'], 2); ?> L</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="detail-section">
                                <h6><i class="fas fa-camera"></i>Dokumentasi Foto</h6>
                                <div class="row">
                                    <div class="col-6 text-center mb-3">
                                        <p><strong>Foto Sebelum:</strong></p>
                                        <img src="../../uploads/<?php echo htmlspecialchars($pengisian['foto_sebelum']); ?>" 
                                             class="img-fluid image-preview"
                                             onclick="showImageModal('../../uploads/<?php echo htmlspecialchars($pengisian['foto_sebelum']); ?>', 'Foto Sebelum Pengisian')"
                                             alt="Foto Sebelum">
                                    </div>
                                    <div class="col-6 text-center mb-3">
                                        <p><strong>Foto Sesudah:</strong></p>
                                        <img src="../../uploads/<?php echo htmlspecialchars($pengisian['foto_sesudah']); ?>" 
                                             class="img-fluid image-preview"
                                             onclick="showImageModal('../../uploads/<?php echo htmlspecialchars($pengisian['foto_sesudah']); ?>', 'Foto Sesudah Pengisian')"
                                             alt="Foto Sesudah">
                                    </div>
                                </div>
                            </div>

                            <div class="detail-section">
                                <h6><i class="fas fa-signature"></i>Tanda Tangan</h6>
                                <div class="text-center">
                                    <img src="<?php echo htmlspecialchars($pengisian['tanda_tangan']); ?>" 
                                         class="img-fluid signature-preview"
                                         onclick="showImageModal('<?php echo htmlspecialchars($pengisian['tanda_tangan']); ?>', 'Tanda Tangan Operator Pengisian')"
                                         alt="Tanda Tangan">
                                    <p class="mt-2 text-muted">
                                        <small>Tanda tangan: <?php echo htmlspecialchars($pengisian['operator_pengisian_name']); ?></small>
                                    </p>
                                </div>
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
                    <h5 class="modal-title" id="imageModalTitle"><i class="fas fa-image mr-2"></i>Preview Gambar</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" class="img-fluid" style="max-height: 70vh; border-radius: 10px;">
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function showImageModal(src, title) {
            $('#modalImage').attr('src', src);
            $('#imageModalTitle').text(title);
            $('#imageModal').modal('show');
        }

        $(document).ready(function() {
            // Auto hide alerts if any
            $('.alert').delay(5000).fadeOut('slow');
        });
    </script>
</body>
</html>
