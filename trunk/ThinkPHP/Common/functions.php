<?php
use Think\Build;
use Think\Exception;

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
 * Think 系统函数库
 */

/**
 * 获取和设置配置参数 支持批量定义
 *
 * @param string|array $name    配置变量
 * @param mixed        $value   配置值
 * @param mixed        $default 默认值
 *
 * @return mixed
 */
function C($name = null, $value = null, $default = null)
{

    static $_config = array();
    // 无参数时获取所有
    if (empty($name)) {
        return $_config;
    }

    // 优先执行设置获取或赋值
    if (is_string($name)) {
        if (!strpos($name, '.')) {
            $name = strtoupper($name);
            if (is_null($value)) {
                return isset($_config[$name]) ? $_config[$name] : $default;
            }

            $_config[$name] = $value;

            return null;
        }

        // 二维数组设置和获取支持
        $name = explode('.', $name);
        $name[0] = strtoupper($name[0]);
        if (is_null($value)) {
            return isset($_config[$name[0]][$name[1]]) ? $_config[$name[0]][$name[1]] : $default;
        }

        $_config[$name[0]][$name[1]] = $value;

        return null;
    }

    // 批量设置
    if (is_array($name)) {
        $_config = array_merge($_config, array_change_key_case($name, CASE_UPPER));

        return null;
    }

    return null; // 避免非法参数
}

/**
 * 加载配置文件 支持格式转换 仅支持一级配置
 *
 * @param string $file  配置文件名
 * @param string $parse 配置解析方法 有些格式需要用户自己解析
 *
 * @return array
 */
function load_config($file, $parse = CONF_PARSE)
{

    $ext = pathinfo($file, PATHINFO_EXTENSION);
    switch ($ext) {
        case 'php':
            return include $file;
        case 'ini':
            return parse_ini_file($file);
        case 'yaml':
            return yaml_parse_file($file);
        case 'xml':
            return (array)simplexml_load_file($file);
        case 'json':
            return json_decode(file_get_contents($file), true);
        default:
            if (function_exists($parse)) {
                return $parse($file);
            } else {
                E(L('_NOT_SUPPORT_') . ':' . $ext);
            }
    }
}

/**
 * 解析yaml文件返回一个数组
 *
 * @param string $file 配置文件名
 *
 * @return array
 */
if (!function_exists('yaml_parse_file')) {

    function yaml_parse_file($file)
    {

        vendor('spyc.Spyc');

        return Spyc::YAMLLoad($file);
    }
}

/**
 * 抛出异常处理
 *
 * @param string  $msg  异常消息
 * @param integer $code 异常代码 默认为0
 *
 * @throws Think\Exception
 * @return void
 */
function E($msg, $code = 0)
{

    throw new Exception($msg, $code);
}

/**
 * 记录和统计时间（微秒）和内存使用情况
 * 使用方法:
 * <pre>
 * G('begin'); // 记录开始标记位
 * // ... 区间运行代码
 * G('end'); // 记录结束标签位
 * echo G('begin','end',6); // 统计区间运行时间 精确到小数后6位
 * echo G('begin','end','m'); // 统计区间内存使用情况
 * 如果end标记位没有定义，则会自动以当前作为标记位
 * 其中统计内存使用需要 MEMORY_LIMIT_ON 常量为true才有效
 * </pre>
 *
 * @param string         $start 开始标签
 * @param string         $end   结束标签
 * @param integer|string $dec   小数位或者m
 *
 * @return mixed
 */
function G($start, $end = '', $dec = 4)
{

    static $_info = array();
    static $_mem = array();
    if (is_float($end)) { // 记录时间
        $_info[$start] = $end;
    } elseif (!empty($end)) { // 统计时间和内存使用
        if (!isset($_info[$end])) {
            $_info[$end] = microtime(true);
        }

        if (MEMORY_LIMIT_ON && $dec == 'm') {
            if (!isset($_mem[$end])) {
                $_mem[$end] = memory_get_usage();
            }

            return number_format(($_mem[$end] - $_mem[$start]) / 1024);
        } else {
            return number_format(($_info[$end] - $_info[$start]), $dec);
        }
    } else { // 记录时间和内存使用
        $_info[$start] = microtime(true);
        if (MEMORY_LIMIT_ON) {
            $_mem[$start] = memory_get_usage();
        }
    }

    return null;
}

/**
 * 获取和设置语言定义(不区分大小写)
 *
 * @param string|array $name  语言变量
 * @param mixed        $value 语言值或者变量
 *
 * @return mixed
 */
function L($name = null, $value = null)
{

    static $_lang = array();
    // 空参数返回所有定义
    if (empty($name)) {
        return $_lang;
    }

    // 判断语言获取(或设置)
    // 若不存在,直接返回全大写$name
    if (is_string($name)) {
        $name = strtoupper($name);
        if (is_null($value)) {
            return isset($_lang[$name]) ? $_lang[$name] : $name;
        } elseif (is_array($value)) {
            // 支持变量
            $replace = array_keys($value);
            foreach ($replace as &$v) {
                $v = '{$' . $v . '}';
            }

            return str_replace($replace, $value, isset($_lang[$name]) ? $_lang[$name] : $name);
        }

        $_lang[$name] = $value; // 语言定义
        return null;
    }

    // 批量定义
    if (is_array($name)) {
        $_lang = array_merge($_lang, array_change_key_case($name, CASE_UPPER));
    }

    return null;
}

/**
 * 添加和获取页面Trace记录
 *
 * @param string  $value  变量
 * @param string  $label  标签
 * @param string  $level  日志级别
 * @param boolean $record 是否记录日志
 *
 * @return void|array
 */
function trace($value = '[think]', $label = '', $level = 'DEBUG', $record = false)
{

    return Think\Think::trace($value, $label, $level, $record);
}

/**
 * 编译文件
 *
 * @param string $filename 文件名
 *
 * @return string
 */
function compile($filename)
{

    $content = php_strip_whitespace($filename);
    $content = trim(substr($content, 5));
    // 替换预编译指令
    $content = preg_replace('/\/\/\[RUNTIME\](.*?)\/\/\[\/RUNTIME\]/s', '', $content);
    if (0 === strpos($content, 'namespace')) {
        $content = preg_replace('/namespace\s(.*?);/', 'namespace \\1{', $content, 1);
    } else {
        $content = 'namespace {' . $content;
    }

    if ('?>' == substr($content, -2)) {
        $content = substr($content, 0, -2);
    }

    return $content . '}';
}

/**
 * 获取模版文件 格式 资源://模块@主题/控制器/操作
 *
 * @param string $template 模版资源地址
 * @param string $layer    视图层（目录）名称
 *
 * @return string
 */
function T($template = '', $layer = '')
{

    // 解析模版资源地址
    if (false === strpos($template, '://')) {
        $template = 'http://' . str_replace(':', '/', $template);
    }

    $info = parse_url($template);
    $file = $info['host'] . (isset($info['path']) ? $info['path'] : '');
    $module = isset($info['user']) ? $info['user'] . '/' : MODULE_NAME . '/';
    $extend = $info['scheme'];
    $layer = $layer ? $layer : C('DEFAULT_V_LAYER');

    // 获取当前主题的模版路径
    $auto = C('AUTOLOAD_NAMESPACE');
    if ($auto && isset($auto[$extend])) { // 扩展资源
        $baseUrl = $auto[$extend] . $module . $layer . '/';
    } elseif (C('VIEW_PATH')) {
        // 改变模块视图目录
        $baseUrl = C('VIEW_PATH');
    } elseif (defined('TMPL_PATH')) {
        // 指定全局视图目录
        $baseUrl = TMPL_PATH . $module;
    } else {
        $baseUrl = APP_PATH . $module . $layer . '/';
    }

    // 获取主题
    $theme = substr_count($file, '/') < 2 ? C('DEFAULT_THEME') : '';

    // 分析模板文件规则
    $depr = C('TMPL_FILE_DEPR');
    if ('' == $file) {
        // 如果模板文件名为空 按照默认规则定位
        $file = CONTROLLER_NAME . $depr . ACTION_NAME;
    } elseif (false === strpos($file, '/')) {
        $file = CONTROLLER_NAME . $depr . $file;
    } elseif ('/' != $depr) {
        $file = substr_count($file, '/') > 1 ? substr_replace($file, $depr, strrpos($file, '/'), 1) : str_replace('/', $depr, $file);
    }

    return $baseUrl . ($theme ? $theme . '/' : '') . $file . C('TMPL_TEMPLATE_SUFFIX');
}

/**
 * 获取输入参数 支持过滤和默认值
 * 使用方法:
 * <pre>
 * I('id',0); 获取id参数 自动判断get或者post
 * I('post.name','','htmlspecialchars'); 获取$_POST['name']
 * I('get.'); 获取$_GET
 * </pre>
 *
 * @param string $name    变量的名称 支持指定类型
 * @param mixed  $default 不存在的时候默认值
 * @param mixed  $filter  参数过滤方法
 * @param mixed  $datas   要获取的额外数据源
 *
 * @return mixed
 */
function I($name, $default = '', $filter = null, $datas = null)
{

    static $_PUT = null;
    if (strpos($name, '/')) {
        // 指定修饰符
        list ($name, $type) = explode('/', $name, 2);
    } elseif (C('VAR_AUTO_STRING')) {
        // 默认强制转换为字符串
        $type = 's';
    }

    if (strpos($name, '.')) {
        // 指定参数来源
        list ($method, $name) = explode('.', $name, 2);
    } else { // 默认为自动判断
        $method = 'param';
    }

    switch (strtolower($method)) {
        case 'get':
            $input = &$_GET;
            break;
        case 'post':
            $input = &$_POST;
            break;
        case 'put':
            if (is_null($_PUT)) {
                parse_str(file_get_contents('php://input'), $_PUT);
            }
            $input = $_PUT;
            break;
        case 'param':
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'POST':
                    $input = $_POST;
                    break;
                case 'PUT':
                    if (is_null($_PUT)) {
                        parse_str(file_get_contents('php://input'), $_PUT);
                    }
                    $input = $_PUT;
                    break;
                default:
                    $input = $_GET;
            }
            break;
        case 'path':
            $input = array();
            if (!empty($_SERVER['PATH_INFO'])) {
                $depr = C('URL_PATHINFO_DEPR');
                $input = explode($depr, trim($_SERVER['PATH_INFO'], $depr));
            }
            break;
        case 'request':
            $input = &$_REQUEST;
            break;
        case 'session':
            $input = &$_SESSION;
            break;
        case 'cookie':
            $input = &$_COOKIE;
            break;
        case 'server':
            $input = &$_SERVER;
            break;
        case 'globals':
            $input = &$GLOBALS;
            break;
        case 'data':
            $input = &$datas;
            break;
        default:
            return null;
    }

    if ('' == $name) { // 获取全部变量
        $data = $input;
        $filters = isset($filter) ? $filter : C('DEFAULT_FILTER');
        if ($filters) {
            if (is_string($filters)) {
                $filters = explode(',', $filters);
            }

            foreach ($filters as $filter) {
                $data = array_map_recursive($filter, $data); // 参数过滤
            }
        }
    } elseif (isset($input[$name])) { // 取值操作
        $data = $input[$name];
        $filters = isset($filter) ? $filter : C('DEFAULT_FILTER');
        if ($filters) {
            if (is_string($filters)) {
                if (0 === strpos($filters, '/')) {
                    if (1 !== preg_match($filters, (string)$data)) {
                        // 支持正则验证
                        return isset($default) ? $default : null;
                    }
                } else {
                    $filters = explode(',', $filters);
                }
            } elseif (is_int($filters)) {
                $filters = array(
                    $filters
                );
            }

            if (is_array($filters)) {
                foreach ($filters as $filter) {
                    if (function_exists($filter)) {
                        $data = is_array($data) ? array_map_recursive($filter, $data) : $filter($data); // 参数过滤
                    } else {
                        $data = filter_var($data, is_int($filter) ? $filter : filter_id($filter));
                        if (false === $data) {
                            return isset($default) ? $default : null;
                        }
                    }
                }
            }
        }

        if (!empty($type)) {
            switch (strtolower($type)) {
                case 'a': // 数组
                    $data = (array)$data;
                    break;
                case 'd': // 数字
                    $data = (int)$data;
                    break;
                case 'f': // 浮点
                    $data = (float)$data;
                    break;
                case 'b': // 布尔
                    $data = (boolean)$data;
                    break;
                case 's': // 字符串
                default:
                    $data = (string)$data;
            }
        }
    } else { // 变量默认值
        $data = isset($default) ? $default : null;
    }

    is_array($data) && array_walk_recursive($data, 'think_filter');

    return $data;
}

function array_map_recursive($filter, $data)
{

    $result = array();
    foreach ($data as $key => $val) {
        $result[$key] = is_array($val) ? array_map_recursive($filter, $val) : call_user_func($filter, $val);
    }

    return $result;
}

/**
 * 设置和获取统计数据
 * 使用方法:
 * <pre>
 * N('db',1); // 记录数据库操作次数
 * N('read',1); // 记录读取次数
 * echo N('db'); // 获取当前页面数据库的所有操作次数
 * echo N('read'); // 获取当前页面读取次数
 * </pre>
 *
 * @param string  $key  标识位置
 * @param integer $step 步进值
 * @param boolean $save 是否保存结果
 *
 * @return mixed
 */
