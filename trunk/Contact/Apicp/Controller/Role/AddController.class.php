<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 2017/5/11
 * Time: 下午3:14
 */

namespace Apicp\Controller\Role;


use Common\Service\RoleService;

class AddController extends AbstractController
{

    public function Index_post()
    {

        $roleService = new RoleService();
        $roleService->add($this->_result, I('post.'));

        return true;
    }

}