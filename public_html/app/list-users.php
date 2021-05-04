<?php

//Common setting
require_once('config.php');
require_once('lib.php');

//Initialization
$func_id = 'list_users';
$message = '';
$messageClass = '';
$iconClass = '';
$unBan = 0; //0: un ban; 1: ban

session_start();

//Get param
$param = getParam();

//Connect DB
$con = openDB();

$role       = $_SESSION['role'] ?? '';
$roleParam  = $param['role'] ?? '';
$fullName   = $param['fullname'] ?? '';
$loginId    = $param['loginId'] ?? '';
$dateForm   = $param['dateForm'] ?? '';
$dateTo     = $param['dateTo'] ?? '';
$uid        = $param['uid'] ?? '';
$mode       = $param['mode'] ?? '';

//Set data to combox role
$htmlRole = '';
$htmlRole = getAllRole($con, $func_id, $roleParam);

//Get data user
$htmlUser = '';
$htmlUser = getUserAndSearch($con, $func_id, $fullName, $loginId, $dateForm, $dateTo, $roleParam);

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

if ($param) {
    if (isset($param['registFlg']) && $param['registFlg'] == 1) {
        $mes = validateDataSearch($fullName, $loginId, $dateForm, $dateTo);
        
        $message = join('<br>', $mes);
        if (strlen($message)) {
            $messageClass = 'alert-danger';
            $iconClass = 'fas fa-ban';
        }
        
        if (empty($mes)){
            getUserAndSearch($con, $func_id, $fullName, $loginId, $dateForm, $dateTo, $roleParam);
        }
    }
    if ($mode == 'ban'){
        banUser($con, $func_id, $uid);
    }
    
    if ($mode == 'unban'){
        unBanUser($con, $func_id, $uid);
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
        
        //Button Ban
        $('.ban_user').on('click', function(e) {
            e.preventDefault();
            var message = "Tài khoản này sẽ bị khoá. Bạn có chắc chắn?";
            var form = $(this).closest("form");
            sweetConfirm(2, message, function(result) {
                if (result){
                    form.submit();
                }
            });
        });
        
        //Button un ban
        $('.un_ban_user').on('click', function(e) {
            e.preventDefault();
            var message = "Tài khoản này sẽ được khôi phục. Bạn có chắc chắn?";
            var form = $(this).closest("form");
            sweetConfirm(4, message, function(result) {
                if (result){
                    form.submit();
                }
            });
        });
        
        //Button edit
        $('.edit_user').on('click', function(e) {
            e.preventDefault();
            var message = "Đi đến màn hình chỉnh sửa thông tin. Bạn có chắc chắn?";
            var form = $(this).closest("form");
            sweetConfirm(3, message, function(result) {
                if (result){
                    form.submit();
                }
            });
        });
        
        //paginate
        $(document).ready(function() {
            $(".table").paginate({
                rows: 5,           // Set number of rows per page. Default: 5
                position: "top",   // Set position of pager. Default: "bottom"
                jqueryui: false,   // Allows using jQueryUI theme for pager buttons. Default: false
                showIfLess: false, // Don't show pager if table has only one page. Default: true
                numOfPages: 5
            });
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
include($TEMP_APP_META_PATH);

echo <<<EOF
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
EOF;

//Preloader
//include ($TEMP_APP_PRELOADER_PATH);

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
                                <i class="fas fa-search"></i>&nbspTìm kiếm tài khoản</h1>
                        </div>
                        <!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="dashboard.php">Trang chủ</a></li>
                                <li class="breadcrumb-item active">Danh sách tài khoản</li>
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
                                        <label>Họ tên</label>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" placeholder="Họ tên" name="fullname" value="{$fullName}">
                                        </div>

                                        <label>Tên đăng nhập</label>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" placeholder="Tên đăng nhập" name="loginId" value="{$loginId}">
                                        </div>

                                        <label>Vai trò</label>
                                        <div class="input-group mb-3">
                                            <select class="custom-select" name="role">
                                                {$htmlRole}
                                              </select>
                                        </div>

                                        <label>Thời gian</label>
                                        <div class="row">
                                            <div class="input-group mb-6 col-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                                </div>
                                                <input type="date" class="form-control" name="dateForm" value="{$dateForm}">
                                            </div>
                                            <span><b>~</b></span>
                                            <div class="input-group mb-6 col-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                                </div>
                                                <input type="date" class="form-control" name="dateTo" value="{$dateTo}">
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
                                            <button type="button" class="btn btn-default">
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
                                        <th style="text-align: center; width: 5%;" class="text-th">STT</th>
                                        <th style="width: 35%;" class="text-th">Họ tên</th>
                                        <th style="width: 20%;" class="text-th">Tên đăng nhập</th>
                                        <th style="text-align: center; width: 20%;" class="text-th">Vai trò</th>
                                        <th style="text-align: center; width: 20%;" class="text-th">Trạng thái</th>
                                        <th colspan="3"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {$htmlUser}
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
 * Get all role -> Combox
 * @param $con
 * @param $func_id
 * @param $roleParam
 * @return string
 */
function getAllRole($con, $func_id, $roleParam)
{
    $pg_param = array();
    $recCnt = 0;
    $roleArray = array();
    
    $sql = "";
    $sql .= "SELECT*FROM role";
    
    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query) {
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    } else {
        $recCnt = pg_num_rows($query);
    }
    
    $html = '<option value="0">Chọn vai trò</option>';
    if ($recCnt != 0) {
        while ($row = pg_fetch_assoc($query)) {
            $roleId = $row['id'];
            $roleName = $row['rolename'];
            
            $selected = '';
            if ($roleParam == $roleId) {
                $selected = 'selected="selected"';
            }
            
            $html .= <<<EOF
                <option value="{$roleId}" {$selected}>{$roleName}</option>
EOF;
        
        }
    }
    return $html;
}

/**
 * Validation data
 * @param $fullName
 * @param $loginId
 * @param $dateForm
 * @param $dateTo
 * @param $maxStr
 * @return array
 */
function validateDataSearch($fullName, $loginId, $dateForm, $dateTo)
{
    $maxStr = 200;

    $mes = [
        'chk_format'     => [],
        'chk_max_length' => []
    ];
    
    if (mb_strlen($fullName) > $maxStr) {
        $mes['chk_max_length'][] = 'Họ tên không được nhập quá ' . $maxStr . ' ký tự.';
    }
    
    if (mb_strlen($loginId) > $maxStr) {
        $mes['chk_max_length'][] = 'Tên đăng nhập không được nhập quá ' . $maxStr . ' ký tự.';
    }

    if (!empty($dateForm) && !empty($dateTo) && (strtotime($dateForm) > strtotime($dateTo))){
        $mes['chk_format'][] = 'Không thể tìm kiếm với thông tin ' .$dateForm. ' lớn hơn '.$dateTo.'';
    }
    
    $msg = array_merge(
        $mes['chk_format'],
        $mes['chk_max_length']
    );
    
    return $msg;
}

/**
 * Search function
 * @param $con
 * @param $func_id
 * @param $fullName
 * @param $loginId
 * @param $dateForm
 * @param $dateTo
 * @param $roleParam
 * @return string
 */
function getUserAndSearch($con, $func_id, $fullName, $loginId, $dateForm, $dateTo, $roleParam){
    $pg_param = array();
    $pg_sql = array();
    $recCnt = 0;
    $cnt = 0;
    
    if (!empty($fullName) && (mb_strlen($fullName) > 0)){
        $pg_param[] = '%'.$fullName.'%';
        $cnt++;
        $pg_sql[] = " AND fullname ILIKE $".$cnt."             ";
    }
    
    if (!empty($loginId) && (mb_strlen($loginId) > 0)){
        $pg_param[] = '%'.$loginId.'%';
        $cnt++;
        $pg_sql[] = " AND loginid ILIKE $".$cnt."              ";
    }
    
    if (!empty($dateForm) && (mb_strlen($dateForm) > 0)){
        $pg_param[] = $dateForm;
        $cnt++;
        $pg_sql[] = " AND createDate >= $".$cnt."              ";
    }
    
    if (!empty($dateTo) && (mb_strlen($dateTo) > 0)){
        $pg_param[] = $dateTo;
        $cnt++;
        $pg_sql[] = " AND createDate <= $".$cnt."              ";
    }
    
        if ((mb_strlen($roleParam) > 0) && $roleParam > 0){
        $pg_param[] = $roleParam;
        $cnt++;
        $pg_sql[] = " AND role = $".$cnt."                     ";
    }
    
    $wheresql = join(' ', $pg_sql);
    
    $sql = "";
    $sql .= "SELECT users.id                  ";
    $sql .= "     , users.fullname            ";
    $sql .= "     , users.loginid             ";
    $sql .= "     , users.status              ";
    $sql .= "     , users.role                ";
    $sql .= "     , role.rolename             ";
    $sql .= "  FROM users                     ";
    $sql .= "  INNER JOIN role                ";
    $sql .= "    ON users.role = role.id      ";
    $sql .= " WHERE deldate IS NULL           ";
    $sql .= $wheresql;
    $sql .= " ORDER BY role ASC               ";
    
    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query){
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    } else {
        $recCnt = pg_num_rows($query);
    }
    
    $html = '';
    if ($recCnt != 0){
        while ($row = pg_fetch_assoc($query)){
            if ($row['status'] == 't'){
                $strStatus = "Đang hoạt động";
                $iconBg = "btn-danger";
                $iconStatus = "fa-ban";
                $btnStatus = "ban_user";
                $mode = "ban";
            } else {
                $strStatus = "Vô hiệu hoá";
                $iconBg = "btn-success";
                $iconStatus = "fa-check";
                $btnStatus = "un_ban_user";
                $mode = "unban";
            }

            $htmlButtonBan = '';
            if ($_SESSION['role'] == $row['role']){
                $htmlButtonBan .= <<< EOF
                    <button class="btn btn-block {$iconBg} btn-sm" disabled><i class="fas fa-ban"></i></button>

EOF;
            } else {
                $htmlButtonBan .= <<< EOF
                    <form action="{$_SERVER['SCRIPT_NAME']}" method="POST">
                        <input type="hidden" name="uid" value="{$row['id']}">
                        <input type="hidden" name="mode" value="{$mode}">
                        <a href="#" class="btn btn-block {$iconBg} btn-sm {$btnStatus}"><i class="fas {$iconStatus}"></i></a>
                    </form>
EOF;
            }
            
            $htmlEditButton = '';
            if ($_SESSION['role'] != 1){
                $htmlEditButton .= <<< EOF
                    <button class="btn btn-block btn-primary btn-sm edit_user" disabled><i class="fas fa-edit"></i></button>
EOF;
            } else {
                $htmlEditButton .=<<< EOF
                    <form action="detail-user.php" method="POST">
                        <input type="hidden" name="mode" value="update">
                        <input type="hidden" name="uid" value="{$row['id']}">
                        <a href="" class="btn btn-block btn-primary btn-sm edit_user"><i class="fas fa-edit"></i></a>
                    </form>
EOF;
            }
            
            $cnt++;
            $html .= <<< EOF
                <tr>
                    <td style="text-align: center; width: 5%;">{$cnt}</td>
                    <td style="width: 35%;">{$row['fullname']}</td>
                    <td style="width: 20%;">{$row['loginid']}</td>
                    <td style="text-align: center; width: 20%;">{$row['rolename']}</td>
                    <td style="text-align: center; width: 20%;">{$strStatus}</td>
                    <td style="text-align: center; width: 5%;">
                        {$htmlEditButton}
                    </td>
                    <td style="text-align: center; width: 5%;">
                        {$htmlButtonBan}
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
 * Blocl user function
 * @param $con
 * @param $func_id
 * @param $uid
 */
function banUser($con, $func_id, $uid){
    $pg_param = array();
    $pg_param[] = $uid;
    $recCnt = 0;
    
    $sql = "";
    $sql .= "UPDATE users                             ";
    $sql .= "   SET status = false                    ";
    $sql .= " WHERE deldate IS NULL                   ";
    $sql .= "   AND users.id = $1                     ";
    
    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query){
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    }
    header('location: list-users.php');
}

function unBanUser($con, $func_id, $uid){
    $pg_param = array();
    $pg_param[] = $uid;
    $recCnt = 0;
    
    $sql = "";
    $sql .= "UPDATE users                             ";
    $sql .= "   SET status = true                     ";
    $sql .= " WHERE deldate IS NULL                   ";
    $sql .= "   AND users.id = $1                     ";
    
    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query){
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    }
    header('location: list-users.php');
}
?>

