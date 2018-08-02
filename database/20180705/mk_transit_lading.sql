ALTER TABLE `mk_transit_lading` DROP `ems_state` ,DROP `ems_time` ;

ALTER TABLE `mk_transit_lading` CHANGE `ems_return` `return` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '提单推送返回的信息';

ALTER TABLE `mk_transit_lading` CHANGE `zzState` `zzState` INT( 2 ) NOT NULL DEFAULT '0' COMMENT '提单推送状态(0未推送,1准备推送,2推送成功,3推送失败)';

ALTER TABLE `mk_transit_lading` CHANGE `zzTime` `zzTime` DATETIME NULL DEFAULT NULL COMMENT '提单推送时间'

ALTER TABLE `mk_transit_lading` ADD `order_state` INT( 2 ) NOT NULL DEFAULT '0' COMMENT '订单推送状态(0未推送 1全部推送成功 2部分推送成功)目前是线路23使用';