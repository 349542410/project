ALTER TABLE `mk_logistics_transit` ADD `id` INT( 11 ) NOT NULL AUTO_INCREMENT FIRST ,
ADD PRIMARY KEY ( `id` );

ALTER TABLE `mk_logistics_transit` ADD `status` INT( 2 ) NOT NULL DEFAULT '0' COMMENT '0为未使用 10正在使用中 20已使用';