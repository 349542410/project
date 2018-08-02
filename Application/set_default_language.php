<?php

    // 假如浏览器没有默认语言，则设置默认语言为中文
    if(empty($_COOKIE["think_language"])){
        $_COOKIE["think_language"] = "zh-CN";
    }