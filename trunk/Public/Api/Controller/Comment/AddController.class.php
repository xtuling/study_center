<?php
/**
 * 添加评论接口
 * $author$ 何岳龙
 * $date$ 2016年8月8日11:43:33
 */
namespace Api\Controller\Comment;

use Common\Common\Comment;

class AddController extends AbstractController
{

    public function Index()
    {

        $postData = I('post.');

        // parentId不为空时，rootId不能为空
        if (! empty($postData['parentId']) && empty($postData['rootId'])) {
            $this->_set_error('_ERR_EMPTY_ROOTID');
            return false;
        }

        // 评论内容不能为空
        if (empty($postData['cmtContent'])) {
            $this->_set_error('_ERR_EMPTY_CMTCONTENT');
            return false;
        }


        // 评论对象ID不能为空
        if (empty($postData['cmtObjid'])) {
            $this->_set_error('_ERR_EMPTY_CMTOBJ_ID');
            return false;
        }

        // 评论信息入库
        $comment = array(
            'memUid' => $this->_login->user['memUid'],
            'cmtContent' => $postData['cmtContent'],
            'rootId' => $postData['rootId'],
            'parentId' => $postData['parentId'],
            'cmtObjid' => $postData['cmtObjid'],
            'cmtAttachids' => $postData['cmtAttachids']
        );
        $result = Comment::instance()->add($comment);

        // 添加评论失败
        if (empty($result['epId'])) {
            $this->_set_error('_ERR_ADD_COMMENT');
            return false;
        }

        $this->_result = $result;
        return true;
    }

}
