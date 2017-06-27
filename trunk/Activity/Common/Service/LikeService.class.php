<?php
/**
 * LikeService.class.php
 * 活动、评论点赞信息表
 * @author: daijun
 * @copyright: vchangyi.com
 */

namespace Common\Service;

use Common\Common\User;
use Common\Model\ActivityModel;
use Common\Model\CommentModel;
use Common\Model\LikeModel;

class LikeService extends AbstractService
{
    // 活动点赞类型
    const ACTIVITY_TYPE = 1;
    // 评论点赞类型
    const COMMENT_TYPE = 2;

    // 发布活动
    public static $levels = array(
        self::ACTIVITY_TYPE,
        self::COMMENT_TYPE,
    );

    /**
     * @var  ActivityModel 活动model
     */
    protected $_d_activity;

    /**
     * @var  CommentModel 评论model
     */
    protected $_d_comment;

    // 构造方法
    public function __construct()
    {
        $this->_d = new LikeModel();
        $this->_d_activity = new ActivityModel();
        $this->_d_comment = new CommentModel();
        parent::__construct();
    }

    /**
     * 获取点赞列表
     * @param array $params 查询参数
     * @return array
     */
    public function get_like_list($params = array())
    {
        // 每页条数
        $limit = empty($params['limit']) ? self::DEFAULT_LIMIT : intval($params['limit']);
        $page = empty($params['page']) ? 1 : intval($params['page']);

        // 参数校验
        $cid = intval($params['cid']);
        $type = intval($params['type']);

        if (!in_array($type, self::$levels)) {

            E('_ERR_AC_TYPE');
        }
        if (!$cid) {

            E('_EMPTY_AC_CID');
        }

        list($start, $limit, $page) = page_limit($page, $limit);

        // 分页参数
        $page_option = array($start, $limit);

        // 排序按照发布时间参数
        $order_option = array('created' => 'DESC');

        /** 组装搜索条件 */
        $conds = array('cid' => $cid, 'type' => $type);

        // 返回参数
        $fields = 'like_id,uid,cid,type,created';

        // 查询总条数
        $total = $this->_d->count_by_conds($conds);

        $list = array();
        $user_list = array();
        if ($total > 0) {
            // 列表和总数
            $list = $this->_d->list_by_conds($conds, $page_option, $order_option, $fields);

            $uids = array_column($list, 'uid');

            // 查询用户集合
            $user = new User();
            $users = $user->listAll(['memUids' => $uids]);
            $this->user_list($users, $uids);
            $user_list = array_combine_by_key($users, 'memUid');
        }

        // 循环格式化列表数据
        $arr = array();
        foreach ($list as $key => $val) {
            $value = array();
            $value['created'] = $val['created'];
            $value['like_id'] = $val['like_id'];
            $value['uid'] = $val['uid'];
            $value['username'] = $user_list[$val['uid']]['memUsername'];
            $value['avatar'] = $this->pic_thumbs($user_list[$val['uid']]['memFace']);

            $arr[] = $value;
        }

        return array(
            'page' => $page,
            'limit' => $limit,
            'total' => intval($total),
            'cid' => $cid,
            'type' => $type,
            'list' => $arr,
        );
    }

    /**
     * 点赞接口
     * @param array $param
     * @return bool
     */
    public function add_like_data($param = array())
    {
        $type = intval($param['type']);
        $cid = intval($param['cid']);

        // 验证参数
        if (!in_array($type, self::$levels)) {

            E('_ERR_AC_TYPE');
        }
        if (!$cid) {

            E('_EMPTY_AC_CID');
        }
        if (empty($param['uid'])) {

            E('_ERR_AC_UID_EMPTY');
        }

        // 查询点赞记录
        $count = $this->count_by_conds(
            array(
                'uid' => $param['uid'],
                'cid' => $cid,
                'type' => $type,
            )
        );

        // 已点赞
        if ($count) {

            E('_ERR_AC_LIKE_END');
        }

        //点赞操作
        try {
            // 开始事务
            $this->_d->start_trans();

            // 插入点赞记录表
            $this->_d->insert(array("uid" => $param['uid'], 'cid' => $cid, 'type' => $type));

            // 更新主表统计数据
            if ($type == self::ACTIVITY_TYPE) {

                //活动点赞加一
                $this->_d_activity->update($cid, array("likes=likes+(?)" => 1));
            } else {

                //评论点赞加一
                $this->_d_comment->update($cid, array("likes=likes+(?)" => 1));
            }
            // 提交修改
            $this->_d->commit();

        } catch (\Think\Exception $e) {
            \Think\Log::record($e);
            $this->_d->rollback();
            E('_ERR_ADD_LIKE_FAILED');

            return false;
        } catch (\Exception $e) {
            \Think\Log::record($e);
            $this->_d->rollback();
            E('_ERR_ADD_LIKE_FAILED');

            return false;
        }

        return true;
    }

    /**
     * 取消点赞接口
     * @param array $param
     * @return bool
     */
    public function del_like_data($param = array())
    {
        $type = intval($param['type']);
        $cid = intval($param['cid']);

        // 参数校验
        if (!in_array($type, self::$levels)) {

            E('_ERR_AC_TYPE');
        }
        if (!$cid) {

            E('_EMPTY_AC_CID');
        }
        if (empty($param['uid'])) {

            E('_ERR_AC_UID_EMPTY');
        }
        // 查询点赞记录
        $count = $this->count_by_conds(
            array(
                'uid' => $param['uid'],
                'cid' => $cid,
                'type' => $type,
            )
        );

        // 没有点赞记录
        if (!$count) {

            E('_ERR_AC_UNLIKE_END');
        }

        //取消点赞操作
        try {
            // 开始事务
            $this->_d->start_trans();
            // 删除点赞记录表
            $this->_d->delete_by_conds(array('uid' => $param['uid'], 'cid' => $cid, 'type' => $type));

            // 更新主表统计数据
            if ($type == self::ACTIVITY_TYPE) {

                //取消活动点赞减一
                $this->_d_activity->update($cid, array("likes=likes-(?)" => 1));
            } else {

                //取消评论点赞减加一
                $this->_d_comment->update($cid, array("likes=likes-(?)" => 1));
            }
            // 提交修改
            $this->_d->commit();

        } catch (\Think\Exception $e) {
            \Think\Log::record($e);
            $this->_d->rollback();
            E('_ERR_ADD_LIKE_FAILED');

            return false;
        } catch (\Exception $e) {
            \Think\Log::record($e);
            $this->_d->rollback();
            E('_ERR_ADD_LIKE_FAILED');

            return false;
        }

        return true;
    }
}
