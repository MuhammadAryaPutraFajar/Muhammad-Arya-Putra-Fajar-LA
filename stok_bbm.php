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

// Filter parameters
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$filter_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$filter_applied = !empty($filter_bulan) || $filter_tahun != date('Y');

// Proses tambah stok BBM
if (isset($_POST['add_stok'])) {
    $tanggal = clean_input($_POST['tanggal']);
    $no_pesanan = clean_input($_POST['no_pesanan']);
    $jumlah_liter = clean_input($_POST['jumlah_liter']);
    $admin_id = $_SESSION['user_id'];
    
    if (empty($tanggal) || empty($no_pesanan) || empty($jumlah_liter)) {
        $message = "Semua field harus diisi!";
        $message_type = "danger";
    } else if (!is_numeric($jumlah_liter) || $jumlah_liter <= 0) {
        $message = "Jumlah liter harus berupa angka positif!";
        $message_type = "danger";
    } else {
        // Mulai transaction untuk memastikan konsistensi data
        mysqli_autocommit($conn, FALSE);
        
        try {
            // Insert ke tabel stok_bbm
            $insert_query = "INSERT INTO stok_bbm (tanggal, no_pesanan, jumlah_liter, id_user) 
                            VALUES ('$tanggal', '$no_pesanan', '$jumlah_liter', '$admin_id')";
            
            if (!mysqli_query($conn, $insert_query)) {
                throw new Exception("Error inserting stok_bbm: " . mysqli_error($conn));
            }
            
            // Cek apakah record total_stok_bbm sudah ada
            $check_total = "SELECT id_total FROM total_stok_bbm WHERE id_total = 1";
            $check_result = mysqli_query($conn, $check_total);
            
            if (mysqli_num_rows($check_result) == 0) {
                // Jika belum ada, buat record baru
                $create_total = "INSERT INTO total_stok_bbm (id_total, total_liter) VALUES (1, $jumlah_liter)";
                if (!mysqli_query($conn, $create_total)) {
                    throw new Exception("Error creating total_stok_bbm: " . mysqli_error($conn));
                }
            } else {
                // Update total stok yang sudah ada
                $update_total = "UPDATE total_stok_bbm SET total_liter = total_liter + $jumlah_liter WHERE id_total = 1";
                if (!mysqli_query($conn, $update_total)) {
                    throw new Exception("Error updating total_stok_bbm: " . mysqli_error($conn));
                }
            }
            
            // Commit transaction jika semua berhasil
            mysqli_commit($conn);
            
            $message = "Stok BBM berhasil ditambahkan!";
            $message_type = "success";
            
            // Kirim notifikasi ke supervisor
            $supervisor_query = "SELECT id_user FROM users WHERE role = 'supervisor'";
            $supervisor_result = mysqli_query($conn, $supervisor_query);
            while ($supervisor = mysqli_fetch_assoc($supervisor_result)) {
                $notif_message = "📋 <b>Stok BBM Ditambahkan</b>\n\n";
                $notif_message .= "Tanggal: $tanggal\n";
                $notif_message .= "No. Pesanan: $no_pesanan\n";
                $notif_message .= "Jumlah: " . number_format($jumlah_liter, 2) . " Liter\n";
                $notif_message .= "Admin: " . $_SESSION['name'];
                
                notifyUser($supervisor['id_user'], $notif_message);
            }
            
        } catch (Exception $e) {
            // Rollback jika ada error
            mysqli_rollback($conn);
            $message = "Error: " . $e->getMessage();
            $message_type = "danger";
        }
        
        // Kembalikan autocommit ke true
        mysqli_autocommit($conn, TRUE);
    }
}

// Build where clause untuk filter
$where_clause_stok = "WHERE 1=1";
$where_clause_pengisian = "WHERE 1=1";

