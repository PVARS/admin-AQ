<?php
// Get Css hover
$navs          = getCssOfMenu('mode') ?? array();
$navLinkActive = $navs['navLinkActive'] ?? '';
$navLinkOnlick = $navs['navLinkOnlick'] ?? 'info';

//Output HTML
print <<<EOF
<aside class="main-sidebar sidebar-dark-primary elevation-4">
<!-- Brand Logo -->
<a href="dashboard.php" class="brand-link">
    <img src="dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
    <span class="brand-text font-weight-light">Arsenal Quán</span>
</a>

<!-- Sidebar -->
<div class="sidebar">
    <!-- Sidebar user panel (optional) -->
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image" style="color: #fff; font-size: 140%;">
            <i class="nav-icon fas fa-user-circle"></i>
        </div>
        <div class="info">
            <a href="javascript:void(0)" class="d-block">{$_SESSION['fullname']}</a>
        </div>
    </div>

    <!-- Sidebar Menu -->
    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link nav-link-dashboard">
                    <i class="nav-icon fas fa-home"></i>
                    <p>Trang chủ</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="javascript:void(0)" class="nav-link nav-link-new">
                    <i class="nav-icon fas fa-newspaper"></i>
                    <p>
                        Quản lí bài viết
                        <i class="fas fa-angle-left right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="detail-news.php" class="nav-link nav-link-new-detail">
                            <i class="fas fa-plus-square nav-icon"></i>
                            <p>Thêm bài viết</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="list-news.php" class="nav-link nav-link-new-list">
                            <i class="fas fa-list-ul nav-icon"></i>
                            <p>Danh sách bài viết</p>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
        <a href="logout.php" style="position: absolute; bottom: 0; margin-bottom: 20px">
            <i class="fas fa-sign-out-alt nav-icon" style="font-size: 20px"></i>&nbsp
            Đăng xuất
        </a>
    </nav>
    <!-- /.sidebar-menu -->
</div>
<!-- /.sidebar -->
</aside>
EOF;
?>