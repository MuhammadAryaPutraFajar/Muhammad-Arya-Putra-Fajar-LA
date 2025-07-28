<?php
session_start();
// Cek apakah pengguna sudah login dan memiliki role admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../index.php");
    exit();
}

// Koneksi ke database
require_once('../../config/db.php');

// Fungsi untuk membersihkan input
function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

// Fungsi untuk mendapatkan display name role
function getRoleDisplay($role) {
    switch ($role) {
        case 'admin': 
            return 'Admin';
        case 'supervisor': 
            return 'Supervisor';
        case 'operator': 
            return 'Operator';
        case 'operator_pengisian': 
            return 'Operator Pengisian';
        default:
            return ucfirst($role);
    }
}

// Fungsi untuk mendapatkan class CSS role
function getRoleClass($role) {
    switch ($role) {
        case 'admin': 
            return 'role-admin';
        case 'supervisor': 
            return 'role-supervisor';
        case 'operator': 
            return 'role-operator';
        case 'operator_pengisian': 
            return 'role-operator_pengisian';
        default:
            return 'role-operator';
    }
}

// Inisialisasi variabel untuk pesan
$message = '';
$message_type = '';

// Proses tambah pengguna baru
if (isset($_POST['add_user'])) {
    $name = clean_input($_POST['name']);
    $email = clean_input($_POST['email']);
    $password = clean_input($_POST['password']); // Password plaintext
    $role = clean_input($_POST['role']);
    $chat_id_telegram = !empty($_POST['chat_id_telegram']) ? clean_input($_POST['chat_id_telegram']) : NULL;
    
    // Validasi input
    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $message = "Nama, email, password, dan role harus diisi!";
        $message_type = "danger";
    } else {
        // Cek apakah email sudah terdaftar
        $check_email = "SELECT * FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $check_email);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $message = "Email sudah terdaftar!";
            $message_type = "danger";
        } else {
            // Simpan password dalam format plaintext 
            $plaintext_password = $password;
            
            // Query untuk tambah pengguna
            if ($chat_id_telegram) {
                $insert_query = "INSERT INTO users (name, email, password, role, chat_id_telegram) VALUES (?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $insert_query);
                mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $plaintext_password, $role, $chat_id_telegram);
            } else {
                $insert_query = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $insert_query);
                mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $plaintext_password, $role);
            }
            
            if (mysqli_stmt_execute($stmt)) {
                $message = "Pengguna baru berhasil ditambahkan!";
                $message_type = "success";
            } else {
                $message = "Error: " . mysqli_error($conn);
                $message_type = "danger";
            }
        }
        mysqli_stmt_close($stmt);
    }
}

// Proses hapus pengguna
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = clean_input($_GET['delete']);
    
    // Cek apakah ID valid
    $check_query = "SELECT * FROM users WHERE id_user = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $delete_query = "DELETE FROM users WHERE id_user = ?";
        $stmt_delete = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($stmt_delete, "i", $id);
        
        if (mysqli_stmt_execute($stmt_delete)) {
            $message = "Pengguna berhasil dihapus!";
            $message_type = "success";
        } else {
            $message = "Error: " . mysqli_error($conn);
            $message_type = "danger";
        }
        mysqli_stmt_close($stmt_delete);
    } else {
        $message = "Pengguna tidak ditemukan!";
        $message_type = "danger";
    }
    mysqli_stmt_close($stmt);
}

