<?php
/**
 * 后台登录-普通登陆：扫码单点登陆
 */

namespace Apicp\Controller\Admin;

use Com\Cookie;
use VcySDK\Qylogin;
use VcySDK\Service;
use VcySDK\Adminer;

class QrcodeLoginController extends AbstractAnonymousController
{

    public function Index()
    {

        // auth_code
        $authCode = I('post.auth_code');
        $qyLogin = new Qylogin(Service::instance());
        $loginData = $qyLogin->qrcodeLogin(['wxAuthCode' => $authCode]);

        // 需要授权安装应用
        if (isset($loginData['newFlag']) && $loginData['newFlag'] == Qylogin::WECHAT_NEW_ENTERPRISE) {
            $this->_result = ['newFlag' => $loginData['newFlag']];
            return true;
        }
        // 被锁定
        if ($loginData['adminerInfo']['eaUserstatus'] == cfg('CP_ADMINER_STATUS_DENY')) {
            $this->_set_error("_ERR_LOGIN_FORBIDDEN");
            return false;
        }

        // 记录日志
        Service::instance()->setConfig([
            'enumber' => $loginData['enterpriseInfo']['epEnumber']
        ]);
        $adminer = new Adminer(Service::instance());
        $adminer->loginLog(array(
            'eaId' => $loginData['adminerInfo']['eaId'],
            'eaIp' => get_client_ip(),
            'eaLastlogin' => MILLI_TIME
        ));

        // 重置cookie密钥
        $this->_startCookie();
        $cookie = Cookie::instance();
        $cookie->setCookieSecret(md5(cfg('COOKIE_SECRET') . $loginData['enterpriseInfo']['epEnumber']));
        // 写Cookie
        $uPassword = $this->_login->getAuthPwd($loginData['adminerInfo']['eaId'], $loginData['enterpriseInfo']['epEnumber']);
        $this->_login->flushAuth($loginData['adminerInfo']['eaId'], $uPassword, $loginData['enterpriseInfo']['epEnumber']);
        $this->_result = $loginData;

        return true;
    }
}
