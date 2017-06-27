<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/4/27
 * Time: 14:50
 */
namespace Apicp\Controller\Source;

use Common\Common\Constant;
use Common\Service\SourceAttachService;
use VcySDK\Service;
use VcySDK\Cron;
use Common\Common\Attach;
use Common\Service\TaskService;
use Common\Service\SourceService;
use Common\Service\ArticleSourceService;

class DeleteController extends \Apicp\Controller\AbstractController
{
    /**
     * Delete
     * @author liyifei
     * @desc 素材删除接口(支持单、多个删除)
     * @param Array source_ids:true 素材ID集合
     * @return array // 单个删除时，返回该素材被使用的课程ID；批量删除时，返回不可删除的素材ID；
     */
    public function Index_post()
    {
        $source_ids = I('post.source_ids');
        if (empty($source_ids) || !is_array($source_ids)) {
            E('_ERR_SOURCE_PARAM_FORMAT');
        }

        // 初始化返回值
        $data = [];

        // 素材是否被使用
        $asServ = new ArticleSourceService();
        $asList = $asServ->list_by_conds(['source_id' => $source_ids]);
        if (empty($asList)) {
            // 删除素材
            $sourceServ = new SourceService();
            $sourceServ->delete_by_conds(['source_id' => $source_ids]);

            // 删除定时任务
            $taskServ = new TaskService();
            $task_list = $taskServ->list_by_conds(['source_id' => $source_ids]);
            if (!empty($task_list)) {
                // 删除UC计划任务
                $cron_ids = array_filter(array_column($task_list, 'cron_id'));
                $cronSdk = new Cron(Service::instance());
                foreach ($cron_ids as $cron_id) {
                    $cronSdk->delete($cron_id);
                }

                // 删除本地计划任务记录
                $taskServ->delete_by_conds(['cron_id' => $cron_ids]);
            }

        } else {
            if (count($source_ids) == 1) {
                // 单个删除时，返回使用该素材的课程ID列表
                $data = array_column($asList, 'article_id');

            } else {
                // 批量删除时，返回被使用的素材ID
                $data = array_unique(array_column($asList, 'source_id'));
            }
        }

        // 删除UC服务器文件
        $saServ = new SourceAttachService();
        $atList = $saServ->list_by_conds(['source_id' => $source_ids, 'at_type' => Constant::ATTACH_TYPE_FILE]);
        if (!empty($atList)) {
            $atIds = array_column($atList, 'at_id');
            $attachServ = &Attach::instance();
            $attachServ->deleteFile($atIds);
        }

        $this->_result = [
            'list' => array_values($data),
        ];
    }
}
