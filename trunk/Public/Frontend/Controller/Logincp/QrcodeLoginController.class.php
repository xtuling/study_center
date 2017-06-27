<?php
/**
 * 后台登录-二维码登录
 */

namespace Frontend\Controller\Logincp;

class QrcodeLoginController extends AbstractController
{

    public function Index()
    {

        // 跳转 两次urlencode是因为有两次跳转
        $ref = I('get.ref');
        if (empty($ref) || ! preg_match('/^https?\:\/\//i', $ref)) {
            $ref = oaUrl(cfg('PROTOCAL') . $_SERVER['HTTP_HOST'] . '/admincp/#/login');
        }

        $redirectUri = urlencode(urlencode($ref));
        $url = "http://www.vchangyi.com/sso/index.php?corp_id=wxa7044ee8255576b0&ref=" . $redirectUri;
        header('Location: ' . $url);

        return true;
    }
}
