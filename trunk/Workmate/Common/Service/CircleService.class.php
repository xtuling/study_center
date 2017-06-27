<?php
/**
 * 同事圈信息表
 * User: 代军
 * Date: 2017-04-24
 */
namespace Common\Service;

use Common\Common\User;
use Common\Model\AttachmentModel;
use Common\Model\CircleModel;
use Common\Model\LikeModel;
use VcySDK\Service;

class CircleService extends AbstractService
{

    // 审核中
    const AUDIT_ING = 0;
    // 审核通过
    const AUDIT_OK = 1;
    // 审核驳回
    const AUDIT_NO = 2;


    // 系统审核
    const AUDIT_SYSTEM_TYPE = 1;

    // 后台审核
    const AUDIT_ADMIN_TYPE = 2;


    // 话题最大字符长度
    const FONT_CIRCLE_LENGTH = 500;

    // 评论最大字符长度
    const FONT_COMMENT_LENGTH = 140;

    // 附件最大个数
    const AT_MAX_MUN = 9;

    // 当前用户不是是发起人
    const UN_SPONSOR = 0;

    // 当前用户是发起人
    const SPONSOR = 1;

    // 当前用户没有点过赞
    const UN_LIKE = 0;

    // 当前用户点过赞
    const LIKE = 1;

    // 当前用户不能删除
    const UN_DEL = 0;

    // 当前用户可删除
    const DEL = 1;

    // 含有附件
    const HAVE_ATTACH = 1;

    // 话题搜索全部
    const CIRCLE = 3;

    /**
     * 附件数据库操作类实例
     *
     * @type null|
     */
    protected $_d_att = null;

    /**
     * 点赞操作类实例
     *
     * @type null|
     */
    protected $_d_like = null;

    // 构造方法
    public function __construct()
    {
        $this->_d = new CircleModel();

        $this->_d_att = new AttachmentModel();

        $this->_d_like = new LikeModel();

        parent::__construct();
    }

    /**
     * 格式化后台帖子列表数据
     * @param $list
     * @return bool
     */
    public function format_list_data(&$list)
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

            // 获取帖子ID集合
            $ids = array_column($list, 'id');

            // 获取点赞总数
            $like_data = $this->_d_like->get_like_total($ids);
            // 将点赞数据转换成以帖子ID为key的二维数组
            $like_data = array_combine_by_key($like_data, 'cid');

            // 获取通过审核评论数
            $commont_ok_list = $this->_d->get_comment_num($ids, self::AUDIT_OK);
            // 将评论数据转换成以帖子ID为key的二维数组
            $commont_ok_data = array_combine_by_key($commont_ok_list, 'pid');

            // 获取待审核评论数
            $commont_ing_list = $this->_d->get_comment_num($ids, self::AUDIT_ING);
            // 将评论数据转换成以帖子ID为key的二维数组
            $commont_ing_data = array_combine_by_key($commont_ing_list, 'pid');

