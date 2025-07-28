<?php
/**
 * Header file for Aplikasi Pelaporan Bahan Bakar Minyak PT IPC Terminal Petikemas
 */
?>
<style>
    .header {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        box-shadow: 0 2px 20px rgba(0, 91, 170, 0.1);
        border-bottom: 3px solid #005baa;
        position: relative;
        z-index: 1000;
    }

    .navbar {
        background: transparent !important;
        padding: 1rem 0;
    }

    .navbar-brand {
        display: flex;
        align-items: center;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .navbar-brand:hover {
        transform: translateY(-2px);
    }

    .navbar-brand img {
        height: 50px;
        width: auto;
        margin-right: 15px;
        filter: drop-shadow(0 2px 8px rgba(0, 91, 170, 0.2));
        transition: all 0.3s ease;
    }

    .navbar-brand:hover img {
        filter: drop-shadow(0 4px 12px rgba(0, 91, 170, 0.3));
    }

    .company-name {
        font-size: 1.5rem;
        font-weight: 700;
        background: linear-gradient(135deg, #005baa 0%, #003366 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin: 0;
        line-height: 1.2;
    }

    .company-subtitle {
        font-size: 0.85rem;
        color: #666;
        font-weight: 500;
        margin: 0;
        opacity: 0.8;
    }

    .navbar-toggler {
        border: 2px solid #005baa;
        padding: 0.5rem 0.75rem;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .navbar-toggler:focus {
        box-shadow: 0 0 0 0.2rem rgba(0, 91, 170, 0.25);
    }

    .navbar-toggler:hover {
        background-color: #005baa;
        transform: translateY(-1px);
    }

    .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='%23005baa' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        transition: all 0.3s ease;
    }

    .navbar-toggler:hover .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='%23ffffff' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }

    .navbar-nav .nav-link {
        color: #333 !important;
        font-weight: 500;
        font-size: 1rem;
        padding: 0.75rem 1.25rem !important;
        margin: 0 0.25rem;
        border-radius: 8px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .navbar-nav .nav-link::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #005baa 0%, #003366 100%);
        transition: left 0.3s ease;
        z-index: -1;
    }

    .navbar-nav .nav-link:hover {
        color: white !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 91, 170, 0.3);
    }

    .navbar-nav .nav-link:hover::before {
        left: 0;
    }

    .navbar-nav .nav-link.active {
        background: linear-gradient(135deg, #005baa 0%, #003366 100%);
        color: white !important;
        box-shadow: 0 2px 8px rgba(0, 91, 170, 0.3);
    }

    .navbar-nav .nav-link.active::before {
        left: 0;
    }

    /* Logo placeholder styling */
    .logo-placeholder {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg,rgb(112, 173, 227) 0%,rgb(255, 255, 255) 100%);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 0.9rem;
        margin-right: 15px;
        box-shadow: 0 2px 8px rgba(0, 91, 170, 0.2);
        transition: all 0.3s ease;
    }

    .navbar-brand:hover .logo-placeholder {
        box-shadow: 0 4px 12px rgba(0, 91, 170, 0.3);
        transform: scale(1.05);
    }

    /* Responsive adjustments */
    @media (max-width: 991.98px) {
        .company-name {
            font-size: 1.25rem;
        }
        
        .company-subtitle {
            font-size: 0.8rem;
        }

        .navbar-nav .nav-link {
            margin: 0.25rem 0;
        }

        .navbar-collapse {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e9ecef;
        }
    }

    @media (max-width: 767.98px) {
        .company-name {
            font-size: 1.1rem;
        }
        
        .company-subtitle {
            font-size: 0.75rem;
        }

        .navbar-brand img,
        .logo-placeholder {
            height: 40px;
            width: 40px;
            margin-right: 10px;
        }
    }
</style>

<header class="header">
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <!-- Uncomment this line when you have the actual logo -->
                <!-- <img src="images/logo.png" alt="Logo PT IPC Terminal Petikemas"> -->
                
                <!-- Logo placeholder - remove when you have actual logo -->
                <div class="logo-placeholder">
                    <img src="images/logo.png" alt="Logo PT IPC">
                </div>
                
                <div class="brand-text">
                    <div class="company-name">PT IPC Terminal Petikemas</div>
                    <div class="company-subtitle">Fuel Reporting System</div>
                </div>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-home me-1"></i>Beranda
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#tentang">
                            <i class="fas fa-info-circle me-1"></i>Tentang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#kontak">
                            <i class="fas fa-envelope me-1"></i>Kontak
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="help.php">
                            <i class="fas fa-question-circle me-1"></i>Bantuan
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>