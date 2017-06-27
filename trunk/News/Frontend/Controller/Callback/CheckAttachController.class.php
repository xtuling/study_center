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
use Common\Common\NewsHelper;
use Common\Service\ArticleService;
use Common\Service\AttachService;
use Common\Service\TaskService;
use Common\Service\ClassService;

class CheckAttachController extends AbstractController
{
    /**
     * 附件转换计划任务
     * @author zhonglei
     */
    public function Index()
    {
        Log::record(sprintf('---%s %s CheckAttach START---', QY_DOMAIN, APP_IDENTIFIER), Log::INFO);

        $article_id = I('get.article_id', 0, 'intval');
        if (empty($article_id)) {
            $this->_exit();
        }

        $taskServ = new TaskService();
        $task = $taskServ->get_by_conds(['article_id' => $article_id]);

        // 未找到任务，退出
        if (empty($task)) {
            Log::record("not found task, article_id: {$article_id}", Log::INFO);
            $this->_exit();
        }

        $cron_id = $task['cron_id'];
        $articleServ = new ArticleService();
        $article = $articleServ->get($article_id);

        // 未找到新闻，删除计划任务
        if (empty($article)) {
            Log::record("not found article, article_id: {$article_id}", Log::INFO);
            $this->_delTask($article_id, $cron_id);
        }

        // 获取附件
        $attachServ = new AttachService();
        $list = $attachServ->list_by_conds(['article_id' => $article_id]);
        $attachs = $attachServ->formatDBData($list);

        // 未找到附件，删除计划任务
        if (empty($attachs)) {
            Log::record("not found attach, article_id: {$article_id}", Log::INFO);
            $this->_delTask($article_id, $cron_id);
        }

        $convertServ = new FileConvert(Service::instance());
        $stop_task = true;

        foreach ($attachs as $k => $data) {
            // 仅处理视频与文件
            if (!in_array($k, [Constant::ATTACH_TYPE_VIDEO, Constant::ATTACH_TYPE_FILE])) {
                continue;
            }

            foreach ($data as $attach) {
                // 已完成
                if (!empty($attach['at_convert_url'])) {
                    continue;
                }

                $at_convert_url = '';

                switch ($k) {
                    // 视频
                    case Constant::ATTACH_TYPE_VIDEO:
                        $result = $convertServ->getVodPlayUrl($attach['at_id']);
                        if (is_array($result) && isset($result['url'])) {
                            $at_convert_url = str_replace('http://', '//', $result['url']);
                            $at_convert_url = str_replace('https://', '//', $at_convert_url);
                        }
                        break;

                    // 文件
                    case Constant::ATTACH_TYPE_FILE:
                        $result = $convertServ->get($attach['at_id']);

                        if (is_array($result) && isset($result['caAttachment']) &&
                            $result['caConvertStatus'] == FileConvert::CONVERT_STATUS_SUCCESS) {
                            $at_convert_url = $result['caAttachment'];
                        }
                        break;
                }

                if (empty($at_convert_url)) {
                    $stop_task = false;
                } else {
                    $attachServ->update($attach['attach_id'], ['at_convert_url' => $at_convert_url]);
                    Log::record("convert success, attach_id: {$attach['attach_id']}, at_convert_url: {$at_convert_url}", Log::INFO);
                }
            }
        }

        // 结束任务
        if ($stop_task) {
            // 发布新闻
            if ($article['news_status'] == Constant::NEWS_STATUS_READY_SEND) {
                $articleServ->update($article_id, ['news_status' => Constant::NEWS_STATUS_SEND]);
                Log::record("update article news_status, article_id: {$article_id}", Log::INFO);

                // 获取顶级分类名称
                $classServ = new ClassService();
                $class = $classServ->getTopClass($article['class_id']);
                $article['class_name'] = $class['class_name'];

                // 发送未读提醒
                $newsHelper = &NewsHelper::instance();
                list($uids_all, $uids_read, $uids_unread) = $newsHelper->getReadData($article_id);
                $newsHelper->sendUnreadMsg($uids_unread, $article);

                // RPC推送到运营中心
                $articleServ->addNewsRpc($article_id);
            }

            $this->_delTask($article_id, $cron_id);
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
     * @param int $article_id 新闻ID
     * @param string $cron_id 计划任务ID
     * @return void
     */
    private function _delTask($article_id, $cron_id)
    {
        $taskServ = new TaskService();
        $taskServ->delete_by_conds([
            'article_id' => $article_id,
            'cron_id' => $cron_id,
        ]);

        $cronSdk = new Cron(Service::instance());
        $cronSdk->delete($cron_id);

        Log::record("delete task: {$cron_id}", Log::INFO);
        $this->_exit();
    }
}
