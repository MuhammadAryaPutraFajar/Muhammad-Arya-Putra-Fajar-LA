<?php
function showSidebar($userRole) {
    // Get current page filename
    $currentPage = basename($_SERVER['PHP_SELF']);
    ?>
    <div id="sidebar" class="sidebar">
        <div class="sidebar-header">
            <div class="company-logo">
                <img src="../../images/logo.png" alt="IPC Logo" class="logo-img">
                <div class="company-name">
                    <h3>PT IPC</h3>
                    <span>Terminal Petikemas</span>
                </div>
            </div>
        </div>
        
        <div class="sidebar-menu">
            <div class="menu-category">
                <span class="category-label">Menu Utama</span>
                <ul>
                    <!-- Menu untuk semua role -->
                    <li class="menu-item <?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>">
                        <a href="index.php">
                            <div class="menu-icon">
                                <i class="fas fa-tachometer-alt"></i>
                            </div>
                            <span>Dashboard</span>
                            <div class="menu-ripple"></div>
                        </a>
                    </li>
                    
                    <?php if($userRole == 'admin') { ?>
                        <!-- Menu Admin dengan Collapse -->
                        <li class="menu-item has-submenu <?php echo (in_array($currentPage, ['kelola_pengguna.php', 'kelola_alat.php', 'stok_bbm.php'])) ? 'active' : ''; ?>">
                            <a href="javascript:void(0)" class="submenu-toggle">
                                <div class="menu-icon">
                                    <i class="fas fa-cogs"></i>
                                </div>
                                <span>Kelola Data</span>
                                <div class="submenu-arrow">
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                                <div class="menu-ripple"></div>
                            </a>
                            <ul class="submenu <?php echo (in_array($currentPage, ['users.php', 'kelola_alat.php', 'stok_bbm.php'])) ? 'show' : ''; ?>">
                                <li class="submenu-item <?php echo ($currentPage == 'users.php') ? 'active' : ''; ?>">
                                    <a href="../admin/users.php">
                                        <div class="submenu-icon">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <span>Kelola Pengguna</span>
                                    </a>
                                </li>
                                <li class="submenu-item <?php echo ($currentPage == 'kelola_alat.php') ? 'active' : ''; ?>">
                                    <a href="../admin/kelola_alat.php">
                                        <div class="submenu-icon">
                                            <i class="fas fa-tractor"></i>
                                        </div>
                                        <span>Kelola Alat</span>
                                    </a>
                                </li>
                                <li class="submenu-item <?php echo ($currentPage == 'stok_bbm.php') ? 'active' : ''; ?>">
                                    <a href="../admin/stok_bbm.php">
                                        <div class="submenu-icon">
                                            <i class="fas fa-gas-pump"></i>
                                        </div>
                                        <span>Kelola Stok BBM</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        <!-- Menu Admin lainnya -->
                        <li class="menu-item <?php echo ($currentPage == 'verifikasi_request.php') ? 'active' : ''; ?>">
                            <a href="../admin/verifikasi_request.php">
                                <div class="menu-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <span>Verifikasi Request</span>
                                <div class="menu-ripple"></div>
                            </a>
                        </li>
                        <li class="menu-item <?php echo ($currentPage == 'laporan.php') ? 'active' : ''; ?>">
                            <a href="../admin/laporan.php">
                                <div class="menu-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <span>Laporan</span>
                                <div class="menu-ripple"></div>
                            </a>
                        </li>
                         <li class="menu-item <?php echo ($currentPage == 'analisis.php') ? 'active' : ''; ?>">
                            <a href="../admin/analisis.php">
                                <div class="menu-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <span>Analisis</span>
                                <div class="menu-ripple"></div>
                            </a>
                        </li>
                    <?php } elseif($userRole == 'operator') { ?>
                        <!-- Menu khusus Operator -->
                        <li class="menu-item <?php echo ($currentPage == 'request_pengisian.php') ? 'active' : ''; ?>">
                            <a href="../operator/request_pengisian.php">
                                <div class="menu-icon">
                                    <i class="fas fa-edit"></i>
                                </div>
                                <span>Request Pengisian</span>
                                <div class="menu-ripple"></div>
                            </a>
                        </li>
                        <li class="menu-item <?php echo ($currentPage == 'riwayat_request.php') ? 'active' : ''; ?>">
                            <a href="../operator/riwayat_request.php">
                                <div class="menu-icon">
                                    <i class="fas fa-history"></i>
                                </div>
                                <span>Riwayat Request</span>
                                <div class="menu-ripple"></div>
                            </a>
                        </li>
                    <?php } elseif($userRole == 'operator_pengisian') { ?>
                        <!-- Menu khusus Operator Pengisian -->
                        <li class="menu-item <?php echo ($currentPage == 'request_disetujui.php') ? 'active' : ''; ?>">
                            <a href="../operator_pengisian/request_disetujui.php">
                                <div class="menu-icon">
                                    <i class="fas fa-database"></i>
                                </div>
                                <span>Request Pengisian</span>
                                <div class="menu-ripple"></div>
                            </a>
                        </li>
                        <li class="menu-item <?php echo ($currentPage == 'riwayat_pengisian.php') ? 'active' : ''; ?>">
                            <a href="../operator_pengisian/riwayat_pengisian.php">
                                <div class="menu-icon">
                                    <i class="fas fa-history"></i>
                                </div>
                                <span>Riwayat Pengisian</span>
                                <div class="menu-ripple"></div>
                            </a>
                        </li>
                    <?php } elseif($userRole == 'supervisor') { ?>
                        <!-- Menu khusus Supervisor -->
                        <li class="menu-item <?php echo ($currentPage == 'monitoring_bbm.php') ? 'active' : ''; ?>">
                            <a href="../supervisor/monitoring_bbm.php">
                                <div class="menu-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <span>Monitoring BBM</span>
                                <div class="menu-ripple"></div>
                            </a>
                        </li>
                        <li class="menu-item <?php echo ($currentPage == 'laporan_harian.php') ? 'active' : ''; ?>">
                            <a href="../supervisor/laporan_harian.php">
                                <div class="menu-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <span>Laporan Harian</span>
                                <div class="menu-ripple"></div>
                            </a>
                        </li>
                        <li class="menu-item <?php echo ($currentPage == 'laporan_bulanan.php') ? 'active' : ''; ?>">
                            <a href="../supervisor/laporan_bulanan.php">
                                <div class="menu-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <span>Laporan Bulanan</span>
                                <div class="menu-ripple"></div>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
            
            <div class="menu-category">
                <span class="category-label">Akun</span>
                <ul>
                    <!-- Menu Logout untuk semua role -->
                    <li class="menu-item logout-item">
                        <a href="../../logout.php">
                            <div class="menu-icon">
                                <i class="fas fa-sign-out-alt"></i>
                            </div>
                            <span>Logout</span>
                            <div class="menu-ripple"></div>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">
                    <?php 
                    // Get initials from name
                    $nameParts = explode(' ', $_SESSION['name']);
                    $initials = '';
                    foreach ($nameParts as $part) {
                        $initials .= substr($part, 0, 1);
                        if (strlen($initials) >= 2) break;
                    }
                    echo $initials;
                    ?>
                </div>
                <div class="user-details">
                    <span class="user-name"><?php echo $_SESSION['name']; ?></span>
                    <span class="user-role"><?php echo ucfirst(str_replace('_', ' ', $userRole)); ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        /* Enhanced Modern Sidebar Design - IPC Terminal Petikemas */
        :root {
            --ipc-primary: #1a365d;           /* Navy Blue - lebih elegan */
            --ipc-secondary: #2d5a87;         /* Medium Blue */
            --ipc-accent: #3182ce;            /* Bright Blue untuk aksen */
            --ipc-light-blue: #63b3ed;       /* Light Blue untuk hover */
            --ipc-gradient-start: #1a365d;    /* Gradient start */
            --ipc-gradient-end: #2b77ad;     /* Gradient end */
            --ipc-hover: rgba(49, 130, 206, 0.08);  /* Hover background */
            --ipc-active: rgba(49, 130, 206, 0.15);  /* Active background */
            --ipc-light: #ffffff;             /* White background */
            --ipc-text-primary: #1a365d;     /* Primary text color */
            --ipc-text-secondary: #4a5568;   /* Secondary text color */
            --ipc-text-muted: #718096;       /* Muted text color */
            --ipc-border: rgba(26, 54, 93, 0.1); /* Border color */
            --ipc-shadow: rgba(26, 54, 93, 0.08); /* Box shadow */
            --ipc-glow: rgba(49, 130, 206, 0.4); /* Glow effect */
            --sidebar-width: 280px;           /* Sidebar width */
            --sidebar-collapsed-width: 70px;  /* Collapsed width - disesuaikan */
            --transition-speed: 0.3s;
            --transition-curve: cubic-bezier(0.4, 0, 0.2, 1);
            --border-radius: 12px;
            --border-radius-small: 8px;
            --menu-item-height: 50px;        /* Disesuaikan untuk collapse */
            --header-height: 70px;           /* Disesuaikan untuk collapse */
            --footer-height: 70px;           /* Disesuaikan untuk collapse */
        }
        
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 100;
            background: var(--ipc-light);
            color: var(--ipc-text-primary);
            transition: width var(--transition-speed) var(--transition-curve);
            box-shadow: 
                0 10px 25px -5px rgba(26, 54, 93, 0.08),
                0 8px 10px -6px rgba(26, 54, 93, 0.05);
            font-family: 'Inter', 'Segoe UI', 'Roboto', sans-serif;
            overflow-y: auto;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            border-right: 1px solid var(--ipc-border);
            backdrop-filter: blur(10px);
        }
        
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: rgba(26, 54, 93, 0.02);
            border-radius: 10px;
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, var(--ipc-primary), var(--ipc-secondary));
            border-radius: 10px;
        }
        
        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }
        
        .sidebar-header {
            padding: 15px 12px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-bottom: 1px solid var(--ipc-border);
            height: var(--header-height);
            background: linear-gradient(135deg, 
                        rgba(26, 54, 93, 0.02) 0%, 
                        rgba(49, 130, 206, 0.02) 100%);
            position: relative;
            min-height: var(--header-height);
        }
        
        .sidebar.collapsed .sidebar-header {
            padding: 15px 8px;
        }
        
        .sidebar-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60%;
            height: 2px;
            background: linear-gradient(90deg, 
                        transparent 0%, 
                        var(--ipc-accent) 50%, 
                        transparent 100%);
            border-radius: 2px;
        }
        
        .sidebar.collapsed .sidebar-header::after {
            width: 30px;
        }
        
        .company-logo {
            display: flex;
            align-items: center;
            overflow: hidden;
            background: linear-gradient(135deg, 
                        rgba(26, 54, 93, 0.05) 0%, 
                        rgba(49, 130, 206, 0.05) 100%);
            border-radius: var(--border-radius);
            padding: 8px 12px;
            transition: all var(--transition-speed) var(--transition-curve);
            border: 1px solid rgba(49, 130, 206, 0.1);
            position: relative;
            width: 100%;
            justify-content: flex-start;
        }
        
        .sidebar.collapsed .company-logo {
            padding: 8px;
            justify-content: center;
            width: auto;
            min-width: 46px;
        }
        
        .company-logo::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, 
                        rgba(255, 255, 255, 0.1) 0%, 
                        rgba(255, 255, 255, 0.05) 100%);
            border-radius: var(--border-radius);
            pointer-events: none;
        }
        
        .logo-img {
            height: 36px;
            width: auto;
            margin-right: 12px;
            transition: all var(--transition-speed) var(--transition-curve);
            filter: drop-shadow(0 4px 8px rgba(26, 54, 93, 0.15));
            flex-shrink: 0;
        }
        
        .sidebar.collapsed .logo-img {
            margin-right: 0;
            height: 32px;
        }
        
        .company-name {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
            transition: all var(--transition-speed) var(--transition-curve);
            opacity: 1;
            min-width: 0;
            overflow: hidden;
        }
        
        .sidebar.collapsed .company-name {
            opacity: 0;
            width: 0;
            transform: translateX(-20px);
        }
        
        .company-name h3 {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: var(--ipc-primary);
            text-shadow: 0 2px 4px rgba(26, 54, 93, 0.1);
            background: linear-gradient(135deg, var(--ipc-primary), var(--ipc-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            white-space: nowrap;
        }
        
        .company-name span {
            font-size: 0.75rem;
            opacity: 0.8;
            letter-spacing: 0.3px;
            color: var(--ipc-text-secondary);
            font-weight: 500;
            white-space: nowrap;
        }
        
        .sidebar-menu {
            padding: 15px 0;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .menu-category {
            padding: 0 12px;
            margin-bottom: 8px;
        }
        
        .sidebar.collapsed .menu-category {
            padding: 0 8px;
        }
        
        .category-label {
            display: block;
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--ipc-text-muted);
            padding: 8px 8px 6px;
            font-weight: 600;
            position: relative;
            transition: all var(--transition-speed) var(--transition-curve);
            opacity: 1;
        }
        
        .sidebar.collapsed .category-label {
            opacity: 0;
            height: 0;
            padding: 0;
            margin: 0;
            overflow: hidden;
        }
        
        .category-label::after {
            content: '';
            position: absolute;
            bottom: 2px;
            left: 8px;
            width: 24px;
            height: 2px;
            background: linear-gradient(90deg, var(--ipc-accent), transparent);
            border-radius: 2px;
        }
        
        .sidebar-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .menu-item {
            margin: 4px 0;
            position: relative;
            transition: all var(--transition-speed) var(--transition-curve);
            border-radius: var(--border-radius-small);
            overflow: hidden;
        }
        
        .sidebar.collapsed .menu-item {
            margin: 8px 0;
        }
        
        .menu-item.active {
            background: var(--ipc-active);
            box-shadow: 
                0 4px 12px rgba(49, 130, 206, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            transform: translateX(1px);
        }
        
        .sidebar.collapsed .menu-item.active {
            transform: translateX(0);
        }
        
        .menu-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: linear-gradient(180deg, var(--ipc-accent), var(--ipc-light-blue));
            border-radius: 0 4px 4px 0;
            box-shadow: 0 0 8px var(--ipc-glow);
        }
        
        .menu-item a {
            height: var(--menu-item-height);
            display: flex;
            align-items: center;
            color: var(--ipc-text-primary);
            text-decoration: none;
            transition: all var(--transition-speed) var(--transition-curve);
            position: relative;
            padding: 0 12px;
            overflow: hidden;
            z-index: 1;
            border-radius: var(--border-radius-small);
        }
        
        .sidebar.collapsed .menu-item a {
            padding: 0;
            justify-content: center;
        }
        
        .menu-ripple {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle, 
                        rgba(49, 130, 206, 0.1) 0%, 
                        transparent 70%);
            transform: scale(0);
            transition: transform 0.6s var(--transition-curve);
            border-radius: var(--border-radius-small);
        }
        
        .menu-item a:hover .menu-ripple {
            transform: scale(1);
        }
        
        .menu-item a:hover {
            background: var(--ipc-hover);
            transform: translateX(2px);
            box-shadow: 0 4px 12px rgba(49, 130, 206, 0.1);
        }
        
        .sidebar.collapsed .menu-item a:hover {
            transform: translateX(0) scale(1.05);
        }
        
        .menu-item.active a {
            background: transparent;
            color: var(--ipc-primary);
            font-weight: 600;
        }
        
        .menu-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            transition: all var(--transition-speed) var(--transition-curve);
            width: 32px;
            height: 32px;
            border-radius: var(--border-radius-small);
            background: rgba(49, 130, 206, 0.08);
            position: relative;
            flex-shrink: 0;
        }
        
        .sidebar.collapsed .menu-icon {
            margin-right: 0;
            width: 36px;
            height: 36px;
        }
        
        .menu-item.active .menu-icon {
            background: linear-gradient(135deg, var(--ipc-accent), var(--ipc-light-blue));
            box-shadow: 0 4px 8px rgba(49, 130, 206, 0.3);
        }
        
        .menu-item i {
            font-size: 1.1rem;
            color: var(--ipc-primary);
            transition: all var(--transition-speed) var(--transition-curve);
        }
        
        .sidebar.collapsed .menu-item i {
            font-size: 1.2rem;
        }
        
        .menu-item.active i {
            color: white;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }
        
        .menu-item a:hover i {
            transform: scale(1.1);
        }
        
        .menu-item span {
            white-space: nowrap;
            font-weight: 500;
            font-size: 0.9rem;
            letter-spacing: 0.3px;
            transition: all var(--transition-speed) var(--transition-curve);
            flex-grow: 1;
            color: var(--ipc-text-primary);
            opacity: 1;
            overflow: hidden;
        }
        
        .sidebar.collapsed .menu-item span {
            opacity: 0;
            width: 0;
            transform: translateX(-20px);
        }
        
        .menu-item.active span {
            font-weight: 600;
        }
        
        /* Submenu Styles */
        .menu-item.has-submenu .submenu-arrow {
            margin-left: auto;
            transition: transform var(--transition-speed) var(--transition-curve);
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(49, 130, 206, 0.1);
        }
        
        .sidebar.collapsed .menu-item.has-submenu .submenu-arrow {
            display: none;
        }
        
        .menu-item.has-submenu.active .submenu-arrow {
            transform: rotate(90deg);
            background: rgba(255, 255, 255, 0.2);
        }
        
        .menu-item.has-submenu .submenu-arrow i {
            font-size: 0.8rem;
            color: var(--ipc-primary);
        }
        
        .menu-item.has-submenu.active .submenu-arrow i {
            color: white;
        }
        
        .submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height var(--transition-speed) var(--transition-curve);
            background: rgba(49, 130, 206, 0.02);
            border-radius: var(--border-radius-small);
            margin: 4px 0 0 0;
            border-left: 2px solid var(--ipc-accent);
        }
        
        .sidebar.collapsed .submenu {
            display: none;
        }
        
        .submenu.show {
            max-height: 200px;
        }
        
        .submenu-item {
            margin: 0;
        }
        
        .submenu-item a {
            height: 42px;
            padding: 0 16px 0 24px;
            font-size: 0.85rem;
            color: var(--ipc-text-secondary);
        }
        
        .submenu-item.active a {
            background: var(--ipc-active);
            color: var(--ipc-primary);
            font-weight: 600;
        }
        
        .submenu-item a:hover {
            background: var(--ipc-hover);
            color: var(--ipc-primary);
            transform: translateX(4px);
        }
        
        .submenu-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            width: 24px;
            height: 24px;
            border-radius: var(--border-radius-small);
            background: rgba(49, 130, 206, 0.1);
            flex-shrink: 0;
        }
        
        .submenu-item.active .submenu-icon {
            background: var(--ipc-accent);
        }
        
        .submenu-item i {
            font-size: 0.9rem;
            color: var(--ipc-primary);
        }
        
        .submenu-item.active i {
            color: white;
        }
        
        .logout-item {
            margin-top: 12px;
            border-top: 1px solid var(--ipc-border);
            padding-top: 8px;
        }
        
        .sidebar.collapsed .logout-item {
            border-top: none;
            padding-top: 0;
        }
        
        .logout-item .menu-icon {
            background: rgba(239, 68, 68, 0.08);
        }
        
        .logout-item i {
            color: #dc2626;
        }
        
        .logout-item a:hover {
            background: rgba(239, 68, 68, 0.05);
        }
        
        .logout-item a:hover .menu-icon {
            background: rgba(239, 68, 68, 0.15);
        }
        
        .sidebar-footer {
            padding: 12px;
            border-top: 1px solid var(--ipc-border);
            margin-top: auto;
            background: linear-gradient(135deg, 
                        rgba(26, 54, 93, 0.02) 0%, 
                        rgba(49, 130, 206, 0.02) 100%);
            height: var(--footer-height);
            display: flex;
            align-items: center;
            min-height: var(--footer-height);
        }
        
        .sidebar.collapsed .sidebar-footer {
            padding: 12px 8px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            padding: 8px 10px;
            border-radius: var(--border-radius);
            transition: all var(--transition-speed) var(--transition-curve);
            background: rgba(255, 255, 255, 0.5);
            border: 1px solid rgba(49, 130, 206, 0.1);
            width: 100%;
            cursor: pointer;
        }
        
        .sidebar.collapsed .user-info {
            padding: 8px;
            justify-content: center;
            width: auto;
            min-width: 46px;
        }
        
        .user-info:hover {
            background: rgba(49, 130, 206, 0.05);
            border-color: rgba(49, 130, 206, 0.2);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(49, 130, 206, 0.1);
        }
        
        .sidebar.collapsed .user-info:hover {
            transform: translateY(-1px) scale(1.05);
        }
        
        .user-avatar {
            min-width: 38px;
            height: 38px;
            background: linear-gradient(135deg, var(--ipc-gradient-start), var(--ipc-gradient-end));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            transition: all var(--transition-speed) var(--transition-curve);
            font-weight: 700;
            font-size: 1rem;
            color: white;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            box-shadow: 
                0 4px 12px rgba(26, 54, 93, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            flex-shrink: 0;
        }
        
        .sidebar.collapsed .user-avatar {
            margin-right: 0;
            width: 38px;
            height: 38px;
        }
        
        .user-details {
            display: flex;
            flex-direction: column;
            transition: all var(--transition-speed) var(--transition-curve);
            overflow: hidden;
            flex-grow: 1;
            opacity: 1;
            min-width: 0;
        }
        
        .sidebar.collapsed .user-details {
            opacity: 0;
            width: 0;
            transform: translateX(-20px);
        }
        
        .user-name {
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: var(--ipc-primary);
        }
        
        .user-role {
            font-size: 0.7rem;
            opacity: 0.8;
            letter-spacing: 0.3px;
            color: var(--ipc-text-secondary);
            font-weight: 500;
            text-transform: capitalize;
            white-space: nowrap;
        }
        
        .content-wrapper {
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: all var(--transition-speed) var(--transition-curve);
        }
        
        .content-wrapper.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }
        
        /* Enhanced Responsive Styling */
        @media (max-width: 768px) {
            .sidebar {
                width: var(--sidebar-collapsed-width);
                transform: translateX(0);
            }
            
            .sidebar .company-logo {
                padding: 8px;
                justify-content: center;
            }
            
            .sidebar .menu-item {
                display: flex;
                justify-content: center;
                margin: 8px 0;
            }
            
            .sidebar .menu-item a {
                justify-content: center;
                padding: 0;
            }
            
            .sidebar .category-label,
            .sidebar .menu-item span,
            .sidebar .company-name,
            .sidebar .user-details,
            .sidebar .submenu-arrow {
                opacity: 0;
                width: 0;
                transform: translateX(-20px);
            }
            
            .sidebar .menu-icon {
                margin-right: 0;
                width: 36px;
                height: 36px;
            }
            
            .sidebar .menu-item i {
                font-size: 1.2rem;
            }
            
            .sidebar .logo-img {
                margin-right: 0;
                height: 32px;
            }
            
            .sidebar .user-info {
                padding: 8px;
                justify-content: center;
            }
            
            .sidebar .user-avatar {
                margin-right: 0;
            }
            
            .sidebar .submenu {
                display: none;
            }
            
            .content-wrapper {
                margin-left: var(--sidebar-collapsed-width);
            }
            
            .sidebar.expanded {
                width: var(--sidebar-width);
                transform: translateX(0);
                box-shadow: 
                    0 20px 25px -5px rgba(26, 54, 93, 0.15),
                    0 10px 10px -5px rgba(26, 54, 93, 0.1);
                z-index: 1000;
            }
            
            .sidebar.expanded .company-logo {
                padding: 8px 12px;
                justify-content: flex-start;
            }
            
            .sidebar.expanded .menu-item {
                display: flex;
                justify-content: flex-start;
                margin: 4px 0;
            }
            
            .sidebar.expanded .menu-item a {
                justify-content: flex-start;
                padding: 0 12px;
            }
            
            .sidebar.expanded .menu-icon {
                margin-right: 12px;
                width: 32px;
                height: 32px;
            }
            
            .sidebar.expanded .menu-item i {
                font-size: 1.1rem;
            }
            
            .sidebar.expanded .category-label,
            .sidebar.expanded .menu-item span,
            .sidebar.expanded .company-name,
            .sidebar.expanded .user-details,
            .sidebar.expanded .submenu-arrow {
                opacity: 1;
                width: auto;
                transform: translateX(0);
            }
            
            .sidebar.expanded .logo-img {
                margin-right: 12px;
                height: 36px;
            }
            
            .sidebar.expanded .user-info {
                padding: 8px 10px;
                justify-content: flex-start;
            }
            
            .sidebar.expanded .user-avatar {
                margin-right: 10px;
            }
            
            .sidebar.expanded .submenu {
                display: block;
            }
            
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(26, 54, 93, 0.6);
                z-index: 99;
                backdrop-filter: blur(4px);
                transition: all var(--transition-speed) var(--transition-curve);
            }
            
            .sidebar-overlay.active {
                display: block;
            }
        }
        
        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            :root {
                --ipc-light: #1a202c;
                --ipc-text-primary: #e2e8f0;
                --ipc-text-secondary: #a0aec0;
                --ipc-text-muted: #718096;
                --ipc-border: rgba(255, 255, 255, 0.1);
                --ipc-hover: rgba(49, 130, 206, 0.15);
                --ipc-active: rgba(49, 130, 206, 0.2);
                --ipc-shadow: rgba(0, 0, 0, 0.3);
            }
            
            .sidebar {
                background: var(--ipc-light);
                border-right-color: var(--ipc-border);
            }
            
            .user-info {
                background: rgba(255, 255, 255, 0.05);
                border-color: rgba(255, 255, 255, 0.1);
            }
            
            .user-info:hover {
                background: rgba(49, 130, 206, 0.1);
                border-color: rgba(49, 130, 206, 0.3);
            }
        }
        
        /* Animation keyframes */
        @keyframes slideIn {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes fadeInUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }
        
        .menu-item.active {
            animation: fadeInUp 0.3s ease-out;
        }
        
        .user-avatar:hover {
            animation: pulse 2s infinite;
        }
        
        /* Accessibility improvements */
        .menu-item a:focus {
            outline: 2px solid var(--ipc-accent);
            outline-offset: 2px;
        }
        
        .sidebar:focus-within {
            box-shadow: 
                0 10px 25px -5px rgba(26, 54, 93, 0.08),
                0 8px 10px -6px rgba(26, 54, 93, 0.05),
                0 0 0 2px rgba(49, 130, 206, 0.2);
        }
        
        /* High contrast mode support */
        @media (prefers-contrast: high) {
            :root {
                --ipc-primary: #000000;
                --ipc-secondary: #333333;
                --ipc-accent: #0066cc;
                --ipc-border: #666666;
            }
            
            .menu-item.active {
                border: 2px solid var(--ipc-accent);
            }
        }
        
        /* Reduced motion support */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
        
        /* Tooltip untuk collapsed state */
        .sidebar.collapsed .menu-item {
            position: relative;
        }
        
        .sidebar.collapsed .menu-item:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            left: calc(100% + 10px);
            top: 50%;
            transform: translateY(-50%);
            background: var(--ipc-primary);
            color: white;
            padding: 8px 12px;
            border-radius: var(--border-radius-small);
            font-size: 0.8rem;
            font-weight: 500;
            white-space: nowrap;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(26, 54, 93, 0.3);
            opacity: 0;
            animation: fadeInUp 0.2s ease-out 0.5s forwards;
        }
        
        .sidebar.collapsed .menu-item:hover::before {
            content: '';
            position: absolute;
            left: calc(100% + 4px);
            top: 50%;
            transform: translateY(-50%);
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 6px 6px 6px 0;
            border-color: transparent var(--ipc-primary) transparent transparent;
            z-index: 1000;
            opacity: 0;
            animation: fadeInUp 0.2s ease-out 0.5s forwards;
        }
        
        /* Smooth scrolling untuk sidebar */
        .sidebar {
            scroll-behavior: smooth;
        }
        
        /* Enhanced focus states */
        .menu-item a:focus-visible {
            outline: 2px solid var(--ipc-accent);
            outline-offset: -2px;
            border-radius: var(--border-radius-small);
        }
        
        .user-info:focus-visible {
            outline: 2px solid var(--ipc-accent);
            outline-offset: -2px;
        }
        
        /* Loading state untuk menu items */
        .menu-item {
            opacity: 0;
            transform: translateX(-20px);
            animation: slideIn 0.3s ease-out forwards;
        }
        
        .menu-item:nth-child(1) { animation-delay: 0.1s; }
        .menu-item:nth-child(2) { animation-delay: 0.2s; }
        .menu-item:nth-child(3) { animation-delay: 0.3s; }
        .menu-item:nth-child(4) { animation-delay: 0.4s; }
        .menu-item:nth-child(5) { animation-delay: 0.5s; }
        .menu-item:nth-child(6) { animation-delay: 0.6s; }
    </style>
    <div id="sidebarOverlay" class="sidebar-overlay"></div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const contentWrapper = document.querySelector('.content-wrapper');
            const overlay = document.getElementById('sidebarOverlay');
            
            // Check if there's a saved state in localStorage
            const sidebarState = localStorage.getItem('sidebarCollapsed');
            
            if (sidebarState === 'true') {
                sidebar.classList.add('collapsed');
                if (contentWrapper) contentWrapper.classList.add('expanded');
            }
            
            // Add tooltips to menu items for collapsed state
            const menuItems = document.querySelectorAll('.menu-item');
            menuItems.forEach(item => {
                const menuText = item.querySelector('span');
                if (menuText) {
                    item.setAttribute('data-tooltip', menuText.textContent);
                }
            });
            
            // Enhanced ripple effect for menu items
            const menuLinks = document.querySelectorAll('.menu-item a');
            
            menuLinks.forEach(item => {
                item.addEventListener('click', function(e) {
                    // Create ripple effect
                    const ripple = this.querySelector('.menu-ripple');
                    if (ripple) {
                        ripple.style.transform = 'scale(0)';
                        setTimeout(() => {
                            ripple.style.transform = 'scale(1)';
                        }, 10);
                        
                        setTimeout(() => {
                            ripple.style.transform = 'scale(0)';
                        }, 300);
                    }
                    
                    // Close sidebar on mobile after clicking menu item
                    if (window.innerWidth <= 768) {
                        sidebar.classList.remove('expanded');
                        if (overlay) overlay.classList.remove('active');
                    }
                });
                
                // Enhanced hover effects
                item.addEventListener('mouseenter', function() {
                    const icon = this.querySelector('i');
                    if (icon && !sidebar.classList.contains('collapsed')) {
                        icon.style.transform = 'scale(1.1) rotate(5deg)';
                    } else if (icon) {
                        icon.style.transform = 'scale(1.1)';
                    }
                });
                
                item.addEventListener('mouseleave', function() {
                    const icon = this.querySelector('i');
                    if (icon) {
                        icon.style.transform = 'scale(1) rotate(0deg)';
                    }
                });
            });
            
            // Submenu toggle functionality
            const submenuToggles = document.querySelectorAll('.submenu-toggle');
            submenuToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const menuItem = this.closest('.menu-item');
                    const submenu = menuItem.querySelector('.submenu');
                    const arrow = menuItem.querySelector('.submenu-arrow');
                    
                    // Toggle submenu
                    if (submenu.classList.contains('show')) {
                        submenu.classList.remove('show');
                        menuItem.classList.remove('active');
                    } else {
                        // Close other submenus
                        document.querySelectorAll('.submenu.show').forEach(openSubmenu => {
                            openSubmenu.classList.remove('show');
                            openSubmenu.closest('.menu-item').classList.remove('active');
                        });
                        
                        // Open this submenu
                        submenu.classList.add('show');
                        menuItem.classList.add('active');
                    }
                });
            });
            
            // For mobile: clicking overlay will close expanded sidebar
            if (overlay) {
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('expanded');
                    overlay.classList.remove('active');
                });
            }
            
            // Enhanced window resize handling
            let resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    if (window.innerWidth <= 768) {
                        // Switch to mobile view
                        if (!sidebar.classList.contains('collapsed') && !sidebar.classList.contains('expanded')) {
                            sidebar.classList.add('collapsed');
                            if (contentWrapper) contentWrapper.classList.add('expanded');
                        }
                    } else {
                        // Switch to desktop view, restore from localStorage
                        sidebar.classList.remove('expanded');
                        if (overlay) overlay.classList.remove('active');
                        
                        const savedState = localStorage.getItem('sidebarCollapsed');
                        if (savedState === 'true') {
                            sidebar.classList.add('collapsed');
                            if (contentWrapper) contentWrapper.classList.add('expanded');
                        } else {
                            sidebar.classList.remove('collapsed');
                            if (contentWrapper) contentWrapper.classList.remove('expanded');
                        }
                    }
                }, 100);
            });
            
            // Keyboard navigation support
            document.addEventListener('keydown', function(e) {
                // Toggle sidebar with Ctrl+B or Cmd+B
                if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
                    e.preventDefault();
                    if (window.innerWidth > 768) {
                        sidebar.classList.toggle('collapsed');
                        if (contentWrapper) contentWrapper.classList.toggle('expanded');
                        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
                    }
                }
                
                // Close mobile sidebar with Escape key
                if (e.key === 'Escape' && window.innerWidth <= 768) {
                    sidebar.classList.remove('expanded');
                    if (overlay) overlay.classList.remove('active');
                }
            });
            
            // User info click handler for future profile menu
            const userInfo = document.querySelector('.user-info');
            if (userInfo) {
                userInfo.addEventListener('click', function() {
                    // Add subtle feedback animation
                    this.style.transform = 'scale(0.98)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 150);
                    
                    // Future: Show user profile dropdown
                    console.log('User profile clicked - ready for dropdown implementation');
                });
            }
            
            // Smooth scroll behavior for sidebar
            sidebar.addEventListener('scroll', function() {
                const scrollTop = this.scrollTop;
                const header = this.querySelector('.sidebar-header');
                
                if (scrollTop > 10) {
                    header.style.boxShadow = '0 2px 10px rgba(26, 54, 93, 0.1)';
                } else {
                    header.style.boxShadow = 'none';
                }
            });
            
            // Initialize menu items animation
            setTimeout(() => {
                menuItems.forEach((item, index) => {
                    setTimeout(() => {
                        item.style.opacity = '1';
                        item.style.transform = 'translateX(0)';
                    }, index * 50);
                });
            }, 100);
            
            // Add toggle button for mobile (optional)
            function createToggleButton() {
                if (window.innerWidth <= 768) {
                    let toggleBtn = document.getElementById('sidebarToggle');
                    if (!toggleBtn) {
                        toggleBtn = document.createElement('button');
                        toggleBtn.id = 'sidebarToggle';
                        toggleBtn.innerHTML = '<i class="fas fa-bars"></i>';
                        toggleBtn.style.cssText = `
                            position: fixed;
                            top: 20px;
                            left: 20px;
                            z-index: 1001;
                            background: var(--ipc-primary);
                            color: white;
                            border: none;
                            width: 45px;
                            height: 45px;
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 1.2rem;
                            cursor: pointer;
                            box-shadow: 0 4px 12px rgba(26, 54, 93, 0.3);
                            transition: all 0.3s ease;
                        `;
                        
                        toggleBtn.addEventListener('click', function() {
                            sidebar.classList.toggle('expanded');
                            if (overlay) overlay.classList.toggle('active');
                        });
                        
                        document.body.appendChild(toggleBtn);
                    }
                } else {
                    const existingBtn = document.getElementById('sidebarToggle');
                    if (existingBtn) {
                        existingBtn.remove();
                    }
                }
            }
            
            // Create toggle button on load and resize
            createToggleButton();
            window.addEventListener('resize', createToggleButton);
        });
    </script>
    <?php
}
?>
