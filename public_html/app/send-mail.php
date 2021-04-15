<?php

use PHPMailer\PHPMailer\PHPMailer;

//Common setting
require_once('config.php');
require_once('lib.php');
require_once('../app/plugins/PHPMailer/PHPMailer.php');
require_once('../app/plugins/PHPMailer/SMTP.php');
require_once('../app/plugins/PHPMailer/Exception.php');
require_once('../app/plugins/PHPMailer/FormatBodyMail.php');

//Initialization
$func_id = 'send-password';
$message = '';
$messageHtml = '';
$minInput = 6;
$maxInput = 254;
$maxDatetimeToken = 60 * 15; // 15 minutes
$messageFlg = 0; //1: visible; 0: invisible

session_start();

//Connect DB
$con = openDB();

//Get param
$param = getParam();

$f_loginid = $param['username'] ?? '';
$f_email = $param['email'] ?? '';
$submit = $param['btn_send'] ?? '';

$validate = validateForm($con, $func_id, $f_loginid, $f_email);

// Check btn_submit
if (!empty($_POST)) {
    // Check message error
    if (empty($validate)) {
        $user = getUser($con, $func_id, $f_loginid, $f_email);
        createTokenAndSendMail($con, $user, $f_email, $func_id);
    }

}

if ($param) {
    if (isset($param['registFlg']) && $param['registFlg'] == 1) {
        $mes = $validate;

        $message = join('<br>', $mes);
        if (strlen($message)) {
            $messageClass = 'alert-danger';
            $iconClass = 'fas fa-ban';
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
$scriptHTML = <<< EOF
  <script >
    $(function() {
        $('#btn_send').click(function() {
            $("#loader-gif").show();
            $("#span_send").hide();
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
                <a href="send-mail.php" class="h1"><b>Arsenal</b>Quán</a>
            </div>
            <div class="card-body">
                <form action="{$_SERVER['SCRIPT_NAME']}" method="POST">
                    <div class="input-group mb-3">
                        <input type="text" name="username" class="form-control" placeholder="Tên đăng nhập" value="" autocomplete="off">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="text" name="email" class="form-control" placeholder="Email đã đăng ký" value="" autocomplete="off">
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
                            <button type="submit" name="btn_send" id="btn_send" class="btn btn-primary btn-block">
                                <span id="span_send">Gửi email</span>
                                <img id="loader-gif" src="dist/img/btn-loading.gif" alt="loading" height="25" style="display:none">
                            </button>
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
 * Check validate loginId, email input
 * @param $user
 * @param $f_email
 * @return string
 */
function validateForm($con, $func_id, $f_loginid, $f_email)
{
    global $minInput, $maxInput;

    $msg = '';
    $mes = [
        'chk_required' => [],
        'chk_format' => [],
        'chk_max_length' => [],
        'chk_match' => [],
    ];

    if (empty($f_loginid)) {
        $mes['chk_required'][] = 'Vui lòng nhập tên đăng nhập';
    } else {
        if (!preg_match('/^[0-9A-Za-z]/', $f_loginid) || preg_match('/^(?=.*[@#\-_$%^&+=§!\?])/', $f_loginid)) {
            $mes['chk_format'][] = 'Tên đăng nhập không được chứa kí tự đặc biệt';
        } else if (mb_strlen($f_loginid) < $minInput || mb_strlen($f_loginid) > $maxInput) {
            $mes['chk_max_length'][] = "Tên đăng nhập nhập vào phải lớn hơn $minInput ký tự và bé hơn $maxInput ký tự";
        }
    }

    if (empty($f_email)) {
        $mes['chk_required'][] = 'Vui lòng nhập địa chỉ email';
    } else {
        if (!filter_var($f_email, FILTER_VALIDATE_EMAIL)) {
            $mes['chk_format'][] = 'Email không đúng định dạng. Ví dụ: abc@gmail.com';
        } else if (mb_strlen($f_email) < $minInput || mb_strlen($f_email) > $maxInput) {
            $mes['chk_max_length'][] = "Email đã đăng ký nhập vào phải lớn hơn $minInput ký tự và bé hơn $maxInput ký tự";
        }
    }

    if (empty($mes['chk_required']) && empty($mes['chk_format']) && empty($mes['chk_max_length'])) {
        // Check loginId and mail is exist
        if (!$user = getUser($con, $func_id, $f_loginid, $f_email)) {
            $mes = [
                'chk_required' => [],
                'chk_format' => [],
                'chk_max_length' => [],
                'chk_match' => ['Tên đăng nhập và địa chỉ Email không đúng hoặc không tồn tại'],
            ];
        }
    }

    $msg = array_merge(
        $mes['chk_required'],
        $mes['chk_format'],
        $mes['chk_max_length'],
        $mes['chk_match'],
    );

    return $msg;
}

/**
 * Get user by loginId, email
 * @param $con
 * @param $func_id
 * @param $loginid
 * @param $email
 * @return array
 */
function getUser($con, $func_id, $loginid, $email)
{
    $user = array();
    $pg_param = array();
    $pg_param[0] = $loginid;
    $pg_param[1] = $email;

    // Check email and token exist
    $sql = "";
    $sql .= "SELECT * FROM users    ";
    $sql .= "WHERE loginid = $1     ";
    $sql .= "AND email  = $2        ";
    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query) {
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, TRUE));
    } else {
        $user = pg_fetch_assoc($query);
    }

    return $user;
}

/**
 * Create Token, Sent Mail (bottom func)
 * @param $con
 * @param $user
 * @param $f_email
 * @param $func_id
 * @throws \PHPMailer\PHPMailer\Exception
 */
function createTokenAndSendMail($con, $user, $f_email, $func_id)
{
    $loginId = $user['loginid'];
    // Set Token to email
    $token = md5($loginId) . rand(10, 9999);
    // Set link in mail
    $link = $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/' .
        'change-password.php' . "?uid=" . $loginId . "&token=" . $token;

    $fullname = $user['fullname'];
    $pg_param = array();
    $pg_param[0] = $token; // token
    $pg_param[1] = getDateTime(); // datetime now
    $pg_param[2] = $f_email; // form email

    // SQL Update token
    $sql = "";
    $sql .= "UPDATE users                   ";
    $sql .= "SET reset_link_token = $1,     ";
    $sql .= "date_token = $2                ";
    $sql .= "WHERE email = $3               ";
    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query) {
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, TRUE));
    } else {
        $row = pg_num_rows($query);
    }

    /* --------------------
        REQUEST SEND MAIL
       -------------------- */
    sendMail($f_email, $fullname, $link);
}

/**
 * Send mail to user
 * @param $f_email
 * @param $fullName
 * @param $link
 * @throws \PHPMailer\PHPMailer\Exception
 */
function sendMail($f_email, $fullName, $link)
{
    global $messageFlg;
    $mail_user = 'hotro.arsenalquan@gmail.com';
    $mail_psw = 'arsenalquan123456';

    $mail = new PHPMailer();

    $mail->CharSet = "utf-8";
    $mail->IsSMTP();  // enable SMTP authentication
    $mail->Host = "ssl://smtp.gmail.com";  // sets GMAIL as the SMTP server
    $mail->SMTPAuth = true;
    $mail->Username = $mail_user; // GMAIL username
    $mail->Password = $mail_psw;  // GMAIL password
    $mail->Port = "465";  // set the SMTP port for the GMAIL server
    $mail->SMTPSecure = "ssl";

    $mail->IsHTML(true);
    $mail->From = 'arsenalquan@gmail.com'; // Mail me
    $mail->FromName = 'Arsenal Quán'; // Name me
    $mail->addAddress($f_email); // Mail send
    $mail->Subject = 'Chúng tôi gửi bạn đường link Đổi mật khẩu'; // Title

    $mail->Body = formatBodyMail($fullName, $link);

    // Is mail send?
    if ($mail->Send()) {
        header('location: notification-send-mail.php');
        exit();
    } else {
        exit();
    }
}

?>

