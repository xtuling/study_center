<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/25
 * Time: 23:47
 */

namespace Common\Service;

use VcySDK\Service;
use VcySDK\Department;
use Common\Common\User;
use Common\Common\Department as Dept;
use Common\Model\DeptRightModel;

class DeptRightService extends AbstractService
{

    /**
     * 是否为父级部门:是
     */
    const PARENT_DP_YES = 1;

    /**
     * 是否为父级部门:否
     */
    const PARENT_DP_NO = 0;

    /**
     * 通讯录可查看范围是否为全公司:是
     */
    const COMPANY_IS_ALL = 1;

    /**
     * 通讯录可查看范围是否为全公司:否
     */
    const COMPANY_IS_NOT_ALL = 0;

    /**
     * 通讯录可查看范围是否为本部门:是
     */
    const DEPARTMENT_YES = 1;

    /**
     * 通讯录可查看范围是否为本部门:否
     */
    const DEPARTMENT_NO = 0;

    // 构造方法
    public function __construct()
    {

        parent::__construct();
        $this->_d = new DeptRightModel();
    }

    /**
     * 创建部门
     * @author liyifei
     * @param array $data
     *      + string dpName 部门名称
     *      + string dpParentid 上级部门id，顶级部门为1
     *      + string dpLeaderUids 部门负责人(以逗号分隔的uid字符串)
     *      + int isAll 是否全公司可见（0=不是；1=是）
     *      + int isDept 是否本部门可见（0=不是；1=是）
     *      + array dpIds 可见范围内的部门
     * @return mixed
     */
    public function createDp($data)
    {

        // 请求架构接口,创建部门
        $dpServ = new Department(Service::instance());
        $addData = [
            'dpName' => $data['dpName'],
            'dpParentid' => $data['dpParentid'],
            'dptId' => $data['dptId'],
            'dpSerialNum' => $data['dpSerialNum'],
            'departmentExtJson' => $data['departmentExtJson'],
            'dpLead' => $data['dpLead']
        ];
        /**if (isset($data['dpLeaderUids'])) {
            $addData['dpLead'] = $data['dpLeaderUids'];
        }*/
        // TODO liyifei 2016-11-28 19:32:41 部门Common类未提供创建部门方法,暂直接请求架构
        $result = $dpServ->create($addData);

        // 清空部门缓存
        $deptServ = new Dept();
        $deptServ->clearDepCache();

        // 根据架构返回部门id,本地存储部门可查看通讯录范围
        $insertData = [
            'dp_id' => $result['dpId'],
            'is_all' => $data['isAll'],
            'is_dept' => $data['isDept'],
            'dept_ids' => $data['dpIds'] ? serialize($data['dpIds']) : '',
        ];
        $this->insert($insertData);
    }

    /**
     * 修改部门
     * @author liyifei
     * @param string $dpId        部门ID
     * @param array  $data
     *                            + int order 部门排序
     *                            + string dpName 部门名称
     *                            + string dpParentid 上级部门id，顶级部门为1
     *                            + string dpLeaderUids 部门负责人(以逗号分隔的uid字符串)
     *                            + int isAll 是否全公司可见（0=不是；1=是）
     *                            + int isDept 是否本部门可见（0=不是；1=是）
     *                            + array dpIds 可见范围内的部门
     * @param bool   $updateRight 是否更新查看权限
     * @return mixed
     */
    public function updateDp($dpId, $data, $updateRight = true)
    {

        // 更新架构部门信息
        $upConds = [
            'dpId' => $dpId,
            'dpDisplayorder' => $data['order'],
            'dptId' => $data['dptId'],
            'dpSerialNum' => $data['dpSerialNum'],
            'departmentExtJson' => $data['departmentExtJson'],
            'dpLead' => $data['dpLead']
        ];
        if (isset($data['dpName'])) {
            $upConds['dpName'] = $data['dpName'];
        }
        if (isset($data['dpParentid'])) {
            $upConds['dpParentid'] = $data['dpParentid'];
        }
        /**if (isset($data['dpLeaderUids'])) {
            $upConds['dpLead'] = $data['dpLeaderUids'];
        }*/
        // TODO liyifei 2016-11-28 18:58:04 部门Common类未提供修改部门方法,暂直接请求架构
        $dpServ = new Department(Service::instance());
        $dpServ->modify($upConds);

        // 清空部门缓存
        $deptServ = new Dept();
        $deptServ->clearDepCache();

        if (!$updateRight) {
            return true;
        }

        // 更新本地部门信息
        $datas = [
            'is_all' => $data['isAll'],
            'is_dept' => $data['isDept'],
            'dept_ids' => $data['dpIds'] ? serialize($data['dpIds']) : '',
        ];
        $result = $this->get_by_conds(['dp_id' => $dpId]);
        if (!$result) {
            $datas['dp_id'] = $dpId;
            $this->insert($datas);
        } else {
            $this->update($result['right_id'], $datas);
        }
    }

