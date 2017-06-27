<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace Common\Controller\Api;

use Common\Common\Cache;
use Think\Cache\Driver\Redis;
use Think\Controller\RestController;
use Com\Cookie;
use Common\Common\Login;
use Think\Db;
use Think\Db\Driver\Mysql;
use Think\Log;
use Think\Exception;
use Com\Formhash;
use Com\Error;
use VcySDK\Service;
use Think\Stat;

abstract class AbstractController extends RestController
{

    /**
     * 是否必须登录
     *
     * @var string $_require_login
     */
    protected $_require_login = true;

    /**
     * cookie
     *
     * @type null|Cookie
     */
    protected $_cookie = null;

    /**
     * user
     *
     * @type null|Login
     */
    protected $_login = null;

    /**
     * 站点配置
     *
     * @var array $_setting
     */
    protected $_setting = array();

    /**
     * 是否 A/R 方法调用
     *
     * @var $_is_a_r
     */
    protected $_is_a_r = false;

    /**
     * 返回结果
     *
     * @var array $_result
     */
    protected $_result = array();


    /**
     * 企业配置信息
     *
     * @var array
     */
    protected $config = [];

    // 人员ID
    protected $uid = '';

    /**
     * 前置操作
     *
     * @param string $action
     *
     * @return bool
     */
    public function before_action($action = '')
    {

        try {
            // cookie
            $this->_startCookie();

            // 检查标识符
            $this->_identifier();

            // 初始化SDK
            $config = array(
                'apiUrl' => cfg('UC_APIURL'),
                'enumber' => QY_DOMAIN,
                'pluginIdentifier' => APP_IDENTIFIER,
                'thirdIdentifier' => cfg('SDK_THIRD_IDENTIFIER'),
                'logPath' => RUNTIME_PATH . '/Logs/VcySDK/',
                'apiSecret' => cfg('API_SECRET'),
                'apiSigExpire' => cfg('API_SIG_EXPIRE'),
                'fileConvertApiUrl' => cfg('FILE_CONVERT_API_URL'),
            );
            $service = &Service::instance();
            $service->initSdk($config);

            // 企业配置信息(如果是企业相关接口, 则读取)
            if (cfg('COMMON_DOMAIN') != rstrtolower(QY_DOMAIN)) {
                $cache = &Cache::instance();
                $this->config = $cache->get('Common.EnterpriseConfig');
                $service->setConfig([
                    'appid' => $this->config['wxqyCorpid'],
                ]);
            }
            // 检查是否登陆
            $this->_userLogin();

            // 加载接口访问统计
            $userInfo = [
                'isAdmincp' => 0,
                'userId' => !empty($this->_login->user) ? $this->_login->user['memUid'] : '',
                'userName' => !empty($this->_login->user) ? $this->_login->user['memUsername'] : '',
                'cookie' => $this->_cookie
            ];
            $stat = new Stat(cfg('STAT_OPTIONS'), $userInfo);
            $stat::save();
            // 接口访问统计 End

        } catch (Exception $e) {
            Log::record($e->getMessage() . ':' . $e->getCode());
            $this->_repair_error($e);
            $this->_response($e);
        } catch (\Exception $e) {
            // 记录异常
            Log::record($e->getMessage() . ':' . $e->getCode());
            $this->_response($e);
        }

        return true;
    }

    /**
     * 后置操作
     *
     * @param string $action
     *
     * @return bool
     */
    public function after_action($action = '')
    {

        $this->_response();

        return true;
    }

    /**
     * 清理各种连接
     *
     * @return bool
     */
    public function __destruct()
    {

        // 关闭 redis
        $redis = \Think\Cache::getInstance();
        if ($redis instanceof Redis) {
            $redis->close();
        }

        // 关闭 mysql
        $mysql = Db::getInstance();
        if ($mysql instanceof Mysql) {
            $mysql->close();
        }

        parent::__destruct();
    }

    /**
     * 获取 identifier
     *
     * @return bool
     */
    protected function _identifier()
    {

        // 接收唯一标示符
        $_identifier = APP_IDENTIFIER;

        if (empty($_identifier)) {

            $this->_set_error('_ERR_EMPTY_IDENTIFIER');
            $this->_response();

            return false;
        }

        return true;
    }

    /**
     * 初始化 cookie
     *
     * @return boolean
     */
    protected function _startCookie()
    {

        $domain = cfg('COOKIE_DOMAIN');
        $expired = cfg('COOKIE_EXPIRE');
        $secret = md5(cfg('COOKIE_SECRET') . QY_DOMAIN);
        // 初始化
        $this->_cookie = &Cookie::instance($domain, $expired, $secret);
        ob_start(array($this->_cookie, 'send'));

        return true;
    }

