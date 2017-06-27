<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/11
 * Time: 15:29
 */
namespace Api\Controller\News;

use Com\PackageValidate;
use Common\Service\ArticleService;
use Common\Service\LikeService;

class LikeController extends \Api\Controller\AbstractController
{
   /**
    * Like
    * @author tangxingguo
    * @desc 点赞
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
        $likeCount = $likeServ->count_by_conds(['article_id' => $postData['article_id'], 'uid' => $user['memUid']]);
        if ($likeCount) {
            // 已经点过赞
            E('_ERR_ARTICLE_ALREADY_LIKE');
        }
        $data = [
            'article_id' => $postData['article_id'],
            'uid' => $user['memUid'],
            'username' => $user['memUsername'],
        ];
        $likeServ->insert($data);
        $articleServ->update($postData['article_id'], ['like_total = like_total + ?' => 1]);
    }
}
