<?php
/**
 * 闯关-答卷表
 * @author: houyingcai
 * @email:    594609175@qq.com
 * @date :  2017-05-19 17:51:32
 * @version $Id$
 */

namespace Common\Service;

use Common\Model\BreakDetailModel;
use Common\Model\BreakModel;
use Common\Model\TopicModel;
use Common\Common\Lcs;

class BreakService extends AbstractService
{
    /**
     * @var TopicModel
     */
    protected $_topic_model;
    /**
     * @var BreakDetailModel
     */
    protected $_break_detail_model;

    // 构造方法
    public function __construct()
    {
        $this->_d = new  BreakModel();
        $this->_topic_model = new TopicModel();
        $this->_break_detail_model = new  BreakDetailModel();
        parent::__construct();
    }

    /** 开始闯关
     * @author: 蔡建华
     * @param int $ec_id 课程ID
     * @param string $et_ids 题库
     * @param string $uid 用户uid
     * return int ebreak_id 答题ID
     */
    function start_break($ec_id = 0, $et_ids = '', $uid = '')
    {
        // 课程ID
        if (empty($ec_id)) {
            E('_EMPTY_BREAK_CID');
            return false;
        }
        // 答题Id
        if (empty($et_ids)) {
            E('_EMPTY_BREAK_TID');

            return false;
        }
        // 答题ID分隔
        $et_ids = explode(',', $et_ids);
        if (empty($et_ids)) {
            E('_EMPTY_BREAK_TID');

            return false;
        }
        // 组装 考试开始基本数据
        $base = array(
            'ec_id' => $ec_id,
            'uid' => $uid,
            'my_begin_time' => MILLI_TIME,
            'my_end_time' => 0,
            'my_error_num' => 0,
            'my_is_pass' => 0,
        );
        // 获取试题
        $snapshot = $this->_topic_model->list_by_conds(array("et_id" => $et_ids, 'et_type' => array(1, 2, 4)));
        // 没有题抛出错误
        if (empty($snapshot)) {
            E('_ERR_BANK_TOPIC_FAILED');

            return false;
        }
        $topic = array();
        $topic_ids = array();
        try {
            // 开始事务
            $this->start_trans();
            //插入考试信息
            $ebreak_id = $this->_d->insert($base);
            foreach ($snapshot as $key => $value) {
                // 题目类型(1:单选题 2:判断题 3:问答题 4:多选题 5:语音题)
                if (!in_array($value['et_type'], array(1, 2, 4))) {
                    continue;
                }
                $topic_ids[] = $value['et_id'];
                $val['ec_id'] = $ec_id;
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
                $val = array(
                    'ebreak_id' => $ebreak_id,
                    'et_option' => $value['options'],
                    'et_detail' => serialize($et_detail),
                    'order_num' => $key + 1,
                    'is_pass' => 0,
                    'score' => $value['score'],
                    'my_score' => 0,
                );
                $topic[] = $val;
            }
            //插入闯关试题
            $this->_break_detail_model->insert_all($topic);
            // 开始创建答卷试题
            $this->commit();

            //返回答卷ID
            return array('ebreak_id' => $ebreak_id);
        } catch (\Think\Exception $e) {
            $this->rollback();
            E('_ERR_ADD_BREAK_FAILED');

            return false;

        } catch (\Exception $e) {
            $this->rollback();
            E('_ERR_ADD_BREAK_FAILED');

            return false;
        }
    }

    /** 自动提交试卷
     * @author: 蔡建华
     * @param int $ea_id 答卷ID
     * @param int $uid 用户ID
     * @return bool
     */
    public function submit_papers($ebreak_id = 0, $uid = 0)
    {
        // 判断答卷ID是否为空
        if (!$ebreak_id) {
            E('_EMPTY_BREAK_ID');

            return false;
        }
        // 判断用户ID不能为空
        if (!$uid) {
            E('_EMPTY_UID');

            return false;
        }
        //获取答卷信息
        $ea_data = $this->get_by_conds(array('ebreak_id' => $ebreak_id, 'uid' => $uid));
        $ec_id = intval($ea_data['ec_id']);
        if (empty($ea_data)) {
            E('_EMPTY_BREAK_DATA');

            return false;
        }
        // 交卷处理
        if ($ea_data['is_status'] == 1) {
            E('_ERR_SUBMINT_FAIL');

            return false;
        }
        $count = $this->_break_detail_model->count_by_conds(array("is_status" => 0, 'ebreak_id' => $ebreak_id));
        if ($count) {
            E('_ERR_BREAK_EXAM_UN_FLIASH');
            return false;
        }
        try {
            // 开始事务
            $this->start_trans();
            $count = $this->_break_detail_model->count_by_conds(
                array("ebreak_id" => $ebreak_id, "is_pass" => array(0, 2))
            );
            $success = $this->_break_detail_model->count_by_conds(
                array("ebreak_id" => $ebreak_id, "is_pass" => 1)
            );

            if ($success && !$count) {
                $my_is_pass = 1;
            } else {
                $my_is_pass = 0;
            }

            $rel = $this->update(
                array(
                    "ebreak_id" => $ebreak_id
                ),
                array(
                    'is_status' => 1,
                    'my_end_time' => MILLI_TIME,
                    'my_is_pass' => $my_is_pass
                )
            );

            // 闯关未通过(初始化)
            $is_pass = 1;

            if ($rel) {
                // 闯关通过
                if ($my_is_pass == 1) {
                    $is_pass = 2;
                }

            }

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

        $pass = [
            "uid" => $uid,
            'article_id' => $ec_id,
            'is_pass' => $is_pass
        ];

        $url = convertUrl(QY_DOMAIN . '/Course/Rpc/Exam/Update');
        \Com\Rpc::phprpc($url)->invoke('Index', $pass);

        return array("is_pass" => $my_is_pass, 'ec_id' => $ec_id);
    }
}
