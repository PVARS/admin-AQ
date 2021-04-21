<?php

//Common setting
require_once ('config.php');
require_once ('lib.php');

//Initialization
$func_id        = 'list_student';
$userLogin      = array();
$imageAtt       = '';
$valuecategory  = '';
$titlePage      = '';
$messageSwal = 0; // 0: invisible; 1: visible

session_start();

//Get param
$param = getParam();
$mode  = $param['mode'] ?? 'new';
$nid   = $param['nid'] ?? '';

$role  = $_SESSION['role'] ?? '';

/*data in form*/
$submit       = $param['btn_saveOrUpdate'] ?? '';
$f_category   = $param['category'] ?? '';
$f_title      = $param['title'] ?? '' ;
$f_shortdes   = $param['shortdescription'] ?? '';
$f_fullname   = $param['fullname'] ?? '';
$f_thumbnail  = $param['thumbnail'] ?? '';
$f_urlImamge  = $param['urlImage'] ?? '';
$f_content    = $param['content'] ?? '';
$messageSwal = $param['messageSwal'] ?? 0;

//Connect DB
$con = openDB();

if (!isset($_SESSION['loginId'])){
    header('location: login.php');
    exit();
}

// Get id and fullname user
$userLogin = getUserByLoginId($con, $func_id, $_SESSION['loginId']);

if (isset($submit) && (mb_strlen($submit) > 0)) {

    if (isset($nid) && (mb_strlen($nid) > 0)) { /*Update News*/
        updateNews($con, $func_id, $param, $userLogin['id']);
    } else { /*Insert News*/
        insertNews($con, $func_id, $param, $userLogin['id']);
    }

}

//get data edits
if(isset($nid) && (mb_strlen($nid) > 0)){
    $titlePage = "Chỉnh sửa bài viết";
    $edit_new       = get_newsedit($con, $func_id,$nid);
    $valuetitle     = $edit_new['title'] ;
    $valuecategory  = $edit_new['category'];
    $valueshortdes  = $edit_new['shortdescription'];
    $valueusers     = $edit_new['fullname'];
    $valuethumbnail = $edit_new['thumbnail'];
    $f_urlImamge    = $edit_new['thumbnail']; // set url image
    $valuecontent   = $edit_new['content'];
    $valuethumbnail = getNameFileToLink($valuethumbnail);
} else {
    $titlePage = "Thêm bài viết";
    $valuetitle     = $param['title'] ?? '' ;
    $valueRole      = $param['category'] ?? '';
    $valueshortdes  = $param['shortdescription'] ?? '';
//    $valueusers     = $param['fullname'] ?? '';
    $valueusers     = $userLogin['fullname'];
    $valuethumbnail = "Chọn file";
    $valuecontent   = $param['content'] ?? '';
}

// Set param url image
if (isset($f_urlImamge) && (mb_strlen($f_urlImamge) > 0)) {
    $imageAtt = 'src="' . $f_urlImamge . '" class="mb-2" height="100"';
}

//get combobox
$showcategoryhtml = show_category($con, $func_id, $valuecategory);

//-----------------------------------------------------------
// HTML
//-----------------------------------------------------------
$titleHTML = '';
$cssHTML = '';
$scriptHTML = <<<EOF
<!-- The core Firebase JS SDK is always required and must be listed first -->
<script src="https://www.gstatic.com/firebasejs/8.4.1/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.4.1/firebase-storage.js"></script>
    
<!-- TODO: Add SDKs for Firebase products that you want to use 
https://firebase.google.com/docs/web/setup#available-libraries -->
<script src="https://www.gstatic.com/firebasejs/8.4.1/firebase-analytics.js"></script>

