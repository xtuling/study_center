<?php
/**
 * CommentService.class.php
 * 活动评论信息表
 * @author: daijun
 * @copyright: vchangyi.com
 */

namespace Common\Service;

use Common\Common\User;
use Common\Model\ActivityModel;
use Common\Model\AttachmentModel;
use Common\Model\CommentModel;
use Common\Model\LikeModel;
use Common\Model\RightModel;

class CommentService extends AbstractService
{

    // 可以进行删除操作
    const IS_DEL_OK = 1;

    // 不可以进行删除
    const IS_DEL_NO = 0;

    // 已点赞
    const IS_LIKE_OK = 1;

    // 未点赞
    const IS_LIKE_NO = 0;

    // 回复每页条数
    const REPLY_LIMIT = 5;

    // 点赞类型为回复
    const COMMENT_TYPE = 2;

    // 上传图片个数最大值
    const IMAGES_COUNT = 9;

    // 一级评论
    const FIRST_COMMENT = 0;

    // 回复评论加权数
    const COMMENT_NUM = 1;

    // 默认获取点赞数
    const DEFAULT_LIKE_LIMIT = 12;

    /**
     * @var  User 用户公用类
     */
    protected $_user;

    /**
     * @var  LikeModel 点赞数据model
     */
    protected $_d_like;

    /**
     * @var  ActivityModel 活动数据model
     */
    protected $_d_activity;

    /**
     * @var  AttachmentModel 附件数据model
     */
    protected $_d_attach;

    /**
     * @var  RightModel 部门用户model
     */
    protected $_d_right;

    // 构造方法
    public function __construct()
    {
        $this->_user = new User();

        $this->_d = new CommentModel();

        $this->_d_like = new LikeModel();

        $this->_d_activity = new ActivityModel();

        $this->_d_attach = new AttachmentModel();

        $this->_d_right = new RightModel();

        parent::__construct();
    }

    /**
     * 查询回复评论信息
     * @param $params array 查条件参数
     * @param $page_option array 分页参数
     * @param $uid String 用户UID
     * @return array|bool
     */
    public function replay_list($params, $page_option, $uid)
    {
        $comment_id = intval($params['comment_id']);
        // 判断评论ID是否存在
        if (!$comment_id) {
            E('_ERR_AC_REPLAY_EMPTY');

            return false;
        }
        // 查询评论信息
        $comment_info = $this->get($comment_id);
        if (empty($comment_info)) {
            E('_ERR_DATA_NOT_EXIST');

            return false;
        }
        // 查询发布评论的用户信息
        $user_info = $this->_user->getByUid($comment_info['uid']);
        $info = array(
            'uid' => $user_info['memUid'],
            'username' => $user_info['memUsername'],
            'avatar' => $this->pic_thumbs($user_info['memFace']),
            'content' => $comment_info['content'],
            'created' => $comment_info['created'],
            'likes' => intval($comment_info['likes']),
            'replys' => intval($comment_info['replys']),
        );

        //判断活动是否可以点赞
        $mylikecount = $this->_d_like->count_by_conds(array(
            'uid' => $uid,
            'cid' => $comment_id,
            'type' => self::COMMENT_TYPE,
        ));

        $is_like = $mylikecount ? self::IS_LIKE_OK : self::IS_LIKE_NO;

        $info['is_like'] = $is_like;
        // 获取评论点赞列表
        $info['like_list'] = $this->like_list($comment_id);

        // 获取评论附件列表
        $info['img_list'] = $this->img_list($comment_id);

        //查询回复总条数
        $conds = array('parent_id' => $comment_id);
        $order_option = array('created' => 'asc');

        $total = $this->count_by_conds($conds);

        $list = array();
        if ($total > 0) {
            //查询回复列表
            $list = $this->list_by_conds($conds, $page_option, $order_option);

            if ($list) {
                // 回复列表数据格式化
                $list = $this->format_comment_list($list, $uid);
            }
        }

        $info['total'] = intval($total);
        $info['list'] = $list;

        return $info;
    }

