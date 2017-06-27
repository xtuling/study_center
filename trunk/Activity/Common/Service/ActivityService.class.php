<?php
/**
 * ActivityService.class.php
 * 活动信息表
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-05-05 15:25:21
 */

namespace Common\Service;

use Common\Common\User;
use Common\Model\ActivityModel;
use Common\Model\CommentModel;
use Common\Model\LikeModel;
use Common\Model\RightModel;
use Com\Validate;
use Think\Exception;

class ActivityService extends AbstractService
{
    // 全公司
    const IS_ALL = 1;

    // 发布活动
    const PUBLISH_ACTIVITY = 1;
    // 结束活动
    const STOP_ACTIVITY = 2;

    // 活动综合状态 ：草稿
    const STATUS_DRAFT = 1;
    // 活动综合状态 ：未开始
    const STATUS_NOT_START = 2;
    // 活动综合状态 ：进行中
    const STATUS_ING = 3;
    // 活动综合状态 ：已结束
    const STATUS_END = 4;
    // 活动综合状态 ：已终止
    const STATUS_STOP = 5;

    // 已点赞
    const SUCCES_STATE = 1;
    // 未点赞
    const FALSE_STATE = 0;

    /**
     * @var  LikeModel 点赞数据model
     */
    protected $_d_like = null;

    // 构造方法
    public function __construct()
    {
        $this->_d = new ActivityModel();
        $this->_d_like = new LikeModel();
        parent::__construct();
    }

    /**
     * 新增活动(后端)
     * @param array $result 返回活动信息
     * @param array $reqData 请求数据
     * @return bool
     */
    public function add_activity(&$result, $reqData)
    {
        // 获取活动数据
        $activity = $this->fetch_activity($reqData);

        // 验证数据
        $this->validate_for_add($activity);

        // 是否是立即发布活动
        if ($activity['activity_status'] == ActivityModel::ACTIVITY_PUBLISH) {

            $activity['publish_time'] = MILLI_TIME;
        }

        $activity['last_time'] = MILLI_TIME;

        $rightServ = new RightService();

        try {
            $this->_d->start_trans();

            // 活动数据入库
            $ac_id = $this->_d->insert($activity);
            // 新增活动失败
            if (!$ac_id) {

                E('_ERR_ACTIVITY_ADD_FAILED');

                return false;
            }

            // 权限入库
            if ($activity['is_all'] != self::IS_ALL) {

                $rightServ->save_data(array('ac_id' => $ac_id), $activity['right']);
            }

            $this->_d->commit();
        } catch (Exception $e) {

            $this->_d->rollback();
            E('_ERR_ACTIVITY_ADD_FAILED');

            return false;
        }

        // 【发送消息】状态为发布状态并且开启发送消息时则发送消息
        if ($activity['is_notice'] == ActivityModel::NOTICE_ON && $activity['activity_status'] == ActivityModel::ACTIVITY_PUBLISH && $ac_id) {

            // 组装发送消息的数据
            $params = $this->assemble_msg_params($ac_id, $activity);
            // 发送消息
            $this->send_msg($params, self::MSG_ACTIVITY_PUBLISH);
        }

        // 是否推荐到首页feed流
        if ($activity['is_recomend'] && $activity['activity_status'] == ActivityModel::ACTIVITY_PUBLISH) {

            $url = convertUrl(QY_DOMAIN . '/Public/Rpc/Recommender/ArticleNew');

            $params = [
                'app' => 'activity',
                'dataCategoryId' => '',
                'dataId' => $ac_id,
                'title' => $activity['subject'],
                'summary' => $activity['content'],
                'attachId' => $activity['cover_id'],
                'pic' => !empty($activity['cover_id']) ? imgUrl($activity['cover_id']) : '',
                'url' => 'Activity/Frontend/Index/Msg?type=1&id=' . $ac_id,
            ];

            \Com\Rpc::phprpc($url)->invoke('Index', $params);
        }

        $result = array('ac_id' => intval($ac_id));

        return true;
    }

