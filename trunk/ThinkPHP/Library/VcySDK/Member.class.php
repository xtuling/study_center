<?php
/**
 * Member.class.php
 * 用户接口操作类
 *
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt LGPL
 * @copyright  Copyright (c) 2014 - ? VcySDK (http://www.vchangyi.com/)
 * @author     zhuxun37
 * @version    1.0.0
 */
namespace VcySDK;

use VcySDK\Logger;
use VcySDK\Config;
use VcySDK\Error;
use VcySDK\Exception;

class Member
{

    /**
     * 接口调用类
     *
     * @var object
     */
    private $serv = null;

    /**
     * SERVICE 类
     *
     * @var null
     */
    private $service = null;

    /**
     * 获取用户列表的接口地址
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const LIST_URL = '%s/member/list';

    /**
     * 同步用户信息
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const SYNC_URL = '%s/member/sync';

    /**
     * 获取指定用户信息
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const GET_URL = '%s/member/get';

    /**
     * 更新指定用户信息
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const UPDATE_URL = '%s/member/update';

    /**
     * 更新所有用户的扩展字段
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const UPDATE_EXT_URL = '%s/member/update-ext';

    /**
     * 获取用户和部门对照信息列表
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const MEMBER_DEPARTMENT_LIST_URL = '%s/member/get-member-conn-depart';

    /**
     * 获取用户和职位对照信息列表
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const MEMBER_JOB_LIST_URL = '%s/member/get-member-conn-job';

    /**
     * 新增用户信息
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const ADD_URL = '%s/member/add';

    /**
     * 删除用户
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const DELETE_URL = '%s/member/delete';

    /**
     * 获取用户列表
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const SEARCH_URL = '%s/search/list';

    /**
     * 获取用户列表
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const APP_ALLOW_URL = '%s/get-app-allow';

    /**
     * 批量删除
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const BAT_DEL_URL = '%s/member/bat-delete';

    /**
     * 批量启用、禁用
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const MODIFY_STATUS_URL = '%s/member/modify-member-status';

    /**
     * 批量用户移动部门
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const MOVE_DEPT_URL = '%s/member/move-department';

    /**
     * 添加扩展属性
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const MEMBER_EXT_ADD_URL = '%s/member-ext/add';

    /**
     * 批量添加扩展属性
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const MEMBER_EXT_BATADD_URL = '%s/member-ext/bat-add';

    /**
     * 查询用户扩展属性
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const MEMBER_EXT_GET_URL = '%s/member-ext/get';

    /**
     * 查询用户所有扩展属性
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const MEMBER_EXT_LIST_URL = '%s/member-ext/list';

    /**
     * 修改用户扩展属性
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const MEMBER_EXT_MODIFY_URL = '%s/member-ext/modify';

    /**
     * 删除用户扩展属性
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const MEMBER_EXT_DEL_URL = '%s/member-ext/del';

    /**
     * 批量修改扩展属性
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const MEMBER_EXT_BATMODIFY_URL = '%s/member-ext/bat-modify';

    /**
     * 删除所有用户指定扩展属性
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const MEMBER_EXT_ALLDEL_URL = '%s/member-ext/all-del';

    /**
     * 人员、已关注、未关注、禁用人员总数
     */
    const MEMBER_RELEVANT_TOTAL = '%s/statistics/user';

    /**
     * 在职情况统计
     */
    const MEMBER_ACTIVE_TOTAL = '%s/statistics/job';

    /**
     * 判断指定用户信息是否已存在
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const CHECK_INFO_EXIST_URL = '%s/member/check-info-exist';

    /**
     * 初始化
     *
     * @param Service $serv 接口调用类
     */
    public function __construct($serv)
    {

        $this->serv = $serv;
        $this->service = new Service();
    }

    /**
     * 获取用户列表
     *
     * @param array $condition 查询条件数据
     * @param mixed $orders    排序字段
     * @param int   $page      当前页码
     * @param int   $perpage   每页记录数
     *
     * @return boolean|multitype:
     */
    public function listAll($condition = array(), $page = 1, $perpage = 30, $orders = array())
    {

        // 查询参数
        $keyList = [];
        foreach (['userids', 'memUids'] as $_key) {
            $this->service->setAndIsArr($condition, $_key, $keyList);
        }
        $this->service->getValue($condition, $keyList);
        $condition = $this->serv->mergeListApiParams($condition, $orders, $page, $perpage);
        $result = $this->serv->postSDK(self::LIST_URL, $condition, 'generateApiUrlA');

        if ($result['list'] && $result['memberExtList']) {
            $list = array_combine_by_key($result['list'], 'memUid');

            // 把扩展属性合并到list
            foreach ($result['memberExtList'] as $v) {
                $memUid = $v['memUid'];
                $extKey = $v['extKey'];
                $extValue = $v['extValue'];
                $list[$memUid][$extKey] = $extValue;
            }

            unset($result['memberExtList']);
            $result['list'] = array_values($list);
        }

        return $result;
    }

