<?php

//Common setting
require_once ('config.php');
require_once ('lib.php');

//Initialization
$func_id = 'detail_user';
$message = '';
$messageClass = '';
$displayPopupConfirm = 1; //0: show popup, 1: not show
$strTitle = 'Tạo tài khoản';

session_start();

//Get param
$param = getParam();

$role = $_SESSION['role'] ?? '';
$mode = $param['mode'] ?? 'new';
$uid = $param['uid'] ?? '';
$saveFlag = $param['saveFlag'] ?? '';

//Connect DB
$con = openDB();

if (!isset($_SESSION['loginId']) || !isset($_SESSION['fullname'])){
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

if ($param){
    if (isset($param['registFlg']) && $param['registFlg'] == 1){
        $mes = validateData($con, $func_id, $param);

        $message = join('<br>', $mes);
        if (strlen($message)) {
            $messageClass = 'alert-danger';
            $iconClass = 'fas fa-ban';
        } else {
            $displayPopupConfirm = 0;
        }

        if ($mode == 'delete'){
            deletetUser($con, $func_id, $uid);
        } else{
            if (empty($mes)){
                if ($_SESSION['fullname'] != $param['createBy']){
                    $mes[] = 'Người tạo không tồn tại';
                } elseif ($saveFlag == 1) {
                    //Insert
                    if ($mode == 'new'){
                        insertUser($con, $func_id, $param);
                    }
                    //Update
                    if ($mode == 'update'){
                        updatetUser($con, $func_id, $param, $uid);
                    }
                }
            }
        }
    }
}

$htmlBtnDelete = '';
$htmlInputPassword = '';
if (isset($uid) && (mb_strlen($uid) > 0)){
    $strTitle = 'Cập nhật tài khoản';
    //Get user inf
    $userInf = getUserInf($con, $func_id, $uid);
    
    $valueFullname = $param['fullname'] ?? $userInf['fullname'];
    $valueRole = $param['role'] ?? $userInf['role'];
    $valueEmail = $param['email'] ?? $userInf['email'];
    $valueLoginId = $param['loginId'] ?? $userInf['loginid'];
    $valuePassword = $param['password'] ?? $userInf['password'];
    
    if ($_SESSION['loginId'] != $userInf['loginid']){
        $htmlBtnDelete .= <<< EOF
            <a href="" id="deleteUser" class="btn btn-danger">
                <i class="fas fa-trash"></i>
                &nbspXoá
            </a>
EOF;
    }
} else {
    $valueFullname = $param['fullname'] ?? '';
    $valueRole = $param['role'] ?? '';
    $valueEmail = $param['email'] ?? '';
    $valueLoginId = $param['loginId'] ?? '';
    $valuePassword = $param['password'] ?? '';

    $htmlInputPassword .= <<< EOF
        <label>Mật khẩu&nbsp<span class="badge badge-danger">Bắt buộc</span></label>
        <div class="input-group mb-3">
            <input type="password" class="form-control" placeholder="Mật khẩu" name="password" value="{$valuePassword}">
        </div>
EOF;
}

//Get role combox
$htmlRoleSelect = getComboxRole($con, $func_id, $valueRole);

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
$scriptHTML = <<< EOF
<script>
$(function() {
    if ({$displayPopupConfirm} == 0){
        if ('{$mode}' == 'new'){
            var message = "Thông tin tài khoản sẽ được tạo. Bạn chắc chứ?";
            type = 5;
        } else {
            var message = "Thông tin tài khoản sẽ được cập nhật. Bạn chắc chứ?";
            type = 3;
        }
        sweetConfirm(type, message, function(result) {
            if (result){
                $('<input>').attr({
                    type: 'hidden',
                    name: 'saveFlag',
                    value: 1
                }).appendTo('form#form-edit');
                $('#form-edit').submit();
            }
        });   
    }
    
    //Button delete
    $('#deleteUser').on('click', function(e) {
        e.preventDefault();
        var message = "Tài khoản này sẽ bị xoá. Bạn chắc chứ?";
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
                        <i class="fas fa-folder-plus"></i>&nbsp{$strTitle}</h1>
                </div>
                <!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Trang chủ</a></li>
                        <li class="breadcrumb-item active">Tạo tài khoản</li>
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
                    <a href="list-users.php" class="btn btn-primary float-right mr-3" style="background-color: #17a2b8;" title="Danh sách người dùng">
                        <i class="fas fa-backward"></i>
                        &nbspTrở lại
                    </a>
                </div>
            </div>
            <div class="row">
                <div class="card-body">
                    {$messageHtml}
                    <form action="{$_SERVER['SCRIPT_NAME']}" method="POST" id="form-edit">
                        <div class="card card-info">
                            <div class="card-header">
                                <h3 class="card-title">Tạo tài khoản</h3>
                            </div>
                            <div class="card-body">
                                <label>Họ tên&nbsp<span class="badge badge-danger">Bắt buộc</span></label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" placeholder="Họ tên" name="fullname" value="{$valueFullname}">
                                </div>

                                <label>Người tạo</label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" value="{$_SESSION['fullname']}" readonly name="createBy">
                                </div>

                                <label>Vai trò&nbsp<span class="badge badge-danger">Bắt buộc</span></label>
                                <div class="input-group mb-3">
                                    {$htmlRoleSelect}
                                </div>

                                <label>Email&nbsp<span class="badge badge-danger">Bắt buộc</span></label>
                                <div class="input-group mb-3">
                                    <input type="text"  class="form-control" placeholder="Email" name="email" value="{$valueEmail}">
                                </div>

                                <label>Tên đăng nhập&nbsp<span class="badge badge-danger">Bắt buộc</span></label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" placeholder="Tên đăng nhập" name="loginId" value="{$valueLoginId}">
                                </div>
                                
                                {$htmlInputPassword}
                            </div>
                            <!-- /.card-body -->
                            <div class="card-footer">
                                <input type="hidden" class="mode" name="mode" value="{$mode}">
                                <input type="hidden" name="registFlg" value="1">
                                <input type="hidden" name="uid" value="{$uid}">
                                {$htmlBtnDelete}
                                <button type="submit" class="btn btn-primary float-right" id="saveUser" style="background-color: #17a2b8;">
                                    <i class="fas fa-save"></i>
                                    &nbspLưu
                                </button>
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
 * get user inf
 * @param $con
 * @param $func_id
 * @param $uid
 * @return array
 */
function getUserInf($con, $func_id, $uid){
    $pg_param = array();
    $userArray = array();
    $pg_param[] = $uid;
    $recCnt = 0;
    
    $sql = "";
    $sql .= "SELECT users.id                  ";
    $sql .= "     , users.fullname            ";
    $sql .= "     , users.email               ";
    $sql .= "     , users.loginid             ";
    $sql .= "     , users.createby            ";
    $sql .= "     , users.password            ";
    $sql .= "     , users.role                ";
    $sql .= "     , role.rolename             ";
    $sql .= "  FROM users                     ";
    $sql .= "  INNER JOIN role                ";
    $sql .= "    ON users.role = role.id      ";
    $sql .= " WHERE deldate IS NULL           ";
    $sql .= "  AND users.id = $1              ";
    
    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query){
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    } else {
        $recCnt = pg_num_rows($query);
    }
    
    if ($recCnt != 0){
        $userArray = pg_fetch_assoc($query);
    }
    return $userArray;
}

/**
 * Get loginid for check dup
 * @param $con
 * @param $func_id
 * @param $param
 * @return array
 */
function getCheckDupLogId($con, $func_id, $param){
    $recCnt = 0;
    $dataLoginId = array();
    $pg_param = array();
    $pg_param[] = $param['loginId'];

    $sql = "";
    $sql .= "SELECT loginid          ";
    $sql .= "  FROM users            ";
    $sql .= " WHERE loginid = $1     ";
    $sql .= "   AND deldate IS NULL  ";

    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query){
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    } else {
        $recCnt = pg_num_rows($query);
    }

    if ($recCnt != 0){
        $dataLoginId = pg_fetch_assoc($query);
    }
    return $dataLoginId;
}

/**
 * Get email for check dup
 * @param $con
 * @param $func_id
 * @param $param
 * @return array
 */
function getCheckDupEmail($con, $func_id, $param){
    $recCnt = 0;
    $dataEmail = array();
    $pg_param = array();
    $pg_param[] = $param['email'];

    $sql = "";
    $sql .= "SELECT email            ";
    $sql .= "  FROM users            ";
    $sql .= " WHERE email = $1       ";
    $sql .= "   AND deldate IS NULL  ";

    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query){
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    } else {
        $recCnt = pg_num_rows($query);
    }

    if ($recCnt != 0){
        $dataEmail = pg_fetch_assoc($query);
    }
    return $dataEmail;
}

/**
 * Get role combox
 * @param $con
 * @param $func_id
 * @param $valueRole
 * @return string
 */
function getComboxRole($con, $func_id, $valueRole){
    $pg_param = array();
    $recCnt = 0;
    
    $sql = "";
    $sql .= "SELECT DISTINCT     ";
    $sql .= "       id           ";
    $sql .= "     , rolename     ";
    $sql .= "  FROM role         ";
    $sql .= "  ORDER BY id ASC   ";
    
    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query){
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    } else {
        $recCnt = pg_num_rows($query);
    }
    
    $html = '<select class="custom-select" name="role">';
    $html .= '<option value="0">Chọn vai trò</option>';
    if ($recCnt != 0){
        while ($row = pg_fetch_assoc($query)){
            $selected = '';
            if ($valueRole == $row['id']){
                $selected = 'selected="selected"';
            }
            $html .= '<option value="'.$row['id'].'" '.$selected.'>'.$row['rolename'].'</option>';
        }
    }
    $html .= '</select>';
    return $html;
}

/**
 * Add user function
 * @param $con
 * @param $func_id
 * @param $param
 */
function insertUser($con, $func_id, $param){
    $pg_param = array();
    $pg_param[] = $param['fullname'];
    $pg_param[] = $param['role'];
    $pg_param[] = $param['email'];
    $pg_param[] = $param['loginId'];
    $pg_param[] = $_SESSION['loginId'];
    $pg_param[] = md5($param['password']);

    $sql = "";
    $sql .= "INSERT INTO users(                          ";
    $sql .= "            fullname                           ";
    $sql .= "          , createdate                         ";
    $sql .= "          , role                               ";
    $sql .= "          , email                              ";
    $sql .= "          , loginid                            ";
    $sql .= "          , createby                           ";
    $sql .= "          , password)                          ";
    $sql .= "  VALUES(                                      ";
    $sql .= "            $1                                 ";
    $sql .= "          , '".date('Y/m/d')."'         ";
    $sql .= "          , $2                                 ";
    $sql .= "          , $3                                 ";
    $sql .= "          , $4                                 ";
    $sql .= "          , $5                                 ";
    $sql .= "          , $6)                                ";
    
    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query){
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    }
    
    $_SESSION['message'] = 'Tạo tài khoản thành công';
    $_SESSION['messageClass'] = 'alert-success';
    $_SESSION['iconClass'] = 'fas fa-check';
    
    header('location: list-users.php');
    exit();
}

