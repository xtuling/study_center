<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/7/22
 * Time: 下午3:46
 */

namespace Api\Controller\Debug;

use Com\Cookie;
use Common\Common\Login;
use Common\Common\User;

class LoginController extends AbstractController
{

    /**
     * 用户登陆
     *
     * @return boolean
     */
    protected function _userLogin()
    {

        $this->_login = &Login::instance();
        return true;
    }

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

        $uid = I('get.uid', '', 'trim');
        if (empty($uid)) {
            return false;
        }

        // 获取用户数据
        $User = &User::instance();
        $userData = $User->getByUid($uid);

        // 检查用户信息中必须存在的字段
        if (empty($userData) || empty($userData['memUid']) || empty($userData['memUsername'])) {
            E('_ERR_INVALID_USER');
        }

        // 重新设置 Cookie 签名
        $this->_login->flushAuth($userData['memUid'], QY_DOMAIN);

        $this->_result = $userData;

        return true;
    }
}