    /**
     * 用户登陆
     *
     * @return boolean
     */
    protected function _userLogin()
    {

        $this->_login = &Login::instance();

        // 初始化用户信息
        if (!$this->_login->initUser()) {
            $code = I('get.code');

            // 使用 Code 或根据配置文件中的 DEBUG_UID 进行登陆
            if (!empty($code) || cfg('DEBUG_UID')) {
                $this->_login->autoLogin();

                // 跳转到微信授权
            } else {
                return true;
                $this->assign('redirectUrl', $this->_login->getAuthUrl());
                $this->_output('Common@Frontend/Redirect');
            }
        }

        if ($this->_require_login && empty($this->_login->user)) {
            E('PLEASE_LOGIN');
        }

        // 简化常用的人员ID变量
        if (!empty($this->_login->user)) {
            $this->uid = $this->_login->user['memUid'];
        }

        return true;
    }

    /**
     * 针对一些固定错误进行修复
     *
     * @param mixed $e 错误信息
     *
     * @return boolean
     */
    protected function _repair_error($e)
    {

        return true;
    }

    /**
     * 生成 formhash
     * {@inheritDoc}
     *
     * @see \Think\Controller::_generate_formhash()
     */
    protected function _generate_formhash()
    {

        // 拼凑源字串
        $fh_key = I('server.HTTP_HOST') . cfg('formhash_secret');
        if (!empty($this->_login->user)) {
            $fh_key .= $this->_login->user['memUid'] . $this->_login->user['memUsername'];
        }

        // 生成 hash
        $formhash = &Formhash::instance();
        $hash = '';
        $formhash->generate($hash, $fh_key);

        return $hash;
    }

    /**
     * 设置错误信息
     *
     * @param mixed $message 错误信息
     * @param int   $code    错误号
     *
     * @return bool
     */
    protected function _set_error($message, $code = 0)
    {

        Error::instance()->set_error($message, $code);

        return true;
    }

    /**
     * 暴露报错方法
     *
     * @param mixed  $data 数据对象
     * @param string $type 数据类型
     * @param int    $code 错误码
     *
     * @return bool
     */
    public function response($data = null, $type = 'json', $code = 200)
    {

        return $this->_response($data, $type, $code);
    }

    /**
     * 重写输出方法
     *
     * @param mixed  $data 输出数据
     * @param string $type 输出类型
     * @param int    $code 返回状态
     *
     * @return bool
     */
    protected function _response($data = null, $type = 'json', $code = 200)
    {

        // 如果是 A/R 方法调用, 则不输出.
        if ($this->_is_a_r) {
            return true;
        }

        // 如果需要返回的是异常
        if ($data instanceof Exception) {
            // 如果是显示给用户的错误
            if ($data->is_show() || APP_DEBUG) {
                Error::instance()->set_error($data);
            } else {
                // 如果是系统错误, 则显示默认错误
                $this->_set_error('_ERR_DEFAULT');
            }

            $data = null;
        } elseif ($data instanceof \Exception) {
            $code = $data->getCode();
            $message = $data->getMessage();

            if (!empty($message)) {
                Error::instance()->set_error($data);
            } else {
                // 系统报错
                $data = '';
                $this->_set_error('_ERR_DEFAULT');
            }
        }

        parent::_response(generate_api_result(null == $data ? $this->_result : $data), $type, $code);
        return true;
    }

    /**
     * 魔术方法 有不存在的操作的时候执行(重写)
     *
     * @access public
     *
     * @param string $method 方法名
     * @param array  $args   参数
     *
     * @return mixed
     */
    public function __call($method, $args)
    {

        try {
            if (0 === strcasecmp($method, ACTION_NAME . cfg('ACTION_SUFFIX'))) {
                if (method_exists($this, $method . '_' . $this->_method . '_' . $this->_type)) {
                    // RESTFul方法支持
                    $fun = $method . '_' . $this->_method . '_' . $this->_type;
                    $this->$fun();
                } elseif (method_exists($this, $method . '_' . $this->_method)) {
                    $fun = $method . '_' . $this->_method;
                    $this->$fun();
                } elseif (method_exists($this, '_empty')) {
                    if ($this->_build_action()) {
                        // 报生成成功信息
                        E(__CLASS__ . ':' . $method . '_' . $this->_method . L('METHOD_CREATED'));
                    }

                    // 如果定义了_empty操作 则调用
                    $this->_empty($method, $args);
                } else {
                    E(L('_ERROR_ACTION_') . ':' . ACTION_NAME);
                }
            }
        } catch (Exception $e) {
            // 记录日志
            Log::record($e->getMessage());
            // 返回错误
            $this->_response($e);
        } catch (\Exception $e) {
            // 记录日志
            Log::record($e->getMessage());
            // 返回错误
            $this->_response($e);
        }
    }

    // 空方法, 在未找到处理方法时调用
    protected function _empty()
    {

        E('_ERROR_ACTION_');

        return true;
    }

}