            // 循环给列表中字段赋值
            foreach ($list as &$v) {
                $v['username'] = strval($v['username']);
                $v['like_total'] = intval($like_data[$v['id']]['total']);
                $v['commont_ok_total'] = intval($commont_ok_data[$v['id']]['total']);
                $v['commont_ing_total'] = intval($commont_ing_data[$v['id']]['total']);
                $v['audit_state'] = intval($v['audit_state']);
                $v['id'] = intval($v['id']);
                $v['audit_time'] = strval($v['a_time']);
                // 清除多余数据
                unset(
                    $v['a_time'],
                    $v['audit_type'],
                    $v['is_attach'],
                    $v['pid'],
                    $v['domain'],
                    $v['status'],
                    $v['updated'],
                    $v['deleted']
                );

            }
        }

        return true;
    }


    /**
     * 后台审核评论推送消息
     * @param $data 评论详情
     * @param $param 审核参数
     * @param $user  审核人信息
     * @return bool
     */
    public function send_comment_msg_admin($data, $param, $user)
    {
        // 初始化类型
        $msg_type = 0;

        // 初始化消息参数
        $msg_params = array();
        $msg_params['description'] = $data['content'];
        $msg_params['user'] = $user['eaRealname'];
        $msg_params['uids'] = array(array('memID' => $data['uid']));
        $msg_params['id'] = $data['id'];

        if (self::AUDIT_OK == $param['audit_state']) {
            // 评论审核通过
            $msg_type = self::MSG_COMMENT_ADOPT;

            $msg_params['id'] = $data['pid'];

        } else {
            if (self::AUDIT_NO == $param['audit_state']) {
                // 评论审核驳回
                $msg_type = self::MSG_COMMENT_REFUSE;
            }
        }

        $this->send_msg($msg_params, $msg_type);

        // 给话题发布人推送消息
        if (self::AUDIT_OK == $param['audit_state']) {
            $msg_type = self::MSG_COMMENT;

            // 查询评论对应的话题信息
            $h_data = $this->get($data['pid']);

            // 获取用户详情
            $user_info = User::instance()->getByUid($data['uid']);

            $msg_params = array();
            $msg_params['description'] = $data['content'];
            $msg_params['name'] = $user_info['memUsername'];
            $msg_params['uids'] = array(array('memID' => $h_data['uid']));
            $msg_params['id'] = $data['pid'];

            $this->send_msg($msg_params, $msg_type);
        }

        return true;

    }


    /**
     * 后台审核话题推送消息
     * @param $data 话题详情
     * @param $params 审核参数
     * @param $user  审核人信息
     * @return bool
     */
    public function send_msg_admin($data, $params, $user)
    {
        $msg_type = 0;
        if (self::AUDIT_OK == $params['audit_state']) {
            // 话题审核通过
            $msg_type = self::MSG_CIRCLE_ADOPT;
        } else {
            if (self::AUDIT_NO == $params['audit_state']) {
                // 话题审核驳回
                $msg_type = self::MSG_CIRCLE_REFUSE;
            }
        }

        $msg_params = array();
        $msg_params['description'] = $data['content'];
        $msg_params['user'] = $user['eaRealname'];
        $msg_params['uids'] = array(array('memID' => $data['uid']));
        $msg_params['id'] = $data['id'];

        $this->send_msg($msg_params, $msg_type);

        return true;
    }


    /**
     * 根据用户名获取用户ID集合
     * @param $uname
     * @return array
     */
    public function get_uids_by_uname($uname)
    {
        // 根据发帖人姓名查询发帖人ID
        $member = new User(Service::instance());
        $user_list = $member->listByConds(array('memUsername' => $uname, 'memSubscribeStatus' => 1));

        $uids = array();
        // 如果获取的用户信息不为空
        if (!empty($user_list)) {
            // 将用户数组 转换成 以用户ID为值的一维数组
            $uids = array_column($user_list['list'], 'memUid');
        }

        return $uids;
    }

    /**
     * 组装后台帖子列表查询条件
     * @param $params 传入参数
     * @return Array
     */
    public function get_where_conds($params)
    {
        // 标识主帖
        $where['pid'] = 0;

        // 如果发帖人搜索条件不为空
        if (!empty($params['username'])) {
            $where['username like ?'] = '%' . $params['username'] . '%';
        }

        // 如果帖子状态搜索条件存在
        if (intval($params['audit_state']) < self::CIRCLE) {
            $where['audit_state'] = $params['audit_state'];
        }

        return $where;
    }

    /**
     * 格式化后台帖子评论列表数据
     * @param $list
     * @return bool
     */
    public function format_comment_list_data(&$list)
    {
        if (!empty($list)) {

            // 取出列表的用户ID集合
            $uids = array_column($list, 'uid');

            // 查询用户集合数据
            $member = new User(Service::instance());
            $user_list = $member->listByConds(array('memUids' => $uids), 1, count($uids));

            // 将用户数据转换成以用户ID为key的二维数组
            $user_data = array_combine_by_key($user_list['list'], 'memUid');

            // 获取评论ID集合
            $ids = array_column($list, 'id');
            // 获取点赞总数
            $like_data = $this->_d_like->get_like_total($ids);
            // 将点赞数据转换成以评论ID为key的二维数组
            $like_data = array_combine_by_key($like_data, 'cid');

            // 循环给列表中字段赋值
            foreach ($list as &$v) {
                $v['username'] = strval($user_data[$v['uid']]['memUsername']);
                $v['avatar'] = strval($user_data[$v['uid']]['memFace']);
                $v['like_total'] = intval($like_data[$v['id']]['total']);
                // 清除多余数据
                unset(
                    $v['domain'],
                    $v['status'],
                    $v['updated'],
                    $v['deleted']
                );
            }
        }

        return true;
    }


    /**
     * 格式化帖子详情
     * @param $data
     * @return mixed
     */
    public function format_detail(&$data)
    {
        // 获取发帖人用户名及头像
        $member = new User(Service::instance());
        $user_data = $member->getByUid($data['uid']);

        $data['avatar'] = $this->memFace($user_data['memFace']);

        // 获取帖子图片附件数据
        $images = array();
        // 如果帖子含有附件
        if ($data['is_attach'] == self::HAVE_ATTACH) {
            // 查询附件
            $attach_list = $this->_d_att->list_by_conds(array('cid' => $data['id']));
            // 格式化图片数据
            $image_list = $this->format_att_images($attach_list);
            $images = $image_list[$data['id']];
        }
        $data['images'] = $images;
        // 获取点赞以及评论数量
        $like_total = 0;  // 点赞总数
        $commont_ok_total = 0; // 评论审核通过数
        $commont_no_total = 0; // 评论审核驳回数
        $commont_ing_total = 0; // 评论待审核数

        // 如果主贴已审核通过
        if ($data['audit_state'] == self::AUDIT_OK) {

            // 查询帖子点赞总数
            $like_total = $this->_d_like->count_by_conds(array('cid' => $data['id']));
            // 查询评论审核通过数
            $commont_ok_total = $this->_d->count_by_conds(array('pid' => $data['id'], 'audit_state' => self::AUDIT_OK));
            // 查询评论审核驳回数
            $commont_no_total = $this->_d->count_by_conds(array('pid' => $data['id'], 'audit_state' => self::AUDIT_NO));
            // 查询评论待审核数
            $commont_ing_total = $this->_d->count_by_conds(array(
                'pid' => $data['id'],
                'audit_state' => self::AUDIT_ING
            ));
        }

        $data['id'] = intval($data['id']);
        $data['audit_state'] = intval($data['audit_state ']);
        // 给额外参数赋值
        $data['like_total'] = $like_total;
        $data['commont_ok_total'] = $commont_ok_total;
        $data['commont_no_total'] = $commont_no_total;
        $data['commont_ing_total'] = $commont_ing_total;
        $data['content'] = $this->Enter($data['content']);


        // 删除多余数据
        unset(
            $data['pid'],
            $data['audit_type'],
            $data['uid'],
            $data['is_attach'],
            $data['audit_uid'],
            $data['domain'],
            $data['status'],
            $data['updated'],
            $data['deleted']

        );

        return $data;
    }

    /*
    * 【微信端】发布朋友圈验证数据是否合法
    * @param array $params
    *                  + String content 发布内容
    *                  + Array images  图片列表
    *                              + String atId 附件ID
    *                              + String atFilename 附件名称
    *                              + String atFilesize 附件大小
    *                              + String uid 当前用户UID
    * @return bool
    */
    public function publish_validate($params = array(), $uid = '')
    {
        // 如果是外部人员
        if (empty($uid)) {

            $this->_set_error('_EMPTY_MEM_CIRCLE');

            return false;
        }

        // 去除html标签以及换行标签
        $content = strip_tags($params['content']);

        // 如果内容为空
        if (empty($content)) {

            $this->_set_error('_EMPTY_CONTENT');

            return false;
        }

        // 如果内容存在且字段内容大于500个字
        if (!empty($content) && $this->utf8_strlen($content) > self::FONT_CIRCLE_LENGTH) {

            $this->_set_error('_ERR_CONTENT_MAX');

            return false;
        }

        // 如果存在图片且最大个数大于指定个数
        if (!empty($params['images']) && count(array_column($params['images'], 'atId')) > self::AT_MAX_MUN) {

            $this->_set_error('_ERR_IMG_MAX_MUN');

            return false;
        }

        return true;
    }

    /**
     * 【微信端】 发布朋友圈
     * @param array $params
     *                  + String content 发布内容
     *                  + Array images  图片列表
     *                              + String atId 附件ID
     *                              + String atFilename 附件名称
     *                              + String atFilesize 附件大小
     * @param Array $user 用户信息
     * @param String $release 是否开启话题审核
     * @return bool
     */
    public function insert_data($params = array(), $user = array(), $release = '')
    {

        try {
            $this->start_trans();
            // 重新组装数据
            $data = array(
                'uid' => $user['memUid'],
                'username' => $user['memUsername'],
                'content' => strip_tags($params['content']),
                'is_attach' => !empty($params['images']) ? CircleModel::ATTACH : CircleModel::NOT_ATTACH,
            );

            // 如果未开启审核
            if (empty($release)) {

                $data = array_merge($data, array(
                    'audit_state' => self::AUDIT_OK,
                    'audit_time' => MILLI_TIME,
                    'audit_type' => self::AUDIT_SYSTEM_TYPE
                ));
            }

            // 写入数据到朋友圈
            $id = $this->_d->insert($data);

            // 附件数据初始化
            $att_data = array();

            // 遍历图片数组
            foreach ($params['images'] as $key => $v) {

                // 组装附件数据
                $att_data[] = array(
                    'cid' => $id,
                    'atid' => $v['atId']
                );
            }

            // 插入多条数据到附件表
            $this->_d_att->insert_all($att_data);

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

        // 组装发送消息数据
        $msg_data = array(
            'uids' => array(
                array(
                    'memID' => $user['memUid']
                )
            ),
            'id' => $id,
            'description' => $params['content']
        );

        // 未开启审核功能
        if (empty($release)) {

            // 发送消息
            $this->send_msg($msg_data, self::MSG_CIRCLE_ADOPT);
        } else {

            // 发送消息
            $this->send_msg($msg_data, self::MSG_CIRCLE_PUBLISH);
        }

        return $id;
    }


    /**
     * 计算符串长度
     * @param string $string 字符串
     * @return int
     */
    public function utf8_strlen($string = "")
    {
        // 将字符串分解为单元
        preg_match_all("/./us", $string, $match);

        // 返回单元个数
        return count($match[0]);
    }

    /**
     * 【微信端】格式化同事圈列表
     * @param array $data 列表数据
     * @param string $uid 当前用户UID
     * @return array
     */
    public function circle_list_format($data = array(), $uid = '')
    {
        // 实例化
        $list = array();

        if (empty($data)) {

            return $list;
        }

        // 取出列表的用户ID集合
        $uids = array_column($data, 'uid');

        // 查询用户集合数据
        $member = new User(Service::instance());
        $user_list = $member->listByConds(array('memUids' => $uids), 1, count($uids));

        // 获取已查询到UID列表
        $uid_list = array_column($user_list['list'], 'memUid');

        // 获取全部用户列表
        $this->user_list($user_list['list'], $uids, $uid_list);

        // 将用户数据转换成以用户ID为key的二维数组
        $user_data = array_combine_by_key($user_list['list'], 'memUid');

        $ids = array_column($data, 'id');

        // 获取点赞总数
        $like_data = $this->_d_like->get_like_total($ids);

        // 将点赞数据转换成以帖子ID为key的二维数组
        $like_data = array_combine_by_key($like_data, 'cid');

        // 获取通过审核评论数
        $commont_ok_list = $this->_d->get_comment_num($ids, self::AUDIT_OK);

        // 将评论数据转换成以帖子ID为key的二维数组
        $commont_ok_data = array_combine_by_key($commont_ok_list, 'pid');

        // 获取当前用户点过赞的列表
        $like_list = $this->_d_like->list_by_conds(array('uid' => $uid));

        // 当前用户点过赞的ID
        $my_likes = array_unique(array_column($like_list, 'cid'));

        // 获取所有附件列表
        $att_all_list = $this->_d_att->list_by_conds(array('cid' => $ids));

        // 获取图片列表
        $images = $this->format_att_images($att_all_list);

        // 遍历数据
        foreach ($data as $key => $v) {

            $list[] = array(
                'id' => intval($v['id']),
                'content' => $this->Enter($v['content']),
                'username' => strval($v['username']),
                'avatar' => strval($this->memFace($user_data[$v['uid']]['memFace'])),
                'like_total' => intval($like_data[$v['id']]['total']),
                'comment_total' => intval($commont_ok_data[$v['id']]['total']),
                'is_del' => $uid != $v['uid'] ? self::UN_DEL : self::DEL,
                'is_like' => in_array($v['id'], $my_likes) ? self::LIKE : self::UN_LIKE,
                'images' => !empty($images[$v['id']]) ? $images[$v['id']] : array(),
                'created' => !empty($v['audit_time']) ? $this->get_time($v['audit_time']) : $this->get_time($v['created'])
            );

        }

        return $list;
    }

    /**
     * 【微信端】 格式化前端话题详情
     * @param array $data 详情数据
     * @param string $uid 当前用户UID
     * @return array
     */
    public function format_info($data = array(), $uid = '')
    {

        // 初始化详情
        $info = array();

        if (empty($data)) {

            return $info;
        }

        // 查询用户集合数据
        $user = User::instance();
        $user_info = $user->getByUid($data['uid']);

        // 获取所有附件列表
        $att_list = $this->_d_att->list_by_conds(array('cid' => $data['id']));

        // 获取图片列表
        $images = $this->format_att_images($att_list);

        // 查看当前用户是否点赞过
        $like_total = $this->_d_like->count_by_conds(array('uid' => $uid, 'cid' => $data['id']));

        // 排序参数
        $order_by = array(
            'created' => 'DESC'
        );

        // 获取当前点赞列表
        $like_list = $this->_d_like->list_by_conds(array('cid' => $data['id']), self::DEFAULT_LIMIT, $order_by);

        // 初始化点赞列表
        $mem_users = array();

        // 如果有人点赞
        if (!empty($like_list)) {

            // 获取点赞人员UIDS
            $mem_uids = array_unique(array_filter(array_column($like_list, 'uid')));

            // 格式化人员列表
            $mem_list = $this->format_user($mem_uids);

            // 获取点赞列表
            foreach ($like_list as $key => $v) {

                $mem_users[] = array(
                    'username' => strval($mem_list[$v['uid']]['username']),
                    'avatar' => strval($mem_list[$v['uid']]['avatar']),
                    'uid' => strval($v['uid'])
                );
            }
        }

        // 获取收藏状态 xtong 2017年06月02日
        $url = convertUrl(QY_DOMAIN . '/Public/Rpc/Collection/CollectionStatus');

        $params =[
            'uid'=>$uid,
            'app'=>'workmate',
            'dataId'=>$data['id']
        ];

        $res = \Com\Rpc::phprpc($url)->invoke('Index',$params);

        // 组装详情
        $info = array(
            'id' => intval($data['id']),
            'content' => $this->Enter($data['content']),
            'uid' => strval($data['uid']),
            'username' => strval($data['username']),
            'avatar' => strval($this->memFace($user_info['memFace'])),
            'like_total' => intval($this->_d_like->count_by_conds(array('cid' => $data['id']), self::DEFAULT_LIMIT)),
            'like_list' => $mem_users,
            'is_del' => $uid != $data['uid'] ? self::UN_DEL : self::DEL,
            'is_like' => $like_total ? self::LIKE : self::UN_LIKE,
            'images' => !empty($images) ? $images[$data['id']] : array(),
            'created' => !empty($data['audit_time']) ? $this->get_time($data['audit_time']) : $this->get_time($data['created']),
            'is_collect' => json_decode($res,true)['collection']
        );

        return $info;
    }

    /**
     * 【微信端】 获取评论列表
     * @param array $conditions 查询条件
     * @param array $page_option 分页参数
     * @param array $order_by 排序字段
     * @return mixed
     */
    public function list_by_comment($conditions = array(), $page_option = array(), $order_by = array())
    {

        // 如果没有话题ID
        if (empty($conditions['pid'])) {

            return array();
        }

        return $this->_d->list_by_comment($conditions, $page_option, $order_by);
    }

    /**
     * 【微信端】 格式化前端评论列表
     * @param array $data 列表数据
     * @param string $uid 当前用户UID
     * @return array
     */
    public function commont_list_format($data = array(), $uid = '')
    {
        // 实例化
        $list = array();

        if (empty($data)) {

            return $list;
        }

        // 取出列表的用户ID集合
        $uids = array_column($data, 'uid');

        // 查询用户集合数据
        $member = new User(Service::instance());
        $user_list = $member->listByConds(array('memUids' => $uids), 1, count($uids));

        // 获取已查询到UID列表
        $uid_list = array_column($user_list['list'], 'memUid');

        // 获取全部用户列表
        $this->user_list($user_list['list'], $uids, $uid_list);

        // 将用户数据转换成以用户ID为key的二维数组
        $user_data = array_combine_by_key($user_list['list'], 'memUid');

        // 获取当前用户点过赞的列表
        $like_list = $this->_d_like->list_by_conds(array('uid' => $uid));

        // 当前用户点过赞的ID
        $my_likes = array_unique(array_column($like_list, 'cid'));

        // 遍历数据
        foreach ($data as $key => $v) {

            $list[] = array(
                'id' => intval($v['id']),
                'content' => $v['content'],
                'username' => strval($v['username']),
                'avatar' => strval($this->memFace($user_data[$v['uid']]['memFace'])),
                'like_total' => intval($v['like_total']),
                'is_del' => $uid != $v['uid'] ? self::UN_DEL : self::DEL,
                'is_like' => in_array($v['id'], $my_likes) ? self::LIKE : self::UN_LIKE,
                'created' => !empty($v['audit_time']) ? $this->get_time($v['audit_time']) : $this->get_time($v['created'])
            );
        }

        return $list;
    }

    /**
     * 【微信端】 格式化我的朋友圈详情
     * @param array $data 详情数据
     * @return array
     */
    public function format_my_circle($data = array())
    {

        // 初始化详情
        $info = array();

        if (empty($data)) {

            return $info;
        }

        // 查询用户集合数据
        $user = User::instance();
        $user_info = $user->getByUid($data['uid']);

        // 获取所有附件列表
        $att_list = $this->_d_att->list_by_conds(array('cid' => $data['id']));

        // 获取图片列表
        $images = $this->format_att_images($att_list);

        // 组装详情
        $info = array(
            'content' => $this->Enter($data['content']),
            'username' => strval($data['username']),
            'avatar' => strval($this->memFace($user_info['memFace'])),
            'images' => !empty($images) ? $images[$data['id']] : array(),
            'created' => $this->get_time($data['created']),
            'circle_status' => intval($data['audit_state'])
        );

        return $info;
    }

    /**
     * 【微信端】删除评论
     * @param string $id 评论或者话题ID
     * @param string $uid 当前用户UID
     * @return bool
     */
    public function del_comment($id = '', $uid = '')
    {

        // 如果ID为空
        if (empty($id)) {

            $this->_set_error('_EMPTY_COMMENT_ID');

            return false;
        }

        // 获取当前是否可以删除
        $info = $this->_d->get_by_conds(
            array(
                'id' => $id,
                'pid > ?' => CircleModel::CIRCLE_PID
            )
        );

        // 如果信息不存在
        if (!$info) {

            $this->_set_error('_EMPTY_ID_INFO');

            return false;
        }

        // 如果信息没有权限
        if ($info['uid'] != $uid) {

            $this->_set_error('_ERR_ID_NOT_AUTH_DEL');

            return false;
        }

        // 删除评论
        $this->_d->delete($id);

        return true;
    }

    /**
     * 【微信端】删除话题
     * @param string $id 评论或者话题ID
     * @param string $uid 当前用户UID
     * @return bool
     */
    public function del_circle($id = '', $uid = '')
    {
        // 如果ID为空
        if (empty($id)) {

            $this->_set_error('_EMPTY_CIRCLE_ID');

            return false;
        }

        // 获取当前是否可以删除
        $info = $this->_d->get($id);

        // 如果信息不存在
        if (!$info) {

            $this->_set_error('_EMPTY_ID_INFO');

            return false;
        }

        // 如果信息没有权限
        if ($info['uid'] != $uid) {

            $this->_set_error('_ERR_ID_NOT_AUTH_DEL');

            return false;
        }

        // 删除话题
        try {

            // 开始事务
            $this->_d->start_trans();

            // 删除话题
            $this->_d->delete($id);

            // 删除评论
            $this->_d->delete_by_conds(array('pid' => $id));

            // 删除附件表数据
            $this->_d_att->delete_by_conds(array('cid' => $id));

            // 删除点赞
            $this->_d_like->delete_by_conds(array('cid' => $id));

            // 提交事务
            $this->_d->commit();

        } catch (\Think\Exception $e) {
            \Think\Log::record($e);
            // 事务回滚
            $this->_set_error($e->getMessage(), $e->getCode());
            $this->_d->rollback();

            return false;

        } catch (\Exception $e) {

            \Think\Log::record($e);
            $this->_set_error($e->getMessage(), $e->getCode());
            // 事务回滚
            $this->_d->rollback();

            return false;
        }

        return true;
    }

    /**
     * 【微信端】发布评论
     * @param array $params
     *                  + string pid 话题ID
     *                  + string content 评论内容
     * @param array $user 当前用户
     * @param int $comment 是否开启评论审核
     * @return bool
     */
    public function push_comment($params = array(), $user = array(), $comment = '')
    {

        // 如果是外部人员
        if (empty($user['memUid'])) {

            $this->_set_error('_EMPTY_MEM_COMMENT');

            return false;
        }

        // 如果话题ID为空
        if (empty($params['pid'])) {

            $this->_set_error('_EMPTY_ID');

            return false;
        }

        // 获取话题详情
        $info = $this->_d->get_by_conds(
            array(
                'id' => $params['pid'],
                'pid' => CircleModel::CIRCLE_PID
            )
        );

        // 详情不存在
        if (empty($info)) {

            $this->_set_error('_EMPTY_CIRCLE_INFO');

            return false;
        }

        // 去除html标签以及换行标签
        $content = strip_tags($params['content']);

        // 如果内容为空
        if (empty($content)) {

            $this->_set_error('_EMPTY_COMMENT_CONTENT');

            return false;
        }

        // 如果内容存在且字段内容大于150个字
        if (!empty($content) && $this->utf8_strlen($content) > self::FONT_COMMENT_LENGTH) {

            $this->_set_error('_ERR_COMMENT_CONTENT_MAX');

            return false;
        }

        // 组装数组
        $data = array(
            'pid' => $params['pid'],
            'uid' => $user['memUid'],
            'content' => $content,
            'username' => $user['memUsername']
        );

        // 如果未开启评论审核
        if (empty($comment)) {

            $data = array_merge($data,
                array(
                    'audit_state' => self::AUDIT_OK,
                    'audit_time' => MILLI_TIME,
                    'audit_type' => self::AUDIT_SYSTEM_TYPE
                ));

        }

        // 写入评论
        $comment_id = $this->_d->insert($data);

        // 组装发送消息数据
        $msg_data = array(
            'uids' => array(
                array(
                    'memID' => $user['memUid']
                )
            ),
            'id' => $comment_id,
            'description' => $params['content']
        );

        // 如果未开启评论审核
        if (empty($comment)) {

            // 发送消息
            $this->send_msg($msg_data, self::MSG_COMMENT_ADOPT);

            // 获取话题详情
            $info = $this->_d->get($params['pid']);

            // 组装给发起人发送消息数据
            $msg_circle_data = array(
                'uids' => array(
                    array(
                        'memID' => $info['uid']
                    )
                ),
                'id' => $params['pid'],
                'description' => $params['content'],
                'name' => $user['memUsername']
            );

            // 给发布话题人发送消息
            $this->send_msg($msg_circle_data, self::MSG_COMMENT);

        } else {

            // 给评论人发送消息
            $this->send_msg($msg_data, self::MSG_COMMENT_PUBLISH);
        }

        return $comment_id;
    }

    /**
     * 【微信端】获取评论列表
     * @param array $params
     *                  + string limit 每页条少条
     *                  + string page 当前第几页
     *                  + string pid 话题ID
     * @param array $uid 当前用户UID
     * @return array
     */
    public function get_comment_list($params = array(), $uid = '')
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

        // 初始化查询条件
        $conditions = array(
            'pid' => $params['id'],
            'audit_state' => self::AUDIT_OK
        );

        // 获取评论总数
        $total = $this->_d->count_by_conds($conditions);

        // 获取评论列表
        $data = $this->_d->list_by_comment($conditions, $page_option, array(), 'id,content,uid,created,username,audit_time');

        // 格式化数据
        $list = $this->commont_list_format($data, $uid);

        return array(
            'total' => intval($total),
            'limit' => intval($limit),
            'page' => intval($page),
            'list' => $list,
        );
    }

    /**
     * 【后台】获取评论列表
     * @param array $params
     *                  + string limit 每页条少条
     *                  + string page 当前第几页
     *                  + string pid 话题ID
     *                  + string audit_state 评论状态
     * @return array
     */
    public function get_comment_admin_list($params = array())
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

        // 初始化查询条件
        $conditions = array(
            'pid' => $params['id'],
            'audit_state' => $params['audit_state']
        );

        // 获取评论总数
        $total = $this->_d->count_by_conds($conditions);

        // 获取评论列表
        $list = array();
        if ($total > 0) {

            $data = $this->_d->list_by_comment($conditions, $page_option, array(),
                'id,content,uid,audit_state,audit_time,audit_uname,audit_uid,username,created');

            // 取出列表的用户ID集合
            $uids = array_column($data, 'uid');

            // 查询用户集合数据
            $member = new User(Service::instance());
            $user_list = $member->listByConds(array('memUids' => $uids), 1, count($uids));

            // 获取已查询到UID列表
            $uid_list = array_column($user_list['list'], 'memUid');

            // 获取全部用户列表
            $this->user_list($user_list['list'], $uids, $uid_list);

            // 将用户数据转换成以用户ID为key的二维数组
            $user_data = array_combine_by_key($user_list['list'], 'memUid');

            // 遍历数据
            foreach ($data as $key => $v) {

                $list[] = array(
                    'id' => intval($v['id']),
                    'content' => strval($v['content']),
                    'username' => strval($v['username']),
                    'avatar' => strval($this->memFace($user_data[$v['uid']]['memFace'])),
                    'audit_state' => intval($v['audit_state']),
                    'audit_time' => strval($v['audit_time']),
                    'audit_uname' => strval($v['audit_uname']),
                    'audit_uid' => strval($v['audit_uid']),
                    'created' => $v['created'],
                    'like_total' => intval($v['like_total'])
                );
            }
        }

        return array(
            'total' => intval($total),
            'limit' => intval($limit),
            'page' => intval($page),
            'list' => $list,
        );
    }


    /**
     * 【微信端】获取话题列表
     * @param array $params
     *                  + string limit 每页条少条
     *                  + string page 当前第几页
     * @param array $uid 当前用户UID
     * @return array
     */
    public function get_circle_list($params = array(), $uid = '')
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
            'audit_time' => 'DESC'
        );

        // 初始化查询条件
        $conditions = array(
            'pid' => CircleModel::CIRCLE_PID,
            'audit_state' => self::AUDIT_OK
        );

        // 获取帖子总数
        $total = $this->_d->count_by_conds($conditions);

        // 获取帖子列表
        $data = $this->_d->list_by_conds($conditions, $page_option, $order_by);

        // 格式化数据
        $list = $this->circle_list_format($data, $uid);

        return array(
            'total' => intval($total),
            'limit' => intval($limit),
            'page' => intval($page),
            'list' => $list,
        );
    }

    /**
     * 【微信端】获取我的评论详情
     * @param string $c_id 评论ID
     * @param array $user 当前用户信息
     * @return bool
     */
    public function get_comment_my_info($c_id = '', $user = array())
    {

        // 如果ID为空
        if (empty($c_id)) {

            $this->_set_error('_EMPTY_COMMENT_ID');

            return false;
        }

        // 获取详情
        $info = $this->_d->get_by_conds(array('id' => $c_id, 'pid > ?' => CircleModel::CIRCLE_PID));

        // 详情不存在
        if (empty($info)) {

            $this->_set_error('_EMPTY_ID_INFO');

            return false;
        }

        return array(
            'id' => intval($info['id']),
            'username' => strval($info['username']),
            'avatar' => strval($this->memFace($user['memFace'])),
            'content' => strval($info['content']),
            'created' => !empty($info['audit_time']) ? $this->get_time($info['audit_time']) : $this->get_time($info['created']),
            'reply_status' => intval($info['audit_state'])
        );
    }

    /**
     * 更新收藏状态
     *
     * @param string $ids 数据ID，逗号分割的字符串
     *
     */
    public function update_collection($ids){

        $url = convertUrl(QY_DOMAIN . '/Public/Rpc/Collection/CollectionUpdate');

        $params =[
            'uid'=>'',
            'app'=>'workmate',
            'dataId'=>$ids
        ];

        $res = \Com\Rpc::phprpc($url)->invoke('Index',$params);

        return $res;
    }
}
