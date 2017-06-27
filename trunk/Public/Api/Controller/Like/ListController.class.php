<?php
/**
 * 点赞列表接口
 * 宋双峰  2016-09-05 17:39:49
 */

namespace Api\Controller\Like;

use Common\Common\Comment;

class ListController extends AbstractController
{

    public function Index()
    {

        // 评论对象ID
        $cmtObjid = I('post.cmtObjid');
        // 当前页码
        $page = I('post.page');
        // 页大小 默认30 最大200
        $limit = I('post.limit', 30);

        // 评论对象id不能为空
        if (! $cmtObjid) {
            $this->_set_error("_ERR_EMPTY_CMTOBJ_ID");
            return false;
        }

        // 组织查询条件
        $condition = array(
            "cmtObjid" => $cmtObjid,
            "memUid" => $this->_login->user['memUid']
        );

        // 实例化sdk，搜索评论列表
        $result = Comment::instance()->listLike($condition, $page, $limit);

        // 返回数据
        $this->_result = array(
            "page" => $result['pageNum'],
            "limit" => $result['pageSize'],
            "total" => $result['total'],
            "memUidCmtObjLikeState" => $result['memUidCmtObjLikeState'],
            "list" => $result['list']
        );

        return true;
    }
}
