<?php
/**
 * 闯关-答卷详情表
 * @author: houyingcai
 * @email:    594609175@qq.com
 * @date :  2017-05-19 17:55:55
 * @version $Id$
 */

namespace Common\Service;

use Common\Model\BreakAttachModel;
use Common\Model\BreakDetailModel;

class BreakDetailService extends AbstractService
{
    // 构造方法
    public function __construct()
    {
        $this->_d = new BreakDetailModel();
        $this->_break_attach_model = new BreakAttachModel();
        parent::__construct();
    }

    /***
     * 格式化试题数据
     * @author: 蔡建华
     * @param array $data 获取试题要格式化的数据
     * @param int $type 是否显示答案
     * @return array
     */
    public function question_list_param($data = array(), $type = 0)
    {
        foreach ($data as $key => $value) {
            $val = array();
            $val['ebd_id'] = intval($value['ebd_id']);
            $val['order_num'] = intval($value['order_num']);
            //前端用
            $val['status'] = $value['is_pass'] > self::DO_PASS_STATE ? self::DO_STATE : self::DO_PASS_STATE;
            $et_detail = unserialize($value['et_detail']);
            $val['et_type'] = intval($et_detail['et_type']);
            $val['title'] = $et_detail['title'];
            $val['title_pic'] = $this->pic_url($et_detail['title_pic']);
            $val['score'] = $value['score'];
            $val['my_score'] = $value['my_score'];
            $et_option = unserialize($value['et_option']);
            $val['options'] = $this->format_option($et_option);
            $my_answer = $value['my_answer'];
            // 格式化 答案返回值
            $val['my_answer'] = $this->format_answer($my_answer, $et_detail['et_type']);
            $arr[] = $val;
        }
        return $arr;
    }

    /***
     * 图片数组
     * @param string $str 题目图片
     * @return array
     */
    public function pic_url($str)
    {
        $arr = array();
        $data = explode(',', $str);
        $data = array_filter($data);
        foreach ($data as $k => $v) {
            if (!empty($v)) {
                $arr[] = array(
                    'atId' => $v,
                    'atAttachment' => imgUrl($v),
                );
            }
        }
        return $arr;
    }

    /**
     * 选项格式化图片
     * @param array $str 图片数组
     * @return array
     */
    public function format_option($str)
    {
        $arr = array();
        foreach ($str as $k => $v) {
            $arr[] = array(
                'option_name' => $v['option_name'],
                'option_value' => $v['option_value'],
                'option_image_url' => empty($v['option_image_id']) ? '' : $this->format_cover($v['option_image_id']),
                'option_image_id' => $v['option_image_id']
            );
        }
        return $arr;
    }

    /***
     * 格式化我的答案
     * @author: 蔡建华
     * @param string $str 答案
     * @param int $type 题型
     * @return array
     */
    public function format_answer(
        $str,
        $type = 0
    ) {
        $arr = array();
        $data = array();
        if ($type == self::TOPIC_TYPE_SINGLE || $type == self::TOPIC_TYPE_MULTIPLE) {
            // 试题类型：单选题
            $data = explode(',', $str);
        } elseif ($type == self::TOPIC_TYPE_QUESTION || $type == self::TOPIC_TYPE_JUDGMENT) {
            // 试题类型：判断题,问答题
            $data = array($str);
        }
        if (!empty($data)) {

            foreach ($data as $k => $v) {
                $arr[]['opt'] = $v;
            }
        }
        return $arr;
    }

