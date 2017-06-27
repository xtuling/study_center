<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/17
 * Time: 23:15
 */

namespace Apicp\Controller\User;

use Common\Common\Integral;
use Common\Common\User;
use \Common\Service\UserService;

class DepartmentMemberController extends AbstractController
{

    /**
     * 【通讯录】部门人员列表、人员搜索
     * @author liyifei
     * @time   2016-09-17 23:15:42
     */
    public function Index_post()
    {

        // 接收定义参数
        $dpId = I('post.department_id', '', 'trim');
        $keyword = I('post.keyword', '', 'trim');
        $status = I('post.status', '', 'trim');
        $active = I('post.active', '', 'trim');
        $mobile = I('post.mobile', '', 'trim');
        $email = I('post.email', '', 'trim');
        $page = I('post.page', 1, 'intval');
        $limit = I('post.limit', 30, 'intval');

        // UC查询条件
        $conds = [
            'departmentChildrenFlag' => UserService::DEPT_CHILDREN_FLAG,
            'memUsername' => $keyword,
            'memSubscribeStatus' => $status,
            'memMobile' => $mobile,
            'memEmail' => $email,
            'memActive' => $active
        ];
        if (!empty($dpId)) {
            $conds['dpIdList'] = (array)$dpId;
        }

        // UC排序规则
        $orderList = [
            'memIndex' => 'ASC',
        ];

        $newUser = new User();
        $result = $newUser->listByConds($conds, $page, $limit, $orderList);

        // 整理返回值
        $list = [
            'page' => $result['pageNum'],
            'limit' => $result['pageSize'],
            'total' => $result['total'],
            'status_list' => [
                [
                    "status" => UserService::USER_STATUS_ALL,
                    "name" => "全部",
                    "user_total" => $result['amount'],
                ],
                [
                    "status" => UserService::USER_STATUS_FOLLOW,
                    "name" => "已关注",
                    "user_total" => $result['alreadyConcerned'],
                ],
                [
                    "status" => UserService::USER_STATUS_DISABLE,
                    "name" => "已禁用",
                    "user_total" => $result['disable'],
                ],
                [
                    "status" => UserService::USER_STATUS_UNFOLLOW,
                    "name" => "未关注",
                    "user_total" => $result['notConcerned'],
                ],
            ]
        ];

        // 获取积分列表
        if (!empty($result['list'])) {
            $integrals = Integral::instance()->listByUid(array_column($result['list'], 'memUid'));
        } else {
            $integrals = array();
        }

        // 返回部门成员列表
        $list['list'] = array();
        foreach ($result['list'] as $k => $v) {
            $list['list'][$k]['uid'] = $v['memUid'];
            $list['list'][$k]['face'] = $v['memFace'];
            $list['list'][$k]['name'] = $v['memUsername'];
            $list['list'][$k]['sex'] = $v['memGender'];
            $list['list'][$k]['mobile'] = $v['memMobile'];
            $list['list'][$k]['email'] = $v['memEmail'];
            $list['list'][$k]['status'] = $v['memSubscribeStatus'];
            $list['list'][$k]['job'] = $v['memJob'];
            $list['list'][$k]['active'] = $v['memActive'];
            $list['list'][$k]['dp_name'] = $v['dpName'];
            $list['list'][$k]['integral'] = $integrals[$v['memUid']];
        }

        $this->_result = $list;
    }
}
