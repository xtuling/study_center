<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/5/22
 * Time: 18:07
 */
namespace Frontend\Controller\Callback;

use Think\Log;
use VcySDK\Service;
use VcySDK\FileConvert;
use Common\Common\Constant;
use Common\Service\TaskService;
use Common\Service\FileService;

class CheckFileController extends AbstractController
{
    /**
     * 检查文件转码计划任务回调接口
     * @author liyifei
     */
    public function Index()
    {
        Log::record(sprintf('---%s %s CheckFile START---', QY_DOMAIN, APP_IDENTIFIER), Log::INFO);

        $fileId = I('get.file_id', 0, 'intval');
        if (empty($fileId)) {
            $this->_exit();
        }

        // 未找到任务，退出
        $taskServ = new TaskService();
        $task = $taskServ->get_by_conds(['file_id' => $fileId]);
        if (empty($task)) {
            Log::record("not found task, file_id: {$fileId}", Log::INFO);
            $this->_exit();
        }

        // 未找到文件，删除计划任务
        $fileServ = new FileService();
        $file = $fileServ->get($fileId);
        if (empty($file)) {
            Log::record("not found file_id: {$fileId}", Log::INFO);
            $this->_delTask($fileId, $task['cron_id']);
        }

        // 转码是否成功
        $convertServ = new FileConvert(Service::instance());
        $result = $convertServ->get($file['at_id']);
        if (is_array($result) && isset($result['caAttachment']) && $result['caConvertStatus'] == FileConvert::CONVERT_STATUS_SUCCESS) {
            // 转码成功，更新文件状态、转码后url
            $at_convert_url = $result['caAttachment'];
            $fileServ->update($fileId, [
                'file_status' => Constant::FILE_STATUS_NORMAL,
                'at_convert_url' => $at_convert_url,
            ]);
            Log::record("convert success, file_id: {$fileId}, at_convert_url: {$at_convert_url}", Log::INFO);

            // 结束计划任务
            $this->_delTask($fileId, $task['cron_id']);
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
        Log::record(sprintf('---%s %s CheckFile END---', QY_DOMAIN, APP_IDENTIFIER), Log::INFO);
        exit('SUCCESS');
    }

    /**
     * 删除计划任务并退出
     * @author liyifei
     * @param int $file_id 文件ID
     * @param string $cron_id UC计划任务ID
     * @return void
     */
    private function _delTask($file_id, $cron_id)
    {
        $taskServ = new TaskService();
        $taskServ->delTask($file_id, $cron_id);

        Log::record("delete task: {$cron_id}", Log::INFO);
        $this->_exit();
    }
}