function N($key, $step = 0, $save = false)
{

    static $_num = array();
    if (!isset($_num[$key])) {
        $_num[$key] = (false !== $save) ? S('N_' . $key) : 0;
    }

    if (empty($step)) {
        return $_num[$key];
    } else {
        $_num[$key] = $_num[$key] + (int)$step;
    }

    if (false !== $save) { // 保存结果
        S('N_' . $key, $_num[$key], $save);
    }

    return null;
}

/**
 * 字符串命名风格转换
 * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
 *
 * @param string  $name 字符串
 * @param integer $type 转换类型
 *
 * @return string
 */
function parse_name($name, $type = 0)
{

    if ($type) {
        return ucfirst(preg_replace_callback('/_([a-zA-Z])/', function ($match) {

            return strtoupper($match[1]);
        }, $name));
    } else {
        return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
    }
}

/**
 * 优化的require_once
 *
 * @param string $filename 文件地址
 *
 * @return boolean
 */
function require_cache($filename)
{

    static $_importFiles = array();
    if (!isset($_importFiles[$filename])) {
        if (file_exists_case($filename)) {
            require $filename;
            $_importFiles[$filename] = true;
        } else {
            $_importFiles[$filename] = false;
        }
    }

    return $_importFiles[$filename];
}

/**
 * 区分大小写的文件存在判断
 *
 * @param string $filename 文件地址
 *
 * @return boolean
 */
function file_exists_case($filename)
{

    if (is_file($filename)) {
        if (IS_WIN && APP_DEBUG) {
            if (basename(realpath($filename)) != basename($filename)) {
                return false;
            }
        }

        return true;
    }

    return false;
}

/**
 * 导入所需的类库 同java的Import 本函数有缓存功能
 *
 * @param string $class   类库命名空间字符串
 * @param string $baseUrl 起始路径
 * @param string $ext     导入的文件扩展名
 *
 * @return boolean
 */
function import($class, $baseUrl = '', $ext = EXT)
{

    static $_file = array();
    $searchs = array(
        '.',
        '#'
    );
    $replaces = array(
        '/',
        '.'
    );
    $class = str_replace($searchs, $replaces, $class);
    if (isset($_file[$class . $baseUrl])) {
        return true;
    } else {
        $_file[$class . $baseUrl] = true;
    }

    $class_strut = explode('/', $class);
    if (empty($baseUrl)) {
        $systemDirs = array(
            'Think',
            'Org',
            'Behavior',
            'Com',
            'Vendor'
        );
        if ('@' == $class_strut[0] || MODULE_NAME == $class_strut[0]) {
            // 加载当前模块的类库
            $baseUrl = MODULE_PATH;
            $class = substr_replace($class, '', 0, strlen($class_strut[0]) + 1);
        } elseif ('Common' == $class_strut[0]) {
            // 加载公共模块的类库
            $baseUrl = COMMON_PATH;
            $class = substr($class, 7);
        } elseif (in_array($class_strut[0], $systemDirs) || is_dir(LIB_PATH . $class_strut[0])) {
            // 系统类库包和第三方类库包
            $baseUrl = LIB_PATH;
        } else { // 加载其他模块的类库
            $baseUrl = APP_PATH;
        }
    }

    if (substr($baseUrl, -1) != '/') {
        $baseUrl .= '/';
    }

    $classfile = $baseUrl . $class . $ext;
    if (!class_exists(basename($class), false)) {
        // 如果类不存在 则导入类库文件
        return require_cache($classfile);
    }

    return null;
}

/**
 * 基于命名空间方式导入函数库
 * load('@.Util.Array')
 *
 * @param string $name    函数库命名空间字符串
 * @param string $baseUrl 起始路径
 * @param string $ext     导入的文件扩展名
 *
 * @return void
 */
function load($name, $baseUrl = '', $ext = '.php')
{

    $name = str_replace(array(
        '.',
        '#'
    ), array(
        '/',
        '.'
    ), $name);
    if (empty($baseUrl)) {
        if (0 === strpos($name, '@/')) { // 加载当前模块函数库
            $baseUrl = MODULE_PATH . 'Common/';
            $name = substr($name, 2);
        } else { // 加载其他模块函数库
            $array = explode('/', $name);
            $baseUrl = APP_PATH . array_shift($array) . '/Common/';
            $name = implode('/', $array);
        }
    }

    if (substr($baseUrl, -1) != '/') {
        $baseUrl .= '/';
    }

    require_cache($baseUrl . $name . $ext);
}

/**
 * 快速导入第三方框架类库 所有第三方框架的类库文件统一放到 系统的Vendor目录下面
 *
 * @param string $class   类库
 * @param string $baseUrl 基础目录
 * @param string $ext     类库后缀
 *
 * @return boolean
 */
function vendor($class, $baseUrl = '', $ext = '.php')
{

    if (empty($baseUrl)) {
        $baseUrl = VENDOR_PATH;
    }

    return import($class, $baseUrl, $ext);
}

/**
 * 实例化模型类 格式 [资源://][模块/]模型
 *
 * @param string $name  资源地址
 * @param string $layer 模型层名称
 *
 * @return Think\Model
 */
function D($name = '', $layer = '')
{

    if (empty($name)) {
        return new Think\Model();
    }

    static $_model = array();
    $layer = $layer ?: C('DEFAULT_M_LAYER');
    if (isset($_model[$name . $layer])) {
        return $_model[$name . $layer];
    }

    $class = parse_res_name($name, $layer);
    if (class_exists($class)) {
        $model = new $class(basename($name));
    } elseif (false === strpos($name, '/')) {
        // 启用自动生成 MODULE 层 by zhuxun37

        if (APP_DEBUG && cfg('CREATE_MODEL_ON') && I('request._create')) {
            $paths = explode('/', str_replace('\\', '/', $class));
            $model = array_pop($paths);
            Build::buildModel($paths[0], $model);
            $model = new $class($model);
        } else {
            // 自动加载公共模块下面的模型
            if (!C('APP_USE_NAMESPACE')) {
                import('Common/' . $layer . '/' . $class);
            } else {
                $class = '\\Common\\' . $layer . '\\' . $name . $layer;
            }

            $model = class_exists($class) ? new $class($name) : new Think\Model($name);
        }
    } else {
        // 启用自动生成 MODULE 层 by zhuxun37
        if (APP_DEBUG && cfg('CREATE_MODULE_ON') && I('request._create')) {
            $paths = explode('/', str_replace('\\', '/', $class));
            $model = array_pop($paths);
            Build::buildModel($paths[0], $model);
            $model = new $class($model);
        } else {
            Think\Log::record('D方法实例化没找到模型类' . $class, Think\Log::NOTICE);
            $model = new Think\Model(basename($name));
        }
    }

    $_model[$name . $layer] = $model;

    return $model;
}

/**
 * 实例化一个没有模型文件的Model
 *
 * @param string $name        Model名称 支持指定基础模型 例如 MongoModel:User
 * @param string $tablePrefix 表前缀
 * @param mixed  $connection  数据库连接信息
 *
 * @return Think\Model
 */
function M($name = '', $tablePrefix = '', $connection = '')
{

    static $_model = array();
    if (strpos($name, ':')) {
        list ($class, $name) = explode(':', $name);
    } else {
        $class = 'Think\\Model';
    }

    $guid = (is_array($connection) ? implode('', $connection) : $connection) . $tablePrefix . $name . '_' . $class;
    if (!isset($_model[$guid])) {
        $_model[$guid] = new $class($name, $tablePrefix, $connection);
    }

    return $_model[$guid];
}

/**
 * 解析资源地址并导入类库文件
 * 例如 module/controller addon://module/behavior
 *
 * @param string  $name  资源地址 格式：[扩展://][模块/]资源名
 * @param string  $layer 分层名称
 * @param integer $level 控制器层次
 *
 * @return string
 */
function parse_res_name($name, $layer, $level = 1)
{

    if (strpos($name, '://')) { // 指定扩展资源
        list ($extend, $name) = explode('://', $name);
    } else {
        $extend = '';
    }

    if (strpos($name, '/') && substr_count($name, '/') >= $level) { // 指定模块
        list ($module, $name) = explode('/', $name, 2);
    } else {
        $module = defined('MODULE_NAME') ? MODULE_NAME : '';
    }

    $array = explode('/', $name);
    if (!C('APP_USE_NAMESPACE')) {
        $class = parse_name($name, 1);
        import($module . '/' . $layer . '/' . $class . $layer);
    } else {
        $class = $module . '\\' . $layer;
        foreach ($array as $name) {
            $class .= '\\' . parse_name($name, 1);
        }

        // 导入资源类库
        if ($extend) { // 扩展资源
            $class = $extend . '\\' . $class;
        }
    }

    return $class . $layer;
}

/**
 * 用于实例化访问控制器
 *
 * @param string $name 控制器名
 * @param string $path 控制器命名空间（路径）
 *
 * @return false
 */
function controller($name, $path = '')
{

    $layer = C('DEFAULT_C_LAYER');
    if (!C('APP_USE_NAMESPACE')) {
        $class = parse_name($name, 1) . $layer;
        import(MODULE_NAME . '/' . $layer . '/' . $class);
    } else {
        $class = ($path ? basename(ADDON_PATH) . '\\' . $path : MODULE_NAME) . '\\' . $layer;
        $array = explode('/', $name);
        foreach ($array as $name) {
            $class .= '\\' . parse_name($name, 1);
        }

        $class .= $layer;
    }

    if (class_exists($class)) {
        return new $class();
    } else {
        // 启用自动生成控制器 by zhuxun37
        if (APP_DEBUG && cfg('CREATE_CONTROLLER_ON') && I('request._create')) {
            $paths = explode('\\', preg_replace("/{$layer}$/i", '', $class));
            $module = array_shift($paths);
            unset($paths[0]);
            Build::buildController($module, implode('/', $paths));

            return new $class();
        }
        // end
        return false;
    }
}

/**
 * 实例化多层控制器 格式：[资源://][模块/]控制器
 *
 * @param string  $name
 *            资源地址
 * @param string  $layer
 *            控制层名称
 * @param integer $level
 *            控制器层次
 *
 * @return false
 */
function A($name, $layer = '', $level = 0)
{

    static $_action = array();
    $layer = $layer ?: C('DEFAULT_C_LAYER');
    $level = $level ?: ($layer == C('DEFAULT_C_LAYER') ? C('CONTROLLER_LEVEL') : 1);
    if (isset($_action[$name . $layer])) {
        return $_action[$name . $layer];
    }

    $class = parse_res_name($name, $layer, $level);
    if (class_exists($class)) {
        $action = new $class();
        $_action[$name . $layer] = $action;

        return $action;
    } else {
        return false;
    }
}

/**
 * 远程调用控制器的操作方法 URL 参数格式 [资源://][模块/]控制器/操作
 *
 * @param string       $url
 *            调用地址
 * @param string|array $vars
 *            调用参数 支持字符串和数组
 * @param string       $layer
 *            要调用的控制层名称
 *
 * @return mixed
 */
function R($url, $vars = array(), $layer = '')
{

    $info = pathinfo($url);
    $action = $info['basename'];
    $module = $info['dirname'];
    $class = A($module, $layer);
    if ($class) {
        if (is_string($vars)) {
            parse_str($vars, $vars);
        }

        return call_user_func_array(array(
            &$class,
            $action . C('ACTION_SUFFIX')
        ), $vars);
    } else {
        return false;
    }
}

/**
 * 处理标签扩展
 *
 * @param string $tag
 *            标签名称
 * @param mixed  $params
 *            传入参数
 *
 * @return void
 */
function tag($tag, &$params = null)
{

    \Think\Hook::listen($tag, $params);
}

/**
 * 执行某个行为
 *
 * @param string $name   行为名称
 * @param string $tag    标签名称（行为类无需传入）
 * @param Mixed  $params 传入的参数
 *
 * @return void
 */
function B($name, $tag = '', &$params = null)
{

    if ('' == $tag) {
        $name .= 'Behavior';
    }

    \Think\Hook::exec($name, $tag, $params);
    return;
}

/**
 * 去除代码中的空白和注释
 *
 * @param string $content
 *            代码内容
 *
 * @return string
 */
function strip_whitespace($content)
{

    $stripStr = '';
    // 分析php源码
    $tokens = token_get_all($content);
    $last_space = false;
    for ($i = 0, $j = count($tokens); $i < $j; $i++) {
        if (is_string($tokens[$i])) {
            $last_space = false;
            $stripStr .= $tokens[$i];
        } else {
            switch ($tokens[$i][0]) {
                // 过滤各种PHP注释
                case T_COMMENT:
                case T_DOC_COMMENT:
                    break;
                // 过滤空格
                case T_WHITESPACE:
                    if (!$last_space) {
                        $stripStr .= ' ';
                        $last_space = true;
                    }
                    break;
                case T_START_HEREDOC:
                    $stripStr .= "<<<THINK\n";
                    break;
                case T_END_HEREDOC:
                    $stripStr .= "THINK;\n";
                    for ($k = $i + 1; $k < $j; $k++) {
                        if (is_string($tokens[$k]) && $tokens[$k] == ';') {
                            $i = $k;
                            break;
                        } else if ($tokens[$k][0] == T_CLOSE_TAG) {
                            break;
                        }
                    }
                    break;
                default:
                    $last_space = false;
                    $stripStr .= $tokens[$i][1];
            }
        }
    }

    return $stripStr;
}

