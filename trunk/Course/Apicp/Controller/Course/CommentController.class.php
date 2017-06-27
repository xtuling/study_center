<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/27
 * Time: 11:41
 */
namespace Apicp\Controller\Course;

use Com\PackageValidate;
use Common\Common\Comment;
use Common\Service\ArticleService;

class CommentController extends \Apicp\Controller\AbstractController
{
    /**
     * Comment
     * @author tangxingguo
     * @desc 同步评论总数
     * @param int data_id:true 数据标识
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'data_id' => 'require',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 课程详情
        $articleServ = new ArticleService();
        $article = $articleServ->get_by_conds(['data_id' => $postData['data_id']]);
        if (empty($article)) {
            E('_ERR_ARTICLE_NOT_FOUND');
        }

        // UC获取当前评论总数
        $commentServ = &Comment::instance();
        $commentList = $commentServ->listAll(['cmtObjid' => $postData['data_id']]);

        // 修改课程主表数据
        if (isset($commentList['cmttlNums'])) {
            $total = $commentList['cmttlNums'];
            $articleServ->update_by_conds(['data_id' => $postData['data_id']], ['comment_total' => $total]);
        }
    }
}
