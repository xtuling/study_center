<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 2017/5/11
 * Time: 下午3:24
 */

namespace Common\Service;


use Com\Validate;
use VcySDK\Job;
use VcySDK\Service;

class JobService extends AbstractService
{

    private $__job = null;

    // 构造方法
    public function __construct()
    {

        parent::__construct();
        $this->__job = new Job(Service::instance());
    }

    /**
     * 搜索职位
     * @param $result  array 职位列表
     * @param $request array 请求参数
     * @return bool
     */
    public function searchList(&$result, $request)
    {

        $condition = array();
        $page = (int)$request['page'];
        $limit = 1500;
        $keyword = (string)$request['keyword'];
        if (!empty($keyword)) {
            $condition['keyword'] = $keyword;
        }

        list(, $limit, $page) = page_limit($page, $limit, $limit);
        $sdkResult = $this->__job->listAll($condition, $page, $limit);

        $result = array(
            'list' => $sdkResult['list'],
            'page' => $sdkResult['pageNum'],
            'limit' => $sdkResult['pageSize'],
            'total' => $sdkResult['total']
        );

        return true;
    }

    /**
     * 新增职位信息
     * @param $result  array 职位信息
     * @param $request array 请求参数
     * @return bool
     */
    public function add(&$result, $request)
    {

        $job = $this->_fetchJob($request);
        $this->_validateJob($job);

        $result = $this->__job->create($job);
        return true;
    }

    /**
     * 删除指定职位
     * @param mixed $result
     * @param array $request
     * @return bool
     */
    public function delete(&$result, $request)
    {

        $jobId = (string)$request['jobId'];
        if (empty($jobId)) {
            E('1003:岗位ID错误');
            return false;
        }

        $condition = array(
            'jobId' => $jobId
        );
        $this->__job->delete($condition);

        return true;
    }

    /**
     * 获取指定职位信息详情
     * @param $result
     * @param $request
     * @return bool
     */
    public function detail(&$result, $request)
    {

        $jobId = (string)$request['jobId'];
        if (empty($jobId)) {
            E('1003:岗位ID错误');
            return false;
        }

        $condition = array(
            'jobId' => $jobId
        );
        $result = $this->__job->detail($condition);

        return true;
    }

    /**
     * 编辑指定职位信息
     * @param $result
     * @param $request
     * @return bool
     */
    public function edit(&$result, $request)
    {

        $jobId = (string)$request['jobId'];
        if (empty($jobId)) {
            E('1003:岗位ID错误');
            return false;
        }

        $job = $this->_fetchJob($request);
        $this->_validateJob($job);

        $job['jobId'] = $jobId;

        $result = $this->__job->modify($job);

        return true;
    }

    /**
     * 获取职位信息
     * @param $request
     * @return array
     */
    protected function _fetchJob($request)
    {

        return array(
            'jobName' => $request['jobName'],
            'jobDisplayorder' => (int)$request['jobDisplayorder']
        );
    }

    /**
     * 检查职位信息合法性
     * @param $job
     * @return bool
     */
    protected function _validateJob(&$job)
    {

        $rules = array(
            'jobName' => 'require|length:2,80'
        );
        $msgs = array(
            'jobName.require' => L('1001:岗位名称不能为空'),
            'jobName.length' => L('1002:岗位名称长度不合法')
        );
        // 开始验证
        $validate = new Validate($rules, $msgs);
        if (!$validate->check($job)) {
            E($validate->getError());
            return false;
        }

        return true;
    }

}