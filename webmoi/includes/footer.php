<?php
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Mua sắm công nghệ chính hãng</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
<style>
    /* FOOTER LAYOUT */
footer {
    background: #111;
    color: #ddd;
    padding: 50px 0 20px;
    margin-top: 40px;
    border-top: 3px solid #ff5252;
}

.footer-content {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 30px;
}

.footer-section h3 {
    color: #fff;
    font-size: 18px;
    margin-bottom: 15px;
}

.footer-section ul {
    list-style: none;
}

.footer-section ul li {
    margin-bottom: 8px;
}

.footer-section ul li a {
    color: #bbb;
    text-decoration: none;
    transition: 0.3s ease;
}

.footer-section ul li a:hover {
    color: #fff;
}

/* SOCIAL ICONS */
.social-links {
    display: flex;
    gap: 15px;
    align-items: center;
    margin-top: 10px;
}

.social-link {
    color: #fff;
    font-size: 20px;
    display: inline-flex;
    transition: 0.3s;
}

.social-link img {
    width: 26px;
    height: 26px;
    filter: brightness(0) invert(1);
    transition: 0.3s;
}

.social-link:hover,
.social-link:hover img {
    transform: scale(1.1);
    filter: brightness(1);
}

/* RESPONSIVE */
@media (max-width: 900px) {
    .footer-content {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 600px) {
    .footer-content {
        grid-template-columns: 1fr;
        text-align: center;
    }
    .social-links {
        justify-content: center;
    }
}

</style>
</head>
<body>


  <!-- FOOTER -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><?php echo SITE_NAME; ?></h3>
                    <p>Hệ thống bán lẻ công nghệ uy tín hàng đầu Việt Nam. Chuyên cung cấp các sản phẩm công nghệ chính hãng với giá tốt nhất.</p>
                    <br>
                    <h3> MẠNG XÃ HỘI </h3>
                    <div class="social-links">
                        <a href="https://zalo.me/" class="social-link" title="Zalo"><img src="images/icons/zalo.png"  alt="Zalo"></a>
                        <a href="https://facebook.com/" class="social-link" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://instagram.com/" class="social-link" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="https://tiktok.com/" class="social-link" title="TikTok"><i class="fab fa-tiktok"></i></a>
                    </div>

                </div>
                
                <div class="footer-section">
                    <h3>Hỗ trợ khách hàng</h3>
                    <ul>
                        <li><a href="#">Chính sách bảo hành</a></li>
                        <li><a href="#">Chính sách đổi trả</a></li>
                        <li><a href="#">Hướng dẫn mua hàng</a></li>
                        <li><a href="#">Phương thức thanh toán</a></li>
                        <li><a href="#">Câu hỏi thường gặp</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Về chúng tôi</h3>
                    <ul>
                        <li><a href="#">Giới thiệu công ty</a></li>
                        <li><a href="#">Tuyển dụng</a></li>
                        <li><a href="#">Tin tức</a></li>
                        <li><a href="#">Hệ thống cửa hàng</a></li>
                        <li><a href="#">Liên hệ</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Liên hệ</h3>
                    <ul>
                        <li>
            📍 <a 
                href="https://www.google.com/maps/search/?api=1&query=ngõ+106,+Hoàng+Quốc+Việt,+TP.Hà+Nội" 
                target="_blank" 
                rel="noopener noreferrer"
            >
                Địa chỉ: ngõ 106, Hoàng Quốc Việt, TP.Hà Nội
            </a>
        </li>
                        
                        <li>📞 Hotline: 1900-8198</li>
                        <li>📧 Email: duongvietchien2005@gmail.com</li>
                        <li>🕐 Giờ làm việc: 8:00 - 22:00</li>
                    </ul>
                    
                </div>
            </div>
        </div>
    </footer>
    </body>

    <script>
        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });

        // Header sticky effect
        let lastScroll = 0;
        const header = document.querySelector('header');
        
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > 100) {
                header.style.boxShadow = '0 5px 30px rgba(0,0,0,0.15)';
            } else {
                header.style.boxShadow = '0 2px 20px rgba(0,0,0,0.08)';
            }
            
            lastScroll = currentScroll;
        });

        // Auto dismiss alerts
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    </script>
</html>
