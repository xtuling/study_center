<?php
/**
 * 试卷-答卷表
 * @author: houyingcai
 * @email:    594609175@qq.com
 * @date :  2017-05-19 17:51:32
 * @version $Id$
 */

namespace Common\Service;

use Common\Common\Integral;
use Common\Common\User;
use Common\Model\AnswerDetailModel;
use Common\Model\AnswerModel;
use Common\Model\LikeModel;
use Common\Model\MedalModel;
use Common\Model\MedalRecordModel;
use Common\Model\MedalRelationModel;
use Common\Model\PaperModel;
use Common\Model\SnapshotModel;
use Common\Model\TopicModel;

class AnswerService extends AbstractService
{
    /***
     * @var AnswerDetailModel
     */
    protected $_answer_detail_model;
    /**
     * @var TopicModel
     */
    protected $_topic_model;
    /**
     * @var SnapshotModel
     */
    protected $_snapshot_model;
    /**
     * @var LikeModel
     */
    protected $_like_model;
    /**
     * @var MedalModel
     */
    protected $_medal_model;

    /**
     * @var MedalRelationModel
     */
    protected $_medal_relation_model;

    /**
     * @var MedalRecordModel
     */
    protected $_medal_record_model;

    /**
     * @var PaperModel
     */
    protected $paper_model;

    // 构造方法
    public function __construct()
    {
        $this->_d = new AnswerModel();
        $this->_like_model = new LikeModel();
        $this->_answer_detail_model = new AnswerDetailModel();
        $this->paper_model = new PaperModel();
        $this->_snapshot_model = new SnapshotModel();
        $this->_topic_model = new TopicModel();
        $this->_medal_model = new MedalModel();
        $this->_medal_relation_model = new MedalRelationModel();
        $this->_medal_record_model = new MedalRecordModel();
        parent::__construct();
    }

    /**
     * @author: 蔡建华
     * 查询考试排名数据
     * @param int $ep_id
     * @return array|bool
     */
    public function answer_list_all($ep_id = 0, $uid = 0)
    {
        /**
         *  判断我是否参与考试
         */
        $data = array(
            'uid' => $uid,
            'ep_id' => $ep_id,
        );
        $count = $this->_d->count_by_conds($data);
        if (!$count) {
            E('_ERR_MY_VISIT_NO');

            return false;
        }
        /*
         * 查询考试成绩最高的一次记录
         */
        $data = $this->_d->answer_all(array("ep_id" => $ep_id), '*');
        if (empty($data)) {
            E('_ERR_DATA_NOT_EXIST');

            return false;
        }
        //获取用户信息
        $uids = array_column($data, 'uid');
        $userlist = $this->getUser($uids);
        $arr = array();
        $ranking = 0;
        /**
         *  获取我已点赞的记录ID
         */
        $like_ids = array_column($data, 'ea_id');
        $like_data = $this->_like_model->list_by_conds(array('ea_id' => $like_ids, 'uid' => $uid));
        $like_data_ea_id = array_column($like_data, 'ea_id');
        //获取点赞数
        $likes_data_list = $this->_like_model->getLikeCount($like_ids, 'ea_id,count(ea_id) as likes');
        $likes = array_column($likes_data_list, 'likes', 'ea_id');
        array_unique($like_data_ea_id);
        foreach ($data as $k => $v) {

            //排名
            $v['ranking'] = $k + 1;
            $v['memID'] = $userlist[$v['uid']]['memUid'] ? $userlist[$v['uid']]['memUid'] : '';
            $v['memUsername'] = $userlist[$v['uid']]['memUsername'] ? $userlist[$v['uid']]['memUsername'] : '';
            $v['memFace'] = $userlist[$v['uid']]['memFace'] ? $userlist[$v['uid']]['memFace'] : '';
            $v['likes'] = array_key_exists($v['ea_id'], $likes) ? $likes[$v['ea_id']]['likes'] : self::FALSE_STATE;
            if ($uid == $v['uid']) {
                $ranking = $k + 1;
            }
            $v['is_like'] = in_array($v['ea_id'], $like_data_ea_id) ? self::SUCCES_STATE : self::FALSE_STATE;
            //删除多余字段
            unset(
                $v['deleted'],
                $v['updated'],
                $v['created'],
                $v['status'],
                $v['uid'],
                $v['domain'],
                $v['paper_info'],
                $v['my_is_pass'],
                $v['my_error_num'],
                $v['my_begin_time'],
                $v['ep_id']
            );

            $arr[] = $v;
        }

        return array("list" => $arr, 'ranking' => $ranking);
    }

