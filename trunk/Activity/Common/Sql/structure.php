<?php
/**
 * 应用的数据表结构文件
 * structure.php
 * $Author$
 */
return "
CREATE TABLE IF NOT EXISTS `oa_activity_activity` (
  `ac_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `subject` VARCHAR(120) NOT NULL COMMENT '活动主题',
  `source` VARCHAR(50) NOT NULL COMMENT '作者与来源',
  `cover_id` CHAR(32) NOT NULL DEFAULT '' COMMENT '封面图片 ID',
  `begin_time` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '活动开始时间',
  `end_time` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '活动结束时间',
  `content` TEXT NOT NULL DEFAULT '' COMMENT '活动描述',
  `is_all` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '参与范围是否全公司：0(默认)=否；1=是',
  `is_notice` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否发送消息通知：0(默认)=否；1=是',
  `is_recomend` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否推荐到首页：0(默认)=否；1=是',
  `activity_status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '活动状态，0(默认)=草稿，1=发布，2=提前终止',
  `likes` INT(11) NOT NULL DEFAULT '0' COMMENT '点赞总数量',
  `comments` INT(11) NOT NULL DEFAULT '0' COMMENT '评论总数量',
  `publish_time` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '发布时间',
  `last_time` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新活动时间',
  `domain` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`ac_id`),
  KEY `domain_status` (`domain`,`status`,`activity_status`)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COMMENT='活动-主表';
CREATE TABLE IF NOT EXISTS `oa_activity_right` (
  `right_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `ac_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '活动ID',
  `uid` CHAR(32) NOT NULL DEFAULT '' COMMENT '人员 ID',
  `dp_id` CHAR(32) NOT NULL DEFAULT '' COMMENT '部门 ID',
  `tag_id` CHAR(32) NOT NULL DEFAULT '' COMMENT '标签 ID',
  `job_id` CHAR(32) NOT NULL DEFAULT '' COMMENT '岗位 ID',
  `role_id` CHAR(32) NOT NULL DEFAULT '' COMMENT '角色 ID',
  `domain` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`right_id`),
  KEY `domain_status` (`domain`,`status`)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COMMENT='活动-权限表';
CREATE TABLE IF NOT EXISTS `oa_activity_comment` (
  `comment_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `ac_id` INT(10) NOT NULL DEFAULT '0' COMMENT '活动ID',
  `parent_id` INT(10) NOT NULL DEFAULT '0' COMMENT '评论父ID',
  `uid` CHAR(32) NOT NULL DEFAULT '' COMMENT '用户ID',
  `content` TEXT NOT NULL COMMENT '评论内容',
  `is_attach` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否含有附件图片（0=无，1=有）',
  `likes` INT(11) NOT NULL DEFAULT '0' COMMENT '点赞总数量',
  `replys` INT(11) NOT NULL DEFAULT '0' COMMENT '回复总数量',
  `domain` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`comment_id`),
  KEY `parent_id` (`parent_id`),
  KEY `domain_status` (`domain`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='活动-评论表';
CREATE TABLE IF NOT EXISTS `oa_activity_like` (
  `like_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` CHAR(32) NOT NULL DEFAULT '' COMMENT '点赞人ID',
  `cid` INT(10) NOT NULL DEFAULT '0' COMMENT '活动、评论ID',
  `type` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '点赞类型：1：活动、2：评论',
  `domain` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`like_id`),
  KEY `cid` (`cid`),
  KEY `domain_status` (`domain`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='活动-点赞表';
CREATE TABLE IF NOT EXISTS `oa_activity_attachment` (
  `attach_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `cid` INT(10) NOT NULL DEFAULT '0' COMMENT '评论ID',
  `at_id` CHAR(32) NOT NULL DEFAULT '' COMMENT 'UC返回的图片ID',
  `domain` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` TINYINT(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`attach_id`),
  KEY `cid` (`cid`),
  KEY `domain_status` (`domain`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='活动-评论附件表';
CREATE TABLE  IF NOT EXISTS `oa_activity_syscache` (
  `name` VARCHAR(32) NOT NULL COMMENT '缓存文件名',
  `domain` VARCHAR(120) NOT NULL DEFAULT '' COMMENT '企业域名',
  `type` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `data` MEDIUMBLOB NOT NULL COMMENT '数据',
  `status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `created` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间'
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COMMENT='活动-缓存表';
CREATE TABLE  IF NOT EXISTS `oa_activity_setting` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增 ID',
  `key` VARCHAR(50) NOT NULL COMMENT '变量名',
  `value` TEXT NOT NULL COMMENT '值',
  `type` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `comment` TEXT NOT NULL COMMENT '说明',
  `domain` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `domain_status` (`domain`,`status`)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COMMENT='活动-设置表';

";
