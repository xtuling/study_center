<?php
/**
 * 点赞列表接口
 * 宋双峰 2016-09-05 17:44:45
 */

namespace Apicp\Controller\Like;

use Common\Common\Comment;

class ListController extends AbstractController
{

    public function Index()
    {

        // 接收数据
        $cmtObjid = I('post.cmtObjid'); // 评论对象ID
        $page = I('post.page'); // 当前页码
        $limit = I('post.limit'); // 页大小 默认30 最大200

        // 评论对象id不能为空
        if (! $cmtObjid) {
            $this->_set_error("_ERR_EMPTY_CMTOBJ_ID");
            return false;
        }

        // 组织查询条件
        $condition = array(
            "cmtObjid" => $cmtObjid
        );

        // 实例化sdk，搜索评论列表
        $result = Comment::instance()->listLike($condition, $page, $limit);
        // 返回数据
        $this->_result = array(
            "page" => $result['pageNum'],
            "limit" => $result['pageSize'],
            "total" => $result['total'],
            "list" => $result['list']
        );

        return true;
    }

}
