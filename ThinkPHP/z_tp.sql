/*
Navicat MySQL Data Transfer

Source Server         : 本地
Source Server Version : 50553
Source Host           : localhost:3306
Source Database       : z_tp

Target Server Type    : MYSQL
Target Server Version : 50553
File Encoding         : 65001

Date: 2017-03-07 00:06:59
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `ztp_user`
-- ----------------------------
DROP TABLE IF EXISTS `ztp_user`;
CREATE TABLE `ztp_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户表',
  `username` char(20) NOT NULL COMMENT '用户名，昵称',
  `phone` int(20) NOT NULL COMMENT '电话号码',
  `email` char(32) NOT NULL COMMENT '邮箱',
  `pwd` char(32) NOT NULL COMMENT '密码 登录密码',
  `twopwd` char(32) NOT NULL COMMENT '二级密码 支付密码',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ztp_user
-- ----------------------------

-- ----------------------------
-- Table structure for `ztp_user_bank`
-- ----------------------------
DROP TABLE IF EXISTS `ztp_user_bank`;
CREATE TABLE `ztp_user_bank` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户银行邦定',
  `uid` int(11) NOT NULL,
  `bank_name` varchar(10) NOT NULL COMMENT '开户行',
  `bank_card` varchar(20) NOT NULL COMMENT '银行卡密码',
  `bank_people` varchar(10) NOT NULL COMMENT '开户人姓名',
  `add_time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ztp_user_bank
-- ----------------------------

-- ----------------------------
-- Table structure for `ztp_user_money1`
-- ----------------------------
DROP TABLE IF EXISTS `ztp_user_money1`;
CREATE TABLE `ztp_user_money1` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户资金表',
  `type` int(11) NOT NULL COMMENT '资金类型 对应类型表 说明资金类型id 例：1 人民币 ',
  `num` float(20,4) NOT NULL COMMENT '数量 金额 可用',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ztp_user_money1
-- ----------------------------

-- ----------------------------
-- Table structure for `ztp_user_money_type`
-- ----------------------------
DROP TABLE IF EXISTS `ztp_user_money_type`;
CREATE TABLE `ztp_user_money_type` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '资金类型表 记录个人资金的分类',
  `name` varchar(32) NOT NULL COMMENT '名称',
  `mark` varchar(16) NOT NULL,
  `content` text NOT NULL COMMENT '说明',
  `add_time` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ztp_user_money_type
-- ----------------------------

-- ----------------------------
-- Table structure for `ztp_user_note`
-- ----------------------------
DROP TABLE IF EXISTS `ztp_user_note`;
CREATE TABLE `ztp_user_note` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '对应user的附表，存储一些不常用的个人信息',
  `qq` char(10) NOT NULL,
  `weixin` varchar(32) NOT NULL,
  `idcard` varchar(20) NOT NULL COMMENT '身份证号',
  `reg_time` int(11) NOT NULL COMMENT '注册时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ztp_user_note
-- ----------------------------
