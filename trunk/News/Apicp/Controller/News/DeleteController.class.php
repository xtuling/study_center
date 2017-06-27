<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/4/11
 * Time: 18:24
 */
namespace Apicp\Controller\News;

use Com\PackageValidate;
use Common\Common\RpcFavoriteHelper;
use Common\Service\ArticleService;
use Common\Service\TaskService;
use VcySDK\Service;
use VcySDK\Cron;

class DeleteController extends \Apicp\Controller\AbstractController
{
    /**
     * Delete
     * @author zhonglei
     * @desc 删除新闻
     * @param Array article_ids:true 新闻ID数组
     * @return mixed
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'article_ids' => 'require|array',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;
        $article_ids = $postData['article_ids'];

        $artServ = new ArticleService();
        $list = $artServ->list_by_conds(['article_id' => $article_ids]);

        if (empty($list)) {
            return true;
        }

        // 删除首页推送
        foreach ($article_ids as $article_id) {
            $artServ->delNewsRpc($article_id);
        }

        // 删除新闻
        $artServ->delete_by_conds(['article_id' => $article_ids]);

        // 删除应用数据时，RPC同步收藏状态
        $rpcFavorite = &RpcFavoriteHelper::instance();
        $rpcFavorite->updateStatus($postData['article_ids']);

        $taskServ = new TaskService();
        $task_list = $taskServ->list_by_conds(['article_id' => $article_ids]);

        if (empty($task_list)) {
            return true;
        }

        $cron_ids = array_filter(array_column($task_list, 'cron_id'));
        $cronSdk = new Cron(Service::instance());

        // 删除计划任务
        foreach ($cron_ids as $cron_id) {
            $cronSdk->delete($cron_id);
        }
    }
}
