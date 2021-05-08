<?php

//Common setting
require_once('config.php');
require_once('lib.php');

//Initialization
$func_id      = 'list_news';
$maxStr       = 200;
$message      = '';
$messageClass = '';
$iconClass    = '';
$htmlListNews = '';
$htmlCategory = '';
$htmlCreateby = '';

session_start();

//Get param
$param       = getParam();
$f_title     = $param['title'] ?? '';
$f_category  = $param['category'] ?? '';
$f_createby  = $param['createby'] ?? '';
$f_keyword   = $param['keyword'] ?? '';
$f_dateForm  = $param['dateForm'] ?? '';
$f_dateTo    = $param['dateTo'] ?? '';
$nid         = $param['nid'] ?? '';
$mode        = $param['mode'] ?? '';

$role        = $_SESSION['role'] ?? '';

//Connect DB
$con = openDB();

if (!isset($_SESSION['loginId'])) {
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

if (isset($_SESSION['role']) && $_SESSION['role'] != 1) {
    header('location: error404.php');
    exit();
}

$messageSwal = $_SESSION['messageSwal'] ?? 0;
if ($messageSwal != 0){
    unset($_SESSION['messageSwal']);
}

$htmlListNews = getNewsAndSearch($con, $func_id, $f_title, $f_category, $f_createby, $f_keyword, $f_dateForm);
$htmlCategory = getComboboxCategory($con, $func_id, $f_category);
$htmlCreateby = getComboboxCreateby($con, $func_id, $f_createby);

// Validate Data input
$validate = validateDataSearch($f_title, $f_keyword, $f_dateForm, $f_dateTo, $maxStr);

if ($param){
    if (isset($param['registFlg']) && $param['registFlg'] == 1) {
        if ($mode == "delete"){
            deleteNew($con, $func_id, $nid);
        }

        $mes = $validate;

        $message = join('<br>', $mes);
        if (strlen($message)) {
            $messageClass = 'alert-danger';
            $iconClass = 'fas fa-ban';
        }

        if (empty($mes)){
            getNewsAndSearch($con, $func_id, $f_title, $f_category, $f_createby, $f_keyword, $f_dateForm);
        }
    }
}

//Message HTML
if (isset($_SESSION['message']) && strlen($_SESSION['message'])) {
    $message      .= $_SESSION['message'];
    $messageClass .= $_SESSION['messageClass'];
    $iconClass    .= $_SESSION['iconClass'];
    $_SESSION['message']        = '';
    $_SESSION['messageClass']   = '';
    $_SESSION['iconClass']      = '';
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
$titleHTML  = '';
$cssHTML    = '';
$scriptHTML = <<<EOF
<script>
    $(function() {
        
        // Button clear
        $('#btn_clear').on('click', function(e) {
            e.preventDefault();
            var message = "Đặt màn hình tìm kiếm về trạng thái ban đầu?";
            var that = $(this)[0];
            sweetConfirm(1, message, function(result) {
                if (result){
                    window.location.href = that.href;
                }
            });
        });
        
        // Button delete
        $('.btn_delete').on('click', function(e) {
            e.preventDefault();
            var message = "Bạn đang yêu cầu xóa bản tin này. Bạn có chắc chắn?";
            var form = $(this).closest("form");
            sweetConfirm(1, message, function(result) {
                if (result){
                    form.submit();
                }
            });
        });
        
        // Button edit
        $('.edit_new').on('click', function(e) {
            e.preventDefault();
            var message = "Đi đến màn hình chỉnh sửa thông tin. Bạn có chắc chắn?";
            var form = $(this).closest("form");
            sweetConfirm(3, message, function(result) {
                if (result){
                    form.submit();
                }
            });
        });
        
        // Paginate
        $(".table").paginate({
            rows: 5,           // Set number of rows per page. Default: 5
            position: "top",   // Set position of pager. Default: "bottom"
            jqueryui: false,   // Allows using jQueryUI theme for pager buttons. Default: false
            showIfLess: false, // Don't show pager if table has only one page. Default: true
            numOfPages: 5
        });
                  
    })
    
    if ({$messageSwal} == 1){
        Swal.fire({
            position: 'top',
            icon: 'success',
            title: 'Bản tin đã thêm thành công',
            showConfirmButton: false,
            timer: 2000
        })
     }
     
     if ({$messageSwal} == 2){
        Swal.fire({
                position: 'top',
                icon: 'success',
                title: 'Bản tin đã cập nhật thành công',
                showConfirmButton: false,
                timer: 2000
        })
     }
    
    if ({$messageSwal} == 3){
        Swal.fire({
            position: 'top',
            icon: 'success',
            title: 'Bản tin đã được xóa thành công',
            showConfirmButton: false,
            timer: 2000
        })
     }
    
</script>
EOF;


echo <<<EOF
<!DOCTYPE html>
<html>
<head>
EOF;

//Meta CSS
include($TEMP_APP_META_PATH);

echo <<<EOF
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
EOF;

//Header
include($TEMP_APP_HEADER_PATH);

//Menu
if ($role == '1') {
    include($TEMP_APP_MENUSYSTEM_PATH);
} else {
    include($TEMP_APP_MENU_PATH);
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
                                <i class="fas fa-search"></i>&nbspTìm kiếm bài viết</h1>
                        </div>
                        <!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="index.html">Trang chủ</a></li>
                                <li class="breadcrumb-item active">Danh sách bài viết</li>
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
                            <form action="{$_SERVER['SCRIPT_NAME']}" method="POST">
                                <div class="card card-info">
                                    <div class="card-header">
                                        <h3 class="card-title">Tìm kiếm</h3>
                                    </div>
                                    <div class="card-body">
                                        <label>Tiêu đề</label>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" name="title" placeholder="Tiêu đề" value="{$f_title}">
                                        </div>

                                        <label>Danh mục</label>
                                        <div class="input-group mb-3">
                                            <select class="custom-select" name="category">
                                                {$htmlCategory}
                                              </select>
                                        </div>

                                        <label>Người đăng</label>
                                        <div class="input-group mb-3">
                                            <select class="custom-select" name="createby">
                                                {$htmlCreateby}
                                              </select>
                                        </div>

                                        <label>Từ khoá</label>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" name="keyword" placeholder="Nhập từ khoá" value="{$f_keyword}">
                                        </div>

                                        <label>Thời gian</label>
                                        <div class="row">
                                            <div class="input-group mb-6 col-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                                </div>
                                                <input type="date" name="dateForm" value="{$f_dateForm}" class="form-control">
                                            </div>
                                            <span><b>~</b></span>
                                            <div class="input-group mb-6 col-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                                </div>
                                                <input type="date" name="dateTo" value="{$f_dateTo}" class="form-control">
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
                                        <a href="" id="btn_clear">
                                            <button type="reset" class="btn btn-default">
                                            <i class="fas fa-eraser fa-fw"></i>
                                            Xoá
                                          </button>
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
                                        <th style="width: 5%;" class="text-th">STT</th>
                                        <th style="width: 35%;" class="text-th">Tiêu đề</th>
                                        <th style="width: 20%;" class="text-th">Người đăng</th>
                                        <th style="text-align: center; width: 20%;" class="text-th">Ngày đăng</th>
                                        <th style="text-align: center; width: 20%;" class="text-th">Lượt xem</th>
                                        <th colspan="3"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {$htmlListNews}
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
include($TEMP_APP_FOOTER_PATH);
//Meta JS
include($TEMP_APP_METAJS_PATH);
echo <<<EOF
    </div>
</body>
</html>
EOF;

/**
 * @param $con
 * @param $func_id
 * @param $f_title
 * @param $f_category
 * @param $f_createby
 * @param $f_keyword
 * @param $f_dateForm
 * @return string
 */
function getNewsAndSearch($con, $func_id, $f_title, $f_category, $f_createby, $f_keyword, $f_dateForm)
{
    $pg_param = array();
    $pg_sql   = array();
    $recCnt   = 0;
    $count    = 0;
    $html     = '';

    if (!empty($f_title) && (mb_strlen($f_title) > 0)) {
        $pg_param[] = '%' . $f_title . '%';
        $count++;
        $pg_sql[] = " AND news.title ILIKE $" . $count . "             ";
    }

    if (!empty($f_category) && (mb_strlen($f_category) > 0)) {
        $pg_param[] = $f_category;
        $count++;
        $pg_sql[] = " AND news.category = $" . $count. "               ";
    }

    if (!empty($f_createby) && (mb_strlen($f_createby) > 0)) {
        $pg_param[] = $f_createby;
        $count++;
        $pg_sql[] = " AND users.id = $" . $count. "                     ";
    }

    if (!empty($f_keyword) && (mb_strlen($f_keyword) > 0)) {
        $pg_param[] = '%' . $f_keyword . '%';
        $count++;
        $pg_sql[] = " AND news.shortdescription ILIKE $" . $count . "   ";
    }

    if (!empty($f_dateForm) && (mb_strlen($f_dateForm) > 0)){
        $pg_param[] = $f_dateForm;
        $count++;
        $pg_sql[] = " AND news.createdate >= $" . $count . "                 ";
    }

    $wheresql = join(' ', $pg_sql);

    $sql  = "";
    $sql .= "SELECT id,                         ";
    $sql .= "       title,                      ";
    $sql .= "       createdate,                 ";
    $sql .= "       view,                       ";
    $sql .= "       title,                      ";
    $sql .= "       createby                    ";
    $sql .= "FROM   news                        ";
    $sql .= "WHERE deldate IS NULL              ";
    $sql .= $wheresql;
    $sql .= "ORDER BY id ASC                    ";

    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query) {
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    } else {
        $recCnt = pg_num_rows($query);
    }

    if ($recCnt != 0) {
        while ($row = pg_fetch_assoc($query)) {
            $count++;
            $html .= <<<EOF
                <tr>
                   <td style="width: 5%;">{$count}</td>
                   <td style="width: 35%;">{$row['title']}</td>
                   <td style="width: 20%;">{$row['createby']}</td>
                   <td style="text-align: center; width: 20%;">{$row['createdate']}</td>
                   <td style="text-align: center; width: 20%;">{$row['view']}</td>
                   <td style="text-align: center; width: 5%;">
                       <form action="detail-news.php" method="POST">
                            <input type="hidden" name="mode" value="update">
                            <input type="hidden" name="nid" value="{$row['id']}">
                            <button href="javascript:void(0)" class="btn btn-block btn-primary btn-sm edit_new" title="Chỉnh sửa"><i class="fas fa-edit"></i></button>
                        </form>
                   </td>
                   <td style="text-align: center; width: 5%;">
                      <form action="{$_SERVER['SCRIPT_NAME']}" method="POST">
                         <input type="hidden" name="nid" value="{$row['id']}">
                         <input type="hidden" name="mode" value="delete">
                         <input type="hidden" name="messageSwal" value="3">
                         <input type="hidden" name="registFlg" value="1">
                         <a href="javascript:void(0)" class="btn btn-block btn-danger btn-sm btn_delete" title="Xóa bài">
                            <i class="fas fa-trash"></i>
                         </a>
                      </form>
                   </td>
                </tr>                                    
EOF;

        }
    }else {
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
 * @param $con
 * @param $func_id
 * @param $f_category
 * @return string
 */
function getComboboxCategory($con, $func_id, $f_category)
{
    $pg_param = array();
    $recCnt   = 0;

    $sql  = "";
    $sql .= "SELECT DISTINCT     ";
    $sql .= "       id,          ";
    $sql .= "       category     ";
    $sql .= "  FROM category     ";
    $sql .= "  ORDER BY id ASC   ";

    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query) {
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    } else {
        $recCnt = pg_num_rows($query);
    }

    $html = '<option value="0">Chọn danh mục</option>';
    if ($recCnt != 0) {
        while ($row = pg_fetch_assoc($query)) {

            $selected = '';
            if ($f_category == $row['id']) {
                $selected = 'selected="selected"';
            }

            $html .= <<<EOF
            <option value="{$row['id']}" {$selected}>{$row['category']}</option>
EOF;

        }
    }
    return $html;
}

/**
 * @param $con
 * @param $func_id
 * @param $f_createby
 * @return string
 */
function getComboboxCreateby($con, $func_id, $f_createby)
{
    $pg_param = array();
    $recCnt   = 0;

    $sql  = "";
    $sql .= "SELECT DISTINCT     ";
    $sql .= "       id,          ";
    $sql .= "       fullname     ";
    $sql .= "  FROM users        ";
    $sql .= "  ORDER BY id ASC   ";

    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query) {
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    } else {
        $recCnt = pg_num_rows($query);
    }

    $html = '<option value="0">Chọn người đăng</option>';
    if ($recCnt != 0) {
        while ($row = pg_fetch_assoc($query)) {

            $selected = '';
            if ($f_createby == $row['id']) {
                $selected = 'selected="selected"';
            }

            $html .= <<<EOF
            <option value="{$row['id']}" {$selected}>{$row['fullname']}</option>
EOF;

        }
    }
    return $html;
}

/**
 * @param $con
 * @param $func_id
 * @param $nid
 */
function deleteNew($con, $func_id, $nid){
    $datenow = '';
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    $datenow = date("Y-m-d H:i:s");

    $pg_param   = array();
    $pg_param[] = $datenow;
    $pg_param[] = $nid;

    $sql  = "";
    $sql .= "UPDATE news                     ";
    $sql .= "SET    deldate = $1             ";
    $sql .= "WHERE  id = $2                  ";

    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query){
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    }

    $_SESSION['messageSwal'] = 3;
    header("location: list-news.php");
    exit();
}

/**
 * @param $f_title
 * @param $f_keyword
 * @param $dateForm
 * @param $dateTo
 * @param $maxStr
 * @return array
 */
function validateDataSearch($f_title, $f_keyword, $dateForm, $dateTo, $maxStr)
{
    $mes = [
        'chk_format'     => [],
        'chk_max_length' => []
    ];

    if (mb_strlen($f_title) > $maxStr) {
        $mes['chk_max_length'][] = 'Tiêu đề không được nhập quá ' . $maxStr . ' ký tự.';
    }

    if (mb_strlen($f_keyword) > $maxStr) {
        $mes['chk_max_length'][] = 'Từ khóa không được nhập quá ' . $maxStr . ' ký tự.';
    }

    if (strtotime($dateForm) > strtotime($dateTo)){
        $mes['chk_format'][] = 'Không thể tìm kiếm với thông tin ' .$dateForm. ' lớn hơn '.$dateTo.'';
    }

    $msg = array_merge(
        $mes['chk_format'],
        $mes['chk_max_length']
    );

    return $msg;
}

?>

