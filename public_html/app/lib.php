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
function sytemError(){
    closeDB();
    //Print error
    systemErrorPrint();
    exit();
}
?>
