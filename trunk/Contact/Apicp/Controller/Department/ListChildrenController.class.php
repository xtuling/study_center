<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/11/14
 * Time: 17:14
 */

namespace Apicp\Controller\Department;

use Common\Common\User;
use VcySDK\Department;
use VcySDK\Member;
use VcySDK\Service;

class ListChildrenController extends AbstractController
{

    /**
     * VcySDK 附件操作类
     *
     * @type Department
     */
    protected $_department;

    /**
     * VcySDK 用户操作类
     *
     * @type Member
     */
    protected $_mem;


    public function before_action($action = '')
    {

        if (!parent::before_action($action)) {
            return false;
        }

        $serv = &Service::instance();
        $this->_department = new Department($serv);
        $this->_mem = new Member($serv);

        return true;
    }

    /**
     * 【通讯录】部门列表
     * @author liyifei
     */
    public function Index_post()
    {

        $dpParentId = I("post.dpParentId");
        $limit = I("post.limit", 30);
        $page = I("post.page", 1);

        // 如果上级部门ID为空
        if (empty($dpParentId)) {
            // 获取顶级部门
            $dpParentId = $this->maxDpId();
        }

        // 查询部分指定部门
        list($departments, $data) = $this->partdp($dpParentId, $page, $limit);
        $this->_result = array(
            'list' => $departments,
            'total' => (int)$data['total'],
            'limit' => (int)$data['pageSize'],
            'page' => (int)$data['pageNum'],
        );

        return true;
    }

    /**
     * 获取部门列表
     *
     * @param string $dpParentId 上级部门ID
     * @param int    $page       页码
     * @param int    $limit      每页条数
     *
     * @return array
     */
    private function partdp($dpParentId, $page, $limit)
    {

        // 初始化
        $data = array();
        // 获取部门详情
        $dpIds = array_values(\Common\Common\Department::instance()->list_childrens_by_cdid($dpParentId, true));
        $departments = \Common\Common\Department::instance()->listAll();
        $start = ($page - 1) * $limit;
        $end = $start + $limit;
        $dpId2uids = array();
        for (; $start < $end; $start++) {
            if (empty($dpIds[$start])) {
                break;
            }

            $currentDp = $departments[$dpIds[$start]];
            if (!empty($currentDp['dpLead'])) {
                $dpId2uids[$currentDp['dpId']] = explode(',', $currentDp['dpLead']);
            }

            $data[$currentDp['dpId']] = array(
                'dpId' => $currentDp['dpId'],
                'dpName' => $currentDp['dpName'],
                'isChildDepartment' => $currentDp['isChildDepartment'],
                'order' => $currentDp['dpDisplayorder'],
                'user_total' => $currentDp['departmentMemberCount'],
                'dept_level' => $currentDp['dpLevel'],
                'dptName' => $currentDp['dptName'],
                'dpSerialNum' => $currentDp['dpSerialNum'],
                'dpLead' => $currentDp['dpLead'],
                'dpLeadList' => array()
            );
        }

        $uids = array();
        foreach ($dpId2uids as $_uids) {
            foreach ($_uids as $_uid) {
                $uids[$_uid] = $_uid;
            }
        }
        $users = User::instance()->listByUid($uids);
        foreach ($dpId2uids as $_dpId => $_uids) {
            foreach ($_uids as $_uid) {
                if (!empty($users[$_uid])) {
                    $data[$_dpId]['dpLeadList'][] = $users[$_uid];
                }
            }
        }

        return array(array_values($data), array('total' => count($dpIds), 'pageNum' => $page, 'pageSize' => $limit));
    }

    /**
     * 获取顶级部门ID
     *
     * @return array
     */
    private function maxDpId()
    {

        $topId = '';
        \Common\Common\Department::instance()->get_top_dpId($topId);
        return array($topId);
        // 获取应用权限列表
//        $AllowList = $this->_mem->appAllow();
//        // 获取最顶级部门列表
//        $dpMaxList = array();
//        // 获取可见返回部门
//        $MaxList = $AllowList['departmentList'];
//        // 获取可见返回部门IDS
//        $list = array_column($MaxList, 'dpId');
//
//        // 循环获取顶级部门列表
//        foreach ($MaxList as $key => $v) {
//            $dpMaxList[] = $this->listParentIds($list, $v['dpId']);
//        }
//
//        // 返回最顶级部门列表
//        return array_filter($dpMaxList);
    }

    /**
     * 获取上级部门ID
     *
     * @param  array $list 部门关系
     * @param string $dpId 部门ID
     *
     * @return string
     */
    private function listParentIds($list, $dpId = "")
    {

        // 实例化上级菜单
        $dpIds = array();
        // 实例化部门类
        $dps = new \Common\Common\Department();
        // 获取上级菜单
        $dps->list_parent_cdids($dpId, $dpIds);
        // 遍历数组
        foreach ($dpIds as $Id) {
            // 判断是否存在数组中
            if (in_array($Id, $list)) {
                $dpId = '';
            }
        }

        // 返回值
        return $dpId;
    }

}
