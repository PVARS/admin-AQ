<?php

//Common setting
require_once ('config.php');
require_once ('lib.php');

//Initialization
$func_id = 'list_category';
$maxStr = 200;
$message = '';
$messageClass = '';
$iconClass = '';

session_start();

//Get param
$param = getParam();

$role           = $_SESSION['role'] ?? '';
$valueCategory  = $param['category'] ?? '';
$valueDateTo    = $param['dateTo'] ?? '';
$valueDateFrom  = $param['dateFrom'] ?? '';
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
$htmlCategory = getCategoryAndSearch($con, $func_id, $param, $mode);

if ($param){
    if (isset($param['registFlg']) && $param['registFlg'] == 1){
         $mes = array();

        if (mb_strlen($valueCategory) > $maxStr) {
            $mes[] = 'Tên danh mục không được nhập quá ' . $maxStr . ' ký tự.';
        }

        if (!empty($valueDateFrom) && !empty($valueDateTo) && (strtotime($valueDateFrom) > strtotime($valueDateTo))){
            $mes[] = 'Không thể tìm kiếm với thông tin ' .$valueDateFrom. ' lớn hơn '.$valueDateTo.'';
        }

        if (empty($mes)){
            getCategoryAndSearch($con, $func_id, $param, $mode);
        }
    }

    if (isset($param['mode']) && $param['mode'] == 'delete'){
        if (countNewsByCategory($con, $func_id, $param['idCategory']) > 0){
            $emtyCategory = true;
            $mes[] = 'Không thể xoá danh mục có chứa bài viết';
        } else {
            deleteCategory($con, $func_id, $param);
        }
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
    //Button clear
    $('#btnClear').on('click', function(e) {
        e.preventDefault();
        var message = "Đặt màn hình tìm kiếm về trạng thái ban đầu?";
        var that = $(this)[0];
        sweetConfirm(1, message, function(result) {
            if (result){
                window.location.href = that.href;
            }
        });
    });
    
    //Button Add news
    $('.btnAddNews').on('click', function(e) {
        e.preventDefault();
        var message = "Đi đến màn hình thêm bài viết cho danh mục này. Bạn chắc chứ?";
        var form = $(this).closest("form");
        sweetConfirm(5, message, function(result) {
            if (result){
                $('.mode').val('new');
                form.submit();
            }
        });
    });
    
    //Button Edit
    $('.btnEdit').on('click', function(e) {
        e.preventDefault();
        var message = "Đi đến màn hình chỉnh sửa thông tin. Bạn có chắc chắn?";
        var form = $(this).closest("form");
        sweetConfirm(3, message, function(result) {
            if (result){
                $('.mode').val('update');
                form.submit();
            }
        });
    });
    
    //Button Delete
    $('.btnDelete').on('click', function(e) {
        e.preventDefault();
        var message = "Danh mục này sẽ bị xoá. Bạn có chắc chắn?";
        var form = $(this).closest("form");
        sweetConfirm(1, message, function(result) {
            if (result){
                $('.mode').val('delete');
                form.submit();
            }
        });
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
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">
                                <i class="fas fa-search"></i>&nbspTìm kiếm danh mục</h1>
                        </div>
                        <!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="dashboard.php">Trang chủ</a></li>
                                <li class="breadcrumb-item active">Danh sách danh mục</li>
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
                    <div class="row">
                        <div class="card-body">
                            {$messageHtml}
                            <form action="" method="POST">
                                <div class="card card-info">
                                    <div class="card-header">
                                        <h3 class="card-title">Tìm kiếm</h3>
                                    </div>
                                    <div class="card-body">
                                        <label>Tên danh mục</label>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" name="category" value="{$valueCategory}" placeholder="Tên danh mục">
                                        </div>

                                        <label>Thời gian</label>
                                        <div class="row">
                                            <div class="input-group mb-6 col-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                                </div>
                                                <input type="date" name="dateFrom" class="form-control" value="{$valueDateFrom}">
                                            </div>
                                            <span><b>~</b></span>
                                            <div class="input-group mb-6 col-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                                </div>
                                                <input type="date" name="dateTo" class="form-control" value="{$valueDateTo}">
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /.card-body -->
                                    <div class="card-footer">
                                        <input type="hidden" name="registFlg" value="1">
                                        <button type="submit" class="btn btn-primary float-right" style="background-color: #17a2b8;">
                                          <i class="fa fa-search"></i>
                                          &nbspTìm kiếm
                                        </button>
                                        <a class="btn btn-default" id="btnClear">
                                            <i class="fas fa-eraser fa-fw"></i>
                                            Xoá
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- /.row -->
                    <div class="row">
                        <div class="card-body table-responsive">
                            <table class="table table-hover text-nowrap table-bordered" style="background-color: #FFFFFF;">
                                <thead style="background-color: #17A2B8;">
                                    <tr>
                                        <th style="text-align: center; width: 5%;" class="text-th">STT</th>
                                        <th style="text-align: center; width: 20%;" class="text-th">Tên danh mục</th>
                                        <th style="text-align: center; width: 20%;" class="text-th">Người tạo</th>
                                        <th style="text-align: center; width: 20%;" class="text-th">Ngày tạo</th>
                                        <th style="text-align: center; width: 20%;" class="text-th">Số bài viết</th>
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
 * Count news by category
 * @param $con
 * @param $func_id
 * @param $idCate
 * @return mixed
 */
function countNewsByCategory($con, $func_id, $idCate){
    $recCnt = 0;
    $cntNews = array();
    $pg_param = array();
    $pg_param[] = $idCate;

    $sql = "";
    $sql .= "SELECT COUNT(*)             ";
    $sql .= "  FROM news                 ";
    $sql .= " WHERE deldate IS NULL      ";
    $sql .= "   AND category = $1        ";

    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query){
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    } else {
        $recCnt = pg_num_rows($query);
    }

    if ($recCnt != 0){
        $cntNews = pg_fetch_assoc($query);
    }
    return $cntNews['count'];
}

/**
 * Search function
 * @param $con
 * @param $func_id
 * @param $param
 * @return string
 */
function getCategoryAndSearch($con, $func_id, $param, $mode){
    $pg_param = array();
    $pg_sql = array();
    $recCnt = 0;
    $cnt = 0;

    if (!empty($param['category']) && (mb_strlen($param['category']) > 0)){
        $pg_param[] = '%'.$param['category'].'%';
        $cnt++;
        $pg_sql[] = " AND category ILIKE $".$cnt."             ";
    }

    if (!empty($param['dateFrom']) && (mb_strlen($param['dateFrom']) > 0)){
        $pg_param[] = $param['dateFrom'];
        $cnt++;
        $pg_sql[] = " AND createDate >= $".$cnt."              ";
    }

    if (!empty($param['dateTo']) && (mb_strlen($param['dateTo']) > 0)){
        $pg_param[] = $param['dateTo'];
        $cnt++;
        $pg_sql[] = " AND createDate <= $".$cnt."              ";
    }

    $wheresql = join(' ', $pg_sql);

    $sql = "";
    $sql .= "SELECT id                  ";
    $sql .= "     , category            ";
    $sql .= "     , createby            ";
    $sql .= "     , createdate          ";
    $sql .= "  FROM category            ";
    $sql .= " WHERE deldate IS NULL     ";
    $sql .= $wheresql;
    $sql .= " ORDER BY id ASC           ";

    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query){
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    } else {
        $recCnt = pg_num_rows($query);
    }

    $html = '';
    if ($recCnt != 0){
        while ($row = pg_fetch_assoc($query)){
            $cnt++;
            $cntNews = countNewsByCategory($con, $func_id, $row['id']);
            $html .= <<< EOF
                <tr>
                    <td style="text-align: center; width: 5%;">{$cnt}</td>
                    <td style="text-align: center; width: 20%;">{$row['category']}</td>
                    <td style="text-align: center; width: 20%;">{$row['createby']}</td>
                    <td style="text-align: center; width: 20%;">{$row['createdate']}</td>
                    <td style="text-align: center; width: 20%;">{$cntNews}</td>
                    <td style="text-align: center; width: 5%;">
                        <form action="detail-category.php" method="POST">
                            <input type="hidden" name="idCategory" value="{$row['id']}">
                            <input type="hidden" name="dispFrom" value="list-categories">
                            <input type="hidden" name="mode" class="mode" value="{$mode}">
                            <a class="btn btn-primary btn-sm btnEdit"><i class="fas fa-edit"></i></a>
                        </form>
                    </td>
                    <td style="text-align: center; width: 5%;">
                        <form action="detail-news.php" method="POST">
                            <input type="hidden" name="idCategory" value="{$row['id']}">
                            <input type="hidden" name="mode" class="mode" value="{$mode}">
                            <input type="hidden" name="dispFrom" value="list-categories">
                            <a class="btn btn-success btn-sm btnAddNews">
                                <i class="fas fa-plus"></i>
                            </a>
                        </form>
                    </td>
                    <td style="text-align: center; width: 5%;">
                        <form action="{$_SERVER['SCRIPT_NAME']}" method="POST">
                            <input type="hidden" name="idCategory" value="{$row['id']}">
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
            </tr>
EOF;

    }
    return $html;
}

/**
 * Delete category
 * @param $con
 * @param $func_id
 * @param $param
 */
function deleteCategory($con, $func_id, $param){
    $pg_param = array();
    $pg_param[] = $_SESSION['loginId'];
    $pg_param[] = $param['idCategory'];

    $sql  = "";
    $sql .= "UPDATE category SET                                ";
    $sql .= "       deldate = '".date('Y/m/d')."'        ";
    $sql .= "     , updatedate = '".date('Y/m/d')."'     ";
    $sql .= "     , updateby = $1                               ";
    $sql .= " WHERE id = $2                                     ";

    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query){
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    }

    $_SESSION['message'] = 'Danh mục đã được xoá thành công';
    $_SESSION['messageClass'] = 'alert-success';
    $_SESSION['iconClass'] = 'fas fa-check';

    header('location: list-categories.php');
    exit();
}
?>