    /**
     * 获取评论图片列表
     * @param $comment_id int 评论ID
     * @return array
     */
    protected function img_list($comment_id)
    {
        // 排序
        $order_option = array('created' => 'DESC');

        $list = $this->_d_attach->list_by_conds(
            array('cid' => $comment_id),
            null,
            $order_option
        );

        $images = array();
        foreach ($list as $k => $v) {
            $images[] = array(
                'atId' => $v['at_id'],
                'imgUrl' => imgUrl($v['at_id']),
            );
        }

        return $images;
    }

    /**
     * 获取评论点赞列表int
     * @param  $comment_id int 评论ID
     * @return array
     */
    public function like_list($comment_id)
    {
        // 排序
        $order_option = array('created' => 'DESC');

        $user = array();
        $like_list = $this->_d_like->list_by_conds(
            array('cid' => $comment_id, 'type' => self::COMMENT_TYPE),
            null,
            $order_option
        );

        $user_arr = array();
        if (!empty($like_list)) {

            $uids = array_column($like_list, 'uid');
            // 查询用户集合
            $users = $this->_user->listAll(array('memUids' => $uids));

            // 获取被删除的用户信息
            $this->user_list($users, $uids);

            $user_list = array_combine_by_key($users, 'memUid');

            // 格式化点赞列表数据
            foreach ($like_list as $key => $val) {
                $value = array();

                $value['created'] = $val['created'];
                $value['like_id'] = $val['like_id'];
                $value['uid'] = $val['uid'];
                $value['username'] = $user_list[$val['uid']]['memUsername'];
                $value['avatar'] = $this->pic_thumbs($user_list[$val['uid']]['memFace']);

                $user_arr[] = $value;
            }

        }

        return $user_arr;
    }

    /**
     * 获取评论列表（后台）
     * @param array $result 返回活动信息
     * @param array $reqData 请求数据
     * @return bool
     */
    public function comment_list(&$result, $reqData)
    {

        $ac_id = intval($reqData['ac_id']);
        $parent_id = intval($reqData['parent_id']);

        if (empty($ac_id)) {
            E('_ERR_AC_ID_EMPTY');

            return false;
        }

        // 默认值
        $page = isset($reqData['page']) ? intval($reqData['page']) : self::DEFAULT_PAGE;
        // 默认每页条数
        if ($parent_id > 0) {

            $limit = isset($reqData['limit']) ? intval($reqData['limit']) : self::REPLY_LIMIT;
        } else {

            $limit = isset($reqData['limit']) ? intval($reqData['limit']) : self::DEFAULT_LIMIT;
        }

        // 分页
        list($start, $limit) = page_limit($page, $limit);

        // 排序
        $order_option = array('created' => 'ASC');

        // 分页
        $page_option = array($start, $limit);

        // 查询总记录数
        $total = $this->_d->count_by_conds(array('ac_id' => $ac_id, 'parent_id' => $parent_id));

        // 查询列表
        $list = array();
        if ($total > 0) {

            $comm_list = $this->_d->list_by_conds(array('ac_id' => $ac_id, 'parent_id' => $parent_id), $page_option,
                $order_option);
            // 格式化列表数据
            $list = $this->format_comment_list_admin($comm_list);
        }

        $result = array(
            'page' => $page,
            'limit' => $limit,
            'total' => (int)$total,
            'lists' => $list,
        );

        return true;
    }

