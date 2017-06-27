<?php
/**
 * 应用的数据表结构文件
 * structure.php
 * $Author$
 */
return "
CREATE TABLE IF NOT EXISTS `oa_contact_attr` (
  `attr_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键自增id',
  `field_name` varchar(50) NOT NULL COMMENT '架构预留字段名',
  `attr_name` varchar(100) NOT NULL COMMENT '属性名',
  `postion` tinyint(1) unsigned NOT NULL COMMENT '前端显示区域：1=基本信息；2=验证信息；3=身份信息',
  `type` int(10) unsigned NOT NULL COMMENT '属性类型：1=单行文本；2=多行文本；3=数字；4=日期；5=时间；6=日期时间；7=单选；8=多选；9=地址；10=图片；11=下拉框单选；999=部门（选人组件，前端不可选择）',
  `option` text NOT NULL COMMENT '属性选项',
  `order` int(10) unsigned NOT NULL COMMENT '排序',
  `is_system` tinyint(1) unsigned NOT NULL COMMENT '是否为系统默认字段：0=不是；1=是',
  `is_open_cp` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '管理后台是否开启该字段, 0: 不开启; 1: 开启',
  `is_open_cp_edit` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '是否可编辑 is_open_cp, 0: 不允许; 1: 允许;',
  `is_open` tinyint(1) unsigned NOT NULL COMMENT '是否开启：0=不开启；1=开启；',
  `is_open_edit` tinyint(1) unsigned NOT NULL COMMENT '是否允许编辑is_open字段：0=不允许；1=允许',
  `is_required_cp` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '管理后台是否要求必填; 0: 非必填; 1: 必填;',
  `is_required_cp_edit` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '是否可编辑 is_required_cp, 0: 不允许; 1: 允许',
  `is_required` tinyint(1) unsigned NOT NULL COMMENT '是否必填：0=非必填；1=必填',
  `is_required_edit` tinyint(1) unsigned NOT NULL COMMENT '是否允许编辑is_required字段：0=不允许；1=允许',
  `is_show` tinyint(1) unsigned NOT NULL COMMENT '是否显示：0=不显示；1=显示',
  `is_show_edit` tinyint(1) unsigned NOT NULL COMMENT '是否允许编辑is_show字段：0=不允许；1=允许',
  `domain` varchar(50) NOT NULL COMMENT '企业域名',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态：1=新创建；2=已更新；3=已删除',
  `created` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`attr_id`),
  KEY `type` (`type`),
  KEY `is_open` (`is_open`),
  KEY `is_required` (`is_required`),
  KEY `is_show` (`is_show`),
  KEY `domain` (`domain`),
  KEY `status` (`status`),
  KEY `is_system` (`is_system`),
  KEY `attr_name` (`attr_name`),
  KEY `field_name` (`field_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='通讯录-员工属性表';


CREATE TABLE IF NOT EXISTS `oa_contact_card` (
  `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键自增id',
  `uid` varchar(45) NOT NULL COMMENT '人员did',
  `fields` text NOT NULL COMMENT '不显示的属性（序列化存储）',
  `domain` varchar(45) NOT NULL COMMENT '企业域名',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态：1=新创建；2=已更新；3=已删除',
  `created` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`card_id`),
  KEY `uid` (`uid`),
  KEY `domain` (`domain`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='通讯录-人员名片设置';


CREATE TABLE IF NOT EXISTS `oa_contact_competence` (
  `cm_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `cm_name` char(80) NOT NULL DEFAULT '' COMMENT '能力模型名称',
  `cm_level` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '能力级别',
  `cm_desc` varchar(255) NOT NULL DEFAULT '' COMMENT '能力模型说明',
  `cm_displayorder` int(11) NOT NULL DEFAULT '0' COMMENT '排序号',
  `domain` varchar(50) NOT NULL DEFAULT '' COMMENT '企业标识',
  `status` int(11) NOT NULL DEFAULT '1' COMMENT '数据状态, 1: 新建; 2: 更新; 3: 删除',
  `created` bigint(20) NOT NULL DEFAULT '0' COMMENT '新建时间',
  `updated` bigint(20) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`cm_id`),
  KEY `cm_domain` (`domain`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='能力模型表';


CREATE TABLE IF NOT EXISTS `oa_contact_contract` (
  `contract_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键自增id',
  `uid` varchar(32) NOT NULL COMMENT '人员uid',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '合同类型：1=劳动合同；2=劳务合同；3=非全日制合同',
  `work_place` varchar(50) NOT NULL COMMENT '工作地点',
  `money` decimal(12,2) DEFAULT NULL COMMENT '合同工资',
  `years` tinyint(2) NOT NULL DEFAULT '1' COMMENT '合同年限：-1=自定义；0=无固定；1=一年；2=两年；3=三年；4=四年；5=五年；6=六年；7=七年；8=八年；9=九年；10=十年；. . .',
  `begin_time` varchar(20) DEFAULT NULL COMMENT '劳动合同开始日（空=未填写；非空=毫秒级时间戳）',
  `end_time` varchar(20) DEFAULT NULL COMMENT '劳动合同结束日（空=未填写；非空=毫秒级时间戳）',
  `probation` tinyint(1) NOT NULL DEFAULT '0' COMMENT '试用期：-1=自定义；0=无；1=一个月；2=两个月；3=三个月；4=四个月；5=五个月；6=六个月；',
  `probation_money` decimal(12,2) DEFAULT NULL COMMENT '试用期工资',
  `probation_begin_time` varchar(20) DEFAULT NULL COMMENT '试用期开始日（空=未填写；非空=毫秒级时间戳）',
  `probation_end_time` varchar(20) DEFAULT NULL COMMENT '试用期结束日（空=未填写；非空=毫秒级时间戳）',
  `signing_time` varchar(20) DEFAULT NULL COMMENT '合同签订日期（空=未填写；非空=毫秒级时间戳）',
  `company` varchar(50) NOT NULL COMMENT '工作单位',
  `company_place` varchar(50) NOT NULL COMMENT '营业地点',
  `corporation` varchar(50) NOT NULL COMMENT '法定代表',
  `user_address` varchar(50) NOT NULL COMMENT '员工联系地址',
  `user_mobile` varchar(50) NOT NULL COMMENT '员工联系电话',
  `urgent_linkman` varchar(50) NOT NULL COMMENT '紧急联系人',
  `urgent_mobile` varchar(50) NOT NULL COMMENT '紧急联系人电话',
  `urgent_address` varchar(50) NOT NULL COMMENT '紧急联系人地址',
  `domain` varchar(50) NOT NULL COMMENT '企业域名',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态：1=新创建；2=已更新；3=已删除',
  `created` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`contract_id`),
  KEY `uid` (`uid`),
  KEY `type` (`type`),
  KEY `domain` (`domain`),
  KEY `status` (`status`),
  KEY `singint_time` (`signing_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='通讯录-合同信息表';


CREATE TABLE IF NOT EXISTS `oa_contact_dept_right` (
  `right_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键自增id',
  `dp_id` varchar(50) NOT NULL COMMENT '所在部门',
  `is_all` tinyint(1) unsigned NOT NULL COMMENT '是否可查看全公司：0=否；1=是',
  `is_dept` tinyint(1) unsigned NOT NULL COMMENT '是否可查看全公司：0=否；1=是',
  `dept_ids` text NOT NULL COMMENT '可查看部门',
  `domain` varchar(50) NOT NULL COMMENT '企业域名',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态：1=新创建；2=已更新；3=已删除',
  `created` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`right_id`),
  KEY `dp_id` (`dp_id`),
  KEY `domain` (`domain`),
  KEY `status` (`status`),
  KEY `is_all` (`is_all`),
  KEY `is_dept` (`is_dept`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='通讯录-通讯录部门权限';


CREATE TABLE IF NOT EXISTS `oa_contact_import_data` (
  `cid_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键自增id',
  `ea_id` varchar(45) NOT NULL COMMENT '管理人员ca_id',
  `import_flag` char(32) NOT NULL DEFAULT '' COMMENT '导入标识',
  `data_type` char(32) NOT NULL DEFAULT '' COMMENT '数据类型, title: 表头, department: 组织; member: 用户',
  `data` text NOT NULL COMMENT '组织/人员数据',
  `is_error` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '导入结果, 0: 未知; 1: 已出错',
  `domain` varchar(45) NOT NULL COMMENT '企业域名',
  `fail_message` varchar(100) NOT NULL DEFAULT '' COMMENT '失败原因, is_error=1时，才会有值',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态：1=新创建；2=已更新；3=已删除',
  `created` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`cid_id`),
  KEY `ca_id` (`ea_id`),
  KEY `domain` (`domain`),
  KEY `status` (`status`),
  KEY `import_flag` (`import_flag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='通讯录-待导入数据';


CREATE TABLE IF NOT EXISTS `oa_contact_invite_link` (
  `link_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增主键id',
  `invite_uid` varchar(32) NOT NULL DEFAULT '' COMMENT '邀请人uid',
  `default_data` varchar(255) NOT NULL DEFAULT '' COMMENT '默认数据, 比如: 部门/岗位/角色',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '2' COMMENT '接收邀请的类型：1=直接邀请；2=审批邀请；',
  `domain` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态：1=新创建；2=已更新；3=已删除',
  `created` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`link_id`),
  KEY `domain` (`domain`) USING BTREE,
  KEY `status` (`status`),
  KEY `invite_uid` (`invite_uid`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='通讯录-邀请连接表';


CREATE TABLE IF NOT EXISTS `oa_contact_invite_setting` (
  `setting_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键自增id',
  `content` text NOT NULL COMMENT '邀请函内容',
  `share_content` text NOT NULL COMMENT '微信分享语',
  `type` tinyint(1) unsigned NOT NULL COMMENT '邀请方式：1=直接邀请；2=审批邀请',
  `invite_udpids` text NOT NULL COMMENT '有审核权限的人员uid/部门ID/标签ID/岗位ID/角色ID（序列化存储）',
  `check_type` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '审批方式, 1: 邀请人审批; 2: 被邀请人所在组织负责人审批;',
  `check_udpids` text NOT NULL COMMENT '有审核权限的人员uid/部门ID/标签ID/岗位ID/角色ID（序列化存储）',
  `departments` text NOT NULL COMMENT '部门id的数组序列化（为空时进入邀请人所在部门）',
  `inviter_write` text NOT NULL COMMENT '邀请人需要填写的表单, department: 部门, job: 职位, role: 角色（序列化存储）',
  `form` text NOT NULL COMMENT '被邀请人需要填写的表单（序列化存储）',
  `qrcode_expire` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '二维码有效期的时间戳',
  `domain` varchar(50) CHARACTER SET utf8 NOT NULL COMMENT '企业域名',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态：1=新创建；2=已更新；3=已删除',
  `created` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`setting_id`),
  KEY `domain` (`domain`) USING BTREE,
  KEY `status` (`status`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='邀请设置表';


CREATE TABLE IF NOT EXISTS `oa_contact_invite_user` (
  `invite_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增主键id',
  `link_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '邀请链接ID',
  `invite_uid` varchar(32) NOT NULL COMMENT '邀请人uid',
  `udpid` varchar(128) NOT NULL COMMENT '有权限的审核人uid/用户所在部门ID',
  `wx_openid` varchar(64) NOT NULL DEFAULT '' COMMENT '用户的微信openid',
  `check_uid` varchar(32) NOT NULL DEFAULT '' COMMENT '审核人uid',
  `uid` varchar(32) DEFAULT NULL COMMENT '被邀请人：加入企业号后的uid',
  `username` varchar(50) NOT NULL COMMENT '被邀请人：用户姓名',
  `weixin` varchar(50) NOT NULL COMMENT '被邀请人：微信号',
  `email` varchar(50) NOT NULL COMMENT '被邀请人：邮箱',
  `mobile` varchar(50) NOT NULL COMMENT '被邀请人：手机号',
  `form` text NOT NULL COMMENT '被邀请人：人员信息（表单）数组序列化',
  `type` tinyint(1) unsigned NOT NULL COMMENT '接收邀请的类型：1=直接邀请；2=审批邀请；',
  `check_status` tinyint(1) unsigned NOT NULL COMMENT '审批结果：1=待审批；2=通过审批；3=驳回审批',
  `check_time` bigint(20) unsigned NOT NULL COMMENT '审批时间',
  `is_notice` tinyint(1) NOT NULL COMMENT '是否已通知邀请人：0=未通知；1=已通知',
  `domain` varchar(50) CHARACTER SET utf8 NOT NULL COMMENT '企业域名',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态：1=新创建；2=已更新；3=已删除',
  `created` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`invite_id`),
  KEY `domain` (`domain`) USING BTREE,
  KEY `status` (`status`),
  KEY `uid` (`invite_uid`),
  KEY `invite_uid` (`invite_uid`),
  KEY `check_uid` (`check_uid`),
  KEY `username` (`username`),
  KEY `weixin` (`weixin`),
  KEY `email` (`email`),
  KEY `mobile` (`mobile`),
  KEY `check_status` (`check_status`),
  KEY `type` (`type`),
  KEY `wx_openid` (`wx_openid`),
  KEY `link_id` (`link_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='通讯录-邀请人员信息表';


CREATE TABLE IF NOT EXISTS `oa_contact_invite_user_right` (
  `ur_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增主键id',
  `invite_id` int(11) NOT NULL DEFAULT '0' COMMENT '邀请id',
  `udtid` varchar(32) NOT NULL COMMENT '审核人uid/部门id',
  `domain` varchar(50) CHARACTER SET utf8 NOT NULL COMMENT '企业域名',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态：1=新创建；2=已更新；3=已删除',
  `created` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`ur_id`),
  KEY `invite_id` (`invite_id`),
  KEY `udtid` (`udtid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='通讯录-邀请人员信息审核权限表';


CREATE TABLE IF NOT EXISTS `oa_contact_notice` (
  `notice_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '通知表自增id',
  `eaid` varchar(50) NOT NULL COMMENT '管理员id',
  `adminer_mobile` varchar(50) NOT NULL COMMENT '管理员电话号码',
  `uid` text NOT NULL COMMENT '被通知的人的uid',
  `user_name` varchar(50) NOT NULL COMMENT '被通知人的姓名',
  `email` varchar(20) NOT NULL COMMENT '被通知人的电话',
  `user_mobile` varchar(20) DEFAULT NULL COMMENT '被通知人的邮箱',
  `domain` varchar(50) CHARACTER SET utf8 NOT NULL COMMENT '企业域名',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '数据状态：1=新创建；2=已更新；3=已删除',
  `created` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`notice_id`),
  KEY `domain` (`domain`) USING BTREE,
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='通讯录-通知关注表';


CREATE TABLE IF NOT EXISTS `oa_contact_setting` (
  `setting_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键自增id',
  `key` varchar(50) NOT NULL COMMENT '变量名',
  `value` text NOT NULL COMMENT '值',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型：0=非数组; 1=数组',
  `comment` text NOT NULL COMMENT '说明',
  `domain` varchar(50) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态：1=新创建； 2=已更新；3=已删除',
  `created` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`setting_id`),
  KEY `key` (`key`),
  KEY `domain` (`domain`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='通讯录-设置表';


CREATE TABLE IF NOT EXISTS `oa_contact_ssaf` (
  `ssaf_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键自增id',
  `uid` varchar(32) NOT NULL COMMENT '人员uid',
  `place` varchar(50) NOT NULL COMMENT '户籍地',
  `place_type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '户籍性质：1=城镇；2=农村',
  `ss_type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '社保类型：1=无；2=五险；3=三险',
  `ss_place` varchar(50) NOT NULL COMMENT '社保缴纳地',
  `ss_base` decimal(12,2) unsigned DEFAULT NULL COMMENT '社保缴纳基数，单位“元”',
  `ss_begin_month` varchar(20) DEFAULT NULL COMMENT '社保起缴月份（空=未填写；非空=毫秒级时间戳）',
  `ss_handle_month` varchar(20) DEFAULT NULL COMMENT '社保办理月份（空=未填写；非空=毫秒级时间戳）',
  `af_is_pay` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '公积金是否缴纳：0=未缴纳；1=缴纳',
  `af_base` decimal(12,2) unsigned DEFAULT NULL COMMENT '公积金基数，单位“元”',
  `af_begin_month` varchar(20) DEFAULT NULL COMMENT '公积金起缴月份（空=未填写；非空=毫秒级时间戳）',
  `af_handle_month` varchar(20) DEFAULT NULL COMMENT '公积金办理月份（空=未填写；非空=毫秒级时间戳）',
  `remarks` varchar(255) NOT NULL COMMENT '备注',
  `domain` varchar(50) NOT NULL COMMENT '企业域名',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态：1=新创建；2=已更新；3=已删除',
  `created` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`ssaf_id`),
  KEY `uid` (`uid`),
  KEY `ss_handle_month` (`ss_handle_month`),
  KEY `af_handle_monty` (`af_handle_month`),
  KEY `domain` (`domain`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='通讯录-社保公积金信息表';


CREATE TABLE IF NOT EXISTS `oa_contact_syscache` (
  `syscache_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键自增id',
  `name` varchar(64) NOT NULL COMMENT '缓存文件名',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存类型：0=非数组，1=数组',
  `data` mediumblob NOT NULL COMMENT '数据',
  `domain` varchar(50) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态：1=新创建；2=已更新；3=已删除',
  `created` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`syscache_id`),
  KEY `name` (`name`),
  KEY `domain` (`domain`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='通讯录-缓存表';


CREATE TABLE IF NOT EXISTS `oa_contact_task` (
  `task_id` varchar(50) NOT NULL COMMENT '任务ID',
  `runtime` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '任务最后一次执行时间',
  `domain` varchar(50) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态：1=新创建； 2=已更新；3=已删除',
  `created` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`task_id`),
  KEY `domain` (`domain`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='通讯录-计划任务表';

";
