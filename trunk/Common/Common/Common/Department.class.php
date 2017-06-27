<?php
/**
 * Department.class.php
 * 部门操作
 * $Author$
 */

namespace Common\Common;

use VcySDK\Member;
use VcySDK\Service;
use VcySDK\Department as DepartmentSDK;

class Department
{

    /**
     * 部门列表
     *
     * @var array
     */
    protected $_departments = array();

    /**
     * 上级部门id => 子部门ID列表
     *
     * @var array
     */
    protected $_p2c = array();

    /**
     * uid => cdid
     *
     * @var array
     */
    protected $_uid2dpIds = array();

    protected $_uid2dpParentids = array();

    /**
     * cdid => parent cdid
     *
     * @var array
     */
    protected $_dpId2dpParentids = array();

    /**
     * 最大嵌套等级
     */
    const MAX_LEVEL = 20;

    /**
     * 单例实例化
     * @param bool $fromSdk 从SDK获取部门数据 true 是 false 否
     * @return Department
     */
    public static function &instance($fromSdk = true)
    {

        static $instance;
        if (empty($instance)) {
            $instance = new self($fromSdk);
        }

        return $instance;
    }

    /**
     * Department constructor.
     * @param bool $fromSdk 从SDK获取部门数据 true 是 false 否
     */
    public function __construct($fromSdk = true)
    {
        $this->_departments = $fromSdk ?
            $this->listDepartmentFromSdk() : Cache::instance()->get('Common.Department');

        return true;
    }

    /**
     * 从SDK获取部门列表
     * @return array
     */
    public function listDepartmentFromSdk()
    {
        // 获取数据
        $department_sdk = new DepartmentSDK(Service::instance());
        $result = $department_sdk->listAll(array(), 1, 15000);
        $departments = array_combine_by_key($result['list'], 'dpId');

        // 处理扩展
        $this->parseExt($departments, $result['extList']);

        return $departments;
    }

    /**
     * 获取全部部门列表
     *
     * @return array|mixed
     */
    public function listAll()
    {

        return $this->_departments;
    }

    /**
     * 根据ID 获取部门数据
     *
     * @param array $ids     部门ID
     * @param array $fields  需要的部门数据键值
     * @param bool  $isThird ID值是否为第三方部门ID
     *
     * @return array
     *         有需要的fields时
     *         [
     *              '0E19B0A87F0000012652058B28BC30B1' => [
     *                  'epId' => 'B646C6F67F0000017D3965FCF2FD3A2F',
     *                  'dpName' => 'UI'
     *              ]
     *         ]
     *         没有fields时
     *         [
     *              'E19B0A87F0000012652058B28BC30B1' => [
     *                  ... (完整的数据)
     *              ]
     *          ]
     */
    public function listById($ids, $fields = array(), $isThird = false)
    {

        if (empty($ids)) {
            return array();
        }
        settype($ids, 'array');

        if ($isThird) {
            $departments = $this->_listByThirdId($ids);
        } else {
            $departments = $this->_listByCurId($ids);
        }

        // 只需要指定字段
        if (!empty($fields)) {
            $fields = array_fill_keys($fields, '');
            foreach ($departments as $_id => $_department) {
                $departments[$_id] = array_intersect_key($_department, $fields);
            }
        }

        return $departments;
    }

    /**
     * 通过第三方部门ID获取部门信息
     *
     * @param array $ids 第三方部门ID
     *
     * @return array
     */
    protected function _listByThirdId($ids)
    {

        $departments = array();
        $ids = array_fill_keys($ids, '');
        foreach ($this->_departments as $_department) {
            if (isset($ids[$_department['dpThirdid']])) {
                $departments[$_department['dpId']] = $_department;
                unset($ids[$_department['dpThirdid']]);
            }

            // 如果已经读取完了
            if (empty($ids)) {
                break;
            }
        }

        return $departments;
    }