    /**
     * 编辑查询部门权限信息
     * @author liyifei
     * @param string $dpId 部门ID
     * @return mixed
     */
    public function DeptEditQuery($dpId)
    {

        $deptServ = new Dept();
        $dpInfo = $deptServ->getById($dpId);

        // 获取上级部门信息
        $parentDpInfo = $deptServ->getById($dpInfo['dpParentid']);

        // 返回值
        $result = [
            'is_all' => self::COMPANY_IS_ALL,
            'is_dept' => self::DEPARTMENT_NO,
            'dp_ids' => [],
            'name' => $dpInfo['dpName'],
            'order' => $dpInfo['dpDisplayorder'],
            'parent_id' => $dpInfo['dpParentid'],
            'parent_name' => isset($parentDpInfo['dpName']) ? $parentDpInfo['dpName'] : '',
            'dp_leader' => $this->formatLeader($dpInfo['dpLead']),
        ];

        // 查询本地部门权限信息
        $rightData = $this->get_by_conds(['dp_id' => $dpId]);
        if (!empty($rightData)) {
            $result['is_all'] = intval($rightData['is_all']);
            $result['is_dept'] = intval($rightData['is_dept']);
            if ($rightData['dept_ids']) {
                // 从部门缓存类读取部门列表
                $dpIds = unserialize($rightData['dept_ids']);
                $list = $deptServ->listById($dpIds);
                // 格式化为前端需要的格式
                foreach ($list as $v) {
                    $result['dp_ids'][] = [
                        'flag' => 1,
                        'id' => $v['dpId'],
                        'name' => $v['dpName'],
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * 我的部门列表
     * @author liyifei
     * @param string $uid 人员UID
     * @return mixed
     */
    public function getMyDeptList($uid = '')
    {

        // 验证参数
        if (empty($uid)) {
            E('_ERR_UID_IS_NULL');
            return false;
        }

        // 实例化
        $userServ = new User();
        $deptServ = new Department(Service::instance());

        // 该人员所在部门列表
        $dpIds = $userServ->listDepartment(['memUid' => $uid]);
        if (empty($dpIds)) {
            E('_ERR_NOT_JOIN_DEPT');
            return false;
        }

        // 循环读取部门详情
        $dpInfo = [];
        foreach ($dpIds as $dpId) {
            $detail = $deptServ->detail(['dpId' => $dpId]);
            $dpInfo[] = [
                'order' => $detail['dpDisplayorder'],
                'dp_id' => $detail['dpId'],
                'name' => $detail['dpName'],
                'is_child' => $detail['isChildDepartment'],
            ];
        }

        // 排序
        $column = array_column($dpInfo, 'order');
        array_multisort($column, $dpInfo);

        return $dpInfo;
    }

    /**
     * 获取顶级部门下子部门列表
     * @author liyifei
     * @return mixed
     */
    public function getTopDpList()
    {

        $deptServ = new Department(Service::instance());
        $allDept = $deptServ->listAll([], 1, 99999);
        if (empty($allDept['list'])) {
            E('_ERR_DEPT_UNDEFINED');
            return false;
        }

        $dpId = '';
        foreach ($allDept['list'] as $v) {
            if (empty($v['dpParentid'])) {
                $dpId = $v['dpId'];
            }
        }
        $listAll = $deptServ->listAll(['dpId' => $dpId], 1, 99999);
        if (empty($listAll['list'])) {
            return $allDept['list'];
        }

        return $listAll['list'];
    }

    /**
     * 人员通讯录可查看范围内,所有顶级部门的列表
     * @author liyifei
     * @param string $uid 人员UID
     * @return mixed
     */
    public function getDpListByUid($uid)
    {

        // 验证参数
        if (empty($uid)) {
            E('_ERR_UID_IS_NULL');
            return false;
        }

        // 实例化
        $userServ = new User();
        $deptServ = new Department(Service::instance());

        // 该人员所在部门列表
        $dpIds = $userServ->listDepartment(['memUid' => $uid]);
        if (empty($dpIds)) {
            E('_ERR_NOT_JOIN_DEPT');
            return false;
        }

        // 初始化数组,存储可见范围的部门详情
        $list = [];

        // 循环查询该人员所在部门的通讯录可查看范围,若任一所在部门未设置可见范围,则可见全公司通讯录
        foreach ($dpIds as $dpId) {
            // 人员所在部门,是否设置可查看范围信息
            $right = $this->get_by_conds(['dp_id' => $dpId]);
            if (empty($right)) {
                // 可见全公司
                $list = $this->getTopDpList();
                break;
            }

            // 可见全公司
            if (!empty($right['is_all'])) {
                $list = $this->getTopDpList();
                break;

                // 可见本部门
            } elseif (!empty($right['is_dept'])) {
                $data = $deptServ->detail(['dpId' => $dpId]);
                $list[] = $data;

                // 可见指定部门
            } elseif (!empty($right['dept_ids'])) {
                $deptIds = unserialize($right['dept_ids']);
                foreach ($deptIds as $deptId) {
                    try {
                        $data = $deptServ->detail(['dpId' => $deptId]);
                        $list[] = $data;

                    } catch (\VcySDK\Exception $e) {

                    }
                }
            }
        }

        // 排序
        $column = array_column($list, 'dpDisplayorder');
        array_multisort($column, $list);

        // 去除重复部门,并格式化
        $range = [];
        foreach ($list as $v) {
            $range[$v['dpId']] = [
                'dp_id' => $v['dpId'],
                'name' => $v['dpName'],
                'is_child' => $v['isChildDepartment'],
            ];
        }

        return $range;
    }

    /**
     * 人员通讯录可查看范围内,所有部门的列表(包括子部门)
     * @author liyifei
     * @param string $uid 人员UID
     * @return mixed
     */
    public function getRangByUid($uid)
    {

        // 验证参数
        if (empty($uid)) {
            E('_ERR_UID_IS_NULL');
            return false;
        }

        // 实例化
        $userServ = new User();
        $deptServ = new Department(Service::instance());

        // 该人员所在部门列表
        $dpIds = $userServ->listDepartment(['memUid' => $uid]);
        if (empty($dpIds)) {
            E('_ERR_NOT_JOIN_DEPT');
            return false;
        }

        // 初始化数组,存储可见范围的部门详情
        $range = [];

        // 循环查询该人员所在部门的通讯录可查看范围,若任一所在部门未设置可见范围,则可见全公司通讯录
        foreach ($dpIds as $dpId) {
            // 人员所在部门,是否设置可查看范围信息
            $right = $this->get_by_conds(['dp_id' => $dpId]);
            if (empty($right)) {
                // 全公司通讯录
                $allDept = $deptServ->listAll([], 1, 99999);
                $range = isset($allDept['list']) ? $allDept['list'] : [];
                break;
            }

            if (!empty($right['is_all'])) {
                // 全公司通讯录
                $allDept = $deptServ->listAll([], 1, 99999);
                $range = isset($allDept['list']) ? $allDept['list'] : [];
                break;

            } elseif (!empty($right['is_dept'])) {
                // 可见本部门
                $data = $deptServ->detail(['dpId' => $right['dp_id']]);
                $range[] = $data;

                // 可见部门的子部门
                if ($data['childrensDepartmentCount']) {
                    $listAll = $deptServ->listAll(['dpId' => $right['dp_id']], 1, 99999);
                    foreach ($listAll['list'] as $item) {
                        $range[] = $item;
                    }
                }

            } elseif (!empty($right['dept_ids'])) {
                // 可见指定部门
                $deptIds = unserialize($right['dept_ids']);
                foreach ($deptIds as $deptId) {
                    try {
                        $data = $deptServ->detail(['dpId' => $deptId]);
                        $range[] = $data;

                        // 可见指定部门的子部门
                        if ($data['childrensDepartmentCount']) {
                            $listAll = $deptServ->listAll(['dpId' => $deptId], 1, 99999);
                            foreach ($listAll['list'] as $item) {
                                $range[] = $item;
                            }
                        }

                    } catch (\VcySDK\Exception $e) {

                    }
                }
            }
        }

        // 排序
        $column = array_column($range, 'dpDisplayorder');
        array_multisort($column, $range);

        return $range;
    }

    /**
     * 返回格式化后的树状结构部门列表
     * @author liyifei
     * @param array $data 部门列表
     *                    + string dpId 部门ID
     *                    + string dpName 部门名称
     *                    + string dpParentid 上级部门id
     *                    + int isChildDepartment 是否有子部门 0:没有子部门 1：有子部门
     *                    + int dpDisplayorder 排序
     * @return mixed
     */
    public function formatDept($data = [])
    {

        $list = [];
        foreach ($data as $info) {
            $list[] = [
                'dp_id' => $info['dpId'],
                'name' => $info['dpName'],
                'pid' => $info['dpParentid'],
            ];
        }

        // 桶排序算法,将部门及子部门按pid进行分组
        $tree = [];
        foreach ($list as $v) {
            $tree[$v['dp_id']] = $v;
            $tree[$v['dp_id']]['child_list'] = [];
        }
        // 筛选出顶级部门(上级部门信息不存在时,即认为是顶级部门)
        $topDpIds = [];
        foreach ($tree as $v) {
            if (empty($tree[$v['pid']])) {
                $topDpIds[] = $v['dp_id'];
            }
        }
        // 树排序算法,将可见范围内的部门格式化为树状结构
        foreach ($tree as $dpId => $v) {
            $tree[$v['pid']]['child_list'][] = &$tree[$dpId];
        }
        // 以顶级部门分组,过滤重复数据
        $result = [];
        foreach ($topDpIds as $pid) {
            $result[] = $tree[$pid];
        }

        return $result;
    }
}
