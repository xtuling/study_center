<?php
/**
 * 应用的数据表结构文件
 * structure.php
 * $Author$
 */
return "
CREATE TABLE IF NOT EXISTS `oa_course_article` (
  `article_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `data_id` char(32) NOT NULL COMMENT '数据标识',
  `ea_id` char(32) NOT NULL COMMENT '创建人ID',
  `ea_name` varchar(50) NOT NULL COMMENT '创建人姓名',
  `class_id` int(10) unsigned NOT NULL COMMENT '分类ID',
  `cm_id` int(10) unsigned NOT NULL COMMENT '能力模型ID',
  `article_title` varchar(64) NOT NULL COMMENT '课程名称',
  `article_type` tinyint(1) unsigned NOT NULL COMMENT '课程类型（1=单课程；2=系列课程）',
  `source_type` tinyint(1) unsigned NOT NULL COMMENT '素材类型（0=无；1=图文素材；2=音图素材；3=视频素材；4=文件素材；5=外部素材）',
  `cover_id` char(32) NOT NULL COMMENT '封面图片ID',
  `cover_url` varchar(500) NOT NULL COMMENT '封面图片URL',
  `summary` varchar(120) NOT NULL DEFAULT '' COMMENT '摘要',
  `content` text NOT NULL COMMENT '系列课程介绍',
  `source_ids` text NOT NULL COMMENT '素材ID',
  `et_ids` text NOT NULL COMMENT '测评题目ID',
  `tags` varchar(50) NOT NULL DEFAULT '' COMMENT '课程标签',
  `is_secret` tinyint(1) unsigned NOT NULL COMMENT '是否保密（1=不保密；2=保密）',
  `is_share` tinyint(1) unsigned NOT NULL COMMENT '允许分享（1=不允许；2=允许）',
  `is_notice` tinyint(1) unsigned NOT NULL COMMENT '消息通知（1=不开启；2=开启）',
  `is_comment` tinyint(1) unsigned NOT NULL COMMENT '评论功能（1=不开启；2=开启）',
  `is_like` tinyint(1) unsigned NOT NULL COMMENT '点赞功能（1=不开启；2=开启）',
  `is_recommend` tinyint(1) unsigned NOT NULL COMMENT '首页推荐（1=不开启；2=开启）',
  `is_exam` tinyint(1) unsigned NOT NULL COMMENT '是否开启测评（1=未开启；2=已开启）',
  `is_step` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否开启闯关（1=未开启；2=已开启）',
  `study_total` int(10) NOT NULL COMMENT '已学习人数',
  `unstudy_total` int(10) NOT NULL COMMENT '未学习的人数',
  `comment_total` int(10) NOT NULL COMMENT '评论总数',
  `like_total` int(10) NOT NULL COMMENT '点赞总数',
  `update_time` bigint(20) NOT NULL COMMENT '最后更新时间',
  `refresh_time` bigint(20) NOT NULL DEFAULT '0' COMMENT '未学人员总数刷新时间',
  `article_status` tinyint(1) NOT NULL COMMENT '课程状态（1=草稿；2=已发布）',
  `domain` char(32) NOT NULL COMMENT '企业域名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态：1=新创建；2=已更新；3=已删除',
  `created` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`article_id`),
  KEY `domain` (`domain`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `class_id` (`class_id`) USING BTREE,
  KEY `article_title` (`article_title`) USING BTREE,
  KEY `article_type` (`article_type`) USING BTREE,
  KEY `update_time` (`update_time`) USING BTREE,
  KEY `data_id` (`data_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='课程表';

CREATE TABLE IF NOT EXISTS `oa_course_article_source` (
  `article_source_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `article_id` int(10) unsigned NOT NULL COMMENT '课程ID',
  `source_id` int(10) unsigned NOT NULL COMMENT '素材ID',
  `order` int(10) NOT NULL COMMENT '排序',
  `domain` char(32) NOT NULL COMMENT '企业域名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态（1=新创建；2=已更新；3=已删除）',
  `created` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`article_source_id`),
  KEY `article_id` (`article_id`) USING BTREE,
  KEY `source_id` (`source_id`) USING BTREE,
  KEY `domain` (`domain`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='课程素材关系表';

CREATE TABLE IF NOT EXISTS `oa_course_award` (
  `award_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `award_action` varchar(20) NOT NULL COMMENT '激励行为',
  `description` varchar(140) NOT NULL COMMENT '描述',
  `award_type` tinyint(1) NOT NULL COMMENT '激励类型（1=勋章；2=积分）',
  `medal_id` int(10) NOT NULL COMMENT '勋章ID',
  `integral` int(10) NOT NULL COMMENT '积分',
  `article_ids` text NOT NULL COMMENT '选中的课程ID',
  `condition` int(10) NOT NULL COMMENT '勋章发送条件（必须学习课程数量）',
  `domain` char(32) NOT NULL COMMENT '企业域名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态（1=新创建；2=已更新；3=已删除）',
  `created` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`award_id`),
  KEY `domain` (`domain`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `award_action` (`award_action`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='激励类型表';

CREATE TABLE IF NOT EXISTS `oa_course_class` (
  `class_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned NOT NULL COMMENT '上级分类ID',
  `class_name` varchar(20) NOT NULL COMMENT '分类名称',
  `description` varchar(120) NOT NULL COMMENT '分类描述',
  `is_open` tinyint(1) NOT NULL COMMENT '启用分类（1=禁用；2=启用）',
  `order` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `domain` char(32) NOT NULL COMMENT '企业域名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态（1=新创建；2=已更新；3=已删除）',
  `created` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`class_id`),
  KEY `domain` (`domain`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `parent_id` (`parent_id`) USING BTREE,
  KEY `is_open` (`is_open`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='分类表';

CREATE TABLE IF NOT EXISTS `oa_course_exam` (
  `exam_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `article_id` int(10) unsigned NOT NULL COMMENT '课程ID',
  `uid` char(32) NOT NULL COMMENT '用户ID',
  `username` varchar(50) NOT NULL COMMENT '用户姓名',
  `is_pass` tinyint(1) unsigned NOT NULL COMMENT '测评是否通过（1=未通过；2=已通过）',
  `domain` char(32) NOT NULL COMMENT '企业域名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态（1=新创建；2=已更新；3=已删除）',
  `created` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`exam_id`),
  KEY `article_id` (`article_id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `domain` (`domain`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='课程测评结果记录表';

CREATE TABLE IF NOT EXISTS `oa_course_like` (
  `like_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `article_id` int(10) unsigned NOT NULL COMMENT '课程ID',
  `uid` char(32) NOT NULL COMMENT '用户ID',
  `username` varchar(50) NOT NULL COMMENT '用户姓名',
  `domain` char(32) NOT NULL COMMENT '企业域名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态：1=新创建；2=已更新；3=已删除',
  `created` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`like_id`),
  KEY `domain` (`domain`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `article_id` (`article_id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='课程点赞表';

CREATE TABLE IF NOT EXISTS `oa_course_right` (
  `right_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `class_id` int(10) unsigned NOT NULL COMMENT '分类ID',
  `article_id` int(10) unsigned NOT NULL COMMENT '课程ID',
  `award_id` int(10) unsigned NOT NULL COMMENT '激励ID',
  `obj_type` tinyint(1) NOT NULL COMMENT '权限类型（1=全公司；2=部门；3=标签；4=人员；5=职位；6=角色）',
  `obj_id` char(32) NOT NULL COMMENT '部门ID、标签ID、人员ID',
  `domain` char(32) NOT NULL COMMENT '企业域名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态（1=新创建；2=已更新；3=已删除）',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='分类、课程权限表';

CREATE TABLE IF NOT EXISTS `oa_course_setting` (
  `setting_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(50) NOT NULL COMMENT '变量名',
  `value` text NOT NULL COMMENT '变量值',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '缓存类型：0=非数组; 1=数组',
  `comment` text NOT NULL COMMENT '说明',
  `domain` char(32) NOT NULL COMMENT '企业域名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态（1=新创建；2=已更新；3=已删除）',
  `created` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`setting_id`),
  KEY `key` (`key`) USING BTREE,
  KEY `domain` (`domain`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统配置表';

CREATE TABLE IF NOT EXISTS `oa_course_source` (
  `source_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ea_id` char(32) NOT NULL COMMENT '创建人ID',
  `ea_name` varchar(50) NOT NULL COMMENT '创建人姓名',
  `source_key` char(13) NOT NULL COMMENT '素材标识',
  `source_type` tinyint(1) NOT NULL COMMENT '素材类型（1=图文素材；2=音图素材；3=视频素材；4=文件素材；5=外部素材）',
  `source_title` varchar(64) NOT NULL COMMENT '素材标题',
  `author` varchar(50) NOT NULL DEFAULT '' COMMENT '作者',
  `content` text NOT NULL COMMENT '内容描述',
  `audio_imgs` text NOT NULL COMMENT '音图数据',
  `link` varchar(500) NOT NULL DEFAULT '' COMMENT '链接',
  `is_download` tinyint(1) NOT NULL DEFAULT '1' COMMENT '附件是否支持下载（1=不支持；2=支持）',
  `source_status` tinyint(1) NOT NULL DEFAULT '2' COMMENT '素材状态（1=转码中；2=正常）',
  `update_time` bigint(20) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `domain` char(32) NOT NULL COMMENT '企业域名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态（1=新创建；2=已更新；3=已删除）',
  `created` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`source_id`),
  KEY `domain` (`domain`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `source_type` (`source_type`) USING BTREE,
  KEY `source_key` (`source_key`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='素材表';

CREATE TABLE IF NOT EXISTS `oa_course_source_attach` (
  `source_attach_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `source_id` int(10) unsigned NOT NULL COMMENT '素材ID',
  `at_id` char(32) NOT NULL COMMENT '附件ID',
  `at_name` varchar(200) NOT NULL COMMENT '附件名称',
  `at_type` tinyint(1) NOT NULL COMMENT '附件类型（1=视频；2=文件）',
  `at_time` int(10) NOT NULL COMMENT '音频播放时长（毫秒）',
  `at_size` int(10) NOT NULL COMMENT '附件尺寸（单位字节）',
  `at_url` varchar(500) NOT NULL DEFAULT '' COMMENT '附件URL',
  `at_convert_url` varchar(500) NOT NULL DEFAULT '' COMMENT '附件转换后的Url',
  `domain` char(32) NOT NULL COMMENT '企业域名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态（1=新创建；2=已更新；3=已删除）',
  `created` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`source_attach_id`),
  KEY `domain` (`domain`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `source_id` (`source_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='素材附件表';

CREATE TABLE IF NOT EXISTS `oa_course_study` (
  `study_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `article_id` int(10) unsigned NOT NULL COMMENT '课程ID',
  `uid` char(32) NOT NULL COMMENT '用户ID',
  `username` varchar(50) NOT NULL COMMENT '用户姓名',
  `domain` char(32) NOT NULL COMMENT '企业域名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态（1=新创建；2=已更新；3=已删除）',
  `created` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`study_id`),
  KEY `article_id` (`article_id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `domain` (`domain`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='课程已学人员表';

CREATE TABLE IF NOT EXISTS `oa_course_study_record` (
  `study_record_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `article_id` int(10) unsigned NOT NULL COMMENT '课程ID',
  `source_id` int(11) unsigned NOT NULL COMMENT '素材ID',
  `uid` char(32) NOT NULL COMMENT '用户ID',
  `username` varchar(50) NOT NULL COMMENT '用户姓名',
  `domain` char(32) NOT NULL COMMENT '企业域名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态（1=新创建；2=已更新；3=已删除）',
  `created` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`study_record_id`),
  KEY `article_id` (`article_id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `domain` (`domain`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `source_id` (`source_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='课程学习记录表';

CREATE TABLE IF NOT EXISTS `oa_course_syscache` (
  `syscache_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键自增id',
  `name` varchar(50) NOT NULL COMMENT '缓存文件名',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '缓存类型：0=非数组，1=数组',
  `data` mediumblob NOT NULL COMMENT '数据',
  `domain` char(32) NOT NULL COMMENT '企业域名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态（1=新创建；2=已更新；3=已删除）',
  `created` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`syscache_id`),
  KEY `name` (`name`) USING BTREE,
  KEY `domain` (`domain`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统缓存';

CREATE TABLE IF NOT EXISTS `oa_course_task` (
  `task_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `source_id` int(10) unsigned NOT NULL COMMENT '素材ID',
  `cron_id` char(32) NOT NULL COMMENT 'UC计划任务ID',
  `domain` char(32) NOT NULL COMMENT '企业域名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态：1=新创建；2=已更新；3=已删除',
  `created` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`task_id`),
  KEY `domain` (`domain`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `source_id` (`source_id`) USING BTREE,
  KEY `cron_id` (`cron_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='计划任务表';

CREATE TABLE IF NOT EXISTS `oa_course_user` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` char(32) NOT NULL COMMENT '用户ID',
  `username` varchar(50) NOT NULL COMMENT '用户姓名',
  `time_total` int(10) NOT NULL COMMENT '累计学习时长（分钟）',
  `domain` char(32) NOT NULL COMMENT '企业域名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态（1=新创建；2=已更新；3=已删除）',
  `created` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`user_id`),
  KEY `uid` (`uid`) USING BTREE,
  KEY `domain` (`domain`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='人员数据表';

CREATE TABLE IF NOT EXISTS `oa_course_user_award` (
  `user_award_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` char(32) NOT NULL COMMENT '用户ID',
  `username` varchar(50) NOT NULL COMMENT '用户姓名',
  `award_id` int(10) unsigned NOT NULL COMMENT '激励ID',
  `award_action` varchar(20) NOT NULL COMMENT '激励行为',
  `award_type` tinyint(1) NOT NULL COMMENT '激励类型（1=勋章；2=积分）',
  `medal_id` int(10) NOT NULL COMMENT '勋章ID',
  `integral` int(10) NOT NULL COMMENT '积分',
  `article_ids` text NOT NULL COMMENT '选中的课程ID',
  `domain` char(32) NOT NULL COMMENT '企业域名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态（1=新创建；2=已更新；3=已删除）',
  `created` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`user_award_id`),
  KEY `uid` (`uid`) USING BTREE,
  KEY `award_id` (`award_id`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `domain` (`domain`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='人员激励数据表';
";