    /**
     * 通过当前部门ID获取部门信息
     *
     * @param array $ids 部门ID
     *
     * @return array
     */
    protected function _listByCurId($ids)
    {

        $departments = array();
        foreach ($ids as $v) {
            if (isset($this->_departments[$v])) {
                $departments[$v] = $this->_departments[$v];
            }
        }

        return $departments;
    }

    /**
     * 根据ID 获取部门数据
     *
     * @param string $id 部门ID
     *
     * @return bool|array
     */
    public function getById($id)
    {

        if (empty($id) || !is_string($id)) {
            return [];
        }

        $deptSdk = new DepartmentSDK(Service::instance());
        return $deptSdk->detail(['dpId' => $id]);
    }

    /**
     * 获取部门路径
     *
     * @param int $dpId 部门ID
     *
     * @return string
     */
    public function getCdNames($dpId)
    {

        $names = array();
        for ($i = 0; $i < self::MAX_LEVEL; $i++) {
            $department = $this->_departments[$dpId];
            array_unshift($names, $department['dpName']);
            $dpId = $department['dpParentid'];

            // 到顶级部门结束
            if ('' == $department['dpParentid'] || empty($department['dpParentid'])) {
                break;
            }
        }

        return implode('/', $names);
    }

    /**
     * 根据用户id获取当前部门或所有上级部门
     *
     * @param int        $uid    用户UID
     * @param bool|false $parent 是否取上级部门
     * @param bool|false $force  是否强制重新读取
     *
     * @return array
     */
    public function list_dpId_by_uid($uid, $parent = false, $force = false)
    {

        // 只取一级部门id，没调用过或者调用过需要强制重新读取
        if (empty($this->_uid2dpIds[$uid]) || true == $force) {
            $conds = array(
                'memUid' => $uid
            );
            $member_serv = new Member(Service::instance());
            $myDpIds = array_column((array)$member_serv->listDepartment($conds), 'dpId');

            // 将数据存到静态变量里
            $this->_uid2dpIds[$uid] = $myDpIds;
        } else { // 只取一级部门id，并且已有数据，不重新读取
            // 从静态变量里读数据
            $myDpIds = $this->_uid2dpIds[$uid];
        }

        // 如果不查上级部门
        if (!$parent) {
            return $myDpIds;
        }

        $dpParentids = array();
        // 上级部门为空或者取上级部门id并且强制重新读取
        if (empty($this->_uid2dpParentids[$uid]) || (!empty($this->_uid2dpParentids[$uid]) && true == $force)) {
            // 遍历每个当前部门
            foreach ($this->_uid2dpIds[$uid] as $_dpId) {
                // 取多个部门的所有上级部门
                $this->list_parent_cdids($_dpId, $dpParentids);
            }

            // 将查出来的结果放到静态变量里
            $this->_uid2dpParentids[$uid] = $dpParentids;
        } else { // 从静态变量里读数据
            $dpParentids = $this->_uid2dpParentids[$uid];
        }

        return array(
            $myDpIds,
            $dpParentids
        );
    }

    /**
     * 递归查上级部门方法—公共方法
     *
     * @param int   $dpId         部门
     * @param array &$dpParentIds 上级部门ID
     * @param int   $lv           等级
     *
     * @return array|bool
     */
    public function list_parent_cdids($dpId, &$dpParentIds, $lv = 0)
    {

        static $origin_dpId = 0;
        // 如果是第一次获取, 则记录起始部门id
        if (0 == $lv) {
            $origin_dpId = $dpId;
        }

        // 如果记录存在, 则直接使用
        if (isset($this->_dpId2dpParentids[$dpId])) {
            $dpParentIds = array_merge($dpParentIds, $this->_dpId2dpParentids[$dpId]);
            return true;
        }

        // 如果当前部门不存在
        if (!isset($this->_departments[$dpId])) {
            $this->_dpId2dpParentids[$dpId] = !is_array($dpParentIds) ? array() : $dpParentIds;
            return true;
        }

        $upid = $this->_departments[$dpId]['dpParentid'];
        // 不是顶级部门
        if (!empty($upid)) {
            // 存储上级部门id
            $dpParentIds[$upid] = $upid;
            // 没到顶级部门，继续递归
            $this->list_parent_cdids($upid, $dpParentIds, ++$lv);
        }

        return true;
    }

