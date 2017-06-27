<?php
/**
 * 获取子集部门接口
 * Created by PhpStorm.
 * User: 何岳龙
 * Date: 2016年9月1日15:19:21
 */
namespace Apicp\Controller\ChooseMem;

use Common\Common\User;
use VcySDK\Department;
use VcySDK\Member;
use VcySDK\Service;


class DepartmentController extends AbstractController
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

        if (! parent::before_action($action)) {
            return false;
        }

        $serv = &Service::instance();
        $this->_department = new Department($serv);
        $this->_mem = new Member($serv);

        return true;
    }

    public function Index()
    {

        $search = I("post.search");
        $dpParentId = I("post.dpId");
        $limit = I("post.limit", 1000);
        $page = I("post.page", 1);

        // 如果特殊处理存在
        if (! empty($search)) {
            $data = $this->select($search, $page, $limit);
            // 返回部门列表
            $this->_result = $data;
            return false;
        }


        // 如果上级部门ID为空
        if (empty($dpParentId)) {
            // 获取顶级部门
            $dpParentIds = $this->maxDpId();
            // 获取部门列表
            $data = $this->maxList($dpParentIds, $page, $limit);
            // 返回部门列表
            $this->_result = $data;

            return false;
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
        $list = $this->_department->listAll(array('dpId' => $dpParentId), $page, $limit, ['dpDisplayorder' => 'ASC']);
        foreach ($list['list'] as $v) {
            $data[] = array(
                'dpId' => $v['dpId'],
                'dpName' => $v['dpName'],
                'isChildDepartment' => $v['isChildDepartment'],
                'dpLevel' => $v['dpLevel']
            );
        }

        return array($data, $list);
    }

    /**
     * 获取顶级部门ID
     *
     * @return array
     */
    private function maxDpId()
    {

        // 获取应用权限列表
        $AllowList = $this->_mem->appAllow();
        // 获取最顶级部门列表
        $dpMaxList = array();
        
        if ($AllowList['appAllow']) {
            // 获取可见返回部门
            $MaxList = $AllowList['departmentList'];
            // 获取可见返回部门IDS
            $list = array_column($MaxList, 'dpId');

            // 循环获取顶级部门列表
            foreach ($MaxList as $key => $v) {
                $dpMaxList[] = $this->listParentIds($list, $v['dpId']);
            }

            // 返回最顶级部门列表
            return array_filter($dpMaxList);
        } else {
            $dpServ = &\Common\Common\Department::instance();
            $list = $dpServ->listAll();

            // 获取部门层级为1的所有部门
            foreach ($list as $dp) {
                if ($dp['dpLevel'] == 1) {
                    $dpMaxList[] = $dp['dpId'];
                }
            }

            return $dpMaxList;
        }
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

    /**
     * 获取顶级部门列表
     *
     * @param array $list  部门IDS
     * @param int   $page  页码
     * @param int   $limit 条数
     *
     * @return array
     */
    private function maxList($list, $page, $limit)
    {

        // 实例化数据
        $data = array();
        // 实例化部门类
        $dps = new \Common\Common\Department();
        // 获取所有部门列表
        $dpList = $dps->listAll();
        // 获取部门信息
        foreach ($list as $key => $dpId) {
            $data[] = array(
                'dpId' => $dpList[$dpId]['dpId'],
                'dpName' => $dpList[$dpId]['dpName'],
                'isChildDepartment' => $dpList[$dpId]['isChildDepartment']
            );
        }

        // 返回参数
        $result = array(
            'list' => $data,
            'total' => (int)count($data),
            'limit' => $limit,
            'page' => $page,

        );

        return $result;
    }

    /**
     * 特殊处理方式
     *
     * @param array $select 参数
     *                      +array dpIds  部门IDS
     *                      +array uids  人员UIDS
     *                      +array tagIds  标签IDS
     * @param int   $page   页码
     * @param int   $limit  条数
     *
     * @return array
     */
    private function select($select = array(), $page, $limit)
    {

        // 去除数组中的空值
        array_filter($select['uids']);
        array_filter($select['dpIds']);

        // 初始化
        $data = array();

        // 实例化User类
        $User = User::instance();
        //$User->sync();
        // 遍历用户UIDS
        foreach ($select['uids'] as $key => $v) {

            // 获取人员对应部门
            $info = $User->listDepartment(array('memUid' => $v));

            foreach ($info as $item) {
                $data[] = $item;
            }
        }

        // 遍历部门IDS写入返回值中
        foreach ($select['dpIds'] as $dpId) {

            $data[] = $dpId;
        }

        $data = array_unique($data);

        $Department = \Common\Common\Department::instance();

        // 获取部门IDS
        $dps = $data;

        // 遍历部门
        foreach ($data as $key => $v) {

            // 获取所有子部门
            $dpIds = $Department->list_childrens_by_cdid($v);

            // 遍历获取到的所有部门
            foreach ($dps as $k => $item) {

                if (in_array($item, $dpIds)) {

                    // 去除子部门
                    unset($dps[$k]);
                }
            }

        }

        // 重新排序
        sort($dps);

        $list = $this->maxList($dps, $page, $limit);

        return $list;
    }

}