/**
 * 自定义异常处理
 *
 * @param string  $msg
 *            异常消息
 * @param string  $type
 *            异常类型 默认为Think\Exception
 * @param integer $code
 *            异常代码 默认为0
 *
 * @return void
 */
function throw_exception($msg, $type = 'Think\\Exception', $code = 0)
{

    Think\Log::record('建议使用E方法替代throw_exception', Think\Log::NOTICE);
    if (class_exists($type, false)) {
        throw new $type($msg, $code);
    } else {
        Think\Think::halt($msg); // 异常类型不存在则输出错误信息字串
    }
}

/**
 * 浏览器友好的变量输出
 *
 * @param mixed   $var
 *            变量
 * @param boolean $echo
 *            是否输出 默认为True 如果为false 则返回输出字符串
 * @param string  $label
 *            标签 默认为空
 * @param boolean $strict
 *            是否严谨 默认为true
 *
 * @return void|string
 */
function dump($var, $echo = true, $label = null, $strict = true)
{

    $label = ($label === null) ? '' : rtrim($label) . ' ';
    if (!$strict) {
        if (ini_get('html_errors')) {
            $output = print_r($var, true);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        } else {
            $output = $label . print_r($var, true);
        }
    } else {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        if (!extension_loaded('xdebug')) {
            $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        }
    }

    if ($echo) {
        echo($output);

        return null;
    } else {
        return $output;
    }
}

/**
 * 设置当前页面的布局
 *
 * @param string|false $layout
 *            布局名称 为false的时候表示关闭布局
 *
 * @return void
 */
function layout($layout)
{

    if (false !== $layout) {
        // 开启布局
        C('LAYOUT_ON', true);
        if (is_string($layout)) { // 设置新的布局模板
            C('LAYOUT_NAME', $layout);
        }
    } else { // 临时关闭布局
        C('LAYOUT_ON', false);
    }
}

/**
 * URL组装 支持不同URL模式
 *
 * @param string         $url    URL表达式，格式：'[模块/控制器/操作#锚点@域名]?参数1=值1&参数2=值2...'
 * @param string|array   $vars   传入的参数，支持数组和字符串
 * @param string|boolean $suffix 伪静态后缀，默认为true表示获取配置值
 * @param boolean        $domain 是否显示域名
 *
 * @return string
 */
function U($url = '', $vars = '', $suffix = true, $domain = false)
{

    // 解析URL
    $info = parse_url($url);
    $url = !empty($info['path']) ? $info['path'] : ACTION_NAME;
    if (isset($info['fragment'])) { // 解析锚点
        $anchor = $info['fragment'];
        if (false !== strpos($anchor, '?')) { // 解析参数
            list ($anchor, $info['query']) = explode('?', $anchor, 2);
        }

        if (false !== strpos($anchor, '@')) { // 解析域名
            list ($anchor, $host) = explode('@', $anchor, 2);
        }
    } elseif (false !== strpos($url, '@')) { // 解析域名
        list ($url, $host) = explode('@', $info['path'], 2);
    }

    // 解析子域名
    if (isset($host)) {
        $domain = $host . (strpos($host, '.') ? '' : strstr($_SERVER['HTTP_HOST'], '.'));
    } elseif ($domain === true) {
        $domain = $_SERVER['HTTP_HOST'];
        if (C('APP_SUB_DOMAIN_DEPLOY')) { // 开启子域名部署
            $domain = $domain == 'localhost' ? 'localhost' : 'www' . strstr($_SERVER['HTTP_HOST'], '.');
            // '子域名'=>array('模块[/控制器]');
            foreach (C('APP_SUB_DOMAIN_RULES') as $key => $rule) {
                $rule = is_array($rule) ? $rule[0] : $rule;
                if (false === strpos($key, '*') && 0 === strpos($url, $rule)) {
                    $domain = $key . strstr($domain, '.'); // 生成对应子域名
                    $url = substr_replace($url, '', 0, strlen($rule));
                    break;
                }
            }
        }
    }

    // 解析参数
    if (is_string($vars)) { // aaa=1&bbb=2 转换成数组
        parse_str($vars, $vars);
    } elseif (!is_array($vars)) {
        $vars = array();
    }

    if (isset($info['query'])) { // 解析地址里面参数 合并到vars
        parse_str($info['query'], $params);
        $vars = array_merge($params, $vars);
    }

    // URL组装
    $depr = C('URL_PATHINFO_DEPR');
    $urlCase = C('URL_CASE_INSENSITIVE');
    if ($url) {
        if (0 === strpos($url, '/')) { // 定义路由
            $route = true;
            $url = substr($url, 1);
            if ('/' != $depr) {
                $url = str_replace('/', $depr, $url);
            }
        } else {
            if ('/' != $depr) { // 安全替换
                $url = str_replace('/', $depr, $url);
            }

            // 解析模块、控制器和操作
            $url = trim($url, $depr);
            $path = explode($depr, $url);
            $var = array();
            $varModule = C('VAR_MODULE');
            $varController = C('VAR_CONTROLLER');
            $varAction = C('VAR_ACTION');
            $var[$varAction] = !empty($path) ? array_pop($path) : ACTION_NAME;
            $var[$varController] = !empty($path) ? array_pop($path) : CONTROLLER_NAME;
            $maps = C('URL_ACTION_MAP');
            if ($maps) {
                if (isset($maps[strtolower($var[$varController])])) {
                    $maps = $maps[strtolower($var[$varController])];
                    $action = array_search(strtolower($var[$varAction]), $maps);
                    if ($action) {
                        $var[$varAction] = $action;
                    }
                }
            }
            $maps = C('URL_CONTROLLER_MAP');
            if ($maps) {
                $controller = array_search(strtolower($var[$varController]), $maps);
                if ($controller) {
                    $var[$varController] = $controller;
                }
            }

            if ($urlCase) {
                $var[$varController] = parse_name($var[$varController]);
            }

            $module = '';

            if (!empty($path)) {
                $var[$varModule] = implode($depr, $path);
            } else {
                if (C('MULTI_MODULE')) {
                    if (MODULE_NAME != C('DEFAULT_MODULE') || !C('MODULE_ALLOW_LIST')) {
                        $var[$varModule] = MODULE_NAME;
                    }
                }
            }
            $maps = C('URL_MODULE_MAP');
            if ($maps) {
                $_module = array_search(strtolower($var[$varModule]), $maps);
                if ($_module) {
                    $var[$varModule] = $_module;
                }
            }

            if (isset($var[$varModule])) {
                $module = $var[$varModule];
                unset($var[$varModule]);
            }
        }
    }

    if (C('URL_MODEL') == 0) { // 普通模式URL转换
        $url = __APP__ . '?' . C('VAR_MODULE') . "={$module}&" . http_build_query(array_reverse($var));
        if ($urlCase) {
            $url = strtolower($url);
        }

        if (!empty($vars)) {
            $vars = http_build_query($vars);
            $url .= '&' . $vars;
        }
    } else { // PATHINFO模式或者兼容URL模式
        if (isset($route)) {
            $url = __APP__ . '/' . rtrim($url, $depr);
        } else {
            $module = (defined('BIND_MODULE') && BIND_MODULE == $module) ? '' : $module;
            $url = __APP__ . '/' . ($module ? $module . MODULE_PATHINFO_DEPR : '') . implode($depr, array_reverse($var));
        }

        if ($urlCase) {
            $url = strtolower($url);
        }

        if (!empty($vars)) { // 添加参数
            foreach ($vars as $var => $val) {
                if ('' !== trim($val)) {
                    $url .= $depr . $var . $depr . urlencode($val);
                }
            }
        }

        if ($suffix) {
            $suffix = $suffix === true ? C('URL_HTML_SUFFIX') : $suffix;
            $pos = strpos($suffix, '|');
            if ($pos) {
                $suffix = substr($suffix, 0, $pos);
            }

            if ($suffix && '/' != substr($url, -1)) {
                $url .= '.' . ltrim($suffix, '.');
            }
        }
    }

    if (isset($anchor)) {
        $url .= '#' . $anchor;
    }

    if ($domain) {
        $url = (is_ssl() ? 'https://' : 'http://') . $domain . $url;
    }

    return $url;
}

/**
 * 渲染输出Widget
 *
 * @param string $name Widget名称
 * @param array  $data 传入的参数
 *
 * @return void
 */
function W($name, $data = array())
{

    return R($name, $data, 'Widget');
}

/**
 * 判断是否SSL协议
 *
 * @return boolean
 */
function is_ssl()
{

    if (isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))) {
        return true;
    } elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
        return true;
    }

    return false;
}

/**
 * URL重定向
 *
 * @param string  $url  重定向的URL地址
 * @param integer $time 重定向的等待时间（秒）
 * @param string  $msg  重定向前的提示信息
 *
 * @return void
 */
function redirect($url, $time = 0, $msg = '')
{

    // 多行URL地址支持
    $url = str_replace(array(
        "\n",
        "\r"
    ), '', $url);
    if (empty($msg)) {
        $msg = "系统将在{$time}秒之后自动跳转到{$url}！";
    }

    if (!headers_sent()) {
        // redirect
        if (0 === $time) {
            header('Location: ' . $url);
        } else {
            header("refresh:{$time};url={$url}");
            echo($msg);
        }

        exit();
    } else {
        $str = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
        if ($time != 0) {
            $str .= $msg;
        }

        exit($str);
    }
}

/**
 * 缓存管理
 *
 * @param mixed $name    缓存名称，如果为数组表示进行缓存设置
 * @param mixed $value   缓存值
 * @param mixed $options 缓存参数
 *
 * @return mixed
 */
function S($name, $value = '', $options = null)
{

    static $cache = '';
    if (is_array($options)) {
        // 缓存操作的同时初始化
        $type = isset($options['type']) ? $options['type'] : '';
        $cache = Think\Cache::getInstance($type, $options);
    } elseif (is_array($name)) { // 缓存初始化
        $type = isset($name['type']) ? $name['type'] : '';
        $cache = Think\Cache::getInstance($type, $name);

        return $cache;
    } elseif (empty($cache)) { // 自动初始化
        $cache = Think\Cache::getInstance();
    }

    if ('' === $value) { // 获取缓存
        return $cache->get($name);
    } elseif (is_null($value)) { // 删除缓存
        return $cache->rm($name);
    } else { // 缓存数据
        if (is_array($options)) {
            $expire = isset($options['expire']) ? $options['expire'] : null;
        } else {
            $expire = is_numeric($options) ? $options : null;
        }

        // 写入缓存名称数据表时去掉应用标识, Public 没有表, 所以过滤
        if (APP_DIR != 'Public') {
            call_user_func('syscache_record', $name);
        }

        return $cache->set($name, $value, $expire);
    }
}

/**
 * 快速文件数据读取和保存 针对简单类型数据 字符串、数组
 *
 * @param string $name  缓存名称
 * @param mixed  $value 缓存值
 * @param string $path  缓存路径
 *
 * @return mixed
 */
function F($name, $value = '', $path = DATA_PATH)
{

    static $_cache = array();
    $filename = $path . $name . '.php';
    if ('' !== $value) {
        if (is_null($value)) {
            // 删除缓存
            if (false !== strpos($name, '*')) {
                return false;
            } else {
                unset($_cache[$name]);
                return Think\Storage::unlink($filename, 'F');
            }
        } else {
            Think\Storage::put($filename, serialize($value), 'F');
            // 缓存数据
            $_cache[$name] = $value;
            return null;
        }
    }

    // 获取缓存数据
    if (isset($_cache[$name])) {
        return $_cache[$name];
    }

    if (Think\Storage::has($filename, 'F')) {
        $value = unserialize(Think\Storage::read($filename, 'F'));
        $_cache[$name] = $value;
    } else {
        $value = false;
    }

    return $value;
}

/**
 * 记录缓存
 *
 * @param string $cachename 缓存名称
 *
 * @return boolean
 */
function syscache_record($cachename)
{

    static $s_caches = null;

    $serv_sys = D('Common/Syscache', 'Service');
    if (null == $s_caches) {
        $s_caches = $serv_sys->list_all();
        if (!empty($s_caches)) {
            $s_caches = array_combine_by_key($s_caches, 'name');
        }
    }

    // 如果不存在, 则
    if (empty($s_caches[$cachename])) {
        // 记录缓存
        $cache = array('name' => $cachename, 'type' => 1, 'data' => '');
        $s_caches[$cachename] = $cache;
        // 数据入库
        $serv_sys->insert($cache);
    }

    return true;
}

/**
 * 根据PHP各种类型变量生成唯一标识号
 *
 * @param mixed $mix
 *            变量
 *
 * @return string
 */
function to_guid_string($mix)
{

    if (is_object($mix)) {
        return spl_object_hash($mix);
    } elseif (is_resource($mix)) {
        $mix = get_resource_type($mix) . strval($mix);
    } else {
        $mix = serialize($mix);
    }

    return md5($mix);
}

/**
 * XML编码
 *
 * @param mixed  $data
 *            数据
 * @param string $root
 *            根节点名
 * @param string $item
 *            数字索引的子节点名
 * @param string $attr
 *            根节点属性
 * @param string $id
 *            数字索引子节点key转换的属性名
 * @param string $encoding
 *            数据编码
 *
 * @return string
 */
