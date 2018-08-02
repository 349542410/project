/*
Navicat MySQL Data Transfer

Source Server         : root
Source Server Version : 50553
Source Host           : 127.0.0.1:3306
Source Database       : mkil

Target Server Type    : MYSQL
Target Server Version : 50553
File Encoding         : 65001

Date: 2018-05-19 11:11:48
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for mk_user_order_goods_name
-- ----------------------------
DROP TABLE IF EXISTS `mk_user_order_goods_name`;
CREATE TABLE `mk_user_order_goods_name` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `user_id` int(10) unsigned NOT NULL COMMENT '用户id',
  `goods_name` varchar(40) NOT NULL COMMENT '中文商品名称',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `goods_name` (`goods_name`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8 COMMENT='用户下单中文商品名称表';