    /**
     * 格式化评论列表数据（前台）
     * @param array $comm_list 评论数据
     * @param string $uid 用户ID
     * @return array
     */
    public function format_comment_list($comm_list = array(), $uid = '')
    {

        // 获取用户ID集合
        $uids = array_column($comm_list, 'uid');

        // 查询用户列表
        $user_list = $this->_user->listAll(array('memUids' => $uids));

        // 获取被删除的用户信息
        $this->user_list($user_list, $uids);

        // 将用户列表转换为以uid为key的二维数组
        $users = array_combine_by_key($user_list, 'memUid');

        // 查询该用户点赞的所有评论
        $like_list = $this->_d_like->list_by_conds(array('type' => LikeModel::TYPE_COMMENT, 'uid' => $uid));

        // 点赞的评论ID集合
        $like_cids = array_column($like_list, 'cid');

        // 获取评论ID集合
        $comment_ids = array_column($comm_list, 'comment_id');

        $attach_list = array();
        if (!empty($comment_ids)) {
            // 查询附件集合
            $attach_list = $this->_d_attach->list_by_conds(array('cid' => $comment_ids));
        }

        foreach ($comm_list as &$v) {

            // 转换数据返回格式
            $v['comment_id'] = intval($v['comment_id']);
            $v['likes'] = intval($v['likes']);
            $v['comments'] = intval($v['replys']);
            $v['is_attach'] = intval($v['is_attach']);

            // 组装用户数据
            $v['username'] = $users[$v['uid']]['memUsername'];
            $v['avatar'] = $this->pic_thumbs($users[$v['uid']]['memFace']);

            // 格式化时间
            $v['created'] = $this->get_time($v['created']);

            // 组装是否可删除标识
            if ($v['uid'] == $uid) {
                $v['is_del'] = self::IS_DEL_OK;
            } else {
                $v['is_del'] = self::IS_DEL_NO;
            }

            // 组装是否已点赞标识
            if (in_array($v['comment_id'], $like_cids)) {
                $v['is_like'] = self::IS_LIKE_OK;
            } else {
                $v['is_like'] = self::IS_LIKE_NO;
            }

            // 获取评论图片信息
            $images = array();
            foreach ($attach_list as $_v) {
                // 如果是本评论的附件
                if ($_v['cid'] == $v['comment_id']) {
                    $images[] = array(
                        'atId' => $_v['at_id'],
                        'imgUrl' => imgUrl($_v['at_id']),
                    );
                    // 清除该条记录
                    unset($_v);
                }
            }

            $v['images'] = $images;

            // 删除多余数据
            unset(
                $v['parent_id'],
                $v['ac_id'],
                $v['replys'],
                $v['domain'],
                $v['status'],
                $v['updated'],
                $v['deleted']
            );

        }

        return $comm_list;
    }

