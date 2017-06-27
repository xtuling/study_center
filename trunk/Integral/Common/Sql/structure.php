<?php
/**
 * 应用的数据表结构文件
 * structure.php
 * $Author$
 */
return "
CREATE TABLE IF NOT EXISTS `oa_integral_convert` (
  `ic_id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` char(32) NOT NULL COMMENT '兑换人员ID',
  `operator` char(32) NOT NULL DEFAULT '' COMMENT '操作人ID',
  `ia_id` int(10) NOT NULL COMMENT '奖品ID',
  `ucintegral_id` char(32) NOT NULL DEFAULT '' COMMENT 'UC积分操作ID',
  `integral` int(10) NOT NULL DEFAULT '0' COMMENT '所需积分',
  `number` varchar(32) NOT NULL COMMENT '兑换编号',
  `convert_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '兑换状态, 1:待处理 2:已同意 3:已拒绝 4:已取消',
  `applicant_phone` char(11) NOT NULL DEFAULT '' COMMENT '申请人手机号',
  `applicant_email` varchar(120) NOT NULL DEFAULT '' COMMENT '申请人邮箱',
  `applicant_mark` char(60) NOT NULL DEFAULT '' COMMENT '申请人备注',
  `domain` varchar(120) NOT NULL COMMENT '企业标识',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态: 1.初始化 2.已更新 3.已删除',
  `created` bigint(13) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(13) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(13) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`ic_id`),
  KEY `domain` (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='奖品兑换记录';


CREATE TABLE IF NOT EXISTS `oa_integral_convert_process` (
  `icp_id` int(10) NOT NULL AUTO_INCREMENT,
  `ic_id` int(10) NOT NULL COMMENT '申请兑换记录表主键',
  `uid` char(32) NOT NULL DEFAULT '' COMMENT '人员ID',
  `operator` char(32) NOT NULL DEFAULT '' COMMENT '操作人ID',
  `operate` tinyint(1) NOT NULL DEFAULT '1' COMMENT '操作标识 1:待处理 2:已同意 3:已拒绝 4:已取消',
  `operating_time` bigint(13) NOT NULL COMMENT '操作时间',
  `integral` int(10) NOT NULL DEFAULT '0' COMMENT '积分改动情况',
  `mark` varchar(120) NOT NULL COMMENT '备注',
  `domain` varchar(120) NOT NULL COMMENT '企业标识',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态: 1.初始化 2.已更新 3.已删除',
  `created` bigint(13) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(13) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(13) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`icp_id`),
  KEY `domain` (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='申请进程表';


CREATE TABLE IF NOT EXISTS `oa_integral_medal` (
  `im_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `icon` varchar(32) NOT NULL DEFAULT '' COMMENT '图标',
  `icon_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '图标类型 1: 用户上传 2: 系统图标',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '名称',
  `desc` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `domain` varchar(120) NOT NULL DEFAULT '' COMMENT '企业标识',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态: 1.初始化 2.已更新 3.已删除',
  `created` bigint(13) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(13) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(13) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`im_id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='勋章表';


CREATE TABLE IF NOT EXISTS `oa_integral_medal_log` (
  `ml_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `im_id` int(10) NOT NULL COMMENT '勋章ID',
  `mem_uid` char(32) NOT NULL COMMENT '人员ID',
  `get_status` tinyint(1) NOT NULL DEFAULT '2' COMMENT '获得状态 1: 获得 2: 申请 3: 失败',
  `domain` varchar(120) NOT NULL DEFAULT '' COMMENT '企业标识',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态: 1.初始化 2.已更新 3.已删除',
  `created` bigint(13) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(13) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(13) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`ml_id`),
  KEY `im_id` (`im_id`),
  KEY `mem_uid` (`mem_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='勋章获得日志表';


CREATE TABLE IF NOT EXISTS `oa_integral_member_medal` (
  `imm_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `im_id` int(10) unsigned NOT NULL COMMENT '勋章ID',
  `mem_uid` char(32) NOT NULL DEFAULT '' COMMENT '用户ID',
  `mem_username` char(54) NOT NULL DEFAULT '' COMMENT '用户姓名',
  `im_total` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '勋章获得总数',
  `domain` char(32) NOT NULL DEFAULT '' COMMENT '企业标识',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态: 1.初始化 2.已更新 3.已删除',
  `created` bigint(13) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(13) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(13) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`imm_id`),
  KEY `im_id` (`im_id`),
  KEY `mem_uid` (`mem_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户勋章表';


CREATE TABLE IF NOT EXISTS `oa_integral_prize` (
  `ia_id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL DEFAULT '' COMMENT '奖品名称',
  `sequence` smallint(5) NOT NULL DEFAULT '1' COMMENT '序号',
  `reserve` int(10) NOT NULL DEFAULT '0' COMMENT '库存',
  `on_sale` tinyint(1) NOT NULL DEFAULT '2' COMMENT '上架状态, 1:已上架 2:已下架',
  `integral` int(10) NOT NULL DEFAULT '0' COMMENT '所需积分',
  `range_mem` text NOT NULL COMMENT '兑换范围:人员 (逗号间隔)',
  `range_dep` text NOT NULL COMMENT '兑换范围:部门 (逗号间隔)',
  `is_all` tinyint(1) NOT NULL DEFAULT '1' COMMENT '兑换范围是否全公司, 1:是 2:否',
  `times` smallint(5) NOT NULL DEFAULT '-1' COMMENT '兑换次数, -1 为不限制',
  `picture` text NOT NULL COMMENT '图片ID (逗号间隔)',
  `desc` text NOT NULL COMMENT '奖品介绍',
  `domain` varchar(120) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据状态, 1:初始化 2:更新 3:删除',
  `created` bigint(13) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(13) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(13) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`ia_id`),
  KEY `domain` (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='奖品设置';


CREATE TABLE IF NOT EXISTS `oa_integral_setting` (
  `key` varchar(50) CHARACTER SET utf8 NOT NULL COMMENT '变量名',
  `domain` varchar(120) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '企业域名',
  `value` text CHARACTER SET utf8 NOT NULL COMMENT '值',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型:0=非数组; 1=数组',
  `comment` text CHARACTER SET utf8 NOT NULL COMMENT '说明',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态:1=新创建; 2=已更新; 3=已删除',
  `created` bigint(13) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(13) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(13) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='培训 - 设置表';


CREATE TABLE IF NOT EXISTS `oa_integral_syscache` (
  `name` varchar(32) CHARACTER SET utf8 NOT NULL COMMENT '缓存文件名',
  `domain` varchar(120) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '企业域名',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `data` mediumblob NOT NULL COMMENT '数据',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `created` bigint(13) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(13) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(13) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

";
