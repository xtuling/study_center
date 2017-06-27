<?php
/**
 * Created by PhpStorm.
 * 用户登出
 * User: zhoutao
 * Date: 2017-06-20
 * Time: 14:49:54
 */

namespace Api\Controller\Debug;

class LoginOutController extends AbstractController
{
    public function Index()
    {
        $oldCookie = $this->_cookie->get_cookie_data();
        $this->_cookie->destroy();

        $this->_result = [
            'oldCookie' => $oldCookie,
            'nowCookie' => $this->_cookie->get_cookie_data()
        ];
        return true;
    }
}
