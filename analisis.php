<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../index.php");
    exit();
}

require_once('../../config/db.php');

// Ambil data untuk analisis
$current_year = date('Y');
$current_month = date('m');

// Total konsumsi BBM keseluruhan
$total_konsumsi_query = "SELECT SUM(jumlah_liter_diisi) as total FROM pengisian_bbm";
$total_konsumsi_result = mysqli_query($conn, $total_konsumsi_query);
$total_konsumsi = mysqli_fetch_assoc($total_konsumsi_result)['total'] ?? 0;

// Total stok BBM saat ini
$total_stok_query = "SELECT total_liter FROM total_stok_bbm WHERE id_total = 1";
$total_stok_result = mysqli_query($conn, $total_stok_query);
$total_stok = mysqli_fetch_assoc($total_stok_result)['total_liter'] ?? 0;

// Konsumsi bulan ini
$konsumsi_bulan_ini_query = "SELECT SUM(jumlah_liter_diisi) as total 
                              FROM pengisian_bbm 
                              WHERE YEAR(created_at) = '$current_year' 
                              AND MONTH(created_at) = '$current_month'";
$konsumsi_bulan_ini_result = mysqli_query($conn, $konsumsi_bulan_ini_query);
$konsumsi_bulan_ini = mysqli_fetch_assoc($konsumsi_bulan_ini_result)['total'] ?? 0;

// Rata-rata konsumsi per hari
$rata_rata_query = "SELECT AVG(daily_consumption) as rata_rata FROM (
                        SELECT DATE(created_at) as tanggal, SUM(jumlah_liter_diisi) as daily_consumption 
                        FROM pengisian_bbm 
                        GROUP BY DATE(created_at)
                    ) as daily_data";
