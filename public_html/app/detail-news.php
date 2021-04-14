<?php

//Common setting
require_once ('config.php');
require_once ('lib.php');

//Initialization
$func_id = 'list_student';

session_start();

//Get param
$param = getParam();
$mode = $param['mode'] ?? 'new';
$nid = $param['nid'] ?? '';

$role = $_SESSION['role'] ?? '';

//Connect DB
$con = openDB();

if (!isset($_SESSION['loginId'])){
    header('location: login.php');
    exit();
}

//get data edits
if(isset($nid) && (mb_strlen($nid) > 0)){
    $edit_new       = get_newsedit($con, $func_id,$nid);
    $valuetitle     = $edit_new['title'] ;
    $valuecategory  = $edit_new['category'];
    $valueshortdes  = $edit_new['shortdescription'];
    $valueusers     = $edit_new['fullname'];
    $valuethumbnail = $edit_new['thumbnail'];
    $valuecontent   = $edit_new['content'];
} else {
    $valuetitle     = $param['title'] ?? '' ;
    $valueRole      = $param['category'] ?? '';
    $valueshortdes  = $param['shortdescription'] ?? '';
    $valueusers     = $param['fullname'] ?? '';
    $valuethumbnail = $param['thumbnail'] ?? '';
    $valuecontent   = $param['content'] ?? '';
}

//get combobox
$showcategoryhtml = show_category($con, $func_id, $valuecategory);

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
                                <i class="fas fa-plus-square"></i>&nbspThêm bài viết</h1>
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
                            <form action="" method="POST">
                                <div class="card card-info">
                                    <div class="card-header">
                                        <h3 class="card-title">Thêm bài viết</h3>
                                    </div>
                                    <div class="card-body">
                                        <label>Danh mục</label>
                                        <div class="input-group mb-3">
                                            {$showcategoryhtml}
                                        </div>

                                        <label>Tiêu đề</label>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" placeholder="Tiêu đề" value="{$valuetitle}">
                                        </div>

                                        <label>Mô tả ngắn</label>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" placeholder="Mô tả ngắn" value="{$valueshortdes}}">
                                        </div>

                                        <label>Người đăng</label>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" value="{$valueusers}" readonly>
                                        </div>

                                        <label>Thumbnail</label>
                                        <div class="input-group mb-3">
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" id="customFile" value="{$valuethumbnail}">
                                                <label class="custom-file-label" for="customFile">Chọn file</label>
                                            </div>
                                        </div>

                                        <label>Nội dung</label>
                                        <textarea id="summernote">{$valuecontent}</textarea>
                                    </div>
                                    <!-- /.card-body -->
                                    <div class="card-footer">
                                        <button type="submit" class="btn btn-primary float-right" style="background-color: #17a2b8;">
                                            <i class="fas fa-save"></i>
                                            &nbspLưu
                                        </button>
                                        <a href="#" id="btn_clear">
                                            <button type="button" class="btn btn-danger">
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

?>

