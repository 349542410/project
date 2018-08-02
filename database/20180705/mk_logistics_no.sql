ALTER TABLE `mk_logistics_no` CHANGE `use_time` `use_time` DATETIME NULL DEFAULT NULL COMMENT '使用时间';

ALTER TABLE `mk_logistics_no` CHANGE `add_time` `add_time` DATETIME NOT NULL COMMENT '添加时间';