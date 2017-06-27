<?php
/**
 * 试卷表
 * @author: houyingcai
 * @email:    594609175@qq.com
 * @date :  2017-05-19 17:43:16
 * @version $Id$
 */

namespace Common\Service;

use Common\Model\AnswerModel;
use Common\Model\AttrModel;
use Common\Model\BankModel;
use Common\Model\CategoryModel;
use Common\Model\PaperModel;
use Common\Model\PaperTempModel;
use Common\Model\RightModel;
use Common\Model\SnapshotModel;
use Common\Model\TagModel;
use Common\Model\TopicAttrModel;
use Common\Model\TopicModel;
use VcySDK\Cron;
use VcySDK\Service;

class PaperService extends AbstractService
{

    // 构造方法
    public function __construct()
    {
        $this->_d = new PaperModel();
        $this->_d_cate = new CategoryModel();
        $this->_d_answer = new AnswerModel();
        $this->_d_right = new RightModel();
        $this->_d_bank = new BankModel();
        $this->_d_tag = new TagModel();
        $this->_d_attr = new AttrModel();
        $this->_d_snapshot = new SnapshotModel();
        $this->_d_temp = new PaperTempModel();
        $this->_d_topic = new TopicModel();
        $this->_d_topic_attr = new TopicAttrModel();
        parent::__construct();
    }

    /**
     * 获取试卷基本信息(详情用)
     * @author：daijun
     * @param int $ep_id 试卷ID
     * @return array|bool
     */
    public function get_paper_detail_admin($ep_id = 0)
    {
        // 获取试卷信息(此处需加上不是初始化状态的数据)
        $data = $this->_d->get_by_conds(array('ep_id' => $ep_id, 'exam_status>?' => self::PAPER_INIT));

        // 判断试卷是否存在
        if (empty($data)) {
            E('_EMPTY_PAPER_DATA');

            return false;
        }

        // 获取试卷当前状态
        $result['ep_status'] = $this->paper_status($data['exam_status'], $data['begin_time'], $data['end_time']);

        // 获取试卷分类名称
        $cate_data = $this->_d_cate->get($data['ec_id']);
        $result['ec_id'] = intval($data['ec_id']);
        $result['ec_name'] = $cate_data['ec_name'];

        // 获取封面图片
        $result['cover_url'] = $this->format_cover($data['cover_id']);
        unset($data['cover_id']);

        $result['ep_id'] = intval($ep_id);
        $result['ep_name'] = $data['ep_name'];
        $result['paper_type'] = intval($data['paper_type']);
        $result['ep_type'] = intval($data['ep_type']);
        $result['is_all'] = intval($data['is_all']);
        $result['begin_time'] = $data['begin_time'];
        $result['end_time'] = $data['end_time'];
        $result['paper_time'] = intval($data['paper_time']);
        $result['is_notify'] = intval($data['is_notify']);
        $result['notify_begin'] = intval($data['notify_begin']);
        $result['notify_end'] = intval($data['notify_end']);
        $result['is_recommend'] = intval($data['is_recommend']);
        $result['answer_resolve'] = intval($data['answer_resolve']);
        $result['total_score'] = intval($data['total_score']);
        $result['pass_score'] = intval($data['pass_score']);
        $result['intro'] = $data['intro'];
        $result['reason'] = $data['reason']; //终止原因
        $result['reason_time'] = $data['reason_time']; //终止时间

        return $result;
    }


    /**
     * 获取试卷基本信息(编辑用)
     * @author：daijun
     * @param int $ep_id 试卷ID
     * @return array|bool
     */
    public function get_paper_base_detail($ep_id = 0)
    {
        // 获取试卷信息
        $data = $this->_d->get($ep_id);

        // 判断试卷是否存在
        if (empty($data)) {
            E('_EMPTY_PAPER_DATA');

            return false;
        }

        // 格式化返回数据
        $result = array();
        $result['cover_id'] = $data['cover_id'];
        $result['cover_url'] = $this->format_cover($data['cover_id']);
        $result['ep_id'] = intval($data['ep_id']);
        $result['ep_name'] = $data['ep_name'];
        $result['is_all'] = intval($data['is_all']);
        $result['begin_time'] = $data['begin_time'];
        $result['end_time'] = $data['end_time'];
        $result['paper_time'] = intval($data['paper_time']);
        $result['is_notify'] = intval($data['is_notify']);
        $result['notify_begin'] = intval($data['notify_begin']);
        $result['notify_end'] = intval($data['notify_end']);
        $result['is_recommend'] = intval($data['is_recommend']);
        $result['is_pushmsg'] = intval($data['is_pushmsg']);
        //$result['answer_resolve'] = intval($data['answer_resolve']);
        $result['answer_resolve'] = intval($data['answer_resolve']);
        $result['total_score'] = intval($data['total_score']);
        $result['pass_score'] = intval($data['pass_score']);
        $result['exam_status'] = intval($data['exam_status']);
        $result['intro'] = $data['intro'];

        return $result;
    }

    /**
     *  查询总数
     * @param $data array 查询条件
     * @return int|mixed
     */
    public function count_by_paper($data)
    {
        return $this->_d->count_by_paper($data);
    }

    /**
     * 查询列表
     * @author: 蔡建华
     * @param string $data 查询条件
     * @param null $page_option 分页参数
     * @param array $order_option 排序参数
     * @param string $fields 查询的字段
     * @return array|bool
     */
    public function list_by_paper($data = '', $page_option = null, $order_option = array(), $fields = '*')
    {
        return $this->_d->list_by_paper($data, $page_option, $order_option, $fields);
    }

    /**
     * 格式化试题列表返回数据
     * @author: 蔡建华
     * @param array $list
     * @param int $uid
     * @return array
     */
    public function paper_param($list = array(), $uid = 0)
    {
        if (empty($list)) {
            return array();
        }
        //获取点赞
        $ep_ids = array_column($list, 'ep_id');
        $answer_data = $this->_d_answer->list_by_conds(array('ep_id' => $ep_ids, 'uid' => $uid, 'my_time>?' => 0));
        //我已经点赞的活动
        $back_cid = array_column($answer_data, 'ep_id');

        $last_names = array_column($answer_data, 'my_score', 'ep_id');

        array_unique($back_cid);

        //要返回的字段
        $arr = array();
        foreach ($list as $key => $val) {
            $value = array();
            $value['ep_id'] = $val['ep_id'];
            $value['paper_type'] = intval($val['paper_type']);
            $value['ep_name'] = $val['ep_name'];
            $value['ep_status'] = $this->paper_status($val['exam_status'], $val['begin_time'], $val['end_time']);
            /***
             * 测评试卷返回 分值
             */
            if ($val['paper_type'] == self::EVALUATION_PAPER_TYPE) {
                $value['my_score'] = array_key_exists($val['ep_id'], $last_names) ? $last_names[$val['ep_id']] : '';
            } else {
                $value['my_score'] = self::SCORE;
            }
            $value['updated'] = $val['update_time'];
            $value['join_status'] = in_array($val['ep_id'], $back_cid) ? self::VISITED : self::UNVISIT;
            $arr[$key] = $value;
        }

        return $arr;
    }

