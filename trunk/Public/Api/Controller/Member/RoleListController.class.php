<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 2017/6/2
 * Time: 上午10:27
 */

namespace Api\Controller\Member;

use VcySDK\Role;
use VcySDK\Service;

class RoleListController extends AbstractController
{

    public function Index()
    {

        $roleSDK = new Role(Service::instance());
        $roleList = $roleSDK->listAll();
        $this->_result = $roleList['list'];
        return true;
    }

}