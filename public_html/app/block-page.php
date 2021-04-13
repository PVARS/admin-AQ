<?php

//Common setting
require_once ('config.php');
require_once ('lib.php');

//Initialization
$func_id = 'block-page';

session_start();

//Connect DB
$con = openDB();

if (!isset($_SESSION['loginId']) || !strlen($_SESSION['loginId'])){
    systemErrorPrint();
    exit();
} else {
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
                <a href="block-page.php" class="h1"><b>Arsenal</b>Quán</a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="icheck-primary">
                            Tài khoản của bạn đã bị khoá. Trở lại <a href="login.php">đăng nhập?</a>
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

