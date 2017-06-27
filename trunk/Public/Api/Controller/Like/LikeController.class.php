<?php
/**
 * 点赞接口
 * 鲜彤 2016-08-05 15:44:45
 */
namespace Api\Controller\Like;

use VcySDK\Comment;

class LikeController extends AbstractController
{

    public function Index()
    {

        // 评论id
        $cmtId = I('post.cmtId');
        // 评论对象id
        $cmtObjid = I('post.cmtObjid');
        // 点赞类型
        $likeType = I('post.likeType');

        // 点赞类型不能为空
        if (empty($likeType)) {
            $this->_set_error("_ERR_LIKE_TYPE_EMPTY");
            return false;
        }

        // 点赞类型为2时评论id不能为空
        if ($likeType == Comment::LIKE_TYPE_COMMENT && empty($cmtId)) {
            $this->_set_error("_ERR_CMTID_EMPTY_LIKE");
            return false;
        }

        // 评论对象id不能为空
        if (empty($cmtObjid)) {
            $this->_set_error("_ERR_EMPTY_CMTOBJ_ID");
            return false;
        }

        // 调用UC接口
        $like = array(
            "cmtId" => $cmtId,
            'cmtObjid' => $cmtObjid,
            "memUid" => $this->_login->user['memUid'],
            "likeType" => $likeType
        );

        // 点赞操作
        \Common\Common\Comment::instance()->like($like);

        return true;
    }

}
