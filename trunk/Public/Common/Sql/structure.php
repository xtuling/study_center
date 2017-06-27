<?php
/**
 * 应用的数据表结构文件
 * structure.php
 * $Author$
 */
return "
CREATE TABLE IF NOT EXISTS `oa_common_chooselog` (
  `cid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增 ID',
  `choose_type` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '选择类型：1=人员；2=部门；3=标签',
  `chooseId` varchar(32) NOT NULL DEFAULT '' COMMENT '所选择的ID',
  `eaId` varchar(32) NOT NULL DEFAULT '' COMMENT '管理员ID',
  `domain` varchar(20) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`cid`),
  KEY `domain_status` (`domain`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='公共表-选人记录';

CREATE TABLE `oa_common_hidemenu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增 ID',
  `menus` text COMMENT '菜单，序列化的数组',
  `domain` varchar(32) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `domain_status` (`domain`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='公共表 - 定制化企业需要隐藏的菜单';

CREATE TABLE `oa_common_setting` (
  `setting_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `key` varchar(50) CHARACTER SET utf8 NOT NULL COMMENT '变量名',
  `value` text CHARACTER SET utf8 NOT NULL COMMENT '值',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型:0=非数组; 1=数组',
  `comment` text CHARACTER SET utf8 NOT NULL COMMENT '变量说明',
  `domain` char(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态:1=新创建; 2=已更新; 3=已删除',
  `created` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`setting_id`),
  KEY `key` (`key`),
  KEY `domain_status` (`domain`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='公共表 - 设置表';

CREATE TABLE `oa_common_recommender` (
  `recommender_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增 ID',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '数据类型，1=banner；2=icon；3=内容推荐',
  `displayorder` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '显示顺序，默认：1',
  `hide` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否隐藏，1=显示；2=隐藏',
  `system` tinyint(1) unsigned NOT NULL DEFAULT '2' COMMENT '是否系统内置，1=是；2=否',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `attach_id` char(32) NOT NULL DEFAULT '' COMMENT '图片附件 ID',
  `pic` varchar(255) NOT NULL DEFAULT '' COMMENT '图片 URL',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '链接',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `app_dir` varchar(64) NOT NULL DEFAULT '' COMMENT '应用目录名 APP_DIR',
  `app_identifier` varchar(64) NOT NULL DEFAULT '' COMMENT '应用唯一标识符',
  `data_id` varchar(64) NOT NULL DEFAULT '' COMMENT '数据 ID',
  `data_category_id` varchar(32) NOT NULL DEFAULT '' COMMENT '数据所属分类 ID',
  `data` text NOT NULL COMMENT '推送原生数据，自定义添加类型可能为空',
  `dateline` bigint(12) unsigned NOT NULL DEFAULT '0' COMMENT '数据推送时间',
  `adminer_id` char(32) NOT NULL DEFAULT '' COMMENT '操作者 ID',
  `adminer` varchar(64) NOT NULL DEFAULT '' COMMENT '操作者名字',
  `domain` char(50) NOT NULL DEFAULT '' COMMENT '域名',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态，1=新建；2=已更新；3=已删除',
  `created` bigint(12) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(12) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(12) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`recommender_id`),
  KEY `domain_status` (`domain`,`status`),
  KEY `hide_displayorder` (`hide`,`displayorder`),
  KEY `type_app_dir_data_category_id_data_id` (`type`,`app_dir`,`data_category_id`,`data_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='公共表 - 推荐表';

CREATE TABLE `oa_common_collection` (
  `collection_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增 ID',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `cover_id` CHAR (32) NOT NULL DEFAULT '' COMMENT '封面图附件ID',
  `cover_url` VARCHAR (255) NOT NULL DEFAULT '' COMMENT '封面图附件地址',
  `cover_type` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '封面图类型（0：无封面，1：图片，2：音频，3：视频）',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '链接',
  `app_dir` varchar(64) NOT NULL DEFAULT '' COMMENT '应用目录名 APP_DIR',
  `app_identifier` varchar(64) NOT NULL DEFAULT '' COMMENT '应用唯一标识符',
  `data_id` varchar(64) NOT NULL DEFAULT '' COMMENT '数据 ID',
  `data` text NOT NULL COMMENT '序列化数据，收藏标题之外的字段存储（文件：file_type，file_size，is_dir；同事圈：circle_uid，circle_face，circle_name）',
  `uid` CHAR (32) NOT NULL DEFAULT '' COMMENT '收藏者uid',
  `c_time` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '收藏时间',
  `c_deleted` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否已删除（0：未删除，1：已删除）',
  `domain` char(50) NOT NULL DEFAULT '' COMMENT '域名',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态，1=新建；2=已更新；3=已删除',
  `created` bigint(12) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(12) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(12) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`collection_id`),
  KEY `domain_status` (`domain`,`status`),
  KEY `uid_app_dir_data_id` (`uid`,`app_dir`,`data_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='公共表 - 收藏表';

";