/**
 * Update user function
 * @param $con
 * @param $func_id
 * @param $param
 * @param $uid
 */
function updatetUser($con, $func_id, $param, $uid){
    $pg_param = array();
    $pg_param[] = $param['fullname'];
    $pg_param[] = $param['role'];
    $pg_param[] = $param['email'];
    $pg_param[] = $param['loginId'];
    $pg_param[] = $_SESSION['loginId'];
    $pg_param[] = $uid;
    
    $sql = "";
    $sql .= "UPDATE users SET                                      ";
    $sql .= "       fullname = $1                                  ";
    $sql .= "     , updatedate = '".date('Y/m/d H:i:s')."'  ";
    $sql .= "     , role = $2                                      ";
    $sql .= "     , email = $3                                     ";
    $sql .= "     , loginid = $4                                   ";
    $sql .= "     , updateby = $5                                  ";
    $sql .= " WHERE id = $6                                        ";
    
    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query){
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    }
    
    $_SESSION['message'] = 'Cập nhật tài khoản thành công';
    $_SESSION['messageClass'] = 'alert-success';
    $_SESSION['iconClass'] = 'fas fa-check';
    
    header('location: list-users.php');
    exit();
}

/**
 * Delete user function
 * @param $con
 * @param $func_id
 * @param $uid
 */
