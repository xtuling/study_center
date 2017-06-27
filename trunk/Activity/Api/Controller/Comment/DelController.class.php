<?php
/**
 * 删除我的评论接口
 * User: daijun
 * Date: 2017-05-08
 */

namespace Api\Controller\Comment;

use Common\Service\CommentService;

class DelController extends AbstractController
{

    /**
     * @var CommentService 评论回复信息表
     */
    protected $_comment_serv;

    public function before_action($action = '')
    {

        if (!parent::before_action($action)) {
            return false;
        }

        $this->_comment_serv = new CommentService();

        return true;
    }


    public function Index_post()
    {
        // 获取参数
        $comment_id = I('post.comment_id', 0, 'intval');

        // 验证参数
        if (empty($comment_id)) {
            E('_EMPTY_COMMENT_ID');
            return false;
        }

        // 执行删除
        if (!$this->_comment_serv->del_comment($comment_id, $this->uid)) {
            E('_ERR_DEL_FAIL');
            return false;
        }

        return true;
    }
}
