<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/11/17
 * Time: 15:42
 */

namespace Apicp\Controller\Department;

use VcySDK\Service;
use VcySDK\Department;

class ChangeOrderController extends AbstractController
{

    /**
     * 【通讯录】修改部门顺序号接口
     * @author liyifei
     */
    public function Index_post()
    {

        $dpId = I('post.department_id', '', 'trim');
        $order = I('post.order', '', 'float');

        if (strlen($dpId) == 0 || strlen($order) == 0) {
            E('_ERR_PARAM_UNDEFINED');
        }

        $dpServ = new Department(Service::instance());
        $upConds = [
            'dpId' => $dpId,
            'dpDisplayorder' => $order,
        ];
        $dpServ->modify($upConds);

        $this->clearDepartmentCache();

        return true;
    }
}