function xml_encode($data, $root = 'think', $item = 'item', $attr = '', $id = 'id', $encoding = 'utf-8')
{

    if (is_array($attr)) {
        $_attr = array();
        foreach ($attr as $key => $value) {
            $_attr[] = "{$key}=\"{$value}\"";
        }

        $attr = implode(' ', $_attr);
    }

    $attr = trim($attr);
    $attr = empty($attr) ? '' : " {$attr}";
    $xml = "<?xml version=\"1.0\" encoding=\"{$encoding}\"?>";
    $xml .= "<{$root}{$attr}>";
    $xml .= data_to_xml($data, $item, $id);
    $xml .= "</{$root}>";

    return $xml;
}

/**
 * 数据XML编码
 *
 * @param mixed  $data
 *            数据
 * @param string $item
 *            数字索引时的节点名称
 * @param string $id
 *            数字索引key转换为的属性名
 *
 * @return string
 */
function data_to_xml($data, $item = 'item', $id = 'id')
{

    $xml = $attr = '';
    foreach ($data as $key => $val) {
        if (is_numeric($key)) {
            $id && $attr = " {$id}=\"{$key}\"";
            $key = $item;
        }

        $xml .= "<{$key}{$attr}>";
        $xml .= (is_array($val) || is_object($val)) ? data_to_xml($val, $item, $id) : $val;
        $xml .= "</{$key}>";
    }

    return $xml;
}

/**
 * session管理函数
 *
 * @param string|array $name
 *            session名称 如果为数组则表示进行session设置
 * @param mixed        $value
 *            session值
 *
 * @return mixed
 */
function session($name = '', $value = '')
{

    $prefix = C('SESSION_PREFIX');
    if (is_array($name)) { // session初始化 在session_start 之前调用
        if (isset($name['prefix'])) {
            C('SESSION_PREFIX', $name['prefix']);
        }

        if (C('VAR_SESSION_ID') && isset($_REQUEST[C('VAR_SESSION_ID')])) {
            session_id($_REQUEST[C('VAR_SESSION_ID')]);
        } elseif (isset($name['id'])) {
            session_id($name['id']);
        }

        if ('common' == APP_MODE) { // 其它模式可能不支持
            ini_set('session.auto_start', 0);
        }

        if (isset($name['name'])) {
            session_name($name['name']);
        }

        if (isset($name['path'])) {
            session_save_path($name['path']);
        }

        if (isset($name['domain'])) {
            ini_set('session.cookie_domain', $name['domain']);
        }

        if (isset($name['expire'])) {
            ini_set('session.gc_maxlifetime', $name['expire']);
            ini_set('session.cookie_lifetime', $name['expire']);
        }

        if (isset($name['use_trans_sid'])) {
            ini_set('session.use_trans_sid', $name['use_trans_sid'] ? 1 : 0);
        }

        if (isset($name['use_cookies'])) {
            ini_set('session.use_cookies', $name['use_cookies'] ? 1 : 0);
        }

        if (isset($name['cache_limiter'])) {
            session_cache_limiter($name['cache_limiter']);
        }

        if (isset($name['cache_expire'])) {
            session_cache_expire($name['cache_expire']);
        }

        if (isset($name['type'])) {
            C('SESSION_TYPE', $name['type']);
        }

        if (C('SESSION_TYPE')) { // 读取session驱动
            $type = C('SESSION_TYPE');
            $class = strpos($type, '\\') ? $type : 'Think\\Session\\Driver\\' . ucwords(strtolower($type));
            $hander = new $class();
            session_set_save_handler(array(
                &$hander,
                "open"
            ), array(
                &$hander,
                "close"
            ), array(
                &$hander,
                "read"
            ), array(
                &$hander,
                "write"
            ), array(
                &$hander,
                "destroy"
            ), array(
                &$hander,
                "gc"
            ));
        }

        // 启动session
        if (C('SESSION_AUTO_START')) {
            session_start();
        }
    } elseif ('' === $value) {
        if ('' === $name) {
            // 获取全部的session
            return $prefix ? $_SESSION[$prefix] : $_SESSION;
        } elseif (0 === strpos($name, '[')) { // session 操作
            if ('[pause]' == $name) { // 暂停session
                session_write_close();
            } elseif ('[start]' == $name) { // 启动session
                session_start();
            } elseif ('[destroy]' == $name) { // 销毁session
                $_SESSION = array();
                session_unset();
                session_destroy();
            } elseif ('[regenerate]' == $name) { // 重新生成id
                session_regenerate_id();
            }
        } elseif (0 === strpos($name, '?')) { // 检查session
            $name = substr($name, 1);
            if (strpos($name, '.')) { // 支持数组
                list ($name1, $name2) = explode('.', $name);

                return $prefix ? isset($_SESSION[$prefix][$name1][$name2]) : isset($_SESSION[$name1][$name2]);
            } else {
                return $prefix ? isset($_SESSION[$prefix][$name]) : isset($_SESSION[$name]);
            }
        } elseif (is_null($name)) { // 清空session
            if ($prefix) {
                unset($_SESSION[$prefix]);
            } else {
                $_SESSION = array();
            }
        } elseif ($prefix) { // 获取session
            if (strpos($name, '.')) {
                list ($name1, $name2) = explode('.', $name);

                return isset($_SESSION[$prefix][$name1][$name2]) ? $_SESSION[$prefix][$name1][$name2] : null;
            } else {
                return isset($_SESSION[$prefix][$name]) ? $_SESSION[$prefix][$name] : null;
            }
        } else {
            if (strpos($name, '.')) {
                list ($name1, $name2) = explode('.', $name);

                return isset($_SESSION[$name1][$name2]) ? $_SESSION[$name1][$name2] : null;
            } else {
                return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
            }
        }
    } elseif (is_null($value)) { // 删除session
        if (strpos($name, '.')) {
            list ($name1, $name2) = explode('.', $name);
            if ($prefix) {
                unset($_SESSION[$prefix][$name1][$name2]);
            } else {
                unset($_SESSION[$name1][$name2]);
            }
        } else {
            if ($prefix) {
                unset($_SESSION[$prefix][$name]);
            } else {
                unset($_SESSION[$name]);
            }
        }
    } else { // 设置session
        if (strpos($name, '.')) {
            list ($name1, $name2) = explode('.', $name);
            if ($prefix) {
                $_SESSION[$prefix][$name1][$name2] = $value;
            } else {
                $_SESSION[$name1][$name2] = $value;
            }
        } else {
            if ($prefix) {
                $_SESSION[$prefix][$name] = $value;
            } else {
                $_SESSION[$name] = $value;
            }
        }
    }

    return null;
}

/**
 * Cookie 设置、获取、删除
 *
 * @param string $name
 *            cookie名称
 * @param mixed  $value
 *            cookie值
 * @param mixed  $option
 *            cookie参数
 *
 * @return mixed
 */
function cookie($name = '', $value = '', $option = null)
{

    // 默认设置
    $config = array(
        'prefix' => C('COOKIE_PREFIX'), // cookie 名称前缀
        'expire' => C('COOKIE_EXPIRE'), // cookie 保存时间
        'path' => C('COOKIE_PATH'), // cookie 保存路径
        'domain' => C('COOKIE_DOMAIN'), // cookie 有效域名
        'secure' => C('COOKIE_SECURE'), // cookie 启用安全传输
        'httponly' => C('COOKIE_HTTPONLY')
    ); // httponly设置
    // 参数设置(会覆盖黙认设置)
    if (!is_null($option)) {
        if (is_numeric($option)) {
            $option = array(
                'expire' => $option
            );
        } elseif (is_string($option)) {
            parse_str($option, $option);
        }

        $config = array_merge($config, array_change_key_case($option));
    }

    if (!empty($config['httponly'])) {
        ini_set("session.cookie_httponly", 1);
    }

    // 清除指定前缀的所有cookie
    if (is_null($name)) {
        if (empty($_COOKIE)) {
            return null;
        }

        // 要删除的cookie前缀，不指定则删除config设置的指定前缀
        $prefix = empty($value) ? $config['prefix'] : $value;
        if (!empty($prefix)) { // 如果前缀为空字符串将不作处理直接返回
            foreach ($_COOKIE as $key => $val) {
                if (0 === stripos($key, $prefix)) {
                    setcookie($key, '', time() - 3600, $config['path'], $config['domain'], $config['secure'], $config['httponly']);
                    unset($_COOKIE[$key]);
                }
            }
        }

        return null;
    } elseif ('' === $name) {
        // 获取全部的cookie
        return $_COOKIE;
    }

    $name = $config['prefix'] . str_replace('.', '_', $name);
    if ('' === $value) {
        if (isset($_COOKIE[$name])) {
            $value = $_COOKIE[$name];
            if (0 === strpos($value, 'think:')) {
                $value = substr($value, 6);

                return array_map('urldecode', json_decode(MAGIC_QUOTES_GPC ? stripslashes($value) : $value, true));
            } else {
                return $value;
            }
        } else {
            return null;
        }
    } else {
        if (is_null($value)) {
            setcookie($name, '', time() - 3600, $config['path'], $config['domain'], $config['secure'], $config['httponly']);
            unset($_COOKIE[$name]); // 删除指定cookie
        } else {
            // 设置cookie
            if (is_array($value)) {
                $value = 'think:' . json_encode(array_map('urlencode', $value));
            }

            $expire = !empty($config['expire']) ? time() + intval($config['expire']) : 0;
            setcookie($name, $value, $expire, $config['path'], $config['domain'], $config['secure'], $config['httponly']);
            $_COOKIE[$name] = $value;
        }
    }

    return null;
}

/**
 * 加载动态扩展文件
 *
 * @var string $path 文件路径
 * @return void
 */
function load_ext_file($path)
{

    // 加载自定义外部文件
    $files = C('LOAD_EXT_FILE');
    if ($files) {
        $files = explode(',', $files);
        foreach ($files as $file) {
            $file = $path . 'Common/' . $file . '.php';
            if (is_file($file)) {
                include $file;
            } elseif ('___' == substr($file, -3)) { // add by zhuxun37, 系统配置文件的特殊标记字符"___"
                $commonFile = ROOT_PATH . 'Common/Common/Common/' . substr($file, 0, -3) . '.php';
                if (is_file($commonFile)) {
                    include $commonFile;
                }
            }
        }
    }

    // 加载自定义的动态配置文件
    $configs = C('LOAD_EXT_CONFIG');
    if ($configs) {
        if (is_string($configs)) {
            $configs = explode(',', $configs);
        }

        foreach ($configs as $key => $config) {
            $file = is_file($config) ? $config : $path . 'Conf/' . $config . CONF_EXT;
            if (is_file($file)) {
                is_numeric($key) ? C(load_config($file)) : C($key, load_config($file));
            } elseif ('___' == substr($file, -3)) { // add by zhuxun37, 系统配置文件的特殊标记字符"___"
                $commonFile = ROOT_PATH . 'Common/Common/Conf/' . substr($config, 0, -3) . CONF_EXT;
                if (is_file($commonFile)) {
                    is_numeric($key) ? C(load_config($commonFile)) : C($key, load_config($commonFile));
                }
            }
        }
    }
}

/**
 * 获取客户端IP地址
 *
 * @param integer $type
 *            返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv
 *            是否进行高级模式获取（有可能被伪装）
 *
 * @return mixed
 */
function get_client_ip($type = 0, $adv = false)
{

    $type = $type ? 1 : 0;
    static $ip = null;
    if ($ip !== null) {
        return $ip[$type];
    }

    if ($adv) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) {
                unset($arr[$pos]);
            }

            $ip = trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    // IP地址合法验证
    $long = sprintf("%u", ip2long($ip));
    $ip = $long ? array(
        $ip,
        $long
    ) : array(
        '0.0.0.0',
        0
    );

    return $ip[$type];
}

/**
 * 发送HTTP状态
 *
 * @param integer $code
 *            状态码
 *
 * @return void
 */
function send_http_status($code)
{

    static $_status = array(

        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',

        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',

        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Moved Temporarily ', // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',

        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',

        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',

        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    );

    if (isset($_status[$code])) {
        header('HTTP/1.1 ' . $code . ' ' . $_status[$code]);
        // 确保FastCGI模式下正常
        header('Status:' . $code . ' ' . $_status[$code]);
    }
}

function think_filter(&$value)
{

    // 过滤查询特殊字符
    if (preg_match('/^(EXP|NEQ|GT|EGT|LT|ELT|OR|XOR|LIKE|NOTLIKE|NOT BETWEEN|NOTBETWEEN|BETWEEN|NOTIN|NOT IN|IN)$/i', $value)) {
        $value .= ' ';
    }
}

// 不区分大小写的in_array实现
function in_array_case($value, $array)
{

    return in_array(strtolower($value), array_map('strtolower', $array));
}

// by zhuxun, begin.
/**
 * 获取和设置配置参数 支持批量定义
 *
 * @param string|array $name
 *            配置变量
 * @param mixed        $value
 *            配置值
 * @param mixed        $default
 *            默认值
 *
 * @return mixed
 */
function cfg($name = null, $value = null, $default = null)
{

    return C($name, $value, $default);
}

/**
 * 把字串中字母转成小写(重写 rstrtolower);
 *
 * @param string $str 字串;
 *
 * @return string
 */