    /**
     * 获取指定部门下的所有子部门
     *
     * @param string|array $dpIds        部门ID
     * @param bool         $include_self 是否包含本身
     *
     * @return array 所有子部门ID
     */
    public function list_childrens_by_cdid($dpIds, $include_self = false)
    {

        // 如果部门信息为空
        if (empty($dpIds)) {
            return array();
        }

        // 非数组则按 ',' 切分
        if (!is_array($dpIds)) {
            $dpIds = explode(',', $dpIds);
        }

        $return = array();
        // 如果返回值包含自己
        if ($include_self) {
            $return = array_combine($dpIds, $dpIds);
        }

        // 排序
        sort($dpIds);
        $dp_key = implode(',', $dpIds);
        // 如果之前已经获取过
        if (!empty($this->_p2c[$dp_key])) {
            return array_merge($this->_p2c[$dp_key], $return);
        }

        // 获取子部门信息
        foreach ($dpIds as $_dpId) {
            if (!isset($this->_p2c[$_dpId])) {
                $this->_p2c[$_dpId] = $this->_list_childrens($_dpId);
            }

            $return = array_merge($this->_p2c[$_dpId], $return);
        }

        $return = array_unique($return);
        $this->_p2c[$dp_key] = $return;

        return $return;
    }

    /**
     * 找出下级部门
     *
     * @param int $dpId 部门id
     *
     * @return array
     */
    protected function _list_childrens($dpId)
    {

        $dp_ids = array();
        foreach ($this->_departments as $_dep) {
            if ($_dep['dpParentid'] != (string)$dpId) {
                continue;
            }

            $dp_ids[$_dep['dpId']] = $_dep['dpId'];
            // 获取当前部门的下级部门
            $list_childrens = $this->_list_childrens($_dep['dpId']);
            if (!empty($list_childrens)) {
                // 遍历当前部门的下级部门，追加到输出$dp_ids内
                foreach ($list_childrens as $_k => $_i) {
                    if (!isset($dp_ids[$_k])) {
                        $dp_ids[$_k] = $_i;
                    }
                }
            }
            unset($list_childrens);
        }

        return $dp_ids;
    }

    /**
     * 获取最顶级部门id
     *
     * @param int $dp_id 部门id
     *
     * @return boolean
     */
    public function get_top_dpId(&$dp_id)
    {

        static $top_dpId = '';
        if ($top_dpId != '') {
            $dp_id = $top_dpId;
            return true;
        }

        // 获取顶级部门id
        foreach ($this->_departments as $_dep) {
            // 上级部门不存在
            if (!isset($this->_departments[$_dep['dpParentid']])) {
                $top_dpId = $_dep['dpId'];
                break;
            }
        }

        $dp_id = $top_dpId;
        return ($dp_id != '') ? true : false;
    }

    /**
     * 清理部门缓存
     *
     * @return bool
     */
    public function clearDepCache()
    {

        $cache = &Cache::instance();
        $cache->set('Common.Department', null);

        return true;
    }

    /**
     * 整理组织的扩展数据, 并入组织信息
     * @param $departments
     * @param $extList
     * @return bool
     */
    public static function parseExt(&$departments, $extList)
    {

        $configs = Cache::instance()->get('Common.DepartmentFieldConfig');
        foreach ($extList as $_ext) {
            if (!isset($departments[$_ext['dpId']])) {
                continue;
            }

            // 如果是区域数据, 则还需要转换数据
            if ('areaselect' == $configs[$_ext['dptId']][$_ext['dfcId']]['dfcType']) {
                $departments[$_ext['dpId']][$_ext['dfcId']] = json_decode($_ext['dpeValue']);
            } else {
                $departments[$_ext['dpId']][$_ext['dfcId']] = $_ext['dpeValue'];
            }
        }

        return true;
    }

}
