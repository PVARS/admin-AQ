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
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
EOF;

//Preloader
include ($TEMP_APP_PRELOADER_PATH);

//Header
include ($TEMP_APP_HEADER_PATH);

//Menu
include ($TEMP_APP_MENU_PATH);

//Conntent
echo <<<EOF
<div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">
                                <i class="fas fa-search"></i>&nbspTìm kiếm bài viết</h1>
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
                                        <h3 class="card-title">Tìm kiếm</h3>
                                    </div>
                                    <div class="card-body">
                                        <label>Tiêu đề</label>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" placeholder="Tiêu đề">
                                        </div>

                                        <label>Danh mục</label>
                                        <div class="input-group mb-3">
                                            <select class="custom-select">
                                                <option>option 1</option>
                                                <option>option 2</option>
                                                <option>option 3</option>
                                                <option>option 4</option>
                                                <option>option 5</option>
                                              </select>
                                        </div>

                                        <label>Người đăng</label>
                                        <div class="input-group mb-3">
                                            <select class="custom-select">
                                                <option>option 1</option>
                                                <option>option 2</option>
                                                <option>option 3</option>
                                                <option>option 4</option>
                                                <option>option 5</option>
                                              </select>
                                        </div>

                                        <label>Từ khoá</label>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" placeholder="Nhập từ khoá">
                                        </div>

                                        <label>Thời gian</label>
                                        <div class="row">
                                            <div class="input-group mb-6 col-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                                </div>
                                                <input type="date" class="form-control">
                                            </div>
                                            <span><b>~</b></span>
                                            <div class="input-group mb-6 col-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                                </div>
                                                <input type="date" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /.card-body -->
                                    <div class="card-footer">
                                        <button type="submit" class="btn btn-primary float-right" style="background-color: #17a2b8;">
                                          <i class="fa fa-search"></i>
                                          &nbspTìm kiếm
                                        </button>
                                        <a href="#" id="btn_clear">
                                            <button type="button" class="btn btn-default">
                                            <i class="fas fa-eraser fa-fw"></i>
                                            Xoá
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
                                        <th style="width: 5%;" class="text-th">STT</th>
                                        <th style="width: 35%;" class="text-th">Tiêu đề</th>
                                        <th style="width: 20%;" class="text-th">Người đăng</th>
                                        <th style="text-align: center; width: 20%;" class="text-th">Ngày đăng</th>
                                        <th style="text-align: center; width: 20%;" class="text-th">Lượt xem</th>
                                        <th colspan="3"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="width: 5%;">1</td>
                                        <td style="width: 35%;">Chỉ trích bủa vây, Arteta gặp nạn lớn ở Arsenal </td>
                                        <td style="width: 20%;">Lê Văn Lư</td>
                                        <td style="text-align: center; width: 20%;">07/04/2021</td>
                                        <td style="text-align: center; width: 20%;">100</td>
                                        <td style="text-align: center; width: 5%;">
                                            <form action="" method="POST">
                                                <a href="javascript:void(0)" class="btn btn-block btn-primary btn-sm"><i class="fas fa-edit"></i></a>
                                            </form>
                                        </td>
                                        <td style="text-align: center; width: 10%;">
                                            <form action="" method="POST">
                                                <a href="javascript:void(0)" class="btn btn-block btn-success btn-sm">
                                                    <i class="fas fa-plus"></i>
                                                </a>
                                            </form>
                                        </td>
                                        <td style="text-align: center; width: 5%;">
                                            <form action="" method="POST">
                                                <a href="javascript:void(0)" class="btn btn-block btn-danger btn-sm"><i class="fas fa-trash"></i></a>
                                            </form>
                                        </td>
                                    </tr>
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
include ($TEMP_APP_FOOTER_PATH);
//Meta JS
include ($TEMP_APP_METAJS_PATH);
echo <<<EOF
    </div>
</body>
</html>
EOF;

?>

