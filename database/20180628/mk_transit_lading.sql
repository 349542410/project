ALTER TABLE `mk_transit_lading` ADD `ems_state` INT( 2 ) NOT NULL DEFAULT '0' COMMENT 'EMS推送状态(0未推送 1准备推送 2推送成功 3推送失败)';

ALTER TABLE `mk_transit_lading` ADD `ems_time` DATETIME NULL DEFAULT NULL COMMENT 'ems推送时间';

ALTER TABLE `mk_transit_lading` ADD `ems_return` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'ems推送返回信息'