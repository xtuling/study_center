<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

/**
 * ThinkPHP 简体中文语言包
 */
return array(
    'PLEASE_LOGIN' => '40037:请登录',

    /* 核心语言变量 */
	'_MODULE_NOT_EXIST_' => '无法加载模块',
    '_CONTROLLER_NOT_EXIST_' => '无法加载控制器',
    '_ERROR_ACTION_' => '非法操作',
    '_LANGUAGE_NOT_LOAD_' => '无法加载语言包',
    '_TEMPLATE_NOT_EXIST_' => '模板不存在',
    '_MODULE_' => '模块',
    '_ACTION_' => '操作',
    '_MODEL_NOT_EXIST_' => '模型不存在或者没有定义',
    '_VALID_ACCESS_' => '没有权限',
    '_XML_TAG_ERROR_' => 'XML标签语法错误',
    '_DATA_TYPE_INVALID_' => '非法数据对象！',
    '_OPERATION_WRONG_' => '操作出现错误',
    '_NOT_LOAD_DB_' => '无法加载数据库',
    '_NO_DB_DRIVER_' => '无法加载数据库驱动',
    '_NOT_SUPPORT_DB_' => '系统暂时不支持数据库',
    '_NO_DB_CONFIG_' => '没有定义数据库配置',
    '_NOT_SUPPORT_' => '系统不支持',
    '_CACHE_TYPE_INVALID_' => '无法加载缓存类型',
    '_FILE_NOT_WRITABLE_' => '目录（文件）不可写',
    '_METHOD_NOT_EXIST_' => '方法不存在！',
    '_CLASS_NOT_EXIST_' => '实例化一个不存在的类！',
    '_CLASS_CONFLICT_' => '类名冲突',
    '_TEMPLATE_ERROR_' => '模板引擎错误',
    '_CACHE_WRITE_ERROR_' => '缓存文件写入失败！',
    '_TAGLIB_NOT_EXIST_' => '标签库未定义',
    '_OPERATION_FAIL_' => '操作失败！',
    '_OPERATION_SUCCESS_' => '操作成功！',
    '_SELECT_NOT_EXIST_' => '记录不存在！',
    '_EXPRESS_ERROR_' => '表达式错误',
    '_TOKEN_ERROR_' => '表单令牌错误',
    '_RECORD_HAS_UPDATE_' => '记录已经更新',
    '_NOT_ALLOW_PHP_' => '模板禁用PHP代码',
    '_PARAM_ERROR_' => '参数错误或者未定义',
    '_ERROR_QUERY_EXPRESS_' => '错误的查询条件',
    '_ERROR_SQL_PARAMS_' => 'SQL 参数错误',
    '_ERROR_FORBIDDEN_' => '没有权限',
    'DATE' => array(
        'BEFORE' => '前',
        'DAY' => '天',
        'YDAY' => '昨天',
        'BYDAY' => '前天',
        'HOUR' => '小时',
        'HALF' => '半',
        'MIN' => '分钟',
        'SEC' => '秒',
        'NOW' => '刚刚'
    ),

    // SQL 参数错误
    '_ERR_SQL_LIMIT_INVALID_' => 'SQL LIMIT 参数错误',

    // SQL 排序字段错误
    '_ERR_ORDER_BY_FIELD_INVALID_' => '排序字段格式错误 [{$field}]',

    // 删除条件错误
    '_ERR_DELETE_CONDS_INVALID_' => '删除条件错误',

    // 成功时, 页面的提示标题
    'SUCCEED_TITLE' => '成功',

    // 成功时, 页面的提示信息
    'SUCCEED_MESSAGE' => '操作成功',

    // 失败时, 页面的提示标题
    'FAILED_TITLE' => '失败',

    // 失败时, 页面的提示信息
    'FAILED_MESSAGE' => '操作失败',

    // 操作方法创建成功时的提示
    'METHOD_CREATED' => '方法已创建, 请刷新',

    // sms 错误
    '_ERR_SMS_ACCOUNT_OR_PASSWD_IS_EMPTY' => '2000:sms 的账号或密码为空',
    '_ERR_SMS_MOBILE_IS_EMPTY' => '2001:sms 手机号码为空',
    '_ERR_SMS_MSG_IS_EMPTY' => '2002:sms 消息内容为空',
    '_ERR_SMS_SUBMIT_ERROR' => '2003:sms 提交错误',
    '_ERR_SMS_SEND_ERROR' => '2004:sms 发送错误 [{$error}]',
    '_ERR_SERVICE_MODEL_UN_INIT' => '2005:Model 未初始化',
    '_ERR_BEFORE_ACTION' => '2006:前置方法 [{$action}] 调用错误',
    '_ERR_AFTER_ACTION' => '2007:后置方法 [{$action}] 调用错误',
    '_ERR_DEFAULT' => '2008:系统繁忙, 请稍后再试',
    '_ERR_PHPRPC_INIT_PARAMS_EMPTY' => '2009:PHPRPC 初始化参数为空',
    '_ERR_WHERE_FIELD_INVALID' => '2010:SQL 查询 WHERE 条件字段错误',
    '_ERR_SET_FIELD_INVALID' => '2011:SQL 查询 SET 字段错误',
    '_ERR_UPLOAD_FILE_SIZE_INVALID' => '2012:上传文件大小不符',
    '_ERR_UPLOAD_FILE_MIME_INVALID' => '2013:上传文件MIME类型不允许',
    '_ERR_UPLOAD_FILE_TYPE_INVALID' => '2014:上传文件类型不允许',
    '_ERR_UPLOAD_FILE_INVALID' => '2015:非法上传文件！',
    '_ERR_UPLOAD_FILE_NOT_NULL' => '2016:没有选择上传文件',
    '_ERR_PHPEXCEL_READABLE_NO' => '2017:无法读取上传的 Excel 文件',
    '_ERR_PHPEXCEL_NOT_VOA_EXCEL_TPL' => '2018:上传的文件不是标准的云工作模板格式，请使用下载的模板',
    '_ERR_PHPEXCEL_DATA_IS_EMPTY' => '2019:没有读取到有效的数据',
    '_ERR_PHPEXCEL_TITLE_IS_EMPTY' => '2020:标题栏行号不存在',
    '_ERR_PHPEXCEL_FILE_NOT_EXISTS' => '2021:文件不存在',
    '_ERR_PHPEXCEL_FILE_CAN_NOT_OPEN' => '2022:权限不足',

    '_ERROR_DOMAIN_NOT_EXISTS_' => '2023:企业标识信息丢失',
    '_ERROR_DOMAIN_LOST_' => '2024:企业标识信息不存在',
    '_ERROR_DOMAIN_LOST_VALUE_' => '2025:未知的企业标识信息',
    '_ERROR_DOMAIN_MULTI_LOST_VALUE_' => '2026:数据包含未定义的企业标识信息',
    '_ERROR_MISS_PLUGIN_CONFIG_PLS_REINSTALL' => '2027:丢失应用配置信息,请重新安装',
    '_ERROR_SHORTURL_FAIL' => '2028:短网址服务失败',
    '_ERROR_SMS_TOO_LONG' => '2029:短信内容长度超过70个字符限制',
    '_ERROR_MISS_CONFIG_FILECONVERTAPIURL' => '2030:配置项 fileConvertApiUrl 丢失'
);