    /**
     * 格式化评论列表数据（后台）
     * @param $comm_list array 评论列表数据
     * @return array
     */
    public function format_comment_list_admin(&$comm_list)
    {

        // 获取用户ID集合
        $uids = array_column($comm_list, 'uid');

        // 查询用户列表
        $user_list = $this->_user->listAll(array('memUids' => $uids));

        // 获取被删除的用户信息
        $this->user_list($user_list, $uids);

        // 将用户列表转换为以uid为key的二维数组
        $users = array_combine_by_key($user_list, 'memUid');

        // 获取评论ID集合
        $comment_ids = array_column($comm_list, 'comment_id');

        $attach_list = array();
        if (!empty($comment_ids)) {
            // 查询附件集合
            $attach_list = $this->_d_attach->list_by_conds(array('cid' => $comment_ids));
        }

        foreach ($comm_list as &$v) {

            $v['ac_id'] = (int)$v['ac_id'];
            $v['comment_id'] = (int)$v['comment_id'];
            $v['parent_id'] = (int)$v['parent_id'];
            $v['likes'] = (int)$v['likes'];
            $v['replys'] = (int)$v['replys'];
            $v['username'] = (string)$users[$v['uid']]['memUsername'];
            $v['avatar'] = $this->pic_thumbs($users[$v['uid']]['memFace']);

            // 获取评论图片信息
            $images = array();

            foreach ($attach_list as $_v) {
                // 如果是本评论的附件
                if ($_v['cid'] == $v['comment_id']) {
                    $images[] = array(
                        'atId' => $_v['at_id'],
                        'imgUrl' => imgUrl($_v['at_id']),
                    );
                    // 清除该条记录
                    unset($_v);
                }
            }

            $v['images'] = $images;

            // 获取一级评论的回复列表
            if ($v['parent_id'] == self::FIRST_COMMENT && $v['replys'] > 0) {

                $v['reply_lists'] = $this->_d->list_by_conds(
                    array(
                        'ac_id' => $v['ac_id'],
                        'parent_id' => $v['comment_id'],
                    ),
                    array(
                        self::FIRST_COMMENT,
                        self::REPLY_LIMIT,
                    ),
                    array(
                        'created' => 'DESC',
                    ),
                    'comment_id,uid,content,is_attach,likes,replys,created'
                );
            }

            // 获取回复用户信息及附件信息
            if ($v['reply_lists']) {

                // 获取回复用户ID集合
                $reply_uids = array_column($v['reply_lists'], 'uid');

                // 查询回复用户列表
                $reply_user_list = $this->_user->listAll(array('memUids' => $reply_uids));

                // 获取被删除的用户信息
                $this->user_list($reply_user_list, $reply_uids);

                // 将用户列表转换为以uid为key的二维数组
                $reply_users = array_combine_by_key($reply_user_list, 'memUid');

                // 获取回复ID集合
                $reply_ids = array_column($v['reply_lists'], 'comment_id');

                $reply_attach_list = array();
                if (!empty($reply_ids)) {
                    // 查询附件集合
                    $reply_attach_list = $this->_d_attach->list_by_conds(array('cid' => $reply_ids));
                }

                foreach ($v['reply_lists'] as &$reply) {

                    $reply['comment_id'] = (int)$reply['comment_id'];
                    $reply['likes'] = (int)$reply['likes'];
                    $reply['replys'] = (int)$reply['replys'];
                    $reply['username'] = (string)$reply_users[$reply['uid']]['memUsername'];
                    $reply['avatar'] = $this->pic_thumbs($reply_users[$reply['uid']]['memFace']);

                    // 获取评论图片信息
                    $reply_images = array();

                    foreach ($reply_attach_list as $_reply) {
                        // 如果是本评论的附件
                        if ($_reply['cid'] == $reply['comment_id']) {
                            $reply_images[] = array(
                                'atId' => $_reply['at_id'],
                                'imgUrl' => imgUrl($_reply['at_id']),
                            );
                            // 清除该条记录
                            unset($_reply);
                        }
                    }

                    $reply['images'] = $reply_images;
                }
            }

            // 删除多余数据
            unset($v['domain'], $v['status'], $v['updated'], $v['deleted']);

        }

        return $comm_list;
    }

    /**
     * 前端删除我的评论
     * @param $comment_id
     * @param string $uid 前端登录用户ID
     * @return bool
     */
    public function del_comment($comment_id, $uid = '')
    {
        // 查询评论数据
        $data = $this->_d->get($comment_id);

        // 如果数据不存在
        if (empty($data)) {
            E('_ERR_DATA_NOT_EXIST');

            return false;
        }

        // 如果不是自己发布的，则不能删除
        if (!empty($uid) && ($uid != $data['uid'])) {
            E('_ERR_COMMENT_FORYOU');

            return false;
        }

        try {
            // 开始事务
            $this->start_trans();

            // 删除评论
            $this->_d->delete($comment_id);

            //删除评论附件
            $this->_d_attach->delete_by_conds(array('cid' => $comment_id));

            //删除评论点赞
            $this->_d_like->delete_by_conds(array('cid' => $comment_id));

            // 如果评论是一级评论
            if ($data['parent_id'] == self::FIRST_COMMENT) {

                // 查询该评论下的所有回复
                $replay_list = $this->_d->list_by_conds(array('parent_id' => $comment_id));

                // 获取该评论回复的ID集合
                $comment_ids = array_column($replay_list, 'comment_id');

                if (!empty($comment_ids)) {
                    // 删除回复
                    $this->_d->delete_by_conds(array('parent_id' => $comment_id));

                    //删除回复附件
                    $this->_d_attach->delete_by_conds(array('cid in (?)' => $comment_ids));

                    //删除回复点赞
                    $this->_d_like->delete_by_conds(array('cid in (?)' => $comment_ids));
                }

                // 此处给该活动的评论数减1
                $this->_d_activity->update($data['ac_id'], array('comments = comments-?' => 1));

            } else {
                // 此处给该评论的评论数减1
                $this->_d->update($data['parent_id'], array('replys = replys-?' => 1));

            }

            // 提交事务
            $this->commit();

        } catch (\Think\Exception $e) {
            \Think\Log::record($e);
            // 事务回滚
            $this->_set_error($e->getMessage(), $e->getCode());
            $this->rollback();

            return false;

        } catch (\Exception $e) {

            \Think\Log::record($e);
            $this->_set_error($e->getMessage(), $e->getCode());
            // 事务回滚
            $this->rollback();

            return false;
        }

        return true;
    }

