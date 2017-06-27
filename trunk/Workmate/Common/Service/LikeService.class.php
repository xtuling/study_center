<?php
/**
 * 同事圈点赞信息表
 * User: 代军
 * Date: 2017-04-24
 */
namespace Common\Service;

use Common\Common\User;
use Common\Model\CircleModel;
use Common\Model\LikeModel;
use VcySDK\Service;

class LikeService extends AbstractService
{
    /**
     * 实例化同事圈表
     * @var CircleModel
     */
    public $__d_circle = null;

    // 构造方法
    public function __construct()
    {
        $this->_d = new LikeModel();
        $this->_d_circle = new CircleModel();

        parent::__construct();
    }


    /**
     * 格式化同事圈点赞列表
     * @param $list
     * @return bool
     */
    public function format_like_data(&$list)
    {
        if (!empty($list)) {

            // 取出列表的用户ID集合
            $uids = array_column($list, 'uid');

            // 查询用户集合数据
            $member = new User(Service::instance());
            $user_list = $member->listByConds(array('memUids' => $uids), 1, count($uids));

            // 获取已查询到UID列表
            $uid_list = array_column($user_list['list'], 'memUid');

            // 获取全部用户列表
            $this->user_list($user_list['list'], $uids, $uid_list);

            // 将用户数据转换成以用户ID为key的二维数组
            $user_data = array_combine_by_key($user_list['list'], 'memUid');

            // 循环给列表中字段赋值
            foreach ($list as &$v) {
                $v['username'] = strval($user_data[$v['uid']]['memUsername']);
                $v['avatar'] = strval($this->memFace($user_data[$v['uid']]['memFace']));
                $v['like_id'] = intval($v['like_id']);

            }
        }

        return true;
    }

    /**
     * 【微信端】点赞接口
     * @param string $id
     * @return bool
     */
    public function like($id = '', $uid = '')
    {
        // 如果是外部人员
        if (empty($uid)) {

            $this->_set_error('_EMPTY_MEM_LIKE');

            return false;
        }

        // 如果话题ID为空
        if (empty($id)) {

            $this->_set_error('_EMPTY_ID');

            return false;
        }

        // 统计当前评论或者话题是否存在
        $circle = $this->_d_circle->count_by_conds(array('id' => $id));

        // 如果评论或者话题不存在
        if (empty($circle)) {

            $this->_set_error('_EMPTY_ID_INFO');

            return false;
        }

        // 获取当前用户对当前文章是否点过赞
        $total = $this->_d->count_by_conds(array('cid' => $id, 'uid' => $uid));

        // 如果点过
        if ($total) {

            $this->_set_error('_ERR_ID_POINT_PRAISE');

            return false;
        }

        // 写入点赞记录
        $this->_d->insert(array('cid' => $id, 'uid' => $uid));

        return true;
    }

    /**
     * 【微信端】取消点赞接口
     * @param string $id
     * @return bool
     */
    public function cancel_like($id = '', $uid = '')
    {
        // 如果话题ID为空
        if (empty($id)) {

            $this->_set_error('_EMPTY_ID');

            return false;
        }

        // 统计当前评论或者话题是否存在
        $circle = $this->_d_circle->count_by_conds(array('id' => $id));

        // 如果评论或者话题不存在
        if (empty($circle)) {

            $this->_set_error('_EMPTY_ID_INFO');

            return false;
        }

        // 获取当前用户对当前文章是否点过赞
        $total = $this->_d->count_by_conds(array('cid' => $id, 'uid' => $uid));

        // 如果点过
        if (!$total) {

            $this->_set_error('_ERR_ID_POINT_NOT_PRAISE');

            return false;
        }

        // 删除点赞记录
        $this->_d->delete_by_conds(array('cid' => $id, 'uid' => $uid));

        return true;
    }


    /**
     * 【微信端】格式化点赞列表
     * @param array $data 点赞列表
     * @return array
     */
    public function format_like_user_data($data = array())
    {

        // 初始化
        $list = array();

        // 如果数组为空
        if (empty($data)) {

            return $list;
        }

        // 获取UIDS列表
        $mem_uids = array_column($data, 'uid');

        // 获取人员详情
        $users = $this->format_user($mem_uids);

        // 遍历数据
        foreach ($data as $key => $v) {

            $list[] = array(
                'avatar' => strval($this->memFace($users[$v['uid']]['avatar'])),
                'username' => strval($users[$v['uid']]['username']),
                'created' => strval($v['created'])
            );
        }

        return $list;
    }

    /**
     * 【微信端】获取点赞列表
     * @param array $params
     *                  + string limit 每页条少条
     *                  + string page 当前第几页
     * @return array
     */
    public function get_like_list($params = array())
    {

        // 每页条数
        $params['limit'] = empty($params['limit']) ? self::DEFAULT_LIMIT : (int)$params['limit'];
        $params['page'] = empty($params['page']) ? 1 : $params['page'];
        list($start, $limit, $page) = page_limit($params['page'], $params['limit']);

        // 分页参数
        $page_option = array(
            $start,
            $limit,
        );

        // 排序参数
        $order_by = array(
            'created' => 'DESC'
        );

        // 初始化查询条件
        $conditions = array(
            'cid' => $params['pid']
        );

        // 获取点赞总数
        $total = $this->_d->count_by_conds($conditions);

        // 获取点赞列表
        $data = $this->_d->list_by_conds($conditions, $page_option, $order_by);

        // 格式化点赞列表
        $list = $this->format_like_user_data($data);

        return array(
            'total' => intval($total),
            'limit' => intval($limit),
            'page' => intval($page),
            'list' => $list,
        );
    }

}

