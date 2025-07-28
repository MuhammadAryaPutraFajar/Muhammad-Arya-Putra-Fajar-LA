<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'operator_pengisian') {
    header("Location: ../../index.php");
    exit();
}

require_once('../../config/db.php');

// Ambil request yang disetujui dan belum diisi
$requests_query = "SELECT rp.*, u.name as operator_name, a.nama_alat, ns.nomor_seri 
                   FROM request_pengisian rp 
                   JOIN users u ON rp.id_user = u.id_user 
                   JOIN alat a ON rp.id_alat = a.id_alat 
                   JOIN nomor_seri ns ON rp.id_nomor = ns.id_nomor 
                   WHERE rp.status = 'disetujui' 
                   AND rp.id_request NOT IN (SELECT id_request FROM pengisian_bbm)
                   ORDER BY rp.tanggal_verifikasi ASC";
$requests_result = mysqli_query($conn, $requests_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Disetujui - Operator Pengisian</title>
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
        
        .request-card {
            border-left: 5px solid var(--ipc-success);
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
            border-top: 1px solid #e3e6f0;
        }
        
        .table-sm th,
        .table-sm td {
            padding: 10px;
        }
        
        .image-preview {
            max-width: 100%;
            height: auto;
            cursor: pointer;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .image-preview:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
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
        
        .btn-success {
            background: linear-gradient(135deg, var(--ipc-success), #1cc88a);
            border: none;
            color: white;
        }
        
        .btn-success:hover {
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
            color: white;
        }
        
        .btn-lg {
            padding: 15px 30px;
            font-size: 1.1rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 40px;
        }
        
        .empty-state i {
            font-size: 5rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .empty-state h5 {
            color: var(--ipc-text-dark);
            margin-bottom: 15px;
            font-size: 1.3rem;
        }
        
        .empty-state p {
            color: #666;
            margin-bottom: 25px;
            font-size: 1.1rem;
        }
        
        .status-badge {
            background: linear-gradient(135deg, var(--ipc-success), #1cc88a);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .detail-section {
            background-color: #f8f9fc;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .detail-section h6 {
            color: var(--ipc-primary);
            font-weight: 600;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }
        
        .modal-content {
            border-radius: 15px;
            border: none;
        }
        
        .modal-header {
            background: linear-gradient(135deg, var(--ipc-primary), var(--ipc-secondary));
            color: white;
            border-radius: 15px 15px 0 0;
        }
        
        /* Responsive Design */
        @media (max-width: 1200px) {
            .main-content {
                padding: 20px;
            }
            
            .card-header h5 {
                font-size: 1.1rem;
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
            
            .card-header {
                padding: 15px 20px;
            }
            
            .card-body {
                padding: 20px;
            }
            
            .detail-section {
                padding: 15px;
                margin-bottom: 15px;
            }
            
            .btn-lg {
                padding: 12px 25px;
                font-size: 1rem;
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
                margin-bottom: 20px;
            }
            
            .page-header h1 {
                font-size: 1.5rem;
            }
            
            .card-header {
                padding: 15px;
            }
            
            .card-header h5 {
                font-size: 1rem;
            }
            
            .card-header .d-flex {
                flex-direction: column;
                align-items: flex-start !important;
            }
            
            .status-badge {
                margin-top: 10px;
                align-self: flex-start;
            }
            
            .card-body {
                padding: 15px;
            }
            
            .detail-section {
                padding: 15px;
                margin-bottom: 15px;
            }
            
            .detail-section h6 {
                font-size: 1rem;
                margin-bottom: 10px;
            }
            
            .table th,
            .table td {
                padding: 10px 8px;
                font-size: 0.9rem;
            }
            
            .table-sm th,
            .table-sm td {
                padding: 8px 6px;
                font-size: 0.85rem;
            }
            
            .btn {
                padding: 10px 15px;
                font-size: 0.9rem;
            }
            
            .btn-lg {
                padding: 12px 20px;
                font-size: 0.95rem;
            }
            
            .empty-state {
                padding: 40px 20px;
            }
            
            .empty-state i {
                font-size: 3.5rem;
            }
            
            .empty-state h5 {
                font-size: 1.2rem;
            }
            
            .empty-state p {
                font-size: 1rem;
            }
            
            .image-preview {
                max-width: 120px;
                max-height: 80px;
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
                font-size: 1.3rem;
            }
            
            .card-header {
                padding: 12px;
            }
            
            .card-header h5 {
                font-size: 0.95rem;
            }
            
            .card-body {
                padding: 12px;
            }
            
            .detail-section {
                padding: 12px;
                margin-bottom: 12px;
            }
            
            .detail-section h6 {
                font-size: 0.95rem;
                margin-bottom: 8px;
            }
            
            .table th,
            .table td {
                padding: 8px 6px;
                font-size: 0.8rem;
            }
            
            .table-sm th,
            .table-sm td {
                padding: 6px 4px;
                font-size: 0.75rem;
            }
            
            .btn {
                padding: 8px 12px;
                font-size: 0.85rem;
            }
            
            .btn-lg {
                padding: 10px 16px;
                font-size: 0.9rem;
            }
            
            .status-badge {
                font-size: 0.75rem;
                padding: 4px 8px;
            }
            
            .empty-state {
                padding: 30px 15px;
            }
            
            .empty-state i {
                font-size: 3rem;
            }
            
            .empty-state h5 {
                font-size: 1.1rem;
            }
            
            .empty-state p {
                font-size: 0.9rem;
            }
            
            .image-preview {
                max-width: 100px;
                max-height: 70px;
            }
            
            .modal-dialog {
                margin: 10px;
            }
            
            .modal-body {
                padding: 15px;
            }
        }
        
        @media (max-width: 480px) {
            .main-content {
                padding: 8px;
            }
            
            .card-header h5 {
                font-size: 0.9rem;
                line-height: 1.3;
            }
            
            .detail-section h6 {
                font-size: 0.9rem;
            }
            
            .table {
                font-size: 0.75rem;
            }
            
            .table th,
            .table td {
                padding: 6px 4px;
            }
            
            .btn {
                padding: 6px 10px;
                font-size: 0.8rem;
            }
            
            .btn-lg {
                padding: 8px 14px;
                font-size: 0.85rem;
            }
            
            .image-preview {
                max-width: 80px;
                max-height: 60px;
            }
        }
        
        /* Utility classes for responsive images */
        .img-responsive {
            max-width: 100%;
            height: auto;
        }
        
        /* Responsive table wrapper */
        .table-responsive-custom {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
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
    showSidebar('operator_pengisian');
    showTopbar('operator_pengisian');
    ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-check-circle mr-2"></i>Request Disetujui</h1>
        </div>

        <?php if (mysqli_num_rows($requests_result) > 0): ?>
            <?php while ($request = mysqli_fetch_assoc($requests_result)): ?>
                <div class="card request-card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <h5><i class="fas fa-check-circle mr-2"></i>Request #<?php echo $request['id_request']; ?> - <?php echo htmlspecialchars($request['operator_name']); ?></h5>
                            <span class="status-badge">Disetujui</span>
                        </div>
                        <small class="text-white-50 d-block mt-2">Disetujui: <?php echo date('d/m/Y H:i', strtotime($request['tanggal_verifikasi'])); ?></small>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6 col-md-12 mb-3 mb-lg-0">
                                <div class="detail-section">
                                    <h6><i class="fas fa-info-circle mr-2"></i>Detail Request</h6>
                                    <div class="table-responsive-custom">
                                        <table class="table table-sm">
                                            <tr>
                                                <td><strong>Operator:</strong></td>
                                                <td><?php echo htmlspecialchars($request['operator_name']); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Alat:</strong></td>
                                                <td><?php echo htmlspecialchars($request['nama_alat']); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>No. Seri:</strong></td>
                                                <td><?php echo htmlspecialchars($request['nomor_seri']); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Hourmeter:</strong></td>
                                                <td><?php echo htmlspecialchars($request['hourmeter']); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Tanggal Request:</strong></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($request['created_at'])); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-12">
                                <div class="detail-section">
                                    <h6><i class="fas fa-camera mr-2"></i>Dokumentasi</h6>
                                    <div class="row">
                                        <div class="col-6">
                                            <p><strong>Foto Hourmeter:</strong></p>
                                            <img src="../../uploads/<?php echo htmlspecialchars($request['foto_hourmeter']); ?>" 
                                                 class="img-fluid image-preview img-responsive" 
                                                 data-toggle="modal" 
                                                 data-target="#imageModal"
                                                 data-src="../../uploads/<?php echo htmlspecialchars($request['foto_hourmeter']); ?>"
                                                 alt="Foto Hourmeter">
                                        </div>
                                        <div class="col-6">
                                            <p><strong>Foto Indikator BBM:</strong></p>
                                            <img src="../../uploads/<?php echo htmlspecialchars($request['foto_indikator_bbm']); ?>" 
                                                 class="img-fluid image-preview img-responsive"
                                                 data-toggle="modal" 
                                                 data-target="#imageModal"
                                                 data-src="../../uploads/<?php echo htmlspecialchars($request['foto_indikator_bbm']); ?>"
                                                 alt="Foto Indikator BBM">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="text-center">
                            <a href="form_pengisian.php?request_id=<?php echo $request['id_request']; ?>" class="btn btn-success btn-lg">
                                <i class="fas fa-gas-pump mr-2"></i>Lakukan Pengisian BBM
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="card">
                <div class="card-body">
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h5>Tidak ada request yang perlu diisi</h5>
                        <p>Semua request telah diproses atau belum ada yang disetujui.</p>
                        <a href="index.php" class="btn btn-success">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

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
                    <img id="modalImage" src="" class="img-fluid img-responsive" style="max-height: 70vh;">
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview gambar di modal
        $('.image-preview').on('click', function() {
            var src = $(this).data('src');
            $('#modalImage').attr('src', src);
        });
    </script>
</body>
</html>