    /**
     * 发布评论和回复评论接口
     * @param array $data
     * @param string $uid
     * @return mixed
     */
    public function publish_comment($data = array(), $uid = '')
    {
        $ac_id = intval($data['ac_id']);
        $piclist = $data['images'];
        $content = trim($data['content']);
        $comment_id = intval($data['comment_id']);
        $images = array_column($piclist, 'atId');

        //活动ID必须大于0
        if (!$ac_id) {

            E('_ERR_AC_ID_EMPTY');
        }
        //判断评论内容长度
        if (!\Com\Validator::is_len_in_range($content, 2, 140, 'utf-8')) {

            E('_ERR_AC_COMMENT_LENGTH');
        }
        //评论不能自己评论自己的
        /*if ($comment_id) {

        $comment = $this->_d->get($comment_id);
        if ($comment['uid'] == $uid) {

        E('_ERR_AC_COMMENT_OUR');
        }
        }*/

        $pic_total = count($images);
        $is_attach = $pic_total ? 1 : 0;

        // 上传图片数量是否大于上传图片个数最大值
        if ($pic_total > self::IMAGES_COUNT) {

            E('_ERR_AC_IMAGE_LENGTH');
        }

        try {
            // 开始事务
            $this->_d->start_trans();
            //插入评论信息
            $news_cid = $this->_d->insert(array(
                'ac_id' => $ac_id,
                'parent_id' => $comment_id,
                'content' => $content,
                'is_attach' => $is_attach,
                'uid' => $uid,
            ));

            if ($is_attach) {
                // 组装附件信息数组
                $attach_list = array();
                foreach ($images as $k => $v) {
                    if (!empty(trim($v))) {
                        $attach_list[] = array('cid' => $news_cid, 'at_id' => $v);
                    }
                }
                // 插入附件信息
                if (!empty($attach_list)) {
                    $this->_d_attach->insert_all($attach_list);
                }
            }
            if (!$comment_id) {
                //更新活动评论数
                $this->_d_activity->update($ac_id, array("comments=comments+(?)" => self::COMMENT_NUM));
            } else {
                //评论回复
                $this->_d->update($comment_id, array("replys=replys+(?)" => self::COMMENT_NUM));
            }

            //评论消息回复
            $this->comment_send_msg($ac_id, $comment_id, $content);

            //提交修改
            $this->_d->commit();

        } catch (\Think\Exception $e) {
            \Think\Log::record($e);
            $this->_d->rollback();
            E('_ERR_ADD_LIKE_FAILED');

        } catch (\Exception $e) {
            \Think\Log::record($e);
            $this->_d->rollback();
            E('_ERR_ADD_LIKE_FAILED');

        }

        return true;

    }

    /**
     * 前端回复评论消息
     * @param int $ac_id 活动ID
     * @param int $comment_id 评论ID(有则评论回复)
     * @param string $reply_content 回复内容
     * @return bool
     */
    public function comment_send_msg($ac_id = 0, $comment_id = 0, $reply_content = '')
    {
        //批判断活动ID是否存在
        if (!$ac_id) {

            return false;
        }
        $activity = $this->_d_activity->get($ac_id);
        $data = array();
        //判断是否发送消息
        if ($activity['is_notice']) {

            //评论回复消息通知
            if ($comment_id) {

                //查询要回复评论的UID
                $comment = $this->_d->get($comment_id);

                //给评论人提醒有回复
                $data['uids'] = $comment['uid'];
                $data['subject'] = $activity['subject'];
                $data['reply_content'] = $reply_content;
                $data['publish_time'] = MILLI_TIME;
                $data['cid'] = $comment_id;

                $this->send_msg($data, self::MSG_COMMENT_REPLY);
            }
        }

        return false;

    }
}
