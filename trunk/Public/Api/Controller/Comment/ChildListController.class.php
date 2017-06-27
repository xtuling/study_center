<?php
/**
 * 查询子评论列表接口
 * 鲜彤 2016年09月02日
 */
namespace Api\Controller\Comment;

use Common\Common\Comment;

class ChildListController extends AbstractController
{

    public function Index()
    {

        $postData = I('post.');

        // 评论对象id不能为空
        if (! $postData['cmtObjid']) {
            $this->_set_error("_ERR_EMPTY_CMTOBJ_ID");
            return false;
        }

        // 组织查询条件
        $condition = array(
            "rootId" => $postData['rootId'],
            "cmtObjid" => $postData['cmtObjid']
        );

        // 搜索评论列表
        $result = Comment::instance()->listChild($condition, $postData['page'], $postData['limit']);

        // 循环列表，添加是否允许删除
        foreach ($result['list'] as &$_comment) {
            $_comment['allow_delete'] = $_comment['memUid'] == $this->_login->user['memUid'] ? 1 : 0;
        }

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