// Proses update pengguna
if (isset($_POST['update_user'])) {
    $id = clean_input($_POST['id']);
    $name = clean_input($_POST['name']);
    $email = clean_input($_POST['email']);
    $role = clean_input($_POST['role']);
    $password = isset($_POST['password']) ? clean_input($_POST['password']) : '';
    $chat_id_telegram = !empty($_POST['chat_id_telegram']) ? clean_input($_POST['chat_id_telegram']) : NULL;
    
    // Validasi input
    if (empty($name) || empty($email) || empty($role)) {
        $message = "Nama, email, dan role harus diisi!";
        $message_type = "danger";
    } else {
        // Cek apakah email sudah terdaftar oleh pengguna lain
        $check_email = "SELECT * FROM users WHERE email = ? AND id_user != ?";
        $stmt = mysqli_prepare($conn, $check_email);
        mysqli_stmt_bind_param($stmt, "si", $email, $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $message = "Email sudah terdaftar oleh pengguna lain!";
            $message_type = "danger";
        } else {
            // Jika password diubah
            if (!empty($password)) {
                $plaintext_password = $password; // Password plaintext
                if ($chat_id_telegram) {
                    $update_query = "UPDATE users SET name = ?, email = ?, password = ?, role = ?, chat_id_telegram = ? WHERE id_user = ?";
                    $stmt_update = mysqli_prepare($conn, $update_query);
                    mysqli_stmt_bind_param($stmt_update, "sssssi", $name, $email, $plaintext_password, $role, $chat_id_telegram, $id);
                } else {
                    $update_query = "UPDATE users SET name = ?, email = ?, password = ?, role = ?, chat_id_telegram = NULL WHERE id_user = ?";
                    $stmt_update = mysqli_prepare($conn, $update_query);
                    mysqli_stmt_bind_param($stmt_update, "ssssi", $name, $email, $plaintext_password, $role, $id);
                }
            } else {
                if ($chat_id_telegram) {
                    $update_query = "UPDATE users SET name = ?, email = ?, role = ?, chat_id_telegram = ? WHERE id_user = ?";
                    $stmt_update = mysqli_prepare($conn, $update_query);
                    mysqli_stmt_bind_param($stmt_update, "ssssi", $name, $email, $role, $chat_id_telegram, $id);
                } else {
                    $update_query = "UPDATE users SET name = ?, email = ?, role = ?, chat_id_telegram = NULL WHERE id_user = ?";
                    $stmt_update = mysqli_prepare($conn, $update_query);
                    mysqli_stmt_bind_param($stmt_update, "sssi", $name, $email, $role, $id);
                }
            }
            
            if (mysqli_stmt_execute($stmt_update)) {
                $message = "Data pengguna berhasil diupdate!";
                $message_type = "success";
            } else {
                $message = "Error: " . mysqli_error($conn);
                $message_type = "danger";
            }
            mysqli_stmt_close($stmt_update);
        }
        mysqli_stmt_close($stmt);
    }
}

// Ambil data pengguna untuk ditampilkan
$query = "SELECT * FROM users ORDER BY id_user DESC";
$users = mysqli_query($conn, $query);

// Hitung jumlah pengguna berdasarkan role
$total_users = mysqli_num_rows($users);
$count_admin = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
$admin_count = mysqli_fetch_assoc($count_admin)['total'];

$count_supervisor = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'supervisor'");
$supervisor_count = mysqli_fetch_assoc($count_supervisor)['total'];

$count_operator = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'operator'");
$operator_count = mysqli_fetch_assoc($count_operator)['total'];

$count_operator_pengisian = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'operator_pengisian'");
$operator_pengisian_count = mysqli_fetch_assoc($count_operator_pengisian)['total'];