    /**
     * 编辑活动(后端)
     * @param array $result 返回活动信息
     * @param array $reqData 请求数据
     * @return bool
     */
    public function update_activity(&$result, $reqData)
    {
        $ac_id = intval($reqData['ac_id']);

        // 活动ID不能为空
        if (empty($ac_id)) {

            E('_ERR_AC_ID_EMPTY');

            return false;
        }
        // 活动不存在
        if (!$old_activity = $this->_d->get($ac_id)) {

            E('_ERR_ARTICLE_NOT_FOUND');

            return false;
        }

        // 获取用户提交的活动数据
        $activity = $this->fetch_activity($reqData);

        // 验证数据
        $this->validate_for_add($activity);

        // 已发布的不能编辑保存成草稿
        if ($old_activity['activity_status'] == ActivityModel::ACTIVITY_PUBLISH && $activity['activity_status'] == ActivityModel::ACTIVITY_DRAFT) {

            E('_ERR_ACTIVITY_STATUS');

            return false;
        }

        // 是否是立即发布活动
        if ($activity['activity_status'] == ActivityModel::ACTIVITY_PUBLISH) {

            $activity['publish_time'] = MILLI_TIME;
        }

        $activity['last_time'] = MILLI_TIME;

        // 用户提交的新的权限数据
        $right = $activity['right'];
        $right['is_all'] = $activity['is_all'];

        unset($activity['right']);

        $rightServ = new RightService();

        // 获取已有权限
        $old_right = $rightServ->list_by_conds(array('ac_id' => $ac_id));

        try {
            $this->_d->start_trans();
            // 活动数据入库
            $this->_d->update($ac_id, $activity);

            // 权限入库
            if ($activity['is_all'] != self::IS_ALL) {

                $rightServ->save_data(array('ac_id' => $ac_id), $right);
            }

            // 如果是全公司则删除数据库已有的权限
            if ($activity['is_all'] == self::IS_ALL && !empty($old_right)) {

                $rightServ->delete_by_conds(array('ac_id' => $ac_id));
            }

            $this->_d->commit();
        } catch (Exception $e) {

            $this->_d->rollback();
            E('_ERR_ACTIVITY_ADD_FAILED');

            return false;
        }

        // 【发送消息】状态为发布状态并且开启发送消息时则发送消息
        if ($activity['is_notice'] == ActivityModel::NOTICE_ON && $activity['activity_status'] == ActivityModel::ACTIVITY_PUBLISH) {

            //【1】如果是由草稿 改为发布
            if ($old_activity['activity_status'] == ActivityModel::ACTIVITY_DRAFT) {

                // 组装发送消息的数据
                $activity['right'] = $right;
                $params = $this->assemble_msg_params($ac_id, $activity);
                // 发送消息
                $this->send_msg($params, self::MSG_ACTIVITY_PUBLISH);

            } else {
                //【2】如果是由已发布改为发布

                // 获取需要发送消息的所有用户
                $msg_users = $rightServ->right_to_all($old_right, $right);

                // 发送新消息
                if (!empty($msg_users['add'])) {

                    $activity['right']['uids'] = $msg_users['add'];
                    // 组装发送消息的数据
                    $params = $this->assemble_msg_params($ac_id, $activity, 'edit');
                    // 发送消息
                    $this->send_msg($params, self::MSG_ACTIVITY_PUBLISH);
                }

                // 发送更新通知
                if (!empty($msg_users['update'])) {

                    $activity['right']['uids'] = $msg_users['update'];

                    // 组装发送消息的数据
                    $params = $this->assemble_msg_params($ac_id, $activity, 'edit');
                    // 发送消息
                    $this->send_msg($params, self::MSG_ACTIVITY_UPDATE);
                }

            }
        }

        // 旧数据被推荐，编辑后的新数据取消推荐，则删除已推荐的数据
        if ($old_activity['is_recomend'] && !$activity['is_recomend'] && $activity['activity_status'] == ActivityModel::ACTIVITY_PUBLISH) {

            $url = convertUrl(QY_DOMAIN . '/Public/Rpc/Recommender/ArticleDelete');
            $params = [
                'app' => 'activity',
                'dataCategoryId' => '',
                'dataId' => $ac_id,
            ];

            \Com\Rpc::phprpc($url)->invoke('Index', $params);
        }

        // 是否推荐到首页feed流
        if ($activity['is_recomend'] && $activity['activity_status'] == ActivityModel::ACTIVITY_PUBLISH) {

            $url = convertUrl(QY_DOMAIN . '/Public/Rpc/Recommender/ArticleNew');

            $params = [
                'app' => 'activity',
                'dataCategoryId' => '',
                'dataId' => $ac_id,
                'title' => $activity['subject'],
                'summary' => $activity['content'],
                'attachId' => $activity['cover_id'],
                'pic' => !empty($activity['cover_id']) ? imgUrl($activity['cover_id']) : '',
                'url' => 'Activity/Frontend/Index/Msg?type=1&id=' . $ac_id,
            ];

            \Com\Rpc::phprpc($url)->invoke('Index', $params);
        }

        $result = array('ac_id' => intval($ac_id));

        return true;
    }

