<?php

namespace Api\Controller\Integral;

use Common\Common\Department;

class DepartmentsController extends AbstractController
{

    public function Index()
    {
        $memUid = $this->_login->user['memUid'];
        $dep = new Department();
        // 根据用户id获取当前部门及所有上级部门
        list($deps, $dpParentids) = $dep->list_dpId_by_uid($memUid, true);
        // 合并部门数据
        $deps = array_merge($deps, array_values($dpParentids));
        // 获取部门id及部门名称
        $deps = $dep->listById($deps, array('dpId', 'dpName', 'dpDisplayorder', 'dpLevel'));
        // 根据部门排序字段、部门等级来升序排序
        array_multisort(array_column($deps, 'dpLevel'), SORT_ASC,
            array_column($deps, 'dpDisplayorder'), SORT_ASC, $deps);

        $this->_result = array_values($deps);
        return true;
    }
}
