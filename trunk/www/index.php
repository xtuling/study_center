<?php
/**
 * index.php
 * WEB 访问统一入口文件
 * Create By Deepseath
 * $Author$
 * $Id$
 */

// 检测 PHP 环境
if (version_compare(PHP_VERSION, '5.6.0', '<')) {
    die('require PHP > 5.6.0 !');
}

// 用于测试服务器上临时解决前端 ajax 请求跨域问题的
$referHost = '';
if (isset($_SERVER['HTTP_REFERER'])) {
    $url_parse = @parse_url($_SERVER['HTTP_REFERER']);
    $port = '';
    if (isset($url_parse['port']) && 80 != $url_parse['port']) {
        $port = ':' . $url_parse['port'];
    }
    $referHost = $url_parse['scheme'] . '://' . $url_parse['host'] . $port;
    @header("Access-Control-Allow-Origin: " . $url_parse['scheme'] . '://' . $url_parse['host'] . $port);
}
@header("Access-Control-Allow-Credentials: true");
@header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
//@header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');


// 动态获取当前应用目录名、企业目录名
$appDir = 'Public';
// 默认为本地开发 _SERVER['RUN_MODE'] = development
$qyDomain = 'local';
if (isset($_SERVER['REQUEST_URI'])) {
    // 如果不是开发模式，则读取当前企业目录和应用目录名
    list($_qyDomain, $_appDir) = explode('/', preg_replace('/\/+/', '/', trim($_SERVER['REQUEST_URI'], '/')) . '//');
    // 获取企业目录名
    if (preg_match('/^([a-z0-9]{4,32})$/i', $_qyDomain)) {
        $qyDomain = $_qyDomain;
    }

    // 获取应用目录
    if ($_appDir && preg_match('/^[a-z0-9_]+$/i', $_appDir)) {
        $appDir = $_appDir;
    } else {
        // 未指定应用目录 或 应用目录名不合法，则使用默认
        if ($_qyDomain) {
            $_SERVER['REQUEST_URI'] = preg_replace('/\/' . $_qyDomain . '\//i', '/', $_SERVER['REQUEST_URI']);
        }
        if ($_appDir) {
            $_SERVER['REQUEST_URI'] = preg_replace('/\/' . $_appDir . '\//i', '/', $_SERVER['REQUEST_URI']);
        }
        $_SERVER['REQUEST_URI'] = '/' . $qyDomain . '/' . $appDir . '/' . preg_replace('/\/+/', '/', trim($_SERVER['REQUEST_URI'], '/'));
    }

    unset($_appDir, $_qyDomain);
    $appDir = ucfirst($appDir);
    // 赋值真实的路径环境变量 并 重写路径信息
    $_SERVER['CY_PATH_INFO'] = (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : str_replace('?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
    $_SERVER['CY_REQUEST_URI'] = $_SERVER['REQUEST_URI'];
    $_SERVER['CY_PHP_SELF'] = $_SERVER['PHP_SELF'];
    $_SERVER['PATH_INFO'] = '/' . preg_replace('/^\/' . $qyDomain . '\/' . $appDir . '/i', '', $_SERVER['CY_PATH_INFO']);
    $_SERVER['PATH_INFO'] = preg_replace('/\/+/', '/', $_SERVER['PATH_INFO']);
    $_SERVER['REQUEST_URI'] = $_SERVER['PATH_INFO'];
    $_SERVER['PHP_SELF'] = $_SERVER['REQUEST_URI'];
}

// 当前企业域名
define('QY_DOMAIN', $qyDomain);
unset($qyDomain);

// 开启调试模式 建议开发阶段开启 部署阶段注释 或者 设为 false
define('APP_DEBUG', true);

// 为了兼容 Nginx 下的U方法
define('__APP__', '');

// 代码根目录路径
define('CODE_ROOT', dirname(__DIR__));

// 目录分隔符号
define('D_S', DIRECTORY_SEPARATOR);

// 框架所在目录
define('THINK_PATH', CODE_ROOT . D_S . 'ThinkPHP' . D_S);

// 应用目录
$app_path = CODE_ROOT . D_S . $appDir . D_S;
// 如果不存在，则指向到默认目录
if (!is_dir($app_path)) {
    $appDir = 'Public';
    $app_path = CODE_ROOT . D_S . 'Public' . D_S;
}

// 应用目录名称
define('APP_DIR', $appDir);
unset($appDir);

// 动态定义应用目录
define('APP_PATH', $app_path);
unset($app_path);

// 应用唯一标识名，默认同应用目录名一致，如果
$identifier_file = APP_PATH . D_S . 'app.php';
$identifier = '';
// 是否强制使用应用配置的应用唯一标识符（如果应用配置唯一标识符时候前面加“!”）
$identifierForce = false;
if (is_file($identifier_file)) {
    $_identifier = include($identifier_file);
    if (!empty($_identifier['identifier'])) {
        // 应用自定义的应用名
        $identifierForce = strpos($_identifier['identifier'], '!') !== false;
        $identifier = ltrim($_identifier['identifier'], '!');
    }
    unset($_identifier);
}
unset($identifier_file);

if (!$identifier) {
    $identifier = strtolower(APP_DIR);
}

// 自定义公共的应用标识符，这里覆盖上文定义的（如果上文未强制 $identifierForce）
$identiferConfigFile = THINK_PATH . 'Conf' . D_S . 'identifier.php';
if (!$identifierForce && is_file($identiferConfigFile)) {
    $identifierConfig = include($identiferConfigFile);
    if (!empty($identifierConfig['identifier'])) {
        if (stripos($identifierConfig['identifier'], '{') !== false) {
            $identifier = str_replace(['{', '}'], '', $identifierConfig['identifier']) . $identifier;
        } else {
            $identifier = $identifierConfig['identifier'];
        }
    }
    unset($identiferConfigFile);

    // 判断是否配置了应用特定自己的标识符
    if (!empty($identifierConfig['app'])) {
        $appDir = strtolower(APP_DIR);
        if (!empty($identifierConfig['app'][$appDir])) {
            $identifier = $identifierConfig['app'][$appDir];
        }
        unset($appDir);
    }
    unset($identifierConfig);
}
unset($app_path, $app_path, $identifierForce);

if ($identifier != 'public') {
    // 默认应用唯一标识名使用应用目录名
    // 当前请求的应用唯一标识符
    define('APP_IDENTIFIER', $identifier);
    // 当前请求的应用路径
    define('PLUGIN_PATH', CODE_ROOT . D_S . ucfirst(APP_IDENTIFIER) . D_S);
}
unset($identifier);

// 引入 ThinkPHP入 口文件
require THINK_PATH . 'ThinkPHP.php';
