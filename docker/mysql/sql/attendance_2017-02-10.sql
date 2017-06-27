# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 127.0.0.1 (MySQL 5.7.17)
# Database: attendance
# Generation Time: 2017-02-10 03:46:24 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table oa_attendance_askoff
# ------------------------------------------------------------

DROP TABLE IF EXISTS `oa_attendance_askoff`;

CREATE TABLE `oa_attendance_askoff` (
  `a_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `at_id` int(10) NOT NULL DEFAULT '0' COMMENT '请假类型ID',
  `number` char(32) NOT NULL COMMENT '审批编号',
  `logo` char(10) NOT NULL COMMENT '图标',
  `mem_uid` char(32) NOT NULL COMMENT '发起人ID',
  `title` char(255) NOT NULL COMMENT '请假标题',
  `dp_id` char(32) NOT NULL COMMENT '发起人部门ID',
  `start_time` bigint(13) NOT NULL DEFAULT '0' COMMENT '开始时间',
  `end_time` bigint(13) NOT NULL DEFAULT '0' COMMENT '结束时间',
  `leave_time` bigint(13) NOT NULL DEFAULT '0' COMMENT '请假时长',
  `desc` char(255) NOT NULL DEFAULT '' COMMENT '请假说明',
  `picture` text NOT NULL COMMENT '图片ID 逗号相隔',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '审批类型 1: 自由 2: 固定',
  `level` tinyint(1) NOT NULL DEFAULT '0' COMMENT '固定审批流程当前层级 当type为固定(2)时有用',
  `countersign` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否会签 1: 是 2: 否',
  `approver` text NOT NULL COMMENT '审批人',
  `cc_persons` text NOT NULL COMMENT '抄送人',
  `approve_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '审批状态 1: 审批中 2: 已通过 3: 已驳回 4: 已撤销',
  `is_abnormal` tinyint(1) NOT NULL DEFAULT '2' COMMENT '是否异常 1: 是 2: 否',
  `domain` char(50) NOT NULL DEFAULT '' COMMENT '企业标识',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态 1: 初始化 2: 已更新 3: 已删除',
  `created` bigint(13) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(13) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(13) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`a_id`),
  KEY `domain` (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='请假主表';



# Dump of table oa_attendance_askoff_abnormal
# ------------------------------------------------------------

DROP TABLE IF EXISTS `oa_attendance_askoff_abnormal`;

CREATE TABLE `oa_attendance_askoff_abnormal` (
  `aa_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `at_id` int(10) NOT NULL COMMENT '请假类型ID',
  `dp_id` char(32) NOT NULL COMMENT '部门ID',
  `domain` char(50) NOT NULL DEFAULT '' COMMENT '企业标识',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态 1: 初始化 2: 已更新 3: 已删除',
  `created` bigint(13) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(13) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(13) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`aa_id`),
  KEY `at_id` (`at_id`),
  KEY `aa_id` (`aa_id`),
  KEY `domain` (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='请假类型部门相关异常表';



# Dump of table oa_attendance_askoff_process
# ------------------------------------------------------------

DROP TABLE IF EXISTS `oa_attendance_askoff_process`;

CREATE TABLE `oa_attendance_askoff_process` (
  `ap_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `a_id` int(10) NOT NULL COMMENT '审批主表ID',
  `mem_uid` char(32) NOT NULL COMMENT '操作人ID',
  `type` tinyint(1) NOT NULL COMMENT '操作类型 1: 同意 2: 驳回 3: 转审 4: 催办 5: 撤销 6: 待审批 7: 抄送 8: 无需处理',
  `forward_to` char(32) NOT NULL DEFAULT '' COMMENT '转审批对象 type 为转审(3) 时才有值',
  `level` tinyint(1) NOT NULL DEFAULT '0' COMMENT '固定审批流程当前层级 当固定流程时才有用',
  `mark` text NOT NULL COMMENT '备注',
  `domain` char(50) NOT NULL DEFAULT '' COMMENT '企业标识',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态 1: 初始化 2: 已更新 3: 已删除',
  `created` bigint(13) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(13) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(13) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`ap_id`),
  KEY `domain` (`domain`),
  KEY `a_id` (`a_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='请假申请进度表';



# Dump of table oa_attendance_askoff_type
# ------------------------------------------------------------

DROP TABLE IF EXISTS `oa_attendance_askoff_type`;

CREATE TABLE `oa_attendance_askoff_type` (
  `at_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` char(10) NOT NULL COMMENT '类型名称',
  `logo` char(128) NOT NULL COMMENT '图标',
  `open` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用 1: 启用 2: 禁用',
  `visible_range` tinyint(1) NOT NULL DEFAULT '1' COMMENT '可见范围 1: 全公司 2: 指定对象',
  `dp_id` text NOT NULL COMMENT '部门ID 逗号相隔',
  `mem_uid` text NOT NULL COMMENT '人员ID 逗号相隔',
  `tag_id` text NOT NULL COMMENT '标签ID 逗号相隔',
  `picture` tinyint(1) NOT NULL DEFAULT '2' COMMENT '是否必须上传照片 1: 是 2: 否',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '自由或者固定流程 1: 自由 2: 固定 3: 补签',
  `countersign` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否会签 1: 是 2: 否',
  `sub_cond_approver` tinyint(1) NOT NULL DEFAULT '2' COMMENT '是否分条件设置审批人 1: 是 2: 否',
  `cond_data` text NOT NULL COMMENT '条件设置审批人条件数据',
  `approver` text NOT NULL COMMENT '审批人',
  `cc_persons` text NOT NULL COMMENT '抄送人',
  `sys_type` char(20) NOT NULL DEFAULT '' COMMENT '系统预设类型 通常为空',
  `domain` char(50) NOT NULL DEFAULT '' COMMENT '企业标识',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态 1: 初始化 2: 已更新 3: 已删除',
  `created` bigint(13) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(13) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(13) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`at_id`),
  KEY `domain` (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='请假类型';



# Dump of table oa_attendance_setting
# ------------------------------------------------------------

DROP TABLE IF EXISTS `oa_attendance_setting`;

CREATE TABLE `oa_attendance_setting` (
  `key` varchar(50) CHARACTER SET utf8 NOT NULL COMMENT '变量名',
  `domain` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '企业域名',
  `value` text CHARACTER SET utf8 NOT NULL COMMENT '值',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型:0=非数组; 1=数组',
  `comment` text CHARACTER SET utf8 NOT NULL COMMENT '说明',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态:1=新创建; 2=已更新; 3=已删除',
  `created` bigint(13) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(13) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(13) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='培训 - 设置表';



# Dump of table oa_attendance_syscache
# ------------------------------------------------------------

DROP TABLE IF EXISTS `oa_attendance_syscache`;

CREATE TABLE `oa_attendance_syscache` (
  `name` varchar(32) CHARACTER SET utf8 NOT NULL COMMENT '缓存文件名',
  `domain` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '企业域名',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `data` mediumblob NOT NULL COMMENT '数据',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `created` bigint(13) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(13) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(13) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
