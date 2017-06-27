<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/18
 * Time: 11:50
 */

namespace Api\Controller\User;

use Common\Service\UserService;

class DepartmentMemberController extends AbstractController
{

    /**
     * 【通讯录】人员列表
     * @author liyifei
     * @time   2016-09-18 11:58:28
     */
    public function Index_post()
    {

        // 接收参数并验证
        $dpId = I('post.department_id', '', 'trim');
        $keyword = I('post.keyword', '', 'trim');
        $index = I('post.first_letter', '', 'trim');
        $page = I('post.page', 1, 'intval');
        $limit = I('post.limit', 30, 'intval');

        // 登录用户信息
        $user = $this->_login->user;

        // 获取缓存用户列表信息
        $userService = new UserService();
        $this->checkCurrentManagePower($user);

        // 读取用户有权限查看的部门列表
        list(, , $childIds,) = $userService->getUserTopDpId($user);
        if (empty($childIds) || !in_array($dpId, $childIds)) {
            E('1009:您的权限已变更或该部门信息已更新');
            return false;
        }

        $condition = [
            'uid' => $user['memUid'],
            'dpId' => $dpId,
            'keyword' => $keyword,
            'index' => $index,
            'departmentChildrenFlag' => empty($keyword) ? 0 : 1
        ];
        $userList = $userService->getListByConds($condition, $page, $limit);

        // 当$userList中设置memUsername时,即认为返回的$userList为单个用户的详情
        if (isset($userList['memUid'])) {
            $result = [
                'page' => $page,
                'limit' => $limit,
                'total' => 1,
                'list' => [
                    'key' => '',
                    'valuelist' => [
                        'uid' => $userList['memUid'],
                        'username' => $userList['memUsername'],
                        'face' => $userList['memFace'],
                        'title' => $userList['memJob'],
                    ]
                ],
            ];

        } else {
            $result = [
                'page' => $userList['pageNum'],
                'limit' => $userList['pageSize'],
                'total' => $userList['total'],
                'list' => $userList['list']
            ];
        }

        $this->_result = $result;
        return true;
    }
}
