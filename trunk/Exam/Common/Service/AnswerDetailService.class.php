<?php
/**
 * 考试-答卷详情表
 * @author: houyingcai
 * @email:    594609175@qq.com
 * @date :  2017-05-19 17:55:55
 * @version $Id$
 */

namespace Common\Service;

use VcySDK\Service;
use Common\Model\AnswerAttachModel;
use Common\Model\AnswerDetailModel;
use Common\Common\Lcs;

class AnswerDetailService extends AbstractService
{
    /**
     * @var AnswerAttachModel
     */
    var $_answer_attach_model;

    // 构造方法
    public function __construct()
    {
        $this->_d = new AnswerDetailModel();
        $this->_answer_attach_model = new AnswerAttachModel();

        parent::__construct();
    }

    /***
     * 格式化试题数据
     * @author: 蔡建华
     * @param array $data 获取试题要格式化的数据
     * @param int $type 是否显示答案
     * @return array
     */
    public function question_list_param($data = array(), $type = 2)
    {
        $arr = array();
        $ead_id = array_column($data, 'ead_id');
        // 答题附件新start
        $attachlist = $this->_answer_attach_model->list_by_conds(array('ead_id' => $ead_id), null,
            array('ead_id' => 'asc'));
        // 获取附件的媒体ID
        $all_at_ids = array_column($attachlist, 'at_id');
        $_attach_urls = array();
        if ($all_at_ids) {
            sort($all_at_ids);
            $serv = &Service::instance();
            $_attach_obj = new \VcySDK\Attach($serv);
            $_attachs = $_attach_obj->listAll(['atIds' => $all_at_ids], 1, 1000);
            foreach ($_attachs['list'] as $_attach) {
                //获取本地的URL
                $_attach_urls[$_attach['atId']] = $_attach['atAttachment'];
            }
        }
        // 转成功的
        $attach = array();
        // 未转成功
        $_temp = array();
        foreach ($attachlist as $key => $value) {
            // 未成功
            $value['url'] = $_attach_urls[$value['at_id']];
            if ($value['is_complete'] != 1) {
                $_temp[$value['atta_id']] = $value;
            } else {
                $attach[$value['atta_id']] = $value;
            }
        }
        $attach_new = array_merge($attach, $_temp);
        $attach = array();
        foreach ($attach_new as $key => $value) {
            $val = array();
            //顺序号
            $val['order_id'] = $value['order_id'];
            //微信媒体ID
            $val['media_id'] = $value['media_id'];
            //图片  is_complete 1 视频转换完成1 没有完成0
            $val['is_complete'] = intval($value['is_complete']);
            $file_info = unserialize($value['file_info']);
            //音频长度
            $val['length'] = self::TYPE_VOICE ? $file_info['length'] : 0;
            //类型
            $val['type'] = $value['type'];
            if ($value['type'] == self::TYPE_VOICE) {
                //语音
                $val['url'] = $value['url'];
            } else {
                //图片
                $val['url'] = imgUrl($val['at_id']);
            }
            $attach[$value['ead_id']][$val['order_id']] = $val;
        }
        // 答题附件新end
        foreach ($data as $key => $value) {
            $val = array();
            $val['ead_id'] = intval($value['ead_id']);
            $val['order_num'] = intval($value['order_num']);

            //作答状态
            if ($type == 1) {
                // 后台用
                $val['is_pass'] = intval($value['is_pass']);
            } elseif ($type == 2) {
                // 前端用
                $val['status'] = $value['is_pass'];
            } else {
                $val['status'] = $value['is_pass'] > self::DO_PASS_STATE ? self::DO_STATE : self::DO_PASS_STATE;
            }

            $et_detail = unserialize($value['et_detail']);
            $val['et_type'] = intval($et_detail['et_type']);
            $val['title'] = $et_detail['title'];
            $val['title_pic'] = $this->pic_url($et_detail['title_pic']);
            $val['score'] = $value['score'];
            $val['my_score'] = $value['my_score'];
            $et_option = unserialize($value['et_option']);
            $val['options'] = $this->format_option($et_option);
            $my_answer = $value['my_answer'];
            // 试题类型：语音题
            if ($et_detail['et_type'] == self::TOPIC_TYPE_VOICE) {
                $attr_key = explode(',', $my_answer);
                $attr_key = array_filter($attr_key);
                // 判断语音答题信息
                if (!empty($my_answer)) {
                    $attr = $attach[$value['ead_id']];
                    $arr_attr = array();
                    $order_arr = array();
                    foreach ($attr_key as $k => $v) {
                        if ($v && $attr[$v]) {
                            $order_arr[] = $attr[$v];
                        }
                    }
                    foreach ($attr as $k => $v) {
                        $arr_attr[] = $v;
                    }
                    if ($order_arr) {
                        $val['my_answer'] = $order_arr;
                    } else {
                        $val['my_answer'] = '';
                    }

                } else {
                    $val['my_answer'] = '';
                }
            } else {
                $val['my_answer'] = $this->format_answer($my_answer, $et_detail['et_type']);
            }
            if ($type == 2||$type == 1) {
                // 正确答案
                $val['answer'] = $et_detail['answer'];
                // 是否匹配关键字(0:否 ；1:是)
                $val['match_type'] = intval($et_detail['match_type']);
                //	答案关键字
                $val['answer_keyword'] = $et_detail['answer_keyword'];
                // 答案解析
                $val['answer_resolve'] = $et_detail['answer_resolve'];
            }
            $arr[] = $val;
        }
        return $arr;
    }