    /**
     * 删除活动信息
     * @param array $reqData 请求数据
     * @return bool
     */
    public function delete_activity($reqData)
    {

        $ids = $reqData['ac_ids'];

        if (empty($ids) || !is_array($ids)) {
            E('_ERR_AC_ID_EMPTY');

            return false;
        }

        $rightModel = new RightModel();
        $commentModel = new CommentModel();

        $conds = array('ac_id' => $ids);
        try {
            // 删除开始
            $this->_d->start_trans();

            // 获取需要删除的所有活动，用于删除首页推荐feed流
            $activity_lists = $this->_d->list_by_pks($ids);

            // 删除活动主题
            $this->_d->delete($ids);
            // 删除权限
            $rightModel->delete_by_conds($conds);
            // 删除评论
            $commentModel->delete_by_conds($conds);

            // 提交修改
            $this->_d->commit();
        } catch (Exception $e) {
            $this->_d->rollback();
            E('_ERR_DELETE_FAILED');

            return false;
        }

        // 删除首页推荐feed流
        foreach ($activity_lists as $activity) {

            if ($activity['is_recomend']) {

                $url = convertUrl(QY_DOMAIN . '/Public/Rpc/Recommender/ArticleDelete');
                $params = [
                    'app' => 'activity',
                    'dataCategoryId' => '',
                    'dataId' => $activity['ac_id'],
                ];

                \Com\Rpc::phprpc($url)->invoke('Index', $params);
            }
        }

        return true;
    }

    /**
     * 立即发布活动
     * @param array $reqData 请求数据
     * @return bool
     */
    public function publish_activity(&$result, $reqData)
    {

        $ac_id = intval($reqData['ac_id']);
        // 活动详情
        $activity = $this->_d->get($ac_id);

        // 活动开始时间是否小于当前时间
        if ($activity['begin_time'] <= MILLI_TIME) {

            E('_ERR_ACTIVITY_OVERDUE');

            return false;
        }

        $data = array();
        $data['last_time'] = MILLI_TIME;
        $data['publish_time'] = MILLI_TIME;
        $data['activity_status'] = ActivityModel::ACTIVITY_PUBLISH;
        $this->_d->update_by_conds(array('ac_id' => $ac_id), $data);

        // 【发送消息】开启发送消息时则发送消息
        if ($activity['is_notice'] == ActivityModel::NOTICE_ON) {

            $activity['publish_time'] = $data['publish_time'];

            // 如果不是全公司则需要组装权限范围
            if ($activity['is_all'] != self::IS_ALL) {

                $rightServ = new RightService();
                // 获取权限数据
                list($right_list, $right_data) = $rightServ->get_data(array('ac_id' => $ac_id));

                $activity['right']['uids'] = $right_data['user_arr'];
                $activity['right']['dp_ids'] = $right_data['dp_arr'];
                // $activity['right']['tag_ids'] = $right_data['tag_arr'];
                $activity['right']['job_ids'] = $right_data['job_arr'];
                $activity['right']['role_ids'] = $right_data['role_arr'];
            }

            // 组装发送消息的数据
            $params = $this->assemble_msg_params($ac_id, $activity);
            // 发送消息
            $this->send_msg($params, self::MSG_ACTIVITY_PUBLISH);
        }

        $result = array('ac_id' => intval($ac_id));

        return true;
    }

