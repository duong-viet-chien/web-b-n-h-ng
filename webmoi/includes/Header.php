 <?php
// Kết nối database và load các hàm dùng chung
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Lấy danh sách danh mục nếu chưa có
if (!isset($categories)) {
    $categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Mua sắm công nghệ chính hãng</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/index.css">    
    <link rel="stylesheet" href="css/products.css">
    <link rel="stylesheet" href="css/product_detail.css">
    <link rel="stylesheet" href="css/cart.css">
    <link rel="stylesheet" href="css/promotions.css">
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="css/register.css">
    <link rel="stylesheet" href="css/anmin.css">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

</head>
<body>
    
 <header>
  <!-- HEADER TOP -->
  <div class="header-top">
      <div class="container">
          <div><i class="fa-solid fa-phone"></i> Hotline: 1900-8189</div>
          <div><i class="fa-solid fa-envelope"></i> Email: duongvietchien2005@gmail.com</div>
          <div>
              <?php if (is_logged_in()): ?>
                  <a href="#">Xin chào, <?php echo $_SESSION['username']; ?></a>
                  <a href="logout.php">Đăng xuất</a>
              <?php else: ?>
                  <a href="login.php">Đăng nhập</a>
                  <a href="register.php">Đăng ký</a>
              <?php endif; ?>
          </div>
      </div>
  </div>

  <!-- HEADER MAIN -->
  <div class="header-main">
      <div class="container header-content">
          <a href="index.php" class="logo-link">
              <img src="images/logo/logo_web.png" alt="Logo" class="logo">
              <h1 class="site-name"><?php echo SITE_NAME; ?></h1>
          </a>

          <div class="header-search">
              <form action="products.php" method="GET" class="search-form">
                  <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..." 
                         value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                  <button type="submit">🔍 Tìm kiếm</button>
              </form>
          </div>

          <div class="header-actions">
              <a href="cart.php" class="header-action">
                  <div class="header-action-icon">🛒
                      <?php if (get_cart_count() > 0): ?>
                          <span class="header-action-badge"><?php echo get_cart_count(); ?></span>
                      <?php endif; ?>
                  </div>
                  <div class="header-action-text">
                      <small>Giỏ hàng</small>
                      <strong><?php echo get_cart_count(); ?> sản phẩm</strong>
                  </div>
              </a>
          </div>
      </div>
  </div>
</header>
<header>
        <!-- NAVIGATION -->
        <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
          <nav>
            <div class="container">
              <ul>
                  <li><a href="index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>"><i class="fa-solid fa-house"></i> Trang chủ</a></li>

                  <li><a href="products.php" class="<?= $current_page == 'products.php' ? 'active' : '' ?>"><i class="fa-solid fa-box-open"></i> Sản phẩm</a></li>

                  <li>
                      <a href="#" class="<?= $current_page == 'products.php' && isset($_GET['category']) ? 'active' : '' ?>">
                          <i class="fa-solid fa-list"></i> Danh mục <i class="fa-solid fa-chevron-down"></i>
                      </a>
                      <div class="mega-menu">
                          <?php 
                          $categories->data_seek(0);
                          while ($cat = $categories->fetch_assoc()): 
                          ?>
                              <a href="products.php?category=<?= $cat['id']; ?>">
                                  <?= htmlspecialchars($cat['name']); ?>
                              </a>
                          <?php endwhile; ?>
                      </div>
                  </li>

                  <li><a href="promotions.php" class="<?= $current_page == 'promotions.php' ? 'active' : '' ?>"><i class="fa-solid fa-fire"></i> Khuyến mãi</a></li>

                  <li><a href="news.php" class="<?= $current_page == 'news.php' ? 'active' : '' ?>"><i class="fa-solid fa-newspaper"></i> Tin tức</a></li>
              </ul>
            </div>
          </nav>

    </header>

    <!-- THÔNG BÁO -->
    <?php
    $message = get_message();
    if ($message): ?>
        <div class="container mt-2">
            <div class="alert alert-<?php echo $message['type'] === 'success' ? 'success' : 'error'; ?>">
                <?php echo $message['message']; ?>
            </div>
        </div>
    <?php endif; ?>
    
   <script>
let lastScrollTop = 0;
const headerTop = document.querySelector('.header-top');

window.addEventListener('scroll', () => {
  const scrollTop = window.scrollY || document.documentElement.scrollTop;

  if (scrollTop > lastScrollTop && scrollTop > 80) {
    // Cuộn xuống → ẩn header-top
    headerTop.classList.add('hidden');
  } else {
    // Cuộn lên → hiện lại header-top
    headerTop.classList.remove('hidden');
  }

  lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
});

</script>

</body>
</html>