<?php
return array(
    // 添加下面一行定义即可		调用表单验证的类
    'view_filter' => array('Behavior\TokenBuildBehavior'),
    'app_begin' => array('Behavior\CheckLangBehavior'),
);