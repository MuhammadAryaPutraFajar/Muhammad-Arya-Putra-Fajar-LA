<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../index.php");
    exit();
}

require_once('../../config/db.php');
require_once('../../config/telegram.php');

function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

$message = '';
$message_type = '';

// Proses verifikasi request
if (isset($_POST['verifikasi'])) {
    $request_id = clean_input($_POST['request_id']);
    $status = clean_input($_POST['status']);
    $alasan_tolak = isset($_POST['alasan_tolak']) ? clean_input($_POST['alasan_tolak']) : null;
    $admin_id = $_SESSION['user_id'];
    
    if ($status == 'ditolak' && empty($alasan_tolak)) {
        $message = "Alasan penolakan harus diisi!";
        $message_type = "danger";
    } else {
        $update_query = "UPDATE request_pengisian 
                        SET status = '$status', id_user_admin = '$admin_id', tanggal_verifikasi = NOW()";
        
        if ($status == 'ditolak') {
            $update_query .= ", alasan_tolak = '$alasan_tolak'";
        }
        
        $update_query .= " WHERE id_request = '$request_id'";
        
        if (mysqli_query($conn, $update_query)) {
            // Ambil data request untuk notifikasi
            $request_query = "SELECT rp.*, u.name as operator_name, a.nama_alat, ns.nomor_seri 
                             FROM request_pengisian rp 
                             JOIN users u ON rp.id_user = u.id_user 
                             JOIN alat a ON rp.id_alat = a.id_alat 
                             JOIN nomor_seri ns ON rp.id_nomor = ns.id_nomor 
                             WHERE rp.id_request = '$request_id'";
            $request_result = mysqli_query($conn, $request_query);
            $request_data = mysqli_fetch_assoc($request_result);
            
            if ($status == 'disetujui') {
                $message = "Request berhasil disetujui!";
                $message_type = "success";
                
                // Notifikasi ke operator pengisian
                $operator_pengisian_query = "SELECT id_user FROM users WHERE role = 'operator_pengisian'";
                $operator_pengisian_result = mysqli_query($conn, $operator_pengisian_query);
                while ($op_pengisian = mysqli_fetch_assoc($operator_pengisian_result)) {
                    $notif_message = "✅ <b>Request Pengisian Disetujui</b>\n\n";
                    $notif_message .= "Operator: " . $request_data['operator_name'] . "\n";
                    $notif_message .= "Alat: " . $request_data['nama_alat'] . "\n";
                    $notif_message .= "No. Seri: " . $request_data['nomor_seri'] . "\n";
                    $notif_message .= "Hourmeter: " . $request_data['hourmeter'] . "\n\n";
                    $notif_message .= "Silakan lakukan pengisian BBM.";
                    
                    notifyUser($op_pengisian['id_user'], $notif_message);
                }
                
                // Notifikasi ke operator yang request
                $notif_message = "✅ <b>Request Pengisian Anda Disetujui</b>\n\n";
                $notif_message .= "Alat: " . $request_data['nama_alat'] . "\n";
                $notif_message .= "No. Seri: " . $request_data['nomor_seri'] . "\n";
                $notif_message .= "Hourmeter: " . $request_data['hourmeter'] . "\n\n";
                $notif_message .= "Request Anda telah disetujui dan akan segera diproses oleh operator pengisian.";
                
                notifyUser($request_data['id_user'], $notif_message);
                
            } else {
                $message = "Request berhasil ditolak!";
                $message_type = "warning";
                
                // Notifikasi ke operator yang request
                $notif_message = "❌ <b>Request Pengisian Ditolak</b>\n\n";
                $notif_message .= "Alat: " . $request_data['nama_alat'] . "\n";
                $notif_message .= "No. Seri: " . $request_data['nomor_seri'] . "\n";
                $notif_message .= "Alasan: " . $alasan_tolak;
                
                notifyUser($request_data['id_user'], $notif_message);
            }
        } else {
            $message = "Error: " . mysqli_error($conn);
            $message_type = "danger";
        }
    }
}

// Ambil data request pending
$requests_query = "SELECT rp.*, u.name as operator_name, a.nama_alat, ns.nomor_seri 
                   FROM request_pengisian rp 
                   JOIN users u ON rp.id_user = u.id_user 
                   JOIN alat a ON rp.id_alat = a.id_alat 
                   JOIN nomor_seri ns ON rp.id_nomor = ns.id_nomor 
                   WHERE rp.status = 'pending' 
                   ORDER BY rp.created_at ASC";
