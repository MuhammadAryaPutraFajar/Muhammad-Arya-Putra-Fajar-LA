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

// Proses tambah alat
if (isset($_POST['add_alat'])) {
    $nama_alat = clean_input($_POST['nama_alat']);
    $status = clean_input($_POST['status']);
    
    if (empty($nama_alat)) {
        $message = "Nama alat harus diisi!";
        $message_type = "danger";
    } else {
        $insert_query = "INSERT INTO alat (nama_alat, status) VALUES ('$nama_alat', '$status')";
        
        if (mysqli_query($conn, $insert_query)) {
            $message = "Alat berhasil ditambahkan!";
            $message_type = "success";
        } else {
            $message = "Error: " . mysqli_error($conn);
            $message_type = "danger";
        }
    }
}

// Proses edit alat
if (isset($_POST['edit_alat'])) {
    $alat_id = clean_input($_POST['alat_id']);
    $nama_alat = clean_input($_POST['nama_alat']);
    $status = clean_input($_POST['status']);
    
    if (empty($nama_alat)) {
        $message = "Nama alat harus diisi!";
        $message_type = "danger";
    } else {
        $update_query = "UPDATE alat SET nama_alat = '$nama_alat', status = '$status' WHERE id_alat = '$alat_id'";
        
        if (mysqli_query($conn, $update_query)) {
            $message = "Alat berhasil diupdate!";
            $message_type = "success";
        } else {
            $message = "Error: " . mysqli_error($conn);
            $message_type = "danger";
        }
    }
}

// Proses hapus alat
if (isset($_POST['delete_alat'])) {
    $alat_id = clean_input($_POST['alat_id']);
    
    // Cek apakah ada nomor seri yang terkait
    $check_seri = "SELECT COUNT(*) as total FROM nomor_seri WHERE id_alat = '$alat_id'";
    $check_result = mysqli_query($conn, $check_seri);
    $seri_count = mysqli_fetch_assoc($check_result)['total'];
    
    if ($seri_count > 0) {
        $message = "Tidak dapat menghapus alat karena masih memiliki nomor seri terkait!";
        $message_type = "danger";
    } else {
        $delete_query = "DELETE FROM alat WHERE id_alat = '$alat_id'";
        
        if (mysqli_query($conn, $delete_query)) {
            $message = "Alat berhasil dihapus!";
            $message_type = "success";
        } else {
            $message = "Error: " . mysqli_error($conn);
            $message_type = "danger";
        }
    }
}

// Proses tambah nomor seri
if (isset($_POST['add_nomor_seri'])) {
    $alat_id = clean_input($_POST['alat_id']);
    $nomor_seri = clean_input($_POST['nomor_seri']);
    $status = clean_input($_POST['status_seri']);
    
    if (empty($alat_id) || empty($nomor_seri)) {
        $message = "Semua field harus diisi!";
        $message_type = "danger";
    } else {
        // Cek apakah nomor seri sudah ada
        $check_query = "SELECT id_nomor FROM nomor_seri WHERE nomor_seri = '$nomor_seri'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $message = "Nomor seri sudah ada!";
            $message_type = "danger";
        } else {
            $insert_query = "INSERT INTO nomor_seri (id_alat, nomor_seri, status) VALUES ('$alat_id', '$nomor_seri', '$status')";
            
            if (mysqli_query($conn, $insert_query)) {
                $message = "Nomor seri berhasil ditambahkan!";
                $message_type = "success";
            } else {
                $message = "Error: " . mysqli_error($conn);
                $message_type = "danger";
            }
        }
    }
}

