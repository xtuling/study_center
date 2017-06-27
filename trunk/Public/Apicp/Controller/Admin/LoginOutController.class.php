<?php
/**
 * 后台登录-退出登录
 * Created by PhpStorm.
 * User: 原习斌
 * Date: 2016/8/23
 */

namespace Apicp\Controller\Admin;

class LoginOutController extends AbstractAnonymousController
{

    public function before_action($action = '')
    {

        if (! parent::before_action($action)) {
            return false;
        }

        return true;
    }

    public function Index()
    {

        $this->_startCookie();
        $this->_cookie->destroy();

        return true;
    }

}
