<?php
/**
 * 评论列表接口
 * 原习斌
 * 2016-08-18
 */
namespace Apicp\Controller\Comment;

use Common\Common\Comment;

class ListController extends AbstractController
{

    public function Index()
    {

        // 接收数据
        $cmtObjid = I('post.cmtObjid'); // 评论对象ID 注：此处为由前端接收的'评论对象id'，发送UC时需要进行处理
        $childPageSize = I('post.childPageSize'); // 子评论的页大小 默认5 最大1000
        $page = I('post.page'); // 当前页码
        $perpage = I('post.limit'); // 页大小  最大200

        // 评论对象id不能为空
        if (! $cmtObjid) {
            $this->_set_error("_ERR_EMPTY_CMTOBJ_ID");
            return false;
        }

        // 组织查询条件
        $condition = array(
            "cmtObjid" => $cmtObjid,
            "childPageSize" => $childPageSize
        );

        // 实例化sdk，搜索评论列表
        $result = Comment::instance()->listAll($condition, '', $page, $perpage);

        // 返回数据
        $this->_result = array(
            "page" => $result['pageNum'],
            "limit" => $result['pageSize'],
            "total" => $result['total'],
            "cmttlNums" => $result['cmttlNums'],
            "cmttlLikes" => $result['cmttlLikes'],
            "cmttlObjLikes" => $result['cmttlObjLikes'],
            "memUidCmtObjLikeState" => $result['memUidCmtObjLikeState'],
            "memUidLikeList" => $result['memUidLikeList'],
            "childPageNum" => $result['childPageNum'],
            "childPageSize" => $result['childPageSize'],
            "childTotal" => $result['childTotal'],
            "list" => $result['list']
        );

        return true;
    }

}

