<?php

//Common setting
require_once ('config.php');
require_once ('lib.php');

//Initialization
$funcId = 'dashboard';

session_start();

//Connect DB
$con = openDB();

//Get param
$param = getParam();

$role = $_SESSION['role'] ?? '';

if (!isset($_SESSION['loginId'])){
    header('location: login.php');
    exit();
}

if (!empty(getDelDate($con, $_SESSION['loginId']))){
    header('location: block-page.php');
    exit();
}

if (checkStatusUser($con, $_SESSION['loginId']) == 'f'){
    header('location: block-page.php');
    exit();
}

//Get total news
$totalNews    = totalNews($con, $funcId);

//Get post of day
$totalPost    = totalPostDay($con, $funcId);

//Get total account
$totalAccount = totalAccount($con, $funcId);

//Get staticts current day
$htmlCategoryPostDay = '';
$htmlCategoryPostDay = getCategoryPostDay($con, $funcId);

//Get news with max views
$htmlNewsTopView = '';
$htmlNewsTopView = getNewsTopView($con,$funcId);

//-----------------------------------------------------------
// HTML
//-----------------------------------------------------------
$titleHTML = '';
$cssHTML = '';
$scriptHTML = <<< EOF
<script>
$(function() {
  $('.editNews').on('click', function(e) {
        e.preventDefault();
        var message = "Đi đến màn hình chỉnh sửa thông tin. Bạn có chắc chắn?";
        var form = $(this).closest("form");
        sweetConfirm(3, message, function(result) {
            if (result){
                form.submit();
            }
        });
    });
});

//paginate
$(document).ready(function() {
    $(".tableNewsTopView").paginate({
        rows: 5,           // Set number of rows per page. Default: 5
        position: "top",   // Set position of pager. Default: "bottom"
        jqueryui: false,   // Allows using jQueryUI theme for pager buttons. Default: false
        showIfLess: false, // Don't show pager if table has only one page. Default: true
        numOfPages: 5
    });
    
    $(".tableNewsByCate").paginate({
        rows: 5,           // Set number of rows per page. Default: 5
        position: "top",   // Set position of pager. Default: "bottom"
        jqueryui: false,   // Allows using jQueryUI theme for pager buttons. Default: false
        showIfLess: false, // Don't show pager if table has only one page. Default: true
        numOfPages: 5
    });
});
</script>
EOF;

echo <<<EOF
<!DOCTYPE html>
<html>
<head>
EOF;

//Meta CSS
include ($TEMP_APP_META_PATH);

echo <<<EOF
</head>
<body class="hold-transition sidebar-mini layout-fixed" id="{$funcId}">
    <div class="wrapper">
EOF;

//Preloader
//include ($TEMP_APP_PRELOADER_PATH);

//Header
include ($TEMP_APP_HEADER_PATH);

//Menu
if ($role == '1'){
    include ($TEMP_APP_MENUSYSTEM_PATH);
}
if ($role == '2') {
    include ($TEMP_APP_MENU_PATH);
}

