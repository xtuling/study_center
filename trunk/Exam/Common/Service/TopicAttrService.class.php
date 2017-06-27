<?php
/**
 * 考试-题目属性关联表
 * @author: houyingcai
 * @email:    594609175@qq.com
 * @date :  2017-05-19 17:48:21
 * @version $Id$
 */

namespace Common\Service;

use Common\Model\AttrModel;
use Common\Model\BankModel;
use Common\Model\TagModel;
use Common\Model\TopicAttrModel;
use Common\Model\TopicModel;

class TopicAttrService extends AbstractService
{

    // 构造方法
    public function __construct()
    {
        $this->_d = new TopicAttrModel();
        $this->_d_attr = new AttrModel();
        $this->_d_tag = new TagModel();
        $this->_d_bank = new BankModel();
        $this->_topic_attr = new TopicAttrModel();
        $this->_topic = new TopicModel();
        parent::__construct();
    }

    /**
     * 根据题库获取标签属性
     * @author daijun
     * @param array $eb_ids
     * @return array
     */
    public function get_bank_attr($eb_ids = array())
    {

        $topic_attr_list = $this->_d->list_by_conds(array('eb_id' => $eb_ids), null, array());

        if(empty($topic_attr_list)){
            return array();
        }
        // 获取标签ID集合
        $tag_ids = array_unique(array_column($topic_attr_list, 'etag_id'));

        // 查询标签信息
        $tag_list = $this->_d_tag->list_by_conds(array('etag_id' => $tag_ids), null, array('etag_id' => 'ASC'));
        // 获取属性ID集合
        $attr_ids = array_unique(array_column($topic_attr_list, 'attr_id'));
        // 查询属性信息
        $attr_list = $this->_d_attr->list_by_conds(array('attr_id' => $attr_ids), null, array('etag_id' => 'ASC'));

        $return_list = array();
        // 循环标签数据
        foreach ($tag_list as $k => $v) {

            $return_list[$k]['etag_id'] = intval($v['etag_id']);
            $return_list[$k]['etag_name'] = $v['tag_name'];

            $attr_data = array();
            // 循环属性数据
            foreach ($attr_list as $_k => $_v) {
                // 如果属性是该标签下的
                if ($_v['etag_id'] == $v['etag_id']) {
                    $attr = array();
                    $attr['attr_id'] = intval($_v['attr_id']);
                    $attr['attr_name'] = $_v['attr_name'];
                    $attr_data[] = $attr;
                    // 删除调该条记录
                    unset($attr_list[$_k]);
                }
            }

            $return_list[$k]['attr_data'] = $attr_data;
        }

        // 返回组装好的数据
        return $return_list;
    }