// Reset pointer users query untuk digunakan lagi
mysqli_data_seek($users, 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - Admin Dashboard</title>
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
        
        /* Alert Messages */
        .alert {
            border-radius: 10px;
            border: none;
            margin-bottom: 25px;
        }
        
        /* Stats Cards */
        .stats-row {
            margin-bottom: 25px;
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
        
        .card-stats {
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .card-stats.primary { 
            background: linear-gradient(135deg, var(--ipc-primary), var(--ipc-secondary)); 
        }
        .card-stats.danger { 
            background: linear-gradient(135deg, #e74a3b, #c0392b); 
        }
        .card-stats.info { 
            background: linear-gradient(135deg, #36b9cc, #17a2b8); 
        }
        .card-stats.success { 
            background: linear-gradient(135deg, #1cc88a, #17a673); 
        }
        .card-stats.warning { 
            background: linear-gradient(135deg, #f39c12, #e67e22); 
        }
        
        .card-stats .card-body {
            padding: 20px;
            position: relative;
            z-index: 2;
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
            z-index: 1;
        }
        
        /* Main Card */
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
            padding: 0;
        }
        
        /* Table Styling */
        .table-responsive {
            border-radius: 0 0 15px 15px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .table {
            margin-bottom: 0;
            font-size: 0.9rem;
            min-width: 800px;
        }
        
        .table thead th {
            background-color: #f8f9fc;
            border-bottom: 2px solid #e3e6f0;
            font-weight: 600;
            color: var(--ipc-text-dark);
            padding: 12px 10px;
            font-size: 0.85rem;
            vertical-align: middle;
            white-space: nowrap;
        }
        
        .table tbody td {
            padding: 12px 10px;
            vertical-align: middle;
            border-bottom: 1px solid #f0f0f0;
            white-space: nowrap;
        }
        
        .table-hover tbody tr:hover {
            background-color: #f8f9fc;
        }
        
        /* Mobile Card Layout */
        .user-cards {
            display: none;
        }
        
        .user-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 15px;
            padding: 20px;
            position: relative;
        }
        
        .user-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .user-card-name {
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--ipc-text-dark);
            margin: 0;
        }
        
        .user-card-role {
            margin-left: 10px;
        }
        
        .user-card-info {
            margin-bottom: 15px;
        }
        
        .user-card-email {
            color: #666;
            font-size: 0.9rem;
            margin: 5px 0;
            word-break: break-all;
        }
        
        .user-card-telegram {
            color: #666;
            font-size: 0.9rem;
            margin: 5px 0;
        }
        
        .user-card-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        /* Role Badges */
        .role-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
            white-space: nowrap;
        }
        
        .role-admin { 
            background-color: #fce4ec; 
            color: #ad1457; 
        }
        .role-supervisor { 
            background-color: #fff8e1; 
            color: #f57f17; 
        }
        .role-operator { 
            background-color: #e8f5e8; 
            color: #2e7d32; 
        }
        .role-operator_pengisian { 
            background-color: #e3f2fd; 
            color: #1565c0; 
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 5px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-action {
            width: 32px;
            height: 32px;
            padding: 0;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            font-size: 0.8rem;
        }
        
        .btn-action:hover {
            transform: translateY(-1px);
        }
        
        /* Modal Styling */
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        }
        
        .modal-header {
            border-bottom: 1px solid #e3e6f0;
            padding: 20px 25px;
            border-radius: 15px 15px 0 0;
        }
        
        .modal-header h5 {
            margin: 0;
            font-weight: 600;
            color: var(--ipc-text-dark);
        }
        
        .modal-body {
            padding: 25px;
        }
        
        .modal-footer {
            border-top: 1px solid #e3e6f0;
            padding: 20px 25px;
            border-radius: 0 0 15px 15px;
        }
        
        .form-group label {
            font-weight: 600;
            color: var(--ipc-text-dark);
            margin-bottom: 8px;
        }
        
        .form-control {
            border-radius: 8px;
            border: 1px solid #d1d3e2;
            padding: 12px 15px;
            transition: all 0.2s ease;
        }
        
        .form-control:focus {
            border-color: var(--ipc-primary);
            box-shadow: 0 0 0 0.2rem rgba(0, 91, 170, 0.25);
        }
        
        /* Button Styling */
        .btn {
            border-radius: 8px;
            font-weight: 600;
            padding: 10px 20px;
            transition: all 0.2s ease;
        }
        
        .btn-primary {
            background: var(--ipc-primary);
            border-color: var(--ipc-primary);
        }
        
        .btn-primary:hover {
            background: var(--ipc-accent);
            border-color: var(--ipc-accent);
            transform: translateY(-1px);
        }
        
        .btn-sm {
            padding: 8px 15px;
            font-size: 0.85rem;
        }
        
        /* Telegram Info Styling */
        .telegram-info {
            background-color: #e3f2fd;
            border: 1px solid #2196f3;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 0.85rem;
            color: #1565c0;
        }
        
        .telegram-info i {
            margin-right: 5px;
        }
        
        /* Plaintext Warning Styling */
        .plaintext-warning {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 0.85rem;
            color: #856404;
        }
        
        .plaintext-warning i {
            margin-right: 5px;
        }
        
        .security-warning {
            background-color: #f8d7da;
            border: 1px solid #dc3545;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            font-size: 0.85rem;
            color: #721c24;
        }
        
        .security-warning i {
            margin-right: 5px;
        }
        
        /* Responsive Design */
        @media (max-width: 1200px) {
            .stats-row .col-xl-3 {
                flex: 0 0 50%;
                max-width: 50%;
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
            
            .stats-row .col-xl-3 {
                flex: 0 0 50%;
                max-width: 50%;
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
            }
            
            .page-header h1 {
                font-size: 1.5rem;
            }
            
            .card-header {
                padding: 15px 20px;
                flex-direction: column;
                align-items: flex-start;
            }
            
            .stats-row .col-xl-3 {
                flex: 0 0 100%;
                max-width: 100%;
                margin-bottom: 15px;
            }
            
            .card-stats .card-body {
                padding: 15px;
            }
            
            /* Hide table and show cards on mobile */
            .table-responsive {
                display: none;
            }
            
            .user-cards {
                display: block !important;
            }
            
            .role-badge {
                font-size: 0.7rem;
                padding: 3px 8px;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 10px;
            }
            
            .stats-number {
                font-size: 1.5rem;
            }
            
            .stats-icon {
                font-size: 1.8rem;
            }
            
            .modal-header,
            .modal-body,
            .modal-footer {
                padding: 15px 20px;
            }
            
            .table {
                font-size: 0.75rem;
            }
            
            .btn {
                padding: 8px 12px;
                font-size: 0.8rem;
            }
        }
        
        /* Custom scrollbar for table */
        .table-responsive::-webkit-scrollbar {
            height: 6px;
        }
        
        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .table-responsive::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        
        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #555;
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
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        
        <div class="page-header">
            <h1><i class="fas fa-users-cog mr-2"></i>Kelola Pengguna</h1>
        </div>
        
        <div class="row stats-row">
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                <div class="card card-stats danger h-100">
                    <div class="card-body">
                        <div class="stats-text">Admin</div>
                        <div class="stats-number"><?php echo $admin_count; ?></div>
                        <i class="fas fa-user-shield stats-icon"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                <div class="card card-stats info h-100">
                    <div class="card-body">
                        <div class="stats-text">Supervisor</div>
                        <div class="stats-number"><?php echo $supervisor_count; ?></div>
                        <i class="fas fa-user-tie stats-icon"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                <div class="card card-stats success h-100">
                    <div class="card-body">
                        <div class="stats-text">Operator</div>
                        <div class="stats-number"><?php echo $operator_count; ?></div>
                        <i class="fas fa-user-cog stats-icon"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                <div class="card card-stats warning h-100">
                    <div class="card-body">
                        <div class="stats-text">Op. Pengisian</div>
                        <div class="stats-number"><?php echo $operator_pengisian_count; ?></div>
                        <i class="fas fa-user-plus stats-icon"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-list mr-2"></i>Daftar Pengguna</h5>
                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addUserModal">
                    <i class="fas fa-plus mr-1"></i> Tambah Pengguna
                </button>
            </div>
            <div class="card-body">
                <!-- Desktop Table View -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th width="20%">Nama</th>
                                <th width="25%">Email</th>
                                <th width="12%" class="text-center">Role</th>
                                <th width="18%">Chat ID Telegram</th>
                                <th width="20%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if (mysqli_num_rows($users) > 0):
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($users)):
                                    $role_class = getRoleClass($row['role']);
                                    $role_display = getRoleDisplay($row['role']);
                            ?>
                            <tr>
                                <td class="text-center"><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td class="text-center">
                                    <span class="role-badge <?php echo $role_class; ?>">
                                        <?php echo $role_display; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($row['chat_id_telegram'])): ?>
                                        <i class="fab fa-telegram-plane text-primary mr-1"></i>
                                        <span class="text-muted"><?php echo htmlspecialchars($row['chat_id_telegram']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-primary btn-action edit-user-btn" 
                                                data-id="<?php echo $row['id_user']; ?>"
                                                data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                                data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                                data-role="<?php echo htmlspecialchars($row['role']); ?>"
                                                data-telegram="<?php echo htmlspecialchars($row['chat_id_telegram']); ?>"
                                                title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger btn-action" 
                                                data-toggle="modal" 
                                                data-target="#deleteModal" 
                                                data-id="<?php echo $row['id_user']; ?>" 
                                                data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                                title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                endwhile; 
                                // Reset pointer untuk mobile cards
                                mysqli_data_seek($users, 0);
                            else: 
                            ?>
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada data pengguna</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Mobile Card View -->
                <div class="user-cards">
                    <?php 
                    if (mysqli_num_rows($users) > 0):
                        while ($row = mysqli_fetch_assoc($users)):
                            $role_class = getRoleClass($row['role']);
                            $role_display = getRoleDisplay($row['role']);
                    ?>
                    <div class="user-card">
                        <div class="user-card-header">
                            <h6 class="user-card-name"><?php echo htmlspecialchars($row['name']); ?></h6>
                            <span class="role-badge <?php echo $role_class; ?> user-card-role">
                                <?php echo $role_display; ?>
                            </span>
                        </div>
                        <div class="user-card-info">
                            <div class="user-card-email">
                                <i class="fas fa-envelope mr-2"></i><?php echo htmlspecialchars($row['email']); ?>
                            </div>
                            <div class="user-card-telegram">
                                <?php if (!empty($row['chat_id_telegram'])): ?>
                                    <i class="fab fa-telegram-plane mr-2"></i><?php echo htmlspecialchars($row['chat_id_telegram']); ?>
                                <?php else: ?>
                                    <i class="fab fa-telegram-plane mr-2 text-muted"></i><span class="text-muted">Tidak ada</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="user-card-actions">
                            <button class="btn btn-sm btn-primary edit-user-btn" 
                                    data-id="<?php echo $row['id_user']; ?>"
                                    data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                    data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                    data-role="<?php echo htmlspecialchars($row['role']); ?>"
                                    data-telegram="<?php echo htmlspecialchars($row['chat_id_telegram']); ?>">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-danger" 
                                    data-toggle="modal" 
                                    data-target="#deleteModal" 
                                    data-id="<?php echo $row['id_user']; ?>" 
                                    data-name="<?php echo htmlspecialchars($row['name']); ?>">
                                <i class="fas fa-trash mr-1"></i> Hapus
                            </button>
                        </div>
                    </div>
                    <?php 
                        endwhile; 
                    else: 
                    ?>
                    <div class="text-center py-4">
                        <p class="text-muted">Tidak ada data pengguna</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Tambah Pengguna -->
    <div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel"><i class="fas fa-user-plus mr-2"></i>Tambah Pengguna Baru</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="password" required id="password">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary toggle-password" type="button">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="plaintext-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Peringatan:</strong> Password akan disimpan dalam format plaintext (tidak terenkripsi).
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Role</label>
                            <select class="form-control" name="role" required>
                                <option value="">Pilih Role</option>
                                <option value="admin">Admin</option>
                                <option value="supervisor">Supervisor</option>
                                <option value="operator">Operator</option>
                                <option value="operator_pengisian">Operator Pengisian</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Chat ID Telegram <span class="text-muted">(Opsional)</span></label>
                            <input type="text" class="form-control" name="chat_id_telegram" placeholder="Contoh: 123456789">
                            <div class="telegram-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Cara mendapatkan Chat ID:</strong><br>
                                1. Kirim pesan ke bot @get_id_bot di Telegram<br>
                                2. Ketik /my_id untuk mendapatkan Chat ID Anda<br>
                                3. Salin angka yang diberikan (tanpa tanda minus jika ada)
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" name="add_user">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal Edit Pengguna -->
    <div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel"><i class="fas fa-user-edit mr-2"></i>Edit Pengguna</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" class="form-control" name="name" id="edit_name" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email" id="edit_email" required>
                        </div>
                        <div class="form-group">
                            <label>Password Baru (Kosongkan jika tidak diubah)</label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="password" id="edit_password">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary toggle-password" type="button">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="plaintext-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Peringatan:</strong> Password baru akan disimpan dalam format plaintext jika diubah.
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Role</label>
                            <select class="form-control" name="role" id="edit_role" required>
                                <option value="admin">Admin</option>
                                <option value="supervisor">Supervisor</option>
                                <option value="operator">Operator</option>
                                <option value="operator_pengisian">Operator Pengisian</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Chat ID Telegram <span class="text-muted">(Opsional)</span></label>
                            <input type="text" class="form-control" name="chat_id_telegram" id="edit_telegram" placeholder="Contoh: 123456789">
                            <div class="telegram-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Cara mendapatkan Chat ID:</strong><br>
                                1. Kirim pesan ke bot @get_id_bot di Telegram<br>
                                2. Ketik /my_id untuk mendapatkan Chat ID Anda<br>
                                3. Salin angka yang diberikan (tanpa tanda minus jika ada)
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" name="update_user">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel"><i class="fas fa-trash-alt mr-2"></i>Konfirmasi Hapus</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin menghapus pengguna <strong id="deleteUserName"></strong>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <a href="#" class="btn btn-danger" id="confirmDelete">Hapus</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Toggle password visibility
            $('.toggle-password').click(function() {
                const passwordInput = $(this).closest('.input-group').find('input');
                const icon = $(this).find('i');
                
                if (passwordInput.attr('type') === 'password') {
                    passwordInput.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    passwordInput.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });
            
            // Delete modal functionality
            $('#deleteModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                var name = button.data('name');
                
                $(this).find('#deleteUserName').text(name);
                $(this).find('#confirmDelete').attr('href', '?delete=' + id);
            });
            
            // Edit user modal functionality
            $('.edit-user-btn').click(function() {
                var id = $(this).data('id');
                var name = $(this).data('name');
                var email = $(this).data('email');
                var role = $(this).data('role');
                var telegram = $(this).data('telegram');
                
                $('#edit_id').val(id);
                $('#edit_name').val(name);
                $('#edit_email').val(email);
                $('#edit_password').val('');
                $('#edit_role').val(role);
                $('#edit_telegram').val(telegram);
                
                $('#editUserModal').modal('show');
            });
            
            // Animate stats numbers on page load
            $('.stats-number').each(function() {
                const $this = $(this);
                const finalValue = parseInt($this.text());
                
                $({ countNum: 0 }).animate({ countNum: finalValue }, {
                    duration: 1000,
                    easing: 'swing',
                    step: function() {
                        $this.text(Math.floor(this.countNum));
                    },
                    complete: function() {
                        $this.text(finalValue);
                    }
                });
            });
            
            // Auto-hide alerts after 5 seconds
            $('.alert').delay(5000).fadeOut('slow');
            
            // Form validation
            $('form').on('submit', function(e) {
                let isValid = true;
                const form = $(this);
                
                // Check required fields
                form.find('input[required], select[required]').each(function() {
                    if ($(this).val().trim() === '') {
                        isValid = false;
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });
                
                // Email validation
                const emailInputs = form.find('input[type="email"]');
                emailInputs.each(function() {
                    const email = $(this).val();
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    
                    if (email && !emailRegex.test(email)) {
                        isValid = false;
                        $(this).addClass('is-invalid');
                    }
                });
                
                // Password validation for add user form
                if (form.find('input[name="add_user"]').length > 0) {
                    const password = form.find('input[name="password"]').val();
                    if (password && password.length < 3) {
                        isValid = false;
                        form.find('input[name="password"]').addClass('is-invalid');
                        alert('Password minimal 3 karakter!');
                    }
                }
                
                // Telegram Chat ID validation (optional but if filled, must be numeric)
                const telegramInputs = form.find('input[name="chat_id_telegram"]');
                telegramInputs.each(function() {
                    const chatId = $(this).val().trim();
                    if (chatId && !/^\d+$/.test(chatId)) {
                        isValid = false;
                        $(this).addClass('is-invalid');
                        alert('Chat ID Telegram harus berupa angka!');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    return false;
                }
            });
            
            // Remove invalid class on input change
            $('input, select').on('change keyup', function() {
                $(this).removeClass('is-invalid');
            });
            
            // Responsive table handling
            function handleResponsiveLayout() {
                const windowWidth = $(window).width();
                if (windowWidth < 768) {
                    $('.table-responsive').hide();
                    $('.user-cards').show();
                } else {
                    $('.table-responsive').show();
                    $('.user-cards').hide();
                }
            }
            
            // Call on load and resize
            handleResponsiveLayout();
            $(window).resize(handleResponsiveLayout);
            
            // Smooth scroll for better UX
            $('html').css('scroll-behavior', 'smooth');
        });
        
        // Utility function to show alerts
        function showAlert(message, type) {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `;
            
            $('.main-content').prepend(alertHtml);
            
            // Auto-hide after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        }
    </script>
</body>
</html>
