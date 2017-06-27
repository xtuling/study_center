<?php
/**
 * 删除评论接口
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-05-09 14:11:22
 * @version $Id$
 */

namespace Apicp\Controller\Comment;

use Common\Service\CommentService;

class DeleteController extends AbstractController
{

    public function Index_post()
    {

        $comment_service = new CommentService();

        $comment_id = I('post.comment_id', 0, 'intval');

        if (empty($comment_id)) {

            E('_ERR_COMMENT_ID_EMPTY');
            return false;
        }

        if (!$comment_service->del_comment($comment_id)) {

            return false;
        }

        return true;
    }

}
