<?php

/**
 * Cron.class.php
 * 计划任务接口操作类
 * @license http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt LGPL
 * @copyright  Copyright (c) 2014 - ? VcySDK (http://www.vchangyi.com/)
 * @author zhuxun37
 * @version 1.0.0
 */
namespace VcySDK;

class Cron
{

    /**
     * 查询
     * %s = {apiUrl}/b/{enumber}
     *
     * @var string
     */
    const LIST_URL = '%s/cron/list';

    /**
     * 添加
     * %s = {apiUrl}/b/{enumber}
     *
     * @var string
     */
    const ADD_URL = '%s/cron/add';

    /**
     * 更新
     * %s = {apiUrl}/b/{enumber}
     *
     * @var string
     */
    const UPDATE_URL = '%s/cron/update';

    /**
     * 删除
     * %s = {apiUrl}/b/{enumber}
     *
     * @var string
     */
    const DELETE_URL = '%s/cron/delete';

    /**
     * 详情
     * %s = {apiUrl}/b/{enumber}
     *
     * @var string
     */
    const GET_URL = '%s/cron/get';

    /**
     * 暂停
     * %s = {apiUrl}/b/{enumber}
     *
     * @var string
     */
    const PAUSE_URL = '%s/cron/pause';

    /**
     * 恢复
     * %s = {apiUrl}/b/{enumber}
     *
     * @var string
     */
    const RESUME_URL = '%s/cron/resume';

    /**
     * 接口调用类
     *
     * @var object
     */
    private $serv = null;

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
     * 查询
     *
     * @param array $condition 查询条件
     *        + int $crServerStatus 任务运行状态,1=READY,2=RUNNING,3=DISABLED,4=PAUSED,5=CRASHED,6=SHUTDOWN
     *        + int int $crExecuteStatus 任务处理状态，0=未执行,1=成功,2=失败
     *        + int $crType 任务类型,1=简单任务,2=回调任务
     *        + string $crRemark 任务标识(sign_in,sign_on等)
     * @param int $pageNum 页码
     * @param int $pageSize 每页条数
     * @param array $orderList 排序
     *        + string column 排序字段
     *        + string orderType 排序类型
     * @return mixed
     */
    public function listAll($condition = array(), $pageNum = 1, $pageSize = 30, $orderList = array())
    {
        $condition = $this->serv->mergeListApiParams($condition, $orderList, $pageNum, $pageSize);

        return $this->serv->postSDK(self::LIST_URL, $condition, 'generateApiUrlE');
    }

    /**
     * 添加
     *
     * @param array $params 提交数据
     *        + String $crRemark 任务标识(sign_in,sign_on等)
     *        + String $crCron cron表达式(具体使用方式百度or谷歌)
     *        + String $crDescription 任务描述
     *        + int $crType 任务类型,1=简单任务,2=回调任务(默认1)
     *        + String $crParams 请求参数(注意:如果传了此参数,数据格式必须为json)
     *        + String $crMethod 请求Method方法(GET,POST,PUT,DELETE),回调型任务此参数为必填
     *        + String $crReqUrl 回调地址(回调型任务此参数为必填)
     *        + int $crTimes 计划任务需要运行的次数(默认0为不限制)
     *        + int $crMonitorExecution 监控作业执行时状态,1=true,0=false(每次作业执行时间和间隔时间均非常短的情况，建议不监控作业运行时状态以提升效率，因为是瞬时状态，所以无必要监控。默认0)
     * @return mixed
     */
    public function add($params)
    {

        return $this->serv->postSDK(self::ADD_URL, $params, 'generateApiUrlE');
    }

    /**
     * 更新
     *
     * @param array $params 提交数据
     *        + String $crId 任务ID
     *        + String $crParams 请求参数(数据格式为json)
     *        + String $crMethod 请求Method方法(GET,POST,PUT,DELETE),回调型任务此参数为必填
     *        + String $crReqUrl 回调地址(回调型任务此参数为必填)
     *        + Int $crTimes 计划任务需要运行的次数(默认0为不限制)
     *        + String $crCron cron表达式(具体使用方式百度or谷歌)
     *        + Int $crMonitorExecution 监控作业执行时状态,1=true,0=false(每次作业执行时间和间隔时间均非常短的情况，建议不监控作业运行时状态以提升效率，因为是瞬时状态，所以无必要监控。默认0)
     *        + String $crDescription 任务描述
     * @return mixed
     */
    public function update($params)
    {

        return $this->serv->postSDK(self::UPDATE_URL, $params, 'generateApiUrlE');
    }

    /**
     * 删除
     *
     * @param String $crId 任务ID
     * @return mixed
     */
    public function delete($crId)
    {
        $params = [
            'crId' => $crId,
        ];

        return $this->serv->postSDK(self::DELETE_URL, $params, 'generateApiUrlE');
    }

    /**
     * 获取任务详情
     *
     * @param string $crId 任务ID
     * @return mixed
     */
    public function get($crId)
    {
        $params = [
            'crId' => $crId,
        ];

        return $this->serv->postSDK(self::GET_URL, $params, 'generateApiUrlE');
    }

    /**
     * 暂停
     *
     * @param string $crId 任务ID
     * @return mixed
     */
    public function pause($crId)
    {
        $params = [
            'crId' => $crId,
        ];

        return $this->serv->postSDK(self::PAUSE_URL, $params, 'generateApiUrlE');
    }

    /**
     * 恢复
     *
     * @param string $crId 任务ID
     * @return mixed
     */
    public function resume($crId)
    {
        $params = [
            'crId' => $crId,
        ];

        return $this->serv->postSDK(self::RESUME_URL, $params, 'generateApiUrlE');
    }
}