    /**
     * 同步用户信息
     *
     * @return boolean|multitype:
     */
    public function sync()
    {

        return $this->serv->postSDK(self::SYNC_URL, [], 'generateApiUrlB');
    }

    /**
     * 获取指定用户信息
     *
     * @param array $condition 请求参数
     *                         + memUid string 用户UID
     *
     * @return boolean|multitype:
     */
    public function fetch($condition)
    {
        $result = $this->serv->postSDK(self::GET_URL, $condition, 'generateApiUrlA');

        if ($result['memberExtList']) {
            // 把扩展属性合并到用户数据
            foreach ($result['memberExtList'] as $v) {
                $result[$v['extKey']] = $v['extValue'];
            }

            unset($result['memberExtList']);
        }

        $attr_keys = array_keys(cfg('USER_ATTRS'));
        $user_keys = array_keys($result);
        $diffs = array_diff($attr_keys, $user_keys);

        // 补全属性
        foreach ($diffs as $k) {
            $result[$k] = '';
        }

        return $result;
    }

    /**
     * 更新用户信息
     *
     * @param array $data 人员数据
     *                      + string(32) memUid 人员ID
     *                      + string(64) memWeixin 微信号 手机/微信号/邮箱,不可同时为空
     *                      + string(11) memMobile 手机号码 手机/微信号/邮箱,不可同时为空
     *                      + string(80) memEmail 邮箱地址 手机/微信号/邮箱,不可同时为空
     *                      + int(10) memActive 是否在职(0: 已离职; 1: 在职)
     *                      + string(54) memUsername 用户名称
     *                      + int(10) memNum 用户编号
     *                      + int(10) memAdmincp 是否管理员(0: 非管理员; 1: 管理员)
     *                      + int(11) memGroupid 分组ID
     *                      + int(2) memGender 性别(0: 未知; 1: 男; 2: 女)
     *                      + string(255) memAddress 用户所在地址
     *                      + string(18) memIdcard 身份证号
     *                      + string(64) memTelephone 电话号码, 一般是固话
     *                      + string(12) memQq QQ
     *                      + string(10) memBirthday 生日(年月日)
     *                      + string(255) memRemark 备注
     *                      + string(255) memExt1 预留字段
     *                      + string(255) memExt2 预留字段
     *                      + string(255) memExt3 预留字段
     *                      + string(255) memExt4 预留字段
     *                      + string(255) memExt5 预留字段
     *                      + string(255) memExt6 预留字段
     *                      + string(255) memExt7 预留字段
     *                      + string(255) memExt8 预留字段
     *
     * @throws \VcySDK\Exception
     * @return boolean|mixed
     */
    public function update($data)
    {
        $this->service->getValue($data, ['dpIdList']);

        $memberAttr = new MemberAttr();
        list($defData, $extAttrs) = $memberAttr->splitAttr($data);
        $result = $this->serv->postSDK(self::UPDATE_URL, $defData, 'generateApiUrlA');

        if ($result['memUid'] && $extAttrs) {
            $extData = $memberAttr->formatExtData($result['memUid'], $extAttrs);
            $this->batModifyExt(['data' => $extData]);
        }

        return $result;
    }

    /**
     * 更新所有用户的扩展字段
     *
     * @param array $member 人员数据
     *                      + string(255) memExt1 预留字段
     *                      + string(255) memExt2 预留字段
     *                      + string(255) memExt3 预留字段
     *                      + string(255) memExt4 预留字段
     *                      + string(255) memExt5 预留字段
     *                      + string(255) memExt6 预留字段
     *                      + string(255) memExt7 预留字段
     *                      + string(255) memExt8 预留字段
     *
     * @throws \VcySDK\Exception
     * @return boolean|mixed
     */
    public function updateExt($member)
    {

        return $this->serv->postSDK(self::UPDATE_EXT_URL, $member, 'generateApiUrlA');
    }