    /**
     * 验证新增活动数据
     * @param array $activity 活动数据
     * @return bool
     */
    protected function validate_for_add(&$activity)
    {
        // 验证规则
        $rules = array(
            'subject' => 'require|max:30',
            'source' => 'max:10',
            'begin_time' => 'require',
            'cover_id' => 'require',
            'content' => 'require',
            'is_all' => 'require|in:0,1',
            'is_notice' => 'require|in:0,1',
            'is_recomend' => 'require|in:0,1',
            'activity_status' => 'require|in:0,1,2',
        );

        // 错误提示
        $msgs = array(
            'subject.require' => L('_ERR_SUBJECT_EMPTY'),
            'subject.max' => L('_ERR_SUBJECT_LENGTH_ERROR'),
            'source.max' => L('_ERR_SOURCE_LENGTH_ERROR'),
            'begin_time' => L('_ERR_BEGINTIME_EMPTY'),
            'cover_id' => L('_ERR_COVER_EMPTY'),
            'content' => L('_ERR_CONTENT_EMPTY'),
            'is_all' => L('_ERR_ISALL_INVALID'),
            'is_notice' => L('_ERR_ISNOTICE_INVALID'),
            'is_recomend' => L('_ERR_ISRECOMEND_INVALID'),
            'activity_status' => L('_ERR_ACTIVITY_STATUS_INVALID'),
        );

        // 开始验证
        $validate = new Validate($rules, $msgs);
        if (!$validate->check($activity)) {

            E($validate->getError());

            return false;
        }

        // 开始时间不能小于当前时间
        if ($activity['begin_time'] <= MILLI_TIME) {

            E('_ERR_BEGINTIME_LT_NOWTIME');

            return false;
        }

        // 结束时间不能小于当前时间
        if ($activity['end_time'] > 0 && $activity['end_time'] <= MILLI_TIME) {

            E('_ERR_ENDTIME_LT_NOWTIME');

            return false;
        }

        // 开始时间不能大于结束时间
        if ($activity['begin_time'] > $activity['end_time'] && $activity['end_time'] > 0) {

            E('_ERR_BEGINTIME_GT_ENDTIME');

            return false;
        }

        // 如果不是全公司
        if ($activity['is_all'] != self::IS_ALL) {

            // 参与权限不能为空
            if (empty($activity['right'])) {

                E('_ERR_RIGHT_EMPTY');

                return false;
            }
        }

        return true;
    }

    /**
     * 获取活动数据
     * @param array $activity 活动数据
     * @return array|bool
     */
    protected function fetch_activity($activity)
    {
        $right_view = array();
        $right = $activity['right'];

        unset($activity['right']);

        // 活动状态是否存在
        if (!isset($activity['activity_status'])) {

            E('_EMPTY_ACTIVITY_STATUS');

            return false;
        }

        // 格式化权限字段
        if (!empty($right)) {

            $right_view['uids'] = !empty($right['user_arr']) ? $right['user_arr'] : '';
            $right_view['dp_ids'] = !empty($right['dp_arr']) ? $right['dp_arr'] : '';
            // $right_view['tag_ids'] = !empty($right['tag_arr']) ? $right['tag_arr'] : '';
            $right_view['job_ids'] = !empty($right['job_arr']) ? $right['job_arr'] : '';
            $right_view['role_ids'] = !empty($right['role_arr']) ? $right['role_arr'] : '';
        }

        return array(
            'subject' => raddslashes($activity['subject']),
            'source' => raddslashes($activity['source']),
            'begin_time' => $activity['begin_time'],
            'end_time' => $activity['end_time'] ? $activity['end_time'] : 0,
            'cover_id' => raddslashes($activity['cover_id']),
            'content' => serialize($activity['content']),
            'is_all' => intval($activity['is_all']),
            'is_notice' => intval($activity['is_notice']),
            'is_recomend' => intval($activity['is_recomend']),
            'activity_status' => intval($activity['activity_status']),
            'right' => $right_view,
        );
    }

    /**
     * 组装发送消息的数据
     * @param  int $ac_id 活动ID
     * @param  array $activity 活动信息数组
     * @return array
     */
    protected function assemble_msg_params($ac_id, $activity, $type = 'add')
    {

        $right_params = array();
        $params = array(
            'cid' => $ac_id,
            'subject' => $activity['subject'],
            'begin_time' => $activity['begin_time'],
            'end_time' => $activity['end_time'],
            'publish_time' => $activity['publish_time'],
        );

        // 发布、立即发布活动
        if ($type == 'add') {

            // 全公司
            if ($activity['is_all'] == self::IS_ALL) {

                $params['is_all'] = $activity['is_all'];
            } else {

                $right = $activity['right'];
                $rightServ = new RightService();
                // 获取参与活动权限范围
                $right_params = $rightServ->list_by_right($right);
            }
        }

        // 编辑活动
        if ($type == 'edit') {

            $right_params = $activity['right'];
        }

        return $params = array_merge($params, $right_params);

    }

