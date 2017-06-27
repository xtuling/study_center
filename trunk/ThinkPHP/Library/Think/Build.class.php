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
namespace Think;

/**
 * 用于ThinkPHP的自动生成
 */
class Build
{

    /**
     * 控制层模板
     */
    protected static $controller = '<?php
/**
 * [CONTROLLER][CNAME].class.php
 * $author$
 */

namespace [MODULE]\[CNAME_CPATH];

class [CONTROLLER][CNAME] extends [BASE_CONTROLLER]
{

    public function [DEFAULT_ACTION]()
    {

        $this->show(\'[[CONTROLLER][CNAME]->[DEFAULT_ACTION]]\');
        $this->_output("[TPL_PATH]");
    }
}
';

    /**
     * Abstract 的方法
     */
    protected static $abstract_controller = '<?php
/**
 * [CONTROLLER][CNAME].class.php
 * $author$
 */

namespace [MODULE]\[CNAME_CPATH];

abstract class [CONTROLLER][CNAME] extends [BASE_CONTROLLER]
{

    public function before_action($action = \'\')
    {

        return parent::before_action($action);
    }

    public function after_action($action = \'\')
    {

        return parent::after_action($action);
    }
}
';

    /**
     * Model 层模板
     */
    protected static $model = '<?php
/**
 * [MODEL]Model.class.php
 * $author$
 */

namespace [MODULE]\Model;

class [MODEL]Model extends AbstractModel
{

    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();
    }
}
';

    /**
     * Model 层基类模板
     */
    protected static $abstract_model = '<?php
/**
 * [MODEL]Model.class.php
 * $author$
 */

namespace [MODULE]\Model;

abstract class [MODEL]Model extends \Common\Model\AbstractModel
{

    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();
    }
}
';

    /**
     * Service 层模板
     */
    protected static $service = '<?php
/**
 * [MODEL]Service.class.php
 * $author$
 */

namespace [MODULE]\Service;

class [MODEL]Service extends AbstractService
{

    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();
        $this->_d = D("[MODULE]/[MODEL]");
    }
}
';

    /**
     * Service 层基类模板
     */
    protected static $abstract_service = '<?php
/**
 * [MODEL]Service.class.php
 * $author$
 */

namespace [MODULE]\Service;

abstract class [MODEL]Service extends \Common\Service\AbstractService
{

    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();
    }
}
';

    /**
     * 操作层方法模板
     */
    protected static $action = '

    /**
     * [ACTION_NAME]
     */
    public function [ACTION_NAME]()
    {

        $this->_output("[TPL_PATH]");
    }
';

    /**
     * 操作层方法模板
     */
    protected static $action_api = '

    /**
     * [ACTION_NAME]
     */
    public function [ACTION_NAME]()
    {

        return true;
    }
';

    /**
     * View 层模板
     */
    protected static $template = '<include file="Header" />
body
<include file="Footer" />
';

    /**
     * View 层错误模板
     */
    protected static $error_tpl = '<include file="Common@Frontend/Error" />
