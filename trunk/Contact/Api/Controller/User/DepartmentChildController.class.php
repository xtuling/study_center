<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/10/18
 * Time: 18:16
 */

namespace Api\Controller\User;

use VcySDK\Service;
use VcySDK\Department;
use Common\Service\InviteUserService;

class DepartmentChildController extends AbstractController
{

    /**
     * 【通讯录】子部门列表
     * @author liyifei
     */
    public function Index_post()
    {

        // 接收参数并验证
        $dpId = I('post.department_id', '', 'trim');
        if (empty($dpId)) {
            E('_ERR_DEPTID_IS_NULL');
            return false;
        }

        $user = $this->_login->user;
        if (empty($user['memUid'])) {
            E('_ERR_NOT_LOGIN');
            return false;
        }

        // 获取该部门下子部门列表
        $deptServ = new Department(Service::instance());
        $data = $deptServ->listAll(['dpId' => $dpId], 1, 999);
        if (empty($data['list'])) {
            E('_ERR_CHILD_DEPT_UNDEFINED');
            return false;
        }

        // 排序
        $column = array_column($data['list'], 'dpDisplayorder');
        array_multisort($data['list'], $column);

        // 格式化返回
        $result = [];
        foreach ($data['list'] as $v) {
            $result[] = [
                'dp_id' => $v['dpId'],
                'name' => $v['dpName'],
                'is_child' => $v['isChildDepartment'],
            ];
        }

        $this->_result = $result;
    }
}
