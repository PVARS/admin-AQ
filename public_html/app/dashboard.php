<?php

//Common setting
require_once ('config.php');
require_once ('lib.php');

//Initialization
$func_id = 'dashboard';

session_start();

// current datetime
$current_day = getDateVn();
//Get param
$param = getParam();

$role = $_SESSION['role'] ?? '';

//Connect DB
$con = openDB();

if (!isset($_SESSION['loginId'])){
    header('location: login.php');
    exit();
}
// total article count
$sql = "";
$sql .= "SELECT COUNT(id)      ";
$sql .= " AS count_news        ";
$sql .= "FROM news             ";
$query = pg_query($con, $sql);
if(!$query){
    echo pg_last_error($con);
    exit();
}
while ($number_news = pg_fetch_row($query)){
    $count_news = $number_news[0];
}

//total post of new day
$sql = "";
$sql .= "SELECT COUNT(id)                           ";
$sql .= " AS post_new_day                           ";
$sql .= " FROM news                                 ";
$sql .= " WHERE createdate = '".$current_day."'       ";

$res = pg_query($con, $sql);
if(!$res){
    echo pg_last_error($con);
    exit();
}
while ($number_day = pg_fetch_row($res)){
    $count_news_day = $number_day[0];
}

// number of accounts
$sql = "";
$sql .= "SELECT COUNT(id)      ";
$sql .= " AS count_users        ";
$sql .= "FROM users             ";
$resu = pg_query($con, $sql);
if(!$query){
    echo pg_last_error($con);
    exit();
}
while ($number_users = pg_fetch_row($resu)){
    $count_users = $number_users[0];
}

//-----------------------------------------------------------
// HTML
//-----------------------------------------------------------
$titleHTML = '';
$cssHTML = '';
$scriptHTML = '';

echo <<<EOF
<!DOCTYPE html>
<html>
<head>
EOF;

//Meta CSS
include ($TEMP_APP_META_PATH);

echo <<<EOF
</head>
<body class="hold-transition sidebar-mini layout-fixed" id="{$func_id}">
    <div class="wrapper">
EOF;

//Preloader
include ($TEMP_APP_PRELOADER_PATH);

//Header
include ($TEMP_APP_HEADER_PATH);

//Menu
if ($role == '1'){
    include ($TEMP_APP_MENUSYSTEM_PATH);
} else {
    include ($TEMP_APP_MENU_PATH);
}