<script>
    // Your web app's Firebase configuration
    // For Firebase JS SDK v7.20.0 and later, measurementId is optional
    var firebaseConfig = {
        apiKey: "AIzaSyBTF-NeiTUHRLqArhelm_AfCJ-bWgq8Umg",
        authDomain: "arsenalquan-82401.firebaseapp.com",
        projectId: "arsenalquan-82401",
        storageBucket: "arsenalquan-82401.appspot.com",
        messagingSenderId: "426824354942",
        appId: "1:426824354942:web:1acc2d5be62d6d72191c2f",
        measurementId: "G-B0TKQTF919"
    };
    
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
        }else{ /*return false*/
            alert("Only jpg/jpeg and png files are allowed!");
            file.value = "";  // Reset the input so no files are uploaded
            document.getElementById("urlImage").value = '';
            document.getElementById("image").style.display = "none";
            document.getElementById("image").src = '';
        }
    }

    /*Submit form and upload file image*/
    function uploadImage() {
        var file_image = document.getElementById("thumbnail");
        var url_image = document.getElementById("urlImage");
        document.getElementById("btn_saveOrUpdate").value = 'save';
        
        /*Check file exist*/
        if (url_image.value == "" && file_image.files.length == 0){
            alert("Image Upload no file selected");
        } else if (file_image.files.length == 0){ 
            document.getElementById("ismForm").submit(); /*Submit form*/
        } else{     
            // Disable button save
            document.getElementById("submit_saveOrUpdate").style.display = "none";
            document.getElementById("submit_disable").style.display = "block";
            
            /*Upload file to Firebase*/
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
                    document.getElementById("ismForm").submit(); /*Submit form*/
                });
        }
    }
    
    if ({$messageSwal} == 1){
        document.getElementById("submit_saveOrUpdate").style.display = "none";
        document.getElementById("submit_disable").style.display = "block";
        Swal.fire({
            position: 'top',
            icon: 'success',
            title: 'Bản tin đã được xóa thành công',
            showConfirmButton: false,
            timer: 1500
        })
     }
    
    $(function() {
        
        // Set name file
        $('#thumbnail').change(function() {
          // var i = $(this).prev('label').clone();
          var file = $('#thumbnail')[0].files[0].name;
          console.log(file);
          $(this).next('.custom-file-label').html(file);
        });
        
        // Button clear
        $('#btn_clear').on('click', function(e) {
            e.preventDefault();
            var message = "Đặt màn hình tìm kiếm về trạng thái ban đầu?";
            var that = $(this)[0];
            sweetConfirm(1, message, function(result) {
                if (result){
                    window.location.href = location.protocol + '//' + location.host + location.pathname;
                }
            });
        });
    })
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
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
EOF;

//Preloader
include ($TEMP_APP_PRELOADER_PATH);

//Header
include ($TEMP_APP_HEADER_PATH);

//Menu
if ($role == '1'){
    include ($TEMP_APP_MENUSYSTEM_PATH);
} else {
    include ($TEMP_APP_MENU_PATH);
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
                        <div class="card-body">
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
                                            <input type="text" name="title" class="form-control" placeholder="Tiêu đề" value="{$valuetitle}">
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
                                        <input type="hidden" name="messageSwal" value="1">
                                        <input type="hidden" name="btn_saveOrUpdate" id="btn_saveOrUpdate" value="saveOrUpdate">
                                        <span type="submit" class="btn btn-primary float-right" onclick="uploadImage()" name="submit_saveOrUpdate" id="submit_saveOrUpdate" style="background-color: #17a2b8;">
                                            <i class="fas fa-save"></i>&nbspLưu
                                        </span>
                                        <button class="btn btn-primary float-right" id="submit_disable" disabled style="display: none;">
                                            <i class="fas fa-save"></i>&nbspLưu
                                        </button>
                                        <a href="#" id="btn_clear">
                                            <button type="reset" class="btn btn-danger">
                                            <i class="fas fa-trash fa-fw"></i>
                                            Xoá
                                          </button>
                                        </a>
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
/*
 * function get data edit with nid;
 */
function get_newsedit($con, $func_id, $nid){

    $editArray = array();
    $pg_param = array();
    $pg_param[] = $nid;
    $recCnt = 0;
    $sql = "";
    $sql .= "SELECT news.title                               ";
    $sql .= " ,news.shortdescription                         ";
    $sql .= " ,users.fullname                                ";
    $sql .=" ,news.thumbnail                                 ";
    $sql .=" ,news.category                                  ";
    $sql .=" ,news.content                                   ";
    $sql .= " FROM news                                      ";
    $sql .= " INNER JOIN users                               ";
    $sql .= " ON news.createby = users.id                    ";
//    $sql .= " INNER JOIN category                            ";
//    $sql .= " ON category.id = news.category                 ";
    $sql .= " WHERE news.id = $1                             ";

    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query){
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    } else {
        $recCnt = pg_num_rows($query);
    }

    if ($recCnt != 0){
        $editArray = pg_fetch_assoc($query);
    }
    return $editArray;
}