    /**
     * 查询用户对应的部门列表
     *
     * @param array $condition 查询条件
     *
     * @return boolean|multitype
     */
    public function listDepartment($condition)
    {

        return $this->serv->postSDK(self::MEMBER_DEPARTMENT_LIST_URL, $condition, 'generateApiUrlA');
    }

    /**
     * 查询用户对应职位的列表
     *
     * @param array $condition 查询条件
     *
     * @return bool|mixed
     * @throws \VcySDK\Exception
     */
    public function listJob($condition)
    {

        return $this->serv->postSDK(self::MEMBER_JOB_LIST_URL, $condition, 'generateApiUrlA');
    }

    /**
     * 添加人员
     *
     * @param array $data 人员数据
     *                    + array dpIdList 部门ID
     *                    + string(54) memUsername 用户名称
     *                    + string(64) memWeixin 微信号 手机/微信号/邮箱,不可同时为空
     *                    + string(11) memMobile 手机号码 手机/微信号/邮箱,不可同时为空
     *                    + string(80) memEmail 邮箱地址 手机/微信号/邮箱,不可同时为空
     *                    + int(10) memActive 是否在职(0: 已离职; 1: 在职)
     *                    + int(10) memNum 用户编号
     *                    + int(10) memAdmincp 是否管理员(0: 非管理员; 1: 管理员)
     *                    + int(11) memGroupid 分组ID
     *                    + int(2) memGender 性别(0: 未知; 1: 男; 2: 女)
     *                    + string(255) memAddress 用户所在地址
     *                    + string(18) memIdcard 身份证号
     *                    + string(64) memTelephone 电话号码, 一般是固话
     *                    + string(12) memQq QQ
     *                    + string(10) memBirthday 生日(年月日)
     *                    + string(255) memRemark 备注
     *                    + string(255) memExt1 预留字段
     *                    + string(255) memExt2 预留字段
     *                    + string(255) memExt3 预留字段
     *                    + string(255) memExt4 预留字段
     *                    + string(255) memExt5 预留字段
     *                    + string(255) memExt6 预留字段
     *                    + string(255) memExt7 预留字段
     *                    + string(255) memExt8 预留字段
     *
     * @return bool|mixed
     * @throws \VcySDK\Exception
     */
    public function add($data)
    {
        $this->service->getValue($data, ['dpIdList']);

        $memberAttr = new MemberAttr();
        list($defData, $extAttrs) = $memberAttr->splitAttr($data);
        $result = $this->serv->postSDK(self::ADD_URL, $defData, 'generateApiUrlA');

        if ($result['memUid'] && $extAttrs) {
            $extData = $memberAttr->formatExtData($result['memUid'], $extAttrs);
            $this->batModifyExt(['data' => $extData]);
        }

        return $result;
    }

    /**
     * 用户删除
     *
     * @param array $condition 条件数组
     *                         + string(32) memUid 人员ID
     *
     * @return bool|mixed
     * @throws \VcySDK\Exception
     */
    public function delete($condition)
    {

        return $this->serv->postSDK(self::DELETE_URL, $condition, 'generateApiUrlA');
    }

    /**
     * 用户列表
     *
     * @param $condition
     *        + array memUids memUid数组
     *        + int pageNum 页码
     *        + int pageSize 每页记录数
     *        + string|array name 搜索条件
     * @return array|bool
     */
    public function searchList($condition)
    {

        $this->service->getValue($condition, ['memUids']);
        return $this->serv->postSDK(self::SEARCH_URL, $condition, 'generateApiUrlA');
    }

    /**
     * 读取应用权限列表
     *
     * @return array|bool
     */
    public function appAllow()
    {

        return $this->serv->postSDK(self::APP_ALLOW_URL, [], 'generateApiUrlA');
    }

    /**
     * 批量删除
     *
     * @param $param
     *        + string memUids 用户ID列表
     * @return array|bool
     */
    public function batDelete($param)
    {

        return $this->serv->postSDK(self::BAT_DEL_URL, $param, 'generateApiUrlA');
    }

    /**
     * 批量启用、禁用
     *
     * @param $param
     *        + array memUids 用户ID列表
     *        + int enable 操作类型 : 1=启用, 0=禁用
     * @return array|bool
     */
    public function batchModifyStatus($param)
    {

        return $this->serv->postSDK(self::MODIFY_STATUS_URL, $param, 'generateApiUrlA');
    }

