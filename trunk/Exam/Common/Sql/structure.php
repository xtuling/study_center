<?php
/**
 * 应用的数据表结构文件
 * structure.php
 * $Author$
 */

return "
CREATE TABLE IF NOT EXISTS `oa_exam_like` (
  `like_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` CHAR(32) NOT NULL DEFAULT '' COMMENT '点赞人ID',
  `ea_id` INT(10) NOT NULL DEFAULT '0' COMMENT '答卷表Id',
  `domain` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`like_id`),
  KEY `ea_id` (`ea_id`),
  KEY `domain_status` (`domain`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='考试-点赞表';

CREATE TABLE IF NOT EXISTS `oa_exam_bank` (
  `eb_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `eb_name` VARCHAR(50) NOT NULL COMMENT '题库名称',
  `single_count` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '单选题数量',
  `multiple_count` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '多选题数量',
  `judgment_count` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '判断题数量',
  `question_count` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '问答题数量',
  `voice_count` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '语音题数量',
  `total_count` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '总数',
  `domain` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`eb_id`),
  KEY `domain_status` (`domain`,`status`)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COMMENT='考试-题库表';

CREATE TABLE IF NOT EXISTS `oa_exam_tag` (
  `etag_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键(标签ID)',
  `tag_name` VARCHAR(200) NOT NULL DEFAULT '' COMMENT '标签名称',
  `tag_type` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '标签类型(0:手动添加 1:关联导入)',
  `domain` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`etag_id`),
  KEY `domain_status` (`domain`,`status`)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COMMENT='考试-标签信息表';

CREATE TABLE IF NOT EXISTS `oa_exam_attr` (
  `attr_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键(属性ID)',
  `etag_id` INT(10) UNSIGNED NOT NULL  COMMENT '所属标签',
  `attr_name` VARCHAR(200) NOT NULL DEFAULT '' COMMENT '属性名称',
  `domain` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`attr_id`),
  KEY `domain_status` (`etag_id`,`domain`,`status`)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COMMENT='考试-属性信息表';

CREATE TABLE IF NOT EXISTS `oa_exam_category` (
  `ec_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键(分类ID)',
  `parent_id` INT(10) UNSIGNED NOT  NULL DEFAULT '0' COMMENT '父级分类ID',
  `ec_name` VARCHAR(100)  NOT NULL  COMMENT '分类名称',
  `ec_desc` TEXT  NOT NULL  COMMENT '分类描述',
  `order_num` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '序号',
  `ec_status` TINYINT(1) UNSIGNED NOT  NULL DEFAULT '1'   COMMENT '分类状态（0：禁用，1：开启）',
  `is_all` TINYINT(1) UNSIGNED NOT  NULL DEFAULT '1'   COMMENT '权限状态（0：不是全公司，1：全公司）',
  `domain` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`ec_id`),
  KEY `order_num` (`order_num`),
  KEY `domain_status` (`domain`,`status`)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COMMENT='考试-试卷分类表';

CREATE TABLE IF NOT EXISTS `oa_exam_topic` (
  `et_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `eb_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '题库id',
  `et_type` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '题目类型(1:单选题 2:判断题 3:问答题 4:多选题 5:语音题)',
  `title` TEXT NOT NULL COMMENT '题目名称',
  `title_pic` TEXT NOT NULL COMMENT '题目图片（逗号分割）',
  `score` DECIMAL(18,2) UNSIGNED NOT NULL  DEFAULT '0' COMMENT '分数',
  `options` TEXT COMMENT '选项(序列化:选项名称,选项值，图片ID)',
  `answer` TEXT NOT NULL COMMENT '正确答案（多选用逗号分隔）',
  `answer_resolve` TEXT NOT NULL  COMMENT '答案解析',
  `use_num` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '题目使用次数',
  `answer_coverage` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '答案覆盖率（问答题）',
  `match_type` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否匹配关键字(0:否 1:是)',
  `answer_keyword` TEXT NOT NULL COMMENT '答案关键字(序列化:关键字，百分比)',
  `order_num` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '序号(越小越靠前)',
  `domain` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`et_id`),
  KEY `domain_status` (`domain`,`status`)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COMMENT='考试-题目表';

CREATE TABLE IF NOT EXISTS `oa_exam_topic_attr` (
  `eta_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `etag_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '标签ID',
  `attr_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '属性ID',
  `eb_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '题目所属题库ID',
  `et_id`  INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '题目ID',
  `domain` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`eta_id`),
  KEY `attr_et_id` (`attr_id`,`et_id`),
  KEY `domain_status` (`domain`,`status`)
)ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COMMENT='考试-题目属性关联表';