    /**
     * 获取后台活动列表
     * @param array $params 分页及搜索参数
     * @return mixed
     */
    public function get_list_admin($params = array())
    {
        // 默认值
        $page = !empty($params['page']) ? intval($params['page']) : self::DEFAULT_PAGE;
        $limit = !empty($params['limit']) ? intval($params['limit']) : self::DEFAULT_LIMIT;

        // 分页
        list($start, $limit) = page_limit($page, $limit);

        // 排序
        $order_option = array('last_time' => 'DESC');

        // 获取记录总数
        $total = $this->_d->count_by_where($params);

        // 获取列表数据
        $list = array();
        if ($total > 0) {
            $list = $this->_d->list_by_where($params, array($start, $limit), $order_option);
        }

        // 组装返回数据
        $result['total'] = intval($total);
        $result['limit'] = intval($limit);
        $result['page'] = intval($page);
        $result['list'] = $this->format_list_admin($list);

        return $result;
    }

    /**
     * 格式化后台活动详情数据
     * @param $data array 活动详情数据
     * @return mixed
     */
    public function format_activity_detail($data)
    {
        $data['ac_id'] = intval($data['ac_id']);
        $data['is_all'] = intval($data['is_all']);
        $data['is_notice'] = intval($data['is_notice']);
        $data['is_recomend'] = intval($data['is_recomend']);
        $data['activity_status'] = $this->activity_status($data['activity_status'], $data['begin_time'],
            $data['end_time']);
        $data['cover_url'] = imgUrl($data['cover_id']);
        $data['likes'] = intval($data['likes']);
        $data['comments'] = intval($data['comments']);
        $data['content'] = unserialize($data['content']);
        unset($data['domain'], $data['status'], $data['updated'], $data['deleted']);

        return $data;
    }

    /**
     * 格式化详情数据
     * @param array $data
     * @param string $uid
     * @return array
     */
    public function format_detail_data($data = array(), $uid = '')
    {
        // 转换活动状态
        $data['activity_status'] = $this->activity_status($data['activity_status'],
            $data['begin_time'], $data['end_time']);

        // 获取收藏状态 xtong 2017年06月02日
        $url = convertUrl(QY_DOMAIN . '/Public/Rpc/Collection/CollectionStatus');

        $params = [
            'uid' => $uid,
            'app' => 'activity',
            'dataId' => $data['ac_id']
        ];

        $res = \Com\Rpc::phprpc($url)->invoke('Index', $params);

        $data['is_collect'] = json_decode($res, true)['collection'];

        $data['content'] = unserialize($data['content']);
        // 获取活动图片url
        $data['cover_url'] = imgUrl($data['cover_id']);

        $data['ac_id'] = intval($data['ac_id']);
        $data['likes'] = intval($data['likes']);
        $data['comments'] = intval($data['comments']);

        //查询当前用户是否点赞
        $mylikecount = $this->_d_like->count_by_conds(
            array(
                'uid' => $uid,
                'cid' => $data['ac_id'],
                'type' => LikeService::ACTIVITY_TYPE,
            )
        );
        $data['is_like'] = $mylikecount > 0 ? self::SUCCES_STATE : self::FALSE_STATE;
        // 组装点赞列表查询条件
        $fields = 'like_id,uid,created';
        $limit = empty($params['limit']) ? self::DEFAULT_LIMIT : intval($params['limit']);
        $order_option = array(
            'created' => 'DESC',
        );

        // 查询活动点赞列表
        $like_list = $this->_d_like->list_by_conds(array('cid' => $data['ac_id'], 'type' => LikeService::ACTIVITY_TYPE),
            array(0, $limit),
            $order_option, $fields);

        // 获取点赞用户集合
        $uids = array_column($like_list, 'uid');

        // 查询用户集合
        $user = new User();
        $users = $user->listAll(['memUids' => $uids]);
        $this->user_list($users, $uids);
        $user_list = array_combine_by_key($users, 'memUid');

        // 获取被删除的用户信息
        $this->user_list($user_list, $uids);

        // 格式化点赞列表数据
        $arr = array();
        foreach ($like_list as $key => $val) {
            $value = array();

            $value['created'] = $val['created'];
            $value['like_id'] = $val['like_id'];
            $value['uid'] = $val['uid'];
            $value['username'] = $user_list[$val['uid']]['memUsername'];
            $value['avatar'] = $this->pic_thumbs($user_list[$val['uid']]['memFace']);

            $arr[] = $value;
        }

        // 格式化点赞列表函
        $data['like_list'] = $arr;

        // 删除多余数据
        unset(
            $data['is_all'],
            $data['is_notice'],
            $data['is_recomend'],
            $data['domain'],
            $data['status'],
            $data['created'],
            $data['updated'],
            $data['deleted'],
            $data['last_time']
        );

        return $data;
    }