// Proses edit nomor seri
if (isset($_POST['edit_nomor_seri'])) {
    $seri_id = clean_input($_POST['seri_id']);
    $alat_id = clean_input($_POST['alat_id']);
    $nomor_seri = clean_input($_POST['nomor_seri']);
    $status = clean_input($_POST['status_seri']);
    
    if (empty($alat_id) || empty($nomor_seri)) {
        $message = "Semua field harus diisi!";
        $message_type = "danger";
    } else {
        // Cek apakah nomor seri sudah ada (kecuali untuk record yang sedang diedit)
        $check_query = "SELECT id_nomor FROM nomor_seri WHERE nomor_seri = '$nomor_seri' AND id_nomor != '$seri_id'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $message = "Nomor seri sudah ada!";
            $message_type = "danger";
        } else {
            $update_query = "UPDATE nomor_seri SET id_alat = '$alat_id', nomor_seri = '$nomor_seri', status = '$status' WHERE id_nomor = '$seri_id'";
            
            if (mysqli_query($conn, $update_query)) {
                $message = "Nomor seri berhasil diupdate!";
                $message_type = "success";
            } else {
                $message = "Error: " . mysqli_error($conn);
                $message_type = "danger";
            }
        }
    }
}

// Proses hapus nomor seri
if (isset($_POST['delete_nomor_seri'])) {
    $seri_id = clean_input($_POST['seri_id']);
    
    $delete_query = "DELETE FROM nomor_seri WHERE id_nomor = '$seri_id'";
    
    if (mysqli_query($conn, $delete_query)) {
        $message = "Nomor seri berhasil dihapus!";
        $message_type = "success";
    } else {
        $message = "Error: " . mysqli_error($conn);
        $message_type = "danger";
    }
}

// Proses update status alat
if (isset($_POST['update_alat_status'])) {
    $alat_id = clean_input($_POST['alat_id']);
    $status = clean_input($_POST['status']);
    
    $update_query = "UPDATE alat SET status = '$status' WHERE id_alat = '$alat_id'";
    
    if (mysqli_query($conn, $update_query)) {
        $message = "Status alat berhasil diupdate!";
        $message_type = "success";
    } else {
        $message = "Error: " . mysqli_error($conn);
        $message_type = "danger";
    }
}

// Proses update status nomor seri
if (isset($_POST['update_seri_status'])) {
    $seri_id = clean_input($_POST['seri_id']);
    $status = clean_input($_POST['status']);
    
    $update_query = "UPDATE nomor_seri SET status = '$status' WHERE id_nomor = '$seri_id'";
    
    if (mysqli_query($conn, $update_query)) {
        $message = "Status nomor seri berhasil diupdate!";
        $message_type = "success";
    } else {
        $message = "Error: " . mysqli_error($conn);
        $message_type = "danger";
    }
}

// Ambil data untuk edit jika ada parameter edit
$edit_alat = null;
$edit_seri = null;

if (isset($_GET['edit_alat'])) {
    $edit_id = clean_input($_GET['edit_alat']);
    $edit_query = "SELECT * FROM alat WHERE id_alat = '$edit_id'";
    $edit_result = mysqli_query($conn, $edit_query);
    if (mysqli_num_rows($edit_result) > 0) {
        $edit_alat = mysqli_fetch_assoc($edit_result);
    }
}

if (isset($_GET['edit_seri'])) {
    $edit_id = clean_input($_GET['edit_seri']);
    $edit_query = "SELECT * FROM nomor_seri WHERE id_nomor = '$edit_id'";
    $edit_result = mysqli_query($conn, $edit_query);
    if (mysqli_num_rows($edit_result) > 0) {
        $edit_seri = mysqli_fetch_assoc($edit_result);
    }
}

// Ambil data alat
$alat_query = "SELECT * FROM alat ORDER BY created_at DESC";
$alat_result = mysqli_query($conn, $alat_query);

// Ambil data nomor seri dengan join alat
$nomor_seri_query = "SELECT ns.*, a.nama_alat 
                     FROM nomor_seri ns 
                     JOIN alat a ON ns.id_alat = a.id_alat 
                     ORDER BY ns.created_at DESC";
$nomor_seri_result = mysqli_query($conn, $nomor_seri_query);

