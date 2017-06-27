<?php
/**
 * 后台登录 - 普通登陆: loginToken 登陆
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 2016/9/14
 * Time: 15:35
 *
 */

namespace Apicp\Controller\Admin;

use Com\Cache;
use VcySDK\Service;
use VcySDK\Adminer;

class LoginController extends AbstractAnonymousController
{

    /**
     * SDK的Adminer对象
     *
     * @var Adminer
     */
    protected $_adminer;

    public function before_action($action = '')
    {

        if (! parent::before_action($action)) {
            return false;
        }

        // 调用UC，登陆接口
        $serv_sdk = &Service::instance();

        // 实例化
        $this->_adminer = new Adminer($serv_sdk);

        return true;
    }

    public function Index()
    {

        // 接收数据
        $loginToken = I('post.loginToken'); // 用户手机
        $passwd = I('post.passwd'); // 密码

        // 解析加密字串
        $data = array();
        if (! $this->_login->parseLoginToken($data, $loginToken, $passwd, cfg('CP_LOGIN_TOKEN_EXPIRE'))) {
            $this->_set_error('_ERR_LOGIN_TOKEN_EXPIRED');
            return false;
        }

        list($enumber, $eaId) = $data;
        // 记录登录日志
        $this->_adminer->loginLog(array(
            'eaId' => $eaId,
            'eaIp' => get_client_ip(),
            'eaLastlogin' => MILLI_TIME
        ));

        // 写 Cookie
        $this->_login->flushAuth($eaId, $this->_login->getAuthPwd($eaId, $enumber), $enumber);
        // 写入密码Cookie，为了每次验证密码是否被改变
        $this->_login->setCookie('pwdAuth', $passwd);

        return true;
    }

}

