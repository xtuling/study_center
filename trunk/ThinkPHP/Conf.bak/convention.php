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
 * ThinkPHP惯例配置文件
 * 该文件请不要修改，如果要覆盖惯例配置的值，可在应用配置文件中设定和惯例不符的配置项
 * 配置名称大小写任意，系统会统一转换成小写
 * 所有配置参数都可以在生效前动态改变
 */
defined('THINK_PATH') or exit();
return array(
    // 是否开启升级
    'BOSS_OPEN_UPDATE_SWITCH' => false,
    // 分页参数变量
    'VAR_PAGE' => 'page',

    // OA企业站HTTP协议，https:// 或 http://
    'PROTOCAL' => 'http://',

    // PHPRPC 加密秘钥
    'PHPRPC_SECRET' => '2fi2vQ2pxuoP9JWi7xkffxfh4ifJoxym',

    // formhash 秘钥
    'FORMHASH_SECRET' => 'eWzf7tb3MTFfeHwDmqLR6W8hEoK2U2Gd',
    'FORMHASH_EXPIRE' => 1800,

    // UC API 地址
    'UC_APIURL' => 'http://d-rst.vchangyi.com',
    // 视频回调转码 UC API 地址
    'UC_REST_APIURL' => 'http://restapi.99hr.com',

    // UC 数据不存在时的报错CODE
    'UC_DATA_NOT_FOUNT' => 'ERROR_DATA_NOT_FOUNT',
    // UC 账号密码错误时的报错CODE
    'UC_ADMIN_LOGIN_ERROR' => 'ADMIN_LOGIN_INVALID_USER_OR_PASSWD_ERROR',

    // API 签名秘钥
    'API_SECRET' => 'uHxhfNF7xxJNbRG2s4YgfAKG9MoXc3i7',
    'API_SIG_EXPIRE' => 600,

    // LBS 接口服务商
    'LBS_PROVIDER' => 'baidu',
    // 字体文件
    'FONT_CN' => THINK_PATH . 'YaHei.ttf',

    // 时间以及时区
    'DATE_FORMAT' => 'Y-m-d',
    'TIME_FORMAT' => 'H:i',
    'TIME_OFFSET' => '8',

    // Sms 配置(创蓝)
    'SMS_CHUANGLAN' => array(
        'ACCOUNT' => 'test',
        'PASSWORD' => 'test'
    ),

    // 公共緩存
    'CACHE_COMMON_FIELD' => [
        'Common.Department',
        'Common.User',
        'Common.Job',
        'Common.StrategySetting',
        'Common.Public'
    ],
    // 应用标识对应的应用名称
    'IDENTIFIER_NAME' => [
        'news' => '新闻公告',
        'jobtrain' => '培训',
        'integral' => '积分',
        'exam' => '考试',
        'dailyreport' => '工作报告',
        'contact' => '通讯录',
        'activity' => '活动报名',
    ],

    // UC回调事件类型: 文本、关注、取消关注
    'UC_CALLBACK_MSG_TYPE_TEXT' => 'text',
    'UC_CALLBACK_MSG_TYPE_SUBSCRIBE' => 'subscribe',
    'UC_CALLBACK_MSG_TYPE_UNSUBSCRIBE' => 'unsubscribe',

    // 前台静态目录定义
    'STATICDIR' => '/misc/', // 静态目录位置
    'IMGDIR' => '/misc/images/', // 默认图标路径
    'CSSDIR' => '/misc/styles', // 样式文件路径
    'SCRIPTDIR' => '/misc/scripts/', // js 文件路径

    // 应用设定
    'APP_USE_NAMESPACE' => true, // 应用类库是否使用命名空间
    'APP_SUB_DOMAIN_DEPLOY' => false, // 是否开启子域名部署
    'APP_SUB_DOMAIN_RULES' => array(), // 子域名部署规则
    'APP_DOMAIN_SUFFIX' => '', // 域名后缀 如果是com.cn net.cn 之类的后缀必须设置
    'ACTION_SUFFIX' => '', // 操作方法后缀
    'MULTI_MODULE' => true, // 是否允许多模块 如果为false 则必须设置 DEFAULT_MODULE
    'MODULE_DENY_LIST' => array(
        'Common',
        'Cli',
        'Runtime'
    ),
    'CONTROLLER_LEVEL' => 2,
    'APP_AUTOLOAD_LAYER' => 'Controller,Model', // 自动加载的应用类库层 关闭APP_USE_NAMESPACE后有效
    'APP_AUTOLOAD_PATH' => '', // 自动加载的路径 关闭APP_USE_NAMESPACE后有效

    // Cookie设置
    'COOKIE_EXPIRE' => 864000, // Cookie有效期
    'COOKIE_DOMAIN' => 'vchangyi.com', // Cookie有效域名
    'COOKIE_PATH' => '/', // Cookie路径
    'COOKIE_PREFIX' => 'HR', // Cookie前缀 避免冲突
    'COOKIE_SECURE' => false, // Cookie安全传输
    'COOKIE_HTTPONLY' => '', // Cookie httponly设置
    'COOKIE_SECRET' => 'vVbAJ2vFzwB6qbvJsudcPFWpM4h97VQ4', // 微信端 Cookie 加密秘钥
    'COOKIE_USERDATA_EXPIRE' => 60 * 60 * 24 * 30, // 用户信息有效期

    // 默认设定
    'DEFAULT_M_LAYER' => 'Model', // 默认的模型层名称
    'DEFAULT_C_LAYER' => 'Controller', // 默认的控制器层名称
    'DEFAULT_V_LAYER' => 'View', // 默认的视图层名称
    'DEFAULT_LANG' => 'zh-cn', // 默认语言
    'DEFAULT_THEME' => '', // 默认模板主题名称
    'DEFAULT_MODULE' => 'Home', // 默认模块
    'DEFAULT_CONTROLLER' => 'Frontend/Index', // 默认控制器名称
    'DEFAULT_ACTION' => 'Index', // 默认操作名称
    'DEFAULT_CHARSET' => 'utf-8', // 默认输出编码
    'DEFAULT_TIMEZONE' => 'UTC', // 默认时区
    'DEFAULT_AJAX_RETURN' => 'JSON', // 默认AJAX 数据返回格式,可选JSON XML ...
    'DEFAULT_JSONP_HANDLER' => 'jsonpReturn', // 默认JSONP格式返回的处理方法
    'DEFAULT_FILTER' => '', // 默认参数过滤方法 用于I函数...

    // 数据库设置
    'DB_TYPE' => 'mysql', // 数据库类型
    'DB_HOST' => 'localhost', // 服务器地址
    'DB_NAME' => 'vchangyi_oa2', // 数据库名
    'DB_USER' => 'root', // 用户名
    'DB_PWD' => 'password', // 密码
    'DB_PORT' => '3306', // 端口
    'DB_PREFIX' => 'oa_', // 数据库表前缀
    'DB_PARAMS' => array(), // 数据库连接参数
    'DB_DEBUG' => true, // 数据库调试模式 开启后可以记录SQL日志
    'DB_FIELDS_CACHE' => true, // 启用字段缓存
    'DB_CHARSET' => 'utf8mb4', // 数据库编码默认采用utf8
    'DB_DEPLOY_TYPE' => 0, // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'DB_RW_SEPARATE' => false, // 数据库读写是否分离 主从式有效
    'DB_MASTER_NUM' => 1, // 读写分离后 主服务器数量
    'DB_SLAVE_NO' => '', // 指定从服务器序号

    // 数据缓存设置
    'DATA_CACHE_TIME' => 7200, // 数据缓存有效期 0表示永久缓存 (单位:秒)
    'DATA_CACHE_COMPRESS' => false, // 数据缓存是否压缩缓存
    'DATA_CACHE_CHECK' => false, // 数据缓存是否校验缓存
    'DATA_CACHE_PREFIX' => '', // 缓存前缀
    'DATA_CACHE_TYPE' => 'File', // 数据缓存类型,支持:File|Db|Apc|Memcache|Shmop|Sqlite|Xcache|Apachenote|Eaccelerator
    'DATA_CACHE_PATH' => TEMP_PATH, // 缓存路径设置 (仅对File方式缓存有效)
    'DATA_CACHE_KEY' => '', // 缓存文件KEY (仅对File方式缓存有效)
    'DATA_CACHE_BEST_KEY_SIZE' => 2000, // redis 单个key最优数组大小
    'DATA_CACHE_SUBDIR' => false, // 使用子目录缓存 (自动根据缓存标识的哈希创建子目录)
    'DATA_PATH_LEVEL' => 1, // 子目录缓存级别

    // redis 配置
    'REDIS_HOST' => '127.0.0.1',
    'REDIS_PORT' => '6379',
    'REDIS_PWD' => '',

    // 错误设置
    'ERROR_MESSAGE' => '页面错误！请稍后再试', // 错误显示信息,非调试模式有效
    'ERROR_PAGE' => '', // 错误定向页面
    'SHOW_ERROR_MSG' => false, // 显示错误信息
    'TRACE_MAX_RECORD' => 100, // 每个级别的错误信息 最大记录数

    // 日志设置
    'LOG_RECORD' => false, // 默认不记录日志
    'LOG_TYPE' => 'File', // 日志记录类型 默认为文件方式
    'LOG_LEVEL' => 'EMERG,ALERT,CRIT,ERR', // 允许记录的日志级别
    'LOG_FILE_SIZE' => 2097152, // 日志文件大小限制
    'LOG_EXCEPTION_RECORD' => false, // 是否记录异常信息日志

    // SESSION设置
    'SESSION_AUTO_START' => false, // 是否自动开启Session
    'SESSION_OPTIONS' => array(), // session 配置数组 支持type name id path expire domain 等参数
    'SESSION_TYPE' => '', // session hander类型 默认无需设置 除非扩展了session hander驱动
    'SESSION_PREFIX' => '', // session 前缀
                            // 'VAR_SESSION_ID' => 'session_id', //sessionID的提交变量

    // 模板引擎设置
    'TMPL_CONTENT_TYPE' => 'text/html', // 默认模板输出类型
    'TMPL_ACTION_ERROR' => THINK_PATH . 'Tpl/dispatch_jump.tpl', // 默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS' => THINK_PATH . 'Tpl/dispatch_jump.tpl', // 默认成功跳转对应的模板文件
    'TMPL_EXCEPTION_FILE' => THINK_PATH . 'Tpl/think_exception.tpl', // 异常页面的模板文件
    'TMPL_DETECT_THEME' => false, // 自动侦测模板主题
    'TMPL_TEMPLATE_SUFFIX' => '.tpl', // 默认模板文件后缀
    'TMPL_FILE_DEPR' => '/', // 模板文件CONTROLLER_NAME与ACTION_NAME之间的分割符

    // 布局设置
    'TMPL_ENGINE_TYPE' => 'Think', // 默认模板引擎 以下设置仅对使用Think模板引擎有效
    'TMPL_CACHFILE_SUFFIX' => '.php', // 默认模板缓存后缀
    'TMPL_DENY_FUNC_LIST' => 'echo,exit', // 模板引擎禁用函数
    'TMPL_DENY_PHP' => false, // 默认模板引擎是否禁用PHP原生代码
    'TMPL_L_DELIM' => '{', // 模板引擎普通标签开始标记
    'TMPL_R_DELIM' => '}', // 模板引擎普通标签结束标记
    'TMPL_VAR_IDENTIFY' => 'array', // 模板变量识别。留空自动判断,参数为'obj'则表示对象
    'TMPL_STRIP_SPACE' => true, // 是否去除模板文件里面的html空格与换行
    'TMPL_CACHE_ON' => true, // 是否开启模板编译缓存,设为false则每次都会重新编译
    'TMPL_CACHE_PREFIX' => '', // 模板缓存前缀标识，可以动态改变
    'TMPL_CACHE_TIME' => 0, // 模板缓存有效期 0 为永久，(以数字为值，单位:秒)
    'TMPL_LAYOUT_ITEM' => '{__CONTENT__}', // 布局模板的内容替换标识
    'LAYOUT_ON' => false, // 是否启用布局
    'LAYOUT_NAME' => 'layout', // 当前布局名称 默认为layout

    // Think模板引擎标签库相关设定
    'TAGLIB_BEGIN' => '<', // 标签库标签开始标记
    'TAGLIB_END' => '>', // 标签库标签结束标记
    'TAGLIB_LOAD' => true, // 是否使用内置标签库之外的其它标签库，默认自动检测
    'TAGLIB_BUILD_IN' => 'cx', // 内置标签库名称(标签使用不必指定标签库名称),以逗号分隔 注意解析顺序
    'TAGLIB_PRE_LOAD' => '', // 需要额外加载的标签库(须指定标签库名称)，多个以逗号分隔

    // URL设置
    'URL_CASE_INSENSITIVE' => true, // 默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_MODEL' => 1, // URL访问模式,可选参数0、1、2、3,代表以下四种模式：
                      // 0 (普通模式); 1 (PATHINFO 模式); 2 (REWRITE 模式); 3 (兼容模式) 默认为PATHINFO 模式
    'URL_PATHINFO_DEPR' => '/', // PATHINFO模式下，各参数之间的分割符号
    'URL_PATHINFO_FETCH' => 'ORIG_PATH_INFO,REDIRECT_PATH_INFO,REDIRECT_URL', // 用于兼容判断PATH_INFO 参数的SERVER替代变量列表
    'URL_REQUEST_URI' => 'REQUEST_URI', // 获取当前页面地址的系统变量 默认为REQUEST_URI
    'URL_HTML_SUFFIX' => 'html', // URL伪静态后缀设置
    'URL_DENY_SUFFIX' => 'ico|png|gif|jpg', // URL禁止访问的后缀设置
    'URL_PARAMS_BIND' => true, // URL变量绑定到Action方法参数
    'URL_PARAMS_BIND_TYPE' => 0, // URL变量绑定的类型 0 按变量名绑定 1 按变量顺序绑定
    'URL_PARAMS_FILTER' => false, // URL变量绑定过滤
    'URL_PARAMS_FILTER_TYPE' => '', // URL变量绑定过滤方法 如果为空 调用DEFAULT_FILTER
    'URL_ROUTER_ON' => false, // 是否开启URL路由
    'URL_ROUTE_RULES' => array(), // 默认路由规则 针对模块
    'URL_MAP_RULES' => array(), // URL映射定义规则

    // 系统变量名称设置
    'VAR_MODULE' => 'm', // 默认模块获取变量
    'VAR_ADDON' => 'addon', // 默认的插件控制器命名空间变量
    'VAR_CONTROLLER' => 'c', // 默认控制器获取变量
    'VAR_ACTION' => 'a', // 默认操作获取变量
    'VAR_AJAX_SUBMIT' => 'ajax', // 默认的AJAX提交变量
    'VAR_JSONP_HANDLER' => 'callback',
    'VAR_PATHINFO' => 's', // 兼容模式PATHINFO获取变量例如 ?s=/module/action/id/1 后面的参数取决于URL_PATHINFO_DEPR
    'VAR_TEMPLATE' => 't', // 默认模板切换变量
    'VAR_AUTO_STRING' => false, // 输入变量是否自动强制转换为字符串 如果开启则数组变量需要手动传入变量修饰符获取变量

    'HTTP_CACHE_CONTROL' => 'private', // 网页缓存控制
    'CHECK_APP_DIR' => true, // 是否检查应用目录是否创建
    'FILE_UPLOAD_TYPE' => 'Local', // 文件上传方式
    'DATA_CRYPT_TYPE' => 'Think', // 数据加密方式

    // 短网址
    'SHORTURL_APIURL' => 'http://99hr.com/yourls-api.php',
    'SHORTURL_USERNAME' => '99hr',
    'SHORTURL_PASSWORD' => '99hr_vchangyi_2016',
    'SHORTURL_TIMEOUT_SECOND' => 10,

    // 手机短信签名
    'SMS_SIGN' => '【畅移云工作】',

    // 文件转换接口地址
    'FILE_CONVERT_API_URL' => 'http://t-dcc.vchangyi.com',

    // 用户属性
    'USER_ATTRS' => include(dirname(__FILE__) . D_S . 'user_attrs.php'),

    // python socket
    'PYTHON_SOCKET_IP' => '127.0.0.1',
    'PYTHON_SOCKET_PORT' => '8237',

    // 资源鉴权 Cookie 密钥
    'RES_AUTH_SECRET' => 'VxDksHVCHanYiSMPtMVXdA==',

    // 资源鉴权 Cookie 名称
    'RES_AUTH_COOKIE_NAME' => 'resourceauth',

    // 访问统计日志相关配置
    'STAT_OPTIONS' => array(
        // 日志记录类型，File=文本格式
        'type' => 'File',
        // 存储配置
        'storage' => array(
            // 采用文本格式记录的相关配置
            'file' => array(
                // 日志记录位置（基于 Runtime 目录）
                'path' => 'Stat',
            )
        )
    ),

    // 指定企业微信应用对应的 secret，用来应对企业微信无法使用通讯录 secret 在手机端网页获取用户身份
	// 如果未定义，则表示使用传统的 企业号 方式获取
    // 格式为：
    /**
     * app_dir => [
     *  'app_id' => '引用 appid',
     *  'app_secret' => '应用 secret ',
     *  'corepid' => '企业 corepid'
     * ]
     * 如果 app_dir = default 表示通用的应用
     */

	/*
    'WXWORK_APP_SECRET' => array(
        'default' => array(
            'app_id' => '1000002',
            'app_secret' => '',
            'corpid' => '',
        ),
        'contact' => array(
            'app_id' => '1000004',
            'app_secret' => '',
            'corpid' => '',
        )
    )
	*/

);