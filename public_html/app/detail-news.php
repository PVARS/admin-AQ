<?php

//Common setting
require_once('config.php');
require_once('lib.php');

//Initialization
$func_id       = 'detail_news';
$userLogin     = array();
$imageAtt      = '';
$message       = '';
$titlePage     = 'Thêm bài viết';
$titleButton   = 'Lưu';
$htmlDeleteNew = '';
$urlRedirect   = 'list-news.php';

session_start();

//Get param
$param = getParam();
$mode  = $param['mode'] ?? 'new';
$nid   = $param['nid'] ?? '';
$role  = $_SESSION['role'] ?? '';

$f_urlImamge   = $param['urlImage'] ?? '';
$valuecategory = $param['idCategory'] ?? '';

//Connect DB
$con = openDB();

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

if (!isset($_SESSION['role'])) {
    header('location: error404.php');
    exit();
}

if (isset($param['dispFrom'])){
    if ($param['dispFrom'] == 'list-categories'){
        $urlRedirect = 'list-categories.php';
    }

    if ($param['dispFrom'] == 'dashboard'){
        $urlRedirect = 'dashboard.php';
    }
}

// Get my web app's Firebase configuration
$htmlFirebaseConfig = getFirebaseConfig($con, $func_id);

// Get id and fullname user
$userLogin = getUserByLoginId($con, $func_id, $_SESSION['loginId']);

if ($param) {
    if (isset($param['registFlg']) && $param['registFlg'] == 1) {
        /*Delete New*/
        if ($mode == 'delete'){
            deleteNews($con, $func_id, $nid, $_SESSION['loginId']);
        }

        if (empty($mes)) {
            $mes = validationDataNews($param);
        }

        $message = join('<br>', $mes);
        if (strlen($message)) {
            $messageClass = 'alert-danger';
            $iconClass = 'fas fa-ban';
        }

        if (empty($mes)) {
            if (isset($nid) && (mb_strlen($nid) > 0)) { /*Update News*/
                updateNews($con, $func_id, $param, $userLogin['loginid']);
            } else {/*Insert News*/
                insertNews($con, $func_id, $param, $userLogin['loginid']);
            }
        }
    }
}

//get data edits
if (isset($nid) && (mb_strlen($nid) > 0)) {
    $titlePage      = "Chỉnh sửa bài viết";
    $titleButton    = "Cập nhật";

    $edit_new       = get_newsedit($con, $func_id, $nid);
    $valuetitle     = $edit_new['title'];
    $valuecategory  = $edit_new['category'];
    $valueshortdes  = $edit_new['shortdescription'];
    $valueusers     = $edit_new['fullname'];
    $valuethumbnail = $edit_new['thumbnail'];
    $f_urlImamge    = $edit_new['thumbnail']; // set url image
    $valuecontent   = $edit_new['content'];
    $valuethumbnail = getNameFileToLink($valuethumbnail);

    $htmlDeleteNew  = <<<EOF
    <form action="{$_SERVER['SCRIPT_NAME']}" method="POST">
        <input type="hidden" name="nid" value="{$nid}">
        <input type="hidden" name="mode" value="delete">
        <input type="hidden" name="registFlg" value="1">
        <a href="javascript:void(0)" class="btn btn-danger btn_delete" title="Xóa bài">
            <i class="fas fa-trash"></i> Xóa
        </a>
    </form>
EOF;

} else {
    $valuecategory  = $param['category'] ?? '';
    $valuetitle     = $param['title'] ?? '';
    $valueRole      = $param['category'] ?? '';
    $valueshortdes  = $param['shortdescription'] ?? '';
    $valueusers     = $userLogin['fullname'];
    $valuecontent   = $param['content'] ?? '';
    $valuethumbnail = "Chọn file";
}

// Set param url image
if (isset($f_urlImamge) && (mb_strlen($f_urlImamge) > 0)) {
    $imageAtt = 'src="' . $f_urlImamge . '" class="mb-2" height="100"';
}

