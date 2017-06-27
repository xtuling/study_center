<?php
/**
 * 考试-试卷快照表
 * @author: houyingcai
 * @email:    594609175@qq.com
 * @date :  2017-05-19 17:49:26
 * @version $Id$
 */

namespace Common\Service;

use Common\Model\PaperModel;
use Common\Model\SnapshotModel;
use Common\Model\TopicModel;

class SnapshotService extends AbstractService
{

    // 构造方法
    public function __construct()
    {
        $this->_d = new SnapshotModel();
        $this->_d_topic = new TopicModel();
        $this->_d_paper = new PaperModel();

        parent::__construct();
    }


    /**
     * 格式化后台试题列表
     * @author daijun
     * @param array $list
     * @return array
     */
    public function format_admin_list($list = array())
    {
        $return_list = array();

        if (empty($list)) {
            return $return_list;
        }

        foreach ($list as $k => $v) {
            $return_list[$k]['et_id'] = intval($v['et_id']);
            $return_list[$k]['et_type'] = intval($v['et_type']);
            $return_list[$k]['title'] = $v['title'];
            $return_list[$k]['score'] = intval($v['score']);

            // 处理图片数据
            $img_list = array();
            if (!empty($v['title_pic'])) {
                $img_data = explode(',', $v['title_pic']);
                foreach ($img_data as $_k => $_v) {
                    $img_list[$_k]['img_url'] = imgUrl($_v);
                }
            }
            $return_list[$k]['title_pic_list'] = $img_list;

            // 处理选项数据
            $options_data = array();
            if (!empty($v['options'])) {
                $options_list = unserialize($v['options']);
                // 循环组装选项数据
                foreach ($options_list as $_k => $_v) {
                    $options_data[$_k]['option_name'] = $_v['option_name'];
                    $options_data[$_k]['option_value'] = $_v['option_value'];
                    $options_data[$_k]['option_image_id'] = $_v['option_image_id'];
                    if (!empty($_v['option_image_id'])) {
                        $options_data[$_k]['option_image_url'] = imgUrl($_v['option_image_id']);
                    } else {
                        $options_data[$_k]['option_image_url'] = '';
                    }

                }
            }

            $return_list[$k]['options'] = $options_data;

            $return_list[$k]['answer'] = $v['answer'];
            $return_list[$k]['answer_resolve'] = $v['answer_resolve'];
            $return_list[$k]['order_num'] = intval($v['order_num']);
        }

        return $return_list;
    }

    /**
     * 获取已选题列表
     * @author：daijun
     * @param int $ep_id 试卷ID
     * @return array
     */
    public function get_snapshot_list($ep_id = 0)
    {
        // 查询已选题列表
        $list = $this->_d->list_by_conds(array('ep_id' => $ep_id), null, array('order_num' => 'ASC'), 'et_id,title');

        // 格式化数据
        foreach ($list as $k => $v) {
            $list[$k]['et_id'] = intval($v['et_id']);
        }

        return $list;
    }

    /**
     * 将选择的试题存入试题快照表
     * @author：daijun
     * @param array $param
     * @return bool
     */
    public function add($param = array())
    {
        // 验证试卷ID
        if (empty($param['ep_id'])) {
            E('_EMPTY_EP_ID');
            return false;
        }

        // 验证所选题目列表
        if (empty($param['topic_list'])) {
            E('_EMPTY_CHOICE_ETID');
            return false;
        }

        // 获取试题ID集合
        $et_ids = array_column($param['topic_list'], 'et_id');

        if (empty($et_ids)) {
            E('_EMPTY_CHOICE_ETID');
            return false;
        }

        // 获取试卷详情
        $paper = $this->_d_paper->get($param['ep_id']);

        // 获取题目列表
        $topic_list = $this->_d_topic->list_by_conds(array('et_id' => $et_ids), null, array(),
            'et_id,et_type,title,title_pic,score,options,answer,answer_resolve,answer_coverage,match_type,answer_keyword');

        $topic_list = array_combine_by_key($topic_list, 'et_id');

        // 试卷总分
        $total_score = 0;
        $snapshot = array();
        // 判断试卷出题规则
        if ($paper['ep_type'] == self::TOPIC_CUSTOMER) {
            // 自主选题，分数按照题目分数计算
            foreach ($et_ids as $k => $v) {
                $arr = array();
                $arr = $topic_list[$v];

                $total_score += intval($arr['score']);
                $arr['ep_id'] = $param['ep_id'];
                // 组装试题序号
                $arr['order_num'] = $k + 1;
                $snapshot[] = $arr;
            }
        } else {
            // 规则抽题，分数按照rule字段配置的计算
            $rule_data = unserialize($paper['rule']);

            foreach ($et_ids as $k => $v) {
                $arr = array();
                $arr = $topic_list[$v];
                // 获取试题配置分数
                $arr['score'] = $this->get_score_by_type($arr['et_type'], $rule_data);
                $total_score += intval($arr['score']);
                $arr['ep_id'] = $param['ep_id'];
                // 组装试题序号
                $arr['order_num'] = $k + 1;
                $snapshot[] = $arr;
            }
        }

        if (intval($total_score) == 0) {
            // 试卷总分不能为0
            return false;
        }

        try {
            // 开始事务
            $this->start_trans();

            // 删除之前存入的试题
            $this->_d->delete_by_conds(array('ep_id' => $param['ep_id']));
            // 执行数据插入操作
            $this->_d->insert_all($snapshot);
            // 更新试卷总分
            $this->_d_paper->update($param['ep_id'], array('total_score' => $total_score));

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

}
