<?php

//Common setting
require_once ('config.php');
require_once ('lib.php');

//Initialization
$func_id    = 'detail_category';
$message = '';
$messageClass = '';
$htmlDelete = '';
$titlePage      = "Thêm danh mục";
$titleButton    = "Lưu";

session_start();

//Get param
$param      = getParam();
$mode       = $param['mode'] ?? 'new';
$cid        = $param['cid'] ?? '';

$role       = $_SESSION['role'] ?? '';
$loginid    = $_SESSION['loginId'] ?? '';

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

if (isset($_SESSION['role']) && $_SESSION['role'] == 3) {
    header('location: error404.php');
    exit();
}

if ($param){
    if (isset($param['registFlg']) && $param['registFlg'] == 1) {
        $mes = array();

        // delete
        if ($mode == 'delete'){
            if (countNewsByCategory($con, $func_id, $cid) > 0){
                $emtyCategory = true;
                $mes[] = 'Không thể xoá danh mục có chứa bài viết';
            } else {
                deleteCategory($con, $func_id, $param);
            }
        }

        if (empty($mes)) {
            $mes = checkValidate($con, $func_id, $param);
        }

        $message = join('<br>', $mes);
        if (strlen($message)) {
            $messageClass = 'alert-danger';
            $iconClass = 'fas fa-ban';
        }

        if (empty($mes)) {
            if ($mode == 'new'){ // insert
                insertCategory($con, $func_id, $param, $loginid);
            } else if ($mode == 'update'){ // update
                updateCategory($con, $func_id, $param, $loginid);
            }

        }

    }
}

if (isset($cid) && (mb_strlen($cid) > 0)) {
    $titlePage      = "Chỉnh sửa danh mục";
    $titleButton    = "Cập nhật";
    $mode           = "update";

    $arr_category   = getCategoryById($con, $func_id, $cid);
    $category       = $arr_category['category'];
    $fullname       = $arr_category['fullname'];
    $icon           = $arr_category['icon'];

    $htmlDelete     = <<<EOF
        <a class="btn btn-danger btn-sm btnDelete"><i class="fas fa-trash"></i>&nbsp;Xóa</a>
EOF;
} else {
    $category       = $param['f_category'] ?? '';
    $icon           = $param['icon'] ?? '';
    $fullname       = $_SESSION['fullname'] ?? '';
}