// Statistik
$total_alat = mysqli_query($conn, "SELECT COUNT(*) as total FROM alat");
$total_alat_count = mysqli_fetch_assoc($total_alat)['total'];

$alat_aktif = mysqli_query($conn, "SELECT COUNT(*) as total FROM alat WHERE status = 'aktif'");
$alat_aktif_count = mysqli_fetch_assoc($alat_aktif)['total'];

$total_nomor_seri = mysqli_query($conn, "SELECT COUNT(*) as total FROM nomor_seri");
$total_nomor_seri_count = mysqli_fetch_assoc($total_nomor_seri)['total'];

$seri_aktif = mysqli_query($conn, "SELECT COUNT(*) as total FROM nomor_seri WHERE status = 'aktif'");
$seri_aktif_count = mysqli_fetch_assoc($seri_aktif)['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Alat - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/css/bootstrap.min.css">
    <style>
        :root {
            --ipc-primary: #005baa;
            --ipc-secondary: #0074d9;
            --ipc-accent: #003b6f;
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
            --topbar-height: 70px;
            --transition-speed: 0.3s;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fc;
            overflow-x: hidden;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--topbar-height);
            padding: 25px;
            min-height: calc(100vh - var(--topbar-height));
            transition: all var(--transition-speed) ease;
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
            color: #333;
        }
        
        /* Alert */
        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 20px;
        }
        
        .alert-dismissible .close {
            position: absolute;
            top: 0;
            right: 0;
            padding: 0.75rem 1.25rem;
            color: inherit;
        }
        
        /* Cards */
        .card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 35px rgba(0,0,0,0.12);
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid #e3e6f0;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }
        
        .card-header h5 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            display: flex;
            align-items: center;
        }
        
        .card-header h5 i {
            margin-right: 10px;
            color: var(--ipc-primary);
        }
        
        .card-body {
            padding: 20px;
        }
        
        /* Stats Cards */
        .card-stats {
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .card-stats.primary { 
            background: linear-gradient(135deg, var(--ipc-primary), var(--ipc-secondary)); 
        }
        .card-stats.success { 
            background: linear-gradient(135deg, #1cc88a, #17a673); 
        }
        .card-stats.warning { 
            background: linear-gradient(135deg, #f39c12, #e67e22); 
        }
        .card-stats.info { 
            background: linear-gradient(135deg, #36b9cc, #17a2b8); 
        }
        
        .stats-text {
            font-size: 0.85rem;
            font-weight: 500;
            opacity: 0.9;
            margin-bottom: 8px;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0;
        }
        
        .stats-icon {
            position: absolute;
            right: 15px;
            top: 15px;
            font-size: 2.5rem;
            opacity: 0.2;
        }
        
        /* Form Elements - Perbaikan untuk keterbacaan */
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.75rem;
            display: block;
            font-size: 0.95rem;
        }
        
        .form-control {
            border: 2px solid #e3e6f0;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            background-color: #fff;
            color: #333;
            height: auto;
            min-height: 45px;
            line-height: 1.5;
        }
        
        .form-control:focus {
            border-color: var(--ipc-primary);
            box-shadow: 0 0 0 0.2rem rgba(0, 91, 170, 0.25);
            outline: none;
        }
        
        .form-control::placeholder {
            color: #999;
            font-style: italic;
            opacity: 0.8;
        }
        
        /* Perbaikan untuk select dropdown */
        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 12px center;
            background-repeat: no-repeat;
            background-size: 16px;
            padding-right: 40px;
        }
        
        /* Perbaikan spacing antar kolom */
        .row .col-md-4 {
            padding-left: 10px;
            padding-right: 10px;
        }
        
        /* Buttons - Perbaikan untuk keterbacaan */
        .btn {
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border: none;
            min-height: 45px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--ipc-primary), var(--ipc-secondary));
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--ipc-accent), var(--ipc-primary));
            transform: translateY(-2px);
            color: white;
        }
        
        .btn-sm { 
            padding: 8px 16px; 
            font-size: 0.8rem;
            min-height: 36px;
        }
        
        .btn-success { 
            background: linear-gradient(135deg, #28a745, #20c997); 
        }
        
        .btn-warning { 
            background: linear-gradient(135deg, #ffc107, #fd7e14); 
            color: #212529; 
        }
        
        .btn-danger { 
            background: linear-gradient(135deg, #dc3545, #c82333); 
        }
        
        .btn-info { 
            background: linear-gradient(135deg, #17a2b8, #138496); 
        }
        
        .btn-secondary { 
            background: linear-gradient(135deg, #6c757d, #5a6268); 
        }
        
        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-align: center;
            white-space: nowrap;
            border-radius: 15px;
            min-width: 60px;
            color: white;
        }
        
        .status-aktif {
            background: linear-gradient(135deg, #28a745, #20c997);
        }
        
        .status-nonaktif {
            background: linear-gradient(135deg, #6c757d, #495057);
        }
        
        /* Table */
        .table {
            margin-bottom: 0;
            font-size: 0.9rem;
        }
        
        .table thead th {
            background-color: #f8f9fc;
            border-bottom: 2px solid #e3e6f0;
            font-weight: 600;
            color: #333;
            padding: 12px 10px;
            font-size: 0.85rem;
        }
        
        .table tbody td {
            padding: 12px 10px;
            vertical-align: middle;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .table-hover tbody tr:hover {
            background-color: #f8f9fc;
        }
        
        /* Tabs */
        .nav-tabs {
            border-bottom: 2px solid #e3e6f0;
            margin-bottom: 20px;
        }
        
        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 600;
            padding: 12px 20px;
            border-radius: 8px 8px 0 0;
            transition: all 0.3s ease;
        }
        
        .nav-tabs .nav-link:hover {
            color: var(--ipc-primary);
            background-color: #f8f9fc;
        }
        
        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, var(--ipc-primary), var(--ipc-secondary));
            color: white;
            border: none;
        }
        
        /* Mobile Cards */
        .mobile-cards {
            display: none;
        }
        
        .mobile-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 15px;
            padding: 20px;
        }
        
        .mobile-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        
        .mobile-card-title {
            font-weight: 600;
            color: var(--ipc-primary);
            font-size: 1.1rem;
            margin-bottom: 5px;
        }
        
        .mobile-card-info {
            margin-bottom: 10px;
        }
        
        .mobile-card-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }
        
        .mobile-card-label {
            color: #666;
            font-weight: 500;
        }
        
        .mobile-card-value {
            color: #333;
            font-weight: 600;
        }
        
        .mobile-card-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        
        /* Error/Success States */
        .form-control.is-invalid {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }
        
        .invalid-feedback {
            display: block;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: #dc3545;
        }
        
        .form-control.is-valid {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
        
        .valid-feedback {
            display: block;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: #28a745;
        }
        
        /* Responsive */
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
                margin-left: 0;
                margin-top: var(--topbar-height);
            }
            
            .table-responsive {
                display: none;
            }
            
            .mobile-cards {
                display: block;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .stats-number {
                font-size: 1.8rem;
            }
            
            .page-header h1 {
                font-size: 1.5rem;
            }
            
            /* Perbaikan responsive untuk form */
            .row .col-md-4 {
                margin-bottom: 1rem;
                padding-left: 15px;
                padding-right: 15px;
            }
            
            .form-group {
                margin-bottom: 1.25rem;
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
            
            .stats-number {
                font-size: 1.5rem;
            }
            
            .card-body {
                padding: 15px;
            }
            
            .page-header {
                padding: 15px;
            }
        }
        
        /* Disabled/readonly states */
        .form-control:disabled,
        .form-control[readonly] {
            background-color: #f8f9fa;
            opacity: 1;
            color: #6c757d;
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
        <div class="page-header">
            <h1><i class="fas fa-cogs mr-2"></i>Kelola Alat</h1>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-3">
                <div class="card card-stats primary h-100">
                    <div class="card-body">
                        <div class="stats-text">Total Alat</div>
                        <div class="stats-number"><?php echo $total_alat_count; ?></div>
                        <i class="fas fa-cogs stats-icon"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-3">
                <div class="card card-stats success h-100">
                    <div class="card-body">
                        <div class="stats-text">Alat Aktif</div>
                        <div class="stats-number"><?php echo $alat_aktif_count; ?></div>
                        <i class="fas fa-check-circle stats-icon"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-3">
                <div class="card card-stats warning h-100">
                    <div class="card-body">
                        <div class="stats-text">Total Nomor Seri</div>
                        <div class="stats-number"><?php echo $total_nomor_seri_count; ?></div>
                        <i class="fas fa-barcode stats-icon"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-3">
                <div class="card card-stats info h-100">
                    <div class="card-body">
                        <div class="stats-text">Seri Aktif</div>
                        <div class="stats-number"><?php echo $seri_aktif_count; ?></div>
                        <i class="fas fa-list-ol stats-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs" id="alatTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link <?php echo (!isset($_GET['edit_seri']) && (!isset($_GET['tab']) || $_GET['tab'] == 'alat')) ? 'active' : ''; ?>" id="alat-tab" data-toggle="tab" href="#alat" role="tab">
                    <i class="fas fa-cogs mr-2"></i>Kelola Alat
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (isset($_GET['edit_seri']) || (isset($_GET['tab']) && $_GET['tab'] == 'nomor-seri')) ? 'active' : ''; ?>" id="nomor-seri-tab" data-toggle="tab" href="#nomor-seri" role="tab">
                    <i class="fas fa-barcode mr-2"></i>Kelola Nomor Seri
                </a>
            </li>
        </ul>

        <div class="tab-content" id="alatTabsContent">
            <!-- Tab Kelola Alat -->
            <div class="tab-pane fade <?php echo (!isset($_GET['edit_seri']) && (!isset($_GET['tab']) || $_GET['tab'] == 'alat')) ? 'show active' : ''; ?>" id="alat" role="tabpanel">
                <!-- Form Tambah/Edit Alat -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-<?php echo $edit_alat ? 'edit' : 'plus'; ?> mr-2"></i><?php echo $edit_alat ? 'Edit Alat' : 'Tambah Alat Baru'; ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php if ($edit_alat): ?>
                                <input type="hidden" name="alat_id" value="<?php echo $edit_alat['id_alat']; ?>">
                            <?php endif; ?>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="nama_alat">Nama Alat</label>
                                        <input type="text" class="form-control" id="nama_alat" name="nama_alat" 
                                               value="<?php echo $edit_alat ? htmlspecialchars($edit_alat['nama_alat']) : ''; ?>" 
                                               placeholder="Masukkan nama alat" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="status">Status</label>
                                        <select class="form-control" id="status" name="status" required>
                                            <option value="aktif" <?php echo ($edit_alat && $edit_alat['status'] == 'aktif') ? 'selected' : ''; ?>>Aktif</option>
                                            <option value="nonaktif" <?php echo ($edit_alat && $edit_alat['status'] == 'nonaktif') ? 'selected' : ''; ?>>Non-aktif</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-0">
                                <button type="submit" name="<?php echo $edit_alat ? 'edit_alat' : 'add_alat'; ?>" class="btn btn-primary">
                                    <i class="fas fa-save mr-2"></i><?php echo $edit_alat ? 'Update Alat' : 'Simpan Alat'; ?>
                                </button>
                                <?php if ($edit_alat): ?>
                                    <a href="kelola_alat.php" class="btn btn-secondary ml-2">
                                        <i class="fas fa-times mr-2"></i>Batal
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Daftar Alat -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-list mr-2"></i>Daftar Alat</h5>
                    </div>
                    <div class="card-body">
                        <!-- Desktop Table View -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="8%">No</th>
                                        <th width="35%">Nama Alat</th>
                                        <th width="15%">Status</th>
                                        <th width="20%">Tanggal Dibuat</th>
                                        <th width="22%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    mysqli_data_seek($alat_result, 0);
                                    while ($alat = mysqli_fetch_assoc($alat_result)): 
                                    ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo htmlspecialchars($alat['nama_alat']); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $alat['status']; ?>">
                                                    <?php echo ucfirst($alat['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($alat['created_at'])); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="?edit_alat=<?php echo $alat['id_alat']; ?>" class="btn btn-warning btn-sm">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus alat ini?')">
                                                        <input type="hidden" name="alat_id" value="<?php echo $alat['id_alat']; ?>">
                                                        <button type="submit" name="delete_alat" class="btn btn-danger btn-sm">
                                                            <i class="fas fa-trash"></i> Hapus
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Cards View -->
                        <div class="mobile-cards">
                            <?php 
                            mysqli_data_seek($alat_result, 0);
                            while ($alat = mysqli_fetch_assoc($alat_result)): 
                            ?>
                                <div class="mobile-card">
                                    <div class="mobile-card-header">
                                        <div>
                                            <div class="mobile-card-title"><?php echo htmlspecialchars($alat['nama_alat']); ?></div>
                                            <span class="status-badge status-<?php echo $alat['status']; ?>">
                                                <?php echo ucfirst($alat['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mobile-card-info">
                                        <div class="mobile-card-row">
                                            <span class="mobile-card-label">Tanggal Dibuat:</span>
                                            <span class="mobile-card-value"><?php echo date('d/m/Y H:i', strtotime($alat['created_at'])); ?></span>
                                        </div>
                                    </div>
                                    <div class="mobile-card-actions">
                                        <a href="?edit_alat=<?php echo $alat['id_alat']; ?>" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus alat ini?')">
                                            <input type="hidden" name="alat_id" value="<?php echo $alat['id_alat']; ?>">
                                            <button type="submit" name="delete_alat" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Kelola Nomor Seri -->
            <div class="tab-pane fade <?php echo (isset($_GET['edit_seri']) || (isset($_GET['tab']) && $_GET['tab'] == 'nomor-seri')) ? 'show active' : ''; ?>" id="nomor-seri" role="tabpanel">
                <!-- Form Tambah/Edit Nomor Seri -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-<?php echo $edit_seri ? 'edit' : 'plus'; ?> mr-2"></i><?php echo $edit_seri ? 'Edit Nomor Seri' : 'Tambah Nomor Seri Baru'; ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php if ($edit_seri): ?>
                                <input type="hidden" name="seri_id" value="<?php echo $edit_seri['id_nomor']; ?>">
                            <?php endif; ?>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="alat_id">Pilih Alat</label>
                                        <select class="form-control" id="alat_id" name="alat_id" required>
                                            <option value="">-- Pilih Alat --</option>
                                            <?php
                                            mysqli_data_seek($alat_result, 0);
                                            while ($alat_option = mysqli_fetch_assoc($alat_result)):
                                            ?>
                                                <option value="<?php echo $alat_option['id_alat']; ?>" 
                                                        <?php echo ($edit_seri && $edit_seri['id_alat'] == $alat_option['id_alat']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($alat_option['nama_alat']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="nomor_seri">Nomor Seri</label>
                                        <input type="text" class="form-control" id="nomor_seri" name="nomor_seri" 
                                               value="<?php echo $edit_seri ? htmlspecialchars($edit_seri['nomor_seri']) : ''; ?>" 
                                               placeholder="Masukkan nomor seri" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="status_seri">Status</label>
                                        <select class="form-control" id="status_seri" name="status_seri" required>
                                            <option value="aktif" <?php echo ($edit_seri && $edit_seri['status'] == 'aktif') ? 'selected' : ''; ?>>Aktif</option>
                                            <option value="nonaktif" <?php echo ($edit_seri && $edit_seri['status'] == 'nonaktif') ? 'selected' : ''; ?>>Non-aktif</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-0">
                                <button type="submit" name="<?php echo $edit_seri ? 'edit_nomor_seri' : 'add_nomor_seri'; ?>" class="btn btn-primary">
                                    <i class="fas fa-save mr-2"></i><?php echo $edit_seri ? 'Update Nomor Seri' : 'Simpan Nomor Seri'; ?>
                                </button>
                                <?php if ($edit_seri): ?>
                                    <a href="?tab=nomor-seri" class="btn btn-secondary ml-2">
                                        <i class="fas fa-times mr-2"></i>Batal
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Daftar Nomor Seri -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-list mr-2"></i>Daftar Nomor Seri</h5>
                    </div>
                    <div class="card-body">
                        <!-- Desktop Table View -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="8%">No</th>
                                        <th width="25%">Nama Alat</th>
                                        <th width="25%">Nomor Seri</th>
                                        <th width="12%">Status</th>
                                        <th width="18%">Tanggal Dibuat</th>
                                        <th width="12%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    while ($seri = mysqli_fetch_assoc($nomor_seri_result)): 
                                    ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo htmlspecialchars($seri['nama_alat']); ?></td>
                                            <td><?php echo htmlspecialchars($seri['nomor_seri']); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $seri['status']; ?>">
                                                    <?php echo ucfirst($seri['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($seri['created_at'])); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="?edit_seri=<?php echo $seri['id_nomor']; ?>&tab=nomor-seri" class="btn btn-warning btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus nomor seri ini?')">
                                                        <input type="hidden" name="seri_id" value="<?php echo $seri['id_nomor']; ?>">
                                                        <button type="submit" name="delete_nomor_seri" class="btn btn-danger btn-sm">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Cards View -->
                        <div class="mobile-cards">
                            <?php 
                            mysqli_data_seek($nomor_seri_result, 0);
                            while ($seri = mysqli_fetch_assoc($nomor_seri_result)): 
                            ?>
                                <div class="mobile-card">
                                    <div class="mobile-card-header">
                                        <div>
                                            <div class="mobile-card-title"><?php echo htmlspecialchars($seri['nomor_seri']); ?></div>
                                            <span class="status-badge status-<?php echo $seri['status']; ?>">
                                                <?php echo ucfirst($seri['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mobile-card-info">
                                        <div class="mobile-card-row">
                                            <span class="mobile-card-label">Nama Alat:</span>
                                            <span class="mobile-card-value"><?php echo htmlspecialchars($seri['nama_alat']); ?></span>
                                        </div>
                                        <div class="mobile-card-row">
                                            <span class="mobile-card-label">Tanggal Dibuat:</span>
                                            <span class="mobile-card-value"><?php echo date('d/m/Y H:i', strtotime($seri['created_at'])); ?></span>
                                        </div>
                                    </div>
                                    <div class="mobile-card-actions">
                                        <a href="?edit_seri=<?php echo $seri['id_nomor']; ?>&tab=nomor-seri" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus nomor seri ini?')">
                                            <input type="hidden" name="seri_id" value="<?php echo $seri['id_nomor']; ?>">
                                            <button type="submit" name="delete_nomor_seri" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Auto hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
            
            // Smooth transitions for cards
            $('.card').hover(
                function() {
                    $(this).addClass('shadow-lg');
                },
                function() {
                    $(this).removeClass('shadow-lg');
                }
            );
            
            // Form validation feedback
            $('form').on('submit', function() {
                var isValid = true;
                
                // Check required fields
                $(this).find('[required]').each(function() {
                    if (!$(this).val()) {
                        $(this).addClass('is-invalid');
                        isValid = false;
                    } else {
                        $(this).removeClass('is-invalid').addClass('is-valid');
                    }
                });
                
                return isValid;
            });
            
            // Remove validation classes on input
            $('input, select').on('input change', function() {
                $(this).removeClass('is-invalid is-valid');
            });
        });
    </script>
</body>
</html>
