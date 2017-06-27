<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/4/20
 * Time: 15:10
 */
namespace Common\Service;

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
     * 创建计划任务
     * @author zhonglei
     * @param int $source_id 素材ID
     * @return bool
     */
    public function create($source_id)
    {
        $task = $this->get_by_conds(['source_id' => $source_id]);

        if (!empty($task)) {
            return true;
        }

        // 创建计划任务
        $cronSdk = new Cron(Service::instance());
        $result = $cronSdk->add([
            'crRemark' => sprintf('%s/%s/%s/CheckAttach', QY_DOMAIN, APP_IDENTIFIER, $source_id),
            'crDescription' => '定时检查附件',
            'crReqUrl' => oaUrl('Frontend/Callback/CheckAttach/Index', ['source_id' => $source_id]),
            'crMethod' => 'GET',
            'crCron' => Constant::TASK_CRON_TIME,
        ]);

        // 记录计划任务ID、素材ID
        if (is_array($result) && isset($result['crId'])) {
            $this->insert([
                'source_id' => $source_id,
                'cron_id' => $result['crId'],
            ]);
        }

        return true;
    }
}
