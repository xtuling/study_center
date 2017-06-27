<?php
/**
 * 取消点赞接口
 * 何岳龙 2016年9月1日10:11:33
 */
namespace Api\Controller\Like;

use VcySDK\Comment;
use VcySDK\Service;

class CancelLikeController extends AbstractController
{

    public function Index()
    {

        // 评论id
        $cmtId = I('post.cmtId');
        // 评论对象id
        $cmtObjid = I('post.cmtObjid');
        // 取消点赞类型
        $cancelLikeType = I('post.cancelLikeType');

        // 点赞类型不能为空
        if (empty($cancelLikeType)) {
            $this->_set_error("_ERR_LIKE_TYPE_EMPTY");
            return false;
        }

        // 评论对象id不能为空
        if (empty($cmtObjid)) {
            $this->_set_error("_ERR_EMPTY_CMTOBJ_ID");
            return false;
        }

        // 点赞类型为2时评论id不能为空
        if ($cancelLikeType == Comment::LIKE_TYPE_COMMENT) {
            if (empty($cmtId)) {
                $this->_set_error("_ERR_CMTID_EMPTY_LIKE");
                return false;
            }

            // 调用UC接口
            $like = array(
                "cmtId" => $cmtId, // 评论id
                'cmtObjid' => $cmtObjid,
                "memUid" => $this->_login->user['memUid'], // 用户uid
                "cancelLikeType" => $cancelLikeType // 取消点赞类型
            );
        } else {
            // 调用UC接口
            $like = array(
                'cmtObjid' => $cmtObjid,
                "memUid" => $this->_login->user['memUid'], // 用户uid
                "cancelLikeType" => $cancelLikeType // 取消点赞类型
            );
        }

        // 取消点赞操作
        \Common\Common\Comment::instance()->cancelLike($like);

        return true;
    }

}
