<?php
/**
 * 获取试卷规则
 * RuleDetailController.class.php
 * User: daijun
 * Date: 2017-05-23
 */

namespace Apicp\Controller\Paper;

use Common\Service\PaperService;
use Common\Service\TopicAttrService;

class RuleDetailController extends AbstractController
{
    /**
     * @var  PaperService 试卷信息表
     */
    protected $paper_serv;

    /**
     * @var TopicAttrService 试题属性关联关系表
     */
    protected $topic_attr_serv;

    public function before_action($action = '')
    {

        if (!parent::before_action($action)) {
            return false;
        }
        // 实例化试卷信息表
        $this->paper_serv = new PaperService();

        // 实例化试题属性关联关系表
        $this->topic_attr_serv = new TopicAttrService();

        return true;
    }

    public function Index_post()
    {
        /**
         * 1.根据试卷ID获取试卷详情
         * 2.格式化返回数据
         */
        $ep_id = I('post.ep_id', 0, 'intval');

        // 验证参数
        if (empty($ep_id)) {
            E('_EMPTY_PAPER_ID');
            return false;
        }

        // 获取试卷信息
        $data = $this->paper_serv->get($ep_id);
        if (!$data) {
            E('_EMPTY_PAPER_DATA');
            return false;
        }

        // 组装请求参数
        $params = array();
        $bank_ids = explode(',', $data['bank_data']);

        $attr_ids = array();
        if (!empty($data['tag_data'])) {
            $tag_data = unserialize($data['tag_data']);
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

        // 获取根据题库和标签计算题库对应题目数量
        $bank_data = $this->topic_attr_serv->get_bank_data($bank_ids, $attr_ids, $data['search_type']);

        // 格式化返回数据
        $result = $this->paper_serv->format_rule_data($data, $bank_data);

        $this->_result = $result;

        return true;

    }

}
