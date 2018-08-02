/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50717
Source Host           : localhost:3306
Source Database       : mkil

Target Server Type    : MYSQL
Target Server Version : 50717
File Encoding         : 65001

Date: 2018-06-26 14:18:46
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `mk_transit_lading`
-- ----------------------------
DROP TABLE IF EXISTS `mk_transit_lading`;
CREATE TABLE `mk_transit_lading` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `lading_no` varchar(30) NOT NULL COMMENT '提单号',
  `take_off_time` datetime NOT NULL COMMENT '起飞时间',
  `arrive_time` datetime NOT NULL COMMENT '抵达时间',
  `gross_weight` double(10,3) NOT NULL COMMENT '毛重(KG)',
  `net_weight` double(10,3) NOT NULL COMMENT '净重(KG)',
  `number` int(5) NOT NULL COMMENT '该提单的订单总数量',
  `price` double(10,2) NOT NULL COMMENT '该提单的订单总额(￥)',
  `transport_no` varchar(30) NOT NULL COMMENT '航班号',
  `created_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `zzState` int(2) NOT NULL DEFAULT '0' COMMENT '卓志物流时间的推送状态(0未推送,1准备推送,2推送成功)',
  `zzTime` datetime DEFAULT NULL COMMENT '卓志物流推送时间',
  `TranKd` int(11) NOT NULL COMMENT 'transit_center表id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='批次号的提单信息';