function rstrtolower($str)
{

    return strtr($str, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz");
}

/**
 * 把字串中字母转成大写(重写 rstrtoupper);
 *
 * @param string $str 字串;
 *
 * @return string;
 */
function rstrtoupper($str)
{

    return strtr($str, "abcdefghijklmnopqrstuvwxyz", "ABCDEFGHIJKLMNOPQRSTUVWXYZ");
}

/**
 * 重写 base64_encode 方法
 *
 * @param string $string
 *            字串
 *
 * @return mixed
 */
function rbase64_encode($string)
{

    $data = base64_encode($string);
    $data = str_replace(array(
        '+',
        '/',
        '='
    ), array(
        '-',
        '_',
        ''
    ), $data);

    return $data;
}

/**
 * 重写 base64_decode 方法
 *
 * @param string $string
 *            字串
 *
 * @return string
 */
function rbase64_decode($string)
{

    $data = str_replace(array(
        '-',
        '_'
    ), array(
        '+',
        '/'
    ), $string);
    $mod4 = strlen($data) % 4;
    if ($mod4) {
        $data .= substr('====', $mod4);
    }

    return base64_decode($data);
}

/**
 * 把数据转成数字
 *
 * @param * $int 传入的数据
 * @param bool $allowarray 是否允许数组
 *
 * @return int
 */
function rintval($int, $allowarray = false)
{

    // 如果是标量
    $ret = is_scalar($int) ? intval($int) : 0;
    if ($int == $ret || (!$allowarray && is_array($int))) {
        return $ret;
    }

    // 如果允许数组
    if ($allowarray && is_array($int)) {
        // 遍历数组
        foreach ($int as &$v) {
            $v = rintval($v, true);
        }

        return $int;
    } elseif ($int <= 0xffffffff) { // 如果数值小于32位
        $l = strlen($int);
        $m = substr($int, 0, 1) == '-' ? 1 : 0;
        // 是正常的数值
        if (($l - $m) === strspn($int, '0987654321', $m)) {
            return $int;
        }
    }

    return $ret;
}

/**
 * 创建目录
 *
 * @param string  $dir       目录
 * @param int     $mode      权限字串
 * @param boolean $makeindex 是否创建默认索引文件
 *
 * @return bool
 */
function rmkdir($dir, $mode = 0777, $makeindex = true)
{

    if (!is_dir($dir)) { // 如果非目录
        rmkdir(dirname($dir), $mode, $makeindex);
        @mkdir($dir, $mode);
        // 如果需要创建索引文件
        if (!empty($makeindex)) {
            @touch($dir . '/index.html');
            @chmod($dir . '/index.html', 0777);
        }
    }

    return true;
}

/**
 * 删除反斜杠
 *
 * @param mixed $string
 *            待处理数据
 *
 * @return array|string
 */
function rstripslashes($string)
{

    // 如果为空或是数字
    if (empty($string) || is_numeric($string)) {
        return $string;
    }

    if (is_array($string)) { // 如果是数组
        // 遍历数据, 逐个过滤
        foreach ($string as $key => $val) {
            $string[$key] = rstripslashes($val);
        }
    } else { // 如果是字串
        $string = stripslashes($string);
    }

    return $string;
}

/**
 * 数据过滤
 *
 * @param mixed $string
 *            待处理数据
 *
 * @return mixed
 */
function raddslashes($string)
{

    if (is_array($string)) { // 如果是数组
        // 先取出所有键值
        $keys = array_keys($string);
        // 遍历整个数组
        foreach ($keys as $key) {
            $val = $string[$key];
            unset($string[$key]);
            $string[addslashes($key)] = raddslashes($val);
        }
    } else { // 如果是字串
        $string = addslashes($string);
    }

    return $string;
}

/**
 * 把一些预定义字符转成HTML实体
 *
 * @param mixed  $string
 *            待处理数据
 * @param string $flags
 *            如何编码单/双引号
 *
 * @return mixed
 */
function rhtmlspecialchars($string, $flags = null)
{

    if (is_array($string)) { // 如果是数组
        // 遍历数组, 嵌套处理
        foreach ($string as $key => $val) {
            $string[$key] = rhtmlspecialchars($val, $flags);
        }
    } else {
        // 如果是数值
        if (is_numeric($string)) {
            return $string;
        }

        // 如果未如何编码单/双引号, 则
        if ($flags === null) {
            // 正则替换
            $searchs = array(
                '&',
                '"',
                '<',
                '>'
            );
            $replaces = array(
                '&amp;',
                '&quot;',
                '&lt;',
                '&gt;'
            );
            $string = str_replace($searchs, $replaces, $string);
            if (strpos($string, '&amp;#') !== false) {
                $string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', $string);
            }
        } else {
            // 判断 PHP 版本
            if (PHP_VERSION < '5.4.0') {
                $string = htmlspecialchars($string, $flags);
            } else {
                if (strtolower(CHARSET) == 'utf-8') {
                    $charset = 'UTF-8';
                } else {
                    $charset = 'ISO-8859-1';
                }

                $string = htmlspecialchars($string, $flags, $charset);
            }
        }
    }

    return $string;
}

/**
 * 时间转换为毫秒
 *
 * @param int $ts 时间戳
 *
 * @return int
 */
function to_milli_time($ts)
{

    if (0 >= ($ts >> 32)) {
        $ts = $ts * 1000;
    }

    return $ts;
}

/**
 * 时间转换为秒
 *
 * @param int $ts 时间戳
 *
 * @return int
 */
function to_second_time($ts)
{

    if (0 < ($ts >> 32)) {
        $ts = $ts / 1000;
    }

    return $ts;
}

/**
 * 把时间戳转换成指定格式
 *
 * @param int    $timestamp  时间戳
 * @param string $format     格式
 * @param string $timeoffset 时区
 * @param string $uformat
 *
 * @return string;
 */
function rgmdate($timestamp, $format = 'zx', $timeoffset = '9999', $uformat = '')
{

    static $dformat, $tformat, $dtformat, $offset, $lang;
    // 如果未指定格式
    if ($dformat === null) {
        $dformat = cfg('DATE_FORMAT');
        $tformat = cfg('TIME_FORMAT');
        $dtformat = $dformat . ' ' . $tformat;
        $lang = L('DATE');
        $offset = cfg('TIME_OFFSET');
        // 默认时区为 +8
        if (!$offset) {
            $offset = 8;
        }
    }

    // 时间戳转换为秒为单位
    $timestamp = to_second_time($timestamp);

    // 剔除首尾空格
    $format = trim($format);
    // 时区偏移
    $timeoffset = $timeoffset == 9999 ? $offset : $timeoffset;
    $timestamp += $timeoffset * 3600;
    // 判断用户所需格式
    $format = (empty($format) || $format == 'zx') ? $dtformat : ($format == 'z' ? $dformat : ($format == 'x' ? $tformat : $format));
    if ($format == 'u') {
        // 今天的起始时间戳(即: 00:00:00)
        $today_ts = NOW_TIME - (NOW_TIME + $timeoffset * 3600) % 86400 + $timeoffset * 3600;
        $s = gmdate(!$uformat ? $dtformat : $uformat, $timestamp);
        // 今天已逝去的时间
        $time = NOW_TIME + $timeoffset * 3600 - $timestamp;
        // 如果当前时间戳就是当天之内
        if ($timestamp >= $today_ts) {
            if ($time > 3600) { // 如果在1个小时以上
                return intval($time / 3600) . '&nbsp;' . $lang['HOUR'] . $lang['BEFORE'];
            } elseif ($time > 1800) { // 如果在半个小时以上
                return $lang['HALF'] . $lang['HOUR'] . $lang['BEFORE'];
            } elseif ($time > 60) { // 如果在1分钟以上
                return intval($time / 60) . '&nbsp;' . $lang['MIN'] . $lang['BEFORE'];
            } elseif ($time > 0) { // 如果在1分钟以内
                return $time . '&nbsp;' . $lang['SEC'] . $lang['BEFORE'];
            } elseif ($time == 0) { // 当前
                return $lang['NOW'];
            } else {
                return $s;
            }
        } elseif (($days = intval(($today_ts - $timestamp) / 86400)) >= 0 && $days < 7) { // 一周之内
            if ($days == 0) { // 当天
                return $lang['YDAY'] . '&nbsp;' . gmdate($tformat, $timestamp);
            } elseif ($days == 1) { // 昨天
                return $lang['BYDAY'] . '&nbsp;' . gmdate($tformat, $timestamp);
            } else {
                return ($days + 1) . '&nbsp;' . $lang['DAY'] . $lang['BEFORE'];
            }
        } else {
            return $s;
        }
    } else { // 默认时间格式
        $format = trim($format);
        if (!$format) {
            $format = 'Y-m-d H:i';
        }

        return gmdate($format, $timestamp);
    }
}

/**
 * 把数组合并成一个字串(会过滤单/双引号)
 *
 * @param array $array 数组
 *
 * @return string|number
 */
function rimplode($array)
{

    if (!empty($array)) {
        $array = array_map('addslashes', $array);

        return "'" . implode("','", is_array($array) ? $array : array(
                $array
            )) . "'";
    } else {
        return 0;
    }
}

/**
 * 生成随机字串
 *
 * @param int $length  随机字串长度
 * @param int $numeric 是否为数字字串
 *
 * @return bool
 */
function random($length, $numeric = 0)
{

    // 生成随机数种子
    $seed = base_convert(md5(microtime() . $_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
    $seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . rstrtoupper($seed));
    // 如果只是需要数字
    if ($numeric) {
        $hash = '';
    } else { // 生成字串
        // 随机生成一个首字母
        $hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
        $length--;
    }

    $max = strlen($seed) - 1;
    // 逐个生成字符
    for ($i = 0; $i < $length; $i++) {
        $hash .= $seed{mt_rand(0, $max)};
    }

    return $hash;
}

/**
 * 判断文件或是路径是否可写;
 *
 * @param string $filepath
 *
 * @return bool 可写则返回 true;
 */
function ris_writable($filepath)
{

    $unlink = false;
    // 判断是否以 / 结尾
    '/' == substr($filepath, -1) && $filepath = substr($filepath, 0, -1);
    // 如果是目录
    if (is_dir($filepath)) {
        $unlink = true;
        mt_srand((double)microtime() * 1000000);
        $filepath = $filepath . '/vcy_' . uniqid(mt_rand()) . '.tmp';
    }

    // 打开文件
    $fp = @fopen($filepath, 'ab');
    // 失败, 则返回 false
    if (false === $fp) {
        return false;
    }

    // 关闭文件
    fclose($fp);
    // 删除临时测试文件
    if ($unlink) {
        @unlink($filepath);
    }

    return true;
}

/**
 * 读取指定文件;
 *
 * @param string $filename
 * @param string $method
 *
 * @return string 返回读取的数据;
 */
function rfread($filename, $method = 'rb')
{

    check_filepath($filename);
    $filedata = '';
    // 如果打开成功
    $handle = @fopen($filename, $method);
    if ($handle) {
        flock($handle, LOCK_SH);
        $filedata = @fread($handle, filesize($filename));
        fclose($handle);
    }

    return $filedata;
}

/**
 * 把数据写入文件;
 *
 * @param string  $filename 文件名
 * @param string  $data     数据
 * @param string  $method   fopen参数
 * @param boolean $iflock   是否需要锁
 * @param boolean $check    是否检查文件路径
 * @param boolean $chmod    是否修改权限
 *
 * @return bool
 */
function rfwrite($filename, $data, $method = 'rb+', $iflock = true, $check = true, $chmod = true)
{

    $check && check_filepath($filename);
    // 判断文件是否可写
    if (false == ris_writable($filename)) {
        \Think\Log::record('Can not write to cache files, please check directory ' . $filename . ' .');
    }

    // 生成文件
    touch($filename);
    // 打开文件
    $handle = fopen($filename, $method);
    // 如果需要文件锁, 则
    $iflock && flock($handle, LOCK_EX);
    // 写入数据
    fwrite($handle, $data);
    $method == 'rb+' && ftruncate($handle, strlen($data));
    // 关闭文件
    fclose($handle);
    // 修改权限
    $chmod && @chmod($filename, 0777);

    return true;
}

/**
 * 检查文件的路径是否合法;
 *
 * @param string  $filename
 * @param boolean $ifcheck
 *            -> 可能会造成跨目录的BUG);
 *
 * @return string 返回文件路径;
 */
function check_filepath($filename, $ifcheck = true)
{

    $tmpname = strtolower($filename);
    $arr = array(
        'http://'
    );
    // 剔除掉 ..
    $ifcheck && array_push($arr, '..');
    if (str_replace($arr, '', $tmpname) != $tmpname) {
        E('_ERROR_FORBIDDEN_');

        return false;
    }

    return $filename;
}

/**
 * 格式化文件的大小;
 *
 * @param int $filesize
 *
 * @return string 返回格式化后的字串；
 */
function size_count($filesize)
{

    if (1073741824 <= $filesize) {
        $filesize = (round($filesize / 1073741824 * 100) / 100) . ' G';
    } elseif (1048576 <= $filesize) {
        $filesize = (round($filesize / 1048576 * 100) / 100) . ' M';
    } elseif (1024 <= $filesize) {
        $filesize = (round($filesize / 1024 * 100) / 100) . ' K';
    } else {
        $filesize = $filesize . ' bytes';
    }

    return $filesize;
}

/**
 * 文件尺寸描述转为bytes整数值
 *
 * @param string $val
 *
 * @return number
 */
function count_size($val)
{

    $unit = '';
    $value = 0;
    // 如果不是数字
    if (preg_match('/^(\d+)(.*)/s', trim($val), $match)) {
        $unit = strtolower(trim($match[2]));
        $value = intval(trim($match[1]));
    }

    // 如果单位是 bytes
    if ($unit == 'bytes' || empty($unit)) {
        return $value;
    }

    // 这里需要注意字节转换的代码写法!!!
    switch ($unit) {
        case 't':
            $value *= 1024;
        case 'g':
            $value *= 1024;
        case 'm':
            $value *= 1024;
        case 'k':
            $value *= 1024;
    }

    return $value;
}

/**
 * 计算目录的大小;
 *
 * @param string $dir 目录
 *
 * @return int
 */
function dir_size($dir)
{

    // 打开目录
    $dh = opendir($dir);
    $size = 0;
    // 遍历所有目录
    $file = readdir($dh);
    while ($file) {
        // 如果是当前/上级目录
        if ($file != '.' && $file != '..') {
            $path = $dir . "/" . $file;
            // 如果是目录
            if (@is_dir($path)) {
                $size += dir_size($path);
            } else { // 非目录, 则直接计算大小
                $size += filesize($path);
            }
        }
    }

    @closedir($dh);

    return $size;
}

/**
 * 转换编码
 *
 * @param mixed  $var
 * @param string $from
 * @param string $to
 *
 * @return mixed
 */
function riconv($var, $from = 'UTF-8', $to = 'GBK')
{

    // 如果没有指定默认处理方法, 则
    if (strpos($to, '//') === false) {
        $to = $to . '//IGNORE';
    }

    switch (gettype($var)) {
        case 'integer':
        case 'boolean':
        case 'float':
        case 'double':
        case 'NULL':
            return $var;
        case 'string':
            return @iconv($from, $to, $var);
        case 'object':

            // 如果为对象, 则取出所有值
            $vars = array_keys(get_object_vars($var));
            // 遍历成员
            foreach ($vars as $key) {
                $var->$key = riconv($var->$key, $from, $to);
            }

            return $var;
        case 'array':

            // 遍历数组
            foreach ($var as $k => $v) {
                $k2 = riconv($k, $from, $to);
                // 如果键值和转换后的不相等, 则剔除
                if ($k != $k2) {
                    unset($var[$k]);
                }

                $var[$k2] = riconv($v, $from, $to);
            }

            return $var;
        default:
            return '';
    }
}

/**
 * json_encode2 将变量转为 json 编码字符串
 *
 * @param mixed $value
 * @param int   $options
 *            同内置函数的第二个参数参量，默认为：0，或JSON_*_*
 *
 * @return string
 */
function rjson_encode($value, $options = 0)
{

    // 如果数据为对象
    if (is_object($value)) {
        $value = get_object_vars($value);
    }

    $value = _urlencode($value);
    $json = json_encode($value, $options);

    return urldecode($json);
}

/**
 * _urlencode urlencode 字符串或数组
 * 注意： 本函数其实只是用于json_encode2，如果php版本>=5.3的话， 建议用闭包实现，这样就不用将此函数暴露在全局中
 *
 * @param string|array $value
 *
 * @return string|array
 */
function _urlencode($value)
{

    // 如果数据为数组
    if (is_array($value)) {
        foreach ($value as $k => $v) {
            $_k = _urlencode($k);
            if ($_k != $k) {
                unset($value[$k]);
            }
            $value[$_k] = _urlencode($v);
        }
    } else if (is_string($value)) {
        $searchs = array(
            "\\",
            "\r\n",
            "\r",
            "\n",
            "\"",
            "\/",
            "\t"
        );
        $replaces = array(
            '\\\\',
            '\\n',
            '\\n',
            '\\n',
            '\\"',
            '\\/',
            '\\t'
        );
        $value = urlencode(str_replace($searchs, $replaces, $value));
    }

    return $value;
}

/**
 * 字符截取
 *
 * @param string $string 数据字串
 * @param number $length 需要的长度
 * @param string $dot    末尾剪切时的后缀
 * @param string $charset
 *
 * @return string
 */
function rsubstr($string, $length, $dot = ' ...', $charset = null)
{

    // 如果长度小于指定长度
    if (strlen($string) <= $length) {
        return $string;
    }

    // 字符集为空时, 默认为 UTF8
    if ($charset === null) {
        $charset = 'utf-8';
    }

    // 标记空格/逗号/左右尖括号
    $pre = chr(1);
    $end = chr(1);
    $searchs = array(
        '&amp;',
        '&quot;',
        '&lt;',
        '&gt;'
    );
    $replaces = array(
        $pre . '&' . $end,
        $pre . '"' . $end,
        $pre . '<' . $end,
        $pre . '>' . $end
    );
    $string = str_replace($searchs, $replaces, $string);
    $strcut = '';
    // 如果字符集为 UTF8
    if (strtolower($charset) == 'utf-8') {
        $n = $tn = $noc = 0;
        // 遍历
        while ($n < strlen($string)) {
            // 获取当前位置的ASCII码值
            $t = ord($string[$n]);
            // 如果为标准的ASCII码
            if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                $tn = 1;
                $n++;
                $noc++;
            } elseif (194 <= $t && $t <= 223) { // 双字节字符
                $tn = 2;
                $n += 2;
                $noc += 2;
            } elseif (224 <= $t && $t <= 239) { // 三字节字符
                $tn = 3;
                $n += 3;
                $noc += 2;
            } elseif (240 <= $t && $t <= 247) { // 四字节字符
                $tn = 4;
                $n += 4;
                $noc += 2;
            } elseif (248 <= $t && $t <= 251) { // 五字节字符
                $tn = 5;
                $n += 5;
                $noc += 2;
            } elseif ($t == 252 || $t == 253) { // 六字节字符
                $tn = 6;
                $n += 6;
                $noc += 2;
            } else { // 非正常字符
                $n++;
            }

            // 如果当前长度已经达到需要的长度
            if ($noc >= $length) {
                break;
            }
        }

        if ($noc > $length) {
            $n -= $tn;
        }

        // 剪切字串
        $strcut = substr($string, 0, $n);
    } else {
        // 根据长度剪切
        for ($i = 0; $i < $length; $i++) {
            $strcut .= ord($string[$i]) > 127 ? $string[$i] . $string[++$i] : $string[$i];
        }
    }

    // 换回标记
    $searchs = array(
        $pre . '&' . $end,
        $pre . '"' . $end,
        $pre . '<' . $end,
        $pre . '>' . $end
    );
    $replaces = array(
        '&amp;',
        '&quot;',
        '&lt;',
        '&gt;'
    );
    $strcut = str_replace($searchs, $replaces, $strcut);
    $pos = strrpos($strcut, chr(1));
    // 如果字串中还包含了标记字符, 则
    if ($pos !== false) {
        $strcut = substr($strcut, 0, $pos);
    }

    return $strcut . $dot;
}

/**
 * 转换文本格式的时间为本地时间戳
 *
 * @param string $datetime  时间字串
 * @param int    $precision 要求精度, 0: 秒; 1: 毫秒
 *
 * @return number
 */
function rstrtotime($datetime, $precision = 0)
{

    $timestamp = strtotime($datetime);
    // 如果时间戳不正确
    if (-1 === $timestamp || false === $timestamp) {
        return 0;
    }

    // 判断是否含有时区标识
    if (!preg_match("/(GMT|UTC)/i", $datetime) && !preg_match("/T(.*?)Z$/i", $datetime)) {
        // 获取时区配置
        $offset = (int)cfg('TIME_OFFSET');
        $ymdhis = date('Y m d H i s', $timestamp);
        list ($y, $m, $d, $h, $i, $s) = explode(' ', $ymdhis);
        $timestamp = gmmktime($h, $i, $s, $m, $d, $y) - 3600 * $offset;
    }

    return 0 == $precision ? $timestamp : ($timestamp * 1000);
}

/**
 * 认证字符串的加密和解密函数
 *
 * @param string $string    待处理的字符串
 * @param string $key       加密密钥
 * @param string $operation 操作方式：DECODE=解密，ENCODE=加密
 * @param int    $expiry    过期时间。单位：秒。0=不过期
 *
 * @return boolean|string
 */
function authcode($string, $key, $operation = 'DECODE', $expiry = 0)
{

    // 如果秘钥为空, 则
    if ($key == '') {
        return false;
    }

    $ckey_length = 4;
    $key = md5($key);
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);

    $string = $operation == 'DECODE' ? rbase64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);

    $result = '';
    $box = range(0, 255);

    $rndkey = array();
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc . str_replace('=', '', rbase64_encode($result));
    }
}

