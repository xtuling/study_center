<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/5/22
 * Time: 16:53
 */
namespace Common\Service;

use Think\Log;
use VcySDK\Service;
use VcySDK\Cron;
use Common\Model\TaskModel;
use Common\Common\Constant;

class TaskService extends AbstractService
{
    // 构造方法
    public function __construct()
    {
        parent::__construct();
        $this->_d = new TaskModel();
    }

    /**
     * 创建查询文件转码结果的计划任务
     * @author liyifei
     * @param int $fileId 文件ID
     * @return bool
     */
    public function createTask($fileId)
    {
        // 创建计划任务
        $cronSdk = new Cron(Service::instance());
        $result = $cronSdk->add([
            'crRemark' => sprintf('%s/%s/%s/CheckFile', QY_DOMAIN, APP_IDENTIFIER, $fileId),
            'crDescription' => '定时检查文件转码',
            'crReqUrl' => oaUrl('Frontend/Callback/CheckFile/Index', ['file_id' => $fileId]),
            'crMethod' => 'GET',
            'crCron' => Constant::TASK_CRON_TIME,
        ]);

        // 记录计划任务ID、文件ID
        if (is_array($result) && isset($result['crId'])) {
            $this->insert([
                'file_id' => $fileId,
                'cron_id' => $result['crId'],
            ]);
        }

        return true;
    }

    /**
     * 删除本地计划任务记录、结束UC计划任务
     * @param int $fileId 文件ID
     * @param string $cronId UC计划任务ID
     * @return void
     */
    public function delTask($fileId, $cronId)
    {
        // 删除本地计划任务记录
        $this->_d->delete_by_conds([
            'file_id' => $fileId,
            'cron_id' => $cronId,
        ]);

        // 结束UC计划任务
        $cronSdk = new Cron(Service::instance());
        $cronSdk->delete($cronId);
    }
}
