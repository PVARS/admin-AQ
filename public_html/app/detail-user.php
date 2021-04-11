<?php

//Common setting
require_once ('config.php');
require_once ('lib.php');

//Initialization
$func_id = 'list_student';

session_start();

//Get param
$param = getParam();

$role = $_SESSION['role'] ?? '';
$mode = $param['mode'] ?? 'new';
$uid = $param['uid'] ?? '';

//Connect DB
$con = openDB();

if (!isset($_SESSION['loginId'])){
    header('location: login.php');
    exit();
}

//Get user inf
$userInf = getUserInf($con, $func_id, $uid);
if (isset($uid) && (mb_strlen($uid) > 0)){
    $valueFullname = $userInf['fullname'];
    $valueRoleName = $userInf['rolename'];
    $valueRole = $userInf['role'];
    $valueEmail = $userInf['email'];
    $valueLoginId = $userInf['loginid'];
    $valuePassword = $userInf['password'];
} else {
    $valueFullname = $param['fullname'] ?? '';
    $valueRole = $param['rolename'] ?? '';
    $valueEmail = $param['email'] ?? '';
    $valueLoginId = $param['loginid'] ?? '';
    $valuePassword = $param['password'] ?? '';
}

//Get role combox
$htmlRoleSelect = getComboxRole($con, $func_id, $valueRole);

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
} else {
    include ($TEMP_APP_MENU_PATH);
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
                                <i class="fas fa-folder-plus"></i>&nbspTạo tài khoản</h1>
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
                        <div class="card-body">
                            <form action="" method="POST">
                                <div class="card card-info">
                                    <div class="card-header">
                                        <h3 class="card-title">Tạo tài khoản</h3>
                                    </div>
                                    <div class="card-body">
                                        <label>Họ tên</label>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" placeholder="Họ tên" name="fullname" value="{$valueFullname}">
                                        </div>

                                        <label>Người tạo</label>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" value="Lê Văn Lưu" readonly name="createBy">
                                        </div>

                                        <label>Vai trò</label>
                                        <div class="input-group mb-3">
                                            {$htmlRoleSelect}
                                        </div>

                                        <label>Email</label>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" placeholder="Email" value="{$valueEmail}">
                                        </div>

                                        <label>Tên đăng nhập</label>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" placeholder="Tên đăng nhập" name="loginId" value="{$valueLoginId}">
                                        </div>

                                        <label>Mật khẩu</label>
                                        <div class="input-group mb-3">
                                            <input type="password" class="form-control" placeholder="Mật khẩu" name="password" value="{$valuePassword}">
                                        </div>
                                    </div>
                                    <!-- /.card-body -->
                                    <div class="card-footer">
                                        <button type="submit" class="btn btn-primary float-right" style="background-color: #17a2b8;">
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
    $sql .= "  AND users.id = $1               ";
    
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
?>

