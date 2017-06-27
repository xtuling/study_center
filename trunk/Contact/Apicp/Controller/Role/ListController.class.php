<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 2017/5/11
 * Time: 下午3:13
 */

namespace Apicp\Controller\Role;


use Common\Service\RoleService;

class ListController extends AbstractController
{

    public function Index_post()
    {

        $roleService = new RoleService();
        $roleService->searchList($this->_result, I('post.'));

        return true;
    }

}