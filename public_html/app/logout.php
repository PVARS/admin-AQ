<?php

//Common setting
require_once ('config.php');
require_once ('lib.php');

//Initialization
$func_id = 'list_student';

session_start();

//Connect DB
$con = openDB();

if (!isset($_SESSION['loginId']) || !strlen($_SESSION['loginId'])){
    systemErrorPrint();
    exit();
} else {
    // Unset Cookie
    $cookie_name = 'siteAuth';
    if(isset($cookie_name)){
        if(isset($_COOKIE[$cookie_name])){
            unset($_COOKIE[$cookie_name]); 
            setcookie ($cookie_name, '', time() - 3600, '/');
            $_SESSION['loginId'] = '';
        }
    }

    session_unset();
}
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
<body class="hold-transition login-page">
    <div class="login-box">
EOF;

//Preloader
include ($TEMP_APP_PRELOADER_PATH);

//Conntent
echo <<<EOF
<div class="card card-outline card-primary">
            <div class="card-header text-center">
                <a href="logout.php" class="h1"><b>Arsenal</b>Quán</a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="icheck-primary">
                            Bạn đã đăng xuất khỏi hệ thống. <a href="login.php">Đăng nhập?</a>
                        </div>
                    </div>
                <!-- /.col -->
                </div>
            </div>
            <!-- /.card-body -->
        </div>
EOF;

//Meta JS
include ($TEMP_APP_METAJS_PATH);
echo <<<EOF
    </div>
</body>
</html>
EOF;

?>

