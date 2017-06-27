<?php
/**
 * 获取评论列表接口
 * @author: houyingcai
 * @email:    594609175@qq.com
 * @date :  2017-05-09 09:52:15
 * @version $Id$
 */

namespace Apicp\Controller\Comment;

use Common\Service\CommentService;

class ListController extends AbstractController
{

    public function Index_post()
    {

        $comment_service = new CommentService();

        if (!$comment_service->comment_list($this->_result, I('post.'))) {

            return false;
        }

        return true;
    }
}