echo <<<EOF
<div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Thống kê</h1>
                        </div>
                        <!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">Trang chủ</a></li>
                                <li class="breadcrumb-item active">Thống kê</li>
                            </ol>
                        </div>
                        <!-- /.col -->
                    </div>
                    <!-- /.row -->
                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- /.content-header -->

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <!-- Small boxes (Stat box) -->
                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <!-- small box -->
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>$count_news</h3>

                                    <p>Tổng bài viết</p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-bag"></i>
                                </div>
                                <a href="list-news.php" class="small-box-footer">Xem thêm <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        <!-- ./col -->
                        <div class="col-lg-3 col-6">
                            <!-- small box -->
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>$count_news_day</h3>

                                    <p>Bài đăng trong ngày</p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-stats-bars"></i>
                                </div>
                                <a href="#" class="small-box-footer">Xem thêm <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        <!-- ./col -->
                        <div class="col-lg-3 col-6">
                            <!-- small box -->
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>$count_users</h3>

                                    <p>Số lượng tài khoản</p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-person-add"></i>
                                </div>
                                <a href="list-users.php" class="small-box-footer">Xem thêm <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        <!-- ./col -->
                        <div class="col-lg-3 col-6">
                            <!-- small box -->
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>%</h3>

                                    <p>Bài viết theo danh mục</p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-pie-graph"></i>
                                </div>
                                <a href="#" class="small-box-footer">Xem thêm <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        <!-- ./col -->
                    </div>
                    <!-- /.row -->
                    <!-- Main row -->
                    <div class="row">
                        <div class="col-sm-12">
                            <h1 class="m-0" style="font-size: 1.8rem">Danh mục có bài đăng hôm nay</h1>
                        </div>
                        <div class="col-sm-12" style="margin-top: 20px;">
                            <div id="accordion">
                                <div class="card">
                                    <div class="card-header title-collapse" id="headingOne">
                                        <h5 class="mb-0 col-6">
                                            <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                                tin chuyển nhượng
                                            </button>
                                        </h5>
                                        <h5 class="count-news">
                                            Số lượng - 100
                                        </h5>
                                    </div>

                                    <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
                                        <div class="card-body table-responsive p-0">
                                            <table class="table table-hover text-nowrap table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 10%;">STT</th>
                                                        <th style="width: 30%;">Tiêu đề</th>
                                                        <th style="width: 20%;">Người đăng</th>
                                                        <th style="text-align: center; width: 20%;">Thời gian</th>
                                                        <th colspan="2"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td style="width: 20%;">1</td>
                                                        <td style="width: 30%;">Chỉ trích bủa vây, Arteta gặp nạn lớn ở Arsenal </td>
                                                        <td style="width: 20%;">Lê Văn Lư</td>
                                                        <td style="text-align: center; width: 20%;">15:00</td>
                                                        <td style="text-align: center; width: 5%;">
                                                            <form action="" method="POST">
                                                                <a href="javascript:void(0)" class="btn btn-block btn-primary btn-sm"><i class="fas fa-edit"></i></a>
                                                            </form>
                                                        </td>
                                                        <td style="text-align: center; width: 5%;">
                                                            <form action="" method="POST">
                                                                <a href="javascript:void(0)" class="btn btn-block btn-danger btn-sm"><i class="fas fa-trash"></i></a>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <h1 class="m-0" style="font-size: 1.8rem">Bài viết có nhiều lượt xem nhất</h1>
                        </div>
                        <div class="col-sm-12" style="margin-top: 20px;">
                            <div class="card-body table-responsive p-0">
                                <table class="table table-hover text-nowrap table-bordered" style="background-color: #FFFFFF;">
                                    <thead style="background-color: #17A2B8;">
                                        <tr>
                                            <th style="width: 5%;" class="text-th">STT</th>
                                            <th style="width: 35%;" class="text-th">Tiêu đề</th>
                                            <th style="width: 20%;" class="text-th">Người đăng</th>
                                            <th style="text-align: center; width: 20%;" class="text-th">Ngày đăng</th>
                                            <th style="text-align: center; width: 20%;" class="text-th">Lượt xem</th>
                                            <th colspan="2"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style="width: 5%;">1</td>
                                            <td style="width: 35%;">Chỉ trích bủa vây, Arteta gặp nạn lớn ở Arsenal </td>
                                            <td style="width: 20%;">Lê Văn Lư</td>
                                            <td style="text-align: center; width: 20%;">07/04/2021</td>
                                            <td style="text-align: center; width: 20%;">100</td>
                                            <td style="text-align: center; width: 5%;">
                                                <form action="" method="POST">
                                                    <a href="javascript:void(0)" class="btn btn-block btn-primary btn-sm"><i class="fas fa-edit"></i></a>
                                                </form>
                                            </td>
                                            <td style="text-align: center; width: 5%;">
                                                <form action="" method="POST">
                                                    <a href="javascript:void(0)" class="btn btn-block btn-danger btn-sm"><i class="fas fa-trash"></i></a>
                                                </form>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- /.row (main row) -->
                </div>
                <!-- /.container-fluid -->
            </section>
            <!-- /.content -->
        </div>
EOF;

//Footer
include ($TEMP_APP_FOOTER_PATH);
//Meta JS
include ($TEMP_APP_METAJS_PATH);
echo <<<EOF
    </div>
</body>
</html>
EOF;

?>

