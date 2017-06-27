<?php
/**
 * Created by IntelliJ IDEA.
 * 获取套件授权登录信息
 * User: zhoutao
 * Date: 2016/9/28
 * Time: 上午9:42
 */

namespace Apicp\Controller\Plugin;

use Com\Cookie;
use VcySDK\EnterprisePlugin;
use VcySDK\Service;

class SuiteLoginInfoController extends AbstractController
{

    protected $_require_login = false;

    public function Index()
    {

        $token = I('post.token', '', 'strval');
        $sdk = new EnterprisePlugin(Service::instance());
        $this->_result = $sdk->suiteLoginInfo(['token' => $token]);

        // 重置cookie密钥
        $this->_startCookie();
        $cookie = Cookie::instance();
        $cookie->setCookieSecret(md5(cfg('COOKIE_SECRET') . $this->_result['enterpriseInfo']['epEnumber']));

        // 写cookie
        $eaId = $this->_result['adminerInfo']['eaId'];
        $enumber = $this->_result['enterpriseInfo']['epEnumber'];
        $pwd = $this->_login->getAuthPwd($eaId, $enumber);
        $this->_login->flushAuth($eaId, $pwd, $enumber);

        return true;
    }
}