CREATE TABLE IF NOT EXISTS `oa_exam_paper` (
  `ep_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `ec_id` INT(10) UNSIGNED NOT NULL  COMMENT '所属分类ID',
  `paper_type` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'   COMMENT '试卷使用类型(0:测评试卷，1：模拟试卷)',
  `bank_data` TEXT NOT NULL  COMMENT '题库ID（逗号分隔）',
  `tag_data` TEXT NOT NULL  COMMENT '属性序列化（序列化）',
  `admin_id` CHAR(32) NOT NULL COMMENT '发布人ID',
  `launch_man` VARCHAR(54) NOT NULL COMMENT '发布人',
  `ep_type` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '试卷类型(1:自主选题 2:规则抽题 3:随机抽题)',
  `search_type` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '标签筛选方式(1:全部满足 2:满足任意一个)',
  `bank_topic_data` TEXT NOT NULL  COMMENT '题库题目设置序列化（出题规则序列化）',
  `rule` TEXT NOT NULL COMMENT '规则抽题模式的规则（序列化）',
  `check_topic_data` TEXT NOT NULL  COMMENT '选中题目列表（序列化）',
  `ep_name` VARCHAR(200) NOT NULL DEFAULT '0' COMMENT '试卷名称',
  `topic_count` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT '题目数',
  `join_count` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '已参与人数',
  `unjoin_count` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '未参与人数',
  `cover_id` CHAR(32) NOT NULL  DEFAULT '' COMMENT '封面',
  `begin_time` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '开始时间',
  `end_time` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '结束时间',
  `paper_time` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT '考试时长',
  `is_notify` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否通知(0:否 1:是)',
  `is_recommend` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否开始推荐(0:否 1:是)',
  `notify_begin` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '开始前通知时间',
  `notify_end` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '结束前通知时间',
  `begin_corn` CHAR(32) NOT NULL  DEFAULT '' COMMENT '开始前通知cornid',
  `end_cron` CHAR(32) NOT NULL  DEFAULT '' COMMENT '结束前通知cornid',
  `answer_resolve` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '答案解析(1:开启  2：关闭)',
  `total_score` DECIMAL(18,2) UNSIGNED NOT NULL DEFAULT '0' COMMENT '总分',
  `pass_score` DECIMAL(18,2) UNSIGNED NOT NULL DEFAULT '0' COMMENT '及格分',
  `intro` TEXT NOT NULL COMMENT '考试说明',
  `is_all` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '是否全部 (0:否 1:全部)',
  `is_pushmsg` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '是否即时发送提醒 (0:不发送 1：发送)',
  `flag` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '微信通知标记 (0:未通知 1：已通知)',
  `reason` TEXT NOT NULL COMMENT '终止理由',
  `reason_user_id` CHAR(32) NOT NULL COMMENT '终止人员ID',
  `reason_user` VARCHAR(54) NOT NULL COMMENT '终止人员名称',
  `reason_time` BIGINT(13) UNSIGNED NOT NULL  DEFAULT '0' COMMENT '提前终止时间',
  `publish_time` BIGINT(13) UNSIGNED NOT NULL  DEFAULT '0' COMMENT '发布时间',
  `exam_status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '状态,0=初始化 1=草稿，2=已发布 3=终止',
  `cate_status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '分类状态,0=禁用,1=已开启',
  `domain` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`ep_id`),
  KEY `ec_id` (`ec_id`),
  KEY `domain_status` (`domain`,`status`)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COMMENT='试卷表';

CREATE TABLE IF NOT EXISTS `oa_exam_snapshot` (
  `es_id` INT(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `ep_id` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '试卷id',
  `et_id` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目id',
  `et_type` TINYINT(3) unsigned NOT NULL DEFAULT '0' COMMENT '题目类型(1:单选题 2:判断题 3:问答题 4:多选题,5:语音题)',
  `title` TEXT NOT NULL COMMENT '题目名称',
  `title_pic` TEXT NOT NULL COMMENT '题目图片（逗号分割）',
  `score` DECIMAL(18,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '分数',
  `options` TEXT COMMENT '选项(序列化:选项名称,选项值，图片ID)',
  `answer` TEXT NOT NULL COMMENT '正确答案（多选用逗号分隔）',
  `answer_resolve` TEXT NOT NULL COMMENT '答案解析',
  `answer_coverage` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '答案覆盖率（问答题）',
  `match_type` TINYINT(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否匹配关键字(0:否 1:是)',
  `answer_keyword` TEXT NOT NULL COMMENT '答案关键字(序列化:关键字，百分比)',
  `order_num` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '序号',
  `domain` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` TINYINT(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` BIGINT(13) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` BIGINT(13) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` BIGINT(13) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`es_id`),
  KEY `domain_status` (`domain`,`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='考试-试卷快照表' AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `oa_exam_answer_detail` (
  `ead_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `ea_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '答卷id',
  `et_option` TEXT  NOT NULL  COMMENT '选项序列化（选项名称,选项值，图片ID，是否是正确答案）',
  `et_detail` TEXT  NOT NULL  COMMENT '题目详情序列化',
  `my_score` DECIMAL(18,2)  NOT NULL DEFAULT '0'  COMMENT '我的分数',
  `score` DECIMAL(18,2)  NOT NULL DEFAULT '0'  COMMENT '题目分数',
  `is_pass` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '答题状态(0：未作答；1：已通过；2：未通过)',
  `my_answer` VARCHAR(200) NOT NULL  DEFAULT '' COMMENT '我的答案',
  `order_num` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '序号',
  `domain` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`ead_id`),
  KEY `domain_status` (`domain`,`status`)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COMMENT='考试-答卷详情表';

CREATE TABLE IF NOT EXISTS `oa_exam_paper_temp` (
  `epd_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `ep_id` INT(10) UNSIGNED NOT NULL COMMENT '试卷id',
  `et_id` INT(10) UNSIGNED NOT NULL COMMENT '题目Id',
  `score` DECIMAL(18,2)UNSIGNED NOT NULL DEFAULT '0' COMMENT '分数',
  `order_num` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '序号',
  `domain` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`epd_id`),
  KEY `domain_status` (`domain`,`status`)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COMMENT='试卷临时备选题目储存表';

CREATE TABLE IF NOT EXISTS `oa_exam_right` (
  `er_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `epc_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '试卷ID或者分类ID',
  `er_type` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '权限类型（0：试卷权限，1：分类权限，2：激励权限，3：阅卷权限）',
  `uid` CHAR(32) NOT NULL DEFAULT '' COMMENT '人员 ID',
  `cd_id` CHAR(32) NOT NULL DEFAULT '' COMMENT '部门 ID',
  `tag_id` CHAR(32) NOT NULL DEFAULT '' COMMENT '标签 ID',
  `job_id` CHAR(32) NOT NULL DEFAULT '' COMMENT '岗位 ID',
  `role_id` CHAR(32) NOT NULL DEFAULT '' COMMENT '角色 ID',
  `domain` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`er_id`),
  KEY `domain_status` (`domain`,`status`)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COMMENT='试卷 - 权限表';

CREATE TABLE IF NOT EXISTS `oa_exam_answer` (
  `ea_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` CHAR(32)  NOT NULL COMMENT '用户id',
  `ep_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '试卷id',
  `my_score` DECIMAL(18,2) UNSIGNED NOT NULL DEFAULT '0' COMMENT '考生分数',
  `my_begin_time` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '考生开始考试时间',
  `my_time` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '考生用时(毫秒)',
  `my_error_num` MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT '考生答错的数量',
  `my_is_pass` TINYINT(1) UNSIGNED  NOT NULL DEFAULT '0' COMMENT '考生是否通过（0:否 1：通过）',
  `paper_info` TEXT  NOT NULL COMMENT '试卷信息序列化',
  `domain` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`ea_id`),
  KEY `domain_status` (`domain`,`status`)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COMMENT='试卷 - 答卷表';

CREATE TABLE IF NOT EXISTS `oa_exam_answer_attach` (
  `atta_id` INT(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `ead_id` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '答卷详情 ID',
  `ea_id` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '答卷 ID',
  `order_id` CHAR(32) NOT NULL DEFAULT '' COMMENT '媒体文件顺序编号',
  `media_id` VARCHAR(256) NOT NULL DEFAULT '' COMMENT '微信媒体文件 media_id',
  `is_complete` TINYINT(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否转换完毕，1=已转换;0=未转换，针对音频文件，其他不需要转换的文件都等于1',
  `at_id` CHAR(32) NOT NULL DEFAULT '' COMMENT '本地附件 ID',
  `type` ENUM('voice','image') NOT NULL DEFAULT 'voice' COMMENT '附件类型：voice=音频;image=图片',
  `file_info` TEXT NOT NULL COMMENT '文件信息。序列化字符串',
  `domain` CHAR(50) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` TINYINT(1) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态：1=新创建；2=已更新；3=已删除',
  `created` BIGINT(13) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` BIGINT(13) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` BIGINT(13) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`atta_id`),
  KEY `ead_id` (`ead_id`),
  KEY `domain_status` (`domain`,`status`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='试卷 - 答题文件表';

CREATE TABLE  IF NOT EXISTS `oa_exam_setting` (
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
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COMMENT='考试设置表';

CREATE TABLE  IF NOT EXISTS `oa_exam_syscache` (
  `name` VARCHAR(32) NOT NULL COMMENT '缓存文件名',
  `domain` VARCHAR(120) NOT NULL DEFAULT '' COMMENT '企业域名',
  `type` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '缓存类型, 0:非数组, 1:数组',
  `data` MEDIUMBLOB NOT NULL COMMENT '数据',
  `status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '记录状态, 1初始化，2=已更新, 3=已删除',
  `created` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间'
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COMMENT='考试 - 缓存表';


CREATE TABLE IF NOT EXISTS `oa_exam_like` (
  `like_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` CHAR(32) NOT NULL DEFAULT '' COMMENT '点赞人ID',
  `ea_id` INT(10) NOT NULL DEFAULT '0' COMMENT '答卷表Id',
  `domain` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`like_id`),
  KEY `ea_id` (`ea_id`),
  KEY `domain_status` (`domain`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='考试-点赞表';


CREATE TABLE IF NOT EXISTS `oa_exam_medal` (
  `em_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '激励行为',
  `em_desc` TEXT  NOT NULL  COMMENT '激励描述',
  `em_type` INT(10) NOT NULL DEFAULT '0' COMMENT '激励类型,0=勋章,1=积分',
  `im_id` CHAR(32) NOT NULL DEFAULT '' COMMENT '勋章ID',
  `em_integral` INT(10) NOT NULL DEFAULT '0' COMMENT '积分',
  `icon_type` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '图标来源 (1:用户上传 2: 系统预设）',
  `is_all` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '是否全部 (0:否 1:全部)',
  `em_rule` TEXT  NOT NULL  COMMENT '规格数值序列化 ID,name',
  `em_number` INT(10) NOT NULL DEFAULT '0' COMMENT '次数',
  `em_score` INT(10) NOT NULL DEFAULT '0' COMMENT '分数',
  `domain` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`em_id`),
  KEY `domain_status` (`domain`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='考试-激励表';


CREATE TABLE IF NOT EXISTS `oa_exam_medal_relation` (
  `id` INT(10) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `ep_id` INT(10) NOT NULL DEFAULT '0' COMMENT '试卷ID',
  `em_id` INT(10) NOT NULL DEFAULT '0' COMMENT '激励ID',
  `domain` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `domain_status` (`domain`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='考试-激励试卷关联表';


CREATE TABLE IF NOT EXISTS `oa_exam_medal_record` (
  `emrid` INT(10) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` CHAR(32) NOT NULL DEFAULT '' COMMENT '领取人UID',
  `em_id` INT(10) NOT NULL DEFAULT '0' COMMENT '激励ID',
  `domain` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` BIGINT(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`emrid`),
  KEY `domain_status` (`domain`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='考试-勋章,积分领取表';

CREATE TABLE IF NOT EXISTS `oa_exam_break` (
  `ebreak_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '闯关ID主键',
  `uid` char(32) NOT NULL COMMENT '用户id',
  `ec_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程ID',
  `my_score` decimal(18,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '考生分数',
  `my_begin_time` bigint(13) unsigned NOT NULL DEFAULT '0' COMMENT '闯关开始时间',
  `my_end_time` bigint(13) unsigned NOT NULL DEFAULT '0' COMMENT '闯关结束时间',
  `my_error_num` mediumint(5) unsigned DEFAULT '0' COMMENT '考生答错的数量',
  `my_is_pass` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否闯关成功（0:没有成功 1：成功）',
  `is_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '交卷状态（0:未交卷 1：已交卷）',
  `domain` varchar(32) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` bigint(13) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(13) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(13) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`ebreak_id`),
  KEY `domain_status` (`domain`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='闯关答卷 - 答卷表';

CREATE TABLE IF NOT EXISTS `oa_exam_break_attach` (
  `atta_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `ebreak_id` int(11) DEFAULT '0' COMMENT '闯关ID',
  `ebd_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '试题ID',
  `ec_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程ID',
  `order_id` char(32) NOT NULL DEFAULT '' COMMENT '媒体文件顺序编号',
  `media_id` varchar(256) NOT NULL DEFAULT '' COMMENT '微信媒体文件 media_id',
  `is_complete` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否转换完毕，1=已转换;0=未转换，针对音频文件，其他不需要转换的文件都等于1',
  `at_id` char(32) NOT NULL DEFAULT '' COMMENT '本地附件 ID',
  `type` enum('voice','image') NOT NULL DEFAULT 'voice' COMMENT '附件类型：voice=音频;image=图片',
  `file_info` text NOT NULL COMMENT '文件信息。序列化字符串',
  `domain` char(50) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '数据状态：1=新创建；2=已更新；3=已删除',
  `created` bigint(13) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(13) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(13) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`atta_id`),
  KEY `ebd_id` (`ebd_id`),
  KEY `domain_status` (`domain`,`status`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='闯关答卷 - 答题文件表';

CREATE TABLE IF NOT EXISTS `oa_exam_break_detail` (
  `ebd_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `ebreak_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '答卷id',
  `ec_id` int(11) DEFAULT '0' COMMENT '课程ID',
  `et_option` text NOT NULL COMMENT '选项序列化（选项名称,选项值，图片ID，是否是正确答案）',
  `et_detail` text NOT NULL COMMENT '题目详情序列化',
  `my_score` decimal(18,2) NOT NULL DEFAULT '0.00' COMMENT '我的分数',
  `score` decimal(18,2) NOT NULL DEFAULT '0.00' COMMENT '题目分数',
  `is_pass` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '答题状态(0,1：已通过；2：未通过)',
  `my_answer` varchar(200) NOT NULL DEFAULT '' COMMENT '我的答案',
  `order_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '序号',
  `is_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT ' 作答状态 0未作答, 1已作答',
  `domain` varchar(32) NOT NULL DEFAULT '' COMMENT '企业域名',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态, 1=初始化，2=已更新，3=已删除',
  `created` bigint(13) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated` bigint(13) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted` bigint(13) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`ebd_id`),
  KEY `domain_status` (`domain`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='闯关答卷-答卷详情表';
";

