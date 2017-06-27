<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/4/11
 * Time: 19:12
 */
namespace Apicp\Controller\News;

use Com\PackageValidate;
use Common\Common\Constant;
use Common\Common\NewsHelper;
use Common\Service\ArticleService;

class UpdateUnreadController extends \Apicp\Controller\AbstractController
{
    /**
     * UpdateUnread
     * @author zhonglei
     * @desc 更新未读总数
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
        $list = $artServ->list_by_conds([
            'article_id' => $article_ids,
            'update_time < ?' => MILLI_TIME - Constant::NEWS_UNREAD_TIME,
        ]);

        if (empty($list)) {
            return true;
        }

        $newsHelper = &NewsHelper::instance();

        foreach ($list as $article) {
            list($uids_all, $uids_read, $uids_unread) = $newsHelper->getReadData($article['article_id']);
            $unread_total = count($uids_unread);
            if ($article['unread_total'] != $unread_total) {
                $artServ->update($article['article_id'], [
                    'unread_total' => $unread_total,
                    'update_time' => MILLI_TIME,
                ]);
            }
        }
    }
}
