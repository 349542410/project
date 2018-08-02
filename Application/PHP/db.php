<?php
    header("Content-type: text/html; charset=utf-8");
    $commonConfig = require __DIR__.'/../Common/Conf/config.php';
    error_reporting(E_ALL);//错误等级设置
    date_default_timezone_set('PRC');
    ini_set('memory_limit','4088M');
    set_time_limit(0);

    $dsn    = $commonConfig['DB_TYPE'].':dbname='.$commonConfig['DB_NAME'].';host='.$commonConfig['DB_HOST'];
    $user   = $commonConfig['DB_USER'];	//数据库用户名
    $passwd = $commonConfig['DB_PWD'];	//数据库密码

    try{
        $pdo = new PDO($dsn,$user,$passwd);
        $pdo->query('set names '.$commonConfig['DB_CHARSET']);//设置字符集
    }catch(PDOException $e){
        echo '数据库连接失败'.$e->getMessage();die;
    }