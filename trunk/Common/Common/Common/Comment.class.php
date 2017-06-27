<?php
/**
 * 评论操作类
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 16/10/26
 * Time: 下午4:01
 */

namespace Common\Common;

use VcySDK\Service;
use VcySDK\Comment as CommentSDK;

class Comment
{

    /**
     * SDK Comment
     *
     * @type \VcySDK\Comment
     */
    protected $_servComment = null;

    /**
     * 构造方法
     */
    public function __construct()
    {

        $serv = &Service::instance();
        // 类名称冲突，使用完全限定名称调用
        $this->_servComment = new CommentSDK($serv);
    }

    /**
     * 实例化
     *
     * @return Comment
     */
    public static function &instance()
    {

        static $instance;
        if (empty($instance)) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * 根据给定的评论ID重新生成新的ID
     *
     * @param string $id 评论对象ID
     *
     * @return string
     */
    protected function _rewriteObiId($id)
    {

        return md5(QY_DOMAIN . APP_IDENTIFIER . $id);
    }

    /**
     * 新增评论信息
     *
     * @param array $comment      评论信息
     * @param bool  $rewriteObjId 是否重写评论对象ID
     *
     * @return bool|mixed
     */
    public function add($comment, $rewriteObjId = true)
    {

        if ($rewriteObjId && !empty($comment['cmtObjid'])) {
            $comment['cmtObjid'] = $this->_rewriteObiId($comment['cmtObjid']);
        }

        return $this->_servComment->add($comment);
    }

    /**
     * 读取评论列表
     *
     * @param array $condition    查询条件
     * @param int   $page         当前页码
     * @param int   $perpage      每页记录数
     * @param bool  $rewriteObjId 是否重写评论对象ID
     *
     * @return array|bool
     */
    public function listChild($condition, $page, $perpage, $rewriteObjId = true)
    {

        if ($rewriteObjId && !empty($condition['cmtObjid'])) {
            $condition['cmtObjid'] = $this->_rewriteObiId($condition['cmtObjid']);
        }

        return $this->_servComment->childListAll($condition, $page, $perpage);
    }

    /**
     * 查询评论列表
     *
     * @param array $condition    查询条件
     * @param array $orders       排序条件
     * @param int   $page         当前页码
     * @param int   $perpage      每页记录数
     * @param bool  $rewriteObjId 是否重写评论对象ID
     *
     * @return bool|mixed
     */
    public function listAll($condition, $orders = array(), $page = 1, $perpage = 30, $rewriteObjId = true)
    {

        if ($rewriteObjId && !empty($condition['cmtObjid'])) {
            $condition['cmtObjid'] = $this->_rewriteObiId($condition['cmtObjid']);
        }

        return $this->_servComment->listAll($condition, $orders, $page, $perpage);
    }

    /**
     * 取消点赞
     *
     * @param array $like         点赞信息
     * @param bool  $rewriteObjId 是否重写评论对象ID
     *
     * @return bool|mixed
     */
    public function cancelLike($like, $rewriteObjId = true)
    {

        if ($rewriteObjId && !empty($like['cmtObjid'])) {
            $like['cmtObjid'] = $this->_rewriteObiId($like['cmtObjid']);
        }

        return $this->_servComment->cancelLike($like);
    }

    /**
     * 点赞操作
     *
     * @param array $like         点赞信息
     * @param bool  $rewriteObjId 是否重写评论对象ID
     *
     * @return bool|mixed
     */
    public function like($like, $rewriteObjId = true)
    {

        if ($rewriteObjId && !empty($like['cmtObjid'])) {
            $like['cmtObjid'] = $this->_rewriteObiId($like['cmtObjid']);
        }

        return $this->_servComment->like($like);
    }

    /**
     * 查询点赞列表
     *
     * @param array $condition    查询条件
     * @param int   $page         当前页码
     * @param int   $perpage      没有记录数
     * @param bool  $rewriteObjId 是否重写评论对象ID
     *
     * @return array|bool
     */
    public function listLike($condition, $page = 1, $perpage = 30, $rewriteObjId = true)
    {

        if ($rewriteObjId && !empty($condition['cmtObjid'])) {
            $condition['cmtObjid'] = $this->_rewriteObiId($condition['cmtObjid']);
        }

        return $this->_servComment->likeList($condition, $page, $perpage);
    }

    /**
     * 删除评论
     *
     * @param array $condition 删除条件
     *
     * @return bool|mixed
     */
    public function delete($condition)
    {

        return $this->_servComment->delete($condition);
    }

}
