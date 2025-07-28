<?php
session_start();
error_reporting(0);

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'operator') {
    header("Location: ../../index.php");
    exit();
}

require_once('../../config/db.php');
require_once('../../config/telegram.php');

$operator_id   = $_SESSION['user_id'];
$message       = '';
$message_type  = '';

// ─────────────────────────────────────────────────────────────
// PROSES FORM SUBMISSION
// ─────────────────────────────────────────────────────────────
if (isset($_POST['submit_request'])) {

    $alat_id        = mysqli_real_escape_string($conn, $_POST['id_alat']   ?? '');
    $nomor_seri_id  = mysqli_real_escape_string($conn, $_POST['id_nomor']  ?? '');
    $hourmeter      = mysqli_real_escape_string($conn, $_POST['hourmeter'] ?? '');
    $tanda_tangan   = mysqli_real_escape_string($conn, $_POST['tanda_tangan'] ?? '');

    $errors = [];

    /* ── Validasi input ────────────────────────────────────── */
    if (empty($alat_id) || empty($nomor_seri_id) || empty($hourmeter) || empty($tanda_tangan)) {
        $errors[] = "Semua field wajib diisi.";
    }
    if (!empty($hourmeter) && !is_numeric($hourmeter)) {
        $errors[] = "Hourmeter harus berupa angka.";
    }

    /* ── Proses upload gambar ──────────────────────────────── */
    $target_dir = "../../uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $foto_hourmeter      = '';
    $foto_indikator_bbm  = '';
    $allowed_ext         = ['jpg', 'jpeg', 'png'];
    $max_size            = 5 * 1024 * 1024;   // 5 MB

    // Foto Hourmeter
    if (isset($_FILES['foto_hourmeter']) && $_FILES['foto_hourmeter']['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES['foto_hourmeter']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed_ext) && $_FILES['foto_hourmeter']['size'] <= $max_size) {
            $foto_hourmeter = 'hourmeter_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            move_uploaded_file($_FILES['foto_hourmeter']['tmp_name'], $target_dir . $foto_hourmeter);
        } else {
            $errors[] = "Foto hourmeter tidak valid (max 5 MB, format: JPG/PNG).";
        }
    }

    // Foto Indikator BBM
    if (isset($_FILES['foto_indikator_bbm']) && $_FILES['foto_indikator_bbm']['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES['foto_indikator_bbm']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed_ext) && $_FILES['foto_indikator_bbm']['size'] <= $max_size) {
            $foto_indikator_bbm = 'indikator_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            move_uploaded_file($_FILES['foto_indikator_bbm']['tmp_name'], $target_dir . $foto_indikator_bbm);
        } else {
            $errors[] = "Foto indikator BBM tidak valid (max 5 MB, format: JPG/PNG).";
        }
    }

    /* ── Simpan ke database jika valid ─────────────────────── */
    if (empty($errors)) {

        // ↓↓↓  disesuaikan dgn struktur tpk.sql  ↓↓↓
        $insert = "INSERT INTO request_pengisian
                   (id_user, id_alat, id_nomor, hourmeter,
                    foto_hourmeter, foto_indikator_bbm, tanda_tangan, created_at)
                   VALUES
                   ('$operator_id', '$alat_id', '$nomor_seri_id', '$hourmeter',
                    '$foto_hourmeter', '$foto_indikator_bbm', '$tanda_tangan', NOW())";

        if (mysqli_query($conn, $insert)) {

            $request_id = mysqli_insert_id($conn);

            // Ambil data alat & nomor seri untuk notifikasi
            $alat_q  = "SELECT a.nama_alat, ns.nomor_seri
                        FROM alat a
                        JOIN nomor_seri ns ON ns.id_alat = a.id_alat
                        WHERE a.id_alat = '$alat_id' AND ns.id_nomor = '$nomor_seri_id'";
            $alat_d  = mysqli_fetch_assoc(mysqli_query($conn, $alat_q));

            /* ── Notifikasi Telegram ke admin ───────────────── */
            $pesan  = "🔔 <b>Request Pengisian BBM Baru</b>\n\n";
            $pesan .= "📋 <b>Request ID:</b> #$request_id\n";
            $pesan .= "👤 <b>Operator:</b> " . $_SESSION['name'] . "\n";
            $pesan .= "🚛 <b>Alat:</b> " . $alat_d['nama_alat'] . "\n";
            $pesan .= "🔢 <b>No. Seri:</b> " . $alat_d['nomor_seri'] . "\n";
            $pesan .= "⏱️ <b>Hourmeter:</b> " . number_format($hourmeter, 0, ',', '.') . " Hours\n";
            $pesan .= "📅 <b>Tanggal Request:</b> " . date('d/m/Y H:i:s') . "\n\n";
            $pesan .= "⚠️ <i>Silakan verifikasi request ini di dashboard admin.</i>";

            $admin_q = "SELECT chat_id_telegram FROM users
                        WHERE role = 'admin' AND chat_id_telegram IS NOT NULL";
            $admin_r = mysqli_query($conn, $admin_q);
            while ($admin = mysqli_fetch_assoc($admin_r)) {
                sendTelegramMessage($admin['chat_id_telegram'], $pesan);
            }

            $message      = "Request berhasil dikirim!";
            $message_type = "success";
        } else {
            $message      = "Gagal menyimpan request: " . mysqli_error($conn);
            $message_type = "danger";
        }
    } else {
        $message      = implode("<br>", $errors);
        $message_type = "danger";
    }
}

