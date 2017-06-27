<?php
/**
 * config.php
 * 公共配置
 * $Author$
 */
use VcySDK\Attach;

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
    // 数据库设置
    //'DB_TYPE' => 'mysql',
    //'DB_HOST' => '127.0.0.1',
    //'DB_NAME' => 'vchangyi_oa',
    //'DB_USER' => 'root',
    //'DB_PWD' => 'password',
    //'DB_PORT' => '3306',

    // 数据库表前缀
    'DB_PREFIX' => 'oa_',
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
    // 获取菜单配置
    'LOAD_EXT_CONFIG' => 'menu',
    // SDK 配置
    'SDK_THIRD_IDENTIFIER' => 'qy',
    // DEBUG SDK初始化用的应用标识
    'DEBUG_PLUGIN_IDENTIFIER' => '',
    // Auth key，二维码登录时加密解密数据用
    'QRCODE_LOGIN_AUTU_KEY' => '2fi2vQ2pxuoP9JWi7xkffxfh4ifJoxym',
    // Auth key，二维码登录时scret超时时间
    'QRCODE_LOGIN_SECRET_TIMEOUT' => 600,
    // 畅移云工作的crop_id
    'COM_VCHANGYI_CROP_ID' => 'wxa7044ee8255576b0',
    // 短信标识头
    'MESSAGE_TITLE'=>'【畅移信息】',

    'SMS_CODE_MESSAGE_TPL' => '{SIGN}您的验证码为：{CODE}，请在30分钟内输入',
    'SMS_CODE_SIGN' => '【畅移】',

    // 超级管理员角色保护名称
    'ADMIN_ROLE_PROTECT_NAME' => '超级管理员',

    // 管理员状态: 正常
    'CP_ADMINER_STATUS_NORMAL' => 1,
    // 管理员状态: 禁止登陆
    'CP_ADMINER_STATUS_DENY' => 2,
    // 登录超时时间, 单位: s
    'CP_LOGIN_TOKEN_EXPIRE' => 900,

    // 通讯录标识
    'APP_CONTACT_IDENTIFIER' => 'contact',

    // 通用基础企业标识常量(只是为了兼容URL规则)
    'COMMON_DOMAIN' => 'comm',

    // 资源鉴权配置
    'RES_AUTH_CONFIG' => [
        // 资料库
        'doc' => [
            'atAuthRequired' => Attach::AUTH_REQUIRED_TRUE,
            'atAuthUrl' => ['Frontend/Callback/CheckRight/Index', 'Doc'],
            'atDefaultAuth' => Attach::DEFAULT_AUTH_HIDDEN,
        ],
    ],
);