    /**
     * 新增试卷规则
     * @author：daijun
     * @param array $param 传入参数
     * @return bool
     */
    public function rule_add($param = array())
    {
        $ep_id = 0;
        // 验证数据
        if (!$data = $this->check_rule_add($param)) {
            E('_EMPTY_SAVE_DATA');

            return false;
        }
        // 查询分类的权限信息
        $cate_data = $this->_d_cate->get($param['ec_id']);

        $right = array();
        if ($cate_data['is_all'] == self::AUTH_ALL) {
            // 如果是全公司
            $data['is_all'] = self::AUTH_ALL;

        } else {
            $data['is_all'] = self::AUTH_NOT_ALL;
            // 如果不是全公司，查询分类权限信息
            $right = $this->_d_right->list_by_conds(array(
                'epc_id' => $param['ec_id'],
                'er_type' => self::RIGHT_CATEGORY
            ), null, array(), 'uid,cd_id,tag_id,job_id,role_id');
        }

        // 此处组装其他必填的字段信息
        $data['tag_data'] = !empty($param['tag_data']) ? serialize($param['tag_data']) : '';
        $data['admin_id'] = '';
        $data['launch_man'] = '';
        $data['check_topic_data'] = '';
        $data['intro'] = '';
        $data['reason'] = '';
        $data['reason_user_id'] = '';
        $data['reason_user'] = '';
        $data['exam_status'] = self::PAPER_INIT;

        if (!empty($param['rule'])) {
            // 随机选题 此处需要计算总分
            $rule = $param['rule'];

            $data['total_score'] = intval($rule['single_count']) * intval($rule['single_score'])
                + intval($rule['multiple_count']) * intval($rule['multiple_score'])
                + intval($rule['judgment_count']) * intval($rule['judgment_score'])
                + intval($rule['question_count']) * intval($rule['question_score'])
                + intval($rule['voice_count']) * intval($rule['voice_score']);
        }

        try {
            // 开始事务
            $this->start_trans();

            $ep_id = $this->_d->insert($data);

            if (!empty($right)) {

                foreach ($right as &$v) {
                    $v['epc_id'] = $ep_id;
                    $v['er_type'] = self::RIGHT_PAPER;
                }
                $this->_d_right->insert_all($right);
            }

            $temp_list = array();
            if ($data['ep_type'] != self::TOPIC_RANDOM) {
                // 如果试卷类型不是随机选题，则按配置抽题
                if (!$topic_list = $this->get_temp_list_by_epid($ep_id)) {
                    E('_ERR_CHOICE_PAPER');

                    return false;
                }

                foreach ($topic_list as $k => $v) {
                    $topic_data = array();
                    $topic_data['ep_id'] = $ep_id;
                    $topic_data['et_id'] = $v['et_id'];
                    $topic_data['score'] = $v['score'];
                    $topic_data['order_num'] = $k;
                    $temp_list[] = $topic_data;
                }
            }

            if (!empty($temp_list)) {
                // 存入备选题目列表
                $this->_d_temp->insert_all($temp_list);
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

        return $ep_id;
    }


    /**
     * 编辑试卷规则
     * @author：daijun
     * @param array $param 传入参数
     * @return bool
     */
    public function rule_save($param = array())
    {
        // 验证ID
        if (empty($param['ep_id'])) {
            E('_EMPTY_EP_ID');

            return false;
        }
        // 验证其他数据
        if (!$data = $this->check_rule_add($param)) {
            E('_EMPTY_SAVE_DATA');

            return false;
        }
        // 此处组装其他必填的字段信息
        $data['tag_data'] = !empty($param['tag_data']) ? serialize($param['tag_data']) : '';

        // 此处查询试卷原始数据
        $peper = $this->_d->get($param['ep_id']);

        $topic_list = array();
        // 如果试卷规则和题库抽题规则无变化，则无需重新抽题
        if ($peper['rule'] == $data['rule'] && $peper['bank_topic_data'] == $data['bank_topic_data'] && $peper['search_type'] == $data['search_type'] && $data['tag_data'] == $peper['tag_data']) {
            // 不重新抽题
            $is_choice = 0;
        } else {
            // 重新抽题
            $is_choice = 1;
        }

        if (!empty($param['rule'])) {
            // 随机选题 此处需要计算总分
            $rule = $param['rule'];

            $data['total_score'] = intval($rule['single_count']) * intval($rule['single_score'])
                + intval($rule['multiple_count']) * intval($rule['multiple_score'])
                + intval($rule['judgment_count']) * intval($rule['judgment_score'])
                + intval($rule['question_count']) * intval($rule['question_score'])
                + intval($rule['voice_count']) * intval($rule['voice_score']);
        }

        try {
            // 开始事务
            $this->start_trans();

            // 更新试卷表
            $ep_id = $this->_d->update($param['ep_id'], $data);

            if ($is_choice == 1) {

                // 删除备选题目列表
                $this->_d_temp->delete_by_conds(array('ep_id' => $param['ep_id']));

                // 删除已选题目列表
                $this->_d_snapshot->delete_by_conds(array('ep_id' => $param['ep_id']));

                // 抽题存入
                $temp_list = array();
                if ($data['ep_type'] != self::TOPIC_RANDOM && $is_choice == 1) {
                    // 如果试卷出题规则不是随机，则按配置抽题
                    if (!$topic_list = $this->get_temp_list_by_epid($param['ep_id'])) {
                        E('_ERR_CHOICE_PAPER');

                        return false;
                    }
                    // 循环组装入库数据
                    foreach ($topic_list as $k => $v) {
                        $topic_data = array();
                        $topic_data['ep_id'] = $param['ep_id'];
                        $topic_data['et_id'] = $v['et_id'];
                        $topic_data['score'] = $v['score'];
                        $topic_data['order_num'] = $k;
                        $temp_list[] = $topic_data;
                    }
                }

                if (!empty($temp_list)) {
                    // 存入备选题目列表
                    $this->_d_temp->insert_all($temp_list);
                }
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

        return $ep_id;
    }


    /**
     * 编辑试卷基本信息
     * @author：daijun
     * @param array $param 传入参数
     * @return bool
     */
    public function base_save($param = array())
    {
        // 验证属性
        if (!$data = $this->check_base_save($param)) {
            E('_EMPTY_SAVE_DATA');

            return false;
        }

        $right = $data['right'];
        unset($data['right']);

        $uids_now = array();
        $uids_old = array();
        // 获取试卷信息
        $paper = $this->_d->get($param['ep_id']);

        // 如果是从已发布到发布动作
        if ($data['exam_status'] == self::PAPER_PUBLISH) {

            // 查询组装总题目数量
            if ($paper['ep_type'] != self::TOPIC_RANDOM) {
                // 如果不是随机出题，此处查询该试卷的试题总数
                $data['topic_count'] = $this->_d_snapshot->count_by_conds(array('ep_id' => $param['ep_id']));

            } else {
                
                $rule = unserialize($paper['rule']);
                $data['topic_count'] = intval($rule['single_count'])
                    + intval($rule['multiple_count'])
                    + intval($rule['judgment_count'])
                    + intval($rule['question_count'])
                    + intval($rule['voice_count']);
            }

            // 此处获取所有应参与人员的ID集合
            $right_serv = new RightService();
            $right_res = $right_serv->format_db_data($right);

            // 获取数据更新后的权限用户列表
            $right_res['is_all'] = $data['is_all'];
            $uids_now = $right_serv->list_uids_by_right($right_res);

            // 如果是已发布的试卷进行编辑再次发布，此处需查询之前的权限数据，推送消息会用到
            if ($paper['exam_status'] == self::PAPER_PUBLISH) {

                // 权限查询条件
                $conds = array(
                    'epc_id' => $param['ep_id'],
                    'er_type' => AnswerService::RIGHT_PAPER
                );

                // 获取未参与考试人员列表及人数
                $answer_serv = new AnswerService();
                $unjoin_data = $answer_serv->get_unjoin_data($conds, $param['ep_id'], $paper['is_all']);

                // 获取数据更新前的权限用户列表
                $uids_old = $unjoin_data['unjoin_list'];
            }

            // 参与人数
            $data['unjoin_count'] = count($uids_now);
            // 发布时间
            $data['publish_time'] = MILLI_TIME;

        }


        // 查询分类详情
        $cate_data = $this->_d_cate->get($paper['ec_id']);

        // 将分类的状态赋值给试卷状态
        $data['cate_status'] = $cate_data['ec_status'];

        try {
            // 开始事务
            $this->start_trans();

            // 更新试卷表
            $this->_d->update($param['ep_id'], $data);

            // 删除之前的权限
            $this->_d_right->delete_by_conds(array('epc_id' => $param['ep_id'], 'er_type' => self::RIGHT_PAPER));

            if (!empty($right)) {
                // 新增权限数据
                $this->_d_right->insert_all($right);
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

        // 1.推送消息 2.设置定时任务
        if ($data['exam_status'] == self::PAPER_PUBLISH) {
            $ep_id = $param['ep_id'];
            // 是否推荐到首页feed流
            if ($data['is_recommend']) {

                $url = convertUrl(QY_DOMAIN . '/Public/Rpc/Recommender/ArticleNew');
                $data_send = [
                    'app' => 'exam',
                    'dataCategoryId' => '',
                    'dataId' => $ep_id,
                    'title' => $paper['ep_name'],
                    'summary' => $data['intro'],
                    'attachId' => $data['cover_id'],
                    'pic' => $this->format_cover($data['cover_id']),
                    'url' => 'Exam/Frontend/Index/Msg?ep_id=' . $ep_id
                ];

                \Com\Rpc::phprpc($url)->invoke('Index', $data_send);
            } else {
                $url = convertUrl(QY_DOMAIN . '/Public/Rpc/Recommender/ArticleDelete');
                $data_send = array(
                    'app' => 'exam',
                    'dataCategoryId' => '',
                    'dataId' => $ep_id,
                );
                \Com\Rpc::phprpc($url)->invoke('Index', $data_send);
            }

            // 如果发送消息通知
            if ($data['is_pushmsg']) {
                $params['name'] = $paper['ep_name'];
                $params['description'] = $data['intro'];
                $params['img_id'] = $data['cover_id'];
                $params['id'] = $ep_id;

                if ($paper['exam_status'] == self::PAPER_PUBLISH) {
                    // 如果之前就是已发布的状态,此处取差集
                    $params['uids'] = array_diff($uids_now, $uids_old);
                    if (!empty($params['uids'])) {
                        $this->send_msg($params, self::ANSWER_COMING_MSG);
                    }

                } else {
                    // 从草稿发布
                    $params['uids'] = $uids_now;
                    $this->send_msg($params, self::ANSWER_COMING_MSG);
                }
            }
            // 定时任务
            $this->cron_add($ep_id);
        }

        return true;
    }

    /**
     * 添加试题规则参数验证
     * @author：daijun
     * @param array $param
     * @return array|bool
     */
    public function check_rule_add($param = array())
    {

        $data = array();
        // 验证试卷名称不能为空
        if (empty($param['ep_name'])) {
            E('_EMPTY_PEPER_NAME');

            return false;
        }

        // 验证试卷名称长度
        if ($this->utf8_strlen($param['ep_name']) > 64) {
            E('_ERR_PEPER_NAME_LENGTH');

            return false;
        }
        $data['ep_name'] = $param['ep_name'];

        // 验证试卷分类
        if (empty($param['ec_id'])) {
            E('_EMPTY_CATE_ID');

            return false;
        }
        $data['ec_id'] = $param['ec_id'];

        // 验证试卷类型
        if (!isset($param['paper_type'])) {
            E('_EMPTY_PAPER_TYPE');

            return false;
        }
        $data['paper_type'] = $param['paper_type'];

        // 验证出题类型
        if (empty($param['ep_type'])) {
            E('_EMPTY_CHOICE_TYPE');

            return false;
        }
        $data['ep_type'] = $param['ep_type'];

        // 验证所选题库列表
        if (empty($param['bank_list']) || !is_array($param['bank_list'])) {
            E('_EMPTY_BANK_LIST');

            return false;
        }

        // 如果选择的题库多于50，则抛错
        if (count($param['bank_list']) > 50) {
            E('_ERR_BANK_LIST_MAX');

            return false;
        }

        // 获取题库列表
        $eb_ids = array_column($param['bank_list'], 'eb_id');
        if (empty($eb_ids)) {
            E('_EMPTY_BANK_LIST');

            return false;
        }
        $data['bank_data'] = implode(',', $eb_ids);

        // 验证筛选类型
        if (!empty($param['search_type'])) {
            $data['search_type'] = $param['search_type'];
        }

        // 如果是规则抽题 和 随机选题
        if ($param['ep_type'] == self::TOPIC_RULE || $param['ep_type'] == self::TOPIC_RANDOM) {

            // 验证出题规则是否设置
            if (empty($param['rule'])) {
                E('_EMPTY_BANK_RULE');

                return false;
            }

            $rule = $param['rule'];

            // 根据规则计算总题数
            $total_topic = intval($rule['single_count']) + intval($rule['multiple_count']) + intval($rule['judgment_count']) + intval($rule['question_count']) + intval($rule['voice_count']);

            if ($total_topic == 0) {
                // 题目总数为0，则抛错
                E('_ERR_TOTAL_TOPIC');

                return false;
            }

            // 判断如果题目数不为空，则对应分数也不能为空，否则抛错
            if (!empty($rule['single_count']) && !preg_match("/^[1-9][0-9]*$/", $rule['single_score'])) {
                // 单选题
                E('_ERR_TOPIC_SCORE');

                return false;
            } elseif (!empty($rule['multiple_count']) && !preg_match("/^[1-9][0-9]*$/", $rule['multiple_score'])) {
                // 多选题
                E('_ERR_TOPIC_SCORE');

                return false;
            } elseif (!empty($rule['judgment_count']) && !preg_match("/^[1-9][0-9]*$/", $rule['judgment_score'])) {
                // 判断题
                E('_ERR_TOPIC_SCORE');

                return false;
            } elseif (!empty($rule['question_count']) && !preg_match("/^[1-9][0-9]*$/", $rule['question_score'])) {
                // 问答题
                E('_ERR_TOPIC_SCORE');

                return false;
            } elseif (!empty($rule['voice_count']) && !preg_match("/^[1-9][0-9]*$/", $rule['voice_score'])) {
                // 语音题
                E('_ERR_TOPIC_SCORE');

                return false;
            }

            // 序列化抽题规则
            $data['rule'] = serialize($rule);

            // 验证所选题库设置的题目数
            if (empty($param['bank_topic_data'])) {
                E('_EMPTY_BANK_TOPIC_DATA');

                return false;
            }

            // 循环去除多余的字段
            foreach ($param['bank_topic_data'] as &$v) {
                unset($v['$$hashKey']);
            }
            // 序列化题库题目设置
            $data['bank_topic_data'] = serialize($param['bank_topic_data']);

        } else {
            $data['rule'] = '';
            $data['bank_topic_data'] = '';
        }

        return $data;
    }


    /**
     * 添加试卷基本信息参数验证
     * @author：daijun
     * @param array $param
     * @return array|bool
     */
    public function check_base_save($param = array())
    {
        $data = array();

        // 验证ID
        if (empty($param['ep_id'])) {
            E('_EMPTY_EP_ID');

            return false;
        }

        // 验证权限设置
        if (!is_numeric($param['is_all'])) {
            E('_EMPTY_RIGHT');

            return false;
        }
        $data['is_all'] = $param['is_all'];

        // 验证开始时间不能为空
        if (empty($param['begin_time'])) {
            E('_EMPTY_BEGIN_TIME');

            return false;
        }

        // 验证开始时间不能早于当前时间
        if ($param['begin_time'] <= MILLI_TIME) {
            E('_ERR_BEGIN_TIME');

            return false;
        }

        $data['begin_time'] = $param['begin_time'];

        // 验证结束时间不能为空
        if (empty($param['end_time'])) {
            E('_EMPTY_END_TIME');

            return false;
        }

        // 验证结束时间不能早于开始时间
        if ($param['end_time'] <= $param['begin_time']) {
            E('_ERR_END_TIME');

            return false;
        }

        // 验证考试时长
        if (($param['begin_time'] + $param['paper_time'] * 60 * 1000) > $param['end_time']) {
            E('_ERR_PAPER_TIME');

            return false;
        }

        $data['end_time'] = $param['end_time'];

        // 验证考试时长
        if (empty($param['paper_time'])) {
            E('_EMPTY_PAPER_TIME');

            return false;
        }
        $data['paper_time'] = $param['paper_time'];

        // 验证发送提醒
        if (!isset($param['is_pushmsg'])) {
            E('_EMPTY_PUSHMSG');

            return false;
        }
        $data['is_pushmsg'] = intval($param['is_pushmsg']);

        // 验证设置考试通知
        if (!isset($param['is_notify'])) {
            E('_EMPTY_IS_NOTIFY');

            return false;
        }
        $data['is_notify'] = intval($param['is_notify']);

        // 如果开启了考试通知,则时间设置不能为空
        if ($param['is_notify']) {
            if (empty($param['notify_begin'])) {
                E('_ERR_BEGIN_NOTIFY_TIME');

                return false;
            }
            if (empty($param['notify_end'])) {
                E('_ERR_END_NOTIFY_TIME');

                return false;
            }
        }

        // 开始前通知时间
        if (!empty($param['notify_begin'])) {
            // 考试提醒时间验证
            if (($param['begin_time'] - intval($param['notify_begin']) * 60 * 1000) <= MILLI_TIME) {
                E('_ERR_BEGIN_NOTIFY_TIME');

                return false;
            }
            $data['notify_begin'] = intval($param['notify_begin']);
        }

        // 结束前通知时间
        if (!empty($param['notify_end'])) {
            // 考试提醒时间验证
            if (($param['end_time'] - intval($param['notify_end']) * 60 * 1000) <= MILLI_TIME) {
                E('_ERR_END_NOTIFY_TIME');

                return false;
            }

            $data['notify_end'] = intval($param['notify_end']);
        }

        // 验证开启推荐
        if (!isset($param['is_recommend'])) {
            E('_EMPTY_IS_RECOMMEND');

            return false;
        }
        $data['is_recommend'] = intval($param['is_recommend']);

        // 显示答案
        $data['answer_resolve'] = 1;

        // 验证及格分数
        if (empty($param['pass_score'])) {
            E('_EMPTY_PASS_SCORE');

            return false;
        }
        $data['pass_score'] = intval($param['pass_score']);

        // 验证考试说明
        if (empty($param['intro'])) {
            E('_EMPTY_INTRO');

            return false;
        }
        $data['intro'] = $param['intro'];

        if (!empty($param['cover_id'])) {
            $data['cover_id'] = $param['cover_id'];
        }

        // 验证试卷状态
        if (empty($param['exam_status'])) {
            E('_EMPTY_EXAM_STATUS');

            return false;
        }
        $data['exam_status'] = intval($param['exam_status']);

        // 如果不是全公司，组装right数据
        $right = array();
        if ($data['is_all'] == self::AUTH_NOT_ALL) {

            // 用户集合
            if (!empty($param['right']['user_list'])) {
                $users = array_column($param['right']['user_list'], 'memID');
                foreach ($users as $v) {
                    $arr = array();
                    $arr['epc_id'] = $param['ep_id'];
                    $arr['er_type'] = self::RIGHT_PAPER;
                    $arr['uid'] = $v;
                    $arr['cd_id'] = '';
                    $arr['tag_id'] = '';
                    $arr['job_id'] = '';
                    $arr['role_id'] = '';
                    $right[] = $arr;
                }
            }
            // 部门集合
            if (!empty($param['right']['dp_list'])) {
                $dps = array_column($param['right']['dp_list'], 'dpID');
                foreach ($dps as $v) {
                    $arr = array();
                    $arr['epc_id'] = $param['ep_id'];
                    $arr['er_type'] = self::RIGHT_PAPER;
                    $arr['uid'] = '';
                    $arr['cd_id'] = $v;
                    $arr['tag_id'] = '';
                    $arr['job_id'] = '';
                    $arr['role_id'] = '';
                    $right[] = $arr;
                }
            }
            // 标签集合
            if (!empty($param['right']['tag_list'])) {
                $tags = array_column($param['right']['tag_list'], 'tagID');
                foreach ($tags as $v) {
                    $arr = array();
                    $arr['epc_id'] = $param['ep_id'];
                    $arr['er_type'] = self::RIGHT_PAPER;
                    $arr['uid'] = '';
                    $arr['cd_id'] = '';
                    $arr['tag_id'] = $v;
                    $arr['job_id'] = '';
                    $arr['role_id'] = '';
                    $right[] = $arr;
                }
            }
            // 岗位集合
            if (!empty($param['right']['job_list'])) {
                $jobs = array_column($param['right']['job_list'], 'jobID');
                foreach ($jobs as $v) {
                    $arr = array();
                    $arr['epc_id'] = $param['ep_id'];
                    $arr['er_type'] = self::RIGHT_PAPER;
                    $arr['uid'] = '';
                    $arr['cd_id'] = '';
                    $arr['tag_id'] = '';
                    $arr['job_id'] = $v;
                    $arr['role_id'] = '';
                    $right[] = $arr;
                }
            }

            // 角色集合
            if (!empty($param['right']['role_list'])) {
                $roles = array_column($param['right']['role_list'], 'roleID');
                foreach ($roles as $v) {
                    $arr = array();
                    $arr['epc_id'] = $param['ep_id'];
                    $arr['er_type'] = self::RIGHT_PAPER;
                    $arr['uid'] = '';
                    $arr['cd_id'] = '';
                    $arr['tag_id'] = '';
                    $arr['job_id'] = '';
                    $arr['role_id'] = $v;
                    $right[] = $arr;
                }
            }

            // 权限不能都为空
            if (empty($right)) {
                E('_EMPTY_RIGHT');

                return false;
            }

        }
        $data['right'] = $right;

        return $data;
    }

    /**
     * 格式化获取试卷规则数据
     * @author daijun
     * @param array $data 试卷信息
     * @param array $bank_topic_total 各题库含有的各题型的总数
     * @return array
     */
    public function format_rule_data($data = array(), $bank_topic_total = array())
    {
        $result = array();

        $result['ep_name'] = $data['ep_name'];
        $result['ec_id'] = intval($data['ec_id']);
        $result['paper_type'] = intval($data['paper_type']);
        $result['ep_type'] = intval($data['ep_type']);
        $result['search_type'] = intval($data['search_type']);

        // 获取题库ID集合（一维数组）
        $eb_ids = explode(',', $data['bank_data']);
        // 查询题库列表
        $bank_data = $this->_d_bank->list_by_conds(array('eb_id' => $eb_ids), null, array(),
            'eb_id,eb_name,single_count,multiple_count,judgment_count,question_count,voice_count');
        // 格式化组装数据
        $bank_list = array();
        foreach ($bank_data as $v) {
            $arr = array();
            $arr['eb_id'] = intval($v['eb_id']);
            $arr['eb_name'] = $v['eb_name'];
            $arr['single_count'] = intval($v['single_count']);
            $arr['multiple_count'] = intval($v['multiple_count']);
            $arr['judgment_count'] = intval($v['judgment_count']);
            $arr['question_count'] = intval($v['question_count']);
            $arr['voice_count'] = intval($v['voice_count']);
            $bank_list[] = $arr;
        }
        $result['bank_list'] = $bank_list;

        $bank_topic = array_combine_by_key($bank_topic_total, 'eb_id');
        // 格式化题库出题数量数据
        if (!empty($data['bank_topic_data'])) {
            $bank_topic_data = unserialize($data['bank_topic_data']);

            $b_topic_data = array();

            foreach ($bank_topic_data as $k => $v) {
                $bdata = array();
                $bdata['eb_id'] = intval($v['eb_id']);
                $bdata['eb_name'] = $v['eb_name'];
                $bdata['single_count'] = intval($v['single_count']);
                $bdata['multiple_count'] = intval($v['multiple_count']);
                $bdata['judgment_count'] = intval($v['judgment_count']);
                $bdata['question_count'] = intval($v['question_count']);
                $bdata['voice_count'] = intval($v['voice_count']);
                $bdata['single_count_max'] = $bank_topic[$v['eb_id']]['single_count'];
                $bdata['multiple_count_max'] = $bank_topic[$v['eb_id']]['multiple_count'];
                $bdata['judgment_count_max'] = $bank_topic[$v['eb_id']]['judgment_count'];
                $bdata['question_count_max'] = $bank_topic[$v['eb_id']]['question_count'];
                $bdata['voice_count_max'] = $bank_topic[$v['eb_id']]['voice_count'];
                $b_topic_data[] = $bdata;
            }
            $result['bank_topic_data'] = $b_topic_data;
        } else {
            $result['bank_topic_data'] = array();
        }

        // 格式化出题规则数据
        if (!empty($data['rule'])) {
            $rule = unserialize($data['rule']);
            foreach ($rule as $k => $v) {
                $rule[$k] = intval($v);
            }

            $result['rule'] = $rule;
        } else {
            $result['rule'] = array();
        }

        // 格式化标签数据
        if (!empty($data['tag_data'])) {
            $tag_data = unserialize($data['tag_data']);
            // 标签ID集合
            $tag_ids = array_column($tag_data, 'etag_id');
            // 查询标签列表
            $tag_list = $this->_d_tag->list_by_conds(array('etag_id' => $tag_ids));
            $tag_list = array_combine_by_key($tag_list, 'etag_id');

            // 属性ID集合
            $attr_ids = array();
            foreach ($tag_data as $v) {
                $attr_ids_arr = array();
                if (is_array($v['attr_data'])) {
                    $attr_ids_arr = array_column($v['attr_data'], 'attr_id');
                }

                if (!empty($attr_ids_arr) && is_array($attr_ids_arr)) {
                    $attr_ids = array_merge($attr_ids, $attr_ids_arr);
                }
            }
            // 查询属性列表
            $attr_list = $this->_d_attr->list_by_conds(array('attr_id' => $attr_ids));
            $attr_list = array_combine_by_key($attr_list, 'attr_id');

            // 循环格式化组装标签属性
            $tag_format_data = array();
            foreach ($tag_data as $k => $v) {

                $tag_format_data[$k]['etag_id'] = intval($v['etag_id']);
                $tag_format_data[$k]['etag_name'] = $tag_list[$v['etag_id']]['tag_name'];
                // 循环格式化属性
                $attr = array();
                foreach ($v['attr_data'] as $_k => $_v) {
                    $attr[$_k]['attr_id'] = intval($_v['attr_id']);
                    $attr[$_k]['attr_name'] = $attr_list[$_v['attr_id']]['attr_name'];
                }
                $tag_format_data[$k]['attr_data'] = $attr;
            }

            $result['tag_data'] = $tag_format_data;

        } else {
            $result['tag_data'] = array();
        }

        return $result;
    }


    /**
     * 格式化试卷信息
     * @author: 蔡建华
     * @param array $data 试卷信息
     * @return array
     */
    public function format_paper_detail($data = array())
    {
        $arr = array(
            "paper_type" => intval($data['paper_type']),
            "ep_name" => $data['ep_name'],
            "cover_url" => empty($data['cover_id']) ? '' : imgUrl($data['cover_id']),
            "total_score" => $data['total_score'],
            "pass_score" => $data['pass_score'],
            "topic_count" => intval($data['topic_count']),
            "paper_time" => $data['paper_time'],
            "begin_time" => $data['begin_time'],
            "end_time" => $data['end_time'],
            "intro" => $data['intro'],
            "right" => $data['right'],
            "is_all" => intval($data['is_all']),
            "ep_status" => $this->paper_status($data['exam_status'], $data['begin_time'], $data['end_time'])
        );

        return $arr;
    }

    /**
     * 重新抽题
     * @author：daijun
     * @param int $ep_id
     * @return bool
     */
    public function extract_topic($ep_id = 0)
    {
        // 如果试卷出题规则不是随机，则按配置抽题
        if (!$topic_list = $this->get_temp_list_by_epid($ep_id)) {
            E('_ERR_CHOICE_PAPER');

            return false;
        }

        try {
            // 开始事务
            $this->start_trans();

            // 删除快照表（即已选择的试题）
            $this->_d_snapshot->delete_by_conds(array('ep_id' => $ep_id));

            // 删除试卷备选题目信息
            $this->_d_temp->delete_by_conds(array('ep_id' => $ep_id));

            $temp_list = array();
            foreach ($topic_list as $k => $v) {
                $topic_data = array();
                $topic_data['ep_id'] = $ep_id;
                $topic_data['et_id'] = $v['et_id'];
                $topic_data['score'] = $v['score'];
                $topic_data['order_num'] = $k;
                $temp_list[] = $topic_data;
            }

            if (!empty($temp_list)) {
                // 存入备选题目列表
                $this->_d_temp->insert_all($temp_list);
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
     * 按照试卷设置的规则抽题
     * @author：daijun
     * @param int $ep_id 试卷ID
     * @param int $type （0：后台抽题，1：前台抽题）
     * @return array|bool
     */
    public function get_temp_list_by_epid($ep_id = 0, $type = 0)
    {
        $paper = $this->_d->get($ep_id);
        if (empty($paper)) {
            E('_EMPTY_PAPER_DATA');

            return false;
        }
        // 属性数据
        $attr_ids = array();
        if (!empty($paper['tag_data'])) {
            $tag_data = unserialize($paper['tag_data']);
            foreach ($tag_data as $v) {
                $attr_ids_arr = array();
                if (is_array($v['attr_data'])) {
                    $attr_ids_arr = array_column($v['attr_data'], 'attr_id');
                }

                if (!empty($attr_ids_arr) && is_array($attr_ids_arr)) {
                    $attr_ids = array_merge($attr_ids, $attr_ids_arr);
                }
            }
        }

        // 抽题模式的规则
        $rule_data = array();
        if (!empty($paper['rule'])) {
            $rule_data = unserialize($paper['rule']);
        }

        // 题库题目设置数据
        $bank_topic_data = array();
        if (!empty($paper['bank_topic_data'])) {
            $bank_topic_data = unserialize($paper['bank_topic_data']);
        }

        // 获取题库的ID集合
        $bank_ids = explode(',', $paper['bank_data']);

        $temp_list = array();
        if ($paper['ep_type'] == self::TOPIC_CUSTOMER && $type == 0) {
            // 后台自主选题
            if (!$temp_list = $this->choice_topic($bank_ids, $attr_ids, $paper)) {

                E('_ERR_CHOICE_PAPER');

                return false;
            }

        } elseif ($paper['ep_type'] == self::TOPIC_RULE && $type == 0) {
            // 后台规则抽题
            if (!$temp_list = $this->rule_topic($bank_ids, $attr_ids, $paper, $bank_topic_data, $rule_data)) {
                E('_ERR_CHOICE_PAPER');

                return false;
            }

        } elseif ($paper['ep_type'] == self::TOPIC_RANDOM && $type == 1) {
            // 前端随机抽题
            if (!$temp_list = $this->rule_topic($bank_ids, $attr_ids, $paper, $bank_topic_data, $rule_data)) {
                E('_ERR_CHOICE_PAPER');

                return false;
            }
        }

        if (empty($temp_list)) {
            E('_ERR_CHOICE_PAPER');

            return false;
        }

        return $temp_list;
    }

    /**
     * 查询考试统计列表
     * @author: 英才
     * @param array $params 查询条件数组
     * @param null $page_option 分页
     * @param array $order_option 排序数组
     * @param String $fields 查询字段
     * @return array|bool
     */
    public function list_search_where($params, $page_option = null, $order_option = array(), $fields = '*')
    {

        try {
            return $this->_d->list_search_where($params, $page_option, $order_option, $fields);
        } catch (\Exception $e) {
            E($e->getCode() . ":" . $e->getMessage());

            return false;
        }
    }

    /**
     * 统计考试统计列表总数
     * @author: 英才
     * @param array $params 查询条件数组
     * @return array|bool
     */
    public function count_search_where($params)
    {
        try {
            return $this->_d->count_search_where($params);
        } catch (\Exception $e) {
            E($e->getCode() . ":" . $e->getMessage());

            return false;
        }
    }

    /**
     * 按规则抽题
     * @author daijun
     * @param array $bank_ids 题库ID集合
     * @param array $attr_ids 属性ID集合
     * @param array $paper 试卷信息
     * @param array $bank_topic_data 题库题目数量设置
     * @param array $rule_data 题目得分规则设置
     * @return array
     */
    public function rule_topic(
        $bank_ids = array(),
        $attr_ids = array(),
        $paper = array(),
        $bank_topic_data = array(),
        $rule_data = array()
    ) {
        $topic_all_list = array();
        if (empty($attr_ids)) {
            // 没有选择标签，则查询所选题库的所有试题
            $topic_list = $this->_d_topic->list_by_conds(array('eb_id' => $bank_ids));
            // 取出所有题目的ID
            $topic_list_key = array_combine_by_key($topic_list, 'et_id');

            $bank_topic_attr = array();
            foreach ($topic_list_key as $v) {
                $bank_topic_attr[$v['eb_id']][] = $v['et_id'];
            }

            // 循环题库
            foreach ($bank_ids as $k => $v) {
                // 对该题库下的题目ID集合去重
                $et_ids = array_unique($bank_topic_attr[$v]);
                // 如果题库取出的题目ID集合为空，则该题库无需抽题
                if (empty($et_ids)) {
                    continue;
                }
                // 查询该题库下的符合条件的题目列表
                $topic_list = $this->_d_topic->list_by_conds(array('et_id' => $et_ids));

                // 将题目列表按照题目类型分组返回
                if (!$topic_data = $this->format_by_et_type($topic_list, $bank_topic_data, $rule_data, $v)) {
                    E('_ERR_CHOICE_PAPER');

                    return false;
                }

                if (!empty($topic_data) && is_array($topic_data)) {
                    // 合并组装数组
                    $topic_all_list = array_merge($topic_all_list, $topic_data);
                }
            }

        } else {

            $topic_list_ids = array();
            // 如果设置了标签
            if ($paper['search_type'] == self::SEARCH_ATTR_TYPE_NOT_ALL) {
                // 满足任意一个标签的抽题
                $topic_list_ids = $this->_d_topic_attr->list_by_conds(array(
                    'attr_id' => $attr_ids,
                    'eb_id' => $bank_ids
                ), null, array('eb_id' => 'ASC'));

            } else {
                // 满足所有标签的抽题

                // 初始化题库下的题目IDS数组
                $eb_list = array();

                // 获取满足其中之一条件的所有数据
                $topic_attr_all_list = $this->_d_topic_attr->list_by_conds(array(
                    'eb_id' => $bank_ids,
                    'attr_id' => $attr_ids
                ));

                // 遍历关联关系表
                foreach ($topic_attr_all_list as $item) {
                    $eb_list[$item['et_id']][] = $item['attr_id'];
                }

                $topic_ids = array();
                // 组装符合条件的数据id集合
                foreach ($eb_list as $key => $v) {
                    if (array_intersect($attr_ids, $v) == $attr_ids) {
                        $topic_ids[] = $key;
                    }
                }

                if (!empty($topic_ids)) {
                    // 查询符合条件的试题列表
                    $topic_list_ids = $this->_d_topic->list_by_conds(array('et_id' => $topic_ids));
                } else {
                    $topic_list_ids = array();
                }
            }

            $bank_topic_attr = array();
            foreach ($topic_list_ids as $v) {
                $bank_topic_attr[$v['eb_id']][] = $v['et_id'];
            }

            // 循环题库
            foreach ($bank_ids as $k => $v) {
                // 对该题库下的题目ID集合去重
                $et_ids = array_unique($bank_topic_attr[$v]);

                if (empty($et_ids)) {
                    continue;
                }

                // 查询该题库下的符合条件的题目列表
                $topic_list = $this->_d_topic->list_by_conds(array('et_id' => $et_ids));

                // 将题目列表按照题目类型分组返回
                if (!$topic_data = $this->format_by_et_type($topic_list, $bank_topic_data, $rule_data, $v)) {
                    E('_ERR_CHOICE_PAPER');

                    return false;
                }

                if (!empty($topic_data) && is_array($topic_data)) {
                    // 合并组装数组
                    $topic_all_list = array_merge($topic_all_list, $topic_data);
                }

            }
        }

        if (empty($topic_all_list)) {
            E('_ERR_CHOICE_PAPER');

            return false;
        }

        // 对抽取的题目进行打乱
        $topics = array_combine_by_key($topic_all_list, 'et_id');
        $tp_ids = array_column($topic_all_list, 'et_id');
        // 打乱ID
        shuffle($tp_ids);

        // 循环组装试题信息
        $result_list = array();
        foreach ($tp_ids as $k => $v) {
            $data = array();
            $data = $topics[$v];
            $data['order_num'] = $k + 1;
            $result_list[] = $data;
        }

        return $result_list;
    }


    /**
     * 自主选题
     * @author daijun
     * @param array $bank_ids 题库ID集合
     * @param array $attr_ids 属性ID集合
     * @param array $paper 试卷信息
     * @return array|bool
     */
    public function choice_topic($bank_ids = array(), $attr_ids = array(), $paper = array())
    {

        $topic_list = array();
        if (empty($attr_ids)) {
            // 没有选择标签，则查询所选题库的所有试题
            $topic_list = $this->_d_topic->list_by_conds(array('eb_id' => $bank_ids));

        } else {
            // 如果设置了标签
            if ($paper['search_type'] == self::SEARCH_ATTR_TYPE_NOT_ALL) {
                // 满足任意一个标签的抽题
                $topic_ids = $this->_d_topic_attr->list_by_conds(array(
                    'attr_id' => $attr_ids,
                    'eb_id' => $bank_ids
                ), null, array('eb_id' => 'ASC'));

                // 组装符合条件的题目id集合
                $topic_ids = array_unique(array_column($topic_ids, 'et_id'));

            } else {
                // 满足所有标签的抽题
                // 初始化题库下的题目IDS数组
                $eb_list = array();

                // 获取满足其中之一条件的所有数据
                $topic_attr_all_list = $this->_d_topic_attr->list_by_conds(array(
                    'eb_id' => $bank_ids,
                    'attr_id' => $attr_ids
                ));

                // 遍历关联关系表
                foreach ($topic_attr_all_list as $item) {
                    $eb_list[$item['et_id']][] = $item['attr_id'];
                }

                // 组装符合条件的题目id集合
                $topic_ids = array();
                foreach ($eb_list as $key => $v) {
                    if (array_intersect($attr_ids, $v) == $attr_ids) {
                        $topic_ids[] = $key;
                    }
                }
            }

            if (empty($topic_ids)) {
                E('_ERR_CHOICE_PAPER');

                return false;
            }

            // 判断题目总数
            $topic_total = count($topic_ids);
            $count = 50;
            // 题目ID集合分组
            $topic_ids_arr = array();
            if ($topic_total > $count) {
                // 如果题目总数超过50，则需要进行分页查询，否则用in查询会死掉
                $countpage = ceil($topic_total / $count); // 计算总页面数
                // 对题目ID集合进行分页组装
                for ($i = 1; $i <= $countpage; $i++) {
                    $topic_ids_arr[] = array_slice($topic_ids, $i - 1, $count);
                }
            } else {
                $topic_ids_arr[] = $topic_ids;
            }

            // 循环查询题目列表
            foreach ($topic_ids_arr as $v) {
                // 查询题目列表
                $topic_arr = $this->_d_topic->list_by_conds(array('et_id' => $v));
                // 合并结果数组
                $topic_list = array_merge($topic_arr, $topic_list);
            }
        }

        if (empty($topic_list)) {
            E('_ERR_CHOICE_PAPER');

            return false;
        }

        return $topic_list;

    }


    /**
     * 推送停止考试消息与交卷
     * @author daijun
     * @param array $data 试卷信息
     */
    public function stop_paper($data = array())
    {
        $right_serv = new RightService();
        // 去权限表查询权限信息
        $right = $this->_d_right->list_by_conds(array('er_type' => 0, 'epc_id' => $data['ep_id']));

        // 格式化权限信息
        $right_res = $right_serv->format_db_data($right);

        // 获取数据更新后的权限用户列表
        $right_res['is_all'] = $data['is_all'];

        $params['name'] = $data['ep_name'];
        $params['description'] = $data['intro'];
        $params['img_id'] = $data['cover_id'];
        $params['msg'] = $data['reason'];
        $params['id'] = $data['ep_id'];
        $params['uids'] = $right_serv->list_uids_by_right($right_res);

        // 推送考试停止消息
        if (!empty($params)) {
            $right_serv->send_msg($params, self::ANSWER_AHEAD_END);
        }

        // 删除定时任务
        $this->cron_delete($data);
    }


    /**
     * 将题库题目列表按照题目类型分组返回
     * @author daijun
     * @param array $topic_list 题目列表
     * @param array $bank_topic_data 题库题目数量设置
     * @param array $rule_data 抽题规则
     * @param Int $eb_id 题库ID
     * @return array
     */
    public function format_by_et_type(
        $topic_list = array(),
        $bank_topic_data = array(),
        $rule_data = array(),
        $eb_id = 0
    ) {
        $single_attr = array();   //单选题
        $multiple_attr = array(); //多选题
        $judgment_attr = array(); //判断题
        $question_attr = array(); //问答题
        $voice_attr = array();    //语音题

        //所属题库ID
        $bank_id = $eb_id;

        // 循环题目，按照类型组成新数组
        foreach ($topic_list as $_v) {

            // 试题类型：单选题
            if ($_v['et_type'] == self::TOPIC_TYPE_SINGLE) {
                $single_attr[] = $_v;
                continue;
            }

            // 试题类型：判断题
            if ($_v['et_type'] == self::TOPIC_TYPE_JUDGMENT) {
                $judgment_attr[] = $_v;
                continue;
            }

            // 试题类型：问答题
            if ($_v['et_type'] == self::TOPIC_TYPE_QUESTION) {
                $question_attr[] = $_v;
                continue;
            }

            // 试题类型：多选题
            if ($_v['et_type'] == self::TOPIC_TYPE_MULTIPLE) {
                $multiple_attr[] = $_v;
                continue;
            }

            // 试题类型：语音题
            if ($_v['et_type'] == self::TOPIC_TYPE_VOICE) {
                $voice_attr[] = $_v;
                continue;
            }
        }

        // 取出该题库的出题规则
        $bank_topic = array();
        foreach ($bank_topic_data as $v) {
            if ($v['eb_id'] == $bank_id) {
                $bank_topic = $v;
            }
        }

        // *************************抽题开始*************************
        $single_list = array();   //单选题
        $multiple_list = array(); //多选题
        $judgment_list = array(); //判断题
        $question_list = array(); //问答题
        $voice_list = array();    //语音题
        if (intval($bank_topic['single_count']) > 0) {
            // 存在单选题
            if (intval($bank_topic['single_count']) > count($single_attr)) {
                E('_ERR_CHOICE_PAPER');

                return false;
            }
            $single_list = $this->get_rand_arr($single_attr, intval($bank_topic['single_count']));
            foreach ($single_list as &$v) {
                $v['score'] = $rule_data['single_score'];
            }
        }

        if (intval($bank_topic['multiple_count']) > 0) {
            // 存在多选题题
            if (intval($bank_topic['multiple_count']) > count($multiple_attr)) {

                E('_ERR_CHOICE_PAPER');

                return false;
            }
            $multiple_list = $this->get_rand_arr($multiple_attr, intval($bank_topic['multiple_count']));

            foreach ($multiple_list as &$v) {
                $v['score'] = $rule_data['multiple_score'];
            }
        }


        if (intval($bank_topic['judgment_count']) > 0) {
            // 存在判断题
            if (intval($bank_topic['judgment_count']) > count($judgment_attr)) {
                E('_ERR_CHOICE_PAPER');

                return false;
            }
            $judgment_list = $this->get_rand_arr($judgment_attr, intval($bank_topic['judgment_count']));

            foreach ($judgment_list as &$v) {
                $v['score'] = $rule_data['judgment_score'];
            }
        }

        if (intval($bank_topic['question_count']) > 0) {
            // 存在问答题
            if (intval($bank_topic['question_count']) > count($question_attr)) {

                E('_ERR_CHOICE_PAPER');

                return false;
            }
            $question_list = $this->get_rand_arr($question_attr, intval($bank_topic['question_count']));
            foreach ($question_list as &$v) {
                $v['score'] = $rule_data['question_score'];
            }
        }

        if (intval($bank_topic['voice_count']) > 0) {
            // 存在语音题
            if (intval($bank_topic['voice_count']) > count($voice_attr)) {
                E('_ERR_CHOICE_PAPER');

                return false;
            }
            $voice_list = $this->get_rand_arr($voice_attr, intval($bank_topic['voice_count']));
            foreach ($voice_list as &$v) {
                $v['score'] = $rule_data['voice_score'];
            }
        }

        $topic_list = array_merge($single_list, $judgment_list, $multiple_list, $question_list, $voice_list);

        // *************************抽题结束*************************

        return $topic_list;

    }


    /**
     * 数组随机返回列表
     * @author daijun
     * @param array $topic 原始列表
     * @param int $num 随机数量
     * @return array
     */
    public function get_rand_arr($topic = array(), $num = 0)
    {
        if (count($topic) == $num) {
            return $topic;
        } else {
            $topic_list = array();
            if ($num == 1) {
                $index = rand(0, count($topic) - 1);
                $topic_list[] = $topic[$index];
            } else {
                $index_num = array_rand($topic, $num);
                foreach ($index_num as $v) {
                    $topic_list[] = $topic[$v];
                }
            }
        }

        return $topic_list;
    }


    /**
     *  发布试卷定时任务添加
     *  $param arrary
     *          ep_id => 试卷id
     */
    public function cron_add($ep_id)
    {

        // 实例化定时任务类
        $cron_serv = new Cron(Service::instance());

        //获取问卷基本信息
        $data = $this->_d->get($ep_id);

        // 存在定时发布
        if (!empty($data['begin_corn'])) {
            $cron_serv->delete($data['begin_corn']);
        }
        // 存在定时提醒
        if (!empty($data['end_cron'])) {
            $cron_serv->delete($data['end_cron']);
        }

        // 需要定时通知的试卷id
        $res_params = array(
            'ep_id' => $ep_id,
        );
        // json参数
        $json_params = json_encode($res_params);

        // 初始化定时任务入库id
        $begin_corn = '';
        $end_cron = '';


        // 如果开始前提醒时间不为空
        if (!empty($data['notify_begin'])) {

            // 获取截止提醒时间
            $remind_time = $data['begin_time'] - $data['notify_begin'] * 60 * 1000;

            // 考试开始前提醒
            $conds_remind = array(
                'crRemark' => 'exam_notify_begin',
                'crType' => 2,
                'crParams' => $json_params,
                'crMethod' => 'POST',
                'crReqUrl' => oaUrl('Frontend/Callback/RemindBegin'), // 回调地址
                'crTimes' => 1,
                'crCron' => rgmdate((String)$remind_time, 's i G j n ? Y'),
                'crMonitorExecution' => 0,
                'crDescription' => '考试开始提醒',
            );

            // 创建定时任务
            $res_remind = $cron_serv->add($conds_remind); // crId
            $begin_corn = $res_remind['crId'];
        }


        // 如果结束前提醒时间不为空
        if (!empty($data['notify_end'])) {

            // 获取截止提醒时间
            $remind_time = $data['end_time'] - $data['notify_end'] * 60 * 1000;

            //考试结束前提醒
            $conds_remind = array(
                'crRemark' => 'exam_notify_end',
                'crType' => 2,
                'crParams' => $json_params,
                'crMethod' => 'POST',
                'crReqUrl' => oaUrl('Frontend/Callback/RemindEnd'), // 回调地址
                'crTimes' => 1,
                'crCron' => rgmdate((String)$remind_time, 's i G j n ? Y'),
                'crMonitorExecution' => 0,
                'crDescription' => '考试结束提醒',
            );

            // 创建定时任务
            $res_remind = $cron_serv->add($conds_remind); // crId
            $end_cron = $res_remind['crId'];
        }


        $this->_d->update($ep_id,
            array(
                'begin_corn' => $begin_corn,
                'end_cron' => $end_cron,
            ));

        return true;
    }

    /**
     *  删除试卷定时任务
     *  $data arrary 试卷信息
     */
    public function cron_delete($data = array())
    {

        // 实例化定时任务类
        $cron_serv = new Cron(Service::instance());

        // 存在定时发布
        if (!empty($data['begin_corn'])) {
            $cron_serv->delete($data['begin_corn']);
        }
        // 存在定时提醒
        if (!empty($data['end_cron'])) {
            $cron_serv->delete($data['end_cron']);
        }

        return true;
    }

    /*
     * 根据条件更新试卷且不修改最后更新时间
     * 鲜彤
     */
    public function update_by_paper($conds = array(), $data = array())
    {
        return $this->_d->update_by_paper($conds, $data);
    }

}