$rata_rata_result = mysqli_query($conn, $rata_rata_query);
$rata_rata_konsumsi = mysqli_fetch_assoc($rata_rata_result)['rata_rata'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analisis BBM - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <style>
        :root {
            /* Corporate Color Palette */
            --primary-blue: #1e3a8a;
            --primary-blue-light: #3b82f6;
            --primary-blue-dark: #1e40af;
            --secondary-blue: #0ea5e9;
            --accent-orange: #f97316;
            --accent-green: #10b981;
            --accent-red: #ef4444;
            --accent-yellow: #f59e0b;
            
            /* Neutral Colors */
            --white: #ffffff;
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
            
            /* Corporate Gradients */
            --gradient-primary: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-light) 100%);
            --gradient-secondary: linear-gradient(135deg, var(--secondary-blue) 0%, var(--primary-blue-light) 100%);
            --gradient-success: linear-gradient(135deg, var(--accent-green) 0%, #059669 100%);
            --gradient-warning: linear-gradient(135deg, var(--accent-yellow) 0%, #d97706 100%);
            --gradient-danger: linear-gradient(135deg, var(--accent-red) 0%, #dc2626 100%);
            
            /* Layout Variables */
            --sidebar-width: 280px;
            --sidebar-collapsed: 80px;
            --topbar-height: 80px;
            --border-radius: 12px;
            --border-radius-lg: 16px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--gray-50);
            color: var(--gray-900);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Corporate Background Pattern */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 25% 25%, rgba(30, 58, 138, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(59, 130, 246, 0.05) 0%, transparent 50%);
            z-index: -1;
            pointer-events: none;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--topbar-height);
            padding: 2rem;
            min-height: calc(100vh - var(--topbar-height));
            transition: var(--transition);
        }

        .sidebar.collapsed ~ .main-content {
            margin-left: var(--sidebar-collapsed);
        }

        /* Corporate Header */
        .corporate-header {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow);
            padding: 2rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .corporate-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--gradient-primary);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .header-title {
            font-family: 'Poppins', sans-serif;
            font-size: 2.25rem;
            font-weight: 700;
            color: var(--gray-900);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-title i {
            color: var(--primary-blue);
            font-size: 2rem;
        }

        .header-subtitle {
            color: var(--gray-600);
            font-size: 1rem;
            margin-top: 0.5rem;
            font-weight: 400;
        }

        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--border-radius-lg);
            padding: 2rem;
            position: relative;
            overflow: hidden;
            transition: var(--transition);
            box-shadow: var(--shadow);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-xl);
            border-color: var(--gray-300);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            transition: var(--transition);
        }

        .stat-card.primary::before { background: var(--gradient-primary); }
        .stat-card.success::before { background: var(--gradient-success); }
        .stat-card.warning::before { background: var(--gradient-warning); }
        .stat-card.danger::before { background: var(--gradient-danger); }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--white);
            transition: var(--transition);
        }

        .stat-card.primary .stat-icon { background: var(--gradient-primary); }
        .stat-card.success .stat-icon { background: var(--gradient-success); }
        .stat-card.warning .stat-icon { background: var(--gradient-warning); }
        .stat-card.danger .stat-icon { background: var(--gradient-danger); }

        .stat-content {
            flex: 1;
        }

        .stat-number {
            font-family: 'Poppins', sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--gray-900);
            line-height: 1;
            margin-bottom: 0.5rem;
            transition: var(--transition);
        }

        .stat-label {
            color: var(--gray-600);
            font-size: 0.875rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Analysis Cards */
        .analysis-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .analysis-card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: var(--transition);
        }

        .analysis-card:hover {
            box-shadow: var(--shadow-lg);
        }

        .analysis-header {
            background: var(--gray-50);
            border-bottom: 1px solid var(--gray-200);
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .analysis-title {
            font-family: 'Poppins', sans-serif;
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-900);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .analysis-title i {
            color: var(--primary-blue);
        }

        .analysis-body {
            padding: 2rem;
        }

        .chart-container {
            position: relative;
            height: 400px;
            width: 100%;
        }

        /* Filter Controls */
        .filter-controls {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }

        .filter-btn {
            padding: 0.5rem 1rem;
            border: 1px solid var(--gray-300);
            background: var(--white);
            color: var(--gray-700);
            border-radius: var(--border-radius);
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }

        .filter-btn:hover {
            border-color: var(--primary-blue);
            color: var(--primary-blue);
        }

        .filter-btn.active {
            background: var(--primary-blue);
            color: var(--white);
            border-color: var(--primary-blue);
        }

        /* Table Styles */
        .analysis-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .analysis-table th,
        .analysis-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }

        .analysis-table th {
            background: var(--gray-50);
            font-weight: 600;
            color: var(--gray-700);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .analysis-table tbody tr:hover {
            background: var(--gray-50);
        }

        /* Efficiency Indicators */
        .efficiency-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .efficiency-high {
            background: rgba(16, 185, 129, 0.1);
            color: var(--accent-green);
        }

        .efficiency-medium {
            background: rgba(245, 158, 11, 0.1);
            color: var(--accent-yellow);
        }

        .efficiency-low {
            background: rgba(239, 68, 68, 0.1);
            color: var(--accent-red);
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .analysis-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .header-title {
                font-size: 1.875rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .stat-number {
                font-size: 2rem;
            }

            .chart-container {
                height: 300px;
            }

            .analysis-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        /* Animation Classes */
        .fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
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

        .section-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--gray-200), transparent);
            margin: 2rem 0;
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
        <!-- Corporate Header -->
        <div class="corporate-header">
            <div class="header-content">
                <div>
                    <h1 class="header-title">
                        <i class="fas fa-chart-bar"></i>
                        Analisis Penggunaan BBM
                    </h1>
                    <p class="header-subtitle">Analisis Komprehensif Konsumsi Bahan Bakar Minyak</p>
                </div>
            </div>
        </div>

        <!-- Statistics Overview -->
        <div class="stats-grid">
            <div class="stat-card primary fade-in-up">
                <div class="stat-header">
                    <div class="stat-content">
                        <div class="stat-number"><?php echo number_format($total_konsumsi, 1); ?></div>
                        <div class="stat-label">Total Konsumsi BBM (L)</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-gas-pump"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card success fade-in-up" style="animation-delay: 0.1s;">
                <div class="stat-header">
                    <div class="stat-content">
                        <div class="stat-number"><?php echo number_format($total_stok, 1); ?></div>
                        <div class="stat-label">Stok BBM Tersisa (L)</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-tint"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card warning fade-in-up" style="animation-delay: 0.2s;">
                <div class="stat-header">
                    <div class="stat-content">
                        <div class="stat-number"><?php echo number_format($konsumsi_bulan_ini, 1); ?></div>
                        <div class="stat-label">Konsumsi Bulan Ini (L)</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-calendar-month"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card danger fade-in-up" style="animation-delay: 0.3s;">
                <div class="stat-header">
                    <div class="stat-content">
                        <div class="stat-number"><?php echo number_format($rata_rata_konsumsi, 1); ?></div>
                        <div class="stat-label">Rata-rata per Hari (L)</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-divider"></div>

        <!-- Analysis Charts -->
        <div class="analysis-grid">
            <!-- Konsumsi BBM per Alat -->
            <div class="analysis-card fade-in-up" style="animation-delay: 0.4s;">
                <div class="analysis-header">
                    <h5 class="analysis-title">
                        <i class="fas fa-chart-pie"></i>
                        Konsumsi BBM per Alat
                    </h5>
                    <div class="filter-controls">
                        <button class="filter-btn active" onclick="updateAlatChart('all')">Semua</button>
                        <button class="filter-btn" onclick="updateAlatChart('month')">Bulan Ini</button>
                        <button class="filter-btn" onclick="updateAlatChart('year')">Tahun Ini</button>
                    </div>
                </div>
                <div class="analysis-body">
                    <div class="chart-container">
                        <canvas id="alatChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Trend Konsumsi Bulanan -->
            <div class="analysis-card fade-in-up" style="animation-delay: 0.5s;">
                <div class="analysis-header">
                    <h5 class="analysis-title">
                        <i class="fas fa-chart-line"></i>
                        Trend Konsumsi Bulanan
                    </h5>
                </div>
                <div class="analysis-body">
                    <div class="chart-container">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Analysis Table -->
        <div class="analysis-card fade-in-up" style="animation-delay: 0.6s;">
            <div class="analysis-header">
                <h5 class="analysis-title">
                    <i class="fas fa-table"></i>
                    Analisis Detail per Alat
                </h5>
            </div>
            <div class="analysis-body">
                <table class="analysis-table">
                    <thead>
                        <tr>
                            <th>Nama Alat</th>
                            <th>Total Konsumsi (L)</th>
                            <th>Frekuensi Pengisian</th>
                            <th>Rata-rata per Pengisian (L)</th>
                            <th>Efisiensi</th>
                            <th>Persentase dari Total</th>
                        </tr>
                    </thead>
                    <tbody id="analysisTableBody">
                        <!-- Data akan dimuat via JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Global variables untuk charts
        let alatChart, trendChart;
        
        // Corporate color scheme
        const corporateColors = {
            primary: '#1e3a8a',
            primaryLight: '#3b82f6',
            secondary: '#0ea5e9',
            success: '#10b981',
            warning: '#f59e0b',
            danger: '#ef4444',
            gray: '#64748b'
        };

        // Inisialisasi saat halaman dimuat
        $(document).ready(function() {
            initializeCharts();
            loadAnalysisData();
        });

        // Inisialisasi charts
        function initializeCharts() {
            initAlatChart();
            initTrendChart();
        }

        // Chart konsumsi per alat
        function initAlatChart() {
            const ctx = document.getElementById('alatChart').getContext('2d');
            
            alatChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        backgroundColor: [
                            corporateColors.primary,
                            corporateColors.primaryLight,
                            corporateColors.secondary,
                            corporateColors.success,
                            corporateColors.warning,
                            corporateColors.danger,
                            '#8b5cf6',
                            '#06b6d4'
                        ],
                        borderWidth: 0,
                        hoverBorderWidth: 3,
                        hoverBorderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                usePointStyle: true,
                                font: {
                                    size: 12,
                                    family: 'Inter'
                                },
                                color: corporateColors.gray
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.9)',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            borderColor: corporateColors.primary,
                            borderWidth: 1,
                            cornerRadius: 8,
                            titleFont: {
                                family: 'Poppins',
                                size: 14,
                                weight: '600'
                            },
                            bodyFont: {
                                family: 'Inter',
                                size: 13
                            },
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    return `${context.label}: ${context.parsed.toFixed(1)}L (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '60%',
                    animation: {
                        animateRotate: true,
                        duration: 1500
                    }
                }
            });

            // Load data awal
            updateAlatChart('all');
        }

        // Chart trend konsumsi
        function initTrendChart() {
            const ctx = document.getElementById('trendChart').getContext('2d');
            
            trendChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Konsumsi BBM (Liter)',
                        data: [],
                        borderColor: corporateColors.primary,
                        backgroundColor: corporateColors.primary + '15',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: corporateColors.primary,
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.9)',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            borderColor: corporateColors.primary,
                            borderWidth: 1,
                            cornerRadius: 8,
                            titleFont: {
                                family: 'Poppins',
                                size: 14,
                                weight: '600'
                            },
                            bodyFont: {
                                family: 'Inter',
                                size: 13
                            },
                            callbacks: {
                                label: function(context) {
                                    return `Konsumsi: ${context.parsed.y.toFixed(1)} Liter`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#e2e8f0',
                                drawBorder: false
                            },
                            ticks: {
                                callback: function(value) {
                                    return value + 'L';
                                },
                                color: corporateColors.gray,
                                font: {
                                    family: 'Inter',
                                    size: 12
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: corporateColors.gray,
                                font: {
                                    family: 'Inter',
                                    size: 12
                                }
                            }
                        }
                    },
                    animation: {
                        duration: 1500,
                        easing: 'easeInOutQuart'
                    }
                }
            });

            // Load data trend
            loadTrendData();
        }

        // Update chart konsumsi per alat
        function updateAlatChart(period) {
            // Update active button
            $('.filter-btn').removeClass('active');
            $(`button[onclick="updateAlatChart('${period}')"]`).addClass('active');

            $.ajax({
                url: 'api/analisis_data.php',
                type: 'GET',
                data: { type: 'alat', period: period },
                dataType: 'json',
                success: function(response) {
                    alatChart.data.labels = response.labels;
                    alatChart.data.datasets[0].data = response.data;
                    alatChart.update();
                }
            });
        }

        // Load data trend konsumsi
        function loadTrendData() {
            $.ajax({
                url: 'api/analisis_data.php',
                type: 'GET',
                data: { type: 'trend' },
                dataType: 'json',
                success: function(response) {
                    trendChart.data.labels = response.labels;
                    trendChart.data.datasets[0].data = response.data;
                    trendChart.update();
                }
            });
        }

        // Load data untuk tabel analisis
        function loadAnalysisData() {
            $.ajax({
                url: 'api/analisis_data.php',
                type: 'GET',
                data: { type: 'table' },
                dataType: 'json',
                success: function(response) {
                    let tableBody = $('#analysisTableBody');
                    tableBody.empty();

                    response.forEach(function(item) {
                        let efficiencyClass = 'efficiency-medium';
                        let efficiencyText = 'Sedang';
                        
                        if (item.rata_rata >= 80) {
                            efficiencyClass = 'efficiency-high';
                            efficiencyText = 'Tinggi';
                        } else if (item.rata_rata < 50) {
                            efficiencyClass = 'efficiency-low';
                            efficiencyText = 'Rendah';
                        }

                        let row = `
                            <tr>
                                <td><strong>${item.nama_alat}</strong></td>
                                <td>${parseFloat(item.total_konsumsi).toFixed(1)} L</td>
                                <td>${item.frekuensi} kali</td>
                                <td>${parseFloat(item.rata_rata).toFixed(1)} L</td>
                                <td><span class="efficiency-indicator ${efficiencyClass}">${efficiencyText}</span></td>
                                <td>${parseFloat(item.persentase).toFixed(1)}%</td>
                            </tr>
                        `;
                        tableBody.append(row);
                    });
                }
            });
        }
    </script>
</body>
</html>