/*
 * get category in form edit
 * Function works with combobox
*/
function show_category($con, $func_id, $valuecategory){
    $pg_param = array();
    $recCnt = 0;
    $sql = '';
    $sql .= 'SELECT *FROM category ';

    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query){
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    }else {
        $recCnt = pg_num_rows($query);
    }

    $html = '<select class="custom-select" name="category">';
    if ($recCnt != 0){
        while ($row = pg_fetch_assoc($query)){
            $selected = '';
            if ($valuecategory == $row['id']){
                $selected = 'selected="selected"';
            }
            $html .= '<option value="'.$row['id'].'" '.$selected.'>'.$row['category'].'</option>';
        }
    }
    $html .= '</select>';
    return $html;

}

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
 * Get id, fullname of user by loginid
 * @param $con
 * @param $func_id
 * @param $uid
 * @return array
 */
function getUserByLoginId($con, $func_id, $uid){
    $user = array();
    $pg_param = array();
    $pg_param[] = $uid;

    $sql = "";
    $sql .= "SELECT id, fullname        ";
    $sql .= "FROM users                 ";
    $sql .= "WHERE loginid = $1         ";
    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query){
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    }else {
        $recCnt = pg_num_rows($query);
    }
    if ($recCnt != 0){
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
function insertNews($con, $func_id, $param, $idUser){
    $pg_param = array();
    $pg_param[]    = $param['category'];
    $pg_param[]        = $param['title'];
    $pg_param[]     = $param['shortdescription'];
    $pg_param[]    = $param['urlImage'];
    $pg_param[]      = $param['content'];
    $pg_param[]      = getDateTime();
    $pg_param[]      = $idUser;

    $sql = "";
    $sql .= "INSERT INTO news(              ";
    $sql .= "            category           ";
    $sql .= "          , title              ";
    $sql .= "          , shortdescription   ";
    $sql .= "          , thumbnail          ";
    $sql .= "          , content            ";
    $sql .= "          , createdate         ";
    $sql .= "          , createby)          ";
    $sql .= "  VALUES(                      ";
    $sql .= "            $1                 ";
    $sql .= "          , $2                 ";
    $sql .= "          , $3                 ";
    $sql .= "          , $4                 ";
    $sql .= "          , $5                 ";
    $sql .= "          , $6                 ";
    $sql .= "          , $7                 ";
    $sql .= "  )                            ";

    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query){
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    }

}

/**
 * Update News
 * @param $con
 * @param $func_id
 * @param $param
 * @param $idUser
 */
function updateNews($con, $func_id, $param, $idUser){
    $pg_param = array();
    $pg_param[] = $param['category'];
    $pg_param[] = $param['title'];
    $pg_param[] = $param['shortdescription'];
    $pg_param[] = $param['urlImage'];
    $pg_param[] = $param['content'];
    $pg_param[] = $idUser;
    $pg_param[] = $param['nid'];

    $sql = "";
    $sql .= "UPDATE news                     ";
    $sql .= "SET    category = $1,           ";
    $sql .= "       title = $2,              ";
    $sql .= "       shortdescription = $3,   ";
    $sql .= "       thumbnail = $4,          ";
    $sql .= "       content = $5,            ";
    $sql .= "       updateby = $6            ";
    $sql .= "WHERE  id = $7                   ";

    $query = pg_query_params($con, $sql, $pg_param);
    if (!$query){
        systemError('systemError(' . $func_id . ') SQL Error：', $sql . print_r($pg_param, true));
    }

}

function getNameFileToLink($link){
    $str = "";
    $link = substr($link, 76);
    $str = $link;
    $str = explode("?alt", $link);
    return $str[0];
}

?>

