<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/4/11
 * Time: 18:20
 */
namespace Apicp\Controller\News;

use Com\PackageValidate;
use Common\Common\Constant;
use Common\Common\NewsHelper;
use Common\Service\ClassService;
use Common\Service\ArticleService;

class RemindController extends \Apicp\Controller\AbstractController
{
    /**
     * Remind
     * @author liyifei
     * @desc 未读提醒
     * @param Int article_id:true 新闻ID
     * @return array
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'article_id' => 'require|integer',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 获取新闻详情
        $articleServ = new ArticleService();
        $article = $articleServ->get($postData['article_id']);

        if (empty($article)) {
            E('_ERR_ARTICLE_NOT_FOUND');
        }

        // 草稿和预发布不允许发送提醒
        if (in_array($article['news_status'], [Constant::NEWS_STATUS_DRAFT, Constant::NEWS_STATUS_READY_SEND])) {
            E('_ERR_ARTICLE_STATUS_FAIL');
        }

        // 获取顶级分类名称
        $classServ = new ClassService();
        $class = $classServ->getTopClass($article['class_id']);
        $article['class_name'] = $class['class_name'];

        // 获取未读人员列表
        $newsHelper = &NewsHelper::instance();
        list($uids_all, $uids_read, $uids_unread) = $newsHelper->getReadData($postData['article_id']);

        // 发送未读提醒
        $newsHelper->sendUnreadMsg($uids_unread, $article);
    }
}
