<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/7/28
 * Time: 下午8:04
 */

namespace Frontend\Controller\Member;

use Common\Common\WxApi;

class JsLoginController extends AbstractController
{

    public function Index()
    {

        $frontUrl = I('get._fronturl', '', 'trim');

        $user = [
            'uid' => $this->_login->user['memUid'],
            'username' => $this->_login->user['memUsername'],
            'face' => $this->_login->user['memFace'],
            'email' => $this->_login->user['memEmail'],
            'mobilephone' => $this->_login->user['memMobile'],
            'department' => $this->_login->user['dpName']
        ];

        $wxapi = &WxApi::instance();
        $jscfg = $wxapi->getJsSign($frontUrl);
        $result = generate_api_result(['user' => $user, 'jscfg' => $jscfg]);
        $javascript = "var _user = " . json_encode($result) . ";\nwindow.top.authComplete(_user);";

        // 如果是开发环境
        if (I('get._env') == 'dev') {
            exit($javascript);
        }

        $this->assign('javascript', $javascript);
        $this->_output('Common@Frontend/Redirect');

        return true;
    }
}

