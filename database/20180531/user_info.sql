ALTER TABLE `mk_user_extra_info` ADD `tel` VARCHAR( 30 ) NULL DEFAULT NULL COMMENT '手机号码' AFTER `idno` ,
ADD INDEX ( `tel` ) ;


ALTER TABLE `mk_tran_ulist` ADD `package_id` VARCHAR( 200 ) NULL DEFAULT NULL COMMENT '导入订单的包裹号',
ADD INDEX ( `package_id` ) ;



ALTER TABLE `mk_tran_ulist`
  ADD COLUMN `front_id_img` varchar(100) NULL DEFAULT NULL COMMENT '收件人证件照正面' AFTER `package_id`,
  ADD COLUMN `small_front_img` varchar(100) NULL DEFAULT NULL COMMENT '收件人身份证正面缩略图' AFTER `front_id_img`,
  ADD COLUMN `back_id_img` varchar(100) NULL DEFAULT NULL COMMENT '收件人证件照反面' AFTER `small_front_img`,
  ADD COLUMN `small_back_img` varchar(100) NULL DEFAULT NULL COMMENT '收件人身份证背面缩略图';


ALTER TABLE `mk_tran_ulist` ADD `certify_upload_type` TINYINT( 3 ) NOT NULL DEFAULT '2' COMMENT '证件的上传类型 1寄件人上传 2收件人上传 默认为2';
ALTER TABLE `mk_tran_ulist` ADD `lib_idcard` INT( 10 ) NOT NULL DEFAULT '0' COMMENT '身份证库的id，若没有则为0，默认为0';


ALTER TABLE `mk_tran_list`
  ADD COLUMN `front_id_img` varchar(100) NULL DEFAULT NULL COMMENT '收件人证件照正面' AFTER `send_pay_status`,
  ADD COLUMN `small_front_img` varchar(100) NULL DEFAULT NULL COMMENT '收件人身份证正面缩略图' AFTER `front_id_img`,
  ADD COLUMN `back_id_img` varchar(100) NULL DEFAULT NULL COMMENT '收件人证件照反面' AFTER `small_front_img`,
  ADD COLUMN `small_back_img` varchar(100) NULL DEFAULT NULL COMMENT '收件人身份证背面缩略图';


  ALTER TABLE `mk_user_addressee`
  CHANGE COLUMN `id_card_back_small` `id_card_back_small` varchar(200) NULL DEFAULT NULL COMMENT '身份证反面-缩略图';
  ALTER TABLE `mk_user_addressee`
  CHANGE COLUMN `id_card_front_small` `id_card_front_small` varchar(200) NULL DEFAULT NULL COMMENT '身份证正面-缩略图';
  ALTER TABLE `mk_user_addressee`
  CHANGE COLUMN `id_card_back` `id_card_back` varchar(200) NULL DEFAULT NULL COMMENT '身份证反面';
  ALTER TABLE `mk_user_addressee`
  CHANGE COLUMN `id_card_front` `id_card_front` varchar(200) NULL DEFAULT NULL COMMENT '身份证正面';



  ALTER TABLE `mk_tran_ulist` ADD `id_no_status` TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否已填写身份证号码，0表示未填写，100表示已填写，200表示无需填写' AFTER `id_img_status` ;
  ALTER TABLE `mk_tran_ulist`
    CHANGE COLUMN `id_img_status` `id_img_status` tinyint(3) unsigned NULL DEFAULT NULL COMMENT '是否已上传身份证照片，未上传0，已上传100，无需上传200';

  alter table mk_tran_uorder add is_suit tinyint(3) default 0 comment '是否套装（默认为 0   0为非套装， 1为套装）';
  ALTER TABLE mk_tran_order ADD is_suit TINYINT(3) DEFAULT 0 COMMENT  '是否套装（默认为 0   0为非套装， 1为套装）';


  ALTER TABLE `mk_tran_ulist` ADD `delete_time` TIMESTAMP NULL DEFAULT NULL COMMENT '删除时间';
  ALTER TABLE `mk_tran_uorder` ADD `delete_time` TIMESTAMP NULL DEFAULT NULL COMMENT '删除时间';
  ALTER TABLE `mk_user_addressee` ADD `delete_time` TIMESTAMP NULL DEFAULT NULL COMMENT '删除时间';
  ALTER TABLE `mk_user_sender` ADD `delete_time` TIMESTAMP NULL DEFAULT NULL COMMENT '删除时间';



  ALTER TABLE `mk_order_excel` CHANGE COLUMN `postcode` `postcode` varchar(10) NULL DEFAULT NULL COMMENT '收件人邮编';


  ALTER TABLE `mk_user_addressee` ADD `is_supplement` INT NOT NULL DEFAULT '0' COMMENT '身份证图片是否为补填的，0不是，1是';