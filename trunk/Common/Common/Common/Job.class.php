<?php
/**
 * Job.class.php
 * 职位
 * User: zhoutao
 * Date: 16/7/15
 * Time: 下午12:31
 */

namespace Common\Common;

use VcySDK\Service;

class Job
{

    /**
     * 职位列表
     *
     * @var array
     */
    protected $_job = [];

    /**
     * 单例
     * @return Job
     */
    public static function &instance()
    {

        static $instance;
        if (empty($instance)) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Job constructor.
     * @param bool $fromSdk 从SDK获取职位数据 true 是 false 否
     */
    public function __construct($fromSdk = true)
    {

        // 获取职位缓存
        $this->_job = $fromSdk ?
            $this->listJobFromSdk() : Cache::instance()->get('Common.Job', '', cfg('DATA_CACHE_TIME'));

        return true;
    }

    /**
     * 获取职位列表
     * @return array|mixed
     */
    public function listAll()
    {

        return $this->_job;
    }

    /**
     * 根据职位id数组获取对应的职位信息
     *
     * @param $ids
     * @return array
     */
    public function listById($ids)
    {

        if (!is_array($ids) || empty($this->_job)) {
            return [];
        }

        $temp = [];
        foreach ($this->_job as $_id => $_job) {
            if (in_array($_id, $ids)) {
                $temp[$_id] = $_job;
            }
        }

        return $temp;
    }

    /**
     * 根据职位id获取对应的职位信息
     *
     * @param $id
     * @return array
     */
    public function getById($id)
    {

        if (empty($id) || empty($this->_job)) {
            return [];
        }

        return empty($this->_job[$id]) ? [] : $this->_job[$id];
    }

    /**
     * 根据职位名称获取数据
     *
     * @param $name
     * @return array
     */
    public function getByName($name)
    {

        if (empty($name) || empty($this->_job)) {
            return [];
        }

        // 把职位名称变为键值, 便于索引
        $jobArr = array_combine_by_key($this->_job, 'jobName');

        return empty($jobArr[$name]) ? [] : $jobArr[$name];
    }

    /**
     * 根据职位名称数组 获取数据
     *
     * @param $names
     * @return array
     */
    public function listByName($names)
    {

        if (empty($names) || empty($this->_job)) {
            return [];
        }

        // 把职位名称变为键值, 便于索引
        $jobArr = array_combine_by_key($this->_job, 'jobName');

        $temp = [];
        foreach ($jobArr as $_name => $_job) {
            if (in_array($_name, $names)) {
                $temp[$_name] = $_job;
            }
        }

        return $temp;
    }

    /**
     * 清除缓存
     * @return bool
     */
    public function clearJobCache()
    {

        $cache = &Cache::instance();
        $cache->set('Common.Job', null);

        return true;
    }

    /**
     * 从SDK获取职位列表
     * @return array|bool
     */
    private function listJobFromSdk()
    {
        $job_sdk = new \VcySDK\Job(Service::instance());
        $result = $job_sdk->listAll(array(), 1, 5000);

        if (is_array($result) && isset($result['list'])) {
            $jobs = array_combine_by_key($result['list'], 'jobId');
            return $jobs;
        }

        return [];
    }

    /**
     * 添加职位
     * @param $jobData
     * @return bool
     */
    public function addJob($jobData)
    {
        $jobSdk = new \VcySDK\Job(Service::instance());
        $jobSdk->create($jobData);

        // 清楚缓存
        $this->clearJobCache();

        return true;
    }
}
