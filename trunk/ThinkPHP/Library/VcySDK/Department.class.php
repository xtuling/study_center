<?php
/**
 * Department.class.php
 * 部门接口操作类
 *
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt LGPL
 * @copyright  Copyright (c) 2014 - ? VcySDK (http://www.vchangyi.com/)
 * @author     zhuxun37
 * @version    1.0.0
 */

namespace VcySDK;


class Department
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
     * 创建部门的接口地址
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const CREATE_URL = '%s/department/create';

    /**
     * 编辑部门的接口地址
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const MODIFY_URL = '%s/department/modify';

    /**
     * 删除部门的接口地址
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const DELETE_URL = '%s/department/del';

    /**
     * 获取部门列表的接口地址
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const LIST_URL = '%s/department/page-list';

    /**
     * 获取部门详情的接口地址
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const DETAIL_URL = '%s/department/detail';

    /**
     * 获取部门类型列表的接口地址
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const TYPE_LIST_URL = '%s/department/type-list';

    /**
     * 获取部门类型配置列表的接口地址
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const FIELD_CONFIG_LIST_URL = '%s/department/field-config-list';

    /**
     * 初始化
     *
     * @param Service $serv 接口调用类
     */
    public function __construct(Service $serv)
    {

        $this->serv = $serv;
        $this->service = new Service();
    }

    /**
     * 创建部门
     *
     * @param array $condition 请求参数
     *                         + dpName string 部门名称
     *                         + dpParentid int 上级ID
     *                         + dpDisplayorder int 排序值
     *
     * @return boolean|multitype:
     */
    public function create($condition)
    {

        return $this->serv->postSDK(self::CREATE_URL, $condition, 'generateApiUrlA');
    }

    /**
     * 编辑部门
     *
     * @param array $condition 请求参数
     *                         + dpId string 部门ID
     *                         + dpName string 部门名称
     *                         + dpParentid int 上级ID
     *                         + dpDisplayorder int 排序值
     *
     * @return boolean|multitype:
     */
    public function modify($condition)
    {

        return $this->serv->postSDK(self::MODIFY_URL, $condition, 'generateApiUrlA');
    }

    /**
     * 删除部门
     *
     * @param array $condition 请求参数
     *                         + dpId string 部门ID
     *
     * @return boolean|multitype:
     */
    public function delete($condition)
    {

        return $this->serv->postSDK(self::DELETE_URL, $condition, 'generateApiUrlA');
    }

    /**
     * 获取部门列表
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
        if (isset($condition['thirds']) && is_array($condition['thirds'])) {
            $this->service->getValue($condition, ['thirds']);
        }
        $condition = $this->serv->mergeListApiParams($condition, $orders, $page, $perpage);

        return $this->serv->postSDK(self::LIST_URL, $condition, 'generateApiUrlA');
    }

    /**
     * 获取部门详情
     *
     * @param array $condition 请求参数
     *                         + dpId string 部门ID
     *
     * @return boolean|multitype:
     */
    public function detail($condition)
    {

        return $this->serv->postSDK(self::DETAIL_URL, $condition, 'generateApiUrlA');
    }

    /**
     * 获取部门类型列表
     * @param array $condition
     * @param int   $page
     * @param int   $perpage
     * @param array $orders
     * @return array|bool
     */
    public function listType($condition = array(), $page = 1, $perpage = 30, $orders = array())
    {

        $condition = $this->serv->mergeListApiParams($condition, $orders, $page, $perpage);

        return $this->serv->postSDK(self::TYPE_LIST_URL, $condition, 'generateApiUrlA');
    }

    /**
     * 获取部门类型配置列表
     * @param array $condition
     * @param int   $page
     * @param int   $perpage
     * @param array $orders
     * @return array|bool
     */
    public function listFieldConfig($condition = array(), $page = 1, $perpage = 30, $orders = array())
    {

        $condition = $this->serv->mergeListApiParams($condition, $orders, $page, $perpage);

        return $this->serv->postSDK(self::FIELD_CONFIG_LIST_URL, $condition, 'generateApiUrlA');
    }

}
