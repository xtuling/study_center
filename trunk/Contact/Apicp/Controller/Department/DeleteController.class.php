<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/17
 * Time: 23:14
 */

namespace Apicp\Controller\Department;

use VcySDK\Service;
use VcySDK\Department;

class DeleteController extends AbstractController
{

    /**
     * 【通讯录】删除部门
     * @author liyifei
     * @time 2016-09-20 18:57:18
     */
    public function Index_post()
    {

        $id = I('post.department_id', '', 'trim');
        if (empty($id)) {
            E('_ERR_DEPARTMENT_ID_IS_NULL');
        }

        $dpServ = new Department(Service::instance());
        $dpServ->delete(['dpId' => $id]);

        $this->clearDepartmentCache();

        return true;
    }
}
