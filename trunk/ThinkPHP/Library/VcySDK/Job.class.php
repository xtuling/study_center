<?php
/**
 * Job.class.php
 * 职位接口操作类
 *
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt LGPL
 * @copyright  Copyright (c) 2014 - ? VcySDK (http://www.vchangyi.com/)
 * @author     zhuxun37
 * @version    1.0.0
 */

namespace VcySDK;


class Job
{

    /**
     * 接口调用类
     *
     * @var object
     */
    private $serv = null;

    /**
     * 创建职位的接口地址
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const CREATE_URL = '%s/job/add';

    /**
     * 编辑职位的接口地址
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const MODIFY_URL = '%s/job/update';

    /**
     * 删除职位的接口地址
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const DELETE_URL = '%s/job/delete';

    /**
     * 获取职位列表的接口地址
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const LIST_URL = '%s/job/page-list';

    /**
     * 获取职位详情的接口地址
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const DETAIL_URL = '%s/job/get';

    /**
     * 初始化
     *
     * @param Service $serv 接口调用类
     */
    public function __construct(Service $serv)
    {

        $this->serv = $serv;
    }

    /**
     * 创建职位
     *
     * @param array $condition 请求参数
     *                         + jobName string 职位名称
     *                         + jobParentid int 上级ID
     *                         + jobDisplayorder int 排序值
     *
     * @return boolean|multitype:
     */
    public function create($condition)
    {

        return $this->serv->postSDK(self::CREATE_URL, $condition, 'generateApiUrlB');
    }

    /**
     * 编辑职位
     *
     * @param array $condition 请求参数
     *                         + jobId string 职位ID
     *                         + jobName string 职位名称
     *                         + jobParentid int 上级ID
     *                         + jobDisplayorder int 排序值
     *
     * @return boolean|mixed:
     */
    public function modify($condition)
    {

        return $this->serv->postSDK(self::MODIFY_URL, $condition, 'generateApiUrlB');
    }

    /**
     * 删除职位
     *
     * @param array $condition 请求参数
     *                         + jobId string 职位ID
     *
     * @return boolean|mixed:
     */
    public function delete($condition)
    {

        return $this->serv->postSDK(self::DELETE_URL, $condition, 'generateApiUrlB');
    }

    /**
     * 获取职位列表
     *
     * @param array $condition 查询条件数据
     * @param mixed $orders    排序字段
     * @param int   $page      当前页码
     * @param int   $perpage   每页记录数
     *
     * @return boolean|mixed:
     */
    public function listAll($condition = array(), $page = 1, $perpage = 30, $orders = array())
    {

        // 查询参数
        $condition = $this->serv->mergeListApiParams($condition, $orders, $page, $perpage);

        return $this->serv->postSDK(self::LIST_URL, $condition, 'generateApiUrlB');
    }

    /**
     * 获取职位详情
     *
     * @param array $condition 请求参数
     *                         + jobId string 职位ID
     *
     * @return boolean|mixed:
     */
    public function detail($condition)
    {

        return $this->serv->postSDK(self::DETAIL_URL, $condition, 'generateApiUrlB');
    }
}
