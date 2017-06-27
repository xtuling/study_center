<?php
/**
 * Enterprise.class.php
 * 企业信息接口操作类
 *
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt LGPL
 * @copyright  Copyright (c) 2014 - ? VcySDK (http://www.vchangyi.com/)
 * @author     zhuxun37
 * @version    1.0.0
 */
namespace VcySDK;

use VcySDK\Logger;
use VcySDK\Config;

class Enterprise
{

    /**
     * 接口调用类
     *
     * @var object|Service
     */
    private $serv = null;

    /**
     * 注册企业
     * %s = {apiUrl}/s/enterprise/register
     *
     * @var string
     */
    const REGISTER_URL = '%s/enterprise/register';

    /**
     * 企业信息修改
     * %s = {apiUrl}/b/{enumber}/enterprise/modify
     *
     * @var string
     */
    const MODIFY_URL = '%s/enterprise/modify';

    /**
     * 企业列表
     * %s = {apiUrl}/s/enterprise/page-list
     *
     * @var string
     */
    const LIST_URL = '%s/enterprise/page-list';

    /**
     * 获取企业信息
     * %s = {apiUrl}/b/{enumber}/enterprise/detail
     *
     * @var string
     */
    const GET_URL = '%s/enterprise/detail';

    /**
     * 删除企业信息
     * %s = {apiUrl}/b/{enumber}/enterprise/del
     *
     * @var string
     */
    const DEL_URL = '%s/enterprise/del';

    /**
     * 获取企业配置列表
     * %s = {apiUrl}/b/{enumber}/ensetting/query
     *
     * @var string
     */
    const LIST_SETTING_URL = '%s/ensetting/query';

    /**
     * 修改企业配置
     * %s = {apiUrl}/b/{enumber}/ensetting/modify
     *
     * @var string
     */
    const MODIFY_SETTING_URL = '%s/ensetting/modify';

    /**
     * 企业消息列表
     * %s = {apiUrl}/b/{enumber}/enmsg/page-list
     *
     * @var string
     */
    const ENMSG_PAGE_LIST = '%s/enmsg/page-list';

    /**
     * 企业消息详情
     * %s = {apiUrl}/b/{enumber}/enmsg/detail
     *
     * @var string
     */
    const ENMSG_DETAIL = '%s/enmsg/detail';

    /**
     * 添加企业消息
     * %s = {apiUrl}/s/enmsg/add
     */
    const ENMSG_ADD = '%s/enmsg/add';

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
     * @param array $enterprise 企业信息
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function register($enterprise)
    {

        return $this->serv->postSDK(self::REGISTER_URL, $enterprise, 'generateApiUrlS');
    }

    /**
     * @param array $enterprise 企业信息
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function modify($enterprise)
    {

        return $this->serv->postSDK(self::MODIFY_URL, $enterprise, 'generateApiUrlE');
    }

    /**
     * 获取企业列表
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

        return $this->serv->postSDK(self::LIST_URL, $condition, 'generateApiUrlS');
    }

    /**
     * 获取企业信息
     *
     * @param array $condition 请求参数
     *                         + epId string 企业ID
     *
     * @return boolean|mixed:
     */
    public function detail()
    {

        return $this->serv->postSDK(self::GET_URL, [], 'generateApiUrlE');
    }

    /**
     * 获取企业配置列表
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function listSetting()
    {

        return $this->serv->postSDK(self::LIST_SETTING_URL, array(), 'generateApiUrlE');
    }

    /**
     * @param array $setting 配置信息
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function modifySetting($setting)
    {

        return $this->serv->postSDK(self::MODIFY_SETTING_URL, $setting, 'generateApiUrlE');
    }

    /**
     * 删除企业
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function del()
    {

        return $this->serv->postSDK(self::DEL_URL, array(), 'generateApiUrlE');
    }

    /**
     * 企业消息列表
     * @param $params
     * @return array|bool
     */
    public function messageList($params)
    {

        return $this->serv->postSDK(self::ENMSG_PAGE_LIST, $params, 'generateApiUrlE');
    }

    /**
     * 企业消息详情
     * @param $params
     * @return array|bool
     */
    public function messageDetail($params)
    {

        return $this->serv->postSDK(self::ENMSG_DETAIL, $params, 'generateApiUrlE');
    }

    /**
     * 添加企业消息
     * @param $params
     * @return array|bool
     * @throws Exception
     */
    public function addMessage($params) {
        return $this->serv->postSDK(self::ENMSG_ADD, $params, 'generateApiUrlS');
    }

}
