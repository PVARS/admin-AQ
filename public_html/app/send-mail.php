<?php

//Common setting
require_once ('config.php');
require_once ('lib.php');

//Initialization
$func_id = 'list_student';

session_start();

//Connect DB
$con = openDB();

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
//include ($TEMP_APP_PRELOADER_PATH);

//Conntent
echo <<<EOF
<div class="card card-outline card-primary">
            <div class="card-header text-center">
                <a href="send-mail.php" class="h1"><b>Arsenal</b>Quán</a>
            </div>
            <div class="card-body">
                <form action="{$_SERVER['SCRIPT_NAME']}" method="POST">
                    <div class="input-group mb-3">
                        <input type="text" name="email" class="form-control" placeholder="Email đã đăng ký" value="" autocomplete="off">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <i class="fas fa-envelope-square"></i>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="text" name="emailConfirm" class="form-control" placeholder="Nhập lại email" value="" autocomplete="off">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <i class="fas fa-envelope-square"></i>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-7">
                            <div class="icheck-primary">
                                <a href="login.php">
                                    <i class="fas fa-long-arrow-alt-left"></i>
                                    &nbspMàn hình đăng nhập
                                </a>
                            </div>
                        </div>
                        <!-- /.col -->
                        <div class="col-5">
                            <input type="hidden" name="registFlg" value="1">
                            <button type="submit" name="login" class="btn btn-primary btn-block">Gửi email</button>
                        </div>
                        <!-- /.col -->
                    </div>
                </form>
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

