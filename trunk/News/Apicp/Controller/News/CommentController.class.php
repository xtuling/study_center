<?php
namespace Apicp\Controller\News;

use Common\Common\Comment;
use Common\Service\ArticleService;

class CommentController extends \Apicp\Controller\AbstractController
{

    /**

     * DeleteComment
     *
     * @author tangxingguo

     * @desc 删除新闻

     * @param int data_id:true 数据标识

     * @return null

     */
    public function Index_post()
    {
        $data_id = I('post.data_id', 0, 'trim');

        // 数据标识不能为空
        if (empty($data_id)) {
            E('_ERR_ARTICLE_DATA_ID_NOT_FOUND');
        }

        $articleServ = new ArticleService();
        $article = $articleServ->get_by_conds(['data_id' => $data_id]);
        if (empty($article)) {
            E('_ERR_ARTICLE_NOT_FOUND');
        }

        // UC获取当前评论总数
        $commentServ = &Comment::instance();
        $commentList = $commentServ->listAll(['cmtObjid' => $data_id]);

        // 修改课程主表数据
        if (isset($commentList['cmttlNums'])) {
            $total = $commentList['cmttlNums'];
            $articleServ->update_by_conds(['data_id' => $data_id], ['comment_total' => $total]);
        }
    }
}