    /***
     * 图片数组
     * @author: 蔡建华
     * @param string $str 题目图片
     * @return array
     */
    public function pic_url($str)
    {
        $arr = array();
        $data = explode(',', $str);
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
     * @author: 蔡建华
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
    public function format_answer($str, $type = 0)
    {
        $arr = array();
        $data = array();
        if ($type == self::TOPIC_TYPE_SINGLE || $type == self::TOPIC_TYPE_MULTIPLE) {
            // 试题类型：单选题
            $data = explode(',', $str);
        } elseif ($type == self::TOPIC_TYPE_QUESTION || $type == self::TOPIC_TYPE_JUDGMENT) {

            // 试题类型：判断题,问答题
            $data = array($str);
        }
        // 过滤信息
        array_filter($data);
        if (!empty($data)) {

            foreach ($data as $k => $v) {
                $arr[]['opt'] = $v;
            }
        }
        return $arr;
    }

    /**
     * 答题函数
     * @author: 蔡建华
     * @param array $params 答题参数
     * @param array $data 题目信息
     * @return bool
     */
    public function answer_save($params = array(), $data = array())
    {
        $ead_id = intval($params['ead_id']);
        $my_answer = $params['my_answer'];
        $et_detail = unserialize($data['et_detail']);
        $et_option = unserialize($data['et_option']);
        // 多选题

        if ($et_detail['et_type'] == self::TOPIC_TYPE_MULTIPLE) {
            $answer = array_column($my_answer, 'opt');
            $answer = array_filter($answer);
            if (count($answer) < 1) {
                E('_ERR_ANSER_FIED');
                return false;
            }
            $arr = array_column($et_option, 'option_name');
            foreach ($arr as $k => $v) {
                $arr[$k] = strtoupper(trim($v));
            }
            foreach ($answer as $key => &$value) {
                $value = strtoupper(trim($value));
                if (!in_array($value, $arr)) {
                    E('_ERR_ANSER_FIED');
                    return false;
                }
            }
            sort($answer);
            $answer = implode(',', $answer);
        } elseif ($et_detail['et_type'] == self::TOPIC_TYPE_QUESTION || $et_detail['et_type'] == self::TOPIC_TYPE_SINGLE || $et_detail['et_type'] == self::TOPIC_TYPE_JUDGMENT) {
            $answer = array_column($my_answer, 'opt');
            $answer = array_filter($answer);
            if (!count($answer)) {
                E('_EMPTY_ET_ANSWER');
                return false;
            }
            if (count($answer) > 1) {
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

        } else {
            // 语音答案
            $answer = array_column($my_answer, 'opt');
            if (empty($answer)) {
                E('_EMPTY_ANSWER');
                return false;
            }
            // 过滤空的答案
            $answer = array_filter($answer);
            if (!empty($answer)) {
                $answerattr = $this->_answer_attach_model->list_by_conds(array(
                    "ead_id" => $ead_id,
                    'order_id' => $answer
                ));
                $answer = array_column($answerattr, 'order_id');
            }
            $answer = implode(',', $answer);
        }
        // 更新答案信息
        $rel = $this->update_by_conds(array('ead_id' => $ead_id), array('my_answer' => $answer));
        if ($rel) {
            $this->get_answer_score($ead_id);
        }

        return $rel;
    }

    /**
     * 试题分数计算
     * @author: 蔡建华
     * @param $ea_id int 答卷ID
     * @return int 返回分数
     */
    protected
    function get_answer_score(
        $ead_id = 0
    ) {
        $data = $this->_d->get($ead_id);
        $arr = $this->get_topic_score($data);
        $this->_d->update_by_conds(array('ead_id' => $ead_id), $arr);
    }

    /**
     * 题目分数正确
     * @author: 蔡建华
     * @param array $data 题目信息
     * @return array 返回 分数和答案是否正确
     */
    protected
    function get_topic_score(
        $data = array()
    ) {
        if (empty($data['my_answer'])) {
            $my_score = 0;
            $is_pass = 0;
        } else {
            $et_detail = unserialize($data['et_detail']);
            $et_type = $et_detail['et_type'];
            // 如果是选择题或者多选题
            if ($et_type == self::TOPIC_TYPE_SINGLE || $et_type == self::TOPIC_TYPE_MULTIPLE) {
                $answer = explode(',', $et_detail['answer']);
                // 循环我的回答，判断我的回答里有没有不在答案里的
                $my_answer_arr = explode(',', $data['my_answer']);
                $my_answer_arr = array_filter($my_answer_arr);
                $my_score = $data['score']; // 得分，默认是有分数的
                $is_pass = 1; // 已作答
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

            } elseif ($et_type == self::TOPIC_TYPE_JUDGMENT) {

                // 如果是判断题
                $my_answer = trim($data['my_answer']);

                if ($et_detail['answer'] == $my_answer) {

                    $my_score = $data['score'];
                    $is_pass = 1;

                } else {

                    $my_score = 0;
                    if (empty($my_answer)) {

                        $is_pass = 0;

                    } else {

                        $is_pass = 2;
                    }
                }
            } elseif ($et_type == self::TOPIC_TYPE_VOICE) {

                // 如果是语音题
                // 存在答案就认为得满分，不存在则为0
                $my_score = $data['score'];
                $is_pass = 1;

            } else {

                $my_answer = trim($data['my_answer']);


                // 如果是问答题
                if ($et_detail['match_type'] == self::KEYWORD_OPEN) {

                    // 匹配关键字方式
                    $my_score = $this->_key_question_score($et_detail['answer_keyword'], $my_answer,
                        $data['score']);
                    if ($my_score == 0) {
                        $is_pass = 2;
                    } else {
                        $is_pass = 1;
                    }
                } else {
                    // 覆盖率方式
                    $is_pass = $this->_coverage_question_score($et_detail['answer'], $et_detail['answer_coverage'],
                        $my_answer);

                    $my_score = $is_pass === true ? $data['score'] : 0;
                    if ($my_score == 0) {
                        $is_pass = 2;
                    } else {
                        $is_pass = 1;
                    }
                }
            }
        }
        return array('is_pass' => $is_pass, 'my_score' => $my_score);
    }

    /**
     * @author: 蔡建华
     * 计算问答题关键字匹配方式的分数
     * @param array $keywords 关键字数组
     * @param string $my_answer 我的回答
     * @param int $score 总分数
     * @return int 得分
     */
    protected
    function _key_question_score(
        $keywords,
        $my_answer,
        $score
    ) {
        $my_score = 0; // 最后得分
        $match_all = true; // 是否全部匹配

        // 循环关键字
        foreach ($keywords as $v) {
            $keyWord = trim($v['keyword']);
            // 正则匹配关键字
            if (preg_match("/{$keyWord}/", $my_answer)) {

                $percent = (float)((int)$v['percent'] / 100.00);
                $my_score += $score * $percent;

            } else {
                // 有一个不匹配就记录全部匹配为false
                $match_all = false;
            }
        }
        // 如果全部匹配，就返回满分，否则返回对应的分数
        return $match_all === true ? $score : $my_score;
    }

    /**
     * @author: 蔡建华
     * 计算问答题覆盖率方式的分数
     * @param string $sys_answer 答案
     * @param string $answer_coverage 合格覆盖率
     * @param string $my_answer 我的回答
     * @return boolean 我的回答的覆盖率满足合格覆盖率返回true，否则返回false
     */
    protected
function _coverage_question_score(
        $sys_answer,
        $answer_coverage,
        $my_answer
    ) {
        $answer_coverage = (float)(intval($answer_coverage) / 100.0);
        // 当类型为问答题，进行文本比对
        $user_answer = str_replace(array("\r\n", "\r", "\n"), " ", $my_answer);
        $user_answer = preg_replace("/\s(?=\s)/", "\\1", $user_answer);

        $sys_answer = str_replace(array("\r\n", "\r", "\n"), " ", $sys_answer);
        $sys_answer = preg_replace("/\s(?=\s)/", "\\1", $sys_answer);

        // 实例化对比类
        $lcs = new Lcs();
        // 我的答案的覆盖率
        $coverage = $lcs->getSimilar($user_answer, $sys_answer);
        return $coverage >= $answer_coverage;
    }
}
