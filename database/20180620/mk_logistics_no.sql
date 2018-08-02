/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50717
Source Host           : localhost:3306
Source Database       : mkil

Target Server Type    : MYSQL
Target Server Version : 50717
File Encoding         : 65001

Date: 2018-06-20 16:32:37
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `mk_logistics_no`
-- ----------------------------
DROP TABLE IF EXISTS `mk_logistics_no`;
CREATE TABLE `mk_logistics_no` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(64) DEFAULT NULL,
  `uuidtime` varchar(40) DEFAULT NULL COMMENT 'microtime函数',
  `status` int(2) NOT NULL DEFAULT '0' COMMENT '0为未使用 10正在使用中 20已使用',
  `no` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '号段',
  `MKNO` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '美快单号',
  `use_time` int(11) DEFAULT NULL COMMENT '使用时间',
  `add_time` int(11) NOT NULL COMMENT '添加时间',
  `messages` varchar(100) DEFAULT NULL COMMENT '最后一条物流信息',
  `kd100status` int(3) NOT NULL DEFAULT '0',
  `cuid` varchar(64) DEFAULT NULL COMMENT '标识码',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='号段表(参考mk_zjnolist/ems13nolist等表)';