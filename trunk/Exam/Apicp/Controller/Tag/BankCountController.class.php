<?php
/**
 * 根据题库和标签计算题库对应题目数量（添加编辑试卷时使用）
 * BankCountController.class.php
 * User: 何岳龙
 * Date: 2017年5月31日10:49:22
 */

namespace Apicp\Controller\Tag;

use Common\Service\AttrService;
use Common\Service\TopicAttrService;
use Common\Service\TopicService;

class BankCountController extends AbstractController
{
    /**
     * 题目关联属性表
     * @var TopicAttrService
     */
    protected $topic_attr_serv;

    public function before_action($action = '')
    {

        if (!parent::before_action($action)) {
            return false;
        }

        $this->topic_attr_serv = new TopicAttrService();

        return true;
    }

    public function Index_post()
    {

        $params = I('post.');

        // 添加标签验证
        if (!$this->topic_attr_serv->bank_count_validation($params)) {
            return false;
        }

        // 获取题库IDS集合
        $eb_ids = array_unique(array_filter(array_column($params['bank_list'], 'eb_id')));

        $attr_ids = array();
        if (!empty($params['tag_data'])) {

            // 遍历数据
            foreach ($params['tag_data'] as $v) {

                // 初始化数据
                $attr_ids_arr = array();

                // 验证是否为数组
                if (is_array($v['attr_data'])) {

                    // 获取数据列表
                    $attr_ids_arr = array_column($v['attr_data'], 'attr_id');

                }

                // 验证是否为数组
                if (!empty($attr_ids_arr) && is_array($attr_ids_arr)) {

                    // 获取属性IDS
                    $attr_ids = array_merge($attr_ids, $attr_ids_arr);
                }

            }
        }

        $search_type = $params['search_type'];

        // 获取根据题库和标签计算题库对应题目数量
        $bank_data = $this->topic_attr_serv->get_bank_data($eb_ids, $attr_ids, $search_type);

        // 组装返回参数
        $this->_result = array('bank_data' => $bank_data);

        return true;
    }

}
