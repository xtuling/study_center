<?php
/**
 * 查询顶级评论列表接口
 * 鲜彤 2016-08-05 15:40:23
 */
namespace Api\Controller\Comment;

use Common\Common\Comment;

class ListController extends AbstractController
{

    public function Index()
    {

        // 评论对象ID
        $cmtObjid = I('post.cmtObjid');
        // 子评论的页大小 默认5 最大1000
        $childPageSize = I('post.childPageSize');
        $page = I('post.page'); // 当前页码
        $perpage = I('post.limit'); // 页大小 默认30 最大200

        // 评论对象id不能为空
        if (! $cmtObjid) {
            $this->_set_error("_ERR_EMPTY_CMTOBJ_ID");
            return false;
        }

        // 组织查询条件
        $condition = array(
            "cmtObjid" => $cmtObjid,
            "childPageSize" => $childPageSize,
            "memUid" => $this->_login->user['memUid']
        );

        // 搜索评论列表
        $result = Comment::instance()->listAll($condition, '', $page, $perpage);

        // 循环列表，添加是否允许删除
        foreach ($result['list'] as &$_comment) {
            $_comment['allow_delete'] = $_comment['memUid'] == $this->_login->user['memUid'] ? 1 : 0;
            foreach ($_comment['childComentList'] as &$_childComment) {
                $_childComment['allow_delete'] = $_childComment['memUid'] == $this->_login->user['memUid'] ? 1 : 0;
            }
        }

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
