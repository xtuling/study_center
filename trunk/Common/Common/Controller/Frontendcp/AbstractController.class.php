<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace Common\Controller\Frontendcp;

use Common\Common\Cache;
use Think\Cache\Driver\Redis;
use Think\Controller;
use Com\Cookie;
use Common\Common\Logincp;
use Com\Formhash;
use Think\Db;
use Think\Db\Driver\Mysql;
use Think\Exception;
use VcySDK\Service;
use Think\Log;

abstract class AbstractController extends Controller
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
     * @type null|Logincp
     */
    protected $_login = null;

    /**
     * 站点配置
     *
     * @var array
     */
    protected $_setting = array();

    /**
     * 企业配置信息
     *
     * @var array
     */
    protected $config = [];

    /**
     * 插件信息
     *
     * @var array $_plugin
     */
    protected $_plugin = array();

    /**
     * 获取外部人员openid
     *
     * @var string $_exterior_openid
     */
    protected $_exterior_openid = false;

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
                'fileConvertApiUrl' => cfg('FILE_CONVERT_API_URL')
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
            // 解决手机系统为安卓时,JsLogin不跳转的问题
            $os = I('get._os', '', 'trim');
            $top = I('get._top', '', 'trim');
            $this->assign('os', $os);
            $this->assign('top', $top);
            // 检查是否登陆
            $this->_userLogin();
        } catch (Exception $e) {
            $this->_repair_error($e);
            $this->_output('Common@Frontend/Error');
        } catch (\Exception $e) {
            // 记录异常
            Log::record($e->getMessage() . ':' . $e->getCode());
            $this->_output('Common@Frontend/Error');
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
            $this->error(L('_ERR_EMPTY_IDENTIFIER'));
            return false;
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
    }

    /**
     * 用户登陆
     *
     * @return boolean
     */
    protected function _userLogin()
    {

        // 用户信息初始化
        $this->_login = &Logincp::instance();
        $this->_login->initUser();

        if (empty($this->_login->user) && $this->_require_login) {
            $this->assign('redirectUrl', cpUrl('/admincp'));
            $this->_output('Common@Frontend/Redirect');
            return false;
        }

        return true;
    }

    /**
     * output
     * 输出模板
     *
     * @param string $tpl 引入的模板
     *
     * @return bool
     */
    protected function _output($tpl)
    {

        // 域名信息
        $this->view->assign('domain', $this->_setting['domain']);
        // 输出当前用户信息
        $this->view->assign('wbs_user', $this->_login->user);
        if (! empty($this->_login->user)) {
            $this->view->assign('wbs_uid', $this->_login->user['memUid']);
            $this->view->assign('wbs_username', $this->_login->user['memUsername']);
        } else {
            $this->view->assign('wbs_uid', 0);
            $this->view->assign('wbs_username', '');
        }

        // 输出 forumHash
        parent::_output($tpl);
        return true;
    }

}