if (!empty($filter_bulan)) {
    $where_clause_stok .= " AND MONTH(tanggal) = '$filter_bulan'";
    $where_clause_pengisian .= " AND MONTH(created_at) = '$filter_bulan'";
}
if (!empty($filter_tahun)) {
    $where_clause_stok .= " AND YEAR(tanggal) = '$filter_tahun'";
    $where_clause_pengisian .= " AND YEAR(created_at) = '$filter_tahun'";
}

// Ambil data stok BBM dengan filter
$stok_query = "SELECT sb.*, u.name as admin_name 
               FROM stok_bbm sb 
               JOIN users u ON sb.id_user = u.id_user 
               $where_clause_stok
               ORDER BY sb.created_at DESC";
$stok_result = mysqli_query($conn, $stok_query);

// Ambil total stok saat ini (tidak terfilter)
$total_stok_query = "SELECT total_liter FROM total_stok_bbm WHERE id_total = 1";
$total_stok_result = mysqli_query($conn, $total_stok_query);

if ($total_stok_result && mysqli_num_rows($total_stok_result) > 0) {
    $total_stok_saat_ini = mysqli_fetch_assoc($total_stok_result)['total_liter'];
} else {
    // Jika tidak ada record, hitung dari stok_bbm
    $calculate_total = "SELECT SUM(jumlah_liter) as total FROM stok_bbm";
    $calculate_result = mysqli_query($conn, $calculate_total);
    $total_stok_saat_ini = mysqli_fetch_assoc($calculate_result)['total'] ?? 0;
    
    // Buat record baru di total_stok_bbm
    if ($total_stok_saat_ini > 0) {
        $create_total = "INSERT INTO total_stok_bbm (id_total, total_liter) VALUES (1, $total_stok_saat_ini)";
        mysqli_query($conn, $create_total);
    }
}

// Statistik berdasarkan filter
// Total stok masuk periode
$stok_masuk_periode = mysqli_query($conn, "SELECT COALESCE(SUM(jumlah_liter), 0) as total FROM stok_bbm $where_clause_stok");
$stok_masuk_count = mysqli_fetch_assoc($stok_masuk_periode)['total'];

// Total terpakai periode (dari pengisian_bbm)
$total_terpakai_periode = mysqli_query($conn, "SELECT COALESCE(SUM(jumlah_liter_diisi), 0) as total FROM pengisian_bbm $where_clause_pengisian");
$total_terpakai_count = mysqli_fetch_assoc($total_terpakai_periode)['total'];

// Jumlah pengisian periode
$jumlah_pengisian_periode = mysqli_query($conn, "SELECT COUNT(*) as total FROM pengisian_bbm $where_clause_pengisian");
$jumlah_pengisian_count = mysqli_fetch_assoc($jumlah_pengisian_periode)['total'];

// Jumlah penambahan stok periode
$jumlah_penambahan_periode = mysqli_query($conn, "SELECT COUNT(*) as total FROM stok_bbm $where_clause_stok");
$jumlah_penambahan_count = mysqli_fetch_assoc($jumlah_penambahan_periode)['total'];

