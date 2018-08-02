CREATE TABLE mk_view_status(
	`id` BIGINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT COMMENT 'ID',
	`user_id` INT UNSIGNED NOT NULL COMMENT '用户id',
	`group` varchar(200) NOT NULL DEFAULT '' COMMENT '分组',
	`attr_one` varchar(200) NOT NULL DEFAULT '' COMMENT '附加属性1'
)COMMENT='查看状态管理';