if ($role == '3'){
    include ($TEMP_APP_MENU_MOD_PATH);
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
                            <h3>{$totalNews['count_news']}</h3>

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
                            <h3>{$totalPost['post_new_day']}</h3>

                            <p>Bài đăng trong ngày</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-stats-bars"></i>
                        </div>
                        <a href="list-news.php" class="small-box-footer">Xem thêm <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{$totalAccount['count_users']}</h3>

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
                        {$htmlCategoryPostDay}
                    </div>
                </div>
                <div class="col-sm-12">
                    <h1 class="m-0" style="font-size: 1.8rem">Bài viết có nhiều lượt xem nhất</h1>
                </div>
                <div class="col-sm-12" style="margin-top: 20px;">
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover text-nowrap table-bordered tableNewsTopView" style="background-color: #FFFFFF;">
                            <thead style="background-color: #17A2B8;">
                                <tr>
                                    <th style="width: 10%;" class="text-th text-center">STT</th>
                                    <th style="width: 20%;" class="text-th text-center">Tiêu đề</th>
                                    <th style="width: 20%;" class="text-th text-center">Người đăng</th>
                                    <th style="width: 20%;" class="text-th text-center">Ngày đăng</th>
                                    <th style="width: 20%;" class="text-th text-center">Lượt xem</th>
                                    <th style="width: 10%;" class="text-center"></th>
                                </tr>
                            </thead>
                            <tbody>
                                {$htmlNewsTopView}
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

/**
 * function get databae tabel
 * @param $con
 * @param $funcId
 * @return string
 */
function getCategoryPostDay($con, $funcId){
    $pg_param = array();
    $recCnt = 0;
    $cnt = 0;

    $sql = "";
    $sql .= "SELECT DISTINCT                                                                                ";
    $sql .= "       category.id                                                                             ";
    $sql .= "     , category.category                                                                       ";
    $sql .= "  FROM category                                                                                ";
    $sql .= "  INNER JOIN news                                                                              ";
    $sql .= "    ON category.id = news.category                                                             ";
    $sql .= " WHERE category.deldate IS NULL                                                                ";
    $sql .= "   AND news.deldate IS NULL                                                                    ";
    $sql .= "   AND news.createdate BETWEEN '" .getDayNow(). "' AND '".getDatetimeNow()."'                  ";

    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query){
        systemError('systemError(' . $funcId . ') SQL Error：', $sql . print_r($pg_param, true));
    } else {
        $recCnt = pg_num_rows($query);
    }

    $html = '';
    if ($recCnt != 0){
        $categoryArray = pg_fetch_all($query);

        foreach ($categoryArray as $k => $v){
            $cnt++;
            $htmlPostOfDay = postOfDayByCategory($con, $funcId, $categoryArray[$k]['id']);
            $cntNews       = countPostOfDayByCategory($con, $funcId, $categoryArray[$k]['id']);

            $html .= <<< EOF
                <div class="card">
                    <div class="card-header title-collapse" id="headingOne">
                        <h5 class="mb-0 col-6">
                            <button class="btn btn-link" data-toggle="collapse" data-target="#collapse_{$cnt}" aria-expanded="true" aria-controls="collapseOne">
                                {$categoryArray[$k]['category']}
                            </button>
                        </h5>
                        <h5 class="count-news">
                            Số lượng - {$cntNews['coutnews']}
                        </h5>
                    </div>
    
                    <div id="collapse_{$cnt}" class="collapse" aria-labelledby="headingOne" data-parent="#accordion" style="">
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap table-bordered tableNewsByCate">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 10%;">STT</th>
                                        <th class="text-center" style="width: 40%;">Tiêu đề</th>
                                        <th class="text-center" style="width: 20%;">Người đăng</th>
                                        <th class="text-center" style="width: 20%;">Thời gian</th>
                                        <th class="text-center" style="width: 10%;">&nbsp</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {$htmlPostOfDay}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
EOF;
        }
    } else {
        $html .=<<< EOF
            <div class="card card-primary">
                <div class="card-body">
                    <i class="fas fa-bullseye fa-fw" style="color: red"></i>
					Không có dữ liệu
                </div>
            </div>
EOF;

    }
    return $html;
}

/**
 * get data news with max views
 * @param $con
 * @param $funcId
 * @return string
 */

