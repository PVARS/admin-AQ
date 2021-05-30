<?php

//Common setting
require_once ('config.php');
require_once ('lib.php');

//Initialization
$func_id = 'accept-post';
$message = '';
$messageClass = '';
$iconClass = '';

session_start();

//Get param
$param = getParam();

$role           = $_SESSION['role'] ?? '';
$mode           = $param['mode'] ?? '';

//Connect DB
$con = openDB();

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

if (isset($_SESSION['role']) && $_SESSION['role'] == 3) {
    header('location: error404.php');
    exit();
}

$htmlCategory = '';
$htmlCategory = getNewsByStatusIsFalse($con, $func_id, $param, $mode);

if ($param){

    if (isset($param['mode']) && $param['mode'] == 'accept-post'){
        acceptPost($con, $func_id, $param, $_SESSION['loginId']);
    }

    if (isset($param['mode']) && $param['mode'] == 'delete'){
        deleteNew($con, $func_id, $param, $_SESSION['loginId']);
    }

    $message = join('<br>', $mes);
    if (strlen($message)) {
        $messageClass = 'alert-danger';
        $iconClass = 'fas fa-ban';
    }
}

//Message HTML
if (isset($_SESSION['message']) && strlen($_SESSION['message'])) {
    $message .= $_SESSION['message'];
    $messageClass .= $_SESSION['messageClass'];
    $iconClass .= $_SESSION['iconClass'];
    $_SESSION['message'] = '';
    $_SESSION['messageClass'] = '';
    $_SESSION['iconClass'] = '';
}
$messageHtml = '';
if (strlen($message)) {
    $messageHtml = <<< EOF
    <div class="alert {$messageClass} alert-dismissible">
        <div class="row">
            <div class="icon">
                <i class="{$iconClass}"></i>
            </div>
            <div class="col-10">
                {$message}
            </div>
        </div>
    </div>
EOF;
}
//-----------------------------------------------------------
// HTML
//-----------------------------------------------------------
$titleHTML = '';
$cssHTML = '';
$scriptHTML = <<< EOF
<script>
$(function() {
    
    //Button Edit
    $('.btnEdit').on('click', function(e) {
        e.preventDefault();
        var message = "Đi đến màn hình chỉnh sửa bài viết. Bạn có chắc chắn?";
        var form = $(this).closest("form");
        sweetConfirm(3, message, function(result) {
            if (result){
                $('.mode').val('update');
                form.submit();
            }
        });
    });
    
    //Button Add news
    $('.btnAcceptPost').on('click', function(e) {
        e.preventDefault();
        var message = "Bài viết này sẽ được duyệt và hiển thị trên trang chủ. Bạn chắc chứ?";
        var form = $(this).closest("form");
        sweetConfirm(6, message, function(result) {
            if (result){
                $('.mode').val('accept-post');
                form.submit();
            }
        });
    });
    
    //Button Delete
    $('.btnDelete').on('click', function(e) {
        e.preventDefault();
        var message = "Bài viết này sẽ bị xoá. Bạn có chắc chắn?";
        var form = $(this).closest("form");
        sweetConfirm(1, message, function(result) {
            if (result){
                $('.mode').val('delete');
                form.submit();
            }
        });
    });
    
    // Paginate
    $(".table").paginate({
        rows: 6,           // Set number of rows per page. Default: 5
        position: "top",   // Set position of pager. Default: "bottom"
        jqueryui: false,   // Allows using jQueryUI theme for pager buttons. Default: false
        showIfLess: false, // Don't show pager if table has only one page. Default: true
        numOfPages: 5
    });
     
})
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
<body class="hold-transition sidebar-mini layout-fixed">
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

//Conntent
echo <<<EOF
<div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">
                                <i class="fas fa-check-square"></i>&nbspBài viết chờ phê duyệt</h1>
                        </div>
                        <!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="dashboard.php">Trang chủ</a></li>
                                <li class="breadcrumb-item active">Bài viết chờ phê duyệt</li>
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
                    {$messageHtml}
                    <div class="row">
                        <div class="card-body table-responsive pt-0">
                            <table class="table table-hover text-nowrap table-bordered" style="background-color: #FFFFFF;">
                                <thead style="background-color: #17A2B8;">
                                    <tr>
                                        <th style="text-align: center; width: 5%;" class="text-th">STT</th>
                                        <th style="text-align: center; width: 20%;" class="text-th">Tiêu đề</th>
                                        <th style="text-align: center; width: 20%;" class="text-th">Danh mục</th>
                                        <th style="text-align: center; width: 20%;" class="text-th">Người đăng</th>
                                        <th style="text-align: center; width: 20%;" class="text-th">Ngày tạo</th>
                                        <th colspan="3" class="text-center" style="width: 15px"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {$htmlCategory}
                                </tbody>
                            </table>
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

/**
 * Search function
 * @param $con
 * @param $func_id
 * @param $param
 * @return string
 */
