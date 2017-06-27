<?php
/**
 * 管理员列表
 * 鲜彤 2016年8月3日15:46:25
 */
namespace Apicp\Controller\AdminManager;

use VcySDK\Adminer;

class ListController extends AbstractController
{

    public function Index()
    {

        // 接收数据
        $pageSize = I('post.limit'); // 每页条数
        $pageNum = I('post.page'); // 页码
        $eaMobile = I('post.eaMobile'); // 手机号
        $eaRealname = I('post.eaRealname'); // 姓名
        $eaUserstatus = I('post.eaUserstatus'); // 管理员状态 1：启用；2：禁用
        $eaEmail = I('post.eaEmail'); // 邮箱
        $earId = I('post.earId'); // 角色ID
        $eaCreatedBegin = I('post.eaCreatedBegin'); // 创建时间开始
        $eaCreatedEnd = I('post.eaCreatedEnd'); // 创建时间截止
        $eaIsactivated = I('post.eaIsactivated');

        // 查询条件数组拼接
        $condition = array(
            'eaMobile' => $eaMobile,
            'eaRealname' => $eaRealname,
            'eaUserstatus' => $eaUserstatus,
            'eaEmail' => $eaEmail,
            'earId' => $earId,
            'filterType' => Adminer::FILTER_TYPE_MOBILE,
            'eaIsactivated' => $eaIsactivated,
        );
        // 时间范围条件
        $startTime = rstrtotime($eaCreatedBegin, 1);
        $endTime = rstrtotime($eaCreatedEnd, 1);
        if (! empty($eaCreatedBegin) && 0 < $startTime) {
            $condition['startCreatedTime'] = $startTime;
        }
        if (! empty($eaCreatedEnd) && 0 < $endTime) {
            $condition['endCreatedTime'] = $endTime;
        }

        // 调用UC接口，查询符合条件的列表
        $result = $this->_sdkAdminer->listAll($condition, $pageNum, $pageSize);

        // 检查列表中是否有超级管理, 有则修改管理员角色名称
        foreach ($result['list'] as &$_adminer) {
            if (Adminer::TYPE_SUPER_ADMIN == $_adminer['eaType']) {
                $_adminer['earName'] = cfg('ADMIN_ROLE_PROTECT_NAME');
            }
        }

        // 输出
        $this->_result = array(
            'total' => $result['total'],
            'limit' => $pageSize,
            'page' => $pageNum,
            'list' => $result['list']
        );

        return true;
    }

}