    /**
     * 【后台】根据题库和标签计算题库对应题目数量数据验证
     * @author 何岳龙
     * @param array $params
     * @return bool
     */
    public function bank_count_validation($params = array())
    {

        // 验证题库列表
        if (empty($params['bank_list']) || !is_array($params['bank_list'])) {

            E('_ERR_BANK_LIST');

            return false;
        }

        // 获取题库IDS个数
        $eb_ids = count(array_unique(array_filter(array_column($params['bank_list'], 'eb_id'))));

        // 如果数据格式错误
        if (empty($eb_ids)) {

            E('_EMPTY_BANK_IDS_LIST');

            return false;
        }

        $attr_ids = array();
        // 如果标签筛选方式存在
        if (!empty($params['tag_data']) && is_array($params['tag_data'])) {

            foreach ($params['tag_data'] as $v) {
                $attr_ids_arr = array();
                $attr_ids_arr = array_column($v['attr_data'], 'attr_id');
                $attr_ids = array_merge($attr_ids, $attr_ids_arr);
            }

            if (!empty($attr_ids)) {
                // 验证类型
                if (!is_numeric($params['search_type']) || !in_array($params['search_type'],
                        array(self::SEARCH_ATTR_TYPE_ALL, self::SEARCH_ATTR_TYPE_NOT_ALL))
                ) {

                    E('_ERR_SEARCH_TYPE');

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * 【后台】根据题库和标签计算题库对应题目数量
     * @author 何岳龙
     * @param array $eb_ids 题库集合
     * @param array $attr_ids 属性集合
     * @param Int $search_type 筛选方式
     * @return array
     */
    public function get_bank_data($eb_ids = array(), $attr_ids = array(), $search_type = 0)
    {

        // 初始化数据
        $list = array();

        // 如果属性列表为空
        if (empty($attr_ids)) {

            // 获取全部题库的列表
            $list = $this->get_bank_all_count_list($eb_ids);
        }

        // 如果有属性列表
        if (!empty($attr_ids)) {

            // 全部满足
            if ($search_type == self::SEARCH_ATTR_TYPE_ALL) {

                $list = $this->search_type_all($eb_ids, $attr_ids);
            }

            // 满足一个
            if ($search_type == self::SEARCH_ATTR_TYPE_NOT_ALL) {

                $list = $this->search_type_not_all($eb_ids, $attr_ids);
            }

        }

        return $list;
    }

    /**
     * 【后台】获取全部题库下的所有题目统计数
     * @author 岳龙
     * @param array $eb_ids 题库集合
     * @return array
     */
    public function get_bank_all_count_list($eb_ids = array())
    {
        // 初始化
        $list = array();

        // 获取题库列表
        $data = $this->_d_bank->list_by_conds(array('eb_id' => $eb_ids));

        // 遍历数组
        foreach ($data as $key => $v) {

            $list[] = array(
                'eb_id' => intval($v['eb_id']),
                'eb_name' => strval($v['eb_name']),
                'single_count' => intval($v['single_count']),
                'multiple_count' => intval($v['multiple_count']),
                'judgment_count' => intval($v['judgment_count']),
                'question_count' => intval($v['question_count']),
                'voice_count' => intval($v['voice_count']),
            );

        }

        return $list;
    }

    /**
     * 【后台】满足全部
     * @author 岳龙
     * @param array $eb_ids 题库ID集合
     * @param array $attr_ids 属性ID集合
     * @return array
     */
    public function search_type_all($eb_ids = array(), $attr_ids = array())
    {
        // 初始化
        $list = array();

        // 初始化题库下的题目IDS数组
        $eb_list = array();

        // 获取全部满足条件的
        $topic_attr_all_list = $this->_topic_attr->list_by_conds(array('eb_id' => $eb_ids, 'attr_id' => $attr_ids));

        // 遍历关联关系表
        foreach ($topic_attr_all_list as $item) {
            $eb_list[$item['et_id']][] = $item['attr_id'];
        }

        $topic_ids = array();
        // 组装符合条件的数据
        foreach ($eb_list as $key => $v) {
            if (array_intersect($attr_ids, $v) == $attr_ids) {
                $topic_ids[] = $key;
            }
        }

        // 组装数据
        $eb_list_arr = array();

        // 如果没有符合条件的数据
        if (!empty($topic_ids)) {

            // 查询符合条件的试题列表
            $topic_list = $this->_topic->list_by_conds(array('et_id' => $topic_ids));

            foreach ($topic_list as $key => $v) {

                $eb_list_arr[$v['eb_id']][] = $v['et_id'];
            }

        }

        // 获取题库列表
        $bank_list = $this->_d_bank->list_by_conds(array('eb_id' => $eb_ids));

        // 遍历题库列表
        foreach ($bank_list as $vo) {

            list(
                $single_count,
                $multiple_count,
                $judgment_count,
                $question_count,
                $voice_count) = $this->count_bank_num_list($eb_list_arr[$vo['eb_id']]);

            // 组装数据
            $list[] = array(
                'eb_id' => intval($vo['eb_id']),
                'eb_name' => strval($vo['eb_name']),
                'single_count' => $single_count,
                'multiple_count' => $multiple_count,
                'judgment_count' => $judgment_count,
                'question_count' => $question_count,
                'voice_count' => $voice_count,
            );
        }

        return $list;
    }

    /**
     * 【后台】满足一个
     * @author 岳龙
     * @param array $eb_ids 题库ID集合
     * @param array $attr_ids 属性ID集合
     * @return array
     */
    public function search_type_not_all($eb_ids = array(), $attr_ids = array())
    {
        // 初始化
        $list = array();

        // 查询关系表中列表
        $topic_attr_list = $this->_topic_attr->list_by_conds(array('eb_id' => $eb_ids, 'attr_id' => $attr_ids));


        // 初始化题库下的题目IDS数组
        $eb_list = array();

        // 组装数据
        foreach ($topic_attr_list as $key => $v) {

            $eb_list[$v['eb_id']][] = $v['et_id'];
        }

        // 获取题库列表
        $bank_list = $this->_d_bank->list_by_conds(array('eb_id' => $eb_ids));

        // 遍历题库列表
        foreach ($bank_list as $vo) {

            list(
                $single_count,
                $multiple_count,
                $judgment_count,
                $question_count,
                $voice_count) = $this->count_bank_num_list($eb_list[$vo['eb_id']]);

            // 组装数据
            $list[] = array(
                'eb_id' => intval($vo['eb_id']),
                'eb_name' => strval($vo['eb_name']),
                'single_count' => $single_count,
                'multiple_count' => $multiple_count,
                'judgment_count' => $judgment_count,
                'question_count' => $question_count,
                'voice_count' => $voice_count,
            );
        }

        return $list;
    }

    /**
     * 【后台】获取题库ID对应题库五种题目数
     * @param array $ids 题目IDS
     * @return array
     */
    public function count_bank_num_list($ids = array())
    {
        // 初始化
        $list = array();

        // 去重复
        $ids = array_unique($ids);

        // 单选题数量
        $single_count = 0;

        // 多选题数量
        $multiple_count = 0;

        // 判断题数量
        $judgment_count = 0;

        // 问答题数量
        $question_count = 0;

        // 语音题数量
        $voice_count = 0;

        // 如果题库IDS不为空
        if (!empty($ids)) {

            // 获取列表
            $list = $this->_topic->list_by_conds(array('et_id' => $ids));

        }

        // 遍历数据
        foreach ($list as $key => $v) {

            // 试题类型：单选题
            if ($v['et_type'] == self::TOPIC_TYPE_SINGLE) {
                $single_count++;
                continue;
            }

            // 试题类型：判断题
            if ($v['et_type'] == self::TOPIC_TYPE_JUDGMENT) {
                $judgment_count++;
                continue;
            }

            // 试题类型：问答题
            if ($v['et_type'] == self::TOPIC_TYPE_QUESTION) {
                $question_count++;
                continue;
            }

            // 试题类型：多选题
            if ($v['et_type'] == self::TOPIC_TYPE_MULTIPLE) {
                $multiple_count++;
            }

            // 试题类型：语音题
            if ($v['et_type'] == self::TOPIC_TYPE_VOICE) {
                $voice_count++;
                continue;
            }
        }

        return array($single_count, $multiple_count, $judgment_count, $question_count, $voice_count);
    }
}
