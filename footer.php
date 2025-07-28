<?php
/**
 * Footer file for Aplikasi Pelaporan Bahan Bakar Minyak PT IPC Terminal Petikemas
 */
?>

<footer class="modern-footer">
    <div class="container">
        <!-- Main Footer Content -->
        <div class="footer-main">
            <div class="row g-4">
                <!-- Company Info -->
                <div class="col-lg-4 col-md-6">
                    <div class="footer-section">
                        <div class="footer-logo mb-3">
                            <img src="images/logo.png" alt="PT IPC Terminal Petikemas" class="footer-logo-img">
                            <span class="footer-brand">PT IPC Terminal Petikemas</span>
                        </div>
                        <p class="footer-description">
                            Sistem Informasi Monitoring Bahan Bakar (SIMOB) untuk efisiensi operasional pelabuhan terdepan di Indonesia.
                        </p>
                        <div class="footer-address">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Jalan Mayor Memet Sastrawirya no 2 Boom Baru Palembang</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6">
                    <div class="footer-section">
                        <h6 class="footer-title">Navigasi</h6>
                        <ul class="footer-links">
                            <li><a href="#home">Beranda</a></li>
                            <li><a href="#about">Tentang</a></li>
                            <li><a href="#login">Login</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Services -->
                <div class="col-lg-3 col-md-6">
                    <div class="footer-section">
                        <h6 class="footer-title">Layanan</h6>
                        <ul class="footer-links">
                            <li><a href="#login">Monitoring Real-time</a></li>
                            <li><a href="#login">Pelaporan Digital</a></li>
                            <li><a href="#login">Analisis Data</a></li>
                            <li><a href="#login">Support 24/7</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="col-lg-3 col-md-6">
                    <div class="footer-section">
                        <h6 class="footer-title">Hubungi Kami</h6>
                        <div class="footer-contact">
                            <div class="contact-item">
                                <i class="fas fa-phone"></i>
                                <span>+62 21 4301080</span>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-envelope"></i>
                                <span>info@ipcterminal.co.id</span>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-globe"></i>
                                <span>www.indonesiaport.co.id</span>
                            </div>
                        </div>
                        
                        <!-- Social Media -->
                        <div class="footer-social mt-3">
                            <a href="#" class="social-link" aria-label="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://x.com/ipctpk?t=CsqDEL9rocggpU3QGfdk0Q&s=09" class="social-link" aria-label="Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="social-link" aria-label="LinkedIn">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <a href="https://www.instagram.com/ipctpk?igsh=MWJpMWd1MnpzamwyaA==" class="social-link" aria-label="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="footer-copyright">
                        &copy; <?php echo date('Y'); ?> PT IPC Terminal Petikemas. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6">
                    <div class="footer-legal">
                        <a href="#">Privacy Policy</a>
                        <span class="separator">|</span>
                        <a href="#">Terms of Service</a>
                        <span class="separator">|</span>
                        <a href="#">Support</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
/* Modern Footer Styles */
.modern-footer {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
    color: #ffffff;
    position: relative;
    overflow: hidden;
}

.modern-footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100"><polygon fill="rgba(255,255,255,0.02)" points="0,0 1000,20 1000,100 0,80"/></svg>');
    background-size: cover;
    pointer-events: none;
}

.footer-main {
    padding: 4rem 0 2rem 0;
    position: relative;
    z-index: 2;
}

.footer-section {
    height: 100%;
}

/* Footer Logo */
.footer-logo {
    display: flex;
    align-items: center;
    gap: 12px;
}

.footer-logo-img {
    width: 40px;
    height: 40px;
    object-fit: contain;
}

.footer-brand {
    font-size: 1.1rem;
    font-weight: 600;
    color: #ffffff;
}

.footer-description {
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.9rem;
    line-height: 1.6;
    margin-bottom: 1rem;
}

.footer-address {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.85rem;
}

.footer-address i {
    color: #ff8200;
    margin-top: 2px;
    flex-shrink: 0;
}

/* Footer Titles */
.footer-title {
    font-size: 1rem;
    font-weight: 600;
    color: #ffffff;
    margin-bottom: 1.5rem;
    position: relative;
    padding-bottom: 8px;
}

.footer-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 30px;
    height: 2px;
    background: linear-gradient(90deg, #ff8200, #00a651);
    border-radius: 1px;
}

/* Footer Links */
.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 0.75rem;
}

.footer-links a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    position: relative;
}

.footer-links a:hover {
    color: #ff8200;
    padding-left: 8px;
}

.footer-links a::before {
    content: '';
    position: absolute;
    left: -15px;
    top: 50%;
    transform: translateY(-50%);
    width: 0;
    height: 1px;
    background: #ff8200;
    transition: width 0.3s ease;
}

.footer-links a:hover::before {
    width: 10px;
}

/* Contact Items */
.footer-contact {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 10px;
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.9rem;
}

.contact-item i {
    color: #ff8200;
    width: 16px;
    text-align: center;
    flex-shrink: 0;
}

/* Social Media */
.footer-social {
    display: flex;
    gap: 12px;
}

.social-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.social-link:hover {
    background: #ff8200;
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255, 130, 0, 0.3);
}

/* Footer Bottom */
.footer-bottom {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding: 1.5rem 0;
    position: relative;
    z-index: 2;
}

.footer-copyright {
    margin: 0;
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.85rem;
}

.footer-legal {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 8px;
    font-size: 0.85rem;
}

.footer-legal a {
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-legal a:hover {
    color: #ff8200;
}

.footer-legal .separator {
    color: rgba(255, 255, 255, 0.3);
}

/* Responsive Design */
@media (max-width: 768px) {
    .footer-main {
        padding: 3rem 0 1.5rem 0;
    }
    
    .footer-logo {
        justify-content: center;
        text-align: center;
        flex-direction: column;
        gap: 8px;
    }
    
    .footer-section {
        text-align: center;
        margin-bottom: 2rem;
    }
    
    .footer-title::after {
        left: 50%;
        transform: translateX(-50%);
    }
    
    .footer-social {
        justify-content: center;
    }
    
    .footer-legal {
        justify-content: center;
        margin-top: 1rem;
    }
    
    .footer-copyright {
        text-align: center;
    }
    
    .footer-address {
        justify-content: center;
        text-align: center;
    }
    
    .contact-item {
        justify-content: center;
    }
}

@media (max-width: 576px) {
    .footer-legal {
        flex-direction: column;
        gap: 4px;
    }
    
    .footer-legal .separator {
        display: none;
    }
}
</style>