/* ─────────────────────────────────────────────────────────────
   DATA UNTUK FORM
   ──────────────────────────────────────────────────────────── */
$alat_result = mysqli_query($conn,
                "SELECT * FROM alat
                 WHERE status = 'aktif'
                 ORDER BY nama_alat");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Pengisian BBM - Operator Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #dbeafe;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
            --white: #ffffff;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            --radius: 0.75rem;
            --radius-lg: 1rem;
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 80px;
            --topbar-height: 80px;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);
            color: var(--gray-800);
            line-height: 1.6;
            font-size: 14px;
            overflow-x: hidden;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--topbar-height);
            padding: 2rem;
            min-height: calc(100vh - var(--topbar-height));
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .sidebar.collapsed ~ .main-content {
            margin-left: var(--sidebar-collapsed-width);
        }
        
        .page-header {
            background: var(--white);
            padding: 2rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            border: 1px solid var(--gray-200);
        }
        
        .page-header h1 {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 0;
        }
        
        .page-header h1 i {
            color: var(--primary-color);
            font-size: 1.75rem;
        }
        
        .card {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--gray-200);
            overflow: hidden;
            animation: fadeInUp 0.6s ease-out;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: var(--white);
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .card-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(30px, -30px);
        }
        
        .card-header h5 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            position: relative;
            z-index: 1;
        }
        
        .card-body {
            padding: 2.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            font-weight: 500;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }
        
        .text-danger {
            color: var(--danger-color) !important;
            margin-left: 0.25rem;
        }
        
        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--gray-300);
            border-radius: var(--radius);
            background: var(--white);
            color: var(--gray-800);
            font-size: 0.875rem;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            font-family: inherit;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px var(--primary-light);
            transform: translateY(-1px);
        }
        
        .form-control:disabled,
        .form-control[readonly] {
            background: var(--gray-100);
            color: var(--gray-500);
            cursor: not-allowed;
        }
        
        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.75rem center;
            background-repeat: no-repeat;
            background-size: 1rem;
            padding-right: 2.5rem;
        }
        
        .text-muted {
            font-size: 0.75rem;
            color: var(--gray-500);
            margin-top: 0.25rem;
        }
        
        .signature-container {
            background: var(--gray-50);
            border: 2px dashed var(--gray-300);
            border-radius: var(--radius);
            padding: 1.5rem;
            text-align: center;
            transition: all 0.2s ease;
        }
        
        .signature-container:hover {
            border-color: var(--primary-color);
            background: var(--primary-light);
        }
        
        .signature-pad {
            border: 2px solid var(--gray-300);
            border-radius: var(--radius);
            background: var(--white);
            width: 100%;
            height: 200px;
            cursor: crosshair;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 1.5rem;
            border: none;
            border-radius: var(--radius);
            font-weight: 500;
            font-size: 0.875rem;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            font-family: inherit;
            position: relative;
            overflow: hidden;
        }
        
        .btn:before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn:hover:before {
            left: 100%;
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
            color: var(--white);
            box-shadow: var(--shadow);
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            color: var(--white);
        }
        
        .btn-secondary {
            background: var(--gray-200);
            color: var(--gray-700);
            border: 1px solid var(--gray-300);
        }
        
        .btn-secondary:hover {
            background: var(--gray-300);
            transform: translateY(-1px);
            color: var(--gray-700);
        }
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
        }
        
        .alert {
            padding: 1rem 1.25rem;
            border-radius: var(--radius);
            border: 1px solid transparent;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideInDown 0.5s ease;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            border-color: #86efac;
            color: #166534;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border-color: #fca5a5;
            color: #991b1b;
        }
        
        .row {
            display: flex;
            flex-wrap: wrap;
            margin-left: -15px;
            margin-right: -15px;
        }
        
        .col-md-6 {
            flex: 0 0 50%;
            max-width: 50%;
            padding-left: 15px;
            padding-right: 15px;
        }
        
        .mt-2 {
            margin-top: 0.5rem !important;
        }
        
        .mt-4 {
            margin-top: 2rem !important;
        }
        
        .mr-2 {
            margin-right: 0.5rem !important;
        }
        
        @media (max-width: 992px) {
            .main-content {
                margin-left: var(--sidebar-collapsed-width);
                padding: 1.5rem;
            }
            
            .sidebar.expanded ~ .main-content {
                margin-left: var(--sidebar-width);
            }
        }
        
        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }
            
            .page-header {
                padding: 1.5rem;
            }
            
            .page-header h1 {
                font-size: 1.5rem;
            }
            
            .card-header,
            .card-body {
                padding: 1.5rem;
            }
            
            .signature-pad {
                height: 150px;
            }
            
            .col-md-6 {
                flex: 0 0 100%;
                max-width: 100%;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
                margin-bottom: 10px;
            }
        }
        
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
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
    showSidebar('operator');
    showTopbar('operator');
