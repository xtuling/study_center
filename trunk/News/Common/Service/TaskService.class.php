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

class TaskService extends AbstractService
{
    // 构造方法
    public function __construct()
    {
        parent::__construct();
        $this->_d = new TaskModel();
    }

    /**
     * 创建计划任务:查询视频、文件附件转码状态
     * @author liyifei
     * @param int $article_id 新闻ID
     * @return bool
     */
    public function createCheckAttachTask($article_id)
    {
        // 已有计划任务时,不再创建新的计划任务
        $task = $this->get_by_conds(['article_id' => $article_id]);
        if ($task) {
            return true;
        }

        // 定时执行时间(每5分钟执行一次)
        $java_cron_time = '0 0/5 * * * ?';

        // UC保存计划任务
        $cronSdk = new Cron(Service::instance());
        $result = $cronSdk->add([
            'crRemark' => sprintf('%s/%s/%s/CheckAttach', QY_DOMAIN, APP_IDENTIFIER, $article_id),
            'crDescription' => '定时检查附件',
            'crReqUrl' => oaUrl('Frontend/Callback/CheckAttach/Index', ['article_id' => $article_id]),
            'crMethod' => 'GET',
            'crCron' => $java_cron_time,
        ]);

        // 记录计划任务
        if (is_array($result) && isset($result['crId'])) {
            $this->insert([
                'article_id' => $article_id,
                'cron_id' => $result['crId'],
            ]);
        }

        return true;
    }
}