//get combobox
$showcategoryhtml = show_category($con, $func_id, $valuecategory);

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
$titleHTML  = '';
$cssHTML    = '';
$scriptHTML = <<<EOF
<script>
    // Your web app's Firebase configuration
    {$htmlFirebaseConfig}
    
    // Initialize Firebase
    firebase.initializeApp(firebaseConfig);
    firebase.analytics();
        
    /*Check file user input*/
    function validateFileType(){
        let file = document.getElementById("thumbnail");
        var fileName = file.value,
            idxDot = fileName.lastIndexOf(".") + 1,
            extFile = fileName.substr(idxDot, fileName.length).toLowerCase();

        if (extFile=="jpg" || extFile=="jpeg" || extFile=="png"){ /*return true*/          
            // Show image seleced
            var selectedFile = event.target.files[0];
            var reader = new FileReader();
            var imgtag = document.getElementById("image");
            var image_db = document.getElementById("image_db");
            imgtag.style.display = "block";
            image_db.style.display = "none";
            imgtag.title = selectedFile.name;
            reader.onload = function(event) {
                imgtag.src = event.target.result;
            };
            reader.readAsDataURL(selectedFile);       
            
            // Set namefile
            let file = $('#thumbnail')[0].files[0].name;
            $('#thumbnail').next('.custom-file-label').html(file);
                        
        } else { /*return false*/
            Swal.fire({
                position: 'top',
                icon: 'warning',
                title: 'Chỉ cho phép các tệp jpg/jpeg và png!',
                showConfirmButton: false,
                timer: 2000
            });
            file.value = "";  // Reset the input so no files are uploaded
            document.getElementById("urlImage").value = '';
            document.getElementById("image").style.display = "none";
            document.getElementById("image").src = '';
            $('#thumbnail').next('.custom-file-label').html('Chọn file');
        }
    }
    
    /*Submit form and upload file image*/
    function uploadImage() {
        var file_image = document.getElementById("thumbnail");
        var url_image = document.getElementById("urlImage");
        var category = document.getElementById("category");
        var title = document.getElementById("title");
        
        /* SET Message */
        if ('{$mode}' == 'update'){
            var message = "Bạn có muốn chỉnh sửa bài viết này?";
            var numberMessage = 3;
        }else{
            var message = "Bạn có muốn thêm mới bài viết này?";
            var numberMessage = 5;
        }

        /*Check file exist*/
        if (category.value == "" || title.value == ""){ 
            document.getElementById("ismForm").submit(); /*Submit form*/
        } 
        /*Check validate data*/
        else if (url_image.value == "" && file_image.files.length == 0){
            var that = $(this)[0];
            sweetConfirm(numberMessage, message, function(result) {
                if (result){
                    document.getElementById("ismForm").submit(); /*Submit form*/
                }
            });
        } 
        /*Check update image when no select*/
        else if (file_image.files.length == 0){ 
            var that = $(this)[0];
            sweetConfirm(numberMessage, message, function(result) {
                if (result){
                    document.getElementById("ismForm").submit(); /*Submit form*/
                }
            });
        } 
        /* return true */
        else { 
            var that = $(this)[0];
            sweetConfirm(numberMessage, message, function(result) {
                if (result){
                   /* Disable button save */
                    document.getElementById("submit_saveOrUpdate").style.display = "none";
                    document.getElementById("submit_disable").style.display = "block";
                    
                    /* Upload file to Firebase */
                    const ref = firebase.storage().ref();
                    const file = document.querySelector("#thumbnail").files[0];
                    const name = file.name;
                    const metadata = {
                        contentType: file.type
                    };
                    const task = ref.child(name).put(file, metadata);
            
                    task
                    .then(snapshot => snapshot.ref.getDownloadURL())
                    .then(url =>{
                        document.getElementById('image').style.display = "block";
                        document.getElementById("urlImage").value = url;
                        const image = document.querySelector('#image');
                        image.src = url;
                        /*Submit form*/
                        document.getElementById("ismForm").submit(); 
                    });
                }
            });
        }
    }
        
    // Button delete
    $('.btn_delete').on('click', function(e) {
        e.preventDefault();
        var message = "Bạn đang yêu cầu xóa bản tin này. Bạn có chắc chắn?";
        var form = $(this).closest("form");
        sweetConfirm(1, message, function(result) {
            if (result){
                form.submit();
            }
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
                        <i class="fas fa-plus-square"></i>&nbsp{$titlePage}</h1>
                </div>
                <!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.html">Trang chủ</a></li>
                        <li class="breadcrumb-item active">Danh sách bài viết</li>
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
                <div class="col-12">
                    <a href="{$urlRedirect}" class="btn btn-primary float-right mr-3" style="background-color: #17a2b8;" title="Back">
                        <i class="fas fa-backward"></i>
                        &nbspTrở lại
                    </a>
                </div>
            </div>
            <div class="row">
                <div class="card-body">
                    {$messageHtml}
                    <form action="{$_SERVER['SCRIPT_NAME']}" id="ismForm" method="POST">
                        <input type="hidden" name="nid" value="{$nid}">
                        <div class="card card-info">
                            <div class="card-header">
                                <h3 class="card-title">{$titlePage}</h3>
                            </div>
                            <div class="card-body">
                                <label>Danh mục</label>
                                <div class="input-group mb-3">
                                    {$showcategoryhtml}
                                </div>

                                <label>Tiêu đề</label>
                                <div class="input-group mb-3">
                                    <input type="text" name="title" class="form-control" id="title" placeholder="Tiêu đề" value="{$valuetitle}">
                                </div>

                                <label>Mô tả ngắn</label>
                                <div class="input-group mb-3">
                                    <input type="text" name="shortdescription" class="form-control" placeholder="Mô tả ngắn" value="{$valueshortdes}">
                                </div>

                                <label>Người đăng</label>
                                <div class="input-group mb-3">
                                    <input type="text" name="fullname" class="form-control" value="{$valueusers}" readonly>
                                </div>

                                <label>Thumbnail</label>
                                <input type="hidden" class="form-control" name="urlImage" id="urlImage" value="{$f_urlImamge}" readonly>
                                <div class="input-group">
                                    <img id="image" class="mb-2" height="100" style="display: none;"/>
                                </div>
                                <div class="input-group">
                                    <img id="image_db" {$imageAtt}/>
                                </div>
                                <div class="input-group mb-3">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="thumbnail" value="{$valuethumbnail}" accept=".jpg,.jpeg,.png" onchange="validateFileType()">
                                        <label class="custom-file-label" for="customFile">{$valuethumbnail}</label>
                                    </div>
                                </div>

                                <label>Nội dung</label>
                                <textarea id="summernote" name="content">{$valuecontent}</textarea>
                            </div>
                            <!-- /.card-body -->
                            <div class="card-footer">
                                <form action="{$_SERVER['SCRIPT_NAME']}" method="POST">
                                    <input type="hidden" name="mode" value="{$mode}">
                                    <input type="hidden" name="registFlg" value="1">
                                    <span class="btn btn-primary float-right" onclick="uploadImage()" name="submit_saveOrUpdate" id="submit_saveOrUpdate" style="background-color: #17a2b8;">
                                        <i class="fas fa-save"></i>&nbsp{$titleButton}
                                    </span>
                                    <button class="btn btn-primary float-right" id="submit_disable" disabled style="display: none;">
                                        <i class="fas fa-save"></i>&nbspLưu
                                    </button>
                                </form>
                                {$htmlDeleteNew}
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
include($TEMP_APP_FOOTER_PATH);
//Meta JS
include($TEMP_APP_METAJS_PATH);
echo <<<EOF
    </div>
</body>
</html>
EOF;

/**
 * function get data edit with nid;
 * @param $con
 * @param $func_id
 * @param $nid
 * @return array
 */
function get_newsedit($con, $func_id, $nid)
{

    $editArray  = array();
    $pg_param   = array();
    $pg_param[] = $nid;
    $recCnt     = 0;
    $sql = "";
    $sql .= "SELECT news.title                               ";
    $sql .= " ,news.shortdescription                         ";
    $sql .= " ,users.fullname                                ";
    $sql .= " ,news.thumbnail                                ";
    $sql .= " ,news.category                                 ";
    $sql .= " ,news.content                                  ";
    $sql .= " FROM news                                      ";
    $sql .= " INNER JOIN users                               ";
    $sql .= " ON news.createby = users.loginid               ";
//    $sql .= " INNER JOIN category                            ";
//    $sql .= " ON category.id = news.category                 ";
    $sql .= " WHERE news.id = $1                             ";

    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query) {
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    } else {
        $recCnt = pg_num_rows($query);
    }

    if ($recCnt != 0) {
        $editArray = pg_fetch_assoc($query);
    }else{
        header('location: dashboard.php');
        exit();
    }
    return $editArray;
}

/**
 * get category in form edit
 * Function works with combobox
 * @param $con
 * @param $func_id
 * @param $valuecategory
 * @return string
 */
function show_category($con, $func_id, $valuecategory)
{
    $pg_param = array();
    $recCnt   = 0;

    $sql  = '';
    $sql .= "SELECT id, category FROM category ";
    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query) {
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    } else {
        $recCnt = pg_num_rows($query);
    }

    $html = '<select class="custom-select" id="category" name="category">';
    $html .= '<option value="0">Chọn danh mục</option>';
    if ($recCnt != 0) {
        while ($row = pg_fetch_assoc($query)) {
            $selected = '';
            if ($valuecategory == $row['id']) {
                $selected = 'selected="selected"';
            }
            $html .= '<option value="' . $row['id'] . '" ' . $selected . '>' . $row['category'] . '</option>';
        }
    }
    $html .= '</select>';
    return $html;

}

/**
 * Validation data
 * @param $param
 * @return array
 */
function validationDataNews($param){
    $maxStr = 1000;
    $minStr = 1;

    $mes = [
        'chk_required'   => [],
        'chk_max_length' => []
    ];

    if (isset($param['category']) && $param['category'] == 0){
        $mes['chk_required'][] = 'Vui lòng chọn danh mục cho bài viết.';
    }

    if (empty($param['title'])){
        $mes['chk_required'][] = 'Vui lòng nhập tiêu đề bài viết.';
    } elseif (mb_strlen($param['title']) > $maxStr || mb_strlen($param['title']) < $minStr){
        $mes['chk_max_length'][] = 'Vui lòng nhập tiêu đề bài viết lớn hơn '.$minStr.' ký tự và không vượt quá '.$maxStr.' ký tự.';
    }

    $msg = array_merge(
        $mes['chk_required'],
        $mes['chk_max_length']
    );

    return $msg;
}

/**
 * Get id, fullname of user by loginid
 * @param $con
 * @param $func_id
 * @param $uid
 * @return array
 */
function getUserByLoginId($con, $func_id, $uid)
{
    $user       = array();
    $pg_param   = array();
    $pg_param[] = $uid;

    $sql  = "";
    $sql .= "SELECT loginid, fullname     ";
    $sql .= "  FROM users                 ";
    $sql .= " WHERE loginid = $1          ";

    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query) {
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    } else {
        $recCnt = pg_num_rows($query);
    }
    if ($recCnt != 0) {
        $user = pg_fetch_assoc($query);
    }

    return $user;
}

/**
 * Insert News
 * @param $con
 * @param $func_id
 * @param $param
 * @param $idUser
 */
function insertNews($con, $func_id, $param, $idUser)
{
    $pg_param   = array();
    $pg_param[] = $param['category'];
    $pg_param[] = $param['title'];
    $pg_param[] = $param['shortdescription'];
    $pg_param[] = $param['urlImage'];
    $pg_param[] = $param['content'];
    $pg_param[] = getDatetimeNow();
    $pg_param[] = $idUser;
    $pg_param[] = 0;
    $pg_param[] = convert_urlkey($param['title']);

    $sql  = "";
    $sql .= "INSERT INTO news(              ";
    $sql .= "            category           ";
    $sql .= "          , title              ";
    $sql .= "          , shortdescription   ";
    $sql .= "          , thumbnail          ";
    $sql .= "          , content            ";
    $sql .= "          , createdate         ";
    $sql .= "          , createby           ";
    $sql .= "          , view               ";
    $sql .= "          , urlkey)            ";
    $sql .= "  VALUES(                      ";
    $sql .= "            $1                 ";
    $sql .= "          , $2                 ";
    $sql .= "          , $3                 ";
    $sql .= "          , $4                 ";
    $sql .= "          , $5                 ";
    $sql .= "          , $6                 ";
    $sql .= "          , $7                 ";
    $sql .= "          , $8                 ";
    $sql .= "          , $9                 ";
    $sql .= "  )                            ";

    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query) {
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    }

    $_SESSION['message'] = 'Bài viết đã được thêm thành công';
    $_SESSION['messageClass'] = 'alert-success';
    $_SESSION['iconClass'] = 'fas fa-check';

    header("location: list-news.php");
    exit();

}

