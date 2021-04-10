<?php

//Common setting
require_once ('config.php');
require_once ('lib.php');

//Initialization
$func_id = 'login';
$message = '';
$messageClass = '';
$maxLoginId = 254;
$minStr = 6;
$maxPassword = 16;

// Set Variable Cookie
$cookie_name = 'siteAuth';
$cookie_time = (3600 * 24 ); // 1 day

session_start();

//Connect DB
$con = openDB();

//Get param
$param = getParam();

$loginId  = $param['loginId'] ?? '';
$password = $param['password'] ?? '';
$check_rm = $param['remember'] ?? '';

if(isset($cookie_name)){
    if(isset($_COOKIE[$cookie_name])){
        header('location: dashboard.php');
        exit();
    }
}

//Check login inf
$checkLogin = checkLoginId($con, $param, $func_id, $loginId, $password);
//Validation data
$validate = validateData($param, $loginId, $password, $minStr, $maxLoginId, $maxPassword, $checkLogin);

if ($param){
    if (isset($param['registFlg']) && $param['registFlg'] == 1){
        $mes = $validate;
    
        $message = join('<br>', $mes);
        if (strlen($message)) {
            $messageClass = 'alert-danger';
            $iconClass = 'fas fa-ban';
        }
    }
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
<body class="hold-transition login-page" id="{$func_id}">
    <div class="login-box">
    {$messageHtml}
EOF;

//Preloader
//include ($TEMP_APP_PRELOADER_PATH);

//Conntent
echo <<<EOF
<div class="card card-outline card-primary">
            <div class="card-header text-center">
                <a href="login.php" class="h1"><b>Arsenal</b>Quán</a>
            </div>
            <div class="card-body">
                <form action="{$_SERVER['SCRIPT_NAME']}" method="POST">
                    <div class="input-group mb-3">
                        <input type="text" name="loginId" class="form-control" placeholder="Tên đăng nhập" value="{$loginId}" autocomplete="off">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" name="password" class="form-control" placeholder="Mật khẩu" value="{$password}" autocomplete="off">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-7">
                            <div class="icheck-primary">
                                <input type="checkbox" id="remember" name="remember">
                                <label for="remember">Lưu đăng nhập</label>
                            </div>
                        </div>
                        <!-- /.col -->
                        <div class="col-5">
                            <input type="hidden" name="registFlg" value="1">
                            <button type="submit" name="login" class="btn btn-primary btn-block">Đăng nhập</button>
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

/**
 * Check loginId exist
 * @param $con
 * @param $param
 * @param $func_id
 * @return bool
 */
function checkLoginId($con, $param, $func_id, $loginId, $password){
    $recCnt = 0;
    $pg_param = array();
    $userInf = array();
    $pg_param[] = $loginId;
    $pg_param[] = $password;
    
    $sql = "";
    $sql .="SELECT loginid              ";
    $sql .="     , password             ";
    $sql .="     , role                 ";
    $sql .="  FROM users                ";
    $sql .=" WHERE loginid = $1         ";
    $sql .="   AND password  = $2       ";
    $sql .="   AND deldate IS NULL      ";
    
    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query){
        systemError('systemError('.$func_id.') SQL Error：',$sql.print_r($pg_param, TRUE));
    } else {
        $recCnt = pg_num_rows($query);
    }
    
    if ($recCnt != 0){
        $userInf = pg_fetch_assoc($query);
        $_SESSION['loginId'] = $userInf['loginid'];
        $_SESSION['role'] = $userInf['role'];
    }
    return $userInf;
}

/**
 * Validation data
 * @param $param
 * @param $loginId
 * @param $password
 * @param $minLoginId
 * @param $maxLoginId
 * @param $maxPassword
 * @param $checkLogin
 * @return array
 */
function validateData($param, $loginId, $password, $minLoginId, $maxLoginId, $maxPassword, $checkLogin){
    
    // Call local variable
    global $cookie_name, $cookie_time, $check_rm;

    $mes = [
        'chk_required'   => [],
        'chk_format'     => [],
        'chk_max_length' => []
    ];
    
    if (empty($loginId)){
        $mes['chk_required'][] = 'Vui lòng nhập tên đăng nhập.';
    } else {
        if (!preg_match('/^[0-9A-Za-z]/', $loginId) || preg_match('/^(?=.*[@#\-_$%^&+=§!\?])/', $loginId)){
            $mes['chk_format'][] = 'Tên đăng nhập không được chứa kí tự đặc biệt.';
        }
        if (mb_strlen($loginId) > $maxLoginId || mb_strlen($loginId) < $minLoginId){
            $mes['chk_max_length'][] = 'Tên đăng nhập phải hơn '.$minLoginId.' ký tự và bé hơn '.$maxLoginId.' ký tự.';
        }
    }
    
    if (empty($password)){
        $mes['chk_required'][] = 'Vui lòng nhập mật khẩu.';
    } else {
        if (!preg_match('/^(?=.*[0-9A-Za-z])/', $password) || !preg_match('/^(?=.*[@#\-_$%^&+=§!\?])/', $password)){
            $mes['chk_format'][] = 'Mật khẩu không đúng định dạng, phải có ít nhất 1 chữ hoặc số và ký tự đặc biệt.';
        }
        if (mb_strlen($password) > $maxPassword || mb_strlen($password) < $minLoginId){
            $mes['chk_max_length'][] = 'Mật khẩu phải lớn hơn '.$minLoginId.' ký tự và bé hơn '.$maxPassword.' ký tự.';
        }
    }
    
    $msg = array_merge(
        $mes['chk_required'],
        $mes['chk_format'],
        $mes['chk_max_length']
    );
    
    if (empty($msg)){
        if (!empty($checkLogin)){

            // SET COOKIE
            if (!empty($check_rm)) {
                $_SESSION['username']=$loginId;
                setcookie ($cookie_name, 'usr='.$loginId.'&hash=vehhd6vejs8au,', time() + $cookie_time, '/');
            }

            header('location: dashboard.php');
            exit();
        } else {
            $msg[] = 'Tên đăng nhập hoặc mật khẩu không đúng';
        }
    }
    
    return $msg;
}
/**
 * Get Username in Cookie
 * @param $str
 * @param $cookie_name
 * @return string
 */
function getUsernameInCookie($cookie_name){
    $str = '';
    if(isset($cookie_name)){
        $str = explode('usr=', $_COOKIE[$cookie_name]);
        $str = explode('&', $str[1]);
    }
    return $str[0];
}
?>