/**
 * 替换preg_replace 函数，兼容php 5.5
 * //3 array(array('callback' => 'function', 'params' => array(1,2)), array('callback' => 'function', 'params' => array(1,2)) );
 * //2 array('callback' => 'function', 'params' => array(1,2)) array('callback' => array($obj, 'function'), 'params' => array(1,2));
 * //1 array('num \\1', 'num \\2')
 *
 * @param mixed  $pattern     正则表达式
 * @param mixed  $replacement 替换字串
 * @param string $subject     待替换数据
 *
 * @return mixed
 */
function rpreg_replace($pattern, $replacement, $subject)
{

    // 如果正则表达式为数组
    if (is_array($pattern)) {
        // 遍历所有表达式
        foreach ($pattern as $loop => $pat) {
            $rep = $replacement;
            if (is_string($replacement)) { // 用于替换的值为字串时
                $rep = $replacement;
            } elseif (!empty($replacement['callback'])) { // 用户替换的值为回调函数时
                $rep = $replacement;
            } elseif (!empty($replacement[$loop]['callback']) || is_string($replacement[$loop])) {
                $rep = $replacement[$loop];
            }

            $subject = rpreg_replace($pat, $rep, $subject);
        }

        return $subject;
    } else {
        // 回调替换函数
        return preg_replace_callback($pattern, function ($matches) use ($replacement) {

            $ret = '';
            if (is_array($replacement)) { // 如果用于替换的值为数组时
                // 遍历所有替换的值
                foreach ($replacement['params'] as $pk => $pv) {
                    if (is_numeric($pv)) {
                        $replacement['params'][$pk] = $matches[$pv - 1];
                    }
                }

                $ret = call_user_func_array($replacement['callback'], $replacement['params']);
            } elseif (is_string($replacement)) {
                $ret = $replacement;
                foreach ($matches as $k => $v) {
                    $num = $k + 1;
                    $ret = str_replace(array(
                        "\\{$num}",
                        "\${$num}",
                        "\$\{{$num}\}"
                    ), $v, $ret);
                }
            }

            return $ret;
        }, $subject);
    }
}

// 获取页面的唯一标识
function url_uniqueid()
{

    static $uniqueid = '';
    // 如果已经生成
    if (!empty($uniqueid)) {
        return $uniqueid;
    }

    $str = boardurl() . random(16) . MILLI_TIME;
    $uniqueid = md5($str);

    return $uniqueid;
}

// 获取当前页面的完整 URL
function boardurl()
{

    static $boardurl = '';
    // 如果已经生成过
    if (!empty($boardurl)) {
        return $boardurl;
    }

    // 拼接URL
    $protocal = is_ssl() ? 'https://' : 'http://';
    $php_self = I('server.PHP_SELF', I('server.SCRIPT_NAME', '', 'trim'), 'trim');
    $relate_url = I('server.REQUEST_URI', $php_self . I('server.QUERY_STRING', I('server.PATH_INFO', '', 'trim'), 'trim'), 'trim');
    $boardurl = $protocal . I('server.HTTP_HOST', '') . $relate_url;

    return $boardurl;
}

/**
 * 公共的密码加密储存方法
 *
 * @param string  $password    密码值，可以为原文也可以md5后
 * @param string  $salt        散列值，如为空则自动生成
 * @param boolean $is_original 给定的$password是否为密码原文，true是，false为md5后的值
 * @param int     $salt_length 散列值的长度
 *
 * @return array(password, salt)
 *         <p>array(0=>password 加密后的密码字符串, 1 =>salt 密码散列值)</p>
 */
function generate_password($password, $salt = null, $is_original = false, $salt_length = 6)
{

    $salt = (string)$salt;
    // 如果干扰码为空, 则生成
    if (null === $salt || '' === $salt) {
        $salt = random($salt_length);
    }

    // 如果是源密码
    if ($is_original) {
        $password = md5($password);
    }

    return array(
        md5($password . $salt),
        $salt
    );
}

/**
 * 根据数组指定的键对应的值, 作为新数组的键名
 *
 * @param array  $arr 二维数组
 * @param string $key 键名
 *
 * @return array
 */
function array_combine_by_key($arr, $key)
{

    $keys = array_column($arr, $key);

    return array_combine($keys, $arr);
}

/**
 * 根据组数指定的值保留键值对
 *
 * @param $arr array 二维数组
 * @param $key array 值
 * @param $handleneDimensional bool 处理一维数组
 * @return mixed
 */
function array_intersect_key_reserved($arr, $key, $handleneDimensional = false)
{
    // 要处理的键
    settype($key, 'array');
    $key = array_fill_keys($key, '');

    // 处理一维数组
    if ($handleneDimensional) {
        return array_intersect_key($arr, $key);
    }
    // 处理二维数组
    foreach ($arr as &$value) {
        $value = array_intersect_key($value, $key);
    }

    return $arr;
}

/**
 * 根据组数指定的值排除键值对
 *
 * @param $arr array 二维数组
 * @param $key array 值
 * @param $handleneDimensional bool 处理一维数组
 * @return mixed
 */
