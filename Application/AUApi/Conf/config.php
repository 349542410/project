<?php
return array(
	//ThinkPHP3.2.3开始，规范起见，默认的数据库驱动类设置了 字段名强制转换为小写，如果你的数据表字段名采用大小写混合方式的话，需要在配置文件中增加如下设置：
	'DB_PARAMS'    =>    array(\PDO::ATTR_CASE => \PDO::CASE_NATURAL),//
);