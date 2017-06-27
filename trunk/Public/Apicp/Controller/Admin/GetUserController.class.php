<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 16/9/18
 * Time: 上午9:16
 */

namespace Apicp\Controller\Admin;


class GetUserController extends AbstractController
{

    public function Index()
    {

        $this->_result = $this->_login->user;
        return true;
    }
}