    /**
     * @author: 蔡建华
     * 格式化模拟详情页面
     * @param array $data
     * @return array
     */
    public function format_testRecord($data = array())
    {
        $arr = array();
        foreach ($data as $key => $val) {
            $value = array();
            $value['ea_id'] = intval($val['ea_id']);
            $value['my_score'] = $val['my_score'];
            $value['my_time'] = $val['my_time'];
            $value['my_begin_time'] = $val['my_begin_time'];
            $arr[$key] = $value;
        }

        return $arr;
    }

    /**
     * 模拟试卷详情
     * @author: 蔡建华
     * @param string $ep_id 试卷ID
     * @param string $uid 用户ID
     * @return array|bool
     */
    public function get_answer_info($ep_id = '', $uid = '')
    {
        $data = $this->_d->get_by_conds(array("ep_id" => $ep_id, 'uid' => $uid,));
        //判断用户是否参加考试
        if (empty($data)) {

            E("_ERR_MY_VISIT_NO");

            return false;
        }

        // 判断不是模拟试卷则抛出
        $info = unserialize($data['paper_info']);
        if ($info['SIMULATION_PAPER_TYPE'] != self::EVALUATION_PAPER_TYPE) {

            E("_ERR_SIMULATION_PAPER_TYPE");

            return false;
        }

        // 考试状态
        $ep_status = $this->paper_status($info['exam_status'], $info['begin_time'], $info['end_time']);

        // 最高得分
        $high_score = $this->_d->fetchOne(array(' AND ep_id =?' => $ep_id, ' AND uid =?' => $uid),
            'Max(my_score) as high_score');

        // 模拟考试次数
        $exam_times = $this->_d->fetchOne(array(
            ' AND ep_id =?' => $ep_id,
            ' AND uid =?' => $uid,
            ' AND my_time>?' => 0
        ),
            'count(*) as exam_times');

        // 模拟考试通过次数
        $pass_times = $this->_d->fetchOne(array(
            ' AND ep_id =?' => $ep_id,
            ' AND uid =?' => $uid,
            'AND my_is_pass=?' => self::MY_PASS,
        ), 'count(*) as my_is_pass');

        // 模拟考试答题总数
        $answer_num = $this->_answer_detail_model->answer_detail_record_num($ep_id, $uid);

        // 我的排名
        $data = $this->answer_list_all($ep_id, $uid);
        $ranking = $data['ranking'];

        return array(
            "ep_status" => $ep_status,
            'high_score' => $high_score,
            'exam_times' => intval($exam_times),
            'pass_times' => intval($pass_times),
            'answer_num' => intval($answer_num),
            'ranking' => intval($ranking),
        );
    }

