/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50717
Source Host           : localhost:3306
Source Database       : mkil

Target Server Type    : MYSQL
Target Server Version : 50717
File Encoding         : 65001

Date: 2018-06-20 16:32:45
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `mk_logistics_transit`
-- ----------------------------
DROP TABLE IF EXISTS `mk_logistics_transit`;
CREATE TABLE `mk_logistics_transit` (
  `logistics_id` int(11) NOT NULL COMMENT 'logistics_no表主键id',
  `transit_center_id` int(11) NOT NULL COMMENT 'transit_center表主键id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='号段表与线路表的关联';