function array_diff_key_reserved($arr, $key, $handleneDimensional = false)
{
    // 要处理的键
    settype($key, 'array');
    $key = array_fill_keys($key, '');

    // 处理一维数组
    if ($handleneDimensional) {
        return array_diff_key($arr, $key);
    }
    // 处理二维数组
    foreach ($arr as &$value) {
        $value = array_diff_key($value, $key);
    }

    return $arr;
}

/**
 * 重写 fopen 方法, 调用 Snoopy 来模拟请求
 *
 * @param array   $data       返回数据
 * @param string  $url        目标URL
 * @param mixed   $post       请求的数据
 * @param array   $headers    请求的头部
 * @param string  $method     请求协议, 默认 GET
 * @param boolean $ret_snoopy 是否需要返回 Snoopy 实例
 * @param boolean $json       目标地址是否返回的 JSON 数据
 * @param mixed   $files      文件数据
 *
 * @return bool
 */
function rfopen(&$data, $url, $post = null, $headers = array(), $method = 'GET', $ret_snoopy = false, $json = true, $files = null)
{

    // 载入 Snoopy 类
    $snoopy = new \Org\Net\Snoopy();
    // 使用自定义的头字段，格式为 array(字段名 => 值, ... ...)
    if (!is_array($headers)) {
        $headers = array();
    }

    \Think\Log::record("URL: {$url} Method: {$method} Post: " . var_export($post, true) . " Header: " . var_export($headers, true));
    $snoopy->rawheaders = $headers;
    $method = rstrtoupper($method);
    // 非 GET 协议, 需要设置
    $methods = array(
        'POST',
        'PUT',
        'DELETE'
    );
    if (!in_array($method, $methods)) {
        $method = 'GET';
    }

    // 设置协议
    if (!empty($files)) { // 如果需要传文件
        $method = 'POST';
        $snoopy->set_submit_multipart();
    } else {
        $snoopy->set_submit_normal();
    }

    // 判断协议
    $snoopy->set_submit_method($method);
    switch (rstrtoupper($method)) {
        case 'POST':
        case 'PUT':
        case 'DELETE':
            $result = $snoopy->submit($url, $post, $files);
            break;
        default:

            // 如果有请求数据
            if ($post) {
                // 拼凑 GET 字串
                if (is_array($post)) {
                    $get_data = http_build_query($post);
                } else {
                    $get_data = $post;
                }

                // 判断 URL 是否有参数
                if (false === strpos($url, '?')) {
                    $url .= '?';
                } else {
                    $url .= '&';
                }

                $url .= $get_data;
            }

            $result = $snoopy->fetch($url);
    }

    // 如果读取错误
    if (!$result || 200 != $snoopy->status) {
        \Think\Log::record('$snoopy[' . $method . '] error: ' . $url . '|' . $post . '|' . var_export($result, true) . '|' . $snoopy->status);
        $data = $snoopy; // 出错时, 返回 $snoopy 对象
        E('_ERR_SNOOPY_STATUS_ERROR');
        return false;
    }

    // 获取返回数据
    $data = $snoopy->results;
    // 如果返回的是 JSON, 则解析 JSON
    if ($json) {
        $data = json_decode($data, true);
    }

    \Think\Log::record("result: " . var_export($snoopy->results, true) . " data: " . var_export($data, true));
    // 如果返回的数据为空, 则
    if (empty($data)) {
        \Think\Log::record('$snoopy[' . $method . '] error: ' . $url . '|' . var_export($result, true) . '|' . $snoopy->status);
        $data = $snoopy; // 出错时, 返回 $snoopy 对象
        E('_ERR_SNOOPY_RESULT_EMPTY');
        return false;
    }

    // 如果需要返回 Snoopy 对象
    if ($ret_snoopy) {
        $snoopy->set_results($data);
        $data = $snoopy;
    }

    return true;
}

/**
 * 从主机地址中获取二级域名信息(second-level)
 *
 * @param string $host 主机地址
 *
 * @return string
 */
function get_sl_domain($host = '')
{

    static $domain = '';
    // 如果未指定主机, 则取当前请求的
    if (empty($host)) {
        // 如果域名已经存在
        if (!empty($domain)) {
            return $domain;
        }

        $host = I('server.HTTP_HOST');
    }

    // 取二级域名
    $hosts = explode('.', $host);
    $domain = rawurlencode($hosts[0]);

    return $domain;
}

/**
 * 把驼峰转成以下划线分隔, 如:MsgType => msg_type
 *
 * @param string $key      转换
 * @param bool   $to_camel 是否转成驼峰
 *
 * @return string
 */
function convert_camel_underscore($key, $to_camel = false)
{

    // 如果是转成驼峰式
    if ($to_camel) {
        $key{0} = rstrtoupper($key{0});
        $key = preg_replace_callback('/\_[a-z]/s', function ($m) {

            return rstrtoupper($m);
        }, $key);
    } else { // 非驼峰
        $key{0} = rstrtolower($key{0});
        $key = preg_replace("/([A-Z]+)/s", "_\\1", $key);
        $key = rstrtolower($key);
    }

    return $key;
}

/**
 * 根据当前页数、每页的显示数来确定 sql 查询的开始及读取条数;
 *
 * @param int $page        当前页码
 * @param int $perpage     每页记录数
 * @param int $max_perpage 每页显示数的最大值
 * @param int $max_page    最大页码
 *
 * @return array 返回开始数、每页显示数、当前页数;
 */
function page_limit($page = 0, $perpage = 0, $max_perpage = 200, $max_page = 0)
{

    // 类型转换
    $perpage = rintval($perpage);
    // 如果每页个数值超出范围, 则取最大值
    if (0 >= $perpage || $perpage > $max_perpage) {
        $perpage = $max_perpage;
    }

    $max_page = rintval($max_page);
    // 如果最大页码大于规定的值
    $page = max(rintval($page), 1);
    if ($max_page && $page > $max_page) {
        $page = $max_page;
    }

    $start = ($page - 1) * $perpage;

    return array(
        $start,
        $perpage,
        $page
    );
}

/**
 * 返回给定秒数的时间描述
 *
 * @param mixed   $result 结果集
 * @param number  $second 秒
 * @param boolean $diy    是否DIY输出
 *
 * @return array(day, hour, minute)
 */
function sec2dhis(&$result, $second, $diy = false)
{

    $d = floor($second / 86400);
    $h = floor(($second % 86400) / 3600);
    $i = floor(($second % 3600) / 60);
    $s = $second % 60;

    if ($diy) { // 如果是 diy 输出
        $result = array();
        // 读取语言信息
        $lang = L('date');
        // 如果大于 1 天
        if (0 < $d) {
            $result[] = $d . $lang['DAY'];
        }

        // 如果大于 1 小时
        if (0 < $h) {
            $result[] = $h . $lang['HOUR'];
        }

        // 如果大于 1 分钟
        if (0 < $i) {
            $result[] = $i . $lang['MIN'];
        }

        // 如果大于 1 秒
        if (0 < $s) {
            $result[] = $s . $lang['SEC'];
        }

        // 如果时间为 0
        if (empty($result)) {
            $result[] = "0" . $lang['SEC'];
        }

        $result = implode($result);
    } else {
        $result = array(
            $d,
            $h,
            $i,
            $s
        );
    }

    return true;
}

/**
 * 根据给定的键值提取参数
 *
 * @param array $out    提取之后的数据
 * @param array $fields 需要获取的参数列表
 *                      array(
 *                      'passwd',
 *                      array('username', 'string'),
 *                      array('gender', 'string', false),
 *                      'weight' => 'mem_weight'
 *                      )
 * @param array $in     源数据
 *
 * @return boolean
 */
function extract_field(&$out, $fields, $in = array())
{

    // 如果未给定数据, 则取用户提交的数据
    if (empty($in)) {
        $in = I('request.');
    }

    // 遍历所有需要提取的参数
    foreach ($fields as $_in_k => $_rule) {
        /**
         * 取出键值和数据类型
         * $out_k => 解析后的目的键值
         * $type => 类型
         * $ignore => 是否忽略键值不存在的参数
         */
        $_rule = (array)$_rule;
        switch (count($_rule)) {
            case 1: // 如果只有 1 个参数
                $_rule[] = 'string';
            case 2: // 如果只有 2 个参数
                $_rule[] = false;
            default:
                break;
        }

        list ($out_k, $type, $ignore) = $_rule;
        // 取 $out_k 对应的值
        $out_k = (string)$out_k;

        // 如果来源键值为数字, 则说明未指定来源键值
        if ($_in_k === (int)$_in_k) {
            $_in_k = $out_k;
        }

        // 如果允许该字段为空
        if ($ignore && !isset($in[$_in_k])) {
            continue;
        }

        $val = isset($in[$_in_k]) ? $in[$_in_k] : '';
        // 类型强制转换
        switch ($type) {
            case 'array':
                $val = empty($val) ? array() : (array)$val;
                break;
            case 'int':
                $val = (int)$val;
                break;
            case 'string':
                $val = (string)$val;
                break;
            default:
                $val = (string)$val;
                break;
        }

        // 赋值
        $out[$out_k] = $val;
    }

    return true;
}

/**
 * 拼凑 api 数据返回值结构
 *
 * @param array $data 返回数据
 *
 * @return array
 */
function generate_api_result($data)
{

    $errsdkcode = '';
    $requestId = '';
    $errcode = \Com\Error::instance()->get_errcode();
    // 如果是异常数据
    if ($data instanceof \Exception) {
        // 如果是SDK错误
        if (method_exists($data, 'getSdkCode')) {
            $errsdkcode = $data->getSdkCode();
            $errcode = 700;
        }

        if (method_exists($data, 'getRequestID')) {
            $requestId = $data->getRequestID();
        }

        $data = '';
    }

    $result = array(
        'errcode' => $errcode,
        'errmsg' => \Com\Error::instance()->get_errmsg(),
        'errsdkcode' => $errsdkcode,
        'requestId' => $requestId,
        'timestamp' => MILLI_TIME,
        'result' => $data
    );

    return $result;
}

/**
 * 解析错误信息
 *
 * @param string $message 错误信息
 *
 * @return array
 */
function parse_lang_error($message)
{

    $code = 0;
    // 如果是语言
    if (preg_match('/^[\w+\.\_]+$/i', $message)) {
        $message = L($message);
    }

    // 判断是否有错误编号
    if (preg_match('/^(\s*\d+\s*):/', $message)) {
        $pos = stripos($message, ':');
        $ncode = substr($message, 0, $pos);
        $message = substr($message, $pos + 1);
        // 如果错误号为空, 则取详情中得编号
        if (empty($code)) {
            $code = $ncode;
        }
    }

    return array($code, $message);
}

// PHP < 5.5 兼容函数  array_column
if (!function_exists('array_column')) {

    /**
     * 兼容 php < 5.5 以下版本的  array_column 函数 - 返回数组中指定的一列
     *
     * 返回 input 数组中键值为 column_key 的列， 如果指定了可选参数 index_key，那么 input 数组中的这一列的值将作为返回数组中对应值的键
     *
     * @param array $input      需要取出数组列的多维数组（或结果集）
     * @param mixed $column_key 需要返回值的列，它可以是索引数组的列索引，或者是关联数组的列的键。 也可以是 NULL ，此时将返回整个数组（配合index_key参数来重置数组键的时候，非常管用）
     * @param mixed $index_key  作为返回数组的索引/键的列，它可以是该列的整数索引，或者字符串键值
     *
     * @return array 从多维数组中返回单列数组
     */
    function array_column($input, $column_key, $index_key = null)
    {

        if ($index_key !== null) {
            // Collect the keys
            $keys = array();
            $i = 0; // Counter for numerical keys when key does not exist

            foreach ($input as $row) {
                if (array_key_exists($index_key, $row)) {
                    // Update counter for numerical keys
                    if (is_numeric($row[$index_key]) || is_bool($row[$index_key])) {
                        $i = max($i, (int)$row[$index_key] + 1);
                    }

                    // Get the key from a single column of the array
                    $keys[] = $row[$index_key];
                } else {
                    // The key does not exist, use numerical indexing
                    $keys[] = $i++;
                }
            }
        }

        if ($column_key !== null) {
            // Collect the values
            $values = array();
            $i = 0; // Counter for removing keys

            foreach ($input as $row) {
                if (array_key_exists($column_key, $row)) {
                    // Get the values from a single column of the input array
                    $values[] = $row[$column_key];
                    $i++;
                } elseif (isset($keys)) {
                    // Values does not exist, also drop the key for it
                    array_splice($keys, $i, 1);
                }
            }
        } else {
            // Get the full arrays
            $values = array_values($input);
        }

        if ($index_key !== null) {
            return array_combine($keys, $values);
        }

        return $values;
    }
}

/**
 * 根据域名生成站点缓存目录
 *
 * @param string $domain 二级域名
 *
 * @return string
 */
function get_sitedir($domain = '')
{

    static $sitedir = '';
    // 如果已经路径生成
    if (!empty($sitedir)) {
        return $sitedir;
    }

    // 如果 $domain 为空, 则使用当前站点域名
    if (empty($domain)) {
        $domain = QY_DOMAIN;
    }

    // md5, 取首尾字符 + 域名作为目录
    $md5 = md5($domain);
    // cfg('DATA_CACHE_PATH') . substr($md5, 0, 1) . '/' . substr($md5, - 1) . '/' . $domain . '/';
    $sitedir = CODE_ROOT . D_S . 'Common' . D_S . 'Runtime' . D_S . 'Temp' . D_S . substr($md5, 0, 1) . D_S . substr($md5, -1) . D_S . $domain . D_S;
    rmkdir($sitedir);

    return $sitedir;
}

