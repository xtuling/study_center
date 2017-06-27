<?php
/**
 * Created by PhpStorm.
 * User: Slok
 * Date: 2016/10/18
 * Time: 17:58
 */

namespace Api\Controller\Invite;

use Common\Common\Login;

abstract class CheckUserController extends AbstractController
{
    protected $_require_login = false;

    /**
     * 用户登陆
     * @author zhonglei
     * @return boolean
     */
    protected function _userLogin()
    {

        // 用户信息初始化
        $this->_login = &Login::instance();
        $this->_login->initUser();

        // 内部人员不允许访问
        if (!empty($this->_login->user)) {
            E('_ERR_INUSER_ACCESS_DENIED');
        }

        return true;
    }
}