function getNewsTopView($con, $funcId){
    $pg_param = array();
    $cnt = 0;
    $recCnt = 0;
    $sql = "";
    $sql .= "SELECT id                         ";
    $sql .= "     , title                      ";
    $sql .= "     , createdate                 ";
    $sql .= "     , view                       ";
    $sql .= "     , createby                   ";
    $sql .= "  FROM news                       ";
    $sql .= " WHERE deldate IS NULL            ";
    $sql .= " ORDER BY view DESC               ";
    $sql .= " LIMIT 20                         ";

    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query){
        systemError('systemError(' . $funcId . ') SQL Error：', $sql . print_r($pg_param, true));
    } else {
        $recCnt = pg_num_rows($query);
    }
    $html = '';
    if ($recCnt != 0) {
        while ($row = pg_fetch_assoc($query)) {
            $cnt++;

            $html .= <<<EOF
                 <tr>
                     <td class="text-center" style="width: 10%;">{$cnt}</td>
                     <td style="width: 20%;">{$row['title']}</td>
                     <td class="text-center" style="width: 20%;">{$row['createby']}</td>
                     <td class="text-center" style="width: 20%;">{$row['createdate']}</td>
                     <td class="text-center" style="width: 20%;">{$row['view']}</td>
                     <td class="text-center" style="width: 10%;">
                         <form action="detail-news.php" method="POST">
                             <input type="hidden" name="nid" value="{$row['id']}">
                             <input type="hidden" class="mode" name="mode" value="update">
                             <input type="hidden" name="dispFrom" value="dashboard">
                             <a class="btn btn-primary btn-sm editNews"><i class="fas fa-edit"></i></a>
                         </form>
                     </td>
                  </tr>
EOF;

        }
    } else {
        $html .= <<< EOF
            <tr>
                <td colspan = 7>
                    <h3 class="card-title">
                        <i class="fas fa-bullseye fa-fw" style="color: red"></i>
                        Không có dữ liệu
                    </h3>
                </td>
            </tr>
EOF;
    }
    return $html;
}

/**
 * total article count
 * @param $con
 * @return mixed
 */
function totalNews($con, $funcId){
    $pg_param = array();
    $recCnt = 0;
    $newsArray = array();
    
    $sql = "";
    $sql .= "SELECT COUNT(id)         ";
    $sql .= "    AS count_news        ";
    $sql .= "  FROM news              ";
    $sql .= "  WHERE deldate IS NULL  ";

    $query = pg_query_params($con, $sql, $pg_param);
    if(!$query){
        systemError('systemError('.$funcId.') SQL Error：',$sql.print_r($pg_param, TRUE));
    } else {
        $recCnt = pg_num_rows($query);
    }
    
    if ($recCnt != 0){
        $newsArray = pg_fetch_assoc($query);
    }
    return $newsArray;
}

/**
 * total post of new day
 * @param $con
 * @return mixed
 */
function totalPostDay($con, $funcId){
    $pg_param = array();
    $newsDay = array();
    $recCnt = 0;
    
    $sql = "";
    $sql .= "SELECT COUNT(id)                                                                           ";
    $sql .= "    AS post_new_day                                                                        ";
    $sql .= "  FROM news                                                                                ";
    $sql .= " WHERE createdate BETWEEN '" . date('Y/m/d') . "' AND '" . date('Y/m/d') . " 23:59:59'     ";
    $sql .= "   AND deldate IS NULL                                                                     ";
    $query = pg_query_params($con, $sql, $pg_param);
    if(!$query){
        systemError('systemError('.$funcId.') SQL Error：',$sql.print_r($pg_param, TRUE));
    } else {
        $recCnt = pg_num_rows($query);
    }
    
    if ($recCnt != 0){
        $newsDay = pg_fetch_assoc($query);
    }
    return $newsDay;
}

/**
 * number of accounts
 * @param $con
 * @return mixed
 */
function totalAccount($con, $funcId) {
    $pg_param = array();
    $recCnt = 0;
    $userArray = array();
    
    $sql = "";
    $sql .= "SELECT COUNT(id)          ";
    $sql .= "    AS count_users        ";
    $sql .= "  FROM users              ";
    $sql .= "  WHERE deldate IS NULL   ";

    $query = pg_query_params($con, $sql, $pg_param);
    if(!$query){
        systemError('systemError('.$funcId.') SQL Error：',$sql.print_r($pg_param, TRUE));
    } else {
        $recCnt = pg_num_rows($query);
    }
    
    if ($recCnt != 0){
        $userArray = pg_fetch_assoc($query);
    }
    return $userArray;
}