    /**
     * 模拟答卷详情
     * @author: 蔡建华
     * @param int $ep_id 试卷ID
     * @param int $ea_id 答卷ID
     * @param int $uid 当前用户
     * @return array|bool
     */
    public function answer_detail_info($ep_id = 0, $ea_id = 0, $uid = 0)
    {
        // 判断如果是测评试卷$ep_id>0,$ea_id=0,如果是模拟试卷ep_id=0，ea_id>0
        if (($ep_id == 0 && $ea_id == 0) || ($ep_id > 0 && $ea_id > 0)) {
            E('_EMPTY_EA_EP_SOME');

            return false;
        }
        $data = array();
        // 模拟答卷详情
        if ($ea_id > 0) {
            $data = $this->_d->get_by_conds(array("ea_id" => $ea_id, 'uid' => $uid));
        }
        // 获取试卷信息
        if ($ep_id > 0) {
            $data = $this->_d->get_by_conds(array("ep_id" => $ep_id, 'uid' => $uid));
            $ea_id = $data['ea_id'];
        }
        // 判断数据是否存在
        if (empty($data)) {
            E('_ERR_MY_VISIT_NO');

            return false;
        }

        $ep_id = $data['ep_id'];
        $paper = $this->paper_model->get_by_conds(array('ep_id' => $ep_id));
        if (empty($paper)) {
            E('_EMPTY_EXAT_DELETED');

            return false;
        }
        // 判断分类是否被禁用
        if ($paper['cate_status'] != self::EC_OPEN_STATES) {
            E('_ERR_DATA_EXAM_DEL');

            return false;
        }

        // 判断试卷是否交卷
        if (!$data['my_time']) {
            E('_ERR_REPEAT_SUBMINT_END');

            return false;
        }
        $info = unserialize($data['paper_info']);
        // 做错题数
        $my_success_num = $this->_answer_detail_model->count_by_conds(array(
            'ea_id' => $ea_id,
            'is_pass' => self::MY_PASS,
        ));
        // 答题总数
        $my_total = $this->_answer_detail_model->count_by_conds(array(
            'ea_id' => $ea_id,
        ));
        // 答错的题数
        $my_error_num = $my_total - $my_success_num;
        // 排名计算
        $rankdata = $this->answer_list_all($data['ep_id'], $uid);
        // 我的排名
        $ranking = $rankdata['ranking'];
        // 获取答卷试题列表表
        $data_detail = $this->_answer_detail_model->list_by_conds(array("ea_id" => $ea_id));
        // 答卷情况格式化
        $list = $this->get_answer_detail($data_detail, 1);
        $result = array(
            'ea_id' => $data['ea_id'],
            'ep_id' => $data['ep_id'],
            // 我的分数
            "my_score" => $data['my_score'],
            // 我的考试时间
            "my_time" => $data['my_time'],
            // 考试错题数
            "my_error_num" => $my_error_num,
            // 考试是否通过
            "my_is_pass" => intval($data['my_is_pass']),
            // 考试排名
            "ranking" => $ranking,
            // 考试总分
            "total_score" => $info['total_score'],
            // 考试及格分
            "pass_score" => $info['pass_score'],
            // 总题数
            "topic_count" => intval($info['topic_count']),
            // 考试时长
            "paper_time" => $info['paper_time'],
            // 答题总数
            "total" => $my_total,
            'list' => $list,
        );

        return $result;
    }

