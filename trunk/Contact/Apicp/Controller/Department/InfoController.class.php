<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/17
 * Time: 23:02
 */

namespace Apicp\Controller\Department;

use VcySDK\Service;
use VcySDK\Department;

class InfoController extends AbstractController
{

    /**
     * 【通讯录】部门详情
     * @author liyifei
     * @time 2016-09-17 22:38:54
     */
    public function Index_post()
    {

        // 接收参数
        $dpId = I('post.department_id', '', 'trim');
        if (empty($dpId)) {
            E('_ERR_DEPARTMENT_ID_IS_NULL');
            return false;
        }

        // 调用架构接口,查询部门详情
        $department = new Department(Service::instance());
        $data = $department->detail(['dpId' => $dpId]);

        $this->_result = [
            "name" => $data['dpName'],
            "route" => $data['departmentPath'],
            "department_total" => $data['childrensDepartmentCount'],
            "user_total" => $data['departmentMemberCount'],
        ];
    }

    /**
     * 模拟数据接口:【通讯录】部门详情
     * @author liyifei
     * @time 2016-09-17 22:38:54
     */
    public function Test_get()
    {

        $this->_result = [
            "name" => "产品部",
            "route" => "卡松实验室-产品部",
            "department_total" => 12,
            "user_total" => 16
        ];
    }
}
