<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMOB - PT IPC Terminal Petikemas</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #005baa;
            --secondary-color: #ff8200;
            --accent-color: #00a651;
            --dark-blue: #003366;
            --light-gray: #f8f9fa;
            --medium-gray: #6c757d;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: #333;
            overflow-x: hidden;
        }

        /* Loading Splashscreen Styles */
        .loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--dark-blue) 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease-out;
        }

        .loading-screen.fade-out {
            opacity: 0;
            pointer-events: none;
        }

        .loading-logo {
            width: 120px;
            height: 120px;
            margin-bottom: 2rem;
            animation: pulse 2s infinite;
        }

        .loading-text {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 2rem;
            text-align: center;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid var(--secondary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Navigation Styles */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            z-index: 1050;
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
            font-size: 1.2rem;
        }

        .nav-link {
            font-weight: 500;
            color: var(--dark-blue) !important;
            margin: 0 10px;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
        }

        /* Hero Carousel Section */
        .hero-carousel {
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        .carousel-item {
            min-height: 100vh;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
        }

        /* Background images for carousel items */
        .carousel-item:nth-child(1) {
            background-image: linear-gradient(rgba(0, 91, 170, 0.7), rgba(0, 51, 102, 0.8)), url('images/gambar1.png');
        }

        .carousel-item:nth-child(2) {
            background-image: linear-gradient(rgba(0, 91, 170, 0.7), rgba(0, 51, 102, 0.8)), url('images/gambar2.png');
        }

        .carousel-item:nth-child(3) {
            background-image: linear-gradient(rgba(0, 91, 170, 0.7), rgba(0, 51, 102, 0.8)), url('images/gambar3.png');
        }

        /* Fallback gradient if images don't load */
        .carousel-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--dark-blue) 100%);
            z-index: -1;
        }

        .carousel-item::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><polygon fill="rgba(255,255,255,0.05)" points="0,0 1000,300 1000,1000 0,700"/></svg>');
            background-size: cover;
            z-index: 1;
        }

        .hero-content-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            z-index: 10;
        }

        .hero-content {
            color: white;
            z-index: 15;
            position: relative;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero-subtitle {
            font-size: 1.25rem;
            font-weight: 300;
            margin-bottom: 2rem;
            opacity: 0.95;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }

        .btn-hero {
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s ease;
            margin-right: 20px;
            margin-bottom: 10px;
            display: inline-block;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-primary-hero {
            background: var(--secondary-color);
            color: white;
            border: 2px solid var(--secondary-color);
        }

        .btn-primary-hero:hover {
            background: transparent;
            color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 130, 0, 0.3);
        }

        .btn-outline-hero {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-outline-hero:hover {
            background: white;
            color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 255, 255, 0.3);
        }

        /* Carousel Controls */
        .carousel-control-prev,
        .carousel-control-next {
            width: 5%;
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }

        .carousel-control-prev:hover,
        .carousel-control-next:hover {
            opacity: 1;
        }

        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            background-size: 20px 20px;
            width: 20px;
            height: 20px;
        }

        /* Carousel Indicators */
        .carousel-indicators {
            bottom: 30px;
        }

        .carousel-indicators [data-bs-target] {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin: 0 5px;
            background-color: rgba(255, 255, 255, 0.5);
            border: 2px solid white;
        }

        .carousel-indicators .active {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        /* Stats Section */
        .stats-section {
            background: linear-gradient(135deg, var(--primary-color), var(--dark-blue));
            padding: 80px 0;
            color: white;
        }

        .stat-item {
            text-align: center;
            margin-bottom: 2rem;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            color: var(--secondary-color);
            display: block;
        }

        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* About Section */
        .about-section {
            padding: 100px 0;
            background: white;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 1rem;
        }

        .section-subtitle {
            font-size: 1.2rem;
            color: var(--medium-gray);
            text-align: center;
            margin-bottom: 4rem;
        }

        .feature-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid #f0f0f0;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem auto;
            font-size: 2rem;
            color: white;
        }

        .feature-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .feature-description {
            color: var(--medium-gray);
            line-height: 1.6;
        }

        /* Login Modal Styles */
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            max-width: 450px;
            margin: 0 auto;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-title {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .accent-line {
            height: 4px;
            background: linear-gradient(90deg, var(--secondary-color), var(--accent-color));
            width: 60px;
            margin: 0 auto 1rem auto;
            border-radius: 2px;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 91, 170, 0.25);
        }

        .input-group-text {
            background: var(--light-gray);
            border: 2px solid #e9ecef;
            border-right: none;
            border-radius: 10px 0 0 10px;
        }

        .btn-login {
            background: linear-gradient(135deg, var(--primary-color), var(--dark-blue));
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 91, 170, 0.3);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
            
            .login-container {
                padding: 2rem;
                margin: 20px;
            }
            
            .section-title {
                font-size: 2rem;
            }

            .btn-hero {
                margin-right: 0;
                margin-bottom: 15px;
                display: block;
                text-align: center;
            }

            .loading-text {
                font-size: 1.2rem;
                padding: 0 20px;
            }
        }

        /* Animation Classes */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <!-- Loading Splashscreen -->
    <div class="loading-screen" id="loadingScreen">
        <div class="loading-logo">
            <img src="images/logo.png" alt="PT IPC Terminal Petikemas" style="width: 100%; height: 100%; object-fit: contain;">
        </div>
        <div class="loading-text">
            Memuat SIMOB<br>
            <small>Aplikasi Pelaporan Bahan Bakar Minyak</small>
        </div>
        <div class="loading-spinner"></div>
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#home">
                <img src="images/logo.png" alt="PT IPC Terminal Petikemas" style="height: 30px; margin-right: 10px;">
                PT IPC Terminal Petikemas
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">Tentang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#login">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Carousel Section -->
    <section id="home">
        <div id="heroCarousel" class="carousel slide hero-carousel" data-bs-ride="carousel" data-bs-interval="5000" data-bs-touch="true">
            <!-- Carousel Indicators -->
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
            </div>

            <!-- Carousel Items -->
            <div class="carousel-inner">
                <!-- Slide 1 -->
                <div class="carousel-item active">
                    <div class="hero-content-overlay">
                        <div class="container">
                            <div class="row align-items-center">
                                <div class="col-lg-6">
                                    <div class="hero-content fade-in">
                                        <h1 class="hero-title">Aplikasi Pelaporan Bahan Bakar Minyak</h1>
                                        <p class="hero-subtitle">Solusi digital terdepan untuk monitoring dan pelaporan penggunaan bahan bakar minyak PT IPC Terminal Petikemas</p>
                                        <div class="hero-buttons">
                                            <a href="#about" class="btn-hero btn-primary-hero">Pelajari Lebih Lanjut</a>
                                            <a href="#login" class="btn-hero btn-outline-hero">Masuk Sekarang</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="text-center fade-in">
                                        <img src="images/gambar4.png" alt="Ilustrasi Dashboard" style="max-width: 100%; height: auto; opacity: 0.8;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 2 -->
                <div class="carousel-item">
                    <div class="hero-content-overlay">
                        <div class="container">
                            <div class="row align-items-center">
                                <div class="col-lg-6">
                                    <div class="hero-content fade-in">
                                        <h1 class="hero-title">Monitoring Real-Time</h1>
                                        <p class="hero-subtitle">Pantau penggunaan bahan bakar secara langsung dengan dashboard interaktif dan sistem peringatan otomatis</p>
                                        <div class="hero-buttons">
                                            <a href="#about" class="btn-hero btn-primary-hero">Pelajari Lebih Lanjut</a>
                                            <a href="#login" class="btn-hero btn-outline-hero">Masuk Sekarang</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="text-center fade-in">
                                       <img src="images/gambar5.png" alt="Ilustrasi Dashboard" style="max-width: 100%; height: auto; opacity: 0.8;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 3 -->
                <div class="carousel-item">
                    <div class="hero-content-overlay">
                        <div class="container">
                            <div class="row align-items-center">
                                <div class="col-lg-6">
                                    <div class="hero-content fade-in">
                                        <h1 class="hero-title">Laporan Terintegrasi</h1>
                                        <p class="hero-subtitle">Generate laporan komprehensif dengan berbagai format sesuai kebutuhan manajemen dan audit perusahaan</p>
                                        <div class="hero-buttons">
                                            <a href="#about" class="btn-hero btn-primary-hero">Pelajari Lebih Lanjut</a>
                                            <a href="#login" class="btn-hero btn-outline-hero">Masuk Sekarang</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="text-center fade-in">
                                        <img src="images/gambar6.png" alt="Ilustrasi Dashboard" style="max-width: 100%; height: auto; opacity: 0.8;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Carousel Controls -->
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-6">
                    <div class="stat-item fade-in">
                        <span class="stat-number">24/7</span>
                        <span class="stat-label">Monitoring</span>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item fade-in">
                        <span class="stat-number">100%</span>
                        <span class="stat-label">Akurasi Data</span>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item fade-in">
                        <span class="stat-number">50+</span>
                        <span class="stat-label">Pengguna Aktif</span>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item fade-in">
                        <span class="stat-number">5</span>
                        <span class="stat-label">Tahun Pengalaman</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about-section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2 class="section-title fade-in">Tentang SIMOB</h2>
                    <p class="section-subtitle fade-in">Sistem informasi terintegrasi untuk manajemen bahan bakar yang efisien dan akurat</p>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card fade-in">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4 class="feature-title">Monitoring Real-time</h4>
                        <p class="feature-description">Pantau penggunaan bahan bakar secara langsung dengan dashboard interaktif dan notifikasi otomatis</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card fade-in">
                        <div class="feature-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h4 class="feature-title">Pelaporan Komprehensif</h4>
                        <p class="feature-description">Generate laporan detail dengan berbagai format sesuai kebutuhan manajemen dan audit</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card fade-in">
                        <div class="feature-icon">
                            <i class="fas fa-database"></i>
                        </div>
                        <h4 class="feature-title">Data Terintegrasi</h4>
                        <p class="feature-description">Semua data tersimpan aman dengan sistem backup otomatis dan enkripsi tingkat enterprise</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card fade-in">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h4 class="feature-title">Mobile Responsive</h4>
                        <p class="feature-description">Akses sistem dari perangkat apapun dengan antarmuka yang responsif dan user-friendly</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card fade-in">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4 class="feature-title">Keamanan Tinggi</h4>
                        <p class="feature-description">Dilengkapi dengan sistem keamanan berlapis dan kontrol akses berbasis role</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card fade-in">
                        <div class="feature-icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <h4 class="feature-title">Kustomisasi Fleksibel</h4>
                        <p class="feature-description">Sistem dapat disesuaikan dengan kebutuhan spesifik operasional perusahaan</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Login Section -->
    <section id="login" class="about-section" style="background: var(--light-gray);">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="login-container fade-in">
                        <div class="login-header">
                            <div class="text-center mb-4">
                                <img src="images/logo.png" alt="PT IPC Terminal Petikemas" style="height: 100px; margin-right: 10px;">
                            </div>
                            <h3 class="login-title">Masuk ke SIMOB</h3>
                            <div class="accent-line"></div>
                            <p class="text-muted">Silakan masukkan kredensial Anda untuk melanjutkan</p>
                        </div>
                        <form method="post" action="login.php">
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-envelope text-muted"></i>
                                    </span>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan email Anda" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label fw-semibold">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock text-muted"></i>
                                    </span>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password Anda" required>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-login">
                                    <i class="fas fa-sign-in-alt me-2"></i>Masuk 
                                </button>
                            </div>
                            <div class="text-center mt-3">
                                <small class="text-muted">Lupa password? Hubungi administrator sistem</small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Loading Screen
        window.addEventListener('load', function() {
            setTimeout(function() {
                const loadingScreen = document.getElementById('loadingScreen');
                loadingScreen.classList.add('fade-out');
                setTimeout(function() {
                    loadingScreen.style.display = 'none';
                }, 500);
            }, 2000); // Show loading for 2 seconds
        });

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Fade in animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in').forEach(el => {
            observer.observe(el);
        });

        // Navbar background change on scroll
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(255, 255, 255, 0.98)';
            } else {
                navbar.style.background = 'rgba(255, 255, 255, 0.95)';
            }
        });

        // Form validation enhancement
        const form = document.querySelector('form');
        const inputs = form.querySelectorAll('input[required]');

        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                }
            });
        });

        // Initialize carousel with proper touch support
        document.addEventListener('DOMContentLoaded', function() {
            const carouselElement = document.querySelector('#heroCarousel');
            if (carouselElement) {
                const carousel = new bootstrap.Carousel(carouselElement, {
                    interval: 5000,
                    ride: 'carousel',
                    touch: true,
                    wrap: true
                });
            }
        });

        // Carousel pause on hover
        const carousel = document.querySelector('#heroCarousel');
        if (carousel) {
            carousel.addEventListener('mouseenter', () => {
                const carouselInstance = bootstrap.Carousel.getInstance(carousel);
                if (carouselInstance) {
                    carouselInstance.pause();
                }
            });
            
            carousel.addEventListener('mouseleave', () => {
                const carouselInstance = bootstrap.Carousel.getInstance(carousel);
                if (carouselInstance) {
                    carouselInstance.cycle();
                }
            });
        }
    </script>
</body>
</html>
