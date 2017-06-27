<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/18
 * Time: 17:44
 */
namespace Api\Controller\News;

use Com\PackageValidate;
use Common\Service\ArticleService;
use Common\Service\LikeService;

class CancelLikeController extends \Api\Controller\AbstractController
{
    /**
     * CancelLike
     * @author tangxingguo
     * @desc 取消点赞
     * @param int    article_id:true 新闻公告ID
     */
    public function Index_post()
    {
        $user = $this->_login->user;

        // 验证规则
        $rules = [
            'article_id' => 'require|integer',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 新闻检查
        $articleServ = new ArticleService();
        $newsInfo = $articleServ->get($postData['article_id']);
        if (empty($newsInfo)) {
            E('_ERR_ARTICLE_NOT_FOUND');
        }

        // 数据库操作
        $likeServ = new LikeService();
        $deleteCount = $likeServ->delete_by_conds(['article_id' => $postData['article_id'], 'uid' => $user['memUid']]);
        if ($deleteCount == 1) {
            $articleServ->update($postData['article_id'], ['like_total = like_total - ?' => 1]);
        }
    }
}