/**
 * Update News
 * @param $con
 * @param $func_id
 * @param $param
 * @param $idUser
 */
function updateNews($con, $func_id, $param, $idUser)
{
    $pg_param   = array();
    $pg_param[] = $param['category'];
    $pg_param[] = $param['title'];
    $pg_param[] = $param['shortdescription'];
    $pg_param[] = $param['urlImage'];
    $pg_param[] = $param['content'];
    $pg_param[] = $idUser;
    $pg_param[] = getDatetimeNow();
    $pg_param[] = convert_urlkey($param['title']);
    $pg_param[] = $param['nid'];

    $sql  = "";
    $sql .= "UPDATE news                     ";
    $sql .= "   SET category = $1,           ";
    $sql .= "       title = $2,              ";
    $sql .= "       shortdescription = $3,   ";
    $sql .= "       thumbnail = $4,          ";
    $sql .= "       content = $5,            ";
    $sql .= "       updateby = $6,           ";
    $sql .= "       updatedate = $7,         ";
    $sql .= "       urlkey = $8              ";
    $sql .= " WHERE id = $9                  ";

    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query) {
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    }

    $_SESSION['message'] = 'Bài viết đã được cập nhật thành công';
    $_SESSION['messageClass'] = 'alert-success';
    $_SESSION['iconClass'] = 'fas fa-check';

    header("location: list-news.php");
    exit();

}