';

    /**
     * 检测应用目录是否需要自动创建
     *
     * @param string $module
     */
    static public function checkDir($module)
    {

        if (!is_dir(APP_PATH . $module)) {
            // 创建模块的目录结构
            self::buildAppDir($module);
        } elseif (!is_dir(LOG_PATH)) {
            // 检查缓存目录
            self::buildRuntime();
        }
    }

    /**
     * 创建应用和模块的目录结构
     *
     * @param string $module
     */
    static public function buildAppDir($module)
    {

        // 没有创建的话自动创建
        if (!is_dir(APP_PATH)) {
            mkdir(APP_PATH, 0755, true);
        }
        if (is_writeable(APP_PATH)) {
            $c_layer = ucfirst(C('DEFAULT_C_LAYER'));
            $dirs = array(
                COMMON_PATH,
                COMMON_PATH . 'Common/',
                CONF_PATH,
                APP_PATH . $module . '/',
                APP_PATH . $module . '/Behavior/',
                APP_PATH . $module . '/Common/',
                APP_PATH . $module . '/' . $c_layer . '/',
                APP_PATH . $module . '/' . $c_layer . '/Frontend/',
                APP_PATH . $module . '/' . $c_layer . '/Admincp/',
                APP_PATH . $module . '/' . $c_layer . '/Rpc/',
                APP_PATH . $module . '/' . $c_layer . '/Api/',
                APP_PATH . $module . '/' . $c_layer . '/Apicp/',
                APP_PATH . $module . '/Service/',
                APP_PATH . $module . '/Sql/',
                APP_PATH . $module . '/Model/',
                APP_PATH . $module . '/Conf.bak/',
                APP_PATH . $module . '/Lang/',
                APP_PATH . $module . '/View/',
                APP_PATH . $module . '/View/Frontend/',
                APP_PATH . $module . '/View/Admincp/',
                RUNTIME_PATH,
                CACHE_PATH,
                CACHE_PATH . $module . '/',
                LOG_PATH,
                LOG_PATH . $module . '/',
                TEMP_PATH,
                DATA_PATH
            );
            foreach ($dirs as $dir) {
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
            }
            // 写入目录安全文件
            self::buildDirSecure($dirs);
            // 写入应用配置文件
            if (!is_file(CONF_PATH . 'config' . CONF_EXT)) {
                file_put_contents(CONF_PATH . 'config' . CONF_EXT, '.php' == CONF_EXT ? "<?php\nreturn [\n    //'配置项'=>'配置值'\n];" : '');
            }
            // 写入模块配置文件
            if (!is_file(APP_PATH . $module . '/Conf.bak/config' . CONF_EXT)) {
                file_put_contents(APP_PATH . $module . '/Conf.bak/config' . CONF_EXT, '.php' == CONF_EXT ? "<?php\nreturn [\n    //'配置项'=>'配置值'\n];" : '');
            }
            // by zhuxun, begin
            // 表数据
            if (!is_file(APP_PATH . $module . '/Sql/data.php')) {
                file_put_contents(APP_PATH . $module . '/Sql/data.php', "<?php\n/**\n * 应用安装时的初始数据文件\n * data.php\n * \$Author\$\n */\n\nreturn \"\";\n");
            }
            // 表结构
            if (!is_file(APP_PATH . $module . '/Sql/structure.php')) {
                file_put_contents(APP_PATH . $module . '/Sql/structure.php', "<?php\n/**\n * 应用的数据表结构文件\n * structure.php\n * \$Author\$\n */\n\nreturn \"\";\n");
            }
            // 生成错误模板
            self::build_tpl(APP_PATH . $module . '/View/Frontend/Error.tpl', self::$error_tpl);
            // end.
            // 生成模块的测试控制器
            if (defined('BUILD_CONTROLLER_LIST')) {
                // 自动生成的控制器列表（注意大小写）
                $list = explode(',', BUILD_CONTROLLER_LIST);
                foreach ($list as $controller) {
                    self::buildController($module, $controller);
                }
            } else {
                // 生成默认的控制器
                self::buildController($module);
            }
            // 生成模块的模型
            if (defined('BUILD_MODEL_LIST')) {
                // 自动生成的控制器列表（注意大小写）
                $list = explode(',', BUILD_MODEL_LIST);
                foreach ($list as $model) {
                    self::buildModel($module, $model);
                }
            }
        } else {
            header('Content-Type:text/html; charset=utf-8');
            exit('应用目录[' . APP_PATH . ']不可写，目录无法自动生成！<BR>请手动生成项目目录~');
        }
    }

    /**
     * 检查缓存目录(Runtime) 如果不存在则自动创建
     *
     * @return boolean
     */
    static public function buildRuntime()
    {

        if (!is_dir(RUNTIME_PATH)) {
            mkdir(RUNTIME_PATH);
        } elseif (!is_writeable(RUNTIME_PATH)) {
            header('Content-Type:text/html; charset=utf-8');
            exit('目录 [ ' . RUNTIME_PATH . ' ] 不可写！');
        }
        mkdir(CACHE_PATH); // 模板缓存目录
        if (!is_dir(LOG_PATH)) {
            mkdir(LOG_PATH); // 日志目录
        }
        if (!is_dir(TEMP_PATH)) {
            mkdir(TEMP_PATH); // 数据缓存目录
        }
        if (!is_dir(DATA_PATH)) {
            mkdir(DATA_PATH); // 数据文件目录
        }

        return true;
    }

    /**
     * 从文件路径解析所有目录, 检查是否有安全文件
     *
     * @param string $file 文件路径
     * @return bool
     */
    static public function chk_dir_secure($file)
    {

        // 先剔除 app 目录
        $path = str_replace(APP_PATH, '', $file);
        // 切割路径
        $paths = explode('/', $path);
        $dirs = array();
        // 原始目录为 app 目录
        $dir = APP_PATH;
        // 遍历所有路径
        foreach ($paths as $_path) {
            // 路径为空时, 忽略
            if (empty($_path)) {
                continue;
            }
            $dir = $dir . $_path . '/';
            $dirs[] = $dir;
        }
        // 生成安全文件
        self::buildDirSecure($dirs);

        return true;
    }

    /**
     * 创建控制器类
     *
     * @param string $module
     * @param string $controller
     */
    static public function buildController($module, $controller = '')
    {

        // 重写生成方法 by zhuxun37
        $c_layer = ucfirst(C('DEFAULT_C_LAYER'));
        // 如果控制器为空, 则取默认
        if (empty($controller)) {
            $controller = cfg('DEFAULT_CONTROLLER');
        }
        // 控制层文件名
        $file = APP_PATH . $module . '/' . $c_layer . '/' . $controller . $c_layer . EXT;
        $controls = explode('/', $controller);
        $act = array_pop($controls);
        // 如果 $controls 为空
        if (empty($controls)) {
            $cname_cpath = $c_layer;
            $tpl_path = $act;
        } else {
            $cname_cpath = $c_layer . '\\' . str_replace('/', '\\', implode('/', $controls));
            $tpl_path = implode('/', $controls) . '/' . $act;
        }
        // 如果定义了 ACTION_NAME
        if (defined(ACTION_NAME)) {
            $tpl_path .= '/' . ACTION_NAME;
            $acname = ACTION_NAME;
        } else {
            $tpl_path .= '/' . C('DEFAULT_ACTION');
            $acname = C('DEFAULT_ACTION');
        }
        // 判断控制层是否属于 Common
        $controller_tpl = self::$controller;
        if ('Common' == $module) {
            $base_controller = '\Think\Controller';
        } else {
            if ('Abstract' == $act) {
                $controller_tpl = self::$abstract_controller;
                $base_controller = '\Common\\' . $cname_cpath . '\Abstract' . $c_layer;
            } else {
                $base_controller = 'Abstract' . $c_layer;
                // 重建基类文件
                self::buildController($module, empty($controls) ? 'Abstract' : implode('/', $controls) . '/Abstract');
            }
        }
        if (!is_file($file)) {
            $content = str_replace(array(
                '[MODULE]',
                '[CONTROLLER]',
                '[CNAME_CPATH]',
                '[CNAME]',
                '[DEFAULT_ACTION]',
                '[TPL_PATH]',
                '[BASE_CONTROLLER]'
            ), array(
                $module,
                $act,
                $cname_cpath,
                $c_layer,
                $acname,
                $tpl_path,
                $base_controller
            ), $controller_tpl);
            if (!C('APP_USE_NAMESPACE')) {
                $content = preg_replace('/namespace\s(.*?);/', '', $content, 1);
            }
            $dir = dirname($file);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            self::chk_dir_secure($file);
            file_put_contents($file, $content);
        }
        // end
    }

    /**
     * 创建模型类
     *
     * @param string $module
     * @param string $model
     * @return boolean
     */
    static public function buildModel($module, $model)
    {

        $mname = 'Model';
        $tpl = self::$model;
        // 如果为 Service 层
        if ('Service' == substr($model, -7)) {
            $mname = 'Service';
            $model = substr($model, 0, -7);
            $tpl = 'Abstract' == $model ? self::$abstract_service : self::$service;
        } elseif ('Model' == substr($model, -5)) { // 如果为 Model 层
            $model = substr($model, 0, -5);
            $tpl = 'Abstract' == $model ? self::$abstract_model : self::$model;
        }
        // 文件名
        $file = APP_PATH . $module . '/' . $mname . '/' . $model . $mname . EXT;
        if (is_file($file)) { // 如果文件存在
            return true;
        }
        $content = str_replace(array(
            '[MODULE]',
            '[MODEL]'
        ), array(
            $module,
            $model
        ), $tpl);
        // 如果启用了命名空间
        if (!C('APP_USE_NAMESPACE')) {
            $content = preg_replace('/namespace\s(.*?);/', '', $content, 1);
        }
        // 取文件所在目录
        $dir = dirname($file);
        if (!is_dir($dir)) { // 如果目录不存在
            mkdir($dir, 0755, true);
        }
        // 写入文件
        file_put_contents($file, $content);
        // 如果是 Service 层
        if ('Service' == $mname) {
            // 重建基类文件
            if ('Common' != $module) {
                self::buildModel($module, 'AbstractService');
            }
        } else if ('Model' == $mname) {
            if ('Common' != $module) {
                self::buildModel($module, 'AbstractModel');
            }
        }

        return true;
    }

    /**
     * 创建模板文件
     *
     * @param string $tplFile 模板文件路径
     * @param string $tpl     模板内容
     * @return bool
     */
    static public function build_tpl($tplFile, $tpl)
    {

        $dir = dirname($tplFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        self::chk_dir_secure($dir);
        file_put_contents($tplFile, empty($tpl) ? self::$template : $tpl);

        return true;
    }

    /**
     * 生成目录安全文件
     *
     * @param array $dirs
     */
    static public function buildDirSecure($dirs = array())
    {

        // 目录安全写入（默认开启）
        defined('BUILD_DIR_SECURE') or define('BUILD_DIR_SECURE', true);
        if (BUILD_DIR_SECURE) {
            defined('DIR_SECURE_FILENAME') or define('DIR_SECURE_FILENAME', 'index.html');
            defined('DIR_SECURE_CONTENT') or define('DIR_SECURE_CONTENT', ' ');
            // 自动写入目录安全文件
            $content = DIR_SECURE_CONTENT;
            $files = explode(',', DIR_SECURE_FILENAME);
            foreach ($files as $filename) {
                foreach ($dirs as $dir) {
                    file_put_contents($dir . $filename, $content);
                }
            }
        }
    }

    /**
     * 创建默认操作方法
     * by zhuxun begin.
     *
     * @param string $module     模块名称
     * @param string $controller 控制器名称
     * @param string $action     具体操作名称
     * @return bool
     */
    static public function buildAction($module, $controller, $action, $isapi = false)
    {

        $c_layer = ucfirst(C('DEFAULT_C_LAYER'));
        $file = APP_PATH . $module . '/' . $c_layer . '/' . $controller . $c_layer . EXT;
        // 如果不是文件
        if (!is_file($file)) {
            return false;
        }
        $actionName = ucfirst(ACTION_NAME);
        // 模板路径
        $tpl_path = $controller . '/' . $actionName;
        if ($isapi) {
            $actionName = $actionName . '_' . strtolower(REQUEST_METHOD);
        }
        // 解析操作方法
        $action = str_replace(array(
            '[ACTION_NAME]',
            '[TPL_PATH]'
        ), array(
            $actionName,
            $tpl_path
        ), $isapi ? self::$action_api : self::$action);
        // 读取类文件
        $content = file_get_contents($file);
        // 增减方法
        $content = preg_replace('/^(.*?)\}\s*$/is', "\\1" . $action . "}\n", $content);
        // 写入文件
        file_put_contents($file, $content);

        return true;
    }
    // end.
}
