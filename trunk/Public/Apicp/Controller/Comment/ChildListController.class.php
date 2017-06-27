<?php
/**
 * 查询子评论列表接口
 * 鲜彤 2016年09月02日
 */
namespace Apicp\Controller\Comment;

use Common\Common\Comment;

class ChildListController extends AbstractController
{

    public function Index()
    {

        // 接收数据
        $rootId = I('post.rootId'); // 顶级评论id
        $cmtObjid = I('post.cmtObjid'); // 评论对象ID 注：此处为由前端接收的'评论对象id'，发送UC时需要进行处理
        $page = I('post.page'); // 当前页码
        $perpage = I('post.limit'); // 每页条数

        // 评论对象id不能为空
        if (! $cmtObjid) {
            $this->_set_error("_ERR_EMPTY_CMTOBJ_ID");
            return false;
        }

        // 组织查询条件
        $condition = array(
            "rootId" => $rootId,
            "cmtObjid" => $cmtObjid
        );

        // 实例化sdk，搜索评论列表
        $result = Comment::instance()->listChild($condition, $page, $perpage);

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

