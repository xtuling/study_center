<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/18
 * Time: 17:11
 */

namespace Api\Controller\News;

use Com\PackageValidate;
use Common\Common\Comment;
use Common\Common\Constant;
use Common\Service\ArticleService;

class CommentController extends \Api\Controller\AbstractController
{
    /**
     * Comment
     * @author tangxingguo
     * @desc 评论总数同步
     * @param string data_id:true 数据标识
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'data_id' => 'require',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $data_id = $validate->postData['data_id'];

        // 新闻检查
        $articleServ = new ArticleService();
        $article = $articleServ->get_by_conds(['data_id' => $data_id]);
        if (empty($article)) {
            E('_ERR_ARTICLE_NOT_FOUND');
        }

        // UC获取当前评论总数
        $commentServ = &Comment::instance();
        $commentList = $commentServ->listAll(['cmtObjid' => $data_id]);

        // 操作数据库
        if (!empty($commentList)) {
            $total = $commentList['cmttlNums'];
            $articleServ->update_by_conds(['data_id' => $data_id], ['comment_total' => $total]);
        }
    }
}
