<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/7/22
 * Time: 下午3:46
 */

namespace Apicp\Controller\Debug;

use Common\Common\User;
use VcySDK\Adminer;
use VcySDK\Service;

class LoginController extends AbstractController
{

    /**
     * SetCookie
     * @desc 写入Cookie, 并返回人员数据
     * @param string uid:true:****** 用户UID
     *
     * @return array 用户信息
     *               array(
     *               'memUid' => '', // 用户UID
     *               'memUsername' => '', // 用户名
     *               'memUserid' => '', // 用户在微信端的唯一标识
     *               'dpName' => array(), // 所在部门列表
     *               'tagName' => array(), // 所在标签列表
     *               'memMobile' => '', // 用户手机号
     *               'memFace' => '' // 用户头像
     *               )
     */
    public function SetCookie_get()
    {

        $eaId = I('get.eaId', '', 'trim');
        if (empty($eaId)) {
            E('1001:管理员标识为空');
            return false;
        }

        $sdkAdminer = new Adminer(Service::instance());
        $member = $sdkAdminer->fetch(['eaId' => $eaId]);

        // 写 Cookie
        $this->_login->flushAuth($member['eaId'], $this->_login->getAuthPwd($member['eaId'], QY_DOMAIN), QY_DOMAIN);
        // 写入密码Cookie，为了每次验证密码是否被改变
        //$this->_login->setCookie('pwdAuth', $member['eaPasswd']);

        $this->_result = $member;

        return true;
    }
}