    /**
     * 开始考试
     * @author: 蔡建华
     * @param array $data 试卷信息数据
     * @param string $uid 用户ID
     * @return array|bool
     */
    public function paper_start_exam($data = array(), $uid = '')
    {
        $ep_id = $data['ep_id'];
        $ep_type = $data['ep_type'];

        // 组装考试开始基本数据
        $base = array(
            'ep_id' => $ep_id,
            'uid' => $uid,
            'my_begin_time' => MILLI_TIME,
            'paper_info' => serialize($data),
            'my_time' => 0,
            'my_error_num' => 0,
            'my_is_pass' => 0,
        );
        // 随机抽取
        if ($ep_type == self::TOPIC_RANDOM) {
            // 模拟考试
            $paper = new PaperService();
            // 随机生成试题
            $snapshot = $paper->get_temp_list_by_epid($ep_id, 1);

            // 按照单选、多选、判断、问答、语音重新组合数组
            $singles = $multiples = $judgments = $questions = $voices = array();
            foreach ($snapshot as $key => $val) {
                // 单选
                if ($val['et_type'] == self::TOPIC_TYPE_SINGLE) {

                    $singles[] = $val;
                }
                // 多选
                if ($val['et_type'] == self::TOPIC_TYPE_MULTIPLE) {

                    $multiples[] = $val;
                }
                // 判断
                if ($val['et_type'] == self::TOPIC_TYPE_JUDGMENT) {

                    $judgments[] = $val;
                }
                // 问答
                if ($val['et_type'] == self::TOPIC_TYPE_QUESTION) {

                    $questions[] = $val;
                }
                // 语音
                if ($val['et_type'] == self::TOPIC_TYPE_VOICE) {

                    $voices[] = $val;
                }
            }

            $snapshot = array_merge($singles, $multiples, $judgments, $questions, $voices);

        } else {
            // 获取从快照组获取题
            $snapshot = $this->_snapshot_model->list_by_conds(array("ep_id" => $ep_id));
        }
        // 获取试题失败
        if (empty($snapshot)) {
            E('_ERR_BANK_TOPIC_FAILED');

            return false;
        }

        $topic = array();
        $topic_ids = array();

        try {
            // 开始事务
            $this->start_trans();
            // 插入考试信息
            $ea_id = $this->_d->insert($base);
            foreach ($snapshot as $key => $value) {
                $topic_ids[] = $value['et_id'];
                // 题目标题序列号
                $et_detail = array(
                    "et_type" => $value['et_type'],
                    "title" => $value['title'],
                    "title_pic" => $value['title_pic'],
                    "answer" => $value['answer'],
                    "answer_resolve" => $value['answer_resolve'],
                    "answer_coverage" => $value['answer_coverage'],
                    "match_type" => $value['match_type'],
                    "answer_keyword" => unserialize($value['answer_keyword']),
                );
                // 考试试题拼接数据
                if ($ep_type == self::TOPIC_RANDOM) {
                    $order_num = $key + 1;
                } else {
                    $order_num = $value['order_num'];
                }
                $val = array(
                    'ea_id' => $ea_id,
                    'et_option' => $value['options'],
                    'et_detail' => serialize($et_detail),
                    'order_num' => intval($order_num),
                    'is_pass' => 0,
                    'score' => $value['score'],
                    'my_score' => 0,
                );
                $topic[] = $val;
            }

            // 题目ID不为空，更新题目加一
            if (!empty($topic_ids)) {
                $this->_topic_model->AddIncNum(array("et_id" => $topic_ids));
            }
            // 插入试题
            $this->_answer_detail_model->insert_all($topic);
            // 开始创建答卷试题
            $this->commit();
        } catch (\Think\Exception $e) {
            $this->rollback();
            E('_ERR_ADD_EXAM_FAILED');

            return false;
        } catch (\Exception $e) {
            $this->rollback();
            E('_ERR_ADD_EXAM_FAILED');

            return false;
        }

        // 返回答卷ID
        return array('ea_id' => $ea_id);
    }

    /**
     * 获取考试参与人员列表
     * @author: 侯英才
     * @param array $conds 查询条件参数列表
     * @param array $page_option 分页参数
     * @param array $order_option 排序参数
     * @param string $fields 返回字段
     * @return array|bool
     */
    public function get_mock_join_list($conds, $page_option = null, $order_option = array(), $fields = '*')
    {
        try {
            return $this->_d->get_mock_answer_list($conds, $page_option, $order_option, $fields);
        } catch (\Exception $e) {
            E($e->getCode() . ":" . $e->getMessage());

            return false;
        }
    }

    /**
     * 统计考试参与人员总数
     * @author: 侯英才
     * @param array $conds 查询条件参数列表
     * @return array|bool
     */
    public function count_mock_answer($conds)
    {
        try {
            return $this->_d->count_mock_answer($conds);
        } catch (\Exception $e) {
            E($e->getCode() . ":" . $e->getMessage());

            return false;
        }
    }