?>
<div class="main-content">
    <div class="page-header mb-4">
        <h1><i class="fas fa-plus-circle mr-2"></i>Request Pengisian BBM</h1>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?> mr-2"></i>
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-clipboard-list mr-2"></i>Form Request Pengisian BBM</h5>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data" id="requestForm">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nama Operator</label>
                            <input type="text" class="form-control"
                                   value="<?php echo htmlspecialchars($_SESSION['name']); ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Alat <span class="text-danger">*</span></label>
                            <select class="form-control" id="id_alat" name="id_alat" required>
                                <option value="">Pilih Alat</option>
                                <?php while ($alat = mysqli_fetch_assoc($alat_result)): ?>
                                    <option value="<?php echo $alat['id_alat']; ?>">
                                        <?php echo htmlspecialchars($alat['nama_alat']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>No. Seri <span class="text-danger">*</span></label>
                                <select class="form-control" id="id_nomor" name="id_nomor" required>
                                    <option value="">Pilih No. Seri</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Hourmeter <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="hourmeter" name="hourmeter" placeholder="Masukkan hourmeter" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Foto Hourmeter <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="foto_hourmeter" name="foto_hourmeter" accept="image/*" required>
                                <small class="text-muted">Format: JPG, PNG. Maksimal 5MB</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Foto Indikator BBM <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="foto_indikator_bbm" name="foto_indikator_bbm" accept="image/*" required>
                                <small class="text-muted">Format: JPG, PNG. Maksimal 5MB</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Tanda Tangan Digital <span class="text-danger">*</span></label>
                        <div class="signature-container">
                            <canvas id="signature-pad" class="signature-pad"></canvas>
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-secondary" id="clear-signature">
                                    <i class="fas fa-eraser"></i> Hapus
                                </button>
                            </div>
                        </div>
                        <input type="hidden" id="tanda_tangan" name="tanda_tangan">
                    </div>

                    <div class="form-group mt-4">
                        <button type="submit" name="submit_request" class="btn btn-success mr-2">
                            <i class="fas fa-paper-plane"></i> Kirim Request
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Signature Pad
            var canvas = document.getElementById('signature-pad');
            var signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgba(255, 255, 255, 0)',
                penColor: 'rgb(0, 0, 0)'
            });

            // Resize canvas
            function resizeCanvas() {
                var ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext("2d").scale(ratio, ratio);
                signaturePad.clear();
            }

            window.addEventListener("resize", resizeCanvas);
            resizeCanvas();

            document.getElementById('clear-signature').addEventListener('click', function () {
                signaturePad.clear();
            });

            // Load nomor seri
            $('#id_alat').change(function() {
                var alat_id = $(this).val();
                if (alat_id) {
                    $.post('get_nomor_seri.php', {id_alat: alat_id}, function(data) {
                        $('#id_nomor').html(data);
                    });
                } else {
                    $('#id_nomor').html('<option value="">Pilih No. Seri</option>');
                }
            });

            // Form submit
            $('#requestForm').submit(function(e) {
                if (signaturePad.isEmpty()) {
                    alert('Tanda tangan harus diisi!');
                    e.preventDefault();
                    return false;
                }
                
                var dataURL = signaturePad.toDataURL();
                $('#tanda_tangan').val(dataURL);
            });

            // Auto hide alerts
            $('.alert').delay(5000).fadeOut('slow');
        });
    </script>
</body>
</html>
