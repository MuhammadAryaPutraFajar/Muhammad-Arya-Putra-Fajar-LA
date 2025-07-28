<?php
function showTopbar($userRole) {
    // Set timezone to Indonesia
    date_default_timezone_set('Asia/Jakarta');
    
    // Get current time for greeting
    $hour = date('H');
    if ($hour >= 5 && $hour < 12) {
        $greeting = "Selamat Pagi";
    } elseif ($hour >= 12 && $hour < 15) {
        $greeting = "Selamat Siang";
    } elseif ($hour >= 15 && $hour < 18) {
        $greeting = "Selamat Sore";
    } else {
        $greeting = "Selamat Malam";
    }
    ?>
    <header id="topbar" class="topbar">
        <div class="topbar-left">
            <button id="sidebarToggleTop" class="btn-toggle">
                <i class="fa fa-bars"></i>
            </button>
            <div class="welcome-text">
                <span class="greeting"><?php echo $greeting; ?>,</span>
                <span class="username"><?php echo $_SESSION['name']; ?></span>
            </div>
        </div>
        
        <div class="topbar-right">
            <!-- Mobile Menu Button -->
            <button class="mobile-menu-btn d-md-none" id="mobileMenuToggle">
                <i class="fa fa-ellipsis-v"></i>
            </button>
            
            <!-- Desktop Items -->
            <div class="desktop-items">
                <div class="topbar-item date-time-container">
                    <div class="date-time">
                        <span class="current-date"><?php echo date('d M Y'); ?></span>
                    </div>
                </div>
                
                <div class="topbar-divider"></div>
                
                <div class="topbar-item profile-dropdown">
                    <div class="profile-toggle" id="profileDropdown">
                        <div class="avatar-wrapper">
                            <?php if(isset($_SESSION['foto']) && !empty($_SESSION['foto']) && file_exists('uploads/' . $_SESSION['foto'])): ?>
                                <img src="uploads/<?php echo $_SESSION['foto']; ?>" alt="Profile" class="profile-photo">
                            <?php else: ?>
                                <div class="avatar-circle">
                                    <?php 
                                    // Get initials from name
                                    $nameParts = explode(' ', $_SESSION['name']);
                                    $initials = '';
                                    foreach ($nameParts as $part) {
                                        $initials .= substr($part, 0, 1);
                                        if (strlen($initials) >= 2) break;
                                    }
                                    echo strtoupper($initials);
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="profile-info d-none d-md-flex">
                            <span class="profile-name"><?php echo $_SESSION['name']; ?></span>
                            <span class="profile-role"><?php echo ucfirst($userRole); ?></span>
                        </div>
                        <i class="fa fa-angle-down ml-2 d-none d-md-inline"></i>
                    </div>
                    <div class="dropdown-menu profile-menu">
                        <div class="dropdown-header">
                            <div class="profile-header-info">
                                <?php if(isset($_SESSION['foto']) && !empty($_SESSION['foto']) && file_exists('uploads/' . $_SESSION['foto'])): ?>
                                    <img src="uploads/<?php echo $_SESSION['foto']; ?>" alt="Profile" class="profile-header-photo">
                                <?php else: ?>
                                    <div class="avatar-circle-large">
                                        <?php echo strtoupper($initials); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="profile-header-text">
                                    <span class="profile-header-name"><?php echo $_SESSION['name']; ?></span>
                                    <span class="profile-header-role"><?php echo ucfirst($userRole); ?></span>
                                </div>
                            </div>
                        </div>
                        <a class="dropdown-item" href="profile.php">
                            <i class="fa fa-user"></i>
                            <span>Profil</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item logout-btn" href="#" onclick="logout()">
                            <i class="fa fa-sign-out"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Mobile Menu Overlay -->
            <div class="mobile-menu-overlay" id="mobileMenuOverlay">
                <div class="mobile-menu-content">
                    <div class="mobile-menu-header">
                        <span>Menu</span>
                        <button class="mobile-menu-close" id="mobileMenuClose">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                    <div class="mobile-menu-body">
                        <div class="mobile-menu-section">
                            <div class="mobile-profile-info">
                                <?php if(isset($_SESSION['foto']) && !empty($_SESSION['foto']) && file_exists('uploads/' . $_SESSION['foto'])): ?>
                                    <img src="uploads/<?php echo $_SESSION['foto']; ?>" alt="Profile" class="mobile-profile-photo">
                                <?php else: ?>
                                    <div class="avatar-circle-mobile">
                                        <?php echo strtoupper($initials); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="profile-details">
                                    <span class="profile-name"><?php echo $_SESSION['name']; ?></span>
                                    <span class="profile-role"><?php echo ucfirst($userRole); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mobile-menu-section">
                            <div class="mobile-date-time">
                                <i class="fa fa-calendar"></i>
                                <span><?php echo date('d M Y'); ?></span>
                            </div>
                        </div>
                        
                        <div class="mobile-menu-section">
                            <a href="profile.php" class="mobile-menu-item">
                                <i class="fa fa-user"></i>
                                <span>Profil</span>
                            </a>
                            <a href="#" class="mobile-menu-item logout-item" onclick="logout()">
                                <i class="fa fa-sign-out"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

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
        
        /* Utility classes */
        .d-none { display: none !important; }
        .d-md-none { display: block; }
        .d-md-flex { display: none; }
        .ml-2 { margin-left: 8px; }
        
        @media (min-width: 768px) {
            .d-md-none { display: none !important; }
            .d-md-flex { display: flex !important; }
        }
        
        .topbar {
            height: var(--topbar-height);
            background: var(--ipc-text-light);
            position: fixed;
            top: 0;
            right: 0;
            left: var(--sidebar-width);
            z-index: 999;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all var(--transition-speed) ease;
            font-family: 'Arial', sans-serif;
        }
        
        .sidebar.collapsed ~ .topbar {
            left: var(--sidebar-collapsed-width);
        }
        
        /* Left section */
        .topbar-left {
            display: flex;
            align-items: center;
            flex: 1;
            min-width: 0;
        }
        
        .btn-toggle {
            background: transparent;
            border: none;
            color: var(--ipc-text-dark);
            font-size: 1.2rem;
            cursor: pointer;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            transition: all 0.2s;
            flex-shrink: 0;
        }
        
        .btn-toggle:hover {
            background: var(--ipc-gray-light);
            color: var(--ipc-primary);
        }
        
        .welcome-text {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        
        .greeting {
            font-size: 0.8rem;
            color: #666;
            font-weight: normal;
        }
        
        .username {
            font-size: 1rem;
            font-weight: 600;
            color: var(--ipc-text-dark);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        /* Right section */
        .topbar-right {
            display: flex;
            align-items: center;
            position: relative;
        }
        
        .desktop-items {
            display: flex;
            align-items: center;
        }
        
        .topbar-item {
            position: relative;
            margin-left: 10px;
        }
        
        .topbar-divider {
            height: 30px;
            width: 1px;
            background-color: var(--ipc-gray);
            margin: 0 10px;
        }
        
        .date-time {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }
        
        .current-date {
            font-size: 0.8rem;
            color: #666;
            white-space: nowrap;
        }
        
        /* Mobile Menu Button */
        .mobile-menu-btn {
            background: var(--ipc-gray-light);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--ipc-text-dark);
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .mobile-menu-btn:hover {
            background: var(--ipc-gray);
        }
        
        /* Profile Images */
        .profile-photo,
        .profile-header-photo,
        .mobile-profile-photo {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--ipc-gray-light);
        }
        
        .profile-header-photo {
            width: 50px;
            height: 50px;
        }
        
        .mobile-profile-photo {
            width: 50px;
            height: 50px;
        }
        
        .avatar-circle,
        .avatar-circle-large,
        .avatar-circle-mobile {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--ipc-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .avatar-circle-large {
            width: 50px;
            height: 50px;
            font-size: 1.2rem;
        }
        
        .avatar-circle-mobile {
            width: 50px;
            height: 50px;
            font-size: 1.2rem;
        }
        
        /* Profile dropdown (Desktop) */
        .profile-toggle {
            display: flex;
            align-items: center;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 30px;
            background: var(--ipc-gray-light);
            transition: all 0.2s;
        }
        
        .profile-toggle:hover {
            background: var(--ipc-gray);
        }
        
        .avatar-wrapper {
            margin-right: 10px;
        }
        
        .profile-info {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
            margin-right: 8px;
            min-width: 0;
        }
        
        .profile-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--ipc-text-dark);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .profile-role {
            font-size: 0.75rem;
            color: #666;
        }
        
        .profile-menu {
            position: absolute;
            top: 100%;
            right: 0;
            width: 280px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            margin-top: 15px;
            visibility: hidden;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s;
            overflow: hidden;
            z-index: 100;
        }
        
        .profile-menu.show {
            visibility: visible;
            opacity: 1;
            transform: translateY(0);
        }
        
        .dropdown-header {
            padding: 20px;
            background: var(--ipc-gray-light);
            border-bottom: 1px solid var(--ipc-gray);
        }
        
        .profile-header-info {
            display: flex;
            align-items: center;
        }
        
        .profile-header-text {
            margin-left: 15px;
            display: flex;
            flex-direction: column;
        }
        
        .profile-header-name {
            font-weight: 600;
            color: var(--ipc-text-dark);
            font-size: 1rem;
        }
        
        .profile-header-role {
            font-size: 0.85rem;
            color: #666;
            margin-top: 2px;
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: var(--ipc-text-dark);
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .dropdown-item:hover {
            background: var(--ipc-gray-light);
            text-decoration: none;
            color: var(--ipc-text-dark);
        }
        
        .dropdown-item i {
            width: 20px;
            margin-right: 12px;
            font-size: 1rem;
        }
        
        .dropdown-divider {
            height: 1px;
            background: var(--ipc-gray-light);
            margin: 5px 0;
        }
        
        .logout-btn {
            color: var(--ipc-danger);
        }
        
        .logout-btn:hover {
            color: var(--ipc-danger);
        }
        
        /* Mobile Menu Overlay */
        .mobile-menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1050;
            visibility: hidden;
            opacity: 0;
            transition: all 0.3s ease;
        }
        
        .mobile-menu-overlay.active {
            visibility: visible;
            opacity: 1;
        }
        
        .mobile-menu-content {
            position: absolute;
            top: 0;
            right: 0;
            width: 320px;
            max-width: 85vw;
            height: 100vh;
            background: white;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            overflow-y: auto;
        }
        
        .mobile-menu-overlay.active .mobile-menu-content {
            transform: translateX(0);
        }
        
        .mobile-menu-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: var(--ipc-primary);
            color: white;
        }
        
        .mobile-menu-header span {
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .mobile-menu-close {
            background: transparent;
            border: none;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .mobile-menu-body {
            padding: 0;
        }
        
        .mobile-menu-section {
            border-bottom: 1px solid var(--ipc-gray-light);
            padding: 15px 20px;
        }
        
        .mobile-profile-info {
            display: flex;
            align-items: center;
        }
        
        .profile-details {
            margin-left: 15px;
            display: flex;
            flex-direction: column;
        }
        
        .profile-details .profile-name {
            font-weight: 600;
            color: var(--ipc-text-dark);
            margin-bottom: 2px;
        }
        
        .profile-details .profile-role {
            font-size: 0.9rem;
            color: #666;
        }
        
        .mobile-date-time {
            display: flex;
            align-items: center;
            color: #666;
        }
        
        .mobile-date-time i {
            margin-right: 10px;
            color: var(--ipc-primary);
        }
        
        .mobile-menu-item {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: var(--ipc-text-dark);
            text-decoration: none;
            border-bottom: 1px solid var(--ipc-gray-light);
            transition: background 0.2s;
        }
        
        .mobile-menu-item:hover {
            background: var(--ipc-gray-light);
            text-decoration: none;
            color: var(--ipc-text-dark);
        }
        
        .mobile-menu-item i {
            width: 20px;
            margin-right: 15px;
            color: var(--ipc-primary);
        }
        
        .logout-item {
            color: var(--ipc-danger);
        }
        
        .logout-item i {
            color: var(--ipc-danger);
        }
        
        .logout-item:hover {
            color: var(--ipc-danger);
        }
        
        /* Responsive styles */
        @media (max-width: 992px) {
            .topbar {
                padding: 0 15px;
            }
            
            .welcome-text .username {
                max-width: 120px;
            }
        }
        
        @media (max-width: 768px) {
            .topbar {
                left: var(--sidebar-collapsed-width);
                padding: 0 15px;
            }
            
            .sidebar.expanded ~ .topbar {
                left: var(--sidebar-width);
            }
            
            .welcome-text {
                display: none;
            }
            
            .desktop-items {
                display: none;
            }
            
            .mobile-menu-btn {
                display: flex;
            }
        }
        
        @media (max-width: 576px) {
            .topbar {
                padding: 0 10px;
            }
            
            .btn-toggle {
                margin-right: 10px;
            }
            
            .mobile-menu-content {
                width: 300px;
                max-width: 90vw;
            }
        }
        
        @media (max-width: 480px) {
            .topbar {
                padding: 0 8px;
            }
            
            .mobile-menu-content {
                width: 280px;
                max-width: 95vw;
            }
            
            .mobile-menu-section {
                padding: 12px 15px;
            }
            
            .mobile-menu-item {
                padding: 12px 15px;
            }
        }
        
        /* Landscape orientation for mobile */
        @media (max-height: 500px) and (orientation: landscape) {
            .mobile-menu-content {
                width: 300px;
                max-width: 50vw;
            }
        }
        
        /* High DPI displays */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .avatar-circle,
            .profile-photo {
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
            }
        }
        
        /* Focus states for accessibility */
        .btn-toggle:focus,
        .mobile-menu-btn:focus,
        .profile-toggle:focus {
            outline: 2px solid var(--ipc-primary);
            outline-offset: 2px;
        }
        
        .mobile-menu-close:focus {
            outline: 2px solid white;
            outline-offset: 2px;
        }
        
        /* Animation improvements */
        .dropdown-item,
        .mobile-menu-item {
            position: relative;
            overflow: hidden;
        }
        
        .dropdown-item::before,
        .mobile-menu-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.5s;
        }
        
        .dropdown-item:hover::before,
        .mobile-menu-item:hover::before {
            left: 100%;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggleTop = document.getElementById('sidebarToggleTop');
            const sidebar = document.getElementById('sidebar');
            const topbar = document.getElementById('topbar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const contentWrapper = document.querySelector('.content-wrapper');
            
            // Mobile menu elements
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
            const mobileMenuClose = document.getElementById('mobileMenuClose');
            
            // Sidebar toggle functionality
            if (sidebarToggleTop && sidebar) {
                sidebarToggleTop.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    if (window.innerWidth <= 768) {
                        // Mobile behavior: expand/collapse with overlay
                        sidebar.classList.toggle('expanded');
                        if (sidebarOverlay) {
                            sidebarOverlay.classList.toggle('active');
                        }
                    } else {
                        // Desktop behavior: collapse to mini sidebar
                        sidebar.classList.toggle('collapsed');
                        if (contentWrapper) {
                            contentWrapper.classList.toggle('expanded');
                        }
                    }
                    
                    // Save state to localStorage (for desktop only)
                    if (window.innerWidth > 768) {
                        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
                    }
                });
            }
            
            // Mobile menu functionality
            if (mobileMenuToggle && mobileMenuOverlay) {
                mobileMenuToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    mobileMenuOverlay.classList.add('active');
                    document.body.style.overflow = 'hidden';
                });
            }
            
            if (mobileMenuClose) {
                mobileMenuClose.addEventListener('click', function(e) {
                    e.preventDefault();
                    closeMobileMenu();
                });
            }
            
            if (mobileMenuOverlay) {
                mobileMenuOverlay.addEventListener('click', function(e) {
                    if (e.target === mobileMenuOverlay) {
                        closeMobileMenu();
                    }
                });
            }
            
            function closeMobileMenu() {
                mobileMenuOverlay.classList.remove('active');
                document.body.style.overflow = '';
            }
            
            // Profile dropdown functionality (Desktop)
            const profileDropdown = document.getElementById('profileDropdown');
            const profileMenu = document.querySelector('.profile-menu');
            
            if (profileDropdown && profileMenu) {
                profileDropdown.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    profileMenu.classList.toggle('show');
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (profileMenu.classList.contains('show') && 
                        !profileDropdown.contains(e.target) && 
                        !profileMenu.contains(e.target)) {
                        profileMenu.classList.remove('show');
                    }
                });
            }
            
            // Handle window resize
            window.addEventListener('resize', function() {
                // Close mobile menus when switching to desktop
                if (window.innerWidth > 768) {
                    closeMobileMenu();
                }
                
                // Reset sidebar state on resize
                if (window.innerWidth <= 768 && sidebar) {
                    sidebar.classList.remove('expanded');
                    if (sidebarOverlay) {
                        sidebarOverlay.classList.remove('active');
                    }
                }
            });
            
            // Touch events for better mobile experience
            let touchStartY = 0;
            let touchEndY = 0;
            
            if (mobileMenuOverlay) {
                const mobileMenuContent = mobileMenuOverlay.querySelector('.mobile-menu-content');
                
                mobileMenuContent.addEventListener('touchstart', function(e) {
                    touchStartY = e.changedTouches[0].screenY;
                });
                
                mobileMenuContent.addEventListener('touchend', function(e) {
                    touchEndY = e.changedTouches[0].screenY;
                    
                    // Close menu if swiped right
                    if (touchStartY - touchEndY < -50) {
                        closeMobileMenu();
                    }
                });
            }
            
            // Keyboard navigation support
            document.addEventListener('keydown', function(e) {
                // Close mobile menus with Escape key
                if (e.key === 'Escape') {
                    if (mobileMenuOverlay && mobileMenuOverlay.classList.contains('active')) {
                        closeMobileMenu();
                    }
                    if (profileMenu && profileMenu.classList.contains('show')) {
                        profileMenu.classList.remove('show');
                    }
                }
            });
        });
        
        // Logout functionality
        function logout() {
            if (confirm('Apakah Anda yakin ingin keluar dari sistem?')) {
                window.location.href = '../../logout.php';
            }
        }
        
        // Utility function to handle orientation changes
        window.addEventListener('orientationchange', function() {
            setTimeout(function() {
                // Recalculate positions after orientation change
                const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
                
                if (mobileMenuOverlay && mobileMenuOverlay.classList.contains('active')) {
                    mobileMenuOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            }, 100);
        });
    </script>
    
    <?php
}
?>
