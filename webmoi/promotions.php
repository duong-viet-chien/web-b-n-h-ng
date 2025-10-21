<?php
/**
 * FILE: promotions.php
 * MÔ TẢ: Trang khuyến mãi và ưu đãi đặc biệt
 */

require_once 'includes/config.php';
?>
 <?php include 'includes/Header.php';?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khuyến mãi - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="script.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
 <style>
 
  </style>
</head>
<body>
    <!-- BREADCRUMB -->
    <div class="breadcrumb">
        <div class="container">
            <ul>
                <li><a href="index.php">Trang chủ</a></li>
                <li>Khuyến mãi</li>
            </ul>
        </div>
    </div>
       <!-- Banner chính -->
                <div class="max-w-7xl mx-auto rounded-3xl overflow-hidden shadow-2xl">
                    <div class="banner-container flex flex-col lg:flex-row items-center bg-gradient-to-r from-red-700 to-pink-600 relative">

                    <!-- Ảnh nền -->
                    <img src="images/sliders/slider_9.png"
                        alt="Sản phẩm điện tử chủ đạo"
                        class="absolute inset-0 w-full h-full object-cover opacity-20 lg:opacity-30 z-0 transition duration-500 ease-in-out hover:scale-105">

                    <!-- Nội dung -->
                    <div class="w-full lg:w-1/3 flex flex-col items-center lg:items-start text-center lg:text-left text-white z-10 p-4 md:p-10 space-y-4 relative">
                        <h1 class="text-4xl md:text-6xl font-black uppercase leading-tight tracking-tighter">
                        <span class="block text-yellow-300">SIÊU</span>
                        <span class="block">FLASH SALE</span>
                        </h1>

                        <p class="text-xl md:text-2xl font-semibold bg-red-900/50 px-4 py-1 rounded-lg shadow-md">
                        GIẢM SỐC ĐẾN <span class="text-yellow-300 font-extrabold text-3xl">50%</span>
                        </p>

                        <div class="flex flex-col items-center lg:items-start space-y-2">
                        <p class="text-lg font-medium">Kết thúc sau:</p>
                        <div id="countdown" class="flex space-x-3 md:space-x-4 text-3xl md:text-4xl font-bold">
                            <div class="bg-white text-red-700 p-2 rounded-lg shadow-inner">00</div>
                            <div class="text-white">:</div>
                            <div class="bg-white text-red-700 p-2 rounded-lg shadow-inner">00</div>
                            <div class="text-white">:</div>
                            <div class="bg-white text-red-700 p-2 rounded-lg shadow-inner">00</div>
                        </div>
                        </div>
                            <a href="products.php" class="cta-btn">Mua ngay với mã giảm giá!</a>
                       <!-- <button class="cta-btn">MUA NGAY HÔM NAY!</button>-->
                    </div>

                    <div class="hidden lg:block w-full lg:w-2/3 h-full"></div>
                    </div>
                </div>

            <!-- KHUYẾN MÃI CỤ THỂ -->
            <h2 style="font-size: 2rem; margin: 50px 0 30px 0;">📋 Các chương trình khuyến mãi</h2>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-bottom: 50px;">
                <!-- Khuyến mãi 1 -->
                <div style="background: #fff; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.08); transition: var(--transition);" class="promo-card">
                    <div style="background: linear-gradient(135deg, #ff6b6b 0%, #ff8e8e 100%); color: #fff; padding: 30px; position: relative; overflow: hidden;">
                        <div style="position: absolute; top: -50px; right: -50px; font-size: 10rem; opacity: 0.1;">🎁</div>
                        <div style="position: relative; z-index: 1;">
                            <div style="font-size: 3rem; font-weight: 700; margin-bottom: 10px;">20%</div>
                            <h3 style="font-size: 1.3rem; margin: 0;">Giảm giá toàn bộ</h3>
                        </div>
                    </div>
                    <div style="padding: 30px;">
                        <h4 style="margin-bottom: 10px;">Smartphone & Tablet</h4>
                        <p style="color: #666; margin-bottom: 15px;">Giảm 20% cho tất cả sản phẩm điện thoại và máy tính bảng</p>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--border-color);">
                            <span style="font-size: 0.85rem; color: #999;">Hết hạn: 31/12/2025</span>
                            <a href="products.php?category=1" class="btn btn-primary" style="padding: 8px 20px;">Xem ngay</a>
                        </div>
                    </div>
                </div>

                <!-- Khuyến mãi 2 -->
                <div style="background: #fff; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.08); transition: var(--transition);" class="promo-card">
                    <div style="background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%); color: #fff; padding: 30px; position: relative; overflow: hidden;">
                        <div style="position: absolute; top: -50px; right: -50px; font-size: 10rem; opacity: 0.1;">🎊</div>
                        <div style="position: relative; z-index: 1;">
                            <div style="font-size: 3rem; font-weight: 700; margin-bottom: 10px;">Mua 2</div>
                            <h3 style="font-size: 1.3rem; margin: 0;">Tặng 1</h3>
                        </div>
                    </div>
                    <div style="padding: 30px;">
                        <h4 style="margin-bottom: 10px;">Phụ kiện công nghệ</h4>
                        <p style="color: #666; margin-bottom: 15px;">Mua 2 sản phẩm phụ kiện, tặng ngay 1 sản phẩm khác</p>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--border-color);">
                            <span style="font-size: 0.85rem; color: #999;">Hết hạn: 25/12/2025</span>
                            <a href="products.php?category=3" class="btn btn-success" style="padding: 8px 20px;">Xem ngay</a>
                        </div>
                    </div>
                </div>

                <!-- Khuyến mãi 3 -->
                <div style="background: #fff; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.08); transition: var(--transition);" class="promo-card">
                    <div style="background: linear-gradient(135deg, #ffc107 0%, #ffeb3b 100%); color: #333; padding: 30px; position: relative; overflow: hidden;">
                        <div style="position: absolute; top: -50px; right: -50px; font-size: 10rem; opacity: 0.1;">⭐</div>
                        <div style="position: relative; z-index: 1;">
                            <div style="font-size: 3rem; font-weight: 700; margin-bottom: 10px;">500K</div>
                            <h3 style="font-size: 1.3rem; margin: 0;">Voucher miễn phí</h3>
                        </div>
                    </div>
                    <div style="padding: 30px;">
                        <h4 style="margin-bottom: 10px;">Khách hàng mới</h4>
                        <p style="color: #666; margin-bottom: 15px;">Nhận voucher 500K cho đơn hàng đầu tiên</p>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--border-color);">
                            <span style="font-size: 0.85rem; color: #999;">Hết hạn: 30/12/2025</span>
                            <a href="register.php" class="btn btn-warning" style="padding: 8px 20px;">Đăng ký ngay</a>
                        </div>
                    </div>
                </div>

                <!-- Khuyến mãi 4 -->
                <div style="background: #fff; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.08); transition: var(--transition);" class="promo-card">
                    <div style="background: linear-gradient(135deg, #9c27b0 0%, #ba68c8 100%); color: #fff; padding: 30px; position: relative; overflow: hidden;">
                        <div style="position: absolute; top: -50px; right: -50px; font-size: 10rem; opacity: 0.1;">👑</div>
                        <div style="position: relative; z-index: 1;">
                            <div style="font-size: 3rem; font-weight: 700; margin-bottom: 10px;">30%</div>
                            <h3 style="font-size: 1.3rem; margin: 0;">Thành viên VIP</h3>
                        </div>
                    </div>
                    <div style="padding: 30px;">
                        <h4 style="margin-bottom: 10px;">Laptop & Máy tính</h4>
                        <p style="color: #666; margin-bottom: 15px;">Giảm 30% cho thành viên VIP + Tặng bảo hành mở rộng</p>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--border-color);">
                            <span style="font-size: 0.85rem; color: #999;">Hết hạn: 28/12/2025</span>
                            <a href="products.php?category=2" class="btn btn-primary" style="padding: 8px 20px;">Xem ngay</a>
                        </div>
                    </div>
                </div>

                <!-- Khuyến mãi 5 -->
                <div style="background: #fff; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.08); transition: var(--transition);" class="promo-card">
                    <div style="background: linear-gradient(135deg, #ff1744 0%, #f50057 100%); color: #fff; padding: 30px; position: relative; overflow: hidden;">
                        <div style="position: absolute; top: -50px; right: -50px; font-size: 10rem; opacity: 0.1;">🎯</div>
                        <div style="position: relative; z-index: 1;">
                            <div style="font-size: 2rem; font-weight: 700; margin-bottom: 10px;">Flash Sale</div>
                            <h3 style="font-size: 1.3rem; margin: 0;">Mỗi ngày</h3>
                        </div>
                    </div>
                    <div style="padding: 30px;">
                        <h4 style="margin-bottom: 10px;">Sản phẩm hạn chế</h4>
                        <p style="color: #666; margin-bottom: 15px;">Mỗi ngày 10h - 11h, giảm giá khủng sản phẩm được chọn</p>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--border-color);">
                            <span style="font-size: 0.85rem; color: #999;">Hàng ngày 10h</span>
                            <a href="products.php" class="btn btn-primary" style="padding: 8px 20px;">Tham gia</a>
                        </div>
                    </div>
                </div>

                <!-- Khuyến mãi 6 -->
                <div style="background: #fff; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.08); transition: var(--transition);" class="promo-card">
                    <div style="background: linear-gradient(135deg, #00bcd4 0%, #26c6da 100%); color: #fff; padding: 30px; position: relative; overflow: hidden;">
                        <div style="position: absolute; top: -50px; right: -50px; font-size: 10rem; opacity: 0.1;">🚚</div>
                        <div style="position: relative; z-index: 1;">
                            <div style="font-size: 2.5rem; font-weight: 700; margin-bottom: 10px;">MIỄN PHÍ</div>
                            <h3 style="font-size: 1.3rem; margin: 0;">Vận chuyển</h3>
                        </div>
                    </div>
                    <div style="padding: 30px;">
                        <h4 style="margin-bottom: 10px;">Toàn quốc</h4>
                        <p style="color: #666; margin-bottom: 15px;">Miễn phí ship cho đơn hàng từ 500K trở lên</p>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--border-color);">
                            <span style="font-size: 0.85rem; color: #999;">Vĩnh viễn</span>
                            <a href="products.php" class="btn btn-secondary" style="padding: 8px 20px;">Mua hàng</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MÃ GIẢM GIÁ -->
            <div style="background: #fff; padding: 40px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); margin-bottom: 50px;">
                <h2 style="font-size: 1.8rem; margin-bottom: 30px;">🎟️ Mã giảm giá khả dụng</h2>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                    <?php
                    $coupons = [
                        ['code' => 'WELCOME10', 'discount' => '10%', 'min' => '100K', 'expired' => '31/12/2025'],
                        ['code' => 'SUMMER20', 'discount' => '20%', 'min' => '500K', 'expired' => '30/12/2025'],
                        ['code' => 'FLASH50', 'discount' => '50K', 'min' => '1M', 'expired' => '25/12/2025'],
                        ['code' => 'VIPONLY', 'discount' => '30%', 'min' => 'VIP only', 'expired' => '28/12/2025'],
                        ['code' => 'NEWMEMBER', 'discount' => 'Miễn phí ship', 'min' => 'Khách mới', 'expired' => '30/12/2025'],
                        ['code' => 'CYBER30', 'discount' => '30%', 'min' => '300K', 'expired' => '26/12/2025'],
                    ];

                    foreach ($coupons as $coupon):
                    ?>
                        <div style="border: 2px dashed var(--primary-color); padding: 25px; border-radius: 10px; position: relative; overflow: hidden;">
                            <div style="position: absolute; top: -20px; right: -20px; width: 100px; height: 100px; background: rgba(255,107,107,0.1); border-radius: 50%;"></div>
                            <div style="position: relative; z-index: 1;">
                                <div style="font-size: 0.9rem; color: #999; margin-bottom: 10px;">Mã khuyến mãi</div>
                                <div style="background: var(--light-color); padding: 15px; border-radius: 8px; margin-bottom: 15px; font-weight: 700; font-size: 1.2rem; letter-spacing: 2px; text-align: center; color: var(--primary-color);">
                                    <?php echo $coupon['code']; ?>
                                </div>
                                <p style="margin: 10px 0; font-size: 0.95rem;">
                                    <strong>Ưu đãi:</strong> <?php echo $coupon['discount']; ?>
                                </p>
                                <p style="margin: 10px 0; font-size: 0.95rem;">
                                    <strong>Tối thiểu:</strong> <?php echo $coupon['min']; ?>
                                </p>
                                <p style="margin: 10px 0; font-size: 0.85rem; color: #999;">
                                    Hết hạn: <?php echo $coupon['expired']; ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- HƯỚNG DẪN -->
            <div style="background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%); padding: 40px; border-radius: 15px; border-left: 4px solid var(--primary-color);">
                <h2 style="font-size: 1.5rem; margin-bottom: 20px; color: var(--primary-color);">❓ Cách sử dụng mã giảm giá</h2>
                <ol style="padding-left: 30px; line-height: 2.2;">
                    <li><strong>Chọn sản phẩm</strong> mà bạn muốn mua từ cửa hàng</li>
                    <li><strong>Thêm vào giỏ hàng</strong> và tiến hành thanh toán</li>
                    <li><strong>Nhập mã khuyến mãi</strong> tại trang thanh toán</li>
                    <li><strong>Xác nhận</strong> và hoàn tất đơn hàng</li>
                    <li><strong>Hưởng ưu đãi</strong> ngay lập tức!</li>
                </ol>
            </div>
        </div>
    </section>

</body>

    <script>
        // Countdown timer
        function updateCountdown() {
            let countdownDate = new Date("2025-12-25T23:59:59").getTime();
            let countdownElement = document.getElementById('countdown');

            let x = setInterval(function() {
                let now = new Date().getTime();
                let distance = countdownDate - now;

                let days = Math.floor(distance / (1000 * 60 * 60 * 24));
                let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                let seconds = Math.floor((distance % (1000 * 60)) / 1000);

                countdownElement.textContent = hours + ":" + (minutes < 10 ? "0" : "") + minutes + ":" + (seconds < 10 ? "0" : "") + seconds;

                if (distance < 0) {
                    clearInterval(x);
                    countdownElement.textContent = "Đã kết thúc";
                }
            }, 1000);
        }

        updateCountdown();

        // Hover effect
        document.querySelectorAll('.promo-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px)';
                this.style.boxShadow = '0 15px 40px rgba(0,0,0,0.15)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 5px 20px rgba(0,0,0,0.08)';
            });
        });
    </script>
        <?php include 'includes/footer.php'; ?>
</html>