function getNewsByStatusIsFalse($con, $func_id, $param, $mode){
    $pg_param = array();
    $pg_sql = array();
    $recCnt = 0;
    $cnt = 0;

    $sql = "";
    $sql .= "SELECT                                                 ";
    $sql .= "	    NEWS.ID,                                        ";
    $sql .= "	    NEWS.TITLE,                                     ";
    $sql .= " 	    CATEGORY.CATEGORY,                              ";
    $sql .= " 	    NEWS.CREATEBY,                                  ";
    $sql .= "       NEWS.CREATEDATE                                 ";
    $sql .= "  FROM NEWS                                            ";
    $sql .= " INNER JOIN CATEGORY ON NEWS.CATEGORY = CATEGORY.ID    ";
    $sql .= " WHERE NEWS.DELDATE IS NULL                            ";
    $sql .= "   AND NEWS.STATUS IS FALSE                            ";
    $sql .= " ORDER BY CREATEDATE DESC                              ";

    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query){
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    } else {
        $recCnt = pg_num_rows($query);
    }

    $html = '';
    if ($recCnt != 0){
        while ($row = pg_fetch_assoc($query)){
            $dateFormat = date('d/m/Y H:i', strtotime($row['createdate']));

            $cnt++;
            $html .= <<< EOF
                <tr>
                    <td style="text-align: center; width: 5%;">{$cnt}</td>
                    <td style="width: 20%;">{$row['title']}</td>
                    <td style="width: 20%;text-align: center;">{$row['category']}</td>
                    <td style="text-align: center; width: 20%;">{$row['createby']}</td>
                    <td style="text-align: center; width: 20%;">{$dateFormat}</td>
                    <td style="text-align: center; width: 5%;">
                        <form action="detail-news.php" method="POST">
                            <input type="hidden" name="nid" value="{$row['id']}">
                            <input type="hidden" name="dispFrom" value="accept-post">
                            <input type="hidden" name="mode" class="mode" value="{$mode}">
                            <a class="btn btn-primary btn-sm btnEdit"><i class="fas fa-edit"></i></a>
                        </form>
                    </td>
                    <td style="text-align: center; width: 5%;">
                        <form action="{$_SERVER['SCRIPT_NAME']}" method="POST">
                            <input type="hidden" name="idNew" value="{$row['id']}">
                            <input type="hidden" name="mode" class="mode" value="{$mode}">
                            <a class="btn btn-success btn-sm btnAcceptPost">
                                <i class="fas fa-check"></i>
                            </a>
                        </form>
                    </td>
                    <td style="text-align: center; width: 5%;">
                        <form action="{$_SERVER['SCRIPT_NAME']}" method="POST">
                            <input type="hidden" name="idNew" value="{$row['id']}">
                            <input type="hidden" name="mode" class="mode" value="{$mode}">
                            <a class="btn btn-danger btn-sm btnDelete"><i class="fas fa-trash"></i></a>
                        </form>
                    </td>
                </tr>
EOF;

        }
    } else {
        $html .= <<< EOF
            <tr>
                <td colspan = 8>
                    <h3 class="card-title">
                        <i class="fas fa-bullseye fa-fw" style="color: red"></i>
                        Không có dữ liệu
                    </h3>
                </td>
            </tr
EOF;

    }
    return $html;
}

/**
 * Delete New
 * @param $con
 * @param $func_id
 * @param $nid
 */
function deleteNew($con, $func_id, $param, $loginId)
{

    $pg_param   = array();
    $pg_param[] = getDatetimeNow();
    $pg_param[] = $loginId;
    $pg_param[] = $param['idNew'];

    $sql  = "";
    $sql .= "UPDATE news                     ";
    $sql .= "SET    deldate = $1,            ";
    $sql .= "       updateby = $2,           ";
    $sql .= "       updatedate = $1          ";
    $sql .= "WHERE  id = $3                  ";

    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query) {
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    }

    $_SESSION['message'] = 'Bài viết đã được xoá thành công';
    $_SESSION['messageClass'] = 'alert-success';
    $_SESSION['iconClass'] = 'fas fa-check';

    header("location: accept-post.php");
    exit();

}

/**
 * Delete New
 * @param $con
 * @param $func_id
 * @param $nid
 */
function acceptPost($con, $func_id, $param, $loginId)
{

    $pg_param   = array();
    $pg_param[] = $loginId;
    $pg_param[] = 1;
    $pg_param[] = $param['idNew'];

    $sql  = "";
    $sql .= "UPDATE news                    ";
    $sql .= "SET    acceptby = $1,          ";
    $sql .= "       status = $2             ";
    $sql .= "WHERE  id = $3                 ";

    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query) {
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    }

    $_SESSION['message'] = 'Bài viết đã được duyệt thành công';
    $_SESSION['messageClass'] = 'alert-success';
    $_SESSION['iconClass'] = 'fas fa-check';

    header("location: accept-post.php");
    exit();

}
?>

