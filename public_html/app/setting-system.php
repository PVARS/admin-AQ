<?php

//Common setting
require_once ('config.php');
require_once ('lib.php');

//Initialization
$funcId         = 'setting-system';
$arr_apps       = array();
$message        = '';
$messageClass   = '';
$iconClass      = '';
$maxId          = '';

session_start();

//Connect DB
$con = openDB();

//Get param
$param = getParam();
$mode  = $param['mode'] ?? 'new';
$role  = $_SESSION['role'] ?? '';

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

$strPost = "Cập nhật chuyển nhượng 20-05-2021: Tương lai của Emile Smith-Rowe, Martin Ødegaard; Lucas Torreira và Willian nhiều khả năng sẽ rời Arsenal";
echo 'Trước: ' . $strPost . '<br/>Sau: ' . convert_name($strPost);

$maxId = getMaxIdApps($con, $funcId);
if (!empty($maxId)) {
    $arr_apps       = getApps($con, $funcId, $maxId);
    $fromName       = $arr_apps['mailname'];
    $username       = $arr_apps['mailusername'];
    $password       = $arr_apps['mailpassword'];
    $charset        = $arr_apps['mailcharset'];
    $host           = $arr_apps['mailhost'];
    $smtpAuth       = $arr_apps['mailsmtpauth'];
    $smtpSecure     = $arr_apps['mailsmtpsecure'];
    $port           = $arr_apps['mailport'];
    $body           = $arr_apps['mailbody'];
    $firebaseConfig = $arr_apps['firebaseconfig'];

    $selected0 = '';
    $selected1 = '';
    if (!empty($smtpAuth)) {
        if ($smtpAuth == 'f'){
            $selected0 = 'selected="selected"';
        } else {
            $selected1 = 'selected="selected"';
        }
    }
    $mode = 'update';
}
else {
    $fromName         = '';
    $username         = '';
    $password         = '';
    $charset          = '';
    $host             = '';
    $smtpAuth         = '';
    $smtpSecure       = '';
    $port             = '';
    $body             = '';
    $firebaseConfig   = '';
    $selected0        = '';
    $selected1        = '';
}

