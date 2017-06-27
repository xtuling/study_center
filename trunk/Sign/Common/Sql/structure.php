<?php
/**
 * 应用的数据表结构文件
 * structure.php
 * $Author$
 */
return "
CREATE TABLE IF NOT EXISTS `oa_sign_config` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增 ID',
  `cycle` SMALLINT(6) NOT NULL DEFAULT '0' COMMENT '签到循环周期（天）',
  `integral_rules` TEXT NOT NULL COMMENT '积分规则',
  `rules_updated` BIGINT(13) NOT NULL DEFAULT '0' COMMENT '积分规则变更时间',
  `sign_rules` TEXT NOT NULL COMMENT '签到规则',
  `domain` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `domain_status` (`domain`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='签到 - 签到配置表';

CREATE TABLE IF NOT EXISTS `oa_sign_record` (
  `rid` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '签到ID',
  `uid` CHAR(32) NOT NULL DEFAULT '' COMMENT '签到人UID',
  `username` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '签到人姓名',
  `sign_integral` INT(10) NOT NULL DEFAULT '0' COMMENT '签到获得积分数',
  `domain` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`rid`),
  KEY `uid` (`uid`),
  KEY `domain_status` (`domain`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='签到 - 签到记录表';

CREATE TABLE IF NOT EXISTS `oa_sign_count` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增 ID',
  `uid` CHAR(32) NOT NULL DEFAULT '' COMMENT '签到人UID',
  `username` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '签到人姓名',
  `last_time` BIGINT(13) NOT NULL DEFAULT '0' COMMENT '最后签到时间',
  `continuous` SMALLINT(6) NOT NULL DEFAULT '0' COMMENT '连续签到次数（周期内）',
  `sign_nums` INT(10) NOT NULL DEFAULT '0' COMMENT '签到总次数',
  `integrals` INT(10) NOT NULL DEFAULT '0' COMMENT '签到获得总积分',
  `domain` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `domain_status` (`domain`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='签到 - 统计表';

CREATE TABLE IF NOT EXISTS `oa_sign_setting` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增 ID',
  `key` VARCHAR(50) NOT NULL COMMENT '变量名',
  `value` TEXT NOT NULL COMMENT '值',
  `type` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '缓存类型:0=非数组; 1=数组',
  `comment` TEXT NOT NULL COMMENT '说明',
  `domain` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态:1=新创建; 2=已更新; 3=已删除',
  `created` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `domain_status` (`domain`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='签到 - 设置表';

CREATE TABLE  IF NOT EXISTS `oa_sign_syscache` (
  `name` VARCHAR(32) NOT NULL COMMENT '缓存文件名',
  `domain` VARCHAR(120) NOT NULL DEFAULT '' COMMENT '企业域名',
  `type` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `data` MEDIUMBLOB NOT NULL COMMENT '数据',
  `status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `created` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='签到 - 缓存表';

";