    /**
     * 根据条件读取数据
     * @author: 侯英才
     * @param array $conds 查询条件数组
     * @param array $order_option 排序数组
     * @param String $fields 查询字段
     * @return array|bool
     */
    public function get_by_conds($conds, $order_option = array(), $fields = '*')
    {

        try {
            return $this->_d->get_by_conds($conds, $order_option, $fields);
        } catch (\Exception $e) {
            E($e->getCode() . ":" . $e->getMessage());

            return false;
        }
    }

    /**
     * 获取试卷记录
     * @author: 蔡建华
     * @param array $params 请求参数
     * @param string $uid 当前用户ID
     * @param int $type
     * @return array|bool
     */

    public function test_record_list($params = array(), $uid = '', $type = 0)
    {
        $ep_id = intval($params['ep_id']);
        if (!$ep_id) {
            E('_EMPTY_EP_ID');

            return false;
        }

        //获取试卷信息
        $info = $this->get_answer_info($ep_id, $uid);
        if (!$info) {
            return false;
        }
        $page_array = null;
        if ($type) {
            $page = isset($params['page']) ? intval($params['page']) : 1;
            $limit = isset($params['limit']) ? intval($params['limit']) : self::DEFAULT_LIMIT_ADMIN;
            // 分页
            list($start, $limit) = page_limit($page, $limit);
            $page_array = array($start, $limit);
        }


        //  按照发布时间排序
        $order_option = array('created' => 'DESC');

        // 查询模拟考试记录总记录数
        $conds = array("ep_id" => $ep_id, 'uid' => $uid, 'my_time>?' => 0);
        $total = $this->count_by_conds($conds);
        $data = array();

        if ($total) {

            $data = $this->list_by_conds($conds, $page_array, $order_option);
        }

        $result = array(
            'total' => intval($total),
            'limit' => intval($limit),
            'page' => intval($page),
            // 格式化模拟记录
            'list' => $this->format_testRecord($data),
        );

        return array_merge($info, $result);
    }

    /**
     * 获取考试未参与考试人员列表及人数
     * @author: 侯英才
     * @param array $conds 查询权限条件
     * @param int $ep_id 试卷ID
     * @param int $is_all 是否是全公司
     * @return array -返回参数
     *           + unjoin_list    -未参与人列表
     *           + join_count     -已参与人总数
     */
    public function get_unjoin_data($conds, $ep_id, $is_all = 0)
    {
        $right_serv = new RightService();

        // 参与考试的权限范围
        if ($is_all != self::AUTH_ALL) {

            $rights = $right_serv->list_by_conds($conds);
            // 格式化权限范围
            $right_view = $right_serv->format_db_data($rights);
        }

        $right_view['is_all'] = $is_all;

        // 获取要参加考试的全部人员
        $all_join = $right_serv->list_uids_by_right($right_view);

        // 已经参加了考试的人员
        $join = $this->_d->list_by_conds(array('ep_id' => $ep_id, 'my_time > ?' => 0));
        $join = array_column($join, 'uid');
        $join = array_unique($join);

        // 初始未参加考试人员
        $unjoin = array();

        // 遍历全部邀请人员
        foreach ($all_join as $key => $uid) {
            // 如果不在已参加的人员中
            if (!in_array($uid, $join)) {

                $unjoin[] = $uid;
            }
        }

        // 已参与总人数
        $join_count = count($join);

        return array(
            'unjoin_list' => $unjoin, // 未参与列表
            'join_list' => $join,
            'join_count' => (int)$join_count, // 已参与人数
        );
    }

