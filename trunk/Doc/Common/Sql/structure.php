<?php
/**
 * 应用的数据表结构文件
 * structure.php
 * $Author$
 */
return "
CREATE TABLE IF NOT EXISTS `oa_doc_chunk` (
  `chunk_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `file_id` int(10) unsigned NOT NULL COMMENT '分片组合后文件ID',
  `parent_id` int(10) unsigned NOT NULL COMMENT '父目录ID',
  `file_key` varchar(32) NOT NULL COMMENT '文件唯一标识',
  `file_name` varchar(255) NOT NULL COMMENT '文件ID',
  `file_size` int(10) unsigned NOT NULL COMMENT '分片尺寸',
  `chunk` int(10) unsigned NOT NULL COMMENT '当前分片偏移量（从0开始）',
  `chunk_total` int(10) unsigned NOT NULL COMMENT '分片总数',
  `is_complete` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否已组合成一个文件（1=否；2=是）',
  `domain` char(32) NOT NULL COMMENT '企业域名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态：1=新创建；2=已更新；3=已删除',
  `created` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`chunk_id`),
  KEY `domain` (`domain`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `file_id` (`file_id`) USING BTREE,
  KEY `parent_id` (`parent_id`) USING BTREE,
  KEY `file_key` (`file_key`) USING BTREE,
  KEY `is_complete` (`is_complete`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文件上传记录表';

CREATE TABLE IF NOT EXISTS `oa_doc_file` (
  `file_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文件、文件夹主键ID',
  `parent_id` int(10) unsigned NOT NULL COMMENT '父级文件夹ID',
  `file_name` varchar(255) NOT NULL COMMENT '文件、文件夹名称',
  `file_py` varchar(255) NOT NULL COMMENT '文件、文件夹拼音',
  `file_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '文件类型（1=文件夹；2=文件）',
  `at_id` char(32) NOT NULL COMMENT '附件ID',
  `at_size` int(10) NOT NULL COMMENT '附件尺寸（单位字节）',
  `at_url` varchar(500) NOT NULL COMMENT '附件URL',
  `at_convert_url` varchar(500) NOT NULL COMMENT '附件转换后的Url',
  `is_show` tinyint(1) NOT NULL DEFAULT '2' COMMENT '文件、文件夹是否显示（1=隐藏；2=显示）',
  `is_download` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否允许下载（1=不允许；2=允许）',
  `update_time` bigint(20) NOT NULL COMMENT '最后更新时间',
  `file_status` tinyint(1) NOT NULL COMMENT '转码状态（1=转码中；2=转码完成）',
  `order` int(10) NOT NULL DEFAULT '0' COMMENT '文件排序序号',
  `domain` char(32) NOT NULL COMMENT '企业域名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态：1=新创建；2=已更新；3=已删除',
  `created` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`file_id`),
  KEY `parent_id` (`parent_id`) USING BTREE,
  KEY `file_type` (`file_type`) USING BTREE,
  KEY `is_show` (`is_show`) USING BTREE,
  KEY `file_status` (`file_status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='资料库-资料列表';

CREATE TABLE IF NOT EXISTS `oa_doc_right` (
  `right_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `file_id` int(10) unsigned NOT NULL COMMENT '文件夹ID',
  `right_type` tinyint(1) NOT NULL COMMENT '权限类型（1=查阅权限；2=下载权限）',
  `obj_type` tinyint(1) NOT NULL COMMENT '权限对象类型（1=全公司；2=部门；3=标签；4=人员；5=职位；6=角色）',
  `obj_id` char(32) NOT NULL COMMENT '部门ID、标签ID、人员ID',
  `domain` char(32) NOT NULL COMMENT '企业域名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态（1=新创建；2=已更新；3=已删除）',
  `created` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`right_id`),
  KEY `domain` (`domain`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `file_id` (`file_id`) USING BTREE,
  KEY `obj_type` (`obj_type`) USING BTREE,
  KEY `obj_id` (`obj_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='权限表';

CREATE TABLE IF NOT EXISTS `oa_doc_setting` (
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

CREATE TABLE IF NOT EXISTS `oa_doc_syscache` (
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

CREATE TABLE IF NOT EXISTS `oa_doc_task` (
  `task_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `file_id` int(10) unsigned NOT NULL COMMENT '文件ID',
  `cron_id` char(32) NOT NULL COMMENT 'UC计划任务ID',
  `domain` char(32) NOT NULL COMMENT '企业域名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态：1=新创建；2=已更新；3=已删除',
  `created` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`task_id`),
  KEY `domain` (`domain`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `file_id` (`file_id`) USING BTREE,
  KEY `cron_id` (`cron_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='计划任务表';
";
