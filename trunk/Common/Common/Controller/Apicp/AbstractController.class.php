<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace Common\Controller\Apicp;

use Common\Common\Cache;
use Com\Error;
use Com\Formhash;
use Common\Common\Logincp;
use Think\Cache\Driver\Redis;
use Think\Controller\RestController;
use Com\Cookie;
use Think\Db;
use Think\Db\Driver\Mysql;
use Think\Exception;
use Think\Log;
use VcySDK\Service;
use Com\VerifyAuth;
use VcySDK\Adminer;
use Think\Stat;

abstract class AbstractController extends RestController
{

    /**
     * 是否必须登录
     *
     * @var string
     */
    protected $_require_login = true;

    /**
     * 是否必须验证权限
     */
    protected $_require_verify_auth = false;

    /**
     * cookie
     *
     * @type null|Cookie
     */
    protected $_cookie = null;

    /**
     * Login
     *
     * @type null|Logincp
     */
    protected $_login = null;

    /**
     * 站点配置
     *
     * @var array $_setting
     */
    protected $_setting = array();

    /**
     * 返回结果
     *
     * @var array
     */
    protected $_result = array();

    /**
     * 企业配置信息
     *
     * @var array
     */
    protected $config = [];

    // 前置操作
    public function before_action($action = '')
    {

        try {
            // 后台页面标识
            cfg('IS_CP', true);

            // 检查标识符
            $this->_identifier();

            // cookie
            if (cfg('COMMON_DOMAIN') != rstrtolower(QY_DOMAIN)) {
                $this->_startCookie();
            }

            // 初始化SDK
            $config = array(
                'apiUrl' => cfg('UC_APIURL'),
                'enumber' => QY_DOMAIN,
                'pluginIdentifier' => APP_IDENTIFIER,
                'thirdIdentifier' => cfg('SDK_THIRD_IDENTIFIER'),
                'logPath' => RUNTIME_PATH . '/Logs/VcySDK/',
                'apiSecret' => cfg('API_SECRET'),
                'apiSigExpire' => cfg('API_SIG_EXPIRE'),
                'fileConvertApiUrl' => cfg('FILE_CONVERT_API_URL')
            );
            $service = &Service::instance();
            $service->initSdk($config);

            // 企业配置信息(如果是企业相关接口, 则读取)
            $this->_login = &Logincp::instance();
            if (cfg('COMMON_DOMAIN') != rstrtolower(QY_DOMAIN)) {
                $cache = &Cache::instance();
                $this->config = $cache->get('Common.EnterpriseConfig');
                $service->setConfig([
                    'appid' => $this->config['wxqyCorpid'],
                ]);

                // 检查是否登陆
                $this->_userLogin();
                // 判断是否有权限
                $this->_checkAuth();

                // 加载接口访问统计
                $userInfo = [
                    'isAdmincp' => 1,
                    'userId' => !empty($this->_login->user) ? $this->_login->user['eaId'] : '',
                    'userName' => !empty($this->_login->user) ? $this->_login->user['eaRealname'] : '',
                    'cookie' => $this->_cookie
                ];
                $stat = new Stat(cfg('STAT_OPTIONS'), $userInfo);
                $stat::save();
                // 接口访问统计 End
            }
        } catch (Exception $e) {
            Log::record($e->getMessage() . ':' . $e->getCode());
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
     * @param string $action 操作名称
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
     * 判断是否有权限
     */
    protected function _checkAuth()
    {

        // 如果不是必须验证权限，或者为超级管理员，就返回true
        if (! $this->_require_verify_auth || $this->_login->user['eaLevel'] == Adminer::SUPER_MANAGER) {
            return true;
        }

        // 验证权限
        $check_auth = new VerifyAuth($this->_login->user['eaId']);

        // 没有权限直接返回
        if ($check_auth->verify() == false) {
            $this->_set_error('_ERR_FORBIDDEN_ACTION');
            $this->_response();
            return false;
        }

        return true;
    }

    /**
     * 判断是否登陆
     */
    public function _userLogin()
    {
        $this->_login->initUser();

        // 如果需要强制登录
        if ($this->_require_login && empty($this->_login->user['eaId'])) {
            $this->_cookie->destroy();
            $this->_set_error('PLEASE_LOGIN');
            $this->_response();

            return false;
        }

        return true;
    }

    // 生成 formhash
    protected function _generate_formhash()
    {

        // 拼凑源字串
        $fh_key = I('server.HTTP_HOST') . cfg('formhash_secret') . $this->_setting['formhash_key'];
        if (! empty($this->_login->user)) {
            $fh_key .= $this->_login->user['eaId'] . $this->_login->user['eaRealname'];
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
     * 重写输出方法
     *
     * @param $data
     * @param $type
     * @param $code
     *
     * @return bool
     */
    public function response($data, $type = 'json', $code = 200)
    {

        $this->_response($data, $type, $code);

        return true;
    }

    /**
     * 重写输出方法
     *
     * @param mixed  $data 输出数据
     * @param string $type 输出类型
     * @param int    $code 返回状态
     *
     * @see \Think\Controller\RestController::_response()
     */
    protected function _response($data = null, $type = 'json', $code = 200)
    {

        // 如果需要返回的是异常
        if ($data instanceof Exception) {
            // 如果是显示给用户的错误
            if ($data->is_show()) {
                Error::instance()->set_error($data);
            } else {
                // 如果是系统错误, 则显示默认错误
                $this->_set_error('_ERR_DEFAULT');
            }

            $data = '';
        } elseif ($data instanceof \Exception) {
            $code = $data->getCode();
            $message = $data->getMessage();

            if (! empty($message)) {
                Error::instance()->set_error($data);
            } else {
                // 系统报错
                $data = '';
                $this->_set_error('_ERR_DEFAULT');
            }
        }

        // 输出结果
        parent::_response(generate_api_result(null == $data ? $this->_result : $data), $type, $code);
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
    }

}
