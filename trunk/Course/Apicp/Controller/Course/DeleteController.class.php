<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/27
 * Time: 10:59
 */
namespace Apicp\Controller\Course;

use Common\Service\ArticleService;
use Common\Service\RightService;
use Common\Service\LikeService;
use Common\Common\RpcFavoriteHelper;

class DeleteController extends \Apicp\Controller\AbstractController
{
    /**
     * Delete
     * @author liyifei
     * @desc 删除课程
     * @param Array article_ids:true 课程ID数组
     */
    public function Index_post()
    {
        $article_ids = I('post.article_ids');
        if (empty($article_ids) || !is_array($article_ids)) {
            E('_ERR_SOURCE_PARAM_FORMAT');
        }

        // 删除运营中心首页推送
        $articleServ = new ArticleService();
        foreach ($article_ids as $article_id) {
            $articleServ->delCourseRpc($article_id);
        }

        // 删除课程
        $articleServ->delete_by_conds(['article_id' => $article_ids]);

        // 删除权限
        $rightServ = new RightService();
        $rightServ->delete_by_conds(['article_id' => $article_ids]);

        // 删除应用数据时，RPC同步收藏状态
        $rpcFavorite = &RpcFavoriteHelper::instance();
        $rpcFavorite->updateStatus($article_ids);

        // 删除点赞
        $likeServ = new LikeService();
        $likeServ->delete_by_conds(['article_id' => $article_ids]);
    }
}
