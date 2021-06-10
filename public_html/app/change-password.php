<?php

//Common setting
require_once('config.php');
require_once('lib.php');

//Initialization
$func_id = 'change-password';
$message = '';
$messageHtml = '';
$messageClass = '';
$iconClass = '';
$minLoginId = 6;
$maxPassword = 16;
$maxLoginId = 254;
//$maxDatetimeToken = 60 * 15; // 15 minutes
$displayPopupConfirm = 1; //0: show popup, 1: not show

session_start();

//Connect DB
$con = openDB();

//Get param
$param = getParam();

$url_loginId   = $param['uid'] ?? '';
$url_token     = $param['token'] ?? '';
$f_password    = $param['password'] ?? '';
$f_cpassword   = $param['passwordConfirm'] ?? '';
$updateFlag    = $param['updateFlag'] ?? '';

$arr_queryString = getString($_SERVER['QUERY_STRING']);
if (empty($url_loginId) && empty($url_token)) {
    $url_loginId = $arr_queryString[0];  // Set loginId
    $url_token = $arr_queryString[1];    // Set token
}

$validate = checkValidate($con, $func_id, $url_loginId, $url_token, $f_password, $f_cpassword);

if ($param) {
    if (isset($param['registFlg']) && $param['registFlg'] == 1) {
        $mes = $validate;

        $message = join('<br>', $mes);
        if (strlen($message)) {
            $messageClass = 'alert-danger';
            $iconClass = 'fas fa-ban';
        } else {
            $displayPopupConfirm = 0;
        }

        if ($updateFlag == 1){
            updatePassword($con, $func_id, $url_loginId, $f_password);

            $_SESSION['message'] = 'Mật khẩu của bạn đã cập nhật';
            $_SESSION['messageClass'] = 'alert-success';
            $_SESSION['iconClass'] = 'fas fa-check';
            header('location: login.php');
            exit();
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
$titleHTML = '';
$cssHTML = '';
$scriptHTML = '';

$scriptHTML = <<<EOF
<script>
$(function() {
    if ({$displayPopupConfirm} == 0){
        var message = "Mật khẩu sẽ được cập nhật. Bạn chắc chứ?";
        sweetConfirm(3, message, function(result) {
            if (result){
                $('<input>').attr({
                    type: 'hidden',
                    name: 'updateFlag',
                    value: 1
                }).appendTo('form#changePassword');
                $('#changePassword').submit();
            }
        });   
    }
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
<body class="hold-transition login-page">
    <div class="login-box">
        {$messageHtml}
EOF;

//Preloader
//include ($TEMP_APP_PRELOADER_PATH);

//Conntent
echo <<<EOF
<div class="card card-outline card-primary">
    <div class="card-header text-center">
        <a href="{$_SERVER['SCRIPT_NAME']}" class="h1"><b>Arsenal</b>Quán</a>
    </div>
    <div class="card-body">
        <form action="{$_SERVER['SCRIPT_NAME']}?{$_SERVER['QUERY_STRING']}" method="POST" id="changePassword">
            <div class="input-group mb-3">
                <input type="password" name="password" class="form-control" placeholder="Mật khẩu mới" value="{$f_password}" autocomplete="off">
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-lock"></span>
                    </div>
                </div>
            </div>
            <div class="input-group mb-3">
                <input type="password" name="passwordConfirm" class="form-control" placeholder="Nhập lại mật khẩu mới" value="{$f_cpassword}" autocomplete="off">
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-lock"></span>
                    </div>
                </div>
            </div>
            <div class="row">
                <!-- /.col -->
                <div class="col-5">
                    <input type="hidden" name="registFlg" value="1">
                    <button type="submit" name="btn-cpass" id="btn-cpass" class="btn btn-primary btn-block">Xác nhận</button>
                </div>
                <!-- /.col -->
            </div>
        </form>
    </div>
    <!-- /.card-body -->
</div>
EOF;

//Meta JS
include($TEMP_APP_METAJS_PATH);
echo <<<EOF
    </div>
</body>
</html>
EOF;

/**
 * Validate data input
 * @param password
 * @param comfirm password
 * @return message
 */
function checkValidate($con, $func_id, $url_loginId, $url_token, $f_password, $f_cpassword)
{

    global $maxPassword, $minLoginId;

    if (!$user = getUser($con, $func_id, $url_loginId, $url_token)) {
        $_SESSION['message'] = 'Đường dẫn không tồn tại';
        $_SESSION['messageClass'] = 'alert-danger';
        $_SESSION['iconClass'] = 'fa fa-ban';

        header('location: login.php');
        exit();
    }

    $mes = [
        'chk_required' => [],
        'chk_format' => [],
        'chk_max_length' => [],
        'chk_match' => [],
    ];

        if (empty($f_password)) {
            $mes['chk_required'][] = 'Vui lòng nhập mật khẩu mới.';
        } elseif (!preg_match('/^(?=.*[0-9A-Za-z])/', $f_password) || !preg_match('/^(?=.*[@#\-_$%^&+=§!\?])/', $f_password)) {
            $mes['chk_format'][] = 'Mật khẩu mới không đúng định dạng, phải có ít nhất 1 chữ hoặc số và ký tự đặc biệt.';
        } elseif (mb_strlen($f_password) > $maxPassword || mb_strlen($f_password) < $minLoginId) {
            $mes['chk_max_length'][] = 'Mật khẩu phải lớn hơn ' . $minLoginId . ' ký tự và bé hơn ' . $maxPassword . ' ký tự.';
        }

        if (empty($f_cpassword)) {
            $mes['chk_required'][] = 'Vui lòng nhập xác nhận mật khẩu mới.';
        }

        if (empty($mes['chk_required']) && empty($mes['chk_format']) && empty($mes['chk_max_length'])) {
            if ($f_password !== $f_cpassword) {
                $mes = [
                    'chk_required' => [],
                    'chk_format' => [],
                    'chk_max_length' => [],
                    'chk_match' => ['Mật khẩu xác nhận không khớp'],
                    'chk_match' => ['Mật khẩu xác nhận không khớp'],
                ];
            }
        }

        $msg = array_merge(
            $mes['chk_required'],
            $mes['chk_format'],
            $mes['chk_max_length'],
            $mes['chk_match']
    );
    return $msg;
}

/**
 * Update password by email and token
 * @param con
 * @param email
 * @param token
 * @param password
 * @param comfirm password
 * @return boolean
 */
function updatePassword($con, $func_id, $uid, $password)
{
    $pg_param = array();
    $pg_param[] = $uid;
    $pg_param[] = null; // set token is empty in params
    $pg_param[] = password_hash(md5($password), PASSWORD_BCRYPT);

    $sql = "";
    $sql .= "UPDATE users                          ";
    $sql .= "   SET reset_link_token = $2,         ";
    $sql .= "       password = $3                  ";
    $sql .= " WHERE loginid = $1                   ";

    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query) {
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, TRUE));
    }
}

/**
 * Get user by loginId, email
 * @param $con
 * @param $func_id
 * @param $email
 * @param $token
 * @return array
 */
function getUser($con, $func_id, $loginid, $token)
{
    $userArray = array();
    $pg_param = array();
    $pg_param[] = $loginid;
    $pg_param[] = $token;

    $sql = "";
    $sql .= "SELECT * FROM users                      ";
    $sql .= "        WHERE loginid = $1               ";
    $sql .= "          AND reset_link_token = $2      ";
    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query) {
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, TRUE));
    } else {
        $userArray = pg_fetch_assoc($query);
    }

    return $userArray['reset_link_token'];
}

/**
 * @param $strInput
 * @param $strValue
 * @return mixed
 */
function getString($strInput)
{
    $array = [
        'uid' => [],
        'token' => [],
    ];

    if (isset($strInput)) {
        $str = explode('=', $strInput);
        $array['token'][] = $str[2];

        $str = explode('&', $str[1]);
        $array['uid'][] = $str[0];
    }

    $arr = array_merge(
        $array['uid'],
        $array['token']
    );

    return $arr;
}

?>

