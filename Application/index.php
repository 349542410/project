<?php
    //会自动生成Api
    //define('BIND_MODULE', 'Api');
    define('ADMIN_JCM', $GLOBALS['globalConfig']['RESURL'].'/Admin');//http://res.megao.hk/c/Admin   css,js,img专用
    define('ADMIN_FILE', $GLOBALS['globalConfig']['FILEURL'].'/app81/Admin');  // ADV 文件上传位置 http://file.megao.hk/c/Admin
    define('ADMIN_ABS_FILE', $GLOBALS['globalConfig']['UPFILEBASE'].'/app81/Admin');  // ADV 文件上传位置  硬盘路径

    define('TEMPLATE_FILE', $GLOBALS['globalConfig']['UPFILEBASE'].'/webuser/download/production/import_tmp');  // 线路批量下载模板存放位置  硬盘路径
    define('DELIVERY_FILE', $GLOBALS['globalConfig']['UPFILEBASE'].'/webuser/download/production/order_goods');  // 线路快件信息下载模板存放位置  硬盘路径
    //set_time_limit(0);
    //ini_set('memory_limit','4088M');
    //ini_set('max_execution_time', 0);

    //Api
    define('API_JCM', $GLOBALS['globalConfig']['RESURL'].'/Api');
    define('API_FILE', $GLOBALS['globalConfig']['FILEURL']);
    define('API_ABS_FILE', $GLOBALS['globalConfig']['UPFILEBASE']);

    //webuser
    define('WU_JCM', $GLOBALS['globalConfig']['RESURL'].'/webuser');
    define('WEB_JCM', $GLOBALS['globalConfig']['RESURL'].'/Web');
    define('WU_FILE', $GLOBALS['globalConfig']['FILEURL']);
    define('WU_ABS_FILE', $GLOBALS['globalConfig']['UPFILEBASE']);

    // 域名配置
    define('API_URL', $GLOBALS['globalConfig']['API_URL']);
    define('MEMBER_URL', $GLOBALS['globalConfig']['MEMBER_URL']);
    define('WEB_URL', $GLOBALS['globalConfig']['WEB_URL']);
    define('ADMIN_URL', $GLOBALS['globalConfig']['ADMIN_URL']);
    define('WAP_URL', $GLOBALS['globalConfig']['WAP_URL']);
    define('MKAUTO_URL', $GLOBALS['globalConfig']['MKAUTO_URL']);
    define('AUAPI_URL', $GLOBALS['globalConfig']['AUAPI_URL']);

    define('UPFILEBASE', $GLOBALS['globalConfig']['UPFILEBASE']);

    define('PY_UPFILE',  $GLOBALS['globalConfig']['UPFILEBASE'].'/Upfile');
    define('LOG_URL',  $GLOBALS['globalConfig']['UPFILEBASE'].'/Logs');
    // wap
    define('MOB_JCM', $GLOBALS['globalConfig']['RESURL'].'/Wap');

    //获取物流信息
    define('LOGISTICS_DOMAIN',$GLOBALS['globalConfig']['Logistics']['domain']);

    define('WX_NOTIFY_URL', $GLOBALS['globalConfig']['WX_NOTIFY_URL']);

