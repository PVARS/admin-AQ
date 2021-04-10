<?php
/**
 * Connect db
 * @return mysqli
 */
function openDB(){
    global $DB_CONNECT_PATH;

    require (dirname(__FILE__) . $DB_CONNECT_PATH);
    $host = 'host = '.$dsn['host'].' port = '.$dsn['port'].' user = '.$dsn['user'].' dbname = '.$dsn['dbname'].' password = '.$dsn['password'];
    $con = @pg_connect($host);
    
    if(!$con){
        systemError('systemError(lib) Database connection error'.$host);
    } else{
        pg_set_client_encoding($con, "UTF-8");
    }
    return $con;
}

function closeDB(){
    pg_close();
}

/**
 * Error page
 */
function systemErrorPrint(){
    echo <<<EOF
    <!DOCTYPE html>
    <html>
    <head>
    <meta charset="UTF-8">
    <title>System Error</title>
    </head>
    <body id="systemError">
    <section id="main">
        <article id="login_form" class="module width_half">
            <header><h3>The system is paused</h3></header>
            <div class="module_content">
                <p>We apologize for the inconvenience. <br /> Excuse me, but please wait a little longer.</p>
            </div>
        </article>
    </section>
    
    </body>
    </html>
EOF;
}

/**
 * Notification error
 */
function systemError(){
    closeDB();
    //Print error
    systemErrorPrint();
    exit();
}

/**
 * Eliminate full-width and half-width spaces
 * @param $str
 * @return string
 */
function trimBlank($str){
    $stringValue = $str;
    $stringValue=trim($stringValue);
    
    return $stringValue;
}

/**
 * Get param
 * @return array
 */
function getParam(){
    $param = array();
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $a = $_POST;
    }else{
        $a = $_GET;
    }
    foreach($a as $k => $v) {
        if (is_array($v)) {
            foreach($v as $k2 => $v2) {
                if(get_magic_quotes_gpc()) {
                    $v2 = stripslashes($v2);
                }
                $v2 = htmlspecialchars($v2,ENT_QUOTES);
                $v2 = trimBlank($v2);
                $param[$k][$k2] = $v2;
            }
        }else{
            if(get_magic_quotes_gpc()) {
                $v = stripslashes($v);
            }
            $v = htmlspecialchars($v,ENT_QUOTES);
            //        $v = mb_convert_encoding($v, 'UTF-8', 'sjis-win');
            //        $v = mb_convert_encoding($v, 'UTF-8');
            $v = trimBlank($v);
            $param[$k] = $v;
        }
    }
    return $param;
}

/**
 * Get date time current
 * @return false|string
 */
function getDateTime(){
    date_default_timezone_set("Asia/Ho_Chi_Minh");
    $dateTime = date("d-m-yy h:i:sa");
    return $dateTime;
}

/**
 * Get date
 * @return false|string
 */
function getDateVn(){
    date_default_timezone_set("Asia/Ho_Chi_Minh");
    $date = date("yy-m-d");
    return $date;
}
?>
