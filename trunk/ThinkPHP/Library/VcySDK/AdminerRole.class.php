<?php
/**
 * AdminerRole.class.php
 * 角色权限
 *
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt LGPL
 * @copyright  Copyright (c) 2014 - ? VcySDK (http://www.vchangyi.com/)
 * @author     zhuxun37
 * @version    1.0.0
 */

namespace VcySDK;

class AdminerRole
{

    /**
     * 管理员列表过滤字段: 过滤没有绑定手机号的管理员
     */
    const ROLE_LIST_FILTER_TYPE = 2;

    /**
     * 接口调用类
     *
     * @var object|Service
     */
    private $serv = null;

    /**
     * 新增角色
     * %s = {apiUrl}/b/{enumber}/role/add
     *
     * @var string
     */
    const ADD_URL = '%s/role/add';

    /**
     * 修改角色
     * %s = {apiUrl}/b/{enumber}/role/modify
     *
     * @var string
     */
    const MODIFY_URL = '%s/role/modify';

    /**
     * 删除角色
     * %s = {apiUrl}/b/{enumber}/role/del
     *
     * @var string
     */
    const DEL_URL = '%s/role/del';

    /**
     * 角色详情
     * %s = {apiUrl}/b/{enumber}/role/detail
     *
     * @var string
     */
    const DETAIL_URL = '%s/role/detail';

    /**
     * 角色列表
     * %s = {apiUrl}/b/{enumber}/role/list
     *
     * @var string
     */
    const LIST_URL = '%s/role/list';

    /**
     * 初始化
     *
     * @param object $serv 接口调用类
     */
    public function __construct($serv)
    {

        $this->serv = $serv;
    }

    /**
     * 添加角色
     *
     * @param $data
     * + earName 角色名称
     * + earCpmenu 后台权限菜单
     *
     * @return array|bool
     */
    public function add($data)
    {

        return $this->serv->postSDK(self::ADD_URL, $data, 'generateApiUrlE');
    }

    /**
     * 修改角色
     *
     * @param $data
     * + earId 角色ID
     * + earName 角色名称
     * + earCpmenu 后台权限菜单
     *
     * @return array|bool
     */
    public function modify($data)
    {

        return $this->serv->postSDK(self::MODIFY_URL, $data, 'generateApiUrlE');
    }

    /**
     * 删除角色
     *
     * @param $data
     * + earId 角色ID
     *
     * @return array|bool
     */
    public function delete($data)
    {

        return $this->serv->postSDK(self::DEL_URL, $data, 'generateApiUrlE');
    }

    /**
     * 角色详情
     *
     * @param $data
     * + earId 角色ID
     *
     * @return array|bool
     */
    public function detail($data)
    {

        return $this->serv->postSDK(self::DETAIL_URL, $data, 'generateApiUrlE');
    }

    /**
     * 角色列表
     *
     * @param array $condition 查询条件
     * @param mixed $orders    排序字段
     * @param int   $page      当前页码
     * @param int   $perpage   每页记录数
     *
     * @return array|bool
     */
    public function listAll($condition, $page = 1, $perpage = 30, $orders = array())
    {

        // 查询参数
        $condition = $this->serv->mergeListApiParams($condition, $orders, $page, $perpage);

        return $this->serv->postSDK(self::LIST_URL, $condition, 'generateApiUrlE');
    }
}
