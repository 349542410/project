/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50717
Source Host           : localhost:3306
Source Database       : mkil

Target Server Type    : MYSQL
Target Server Version : 50717
File Encoding         : 65001

Date: 2018-06-27 14:03:01
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `mk_transit_lading_log`
-- ----------------------------
DROP TABLE IF EXISTS `mk_transit_lading_log`;
CREATE TABLE `mk_transit_lading_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL COMMENT '修改的内容',
  `note` varchar(50) NOT NULL COMMENT '管理员填写的备注',
  `operator_id` int(6) NOT NULL COMMENT '管理员id',
  `sys_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '操作时间',
  `type` varchar(30) NOT NULL COMMENT '补录类型',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