    /**
     * 批量用户移动部门
     *
     * @param $param
     *        + array memUids 用户UID列表
     *        + array dpIdList 部门ID列表
     * @return array|bool
     */
    public function moveDept($param)
    {

        return $this->serv->postSDK(self::MOVE_DEPT_URL, $param, 'generateApiUrlA');
    }

    /**
     * 添加扩展属性
     *
     * @param $param
     *        + string memUid 用户UID
     *        + string extKey 扩展属性Key
     *        + mixed extValue 扩展属性Value
     * @return array|bool
     */
    public function addExt($param)
    {
        return $this->serv->postSDK(self::MEMBER_EXT_ADD_URL, $param, 'generateApiUrlA');
    }

    /**
     * 批量添加扩展属性
     *
     * @param $param
     *        + array data 属性数据
     * @return array|bool
     *
     * $data = [
     *      [
     *          'memUid' => '',
     *          'extKey' => '',
     *          'extValue' => '',
     *      ],
     * ];
     *
     */
    public function batAddExt($param)
    {
        return $this->serv->postSDK(self::MEMBER_EXT_BATADD_URL, $param, 'generateApiUrlA');
    }

    /**
     * 查询用户扩展属性
     *
     * @param $param
     *        + string extId 扩展属性ID
     * @return array|bool
     */
    public function getExt($param)
    {
        return $this->serv->postSDK(self::MEMBER_EXT_GET_URL, $param, 'generateApiUrlA');
    }

    /**
     * 查询用户所有扩展属性
     *
     * @param $param
     *        + string memUid 用户UID
     * @return array|bool
     */
    public function listExt($param)
    {
        return $this->serv->postSDK(self::MEMBER_EXT_LIST_URL, $param, 'generateApiUrlA');
    }

    /**
     * 修改用户扩展属性
     *
     * @param $param
     *        + string extId 扩展属性ID
     *        + string memUid 用户UID
     *        + string extKey 扩展属性Key
     *        + mixed extValue 扩展属性Value
     * @return array|bool
     */
    public function modifyExt($param)
    {
        return $this->serv->postSDK(self::MEMBER_EXT_MODIFY_URL, $param, 'generateApiUrlA');
    }

    /**
     * 删除用户扩展属性
     *
     * @param $param
     *        + string extId 扩展属性ID
     * @return array|bool
     */
    public function delExt($param)
    {
        return $this->serv->postSDK(self::MEMBER_EXT_DEL_URL, $param, 'generateApiUrlA');
    }

    /**
     * 批量编辑扩展属性
     *
     * @param $param
     *        + array data 属性数据
     * @return array|bool
     *
     * $data = [
     *      [
     *          'memUid' => '',
     *          'extKey' => '',
     *          'extValue' => '',
     *      ],
     * ];
     *
     */
    public function batModifyExt($param)
    {
        return $this->serv->postSDK(self::MEMBER_EXT_BATMODIFY_URL, $param, 'generateApiUrlA');
    }

    /**
     * 删除所有用户指定扩展属性
     *
     * @param $param
     *        + string extKey 扩展属性Key
     * @return array|bool
     */
    public function delAllExt($param)
    {
        return $this->serv->postSDK(self::MEMBER_EXT_ALLDEL_URL, $param, 'generateApiUrlA');
    }

    /**
     * 人员、已关注、未关注、禁用人员总数
     * @return array|bool
     * @throws \VcySDK\Exception
     */
    public function memberRelevantTotal()
    {

        return $this->serv->postSDK(self::MEMBER_RELEVANT_TOTAL, [], 'generateApiUrlE');
    }

    /**
     * 在职情况统计
     * @return array|bool
     * @throws \VcySDK\Exception
     */
    public function memberActiveTotal()
    {

        return $this->serv->postSDK(self::MEMBER_ACTIVE_TOTAL, [], 'generateApiUrlE');
    }

    /**
     * 判断指定用户信息是否已存在 (手机号, 邮箱, 微信号)
     * @param $condition
     * @return array|bool
     * @throws \VcySDK\Exception
     */
    public function checkMemInfoSingle($condition)
    {
        return $this->serv->postSDK(self::CHECK_INFO_EXIST_URL, $condition, 'generateApiUrlA');
    }
}
