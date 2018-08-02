



CREATE TABLE mk_line_template_log
(
`id` int UNSIGNED  NOT NULL  AUTO_INCREMENT ,
`line_id` int comment '线路id',
`whether_batch` 	TINYINT(1) DEFAULT 0 comment '是否有批量模板上传	0为没有上传 1为上传模板',
`is_goods`	TINYINT(1) DEFAULT 0   comment '是否有商品模板上传	0为没有上传 1为上传模板',
`update_time` 	timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP  comment '模板上传时间',

PRIMARY KEY (id)

)COMMENT='模板上传日志';

