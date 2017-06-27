<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/4/13
 * Time: 11:38
 */
namespace Frontend\Controller\Callback;

use Think\Log;
use VcySDK\Service;
use VcySDK\Cron;
use VcySDK\FileConvert;
use Common\Common\Constant;
use Common\Service\SourceService;
use Common\Service\SourceAttachService;
use Common\Service\TaskService;

class CheckAttachController extends AbstractController
{
    /**
     * 检查附件（视频转码、文件转换）计划任务回调接口
     * @author zhonglei
     */
    public function Index()
    {
        Log::record(sprintf('---%s %s CheckAttach START---', QY_DOMAIN, APP_IDENTIFIER), Log::INFO);

        $source_id = I('get.source_id', 0, 'intval');

        if (empty($source_id)) {
            $this->_exit();
        }

        $taskServ = new TaskService();
        $task = $taskServ->get_by_conds(['source_id' => $source_id]);

        // 未找到任务，退出
        if (empty($task)) {
            Log::record("not found task, source_id: {$source_id}", Log::INFO);
            $this->_exit();
        }

        $cron_id = $task['cron_id'];
        $sourceServ = new SourceService();
        $source = $sourceServ->get($source_id);

        // 未找到素材，删除计划任务
        if (empty($source)) {
            Log::record("not found source, source_id: {$source_id}", Log::INFO);
            $this->_delTask($source_id, $cron_id);
        }

        // 获取附件
        $attachServ = new SourceAttachService();
        $source_attachs = $attachServ->list_by_conds(['source_id' => $source_id]);

        // 未找到附件，删除计划任务
        if (empty($source_attachs)) {
            Log::record("not found attach, source_id: {$source_id}", Log::INFO);
            $this->_delTask($source_id, $cron_id);
        }

        $convertServ = new FileConvert(Service::instance());
        $stop_task = true;

        foreach ($source_attachs as $source_attach) {
            // 已完成
            if (!empty($source_attach['at_convert_url'])) {
                continue;
            }

            $at_convert_url = '';

            switch ($source_attach['at_type']) {
                // 视频
                case Constant::ATTACH_TYPE_VIDEO:
                    $result = $convertServ->getVodPlayUrl($source_attach['at_id']);
                    if (is_array($result) && isset($result['url'])) {
                        $at_convert_url = str_replace('http://', '//', $result['url']);
                        $at_convert_url = str_replace('https://', '//', $at_convert_url);
                    }
                    break;

                // 文件
                case Constant::ATTACH_TYPE_FILE:
                    $result = $convertServ->get($source_attach['at_id']);

                    if (is_array($result) && isset($result['caAttachment']) &&
                        $result['caConvertStatus'] == FileConvert::CONVERT_STATUS_SUCCESS) {
                        $at_convert_url = $result['caAttachment'];
                    }
                    break;
            }

            if (empty($at_convert_url)) {
                $stop_task = false;
            } else {
                $attachServ->update($source_attach['source_attach_id'], ['at_convert_url' => $at_convert_url]);
                Log::record("convert success, source_attach_id: {$source_attach['source_attach_id']}, at_convert_url: {$at_convert_url}", Log::INFO);
            }
        }

        // 结束任务
        if ($stop_task) {
            // 更新素材状态
            if ($source['source_status'] == Constant::SOURCE_STATUS_CONVERT) {
                $sourceServ->update($source_id, ['source_status' => Constant::SOURCE_STATUS_NORMAL]);
                Log::record("update source_status, source: {$source_id}", Log::INFO);
            }

            $this->_delTask($source_id, $cron_id);
        }

        $this->_exit();
    }

    /**
     * 退出
     * @author zhonglei
     * @return void
     */
    private function _exit()
    {
        // 日志结束
        Log::record(sprintf('---%s %s CheckAttach END---', QY_DOMAIN, APP_IDENTIFIER), Log::INFO);
        exit('SUCCESS');
    }

    /**
     * 删除计划任务并退出
     * @author zhonglei
     * @param int $source_id 素材ID
     * @param string $cron_id UC计划任务ID
     * @return void
     */
    private function _delTask($source_id, $cron_id)
    {
        $taskServ = new TaskService();
        $taskServ->delete_by_conds([
            'source_id' => $source_id,
            'cron_id' => $cron_id,
        ]);

        $cronSdk = new Cron(Service::instance());
        $cronSdk->delete($cron_id);

        Log::record("delete task: {$cron_id}", Log::INFO);
        $this->_exit();
    }
}
