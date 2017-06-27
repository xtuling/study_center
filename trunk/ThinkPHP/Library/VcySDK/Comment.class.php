<?php
/**
 * Comment.class.php
 * 评论接口操作类
 * @license http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt LGPL
 * @copyright  Copyright (c) 2014 - ? VcySDK (http://www.vchangyi.com/)
 * @author zhoutao
 * @version 1.0.0
 */

namespace VcySDK;

class Comment
{
    // 对于业务对象的点赞
    const LIKE_TYPE_BUSINESS_OBJECT = 1;

    // 对于评论的点赞
    const LIKE_TYPE_COMMENT = 2;

    /**
     * 接口调用类
     *
     * @var object
     */
    private $serv = null;

    /**
     * 查询评论（分页）的接口地址
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const LIST_URL = '%s/comment/list';

    /**
     * 查询子评论（分页）的接口地址
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const CHILD_LIST_URL = '%s/comment/child_list';

    /**
     * 新增评论的接口地址
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const ADD_URL = '%s/comment/add';

    /**
     * 删除评论的接口地址
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const DEL_URL = '%s/comment/delete';

    /**
     * 评论点赞的接口地址
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const LIKE_URL = '%s/comment/like';

    /**
     * 评论取消点赞的接口地址
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const CANCEL_LIKE_URL = '%s/comment/cancel_like';

    /**
     * 查询点赞列表（分页）
     * %s = {apiUrl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @var string
     */
    const LIKE_LIST_URL = '%s/comment/like_list';

    /**
     * 初始化
     *
     * @param Service $serv 接口调用类
     */
    public function __construct($serv)
    {

        $this->serv = $serv;
    }

    /**
     * 查询评论（分页）
     * @param array $condition 查询条件
     * + string parentId 被回复评论id (非必填)
     * + string cmtObjid 评论对象ID,此ID由业务自己生成，如代表一条新闻公告的唯一标示，必须保证单企业全局唯一 (必填)
     * + Integer pageNum 当前页，空时默认为1 (非必填)
     * + Integer pageSize 页大小,空时默认为5,最大200 (非必填)
     * @param array $orders 排序字段
     * @param int $page 当前页数
     * @param int $perpage 每页数量
     * @return bool|mixed
     * @throws Exception
     */
    public function listAll($condition = array(), $orders = array(), $page = 1, $perpage = 30)
    {

        // 查询参数
        $condition = $this->serv->mergeListApiParams($condition, $orders, $page, $perpage);

        return $this->serv->postSDK(self::LIST_URL, $condition, 'generateApiUrlA');
    }

    /**
     * 获取子评论 (分页)
     * @param $condition
     *        + String rootId 顶级评论id
     *        + String cmtObjid 评论对象ID,此ID由业务自己生成，如代表一条新闻公告的唯一标示，必须保证单企业全局唯一
     * @param int $page
     * @param int $perpage
     * @return array|bool
     */
    public function childListAll($condition, $page = 1, $perpage = 5)
    {

        // 查询参数
        $condition = $this->serv->mergeListApiParams($condition, [], $page, $perpage);

        return $this->serv->postSDK(self::CHILD_LIST_URL, $condition, 'generateApiUrlA');
    }

    /**
     * 添加评论
     * @param array $data
     * + string memUid 用户ID (必填)
     * + string cmtContent 评论内容 (必填)
     * + string parentId 被回复评论id (非必填)
     * + string cmtObjid 评论对象ID,此ID由业务自己生成，如代表一条新闻公告的唯一标示，必须保证单企业全局唯一 (必填)
     * + string cmtAttachids 附件ID，使用英文逗号,隔开 (非必填)
     * @return bool|mixed
     * @throws Exception
     */
    public function add($data)
    {
        // 如果评论对象ID为空, 则自动生成
        if (empty($data['cmtObjid'])) {
            $config = &Config::instance();
            $data['cmtObjid'] = md5($config->pluginIdentifier . NOW_TIME . rand(1, 2000));
        }

        return $this->serv->postSDK(self::ADD_URL, $data, 'generateApiUrlA');
    }

    /**
     * 删除评论
     * @param array $condition 删除条件
     * + string cmtId 评论ID (必填)
     * + Integer deleteLevel 删除评论级别:1-如果当前评论包含子回复,会直接删除所有子回复;2-安全删除,必须先删除所有子回复,才能删除当前评论 (必填)
     * @return bool|mixed
     * @throws Exception
     */
    public function delete($condition)
    {
        return $this->serv->postSDK(self::DEL_URL, $condition, 'generateApiUrlA');
    }

    /**
     * 评论点赞
     * @param array $comment
     * + string cmtId 评论ID (点赞类型为2时必填)
     * + string cmtObjid 评论对象ID,此ID由业务自己生成，如代表一条新闻公告的唯一标示，必须保证单企业全局唯一 (必填)
     * + string memUid 用户ID (必填)
     * + Integer likeType 点赞类型,1-对业务对象的点赞（如对新闻公告这片文章的点赞）;2-对评论的点赞(如对新闻公告这篇文章评论的点赞) (必填)
     * @return bool|mixed
     * @throws Exception
     */
    public function like($comment)
    {
        return $this->serv->postSDK(self::LIKE_URL, $comment, 'generateApiUrlA');
    }

    /**
     * 评论点赞
     * @param array $comment
     * + string cmtId 评论ID (点赞类型为2时必填)
     * + string cmtObjid 评论对象ID,此ID由业务自己生成，如代表一条新闻公告的唯一标示，必须保证单企业全局唯一 (必填)
     * + string memUid 用户ID (必填)
     * + Integer likeType 点赞类型,1-对业务对象的点赞（如对新闻公告这片文章的点赞）;2-对评论的点赞(如对新闻公告这篇文章评论的点赞) (必填)
     * @return bool|mixed
     * @throws Exception
     */
    public function cancelLike($comment)
    {
        return $this->serv->postSDK(self::CANCEL_LIKE_URL, $comment, 'generateApiUrlA');
    }

    /**
     * 查询点赞列表（分页）
     * @param $condition
     *        + String cmtObjid 评论对象ID,此ID由业务自己生成，如代表一条新闻公告的唯一标示，必须保证单企业全局唯一
     * @param $page
     * @param $perpage
     * @return array|bool
     */
    public function likeList($condition, $page, $perpage)
    {
        $condition = $this->serv->mergeListApiParams($condition, [], $page, $perpage);

        return $this->serv->postSDK(self::LIKE_LIST_URL, $condition, 'generateApiUrlA');
    }
}