//Message HTML
if(isset($_SESSION['message']) && strlen($_SESSION['message'])){
    $message      .= $_SESSION['message'];
    $messageClass .= $_SESSION['messageClass'];
    $iconClass    .= $_SESSION['iconClass'];
    $_SESSION['message']      = '';
    $_SESSION['messageClass'] = '';
    $_SESSION['iconClass']    = '';
}
$messageHtml  = '';
if(strlen($message)){
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
$scriptHTML = <<<EFO
<script>
$(function() {
  $('.btn_saveOrUpdate').on('click', function(e) {
        /* SET Message */
        if ('{$mode}' == 'update'){
            var message = "Bạn có muốn chỉnh sửa danh mục này?";
            var numberMessage = 3;
        } else {
            var message = "Bạn có muốn thêm mới danh mục này?";
            var numberMessage = 5;
        }
        var form = $(this).closest("form");
        sweetConfirm(numberMessage, message, function(result) {
            e.preventDefault();
            if (result){
                form.submit();
            }
        });
    });
  
    //Button delete
    $('.btnDelete').on('click', function(e) {
        e.preventDefault();
        var message = "Danh mục này sẽ bị xoá. Bạn chắc chứ?";
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
EFO;


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
                                <i class="fas fa-folder-plus"></i>&nbsp{$titlePage}</h1>
                        </div>
                        <!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="index.html">Trang chủ</a></li>
                                <li class="breadcrumb-item active">Thêm danh mục</li>
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
                        <div class="col-12">
                            <a href="list-categories.php" class="btn btn-primary float-right mr-3" style="background-color: #17a2b8;" title="Danh sách danh mục">
                                <i class="fas fa-backward"></i>
                                &nbspTrở lại
                            </a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="card-body">
                            {$messageHtml}
                            <form action="{$_SERVER['SCRIPT_NAME']}" method="POST">
                                <div class="card card-info">
                                    <div class="card-header">
                                        <h3 class="card-title">{$titlePage}</h3>
                                    </div>
                                    <div class="card-body">
                                        <label>Tên danh mục</label>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" placeholder="Tên danh mục" name="f_category" value="{$category}">
                                        </div>
                                        
                                        <label>Icon</label>
                                        <small id="emailHelp" class="text-muted" style="color: red!important;">(Truy cập https://fontawesome.com để sử dụng icon cho danh mục)</small>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" placeholder="Icon" name="icon" value='{$icon}'>
                                        </div>

                                        <label>Người tạo</label>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" value="{$fullname}" readonly>
                                        </div>
                                    </div>
                                    <!-- /.card-body -->
                                    <div class="card-footer">
                                        <input type="hidden" class="mode" name="mode" value="{$mode}">
                                        <input type="hidden" name="cid" value="{$cid}">
                                        <input type="hidden" name="registFlg" value="1">
                                        <a class="btn btn-primary float-right btn_saveOrUpdate" style="background-color: #17a2b8;">
                                            <i class="fas fa-save"></i>
                                            &nbsp{$titleButton}
                                        </a>
                                        {$htmlDelete}
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- /.row -->
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
 * @param $con
 * @param $func_id
 * @return array
 */
function getCategoryById($con, $func_id, $cid){
    $arr_category   = array();
    $pg_param       = array();
    $pg_param[]     = $cid;
    $recCnt         = 0;

    $sql  = "";
    $sql .= "SELECT CATEGORY.CATEGORY,                                  ";
    $sql .= "       CATEGORY.ICON,                                      ";
    $sql .= "	    USERS.FULLNAME                                      ";
    $sql .= " FROM CATEGORY                                             ";
    $sql .= " INNER JOIN USERS ON CATEGORY.CREATEBY = USERS.LOGINID     ";
    $sql .= " WHERE CATEGORY.ID = $1                                    ";

    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query){
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    } else {
        $recCnt = pg_num_rows($query);
    }

    if ($recCnt != 0){
        $arr_category = pg_fetch_assoc($query);
    } else {
        header('location: dashboard.php');
        exit();
    }

    return $arr_category;
}

/**
 * @param $con
 * @param $func_id
 * @param $param
 * @return array
 */
function checkValidate($con, $func_id, $param){

    $arr_category = array();
    $mes = [
        'chk_required'   => [],
        'chk_max_length' => []
    ];

    if (empty($param['f_category'])){
        $mes['chk_required'][] = 'Vui lòng nhập tên danh mục.';
    } elseif (mb_strlen($param['f_category']) > 254){
        $mes['chk_max_length'][] = 'Tên danh mục phải bé hơn 254 ký tự.';
    }

    $msg = array_merge(
        $mes['chk_required'],
        $mes['chk_max_length']
    );

    if (empty($msg)){
        $dupCategory = checkDupCategory($con, $func_id, $param);
        if (!empty($dupCategory)){
            $msg[] = 'Tên danh mục đã tồn tại.';
        }

        if (!empty($msg) && $param['mode'] == 'update') {

            $arr_category = getCategoryById($con, $func_id, $param['cid']);
            if ($param['f_category'] === $arr_category['category']) {
                $msg = array();
            }
        }

    }

    return $msg;
}

/**
 * @param $con
 * @param $func_id
 * @param $param
 * @return array
 */
function checkDupCategory($con, $func_id, $param){
    $recCnt = 0;
    $category = array();
    $pg_param = array();
    $pg_param[] = $param['f_category'];

    $sql = "";
    $sql .= "SELECT category            ";
    $sql .= "  FROM category            ";
    $sql .= " WHERE category = $1       ";
    $sql .= "   AND deldate IS NULL     ";

    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query){
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    } else {
        $recCnt = pg_num_rows($query);
    }

    if ($recCnt != 0){
        $category = pg_fetch_assoc($query);
    }

    return $category;
}

/**
 * @param $con
 * @param $func_id
 * @param $param
 * @param $loginId
 */
function insertCategory($con, $func_id, $param, $loginId){
    $pg_param   = array();
    $pg_param[] = $param['f_category'];
    $pg_param[] = getDatetimeNow();
    $pg_param[] = $loginId;
    $pg_param[] = $param['icon'];

    $sql  = "";
    $sql .= "INSERT INTO CATEGORY(                  ";
    $sql .= "					CATEGORY,           ";
    $sql .= "					CREATEDATE,         ";
    $sql .= "					CREATEBY,           ";
    $sql .= "					ICON)               ";
    $sql .= " VALUES ($1, $2, $3, $4);              ";

    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query){
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    }

    $_SESSION['message'] = 'Danh mục đã được thêm thành công';
    $_SESSION['messageClass'] = 'alert-success';
    $_SESSION['iconClass'] = 'fas fa-check';

    header('location: list-categories.php');
    exit();
}

/**
 * @param $con
 * @param $func_id
 * @param $param
 * @param $loginId
 */
function updateCategory($con, $func_id, $param, $loginId){
    $pg_param   = array();
    $pg_param[] = $param['f_category'];
    $pg_param[] = getDatetimeNow();
    $pg_param[] = $loginId;
    $pg_param[] = $param['icon'];
    $pg_param[] = $param['cid'];

    $sql  = "";
    $sql .= "UPDATE CATEGORY                ";
    $sql .= " SET CATEGORY   = $1,          ";
    $sql .= "	  UPDATEDATE = $2,          ";
    $sql .= "	  UPDATEBY   = $3,          ";
    $sql .= "	  ICON       = $4           ";
    $sql .= " WHERE ID = $5;                ";

    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query){
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    }

    $_SESSION['message'] = 'Danh mục đã được cập nhật thành công';
    $_SESSION['messageClass'] = 'alert-success';
    $_SESSION['iconClass'] = 'fas fa-check';

    header('location: list-categories.php');
    exit();
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
    $pg_param[] = $param['cid'];
    $pg_param[] = getDatetimeNow();

    $sql  = "";
    $sql .= "UPDATE category SET                                ";
    $sql .= "       deldate = $3                                ";
    $sql .= "     , updatedate = $3                             ";
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

/**
 * Count news by category
 * @param $con
 * @param $func_id
 * @param $idCate
 * @return mixed
 */
function countNewsByCategory($con, $func_id, $cid){
    $recCnt = 0;
    $cntNews = array();
    $pg_param = array();
    $pg_param[] = $cid;

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
 * Get date time now
 * @return string
 */
function getDatetimeNow(){
    $datenow = '';
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    $datenow = date("Y-m-d H:i:s");
    return $datenow;
}
?>

