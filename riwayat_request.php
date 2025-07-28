<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'operator') {
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
$where_clause = "WHERE rp.id_user = '$operator_id'";
if (!empty($filter_bulan)) {
    $where_clause .= " AND MONTH(rp.created_at) = '$filter_bulan'";
}
if (!empty($filter_tahun)) {
    $where_clause .= " AND YEAR(rp.created_at) = '$filter_tahun'";
}

// Ambil riwayat request dengan filter
$riwayat_query = "SELECT rp.*, a.nama_alat, ns.nomor_seri, u.name as admin_name 
                  FROM request_pengisian rp 
                  JOIN alat a ON rp.id_alat = a.id_alat 
                  JOIN nomor_seri ns ON rp.id_nomor = ns.id_nomor 
                  LEFT JOIN users u ON rp.id_user_admin = u.id_user 
                  $where_clause
                  ORDER BY rp.created_at DESC";
$riwayat_result = mysqli_query($conn, $riwayat_query);

// Array nama bulan
$nama_bulan = [
    '' => 'Semua Bulan',
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];

// Function untuk mendapatkan status text tanpa badge
function getStatusText($status) {
    // Normalisasi status menjadi lowercase dan trim whitespace
    $normalizedStatus = strtolower(trim($status));
    
    switch($normalizedStatus) {
        case 'pending':
        case '0':
            return 'Menunggu Verifikasi';
        case 'disetujui':
        case 'approved':
        case '1':
            return 'Disetujui';
        case 'ditolak':
        case 'rejected':
        case '2':
            return 'Ditolak';
        default:
            // Jika status tidak dikenali, tampilkan status asli dengan kapitalisasi
            return ucfirst($status);
    }
}

// Function untuk mengecek status untuk kondisi lain
function checkStatus($status) {
    $normalizedStatus = strtolower(trim($status));
    
    switch($normalizedStatus) {
        case 'pending':
        case '0':
            return 'pending';
        case 'disetujui':
        case 'approved':
        case '1':
            return 'disetujui';
        case 'ditolak':
        case 'rejected':
        case '2':
            return 'ditolak';
        default:
            return $normalizedStatus;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Request - Operator</title>
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

        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
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
            min-width: 150px;
        }

        .filter-group label {
            font-weight: 600;
            color: var(--ipc-text-dark);
            margin-bottom: 5px;
            font-size: 0.9rem;
        }

        .filter-group select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            min-width: 150px;
            font-size: 0.9rem;
        }

        .badge-info {
            background-color: var(--ipc-info);
            color: white;
            padding: 0.5em 0.75em;
            font-size: 0.85em;
            border-radius: 0.25rem;
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
        
        .card-body {
            padding: 25px;
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
        }
        
        .table td {
            padding: 15px;
            vertical-align: middle;
        }
        
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 91, 170, 0.02);
        }
        
        .btn {
            border-radius: 8px;
            font-weight: 600;
            padding: 8px 15px;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--ipc-primary), var(--ipc-secondary));
            border: none;
        }

        .btn-primary:hover {
            box-shadow: 0 5px 15px rgba(0, 91, 170, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            border: none;
        }

        .btn-secondary:hover {
            box-shadow: 0 5px 15px rgba(108, 117, 125, 0.3);
        }
        
        .btn-info {
            background: linear-gradient(135deg, var(--ipc-info), #138496);
            border: none;
        }
        
        .btn-info:hover {
            box-shadow: 0 5px 15px rgba(23, 162, 184, 0.3);
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--ipc-success), #1cc88a);
            border: none;
        }
        
        .btn-success:hover {
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        
        /* Status styling dengan warna text saja */
        .status-pending {
            color: #856404;
            font-weight: 600;
        }
        
        .status-approved {
            color: #155724;
            font-weight: 600;
        }
        
        .status-rejected {
            color: #721c24;
            font-weight: 600;
        }
        
        .image-preview {
            max-width: 100px;
            max-height: 75px;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .image-preview:hover {
            transform: scale(1.05);
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .empty-state h5 {
            color: var(--ipc-text-dark);
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #666;
            margin-bottom: 20px;
        }
        
        .modal-header {
            background: linear-gradient(135deg, var(--ipc-primary), var(--ipc-secondary));
            color: white;
            border-radius: 15px 15px 0 0;
        }
        
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        }
        
        .modal-body {
            padding: 25px;
        }
        
        .table-sm th,
        .table-sm td {
            padding: 8px 12px;
        }
        
        /* Status debug info */
        .debug-info {
            font-size: 0.8rem;
            color: #666;
            font-style: italic;
            margin-top: 5px;
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
                padding: 15px;
            }
            
            .page-header h1 {
                font-size: 1.5rem;
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
            
            .card-header,
            .card-body {
                padding: 20px 15px;
            }
            
            .table-responsive {
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 10px;
            }

            .filter-section {
                padding: 12px;
            }
            
            .card-header,
            .card-body {
                padding: 15px;
            }
            
            .table th,
            .table td {
                padding: 10px 8px;
                font-size: 0.8rem;
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
    showSidebar('operator');
    showTopbar('operator');
    ?>
    
    <div class="main-content">
        <div class="page-header mb-4">
            <h1><i class="fas fa-history mr-2"></i>Riwayat Request Pengisian BBM</h1>
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
                        <i class="fas fa-search mr-1"></i>Filter
                    </button>
                </div>
                
                <div class="filter-group">
                    <a href="riwayat_request.php" class="btn btn-secondary">
                        <i class="fas fa-undo mr-1"></i>Reset
                    </a>
                </div>
            </form>
            
            <?php if($filter_applied): ?>
            <div class="mt-3">
                <span class="badge badge-info px-3 py-2">
                    <i class="fas fa-filter mr-1"></i>
                    Menampilkan data <?php echo !empty($filter_bulan) ? $nama_bulan[$filter_bulan] . ' ' : ''; ?><?php echo $filter_tahun; ?>
                </span>
            </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-list mr-2"></i>Semua Request Anda</h5>
            </div>
            <div class="card-body">
                <?php if (mysqli_num_rows($riwayat_result) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Alat</th>
                                    <th>No. Seri</th>
                                    <th>Hourmeter</th>
                                    <th>Status</th>
                                    <th>Verifikator</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                mysqli_data_seek($riwayat_result, 0);
                                while ($riwayat = mysqli_fetch_assoc($riwayat_result)): 
                                    $statusClass = '';
                                    $statusCheck = checkStatus($riwayat['status']);
                                    if ($statusCheck == 'pending') {
                                        $statusClass = 'status-pending';
                                    } elseif ($statusCheck == 'disetujui') {
                                        $statusClass = 'status-approved';
                                    } elseif ($statusCheck == 'ditolak') {
                                        $statusClass = 'status-rejected';
                                    }
                                ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($riwayat['created_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($riwayat['nama_alat']); ?></td>
                                        <td><?php echo htmlspecialchars($riwayat['nomor_seri']); ?></td>
                                        <td><?php echo number_format($riwayat['hourmeter'], 0, ',', '.'); ?></td>
                                        <td class="<?php echo $statusClass; ?>">
                                            <?php echo getStatusText($riwayat['status']); ?>
                                        </td>
                                        <td><?php echo $riwayat['admin_name'] ? htmlspecialchars($riwayat['admin_name']) : '-'; ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#detailModal<?php echo $riwayat['id_request']; ?>">
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
                                Tidak ada request untuk periode <?php echo !empty($filter_bulan) ? $nama_bulan[$filter_bulan] . ' ' : ''; ?><?php echo $filter_tahun; ?>.
                            <?php else: ?>
                                Anda belum membuat request pengisian BBM.
                            <?php endif; ?>
                        </p>
                        <a href="request_pengisian.php" class="btn btn-success">
                            <i class="fas fa-plus"></i> Buat Request
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Detail -->
    <?php 
    mysqli_data_seek($riwayat_result, 0); // Reset pointer
    while ($riwayat = mysqli_fetch_assoc($riwayat_result)): 
    ?>
    <div class="modal fade" id="detailModal<?php echo $riwayat['id_request']; ?>" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Request #<?php echo $riwayat['id_request']; ?></h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3"><i class="fas fa-info-circle"></i> Informasi Request</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Tanggal:</strong></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($riwayat['created_at'])); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Alat:</strong></td>
                                    <td><?php echo htmlspecialchars($riwayat['nama_alat']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>No. Seri:</strong></td>
                                    <td><?php echo htmlspecialchars($riwayat['nomor_seri']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Hourmeter:</strong></td>
                                    <td><?php echo number_format($riwayat['hourmeter'], 0, ',', '.'); ?> Hours</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td><?php echo getStatusText($riwayat['status']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Verifikator:</strong></td>
                                    <td><?php echo $riwayat['admin_name'] ? htmlspecialchars($riwayat['admin_name']) : '-'; ?></td>
                                </tr>
                                <?php if (checkStatus($riwayat['status']) == 'ditolak' && $riwayat['alasan_tolak']): ?>
                                <tr>
                                    <td><strong>Alasan Tolak:</strong></td>
                                    <td class="text-danger"><?php echo htmlspecialchars($riwayat['alasan_tolak']); ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3"><i class="fas fa-images"></i> Dokumentasi</h6>
                            <div class="mb-3">
                                <p><strong>Foto Hourmeter:</strong></p>
                                <img src="../../uploads/<?php echo htmlspecialchars($riwayat['foto_hourmeter']); ?>" 
                                     class="img-fluid image-preview"
                                     onclick="showImageModal('../../uploads/<?php echo htmlspecialchars($riwayat['foto_hourmeter']); ?>')">
                            </div>
                            <div class="mb-3">
                                <p><strong>Foto Indikator BBM:</strong></p>
                                <img src="../../uploads/<?php echo htmlspecialchars($riwayat['foto_indikator_bbm']); ?>" 
                                     class="img-fluid image-preview"
                                     onclick="showImageModal('../../uploads/<?php echo htmlspecialchars($riwayat['foto_indikator_bbm']); ?>')">
                            </div>
                            <div>
                                <p><strong>Tanda Tangan:</strong></p>
                                <img src="<?php echo htmlspecialchars($riwayat['tanda_tangan']); ?>" 
                                     class="img-fluid"
                                     style="max-height: 100px; border: 1px solid #ddd; border-radius: 5px;"
                                     onclick="showImageModal('<?php echo htmlspecialchars($riwayat['tanda_tangan']); ?>')">
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
                    <h5 class="modal-title">Preview Gambar</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" class="img-fluid" style="max-height: 70vh;">
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
    </script>
</body>
</html>
