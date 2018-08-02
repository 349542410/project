<?php
return array(
	// 多语言
    'app_begin' => array('Behavior\CheckLangBehavior'),

    //表单令牌
    'view_filter' => array('Behavior\TokenBuildBehavior'),
);