    /**
     * 格式化后台活动列表数据
     * @param $list
     * @return mixed;
     */
    public function format_list_admin(&$list)
    {

        // 获取权限数据
        $ac_ids = array_column($list, 'ac_id');

        if (empty($ac_ids)) {
            return array();
        }

        $right_serv = new RightService();

        list($right_list, $right_data) = $right_serv->get_data(array('ac_id' => $ac_ids));

        foreach ($list as &$v) {

            $v['ac_id'] = intval($v['ac_id']);
            $v['is_all'] = intval($v['is_all']);
            $v['is_notice'] = intval($v['is_notice']);
            $v['is_recomend'] = intval($v['is_recomend']);
            $v['likes'] = intval($v['likes']);
            $v['comments'] = intval($v['comments']);

            // 转换数据状态
            $v['activity_status'] = $this->activity_status($v['activity_status'], $v['begin_time'], $v['end_time']);

            $user_arr = array();
            $dp_arr = array();
            // $tag_arr = array();
            $job_arr = array();
            $role_arr = array();

            //如果是全公司，则无需获取权限数据
            if ($v['is_all'] == self::IS_ALL) {
                $right_view = array(
                    'user_arr' => $user_arr,
                    'dp_arr' => $dp_arr,
                    // 'tag_arr' => $tag_arr,
                    'job_arr' => $job_arr,
                    'role_arr' => $role_arr,
                );
                $v['right'] = $right_view;
                continue;
            }

            // 获取当前活动的权限集合
            $right_arr = array_filter($right_list, function ($value, $key) use ($v) {
                return $value['ac_id'] == $v['ac_id'];
            }, ARRAY_FILTER_USE_BOTH);

            // 如果不是全公司，则需获取权限数据
            // 循环权限数据集合
            foreach ($right_arr as $_v) {
                // 权限表用户ID不为空
                if (!empty($_v['uid'])) {

                    $user_list = array_combine_by_key($right_data['user_arr'], 'memID');
                    $user_arr[] = $user_list[$_v['uid']];
                    continue;
                }
                // 权限表部门ID不为空
                if (!empty($_v['dp_id'])) {

                    $dp_list = array_combine_by_key($right_data['dp_arr'], 'dpID');
                    $dp_arr[] = $dp_list[$_v['dp_id']];
                    continue;
                }

                // 权限表标签ID不为空
                // if (!empty($_v['tag_id'])) {
                //     
                //     $tag_list = array_combine_by_key($right_data['tag_arr'], 'tagID');
                //     $tag_arr[] = $tag_list[$_v['tag_id']];
                //     continue;
                // }

                // 权限表岗位ID不为空
                if (!empty($_v['job_id'])) {

                    $job_list = array_combine_by_key($right_data['job_arr'], 'jobID');
                    $job_arr[] = $job_list[$_v['job_id']];
                    continue;
                }
                // 权限表角色ID不为空
                if (!empty($_v['role_id'])) {

                    $role_list = array_combine_by_key($right_data['role_arr'], 'roleID');
                    $role_arr[] = $role_list[$_v['role_id']];
                    continue;
                }

            }

            $right_view = array(
                'user_arr' => $user_arr,
                'dp_arr' => $dp_arr,
                /*'tag_arr' => $tag_arr,*/
                'job_arr' => array_filter($job_arr),
                'role_arr' => array_filter($role_arr),
            );

            $v['right'] = $right_view;

        }

        return $list;
    }