    /** 答题函数
     * @author: 蔡建华
     * @param array $params 答题参数
     * @param array $data 题目信息
     * @return bool
     */
    public function answer_save($params = array(), $data = array())
    {
        $ebd_id = intval($params['ebd_id']);
        $my_answer = $params['my_answer'];
        $et_detail = unserialize($data['et_detail']);
        $et_option = unserialize($data['et_option']);
        if ($et_detail['et_type'] == self::TOPIC_TYPE_MULTIPLE) {
            foreach ($my_answer as $key => $value) {
                $answer[] = trim($value['opt']);
            }
            $answer = array_filter($answer);
            sort($answer);
            $answer = implode(',', $answer);
        } elseif ($et_detail['et_type'] == self::TOPIC_TYPE_SINGLE || $et_detail['et_type'] == self::TOPIC_TYPE_JUDGMENT) {
            $answer = array_column($my_answer, 'opt');
            $answer = array_filter($answer);
            if (!count($answer)) {
                E('_EMPTY_ET_ANSWER');
                return false;
            }
            if (count($answer) < 1) {
                E('_ERR_ANSER_FIED');
                return false;
            }
            $answer = implode(',', $answer);
            // 单选题
            if ($et_detail['et_type'] == self::TOPIC_TYPE_SINGLE) {
                $answer = strtoupper(trim($answer[0]));
                $arr = array_column($et_option, 'option_name');
                if (!in_array($answer, $arr)) {
                    E('_ERR_ANSER_FIED');
                    return false;
                }
            } // 判断题
            elseif ($et_detail['et_type'] == self::TOPIC_TYPE_SINGLE) {
                if (!in_array($answer, array("对", '错'))) {
                    E('_ERR_ANSER_FIED');
                    return false;
                }
            }
        }
        // 保存答题结果
        $rel = $this->update_by_conds(array('ebd_id' => $ebd_id), array('my_answer' => $answer, 'is_status' => 1));
        if ($rel) {
            $this->get_answer_score($ebd_id);
        }
        return $rel;
    }

    /**
     * 考试作答情况格式化
     * @author: 蔡建华
     * @param array $data 答卷ID
     * @return array
     */
    public function get_break_detail($data = array())
    {
        $result = array();
        foreach ($data as $key => $val) {
            $value['order_num'] = intval($val['order_num']);
            // 未作答
            $value['status'] = $val['is_status'];
            $result[] = $value;
        }
        return $result;
    }

    /**
     * 试题分数计算
     * @author: 蔡建华
     * @param $ea_id int 答卷ID
     * @return int 返回分数
     */
    protected function get_answer_score($ebd_id = 0)
    {
        $data = $this->_d->get($ebd_id);
        $arr = $this->get_topic_score($data);
        $this->_d->update_by_conds(array('ebd_id' => $ebd_id), $arr);
    }

    /**  题目分数正确
     * @author: 蔡建华
     * @param array $data 题目信息
     * @return array 返回 分数和答案是否正确
     */
    protected function get_topic_score($data = array())
    {
        $my_score = 0;
        // 没有答题
        $et_detail = unserialize($data['et_detail']);
        $et_type = $et_detail['et_type'];
        // 如果是选择题多选题
        if ($et_type == self::TOPIC_TYPE_SINGLE || $et_type == self::TOPIC_TYPE_MULTIPLE) {
            $answer = explode(',', trim($et_detail['answer']));
            // 循环我的回答，判断我的回答里有没有不在答案里的
            $my_answer_arr = explode(',', $data['my_answer']);
            $my_answer_arr = array_filter($my_answer_arr);
            if (empty($my_answer_arr)) {
                $my_score = 0;
                $is_pass = 2;
            } else {
                $my_score = $data['score']; // 得分，默认是有分数的
                $is_pass = 1;
                foreach ($my_answer_arr as $v) {
                    // 如果我的回答不在答案里，就是错的
                    if (!in_array($v, $answer)) {
                        $my_score = 0;
                        $is_pass = 2;
                        break;
                    }
                }
                /*
               * 如果我的回答里没有不在答案里的，再判断下我的回答数量和答案数量是否一致
               * 数量一致就是正确的，否则就是少选了
               */
                if ($my_score > 0 && count($my_answer_arr) != count($answer)) {
                    $my_score = 0;
                    $is_pass = 2;
                }
            }
        } elseif ($et_detail['et_type'] == self::TOPIC_TYPE_JUDGMENT) {
            // 如果是判断题
            $my_answer = trim($data['my_answer']);
            if ($et_detail['answer'] == $my_answer) {
                $my_score = $data['score'];
                $is_pass = 1;
            } else {
                $my_score = 0;
                $is_pass = 2;
            }
        }
        return array("is_pass" => $is_pass, 'my_score' => $my_score);
    }
}
