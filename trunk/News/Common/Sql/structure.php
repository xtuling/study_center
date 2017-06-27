<?php
/**
 * 应用的数据表结构文件
 * structure.php
 * $Author$
 */
return "
CREATE TABLE IF NOT EXISTS `oa_news_article` (
  `article_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `data_id` char(32) NOT NULL COMMENT '数据标识',
  `class_id` int(10) unsigned NOT NULL COMMENT '分类ID',
  `class_name` varchar(50) NOT NULL COMMENT '分类名称',
  `title` varchar(64) NOT NULL COMMENT '标题',
  `ea_id` varchar(32) NOT NULL COMMENT '管理员ID',
  `ea_name` varchar(50) NOT NULL COMMENT '管理员姓名',
  `author` varchar(50) NOT NULL COMMENT '作者',
  `content` text NOT NULL COMMENT '新闻内容',
  `summary` varchar(120) NOT NULL COMMENT '摘要',
  `cover_id` varchar(32) NOT NULL COMMENT '封面图片ID',
  `cover_url` varchar(500) NOT NULL COMMENT '封面图片URL',
  `is_show_cover` tinyint(1) NOT NULL DEFAULT '2' COMMENT '是否正文显示封面图片（1=不显示，2=显示）',
  `link` varchar(500) NOT NULL DEFAULT '' COMMENT '外部链接',
  `is_jump` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否直接跳转外链（1=不直接跳转，2=直接跳转）',
  `is_download` tinyint(1) NOT NULL DEFAULT '1' COMMENT '附件是否支持下载（1=不支持，2=支持）',
  `is_secret` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否保密（1=不保密，2=保密）',
  `is_share` tinyint(1) NOT NULL DEFAULT '1' COMMENT '允许分享（1=不允许，2=允许）',
  `is_notice` tinyint(1) NOT NULL DEFAULT '2' COMMENT '消息通知（1=不开启，2=开启）',
  `is_comment` tinyint(1) NOT NULL DEFAULT '2' COMMENT '评论功能（1=不开启，2=开启）',
  `is_like` tinyint(1) NOT NULL DEFAULT '2' COMMENT '点赞功能（1=不开启，2=开启）',
  `is_recommend` tinyint(1) NOT NULL DEFAULT '2' COMMENT '首页推荐（1=不开启，2=开启）',
  `send_time` bigint(20) NOT NULL DEFAULT '0' COMMENT '发送时间',
  `top_time` bigint(20) NOT NULL DEFAULT '0' COMMENT '置顶时间（0为未置顶）',
  `update_time` bigint(20) NOT NULL DEFAULT '0' COMMENT '未读人数更新时间',
  `read_total` int(10) NOT NULL DEFAULT '0' COMMENT '已阅读总数',
  `unread_total` int(10) NOT NULL DEFAULT '0' COMMENT '未阅读总数',
  `comment_total` int(10) NOT NULL DEFAULT '0' COMMENT '评论总数',
  `like_total` int(10) NOT NULL DEFAULT '0' COMMENT '点赞数总数',
  `news_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '新闻状态（1=草稿，2=已发布，3=预发布）',
  `domain` varchar(32) NOT NULL COMMENT '企业域名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态：1=新创建；2=已更新；3=已删除',
  `created` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`article_id`),
  KEY `domain` (`domain`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `class_id` (`class_id`) USING BTREE,
  KEY `send_time` (`send_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='新闻';

CREATE TABLE IF NOT EXISTS `oa_news_attach` (
  `attach_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `article_id` int(10) unsigned NOT NULL COMMENT '新闻ID',
  `at_id` varchar(32) NOT NULL COMMENT '附件ID',
  `at_name` varchar(200) NOT NULL COMMENT '附件名称',
  `at_type` tinyint(1) NOT NULL COMMENT '附件类型（1=视频；2=音频；3=其它）',
  `at_time` int(10) NOT NULL COMMENT '音、视频播放时长（毫秒）',
  `at_size` int(10) NOT NULL COMMENT '附件尺寸（单位字节）',
  `at_url` varchar(500) NOT NULL DEFAULT '' COMMENT '附件URL',
  `at_convert_url` varchar(500) NOT NULL DEFAULT '' COMMENT '附件转换后的Url',
  `domain` varchar(32) NOT NULL COMMENT '企业域名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态：1=新创建；2=已更新；3=已删除',
  `created` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`attach_id`),
  KEY `domain` (`domain`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `article_id` (`article_id`) USING BTREE,
  KEY `at_id` (`at_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='附件';

CREATE TABLE IF NOT EXISTS `oa_news_class` (
  `class_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned NOT NULL COMMENT '上级分类ID',
  `class_name` varchar(20) NOT NULL COMMENT '分类名称',
  `description` varchar(120) NOT NULL COMMENT '分类描述',
  `is_open` tinyint(1) NOT NULL COMMENT '启用分类（1=禁用，2=启用）',
  `order` int(10) NOT NULL DEFAULT '9999' COMMENT '排序',
  `domain` varchar(32) NOT NULL COMMENT '企业域名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态：1=新创建；2=已更新；3=已删除',
  `created` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`class_id`),
  KEY `domain` (`domain`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `parent_id` (`parent_id`) USING BTREE,
  KEY `is_open` (`is_open`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='新闻分类';

CREATE TABLE IF NOT EXISTS `oa_news_like` (
  `like_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `article_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '新闻ID',
  `uid` varchar(32) NOT NULL COMMENT '用户ID',
  `username` varchar(50) NOT NULL COMMENT '用户姓名',
  `domain` varchar(32) NOT NULL COMMENT '企业域名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态：1=新创建；2=已更新；3=已删除',
  `created` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`like_id`),
  KEY `domain` (`domain`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `article_id` (`article_id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='新闻点赞';

CREATE TABLE IF NOT EXISTS `oa_news_read` (
  `read_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `article_id` int(10) unsigned NOT NULL COMMENT '新闻ID',
  `uid` varchar(32) NOT NULL COMMENT '用户ID',
  `username` varchar(50) NOT NULL COMMENT '用户姓名',
  `dp_name` text NOT NULL COMMENT '所属部门',
  `job` varchar(50) NOT NULL COMMENT '职位',
  `mobile` varchar(11) NOT NULL COMMENT '手机',
  `domain` varchar(32) NOT NULL COMMENT '企业域名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态：1=新创建；2=已更新；3=已删除',
  `created` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`read_id`),
  KEY `domain` (`domain`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `article_id` (`article_id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='新闻阅读';

CREATE TABLE IF NOT EXISTS `oa_news_right` (
  `right_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `class_id` int(10) unsigned NOT NULL COMMENT '分类ID',
  `article_id` int(10) unsigned NOT NULL COMMENT '新闻ID',
  `obj_type` tinyint(1) NOT NULL COMMENT '权限类型（1=全公司；2=部门；3=标签；4=人员；5=职位；6=角色）',
  `obj_id` varchar(32) NOT NULL COMMENT '部门ID、标签ID、人员ID',
  `domain` varchar(32) NOT NULL COMMENT '企业域名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态：1=新创建；2=已更新；3=已删除',
  `created` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`right_id`),
  KEY `domain` (`domain`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `class_id` (`class_id`) USING BTREE,
  KEY `article_id` (`article_id`) USING BTREE,
  KEY `obj_type` (`obj_type`) USING BTREE,
  KEY `obj_id` (`obj_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='权限';

CREATE TABLE IF NOT EXISTS `oa_news_setting` (
  `setting_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(50) NOT NULL COMMENT '变量名',
  `value` text NOT NULL COMMENT '变量值',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '缓存类型：0=非数组; 1=数组',
  `comment` text NOT NULL COMMENT '说明',
  `domain` varchar(32) NOT NULL COMMENT '企业域名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态：1=新创建；2=已更新；3=已删除',
  `created` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`setting_id`),
  KEY `key` (`key`) USING BTREE,
  KEY `domain` (`domain`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统配置';

CREATE TABLE IF NOT EXISTS `oa_news_syscache` (
  `syscache_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键自增id',
  `name` varchar(50) NOT NULL COMMENT '缓存文件名',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '缓存类型：0=非数组，1=数组',
  `data` mediumblob NOT NULL COMMENT '数据',
  `domain` varchar(32) NOT NULL COMMENT '企业域名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态：1=新创建；2=已更新；3=已删除',
  `created` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`syscache_id`),
  KEY `name` (`name`) USING BTREE,
  KEY `domain` (`domain`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统缓存';

CREATE TABLE IF NOT EXISTS `oa_news_task` (
  `task_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `article_id` int(10) unsigned NOT NULL COMMENT '新闻ID',
  `cron_id` varchar(32) NOT NULL COMMENT 'UC计划任务ID',
  `domain` varchar(32) NOT NULL COMMENT '企业域名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态：1=新创建；2=已更新；3=已删除',
  `created` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`task_id`),
  KEY `domain` (`domain`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `article_id` (`article_id`) USING BTREE,
  KEY `cron_id` (`cron_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='新闻阅读';
";
