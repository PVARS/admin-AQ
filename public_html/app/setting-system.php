<?php

//Common setting
require_once ('config.php');
require_once ('lib.php');

//Initialization
$funcId = 'setting-system';
$message = '';
$messageClass = '';
$iconClass = '';

session_start();

//Connect DB
$con = openDB();

//Get param
$param = getParam();

$role = $_SESSION['role'] ?? '';

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

if ($param){
    if (isset($param['registFlg']) && $param['registFlg'] == 1){}
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
$(function() {});
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
                                    <input type="password" class="form-control" placeholder="Tên email" name="nameEmail" value="">
                                </div>
                            
                                <label>Email</label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" placeholder="Email" name="email" value="">
                                </div>

                                <label>Mật khẩu</label>
                                <div class="input-group mb-3">
                                    <input type="password" class="form-control" placeholder="Mật khẩu" name="password" value="">
                                </div>

                                <label>Charset</label>
                                <div class="input-group mb-3">
                                    <input type="password" class="form-control" placeholder="Charset" name="charset" value="">
                                </div>
                                
                                <label>Host</label>
                                <div class="input-group mb-3">
                                    <input type="password" class="form-control" placeholder="Host" name="host" value="">
                                </div>
                                
                                <label>SMTP</label>
                                <div class="input-group mb-3">
                                    <select class="custom-select" name="smtp">
                                        <option value="0">Vui lòng chọn</option>
                                        <option value="1">true</option>
                                        <option value="2">false</option>
                                    </select>
                                </div>
                                
                                <label>Port</label>
                                <div class="input-group mb-3">
                                    <input type="password" class="form-control" placeholder="Port" name="port" value="">
                                </div>
                                
                                <label>SMTPSecure</label>
                                <div class="input-group mb-3">
                                    <input type="password" class="form-control" placeholder="SMTPSecure" name="smtpSecure" value="">
                                </div>
                                
                                <label>Nội dung email</label>
                                <textarea id="summernote" name="content"></textarea>
                            </div>
                            <!-- /.card-body -->
                            <div class="card-footer">
                                <input type="hidden" name="registFlg" value="1">
                                <button type="submit" class="btn btn-primary float-right" style="background-color: #17a2b8;">
                                  <i class="fas fa-save"></i>
                                  &nbspLưu
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

?>