$requests_result = mysqli_query($conn, $requests_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Request - Admin Dashboard</title>
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
        
        /* Main Content Area */
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
        
        /* Page Header */
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
        
        /* Card Styling */
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
            background-color: white;
            border-bottom: 1px solid #e3e6f0;
            padding: 20px 25px;
            border-radius: 15px 15px 0 0 !important;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .card-header h5 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--ipc-text-dark);
        }
        
        .card-body {
            padding: 25px;
        }
        
        /* Request Card */
        .request-card {
            border-left: 4px solid var(--ipc-warning);
            background: white;
        }
        
        .request-card .card-header {
            background: linear-gradient(135deg, #fff8e1, #fff3c4);
            border-bottom: 1px solid #ffe082;
        }
        
        .request-header-info {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .request-id {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--ipc-primary);
        }
        
        .request-operator {
            font-size: 1rem;
            font-weight: 600;
            color: var(--ipc-text-dark);
        }
        
        .request-date {
            font-size: 0.85rem;
            color: #666;
        }
        
        /* Detail Tables */
        .detail-table {
            background: #f8f9fc;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .detail-table .table {
            margin-bottom: 0;
            font-size: 0.9rem;
        }
        
        .detail-table .table td {
            padding: 12px 15px;
            border: none;
            vertical-align: middle;
        }
        
        .detail-table .table td:first-child {
            background: rgba(0, 91, 170, 0.05);
            font-weight: 600;
            color: var(--ipc-primary);
            width: 35%;
        }
        
        /* Hourmeter Badge Fix */
        .hourmeter-value {
            display: inline-block;
            background: linear-gradient(135deg, var(--ipc-primary), var(--ipc-secondary));
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
            box-shadow: 0 2px 8px rgba(0, 91, 170, 0.3);
        }
        
        .hourmeter-value::after {
            content: " Hours";
            font-size: 0.8em;
            opacity: 0.9;
        }
        
        /* Image Preview */
        .image-preview {
            max-width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid #e3e6f0;
        }
        
        .image-preview:hover {
            transform: scale(1.05);
            border-color: var(--ipc-primary);
        }
        
        .image-container {
            position: relative;
            overflow: hidden;
            border-radius: 8px;
        }
        
        .image-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--ipc-text-dark);
            margin-bottom: 8px;
        }
        
        /* Action Buttons */
        .action-buttons {
            background: #f8f9fc;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        
        .btn {
            border-radius: 8px;
            font-weight: 600;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--ipc-success), #1cc88a);
            border: none;
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, var(--ipc-danger), #e74a3b);
            border: none;
        }
        
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .empty-state h5 {
            color: var(--ipc-text-dark);
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #666;
            margin: 0;
        }
        
        /* Modal Styling */
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        
        .modal-header {
            background: linear-gradient(135deg, var(--ipc-danger), #e74a3b);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 20px 25px;
        }
        
        .modal-header .modal-title {
            font-weight: 600;
        }
        
        .modal-header .close {
            color: white;
            opacity: 0.8;
        }
        
        .modal-header .close:hover {
            opacity: 1;
        }
        
        .modal-body {
            padding: 25px;
        }
        
        .form-control {
            border-radius: 8px;
            border: 2px solid #e3e6f0;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--ipc-primary);
            box-shadow: 0 0 0 0.2rem rgba(0, 91, 170, 0.25);
        }
        
        /* Alert Styling */
        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: var(--ipc-success);
        }
        
        .alert-warning {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            color: #856404;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #f8d7da, #f1c2c3);
            color: var(--ipc-danger);
        }
        
        /* Responsive Design */
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
            
            .card-header {
                padding: 15px 20px;
                flex-direction: column;
                align-items: flex-start;
            }
            
            .card-body {
                padding: 20px 15px;
            }
            
            .request-header-info {
                width: 100%;
            }
            
            .image-preview {
                height: 100px;
            }
            
            .action-buttons {
                padding: 15px;
            }
            
            .btn {
                width: 100%;
                margin-bottom: 10px;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 10px;
            }
            
            .detail-table .table td:first-child {
                width: 40%;
            }
            
            .card-header,
            .card-body {
                padding: 15px;
            }
        }
        
        /* Animation */
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
        
        .card:nth-child(1) { animation-delay: 0.1s; }
        .card:nth-child(2) { animation-delay: 0.2s; }
        .card:nth-child(3) { animation-delay: 0.3s; }
        .card:nth-child(4) { animation-delay: 0.4s; }
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
        <div class="page-header">
            <h1><i class="fas fa-check-circle mr-2"></i>Verifikasi Request Pengisian BBM</h1>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <?php if (mysqli_num_rows($requests_result) > 0): ?>
            <?php while ($request = mysqli_fetch_assoc($requests_result)): ?>
                <div class="card request-card">
                    <div class="card-header">
                        <div class="request-header-info">
                            <div class="request-id">
                                <i class="fas fa-clock mr-2"></i>Request #<?php echo $request['id_request']; ?>
                            </div>
                            <div class="request-operator"><?php echo $request['operator_name']; ?></div>
                            <div class="request-date">
                                <i class="fas fa-calendar-alt mr-1"></i>
                                <?php echo date('d/m/Y H:i', strtotime($request['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6 mb-4">
                                <h6 class="mb-3"><i class="fas fa-info-circle mr-2"></i>Detail Request</h6>
                                <div class="detail-table">
                                    <table class="table">
                                        <tr>
                                            <td><strong>Operator:</strong></td>
                                            <td><?php echo $request['operator_name']; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Alat:</strong></td>
                                            <td><?php echo $request['nama_alat']; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>No. Seri:</strong></td>
                                            <td><?php echo $request['nomor_seri']; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Hourmeter:</strong></td>
                                            <td>
                                                <span class="hourmeter-value"><?php echo number_format($request['hourmeter'], 0, ',', '.'); ?></span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="col-lg-6 mb-4">
                                <h6 class="mb-3"><i class="fas fa-camera mr-2"></i>Dokumentasi</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="image-label">Foto Hourmeter:</div>
                                        <div class="image-container">
                                            <img src="../../uploads/<?php echo $request['foto_hourmeter']; ?>" 
                                                 class="img-fluid image-preview" 
                                                 data-toggle="modal" 
                                                 data-target="#imageModal"
                                                 data-src="../../uploads/<?php echo $request['foto_hourmeter']; ?>"
                                                 alt="Foto Hourmeter">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="image-label">Foto Indikator BBM:</div>
                                        <div class="image-container">
                                            <img src="../../uploads/<?php echo $request['foto_indikator_bbm']; ?>" 
                                                 class="img-fluid image-preview"
                                                 data-toggle="modal" 
                                                 data-target="#imageModal"
                                                 data-src="../../uploads/<?php echo $request['foto_indikator_bbm']; ?>"
                                                 alt="Foto Indikator BBM">
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <div class="image-label">Tanda Tangan:</div>
                                    <div class="image-container">
                                        <img src="<?php echo $request['tanda_tangan']; ?>" 
                                             class="img-fluid image-preview"
                                             style="max-height: 80px;"
                                             data-toggle="modal" 
                                             data-target="#imageModal"
                                             data-src="<?php echo $request['tanda_tangan']; ?>"
                                             alt="Tanda Tangan">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="action-buttons">
                            <div class="row">
                                <div class="col-md-6">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="request_id" value="<?php echo $request['id_request']; ?>">
                                        <input type="hidden" name="status" value="disetujui">
                                        <button type="submit" name="verifikasi" class="btn btn-success">
                                            <i class="fas fa-check mr-2"></i>Setujui Request
                                        </button>
                                    </form>
                                    
                                    <button type="button" class="btn btn-danger ml-2" data-toggle="modal" data-target="#tolakModal<?php echo $request['id_request']; ?>">
                                        <i class="fas fa-times mr-2"></i>Tolak Request
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Tolak -->
                <div class="modal fade" id="tolakModal<?php echo $request['id_request']; ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="fas fa-times-circle mr-2"></i>Tolak Request</h5>
                                <button type="button" class="close" data-dismiss="modal">
                                    <span>&times;</span>
                                </button>
                            </div>
                            <form method="POST">
                                <div class="modal-body">
                                    <input type="hidden" name="request_id" value="<?php echo $request['id_request']; ?>">
                                    <input type="hidden" name="status" value="ditolak">
                                    <div class="form-group">
                                        <label for="alasan_tolak" class="font-weight-bold">Alasan Penolakan:</label>
                                        <textarea class="form-control" name="alasan_tolak" rows="4" required placeholder="Masukkan alasan penolakan yang jelas dan detail..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                        <i class="fas fa-arrow-left mr-2"></i>Batal
                                    </button>
                                    <button type="submit" name="verifikasi" class="btn btn-danger">
                                        <i class="fas fa-times mr-2"></i>Tolak Request
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="card">
                <div class="card-body">
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h5>Tidak ada request pending</h5>
                        <p>Semua request telah diverifikasi. Halaman ini akan menampilkan request baru yang perlu diverifikasi.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal untuk preview gambar -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, var(--ipc-primary), var(--ipc-secondary));">
                    <h5 class="modal-title"><i class="fas fa-image mr-2"></i>Preview Gambar</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" class="img-fluid" style="max-height: 70vh; border-radius: 8px;">
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Preview gambar di modal
            $('.image-preview').on('click', function() {
                var src = $(this).data('src');
                $('#modalImage').attr('src', src);
            });
            
            // Smooth scroll
            $('html').css('scroll-behavior', 'smooth');
            
            // Auto hide alerts
            $('.alert').delay(5000).fadeOut('slow');
            
            // Hover effects for cards
            $('.request-card').hover(
                function() {
                    $(this).addClass('shadow-lg');
                },
                function() {
                    $(this).removeClass('shadow-lg');
                }
            );
        });
    </script>
</body>
</html>
