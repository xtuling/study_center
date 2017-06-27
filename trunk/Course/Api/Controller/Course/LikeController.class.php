<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/5/5
 * Time: 10:23
 */
namespace Api\Controller\Course;

use Com\PackageValidate;
use Common\Common\Constant;
use Common\Service\LikeService;
use Common\Service\ArticleService;

class LikeController extends \Api\Controller\AbstractController
{
    /**
     * Like
     * @author liyifei
     * @desc 点赞、取消点赞接口
     * @param Int article_id:true 课程ID
     * @param Int type:true 操作类型（1=取消收藏，2=收藏）
     * @return array
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'article_id' => 'require|integer',
            'type' => 'require|integer|between:1,2',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 课程详情
        $articleServ = new ArticleService();
        $article = $articleServ->get($postData['article_id']);
        if (empty($article)) {
            E('_ERR_ARTICLE_NOT_FOUND');
        }

        // 登录人员
        $user = $this->_login->user;

        // 点赞
        $likeServ = new LikeService();
        if ($postData['type'] == Constant::LIKE_TYPE_ADD) {
            $like = $likeServ->get_by_conds([
                'uid' => $user['memUid'],
                'article_id' => $postData['article_id'],
            ]);
            if ($like) {
                E('_ERR_ARTICLE_ALREADY_LIKE');
            }
            // 写入数据
            $data = [
                'uid' => $user['memUid'],
                'username' => $user['memUsername'],
                'article_id' => $postData['article_id'],
            ];
            $likeServ->insert($data);

            // 更新主表数据
            $upData = ['like_total = like_total + ?' => 1];
        }

        // 取消点赞
        if ($postData['type'] == Constant::LIKE_TYPE_DELETE) {
            $likeServ->delete_by_conds([
                'uid' => $user['memUid'],
                'article_id' => $postData['article_id'],
            ]);

            // 更新主表数据
            $upData = ['like_total = like_total - ?' => 1];
        }

        // 更新课程主表点赞总数
        if (isset($upData)) {
            $articleServ->update($postData['article_id'], $upData);
        }
    }
}
