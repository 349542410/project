ALTER TABLE `mk_tran_ulist` ADD `idno_auth` TINYINT( 3 ) NOT NULL DEFAULT '0' COMMENT '是否实名认证，0没有认证，1认证成功，2认证失败' AFTER `id_no_status`;