    /**
     * 判断试卷状态
     * @author: 蔡建华
     * @param $ea_id int 答卷ID
     * @param $uid string 用户ID
     * @return array|bool
     */
    public function paper_answer_status($ea_id = 0, $uid = '')
    {
        //获取答卷信息
        $ea_data = $this->get_by_conds(array('ea_id' => $ea_id, 'uid' => $uid));
        // 答卷信息不存在
        if (empty($ea_data)) {
            E('_ERR_MY_VISIT_NO');

            return false;
        }
        // 查询试卷信息
        $ep_id = $ea_data['ep_id'];
        $paper = $this->paper_model->get_by_conds(array('ep_id' => $ep_id));
        // 判断试卷状态，其中包括分类状态和试卷状态
        if ($paper['cate_status'] == self::EC_CLOSE_STATES || $paper['exam_status'] == self::PAPER_STOP) {
            E("_ERR_EC_CLOSE_STATES");

            return false;
        }
        // 你已经交卷无法答题
        if ($ea_data['my_time'] > 0) {
            E('_ERR_SUBMINT_ANSWER');

            return false;
        }
        // 试题状态
        // 我的考试开始时间
        $my_begin_time = $ea_data['my_begin_time'];
        // 分钟
        $paper_time = $paper['paper_time'] * 60 * 1000;
        $time = MILLI_TIME;
        // 时间到了交卷
        $endtime = $my_begin_time + $paper_time;
        if ($time > $paper['end_time'] || $time > $endtime) {
            if (!$this->submit_papers($ea_id, $uid)) {
                return false;
            };
            E('_ERR_EC_EXIT_FINISH');

            return false;
        }
        // 考试结束时间大于时间结束时间
        if ($endtime > $paper['end_time']) {
            $left_time = $paper['end_time'] - $time;
            // 考试结束时间小与时间结束时间
        } else {
            $left_time = $endtime - $time;
        }

        return array(
            "ep_name" => $paper['ep_name'],
            // 返回倒计时
            'left_time' => $left_time,
            'my_begin_time' => $my_begin_time,
            'paper_time' => $paper['paper_time'],
            'end_time' => $paper['end_time'],
        );
    }

    /**
     * 自动提交试卷
     * @author: 蔡建华
     * @param int $ea_id 答卷ID
     * @param int $uid 用户ID
     * @param array $award
     * @param int $type 交卷类型 0,自动交卷 1手动交卷
     * @return bool
     */
    public function submit_papers($ea_id = 0, $uid = 0, &$award = array(),$type = 0)
    {
        // 判断答卷ID是否为空
        if (!$ea_id) {

            E('_EMPTY_EA_ID');

            return false;
        }
        // 判断用户ID不能为空
        if (!$uid) {

            E('_EMPTY_UID');

            return false;
        }

        //获取答卷信息
        $ea_data = $this->get_by_conds(array('ea_id' => $ea_id, 'uid' => $uid));
        // 答卷信息不存在
        if (empty($ea_data)) {
            E('_ERR_MY_VISIT_NO');

            return false;
        }
        $ep_id = $ea_data['ep_id'];
        $paper = $this->paper_model->get_by_conds(array('ep_id' => $ep_id));
        $time = MILLI_TIME;
        if ($paper['cate_status'] == self::EC_CLOSE_STATES || $paper['exam_status'] == self::PAPER_STOP) {
            E("_ERR_EC_CLOSE_STATES");

            return false;
        }
        // 你已经交卷
        if ($ea_data['my_time'] > 0) {
            E('_ERR_REPEAT_SUBMINT_EXAR');
            return false;
        }
        // 分钟
        $paper_time = $paper['paper_time'] * 60 * 1000;
        // 我的考试开始时间
        $my_begin_time = $ea_data['my_begin_time'];
        // 时间到了交卷
        $endtime = $my_begin_time + $paper_time;
        if ($time <= $paper['end_time'] && $time <= $endtime) {
            $submit = 1;
            //提前交卷
            $my_time = ($time - $my_begin_time);
        } else {
            $submit = 2;
            //时间到自动交卷
            if ($endtime > $paper['end_time']) {
                $my_time = ($paper['end_time'] - $my_begin_time);
            } else {
                $my_time = ($endtime - $my_begin_time);
            }
        }
        // 交卷处理
        try {
            // 开始事务
            $this->start_trans();
            // 算分函数
            $my_score = $this->_answer_detail_model->get_score($ea_id);
            // 判断是否通过
            if ($paper['pass_score'] > $my_score) {
                $my_is_pass = 0;
            } else {
                $my_is_pass = 1;
            }
            // 考试激励规则调用
            $this->paper_statistics($paper, $uid);
            // 保存交卷信息
            $this->update(
                array("ea_id" => $ea_id),
                array(
                    'my_is_pass' => $my_is_pass,
                    'my_time' => $my_time,
                    'my_score' => $my_score
                )
            );
            $this->commit();
        } catch (\Think\Exception $e) {
            $this->rollback();
            E('_ERR_SUBMINT_FAIL');

            return false;
        } catch (\Exception $e) {
            $this->rollback();
            E('_ERR_SUBMINT_FAIL');

            return false;
        }

        // 添加积分勋章接口
        $this->medal($ep_id, $uid, $award);

        // 时间到自动交卷
        if(!$type)
        {
            if ($submit == 2) {
                E('_ERR_AUTO_SUBMINT_TIME');

                return false;
            }
        }
        return true;
    }

