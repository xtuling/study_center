<?php
/**
 * 应用的数据表结构文件
 * structure.php
 * $Author$
 */
return "
CREATE TABLE  IF NOT EXISTS `oa_workmate_setting` (
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
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COMMENT='同事圈 - 设置表';

CREATE TABLE  IF NOT EXISTS `oa_workmate_syscache` (
  `name` VARCHAR(32) NOT NULL COMMENT '缓存文件名',
  `domain` VARCHAR(120) NOT NULL DEFAULT '' COMMENT '企业域名',
  `type` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `data` MEDIUMBLOB NOT NULL COMMENT '数据',
  `status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `created` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间'
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COMMENT='同事圈 - 缓存表';

CREATE TABLE IF NOT EXISTS `oa_workmate_circle` (
  `id` INT(10) NOT NULL AUTO_INCREMENT,
  `pid` INT(10) NOT NULL DEFAULT '0' COMMENT '评论的帖子ID',
  `uid` CHAR(32) NOT NULL DEFAULT '' COMMENT '用户ID',
  `username` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '用户姓名',
  `content` TEXT NOT NULL COMMENT '帖子内容',
  `is_attach` SMALLINT(2) NOT NULL DEFAULT '0' COMMENT '是否含有附件图片（0=无，1=有）',
  `audit_state` SMALLINT(2) NOT NULL DEFAULT '0' COMMENT '审核状态（0=待审核，1=已通过，2=已驳回）',
  `audit_type` SMALLINT(2) NOT NULL DEFAULT '2' COMMENT '审核类型（1=系统审核，2=后台审核）',
  `audit_time` BIGINT(13) NOT NULL DEFAULT '0' COMMENT '审核时间',
  `audit_uid` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '审核人ID',
  `audit_uname` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '审核人姓名',
  `domain` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `domain_status` (`domain`,`status`)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COMMENT='同事圈帖子信息表' AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `oa_workmate_like` (
  `like_id` INT(10) NOT NULL AUTO_INCREMENT,
  `uid` CHAR(32) NOT NULL DEFAULT '' COMMENT '点赞人ID',
  `cid` INT(10) NOT NULL DEFAULT '0' COMMENT '帖子、评论ID',
  `domain` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`like_id`),
  KEY `domain_status` (`domain`,`status`)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COMMENT='同事圈帖子、回复点赞表' AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `oa_workmate_attachment` (
  `aid` INT(10) NOT NULL AUTO_INCREMENT,
  `cid` INT(10) NOT NULL DEFAULT '0' COMMENT '帖子ID',
  `atid` CHAR(32) NOT NULL DEFAULT '' COMMENT '图片ID',
  `domain` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`aid`),
  KEY `domain_status` (`domain`,`status`)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COMMENT='同事圈帖子附件表' AUTO_INCREMENT=1 ;

";
