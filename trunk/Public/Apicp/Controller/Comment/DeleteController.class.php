<?php
/**
 * 评论删除接口
 * 原习斌
 * 2016-08-18
 */
namespace Apicp\Controller\Comment;

use Common\Common\Comment;

class DeleteController extends AbstractController
{

    public function Index()
    {

        $cmtId = I('post.cmtId');
        $deleteLevel = I('post.deleteLevel');

        // 评论ID不能为空
        if (empty($cmtId)) {
            $this->_set_error('_ERR_EMPTY_CMTID');
            return false;
        }

        // 评论等级不能为空
        if (empty($deleteLevel)) {
            $this->_set_error('_ERR_EMPTY_DELETELEVEL');
            return false;
        }

        // 删除评论
        $condition = array(
            'cmtId' => $cmtId,
            'deleteLevel' => $deleteLevel
        );
        Comment::instance()->delete($condition);

        return true;
    }

}