    /**
     * 考试激励处理
     * @author: 蔡建华
     * @param int $ep_id 试卷ID
     * @param string $uid 用户ID
     * @param array &$award 返回数据
     * @return bool
     */
    public function medal($ep_id, $uid = '', &$award = array())
    {
        // 获取用户信息
        $userServ = &User::instance();
        $user = $userServ->getByUid($uid);

        // 获取用户权限
        $rightServ = new RightService();
        $right = $rightServ->get_by_right($user);

        $data['right'] = $right;
        $data['er_type'] = self::RIGHT_MEDAL;
        $medal = $this->_medal_model->fetch_all_medal($data);

        // 查询出相关激励
        if (empty($medal)) {

            return true;
        }

        // 获取符合条件的激励IDS
        $em_ids = array_column($medal, 'em_id');

        // 查询激励关系列表
        $list = $this->_medal_relation_model->list_by_conds(array('em_id' => $em_ids));

        // 如果存在符合条件激励
        if (empty($list)) {

            return true;
        }

        // 获取激励列表IDS
        $em_id_arr = array_column($list, 'em_id');

        // 如果查询出激励IDS
        if (empty($em_id_arr)) {

            return true;
        }

        // 初始化勋章
        $integral = new Integral();

        // 获取勋章列表
        $integral_list = $integral->listMedal();

        // 获取激励列表
        $integral_key_list = array_combine_by_key($integral_list, 'im_id');

        // 获取激励列表
        $result = $this->_medal_model->list_by_conds(array('em_id' => $em_id_arr));

        // 遍历激励列表
        foreach ($result as $key => $val) {

            // 获取当前用户是否已经领取过当期激励
            $count = $this->_medal_record_model->count_by_conds(array(
                'uid' => $uid,
                'em_id' => $val['em_id']
            ));

            // 如果已领取积分或者勋章
            if ($count) {

                continue;
            }

            // 查询勋章对应关系
            $relate_total = $this->_medal_relation_model->count_by_conds(array(
                'ep_id' => $ep_id,
                'em_id' => $val['em_id']
            ));

            // 如果没有对应关系
            if (!$relate_total) {

                continue;
            }

            // 查出对应ep_list
            $em_list = $this->_medal_relation_model->list_by_conds(array('em_id' => $val['em_id']));

            $ep_ids = array_column($em_list, 'ep_id');

            // 答题列表
            $score_list = $this->_d->list_by_conds(array(
                'ep_id' => $ep_ids,
                'uid' => $uid,
                'my_score >= ?' => $val['em_score']
            ));

            // 获取分数的epids
            $score_ep_ids = array_unique(array_column($score_list, 'ep_id'));

            // 判断满足条件的次数
            if (count($score_ep_ids) >= $val['em_number']) {

                // 获取勋章
                if ($val['em_type'] == self::EC_MEDAL_TYPE_MEDAL) {

                    $integralUtil = &Integral::instance();
                    $integralUtil->endowMedal($val['im_id'], $uid, $user['memUsername']);
                    $params = array(
                        'name' => $integral_key_list[$val['im_id']]['name'],
                        'url' => '',
                        'uids' => array($uid),
                        'power_type' => self::ENDOW__END,
                        'title'=>$val['title']
                    );
                    $this->send_msg($params, self::ENDOW__END);
                }

                // 如果类型为积分
                if ($val['em_type'] == self::EC_MEDAL_TYPE_INTEGRAL) {

                    $integralUtil = &Integral::instance();
                    $integralUtil->asynUpdateIntegral(array(
                        'memUid' => $uid,
                        'miType' => 'mi_type0',
                        'irKey' => 'dt_exam_encourage',
                        'remark' => '考试中心-' . $val['title'],
                        'integral' => intval($val['em_integral']),
                        'msgIdentifier' => APP_IDENTIFIER
                    ));

                }


                $award[] = array( // 激励信息
                    'award_id' => intval($val['em_id']), // 激励ID
                    'award_action' => strval($val['title']), // 激励行为
                    'description' => strval($val['em_desc']), // 描述
                    'award_type' => intval($val['em_type']), // 激励类型（1=勋章；2=积分）
                    'medals' => array( // 勋章
                        'im_id' => intval($val['im_id']), // 勋章ID
                        'icon' => strval($integral_key_list[$val['im_id']]['icon']), // 勋章图标URL或者前端路径
                        'icon_type' => intval($integral_key_list[$val['im_id']]['icon_type']), // 勋章图标来源（1=用户上传；2=系统预设）
                        'name' => strval($integral_key_list[$val['im_id']]['name']), // 勋章名称
                        'desc' => strval($integral_key_list[$val['im_id']]['desc']), // 勋章描述
                    ),
                    'integral' => intval($val['em_integral']), // 积分
                );

                // 满足次数加入记录
                $this->_medal_record_model->insert(array(
                    'uid' => $uid,
                    'em_id' => $val['em_id']
                ));

            }

        }

        return true;
    }

