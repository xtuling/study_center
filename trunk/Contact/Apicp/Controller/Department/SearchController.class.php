<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/17
 * Time: 23:20
 */

namespace Apicp\Controller\Department;

use VcySDK\Service;
use VcySDK\Department;

class SearchController extends AbstractController
{

    /**
     * 【通讯录】搜索部门
     * @author liyifei
     * @time 2016-09-19 14:50:22
     */
    public function Index_post()
    {

        // 接收参数
        $keyword = I('post.keyword');

        // 调用架构接口,搜索部门列表
        $departmentServ = new Department(Service::instance());
        $conds = [];
        if (!empty($keyword)) {
            $conds = [
                'dpName' => $keyword,
            ];
        }
        $result = $departmentServ->listAll($conds, 1, 99999);
        $list = [];
        foreach ($result['list'] as $v) {
            $list[] = [
                'department_id' => $v['dpId'],
                'name' => $v['dpName'],
                'user_total' => $v['departmentMemberCount'],
                'dept_level' => $v['dpLevel'],
            ];
        }

        $this->_result = [
            'list' => $list
        ];
    }
}