/**
 * Get post of day by category
 * @param $con
 * @param $funcId
 * @return array
 */
function postOfDayByCategory($con, $funcId, $idCate){
    $cnt = 0;
    $recCnt = 0;
    $pg_param = array();
    $pg_param[] = $idCate;

    $sql = "";
    $sql .= "SELECT news.id                                                                                 ";
    $sql .= "     , news.title                                                                              ";
    $sql .= "     , news.createdate                                                                         ";
    $sql .= "     , news.createby                                                                          ";
    $sql .= "  FROM news                                                                                    ";
    $sql .= "  INNER JOIN category                                                                          ";
    $sql .= "    ON news.category = category.id                                                             ";
    $sql .= " WHERE news.deldate IS NULL                                                                    ";
    $sql .= "   AND news.createdate BETWEEN '" . date('Y/m/d') . "' AND '" . date('Y/m/d') . " 23:59:59'    ";
    $sql .= "   AND category.deldate IS NULL                                                                ";
    $sql .= "   AND category.id = $1                                                                        ";

    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query){
        systemError('systemError('.$funcId.') SQL Error：',$sql.print_r($pg_param, TRUE));
    } else {
        $recCnt = pg_num_rows($query);
    }

    $html = '';
    if ($recCnt != 0){
        while ($row = pg_fetch_assoc($query)){
            $cnt++;
            $html .= <<< EOF
                <tr>
                    <td class="text-center" style="width: 10%;">{$cnt}</td>
                    <td style="width: 40%;">{$row['title']}</td>
                    <td class="text-center" style="width: 20%;">{$row['createby']}</td>
                    <td class="text-center" style="text-align: center; width: 20%;">{$row['createdate']}</td>
                    <td class="text-center" style="width: 10%;">
                        <form action="detail-news.php" method="POST">
                            <input type="hidden" name="nid" value="{$row['id']}">
                            <input type="hidden" class="mode" name="mode" value="update">
                            <input type="hidden" name="dispFrom" value="dashboard">
                            <a href="" class="btn btn-primary btn-sm editNews"><i class="fas fa-edit"></i></a>
                        </form>
                    </td>
                </tr>
EOF;

        }
    } else {
        $html .= <<< EOF
            <tr>
                <td colspan = 6>
                    <h3 class="card-title">
                        <i class="fas fa-bullseye fa-fw" style="color: red"></i>
                        Không có dữ liệu
                    </h3>
                </td>
            </tr>
EOF;

    }
    return $html;
}

/**
 * Count post of day by category
 * @param $con
 * @param $funcId
 * @param $idCate
 * @return array
 */
function countPostOfDayByCategory($con, $funcId, $idCate){
    $recCnt = 0;
    $cntNews = array();
    $pg_param = array();
    $pg_param[] = $idCate;

    $sql = "";
    $sql .= "SELECT COUNT(news.id) AS COUTNEWS                                                              ";
    $sql .= "  FROM news                                                                                    ";
    $sql .= " INNER JOIN category                                                                           ";
    $sql .= "    ON news.category = category.id                                                             ";
    $sql .= " WHERE news.deldate IS NULL                                                                    ";
    $sql .= "   AND category.deldate IS NULL                                                                ";
    $sql .= "   AND news.category = $1                                                                      ";
    $sql .= "   AND news.createdate BETWEEN '" . date('Y/m/d') . "' AND '" . date('Y/m/d') . " 23:59:59'    ";

    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query){
        systemError('systemError('.$funcId.') SQL Error：',$sql.print_r($pg_param, TRUE));
    } else {
        $recCnt = pg_num_rows($query);
    }

    if ($recCnt != 0){
        $cntNews = pg_fetch_assoc($query);
    }
    return $cntNews;
}

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