    /**
     * 格式化活动列表返回数据
     * @param array $list
     * @param int $uid
     * @return array
     */
    public function activity_param($list = array(), $uid = 0)
    {
        if (empty($list)) {
            return array();
        }
        //获取点赞
        $ac_ids = array_column($list, 'ac_id');
        $like_data = $this->_d_like->list_by_conds(array('cid' => $ac_ids, 'type' => 1, 'uid' => $uid));
        //我已经点赞的活动
        $back_cid = array_column($like_data, 'cid');
        //要返回的字段
        $arr = array();
        //去除中文空格
        $search = array(" ", "　", "\n", "\r", "\t");
        $replace = array("", "", "", "", "");
        foreach ($list as $key => $val) {
            if (empty($val['cover_id'])) {
                $val['cover_id'] = '';
            } else {
                $val['cover_url'] = imgUrl($val['cover_id']);
            }
            $val['activity_status'] = $this->activity_status($val['activity_status'], $val['begin_time'],
                $val['end_time']);
            $val['is_like'] = in_array($val['ac_id'], $back_cid) ? self::SUCCES_STATE : self::FALSE_STATE;
            $val['content'] = str_replace($search, $replace, strip_tags(unserialize($val['content'])));
            $val['likes'] = intval($val['likes']);
            $val['comments'] = intval($val['comments']);
            unset(
                $val['deleted'],
                $val['updated'],
                $val['created'],
                $val['status'],
                $val['domain'],
                $val['is_all'],
                $val['is_notice'],
                $val['is_recomend'],
                $val['last_time']
            );
            $arr[$key] = $val;
        }

        return $arr;
    }

    /**
     * 活动状态转化函数
     * @param string $activity_status 活动状态
     * @param string $begin_time 开始时间
     * @param string $end_time 结束时间
     * @return int 活动状态1：草稿，2：未开始，3：进行中，4：已结束，5：已终止
     */
    public function activity_status($activity_status = '0', $begin_time = '0', $end_time = '0')
    {
        if ($activity_status == ActivityModel::ACTIVITY_DRAFT) {
            // 草稿
            $status = self::STATUS_DRAFT;
        } elseif ($activity_status == ActivityModel::ACTIVITY_STOP) {
            // 已终止
            $status = self::STATUS_STOP;
        } else {
            // 已发布
            if ($begin_time > MILLI_TIME) {
                // 未开始
                $status = self::STATUS_NOT_START;
            } elseif ($begin_time <= MILLI_TIME && ($end_time > MILLI_TIME || $end_time == 0)) {
                // 进行中
                $status = self::STATUS_ING;
            } else {
                // 已结束
                $status = self::STATUS_END;
            }
        }

        return $status;
    }

    /**
     *  查询活动信息
     *  $ac_id   [int] 活动状态
     *  return [array]
     */
    public function activity_info($ac_id = 0)
    {
        //获取活动状态
        $activity = $this->get($ac_id);

        if (empty($activity)) {

            E('_ERR_ARTICLE_NOT_FOUND');
        }
        //转化活动状态
        $activity['activity_status'] = $this->activity_status($activity['activity_status'], $activity['begin_time'],
            $activity['end_time']);

        return $activity;
    }

    /**
     * 前端获取活动列表
     * @param array $params 列表其提交的参数
     * @param array $right 当前用户部门，标签，职位
     * @return mixed
     */
    public function get_list_active($params = array(), $right = array())
    {
        $uid = $right['memID'];
        // 默认值
        $page = !empty($params['page']) ? intval($params['page']) : self::DEFAULT_PAGE;
        $limit = !empty($params['limit']) ? intval($params['limit']) : self::DEFAULT_LIMIT;
        // 分页
        list($start, $limit) = page_limit($page, $limit);

        //  按照发布时间排序
        $order_option = array('publish_time' => 'DESC');

        $data = array();
        $data['right'] = $right;
        // 获取记录总数
        $total = $this->_d->count_by_active($data);
        // 获取列表数据
        $list = array();
        if ($total > 0) {
            $list = $this->_d->list_by_active($data, array($start, $limit), $order_option);
        }
        // 组装返回数据
        $result['total'] = intval($total);
        $result['limit'] = intval($limit);
        $result['page'] = intval($page);
        $result['list'] = $this->activity_param($list, $uid);

        return $result;
    }

    /**
     * 更新收藏状态
     *
     * @param string $ids 数据ID，逗号分割的字符串
     *
     */
    public function update_collection($ids)
    {

        $url = convertUrl(QY_DOMAIN . '/Public/Rpc/Collection/CollectionUpdate');

        $params = [
            'uid' => '',
            'app' => 'activity',
            'dataId' => $ids
        ];

        $res = \Com\Rpc::phprpc($url)->invoke('Index', $params);

        return $res;
    }
}
