<?php
/**
 * Plugin.class.php
 * 应用接口操作类
 *
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt LGPL
 * @copyright  Copyright (c) 2014 - ? VcySDK (http://www.vchangyi.com/)
 * @author     zhuxun37
 * @version    1.0.0
 */
namespace VcySDK;

use VcySDK\Logger;
use VcySDK\Config;

class Plugin
{

    /**
     * 接口调用类
     *
     * @var object|Service
     */
    private $serv = null;

    /**
     * 新增应用
     * %s = {apiUrl}/s/plugin/add
     *
     * @var string
     */
    const ADD_URL = '%s/plugin/add';

    /**
     * 编辑应用信息
     * %s = {apiUrl}/s/plugin/modify
     *
     * @var string
     */
    const MODIFY_URL = '%s/plugin/modify';

    /**
     * 获取应用详情
     * %s = {apiUrl}/s/plugin/detail
     *
     * @var string
     */
    const GET_URL = '%s/plugin/detail';

    /**
     * 获取应用列表
     * %s = {apiUrl}/s/plugin/pageList
     *
     * @var string
     */
    const LIST_URL = '%s/plugin/pageList';

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
     * @param array $plugin 应用信息
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function add($plugin)
    {

        return $this->serv->postSDK(self::ADD_URL, $plugin, 'generateApiUrlE');
    }

    /**
     * @param array $plugin 应用信息
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function modify($plugin)
    {

        return $this->serv->postSDK(self::MODIFY_URL, $plugin, 'generateApiUrlE');
    }

    /**
     * @param array $condition 应用查询条件
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function fetch($condition)
    {

        return $this->serv->postSDK(self::GET_URL, $condition, 'generateApiUrlE');
    }

    /**
     * 获取企业应用列表
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
        $condition = $this->serv->mergeListApiParams($condition, $orders, $page, $perpage);

        return $this->serv->postSDK(self::LIST_URL, $condition, 'generateApiUrlE');
    }
}