function deletetUser($con, $func_id, $uid){
    $pg_param = array();
    $pg_param[] = $_SESSION['loginId'];
    $pg_param[] = $uid;
    
    $sql = "";
    $sql .= "UPDATE users SET                                         ";
    $sql .= "       deldate = '".date('Y/m/d H:i:s')."'        ";
    $sql .= "     , updateby = $1                                     ";
    $sql .= "     , updatedate = '".date('Y/m/d H:i:s')."'     ";
    $sql .= " WHERE id = $2                                           ";
   
    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query){
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    }
    
    $_SESSION['message'] = 'Tài khoản đã được xoá thành công';
    $_SESSION['messageClass'] = 'alert-success';
    $_SESSION['iconClass'] = 'fas fa-check';
    
    header('location: list-users.php');
    exit();
}

/**
 * Validation data
 * @param $con
 * @param $func_id
 * @param $param
 * @return array
 */
function validateData($con, $func_id, $param){
    $dupLoginId = getCheckDupLogId($con, $func_id, $param);
    $dupEmail   = getCheckDupEmail($con, $func_id, $param);

    $mes = [
        'chk_required'   => [],
        'chk_format'     => [],
        'chk_max_length' => []
    ];
    
    if (empty($param['fullname'])){
        $mes['chk_required'][] = 'Vui lòng nhập họ tên.';
    } elseif (mb_strlen($param['fullname']) > 254){
        $mes['chk_max_length'][] = 'Họ tên phải bé hơn 254 ký tự.';
    }

    if ($param['role'] == 0){
        $mes['chk_required'][] = 'Vui lòng chọn vai trò cho tài khoản.';
    }
    
    if (empty($param['email'])){
        $mes['chk_required'][] = 'Vui lòng nhập email.';
    } elseif (!preg_match('/^[\w\.\-_]+@[\w\.\-_]+\.\w+$/', $param['email'])){
        $mes['chk_format'][] = 'Email không đúng định dạng. Ví dụ: abc@gmail.com';
    } elseif (mb_strlen($param['email']) > 254 || mb_strlen($param['email']) < 6){
        $mes['chk_max_length'][] = 'Email phải lớn hơn 6 ký tự và bé hơn 254 ký tự.';
    }

    if (empty($param['loginId'])){
        $mes['chk_required'][] = 'Vui lòng nhập tên đăng nhập.';
    } elseif (!preg_match('/^[0-9A-Za-z]/', $param['loginId']) || preg_match('/^(?=.*[@#\-_$%^&+=§!\?])/', $param['loginId'])){
        $mes['chk_format'][] = 'Tên đăng nhập không được chứa kí tự đặc biệt.';
    }
    elseif (mb_strlen($param['loginId']) > 254 || mb_strlen($param['loginId']) < 6){
        $mes['chk_max_length'][] = 'Tên đăng nhập phải hơn 6 ký tự và bé hơn 254 ký tự.';
    }

    if ($param['mode'] == 'new'){
        if (empty($param['password'])){
            $mes['chk_required'][] = 'Vui lòng nhập mật khẩu.';
        } elseif (!preg_match('/^(?=.*[0-9A-Za-z])/', $param['password']) || !preg_match('/^(?=.*[@#\-_$%^&+=§!\?])/', $param['password'])){
            $mes['chk_format'][] = 'Mật khẩu không đúng định dạng, phải có ít nhất 1 chữ hoặc số và ký tự đặc biệt.';
        }
        elseif (mb_strlen($param['password']) > 254 || mb_strlen($param['password']) < 6){
            $mes['chk_max_length'][] = 'Mật khẩu phải lớn hơn 6 ký tự và bé hơn 254 ký tự.';
        }
    }
    
    $msg = array_merge(
        $mes['chk_required'],
        $mes['chk_format'],
        $mes['chk_max_length']
    );

    if ($param['mode'] == 'new'){
        if (empty($msg)){
            if (!empty($dupEmail)){
                $msg[] = 'Email đã được sử dụng';
            }

            if (!empty($dupLoginId)){
                $msg[] = 'Tên đăng nhập đã được sử dụng';
            }
        }
    }
    return $msg;
}
?>