// Selisih/sisa = stok masuk periode - terpakai periode
$sisa_periode = $stok_masuk_count - $total_terpakai_count;

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
    <title>Kelola Stok BBM - Admin Dashboard</title>
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
        
        /* Alert Styling */
        .alert {
            border-radius: 15px;
            border: none;
            padding: 20px 25px;
            margin-bottom: 25px;
            animation: fadeInDown 0.5s ease-out;
            box-shadow: var(--shadow-md);
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            border-left: 4px solid var(--ipc-success);
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            border-left: 4px solid var(--ipc-danger);
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Stats Cards */
        .stats-row {
            margin-bottom: 30px;
        }
        
        .card {
            border-radius: 20px;
            border: none;
            box-shadow: var(--shadow-lg);
            transition: all 0.3s ease;
            margin-bottom: 25px;
            border: 1px solid #e3e6f0;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
        }
        
        .card-stats {
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .card-stats.primary { 
            background: var(--gradient-primary);
        }
        .card-stats.success { 
            background: var(--gradient-success);
        }
        .card-stats.warning { 
            background: var(--gradient-warning);
        }
        .card-stats.info { 
            background: var(--gradient-info);
        }
        .card-stats.danger {
            background: var(--gradient-danger);
        }
        
        .card-stats .card-body {
            padding: 25px;
            position: relative;
            z-index: 2;
        }
        
        .stats-text {
            font-size: 0.9rem;
            font-weight: 600;
            opacity: 0.9;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stats-number {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 0;
            line-height: 1;
        }
        
        .stats-icon {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 2.5rem;
            opacity: 0.3;
            z-index: 1;
        }
        
        /* Form Styling */
        .card-header {
            background: linear-gradient(135deg, #f8f9fc 0%, #e6f0f9 100%);
            border-bottom: 1px solid #e3e6f0;
            padding: 20px 25px;
            border-radius: 20px 20px 0 0 !important;
        }
        
        .card-header h5 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--ipc-text-dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-body {
            padding: 25px;
        }
        
        .form-group label {
            font-weight: 600;
            color: var(--ipc-text-dark);
            margin-bottom: 8px;
        }
        
        .form-control {
            border-radius: 10px;
            border: 2px solid #e3e6f0;
            padding: 12px 15px;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        .form-control:focus {
            border-color: var(--ipc-primary);
            box-shadow: 0 0 0 3px rgba(0, 91, 170, 0.1);
        }
        
        .btn {
            border-radius: 10px;
            padding: 12px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            box-shadow: var(--shadow-md);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-success {
            background: var(--gradient-success);
            color: white;
        }
        
        /* Table Styling */
        .table-responsive {
            border-radius: 0 0 20px 20px;
            overflow-x: auto;
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
        
        /* Badge styling for liters */
        .liter-badge {
            background: var(--gradient-primary);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
            min-width: 80px;
            text-align: center;
            box-shadow: var(--shadow-sm);
        }
        
        /* Mobile Cards for Table Data */
        .stok-cards {
            display: none;
        }
        
        .stok-card {
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow-md);
            margin-bottom: 20px;
            padding: 20px;
            border: 1px solid #e3e6f0;
            transition: all 0.3s ease;
        }
        
        .stok-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }
        
        .stok-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        
        .stok-card-date {
            font-weight: 700;
            color: var(--ipc-primary);
            font-size: 1.1rem;
        }
        
        .stok-card-liter {
            background: var(--gradient-primary);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .stok-card-info {
            margin-bottom: 10px;
        }
        
        .stok-card-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        .stok-card-label {
            color: #666;
            font-weight: 500;
        }
        
        .stok-card-value {
            color: var(--ipc-text-dark);
            font-weight: 600;
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
            
            .stats-row .col-xl-3 {
                flex: 0 0 100%;
                max-width: 100%;
                margin-bottom: 15px;
            }
            
            .card-stats .card-body {
                padding: 20px;
            }
            
            .stats-number {
                font-size: 1.8rem;
            }
            
            .stats-icon {
                font-size: 2rem;
            }
            
            /* Show mobile cards, hide table */
            .table-responsive {
                display: none;
            }
            
            .stok-cards {
                display: block !important;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 10px;
            }
            
            .stats-number {
                font-size: 1.5rem;
            }
            
            .card-header,
            .card-body {
                padding: 15px;
            }
        }
        
        /* Animation */
        .card {
            animation: fadeInUp 0.6s ease-out;
        }
        
        .card:nth-child(1) { animation-delay: 0.1s; }
        .card:nth-child(2) { animation-delay: 0.2s; }
        .card:nth-child(3) { animation-delay: 0.3s; }
        .card:nth-child(4) { animation-delay: 0.4s; }
        
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
            <h1><i class="fas fa-gas-pump mr-3"></i>Kelola Stok BBM</h1>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        <?php endif; ?>

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
                    <a href="stok_bbm.php" class="btn btn-secondary">
                        <i class="fas fa-undo mr-2"></i>Reset
                    </a>
                </div>
                
                <div class="filter-group">
                    <?php 
                    $export_params = [];
                    if (!empty($filter_bulan)) $export_params[] = "bulan=$filter_bulan";
                    if (!empty($filter_tahun)) $export_params[] = "tahun=$filter_tahun";
                    $export_url = "export_stok.php" . (!empty($export_params) ? '?' . implode('&', $export_params) : '');
                    ?>
                    <a href="<?php echo $export_url; ?>" class="btn btn-success">
                        <i class="fas fa-file-excel mr-2"></i>Export Excel
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

        <!-- Statistics Cards -->
        <div class="row stats-row">
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                <div class="card card-stats primary h-100">
                    <div class="card-body">
                        <div class="stats-text">Total Stok Saat Ini</div>
                        <div class="stats-number"><?php echo number_format($total_stok_saat_ini, 1); ?>L</div>
                        <i class="fas fa-gas-pump stats-icon"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                <div class="card card-stats success h-100">
                    <div class="card-body">
                        <div class="stats-text">Stok Masuk Periode</div>
                        <div class="stats-number"><?php echo number_format($stok_masuk_count, 1); ?>L</div>
                        <i class="fas fa-plus-circle stats-icon"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                <div class="card card-stats warning h-100">
                    <div class="card-body">
                        <div class="stats-text">Total Terpakai</div>
                        <div class="stats-number"><?php echo number_format($total_terpakai_count, 1); ?>L</div>
                        <i class="fas fa-tint stats-icon"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                <div class="card card-stats info h-100">
                    <div class="card-body">
                        <div class="stats-text">Jumlah Pengisian</div>
                        <div class="stats-number"><?php echo $jumlah_pengisian_count; ?></div>
                        <i class="fas fa-check-circle stats-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sisa/Selisih Card -->
        <div class="row mb-4">
            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                <div class="card card-stats <?php echo $sisa_periode >= 0 ? 'success' : 'danger'; ?> h-100">
                    <div class="card-body">
                        <div class="stats-text">Sisa/Selisih Periode</div>
                        <div class="stats-number"><?php echo number_format($sisa_periode, 1); ?>L</div>
                        <i class="fas fa-<?php echo $sisa_periode >= 0 ? 'arrow-up' : 'arrow-down'; ?> stats-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                <div class="card card-stats info h-100">
                    <div class="card-body">
                        <div class="stats-text">Penambahan Stok</div>
                        <div class="stats-number"><?php echo $jumlah_penambahan_count; ?></div>
                        <i class="fas fa-plus stats-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Tambah Stok -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-plus"></i>Tambah Stok BBM</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="tanggal">Tanggal</label>
                                        <input type="date" class="form-control" id="tanggal" name="tanggal" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="no_pesanan">No. Pesanan</label>
                                        <input type="text" class="form-control" id="no_pesanan" name="no_pesanan" placeholder="Masukkan nomor pesanan" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="jumlah_liter">Jumlah Liter</label>
                                        <input type="number" step="0.01" class="form-control" id="jumlah_liter" name="jumlah_liter" placeholder="0.00" required>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" name="add_stok" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>Simpan Stok
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Riwayat Stok -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>
                            <i class="fas fa-history"></i>
                            Riwayat Penambahan Stok
                            <?php if($filter_applied): ?>
                                - <?php echo !empty($filter_bulan) ? $nama_bulan[$filter_bulan] . ' ' : ''; ?><?php echo $filter_tahun; ?>
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($stok_result) > 0): ?>
                            <!-- Desktop Table View -->
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th width="8%">No</th>
                                            <th width="15%">Tanggal</th>
                                            <th width="20%">No. Pesanan</th>
                                            <th width="15%">Jumlah Liter</th>
                                            <th width="20%">Admin</th>
                                            <th width="22%">Waktu Input</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $no = 1;
                                        mysqli_data_seek($stok_result, 0);
                                        while ($stok = mysqli_fetch_assoc($stok_result)): 
                                        ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($stok['tanggal'])); ?></td>
                                                <td><?php echo htmlspecialchars($stok['no_pesanan']); ?></td>
                                                <td>
                                                    <span class="liter-badge">
                                                        <?php echo number_format($stok['jumlah_liter'], 2); ?> L
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($stok['admin_name']); ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($stok['created_at'])); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Mobile Card View -->
                            <div class="stok-cards">
                                <?php 
                                mysqli_data_seek($stok_result, 0);
                                while ($stok = mysqli_fetch_assoc($stok_result)): 
                                ?>
                                    <div class="stok-card">
                                        <div class="stok-card-header">
                                            <div class="stok-card-date">
                                                <i class="fas fa-calendar-alt mr-2"></i>
                                                <?php echo date('d/m/Y', strtotime($stok['tanggal'])); ?>
                                            </div>
                                            <div class="stok-card-liter">
                                                <?php echo number_format($stok['jumlah_liter'], 2); ?> L
                                            </div>
                                        </div>
                                        <div class="stok-card-info">
                                            <div class="stok-card-row">
                                                <span class="stok-card-label">No. Pesanan:</span>
                                                <span class="stok-card-value"><?php echo htmlspecialchars($stok['no_pesanan']); ?></span>
                                            </div>
                                            <div class="stok-card-row">
                                                <span class="stok-card-label">Admin:</span>
                                                <span class="stok-card-value"><?php echo htmlspecialchars($stok['admin_name']); ?></span>
                                            </div>
                                            <div class="stok-card-row">
                                                <span class="stok-card-label">Waktu Input:</span>
                                                <span class="stok-card-value"><?php echo date('d/m/Y H:i', strtotime($stok['created_at'])); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5" style="color: #718096;">
                                <i class="fas fa-inbox fa-4x mb-3" style="opacity: 0.5;"></i>
                                <h5>Tidak ada data</h5>
                                <p>
                                    <?php if($filter_applied): ?>
                                        Tidak ada data stok BBM untuk periode <?php echo !empty($filter_bulan) ? $nama_bulan[$filter_bulan] . ' ' : ''; ?><?php echo $filter_tahun; ?>.
                                    <?php else: ?>
                                        Belum ada data penambahan stok BBM.
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
            // Set tanggal hari ini sebagai default
            document.getElementById('tanggal').value = new Date().toISOString().split('T')[0];
            
            // Animate stats numbers on page load
            $('.stats-number').each(function() {
                const $this = $(this);
                const text = $this.text();
                const finalValue = parseFloat(text.replace(/[^0-9.-]+/g, ""));
                const isDecimal = text.includes('.');
                const suffix = text.replace(/[0-9.-]/g, '');
                
                $({ countNum: 0 }).animate({ countNum: finalValue }, {
                    duration: 1500,
                    easing: 'swing',
                    step: function() {
                        if (isDecimal) {
                            $this.text(this.countNum.toFixed(1) + suffix);
                        } else {
                            $this.text(Math.floor(this.countNum) + suffix);
                        }
                    },
                    complete: function() {
                        if (isDecimal) {
                            $this.text(finalValue.toFixed(1) + suffix);
                        } else {
                            $this.text(finalValue + suffix);
                        }
                    }
                });
            });
            
            // Responsive layout handling
            function handleResponsiveLayout() {
                const windowWidth = $(window).width();
                if (windowWidth < 768) {
                    $('.table-responsive').hide();
                    $('.stok-cards').show();
                } else {
                    $('.table-responsive').show();
                    $('.stok-cards').hide();
                }
            }
            
            // Call on load and resize
            handleResponsiveLayout();
            $(window).resize(handleResponsiveLayout);
        });
    </script>
</body>
</html>