if ($param) {
    if (isset($param['registFlg']) && $param['registFlg'] == 1) {
        if ($mode == 'new') {
            insertApps($con, $funcId, $param);
        }
        if ($mode == 'update') {
            updateApps($con, $funcId, $param, $maxId);
        }

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
$titleHTML = 'Cài đặt hệ thống';
$cssHTML = '';
$scriptHTML = <<< EOF
<script>
    const ipnElement = document.querySelector('#ipnPassword');
    const btnElement = document.querySelector('#btnPassword');
    
    btnElement.addEventListener('click', function() {
      const currentType = ipnElement.getAttribute('type')
      ipnElement.setAttribute(
        'type',
        currentType === 'password' ? 'text' : 'password'
      )
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
                    <h1 class="m-0">{$titleHTML}</h1>
                </div>
                <!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Trang chủ</a></li>
                        <li class="breadcrumb-item active">Cài đặt hệ thống</li>
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
                <div class="card-body">
                    {$messageHtml}
                    <form action="{$_SERVER['SCRIPT_NAME']}" method="POST">
                        <div class="card card-info">
                            <div class="card-header">
                                <h3 class="card-title">Cài đặt</h3>
                            </div>
                            <div class="card-body">                                                        
                                <label>Tên email</label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" placeholder="Tên email" name="fromname" value="{$fromName}">
                                </div>
                            
                                <label>Email</label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" placeholder="Email" name="username" value="{$username}">
                                </div>

                                <label>Mật khẩu</label>
                                <div class="input-group mb-3">
                                    <input type="password" class="form-control" id="ipnPassword" placeholder="Mật khẩu" name="password" value="{$password}">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" id="btnPassword" title="Hiển thị mật khẩu">
                                            <span class="fas fa-eye"></span>
                                        </button>
                                    </div>
                                </div>

                                <label>Charset</label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" placeholder="Charset" name="charset" value="{$charset}">
                                </div>
                                
                                <label>Host</label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" placeholder="Host" name="host" value="{$host}">
                                </div>
                                
                                <label>SMTP Auth</label>
                                <div class="input-group mb-3">
                                    <select class="custom-select" name="smtpauth">
                                        <option value="1" {$selected1}>Có</option>
                                        <option value="0" {$selected0}>Không</option>
                                    </select>
                                </div>
                                
                                <label>SMTP Secure</label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" placeholder="SMTP Secure" name="smtpsecure" value="{$smtpSecure}">
                                </div>
                                
                                <label>Port</label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" placeholder="Port" name="port" value="{$port}">
                                </div>
                                
                                <label>Nội dung email</label>
                                <ul class="text-muted font-italic">
                                    <li>Tên của người nhận phải để mặc định là <b>\$fullname</b>.</li>
                                    <li>Đường dẫn của người nhận phải để mặc định là <b>\$emailLink</b>.</li>
                                </ul>
                                <textarea id="summernote" name="body">{$body}</textarea>
                                                                
                                <label class="mt-3">Cấu hình Firebase</label>
                                <div class="input-group">
                                    <textarea name="firebaseConfig" class="form-control" rows="9">{$firebaseConfig}</textarea>
                                </div>
                                
                            </div>
                            <!-- /.card-body -->
                            <div class="card-footer">
                                <input type="hidden" name="registFlg" value="1">
                                <input type="hidden" name="mode" value="{$mode}">
                                <button type="submit" class="btn btn-primary float-right" style="background-color: #17a2b8;">
                                  <i class="fas fa-cog"></i>
                                  &nbspCài đặt
                                </button>
                            </div>
                        </div>
                    </form>
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
 * Insert apps
 * @param $con
 * @param $funcId
 * @param $param
 */
function insertApps($con, $funcId, $param){
    $pg_param   = array();
    $pg_param[] = 1;
    $pg_param[] = $param['fromname'];
    $pg_param[] = $param['username'];
    $pg_param[] = $param['password'];
    $pg_param[] = $param['charset'];
    $pg_param[] = $param['host'];
    $pg_param[] = $param['smtpauth'];
    $pg_param[] = $param['smtpsecure'];
    $pg_param[] = $param['port'];
    $pg_param[] = $param['body'];
    $pg_param[] = $param['firebaseConfig'];

    $sql = '';
    $sql .= "INSERT INTO PUBLIC.APPS(";
    $sql .= "						ID,                                 ";
    $sql .= "						MAILNAME,                           ";
    $sql .= "						MAILUSERNAME,                       ";
    $sql .= "						MAILPASSWORD,                       ";
    $sql .= "						MAILCHARSET,                        ";
    $sql .= "						MAILHOST,                           ";
    $sql .= "						MAILSMTPAUTH,                       ";
    $sql .= "						MAILSMTPSECURE,                     ";
    $sql .= "						MAILPORT,                           ";
    $sql .= "						MAILBODY,                           ";
    $sql .= "						FIREBASECONFIG)                     ";
    $sql .= " VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11);    ";

    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query) {
        systemError('systemError(' . $funcId . ') SQL Error：', $sql . print_r($pg_param, true));
    }

    $_SESSION['message'] = 'Tạo cài đặt thành công!';
    $_SESSION['messageClass'] = 'alert-success';
    $_SESSION['iconClass'] = 'fas fa-check';

    header('location: setting-system.php');
    exit();
}

/**
 * @param $con
 * @param $funcId
 * @param $param
 */
function updateApps($con, $funcId, $param, $maxId){
    $pg_param   = array();
    $pg_param[] = $param['fromname'];
    $pg_param[] = $param['username'];
    $pg_param[] = $param['password'];
    $pg_param[] = $param['charset'];
    $pg_param[] = $param['host'];
    $pg_param[] = $param['smtpauth'];
    $pg_param[] = $param['smtpsecure'];
    $pg_param[] = $param['port'];
    $pg_param[] = $param['body'];
    $pg_param[] = $param['firebaseConfig'];
    $pg_param[] = $maxId;

    $sql = '';
    $sql .= "UPDATE APPS                         ";
    $sql .= "	SET MAILNAME = $1,               ";
    $sql .= "		MAILUSERNAME = $2,           ";
    $sql .= "		MAILPASSWORD = $3,           ";
    $sql .= "		MAILCHARSET = $4,            ";
    $sql .= "		MAILHOST = $5,               ";
    $sql .= "		MAILSMTPAUTH = $6,           ";
    $sql .= "		MAILSMTPSECURE = $7,         ";
    $sql .= "		MAILPORT = $8,               ";
    $sql .= "		MAILBODY = $9,               ";
    $sql .= "		FIREBASECONFIG = $10         ";
    $sql .= " WHERE ID = $11;                    ";

    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query) {
        systemError('systemError(' . $funcId . ') SQL Error：', $sql . print_r($pg_param, true));
    }

    $_SESSION['message'] = 'Lưu cài đặt thành công!';
    $_SESSION['messageClass'] = 'alert-success';
    $_SESSION['iconClass'] = 'fas fa-check';

    header('location: setting-system.php');
    exit();
}

/**
 * @param $con
 * @param $funcId
 * @return array
 */
function getApps($con, $funcId, $maxId){
    $pg_param   = array();
    $pg_param[] = $maxId;
    $apps       = array();
    $recCnt     = 0;

    $sql = "";
    $sql .= "SELECT MAILNAME,                   ";
    $sql .= "		MAILUSERNAME,               ";
    $sql .= "		MAILPASSWORD,               ";
    $sql .= "		MAILCHARSET,                ";
    $sql .= "		MAILHOST,                   ";
    $sql .= "		MAILSMTPAUTH,               ";
    $sql .= "		MAILSMTPSECURE,             ";
    $sql .= "		MAILPORT,                   ";
    $sql .= "		MAILBODY,                   ";
    $sql .= "		FIREBASECONFIG              ";
    $sql .= " FROM 	APPS                        ";
    $sql .= "WHERE 	ID = $1                     ";
    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query) {
        systemError('systemError(' . $funcId . ') SQL Error：', $sql . print_r($pg_param, true));
    } else {
        $recCnt = pg_num_rows($query);
    }

    if ($recCnt != 0){
        $apps = pg_fetch_assoc($query);
    }
    return $apps;
}

/**
 * @param $con
 * @param $funcId
 * @return mixed
 */
function getMaxIdApps($con, $funcId){
    $pg_param = array();
    $apps = array();
    $recCnt = 0;

    $sql = "";
    $sql .= "SELECT MAX(ID) id FROM apps          ";
    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query) {
        systemError('systemError(' . $funcId . ') SQL Error：', $sql . print_r($pg_param, true));
    } else {
        $recCnt = pg_num_rows($query);
    }

    if ($recCnt != 0){
        $apps = pg_fetch_assoc($query);
    }
    return $apps['id'];
}

?>