/**
 * @param $con
 * @param $func_id
 * @param $nid
 */
function deleteNews($con, $func_id, $nid, $loginId)
{

    $pg_param   = array();
    $pg_param[] = getDatetimeNow();
    $pg_param[] = $loginId;
    $pg_param[] = $nid;

    $sql  = "";
    $sql .= "UPDATE news                     ";
    $sql .= "SET    deldate = $1,            ";
    $sql .= "       updateby = $2,           ";
    $sql .= "       updatedate = $1          ";
    $sql .= "WHERE  id = $3                  ";

    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query) {
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    }

    $_SESSION['message'] = 'Bài viết đã được xoá thành công';
    $_SESSION['messageClass'] = 'alert-success';
    $_SESSION['iconClass'] = 'fas fa-check';

    header("location: list-news.php");
    exit();

}

/**
 * @param $con
 * @param $func_id
 * @return string
 */
function getFirebaseConfig($con, $func_id)
{
    $arr_apps       = array();
    $firebaseConfig = '';
    $recCnt         = 0;

    $sql  = "";
    $sql .= "SELECT FIREBASECONFIG        ";
    $sql .= "  FROM APPS                  ";
    $sql .= " WHERE ID = 1                ";
    $query = pg_query($con, $sql);
    if (!$query) {
        systemError('systemError(' . $func_id . ') SQL Error：', $sql);
    } else {
        $recCnt = pg_num_rows($query);
    }
    if ($recCnt != 0) {
        $arr_apps = pg_fetch_assoc($query);
        $firebaseConfig = html_entity_decode($arr_apps['firebaseconfig']);
    }

    return $firebaseConfig;
}

/**
 * @param $link
 * @return string
 */
function getNameFileToLink($link)
{
    $str  = "";
    $link = substr($link, 76);
    $str  = $link;
    $str  = explode("?alt", $link);
    return $str[0];
}

/**
 * Get date time now
 * @return string
 */
function getDatetimeNow(){
    $datenow = '';
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    $datenow = date("Y-m-d H:i:s");
    return $datenow;
}

/**
 * Convert title to url key
 * @param $str
 * @return string|string[]|null
 */
function convert_urlkey($str) {
    $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
    $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
    $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
    $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
    $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
    $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
    $str = preg_replace("/(đ)/", 'd', $str);
    $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $str);
    $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $str);
    $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $str);
    $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $str);
    $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $str);
    $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $str);
    $str = preg_replace("/(Đ)/", 'D', $str);
    $str = preg_replace("/(\“|\”|\‘|\’|\,|\!|\&|\;|\@|\#|\%|\~|\`|\=|\_|\'|\]|\[|\}|\{|\)|\(|\+|\^)/", '-', $str);
    $str = preg_replace("/( )/", '-', $str);
    return $str;
}
?>

