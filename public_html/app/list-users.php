<?php

//Common setting
require_once('config.php');
require_once('lib.php');

//Initialization
$func_id = 'list_users';
$message = '';
$messageClass = '';
$iconClass = '';
$unBan = 0; //0: un ban; 1: ban

session_start();

//Get param
$param = getParam();

//Connect DB
$con = openDB();

$role       = $_SESSION['role'] ?? '';
$roleParam  = $param['role'] ?? '';
$fullName   = $param['fullname'] ?? '';
$loginId    = $param['loginId'] ?? '';
$dateForm   = $param['dateForm'] ?? '';
$dateTo     = $param['dateTo'] ?? '';
$uid        = $param['uid'] ?? '';
$mode       = $param['mode'] ?? 'update';

//Set data to combox role
$htmlRole = '';
$htmlRole = getAllRole($con, $func_id, $roleParam);

//Get data user
$htmlUser = '';
$htmlUser = getUserAndSearch($con, $func_id, $fullName, $loginId, $dateForm, $dateTo, $roleParam, $mode);

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

if ($param) {
    if (isset($param['registFlg']) && $param['registFlg'] == 1) {
        $mes = validateDataSearch($fullName, $loginId, $dateForm, $dateTo);
        
        $message = join('<br>', $mes);
        if (strlen($message)) {
            $messageClass = 'alert-danger';
            $iconClass = 'fas fa-ban';
        }
        
        if (empty($mes)){
            getUserAndSearch($con, $func_id, $fullName, $loginId, $dateForm, $dateTo, $roleParam, $mode);
        }
    }
    if ($mode == 'ban'){
        banUser($con, $func_id, $uid);
    }
    
    if ($mode == 'unban'){
        unBanUser($con, $func_id, $uid);
    }

    if ($mode == 'delete'){
        deleteUser($con, $func_id, $uid);
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
$scriptHTML = <<< EOF
<script>
    $(function() {
        //Button clear
        $('#btn_clear').on('click', function(e) {
            e.preventDefault();
            var message = "?????t m??n h??nh t??m ki???m v??? tr???ng th??i ban ?????u?";
            var that = $(this)[0];
            sweetConfirm(1, message, function(result) {
                if (result){
                    window.location.href = that.href;
                }
            });
        });
        
        //Button Ban
        $('.ban_user').on('click', function(e) {
            e.preventDefault();
            var message = "T??i kho???n n??y s??? b??? kho??. B???n c?? ch???c ch???n?";
            var form = $(this).closest("form");
            sweetConfirm(2, message, function(result) {
                $('.mode').val('ban');
                if (result){
                    form.submit();
                }
            });
        });
        
        //Button un ban
        $('.un_ban_user').on('click', function(e) {
            e.preventDefault();
            var message = "T??i kho???n n??y s??? ???????c kh??i ph???c. B???n c?? ch???c ch???n?";
            var form = $(this).closest("form");
            sweetConfirm(4, message, function(result) {
                $('.mode').val('unban');
                if (result){
                    form.submit();
                }
            });
        });
        
        //Button edit
        $('.edit_user').on('click', function(e) {
            e.preventDefault();
            var message = "??i ?????n m??n h??nh ch???nh s???a th??ng tin. B???n c?? ch???c ch???n?";
            var form = $(this).closest("form");
            sweetConfirm(3, message, function(result) {
                if (result){
                    form.submit();
                }
            });
        });
        
        //Button delete
        $('.deleteUser').on('click', function(e) {
            e.preventDefault();
            var message = "T??i kho???n n??y s??? b??? xo??. B???n ch???c ch????";
            var form = $(this).closest("form");
            sweetConfirm(1, message, function(result) {
                if (result){
                    $('.mode').val('delete');
                    form.submit();
                }
            });
        });
        
        //paginate
        $(document).ready(function() {
            $(".table").paginate({
                rows: 5,           // Set number of rows per page. Default: 5
                position: "top",   // Set position of pager. Default: "bottom"
                jqueryui: false,   // Allows using jQueryUI theme for pager buttons. Default: false
                showIfLess: false, // Don't show pager if table has only one page. Default: true
                numOfPages: 5
            });
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
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
EOF;

//Preloader
//include ($TEMP_APP_PRELOADER_PATH);

//Header
include($TEMP_APP_HEADER_PATH);

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
                                <i class="fas fa-search"></i>&nbspT??m ki???m t??i kho???n</h1>
                        </div>
                        <!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="dashboard.php">Trang ch???</a></li>
                                <li class="breadcrumb-item active">Danh s??ch t??i kho???n</li>
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
                            {$messageHtml}
                            <form action="{$_SERVER['SCRIPT_NAME']}" method="POST">
                                <div class="card card-info">
                                    <div class="card-header">
                                        <h3 class="card-title">T??m ki???m</h3>
                                    </div>
                                    <div class="card-body">
                                        <label>H??? t??n</label>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" placeholder="H??? t??n" name="fullname" value="{$fullName}">
                                        </div>

                                        <label>T??n ????ng nh???p</label>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" placeholder="T??n ????ng nh???p" name="loginId" value="{$loginId}">
                                        </div>

                                        <label>Vai tr??</label>
                                        <div class="input-group mb-3">
                                            <select class="custom-select" name="role">
                                                {$htmlRole}
                                              </select>
                                        </div>

                                        <label>Th???i gian</label>
                                        <div class="row">
                                            <div class="input-group mb-6 col-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                                </div>
                                                <input type="date" class="form-control" name="dateForm" value="{$dateForm}">
                                            </div>
                                            <span><b>~</b></span>
                                            <div class="input-group mb-6 col-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                                </div>
                                                <input type="date" class="form-control" name="dateTo" value="{$dateTo}">
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /.card-body -->
                                    <div class="card-footer">
                                        <input type="hidden" name="registFlg" value="1">
                                        <button type="submit" class="btn btn-primary float-right" style="background-color: #17a2b8;">
                                          <i class="fa fa-search"></i>
                                          &nbspT??m ki???m
                                        </button>
                                        <a href="" id="btn_clear">
                                            <button type="button" class="btn btn-default">
                                            <i class="fas fa-eraser fa-fw"></i>
                                            Xo??
                                          </button>
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- /.row -->
                    <div class="row">
                        <div class="card-body table-responsive">
                            <table class="table table-hover text-nowrap table-bordered" style="background-color: #FFFFFF;">
                                <thead style="background-color: #17A2B8;">
                                    <tr>
                                        <th style="width: 5%;" class="text-th text-center">STT</th>
                                        <th style="width: 20%;" class="text-th text-center">H??? t??n</th>
                                        <th style="width: 20%;" class="text-th text-center">T??n ????ng nh???p</th>
                                        <th style="width: 20%;" class="text-th text-center">Vai tr??</th>
                                        <th style="width: 20%;" class="text-th text-center">Tr???ng th??i</th>
                                        <th colspan="3" style="text-align: center; width: 20%;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {$htmlUser}
                                </tbody>
                            </table>
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
include($TEMP_APP_FOOTER_PATH);
//Meta JS
include($TEMP_APP_METAJS_PATH);
echo <<<EOF
    </div>
</body>
</html>
EOF;

/**
 * Get all role -> Combox
 * @param $con
 * @param $func_id
 * @param $roleParam
 * @return string
 */
function getAllRole($con, $func_id, $roleParam)
{
    $pg_param = array();
    $recCnt = 0;
    $roleArray = array();
    
    $sql = "";
    $sql .= "SELECT*FROM role";
    
    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query) {
        systemError('systemError(' . $func_id . ') SQL Error???', $sql . print_r($pg_param, true));
    } else {
        $recCnt = pg_num_rows($query);
    }
    
    $html = '<option value="0">Ch???n vai tr??</option>';
    if ($recCnt != 0) {
        while ($row = pg_fetch_assoc($query)) {
            $roleId = $row['id'];
            $roleName = $row['rolename'];
            
            $selected = '';
            if ($roleParam == $roleId) {
                $selected = 'selected="selected"';
            }
            
            $html .= <<<EOF
                <option value="{$roleId}" {$selected}>{$roleName}</option>
EOF;
        
        }
    }
    return $html;
}

/**
 * Validation data
 * @param $fullName
 * @param $loginId
 * @param $dateForm
 * @param $dateTo
 * @param $maxStr
 * @return array
 */
function validateDataSearch($fullName, $loginId, $dateForm, $dateTo)
{
    $maxStr = 200;

    $mes = [
        'chk_format'     => [],
        'chk_max_length' => []
    ];
    
    if (mb_strlen($fullName) > $maxStr) {
        $mes['chk_max_length'][] = 'H??? t??n kh??ng ???????c nh???p qu?? ' . $maxStr . ' k?? t???.';
    }
    
    if (mb_strlen($loginId) > $maxStr) {
        $mes['chk_max_length'][] = 'T??n ????ng nh???p kh??ng ???????c nh???p qu?? ' . $maxStr . ' k?? t???.';
    }

    if (!empty($dateForm) && !empty($dateTo) && (strtotime($dateForm) > strtotime($dateTo))){
        $mes['chk_format'][] = 'Kh??ng th??? t??m ki???m v???i th??ng tin ' .$dateForm. ' l???n h??n '.$dateTo.'';
    }
    
    $msg = array_merge(
        $mes['chk_format'],
        $mes['chk_max_length']
    );
    
    return $msg;
}

/**
 * Search function
 * @param $con
 * @param $func_id
 * @param $fullName
 * @param $loginId
 * @param $dateForm
 * @param $dateTo
 * @param $roleParam
 * @return string
 */
function getUserAndSearch($con, $func_id, $fullName, $loginId, $dateForm, $dateTo, $roleParam, $mode){
    $pg_param = array();
    $pg_sql = array();
    $recCnt = 0;
    $cnt = 0;
    
    if (!empty($fullName) && (mb_strlen($fullName) > 0)){
        $pg_param[] = '%'.$fullName.'%';
        $cnt++;
        $pg_sql[] = " AND fullname ILIKE $".$cnt."             ";
    }
    
    if (!empty($loginId) && (mb_strlen($loginId) > 0)){
        $pg_param[] = '%'.$loginId.'%';
        $cnt++;
        $pg_sql[] = " AND loginid ILIKE $".$cnt."              ";
    }
    
    if (!empty($dateForm) && (mb_strlen($dateForm) > 0)){
        $pg_param[] = $dateForm;
        $cnt++;
        $pg_sql[] = " AND createDate >= $".$cnt."              ";
    }
    
    if (!empty($dateTo) && (mb_strlen($dateTo) > 0)){
        $pg_param[] = $dateTo;
        $cnt++;
        $pg_sql[] = " AND createDate <= $".$cnt."              ";
    }
    
        if ((mb_strlen($roleParam) > 0) && $roleParam > 0){
        $pg_param[] = $roleParam;
        $cnt++;
        $pg_sql[] = " AND role = $".$cnt."                     ";
    }
    
    $wheresql = join(' ', $pg_sql);
    
    $sql = "";
    $sql .= "SELECT users.id                  ";
    $sql .= "     , users.fullname            ";
    $sql .= "     , users.loginid             ";
    $sql .= "     , users.status              ";
    $sql .= "     , users.role                ";
    $sql .= "     , role.rolename             ";
    $sql .= "  FROM users                     ";
    $sql .= "  INNER JOIN role                ";
    $sql .= "    ON users.role = role.id      ";
    $sql .= " WHERE deldate IS NULL           ";
    $sql .= $wheresql;
    $sql .= " ORDER BY role ASC               ";

    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query){
        systemError('systemError(' . $func_id . ') SQL Error???', $sql . print_r($pg_param, true));
    } else {
        $recCnt = pg_num_rows($query);
    }
    
    $html = '';
    if ($recCnt != 0){
        while ($row = pg_fetch_assoc($query)){
            $cnt++;
            if ($row['status'] == 't'){
                $strStatus = "??ang ho???t ?????ng";
                $iconBg = "btn-danger";
                $iconStatus = "fa-lock";
                $btnStatus = "ban_user";
            } else {
                $strStatus = "V?? hi???u ho??";
                $iconBg = "btn-success";
                $iconStatus = "fa-lock-open";
                $btnStatus = "un_ban_user";
            }

            $htmlButtonBan = '';
            if ($_SESSION['loginId'] == $row['loginid']){
                $htmlButtonBan .= <<< EOF
                    <button class="btn {$iconBg} btn-sm" disabled><i class="fas fa-lock"></i></button>

EOF;
            } else {
                $htmlButtonBan .= <<< EOF
                    <form action="{$_SERVER['SCRIPT_NAME']}" method="POST">
                        <input type="hidden" name="uid" value="{$row['id']}">
                        <input type="hidden" name="mode" class="mode" value="{$mode}">
                        <button class="btn {$iconBg} btn-sm {$btnStatus}"><i class="fas {$iconStatus}"></i></button>
                    </form>
EOF;
            }

            $htmlButtonDelete = '';
            if ($_SESSION['loginId'] == $row['loginid']){
                $htmlButtonDelete .= <<< EOF
                    <button class="btn btn-danger btn-sm" disabled><i class="fas fa-trash"></i></button>
EOF;
            } else {
                $htmlButtonDelete .= <<< EOF
                    <form action="{$_SERVER['SCRIPT_NAME']}" method="POST">
                        <input type="hidden" name="uid" value="{$row['id']}">
                        <input type="hidden" name="mode" class="mode" value="{$mode}">
                        <button class="btn btn-danger btn-sm deleteUser"><i class="fas fa-trash"></i></button>
                    </form>
EOF;
            }
            
            $html .= <<< EOF
                <tr>
                    <td style="text-align: center; width: 5%;">{$cnt}</td>
                    <td style="width: 20%;">{$row['fullname']}</td>
                    <td style="width: 20%;">{$row['loginid']}</td>
                    <td style="text-align: center; width: 20%;">{$row['rolename']}</td>
                    <td style="text-align: center; width: 20%;">{$strStatus}</td>
                    <td style="text-align: center; width: 5%;">
                        <form action="detail-user.php" method="POST">
                            <input type="hidden" name="mode" class="mode" value="{$mode}">
                            <input type="hidden" name="uid" value="{$row['id']}">
                            <button class="btn btn-primary btn-sm edit_user"><i class="fas fa-edit"></i></button>
                        </form>
                    </td>
                    <td style="text-align: center; width: 5%;">
                        {$htmlButtonBan}
                    </td>
                    <td style="text-align: center; width: 5%;">
                        {$htmlButtonDelete}
                    </td>
                </tr>
EOF;

        }
    } else {
        $html .= <<< EOF
            <tr>
                <td colspan = 7>
                    <h3 class="card-title">
                        <i class="fas fa-bullseye fa-fw" style="color: red"></i>
                        Kh??ng c?? d??? li???u
                    </h3>
                </td>
            </tr>
EOF;

    }
    return $html;
}

/**
 * Block user function
 * @param $con
 * @param $func_id
 * @param $uid
 */
function banUser($con, $func_id, $uid){
    $pg_param = array();
    $pg_param[] = $uid;
    $recCnt = 0;
    
    $sql = "";
    $sql .= "UPDATE users                             ";
    $sql .= "   SET status = false                    ";
    $sql .= " WHERE deldate IS NULL                   ";
    $sql .= "   AND users.id = $1                     ";
    
    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query){
        systemError('systemError(' . $func_id . ') SQL Error???', $sql . print_r($pg_param, true));
    }
    header('location: list-users.php');
}

/**
 * Un ban user function
 * @param $con
 * @param $func_id
 * @param $uid
 */
function unBanUser($con, $func_id, $uid){
    $pg_param = array();
    $pg_param[] = $uid;
    $recCnt = 0;
    
    $sql = "";
    $sql .= "UPDATE users                             ";
    $sql .= "   SET status = true                     ";
    $sql .= " WHERE deldate IS NULL                   ";
    $sql .= "   AND users.id = $1                     ";
    
    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query){
        systemError('systemError(' . $func_id . ') SQL Error???', $sql . print_r($pg_param, true));
    }
    header('location: list-users.php');
}

/**
 * Delete user function
 * @param $con
 * @param $func_id
 * @param $uid
 */
function deleteUser($con, $func_id, $uid){
    $pg_param = array();
    $pg_param[] = $_SESSION['loginId'];
    $pg_param[] = $uid;
    $recCnt = 0;

    $sql = "";
    $sql .= "UPDATE users                                     ";
    $sql .= "   SET deldate = '".date('Y/m/d')."'      ";
    $sql .= "     , updatedate = '".date('Y/m/d')."'   ";
    $sql .= "     , updateby = $1                             ";
    $sql .= " WHERE id = $2                                   ";

    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query){
        systemError('systemError(' . $func_id . ') SQL Error???', $sql . print_r($pg_param, true));
    }

    $_SESSION['message'] = 'T??i kho???n ???? ???????c xo?? th??nh c??ng';
    $_SESSION['messageClass'] = 'alert-success';
    $_SESSION['iconClass'] = 'fas fa-check';

    header('location: list-users.php');
    exit();
}
?>

