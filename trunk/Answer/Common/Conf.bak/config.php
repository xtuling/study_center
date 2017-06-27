<?php
/**
 * config.php
 * 公共配置
 * $Author$
 */

return array(

    // '配置项'=>'配置值'

    // 默认模块
    'DEFAULT_MODULE' => 'Home',

    // 默认控制器
    'DEFAULT_CONTROLLER' => 'Frontend/Index',

    // 附件服务器
    'FILE_SERVER_URL' => 'http://file/',

    // 头像服务器
    'FACE_SERVER_URL' => 'http://face/',

    // 开启多语言
    'LANG_SWITCH_ON' => true,

    // 自动侦测语言 开启多语言功能后有效
    'LANG_AUTO_DETECT' => true,

    // 允许切换的语言列表 用逗号分隔
    'LANG_LIST' => 'zh-cn',

    // 默认语言切换变量
    'VAR_LANGUAGE' => 'lang',

    // 数据库表前缀
    'DB_PREFIX' => 'oa_answer_',

    // 数据库连接参数
    'DB_PARAMS' => array(),

    // 数据库调试模式 开启后可以记录SQL日志
    'DB_DEBUG' => true,

    // 启用字段缓存
    'DB_FIELDS_CACHE' => true,

    // 数据库编码默认采用utf8
    'DB_CHARSET' => 'utf8',

    // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'DB_DEPLOY_TYPE' => 0,

    // 数据库读写是否分离 主从式有效
    'DB_RW_SEPARATE' => false,

    // 读写分离后 主服务器数量
    'DB_MASTER_NUM' => 1,

    // 指定从服务器序号
    'DB_SLAVE_NO' => '',

    // 前端路径
    'FRONTEND_PATH' => 'h5',

    // SDK 配置 第三方标识
    'SDK_THIRD_IDENTIFIER' => 'qy',
);