if (!function_exists('array_column')) {

    function array_column($input, $column_key, $index_key = null)
    {

        if ($index_key !== null) {
            // Collect the keys
            $keys = array();
            $i = 0; // Counter for numerical keys when key does not exist

            foreach ($input as $row) {
                if (array_key_exists($index_key, $row)) {
                    // Update counter for numerical keys
                    if (is_numeric($row[$index_key]) || is_bool($row[$index_key])) {
                        $i = max($i, (int)$row[$index_key] + 1);
                    }

                    // Get the key from a single column of the array
                    $keys[] = $row[$index_key];
                } else {
                    // The key does not exist, use numerical indexing
                    $keys[] = $i++;
                }
            }
        }

        if ($column_key !== null) {
            // Collect the values
            $values = array();
            $i = 0; // Counter for removing keys

            foreach ($input as $row) {
                if (array_key_exists($column_key, $row)) {
                    // Get the values from a single column of the input array
                    $values[] = $row[$column_key];
                    $i++;
                } elseif (isset($keys)) {
                    // Values does not exist, also drop the key for it
                    array_splice($keys, $i, 1);
                }
            }
        } else {
            // Get the full arrays
            $values = array_values($input);
        }

        if ($index_key !== null) {
            return array_combine($keys, $values);
        }

        return $values;
    }
}

/**
 * 记录SQL日志
 *
 * @param string $sql    SQL 语句
 * @param array  $params 参数数组
 *
 * @return bool
 */
function sql_record($sql, $params = array())
{

    return true;
    // 导入类库文件
    $class = parse_res_name('Common/CommonSqlrecord', 'Service');
    // 如果该类文件不存在, 则直接返回
    if (!class_exists($class)) {
        return true;
    }

    // 初始化 Service 层
    $serv = D('Common/CommonSqlrecord', 'Service');
    // 如果不是 SQL 日志表操作, 则记录
    if (!preg_match('/\s+\`?' . $serv->tname() . '\`?(\(|\s+)/is', $sql)) {
        $serv->insert($sql . ';' . var_export((array)$params, true));

        return true;
    }

    return true;
}

/**
 * OA URL 拼写输出函数
 *
 * 根据 OA 特点扩展了的 TP 内置的  U() 函数，前两个参数的使用方法同 U() 函数使用完全一致
 *
 * @param string $url        URL表达式，格式：'[模块/控制器/操作#锚点@域名]?参数1=值1&参数2=值2...'
 * @param array  $vars       传入的参数，支持数组和字符串
 * @param string $qyDomain   指定企业的域名，不指定则使用当前访问的企业域名 QY_DOMAIN
 * @param string $app_dir    指定应用目录名称，不指定则使用当前目录名称 APP_DIR
 * @param bool   $showDomain 是否显示域名，true=等于输出完整的URL，同  U() 函数的 $domain 参数一致。
 *
 * @return string
 */
function oaUrl($url = '', $vars = array(), $qyDomain = '', $app_dir = '', $showDomain = true)
{

    // 如果是完整的 url
    if (preg_match("/^https?\:\/\//i", $url)) {
        return U($url, $vars, false, $showDomain);
    }

    if (!$qyDomain) {
        // 如果未指定企业域名，则使用当前定义的
        $qyDomain = QY_DOMAIN;
    }
    if (!$app_dir) {
        // 如果未指定应用，则使用当前定义的
        $app_dir = APP_DIR;
    }

    // 拼凑当前的 URL 前缀（企业域名/应用唯一标识符）
    if (!preg_match("/^\/?{$qyDomain}\/{$app_dir}\//i", $url)) {
        $url = '/' . $qyDomain . '/' . $app_dir . '/' . ltrim($url, '/');
    }

    return U($url, $vars, false, $showDomain);
}

/**
 * 后台地址格式化
 *
 * @param string  $url    URL地址
 * @param string  $vars   传入的参数，支持数组和字符串
 * @param boolean $suffix 伪静态后缀，默认为true表示获取配置值
 * @param boolean $domain 是否显示域名
 *
 * @return string
 */
function cpUrl($url, $vars = '', $suffix = false, $domain = false)
{

    return U($url, $vars, $suffix, $domain);
}

/**
 * 根据图片id获取图片展示的Url
 *
 * @param string $atid        附件ID
 * @param string $qyDomain    企业域名
 * @param string $_identifier 应用唯一标识
 *
 * @return string
 */
function imgUrl($atid = '', $qyDomain = '', $_identifier = '')
{

    if (empty($_identifier)) {
        $_identifier = APP_IDENTIFIER;
    }
    // 拼凑URL
    $url = oaUrl('/Frontend/Attachment/View/Index', array("atid" => $atid), $qyDomain, "Public");

    return $url . '?_identifier=' . $_identifier;
}

/**
 * 字节转换单位显示
 *
 * @param  number $size      字节数
 * @param  string $delimiter 数字和单位分隔符
 *
 * @return string            格式化后的带单位的大小
 */
function formatBytes($size, $delimiter = '')
{

    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 6; $i++) {
        $size /= 1024;
    }

    return round($size, 2) . $delimiter . $units[$i];
}

/**
 * 清理缓存 (所有缓存)
 *
 * @return boolean
 */
function clear_cache()
{

    $serv_sys = D('Common/Syscache', 'Service');
    $list = $serv_sys->list_all();
    foreach ($list as $_cache) {
        $cachekey = $_cache['name'];
        // 去除应用标识
        $cachekey = str_replace(APP_DIR, '', $cachekey);
        \Common\Common\Cache::instance()->set($cachekey, null);
    }

    return true;
}

/**
 * 清理缓存
 *
 * @param string|array $cacheName
 * @return bool
 */
function clear_sys_cache($cacheName = null)
{

    if (empty($cacheName)) {
        clear_cache();
        return true;
    }

    settype($cacheName, 'array');
    // 清理指定缓存
    foreach ($cacheName as $name) {
        \Common\Common\Cache::instance()->set($name, null);
    }

    return true;
}

/**
 * 获取前端完整Url
 *
 * @author zhonglei
 *
 * @param string $pageUrl 前端地址（#号之后的页面地址，不包含参数）
 * @param array  $params  参数
 * @param string $appdir  应用目录，默认为当前目录
 *
 * @return string
 */
function frontUrl($pageUrl, $params = [], $appdir = '')
{

    if (!isset($params['ts'])) {
        $params['ts'] = MILLI_TIME;
    }

    if (cfg('STATIC_URL_DEBUG') === false) {
        unset($params['ts']);
    }

    if (empty($appdir)) {
        $appdir = APP_DIR;
    }

    $frontPath = cfg('FRONTEND_PATH');
    $url = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
    $url .= sprintf('/%s/%s/%s/index.html#%s?%s', QY_DOMAIN, $appdir, $frontPath, $pageUrl, http_build_query($params));
    return $url;
}

/**
 * 跳转至前端Url
 *
 * @author zhonglei
 *
 * @param string $pageUrl 前端地址（#号之后的页面地址，不包含参数）
 * @param array  $params  参数
 * @param string $appdir  应用目录，默认为当前目录
 *
 * @return void
 */
function redirectFront($pageUrl, $params = [], $appdir = '')
{

    $url = frontUrl($pageUrl, $params, $appdir);
    redirect($url);
}

/**
 * 获取当前访问的操作系统
 *
 * @return string 返回值可能是：ios|android 或者 空字符串
 */
function getOS()
{

    $userAgent = !empty($_SERVER['HTTP_USER_AGENT']) ? (string)$_SERVER['HTTP_USER_AGENT'] : '';
    if ($userAgent) {
        if (stripos($userAgent, 'iPhone') !== false || stripos($userAgent, 'iPad') !== false) {
            return 'ios';
        }
        if (stripos($userAgent, 'Android') !== false) {
            return 'android';
        }
    }

    return '';
}

/**
 * 字符串可逆加密函数
 * @param string $string    待 加密/解密 的字符串
 * @param string $operation 执行动作（注意大小写）。DECODE=解密,ENCODE=加密，默认：DECODE=解密
 * @param string $key       加密的密钥
 * @param int    $expiry    过期时间，单位：秒，默认=0 不过期。
 * @return string
 */
function strEncrypt($string, $operation = 'DECODE', $key = '', $expiry = 0)
{

    $ckey_length = 4;
    $key = md5($key);
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);

    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);

    $result = '';
    $box = range(0, 255);

    $rndkey = array();
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc . str_replace('=', '', base64_encode($result));
    }
}

/**
 * 获取 IP 地址的地理位置
 * @param string $ip IP 地址
 * @return string
 */
function ipLocation($ip)
{

    if (empty($ip)) {
        return '';
    }

    $result = Com\IP\IP::find($ip);

    if (empty($result) || !is_array($result)) {
        return '';
    }

    $result = array_unique($result);
    $location = '';

    foreach ($result as $v) {
        if (empty($v)) {
            break;
        } elseif ($v == '中国') {
            continue;
        }

        $location .= $v;
    }

    return $location;
}

/**
 * 调用 python
 * @throws Exception
 * @return mixed
 */
function callPython()
{

    // 参数数量
    $args_len = func_num_args();
    // 参数数组
    $arg_array = func_get_args();

    // 参数数量不能小于 1
    if ($args_len < 1) {
        E('_ERR_PYTHON_PARAM_LENGTH_ERROR');
    }
    // 第一个参数是Python模块函数名称，必须是string类型
    if (!is_string($arg_array[0])) {
        E('_ERR_PYTHON_PARAM_MODULE_ERROR');
    }

    if (($socket = socket_create(AF_INET, SOCK_STREAM, 0)) === false) {
        E('_ERR_PYTHON_SOCKET_CREATE_ERROR');
    }

    if (socket_connect($socket, cfg('PYTHON_SOCKET_IP'), cfg('PYTHON_SOCKET_PORT')) === false) {
        E('_ERR_PYTHON_CONNECT_ERROR');
    }

    // 消息体序列化
    $request = json_encode($arg_array);
    $req_len = strlen($request);
    $request = $req_len . "," . $request;
    // echo "{$request}<br>";

    $send_len = 0;
    do {
        // 发送
        if (($sends = socket_write($socket, $request, strlen($request))) === false) {
            E('_ERR_PYTHON_SOCKET_WRITE_ERROR');
        }

        $send_len += $sends;
        $request = substr($request, $sends);
    } while ($send_len < $req_len);

    // 接收
    $response = "";
    while (true) {
        if (($buf = socket_read($socket, 2048)) === false) {
            E('_ERR_PYTHON_SOCKET_READ_ERROR');
        }
        if ($buf == "") {
            break;
        }

        $response .= $buf;
    }

    // 关闭
    socket_close($socket);

    $resp_stat = substr($response, 0, 1); // 返回类型 "S":成功 "F":异常
    $resp_msg = substr($response, 1); // 返回信息
    // echo "返回类型:{$resp_stat},返回信息:{$resp_msg}<br>";

    if ($resp_stat == "F") {
        // 异常信息不用反序列化
        E('_ERR_PYTHON_CALL_ERROR', array('errmsg' => $resp_msg));
    }

    // 反序列化
    return json_decode($resp_msg);
}

/**
 * 对二维数组进行排序
 * @param array $arr 二维数组
 * @param string $key 排序的依据
 * @param int $type 排序类型
 *      SORT_REGULAR 对对象进行 通常 比较
 *      SORT_NUMERIC 对对象进行 数值 比较
 *      SORT_STRING 对对象进行 字符串 比较
 *      SORT_LOCALE_STRING 基于当前区域来对对象进行字符串比较
 *      详见: http://php.net/manual/zh/array.constants.php
 * @param int $short 排序顺序
 *      SORT_ASC 升序
 *      SORT_DESC 降序
 * @return mixed
 * @author zhoutao
 * @date 2017-06-01 18:49:42
 */
function multi_array_sort($arr, $key, $type = SORT_NUMERIC, $short = SORT_DESC)
{
    array_multisort(array_column($arr, $key), $type, $short, $arr);

    return $arr;
}

/**
 * 将链接地址协议转换为当前访问使用的协议
 * @param string $url
 * @return $url
 */
function urlProtocolConversion($url)
{
    if (empty($url)) {
        return $url;
    }

    if (is_ssl() && stripos($url, 'https://') !== 0) {
        $url = preg_replace('/^http:\/\//i', 'https://', $url);
    }

    return $url;
}

/**
 * 获取资源鉴权配置
 * @param string $key 配置名称
 * @return array
 */
function getResAuthConfig($key)
{
    if (empty($key)) {
        return [];
    }

    $auth_config = cfg('RES_AUTH_CONFIG');

    if (!is_array($auth_config) || !isset($auth_config[$key])) {
        return [];
    }

    $config = $auth_config[$key];

    // 处理资源权限认证地址
    if (isset($config['atAuthUrl']) && is_array($config['atAuthUrl'])) {
        list($url, $app_dir) = $config['atAuthUrl'];
        $config['atAuthUrl'] = oaUrl($url, [], '', $app_dir);
    }

    return $config;
}