    /**
     * 更新参与未参与人数
     * @author: 蔡建华
     * @param $data array 试题
     * @param $uid string 用户ID
     */
    public function paper_statistics($data = array(), $uid)
    {
        $count = $this->_d->count_by_conds(array("ep_id" => $data['ep_id'], 'uid' => $uid, 'my_time>?' => 0));
        if (!$count) {
            if (!$data['unjoin_count']) {

                $data['unjoin_count'] = 1;
            }
            $this->paper_model->update_by_paper(
                array('ep_id' => $data['ep_id']),
                array(
                    "join_count" => $data['join_count'] + 1,
                    'unjoin_count' => $data['unjoin_count'] - 1
                )
            );
        }
    }

    /**
     * 【微信端】 提交题目试卷验证
     * @author: 何岳龙
     * @param array $params
     * @param string $uid
     * @return bool
     */
    public function end_time_validation($params = array(), $uid = '')
    {

        // 获取答卷详情
        $answer_info = $this->_answer_detail_model->get($params['ead_id']);

        // 获取得分详情
        $info = $this->_d->get($answer_info['ea_id']);

        // 获取试卷详情
        $paper_info = $this->paper_model->get($info['ep_id']);

        // 如果考试考生考试开始时间+考试时长大于结束时间
        if (($info['my_begin_time'] + ($paper_info['paper_time'] * 60 * 1000)) > $paper_info['end_time']) {

            // 如果当前时间大于或者等于结束时间
            if (MILLI_TIME >= $paper_info['end_time']) {

                // 交卷
                $this->submit_papers($answer_info['ea_id'], $uid);

                return false;
            }

        } else {

            // 如果当前时间大于开始的结束时间
            if (MILLI_TIME >= ($info['my_begin_time'] + ($paper_info['paper_time'] * 60 * 1000))) {

                // 交卷
                $this->submit_papers($answer_info['ea_id'], $uid);

                return false;
            }

        }

        return true;
    }
}
