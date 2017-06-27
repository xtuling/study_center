<?php
/**
 * 考试-题目表
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-05-19 17:45:33
 * @version $Id$
 */

namespace Common\Service;

use Common\Model\AttrModel;
use Common\Model\BankModel;
use Common\Model\TopicAttrModel;
use Common\Model\TopicModel;
use Com\Validate;
use Think\Exception;

class TopicService extends AbstractService
{
    // 标题长度
    const TIT_MUN = 500;

    // 表头最小长度
    const TIT_LEN = 8;

    // 判断题字段长度
    const JU_LEN = 5;

    // 多选题限制个数
    const CHECKBOX_LEN = 2;

    // 模版选项文字长度
    const FONT_LEN = 120;

    // 模版数据记录数
    const TPL_LEN = 5;

    // 标题长度
    const TIT_LEN_LEN = 15;

    // 最大长度
    const HEAD_LEN = 16;

    // 初始值
    const LEN = 1;

    // 模板类型
    protected $_xls_type = array(
        'radio' => '单选题',
        'checkbox' => '多选题',
        'jundgment' => '判断题',
    );

    // 判断题答案
    protected $_choice_list = array(
        '对',
        '错',
    );

    // 选项列表
    protected $_options_list = array(
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
    );

    // 错误列表
    protected $_errorList = array(
        'et_type' => '题目类型不符合规范',
        'attr_list' => '属性不合法',
        'order_num' => '题目排序不符合规范',
        'score' => '题目分数不符合规范',
        'title' => '题目名称不符合规范',
        'answer' => '正确答案不符合规范',
        'answer_resolve' => '答案解析不符合规范',
        'A' => '选项A不符合规范',
        'B' => '选项B不符合规范',
        'C' => '选项C不符合规范',
        'D' => '选项D不符合规范',
        'E' => '选项E不符合规范',
        'F' => '选项F不符合规范',
        'G' => '选项G不符合规范',
        'H' => '选项H不符合规范',
        'I' => '选项I不符合规范',
        'J' => '选项J不符合规范',
    );

    // 列表名称
    protected $__fieldName = array(
        array(
            'key' => "et_type",
            'name' => "题目类型",
        ),
        array(
            'key' => "attr_list",
            'name' => "题目标签属性",
        ),
        array(
            'key' => "title",
            'name' => "题目名称",
        ),
        array(
            'key' => "score",
            'name' => "题目分数",
        ),
        array(
            'key' => "answer",
            'name' => "正确答案",
        ),
        array(
            'key' => "answer_resolve",
            'name' => "答案解析",
        ),
        array(
            'key' => "A",
            'name' => "选项A",
        ),
        array(
            'key' => "B",
            'name' => "选项B",
        ),
        array(
            'key' => "C",
            'name' => "选项C",
        ),
        array(
            'key' => "D",
            'name' => "选项D",
        ),
        array(
            'key' => "E",
            'name' => "选项E",
        ),
        array(
            'key' => "F",
            'name' => "选项F",
        ),
        array(
            'key' => "G",
            'name' => "选项G",
        ),
        array(
            'key' => "H",
            'name' => "选项H",
        ),
        array(
            'key' => "I",
            'name' => "选项I",
        ),
        array(
            'key' => "J",
            'name' => "选项J",
        ),
        array(
            'key' => "result",
            'name' => "导入结果",
        ),
    );

    // 默认页数
    const DEFAULT_PAGE = 1;

    /**
     * @var BankModel 初始化题目性表
     */
    protected $_d_bank = null;

    /**
     * @var AttrModel 初始化属性表
     */
    protected $_d_attr = null;

    /**
     * @var TagService 初始化标签表
     */
    protected $_d_tag = null;

    /**
     * @var TopicAttrModel 初始化题目属性表
     */
    protected $_d_topic_attr = null;

    // 构造方法
    public function __construct()
    {
        $this->_d = new TopicModel();
        $this->_d_bank = new BankModel();
        $this->_d_attr = new AttrModel();
        $this->_d_topic_attr = new TopicAttrModel();
        $this->_d_tag = new TagService();

        parent::__construct();
    }

    /**
     * 添加题库题目(后台)
     * @author 英才
     * @param array $result 返回题目信息
     * @param array $reqData 请求数据
     *
     * @return bool
     */
    public function add_topic(&$result, $reqData)
    {
        // 获取活动数据
        $topic = $this->fetch_topic($reqData);

        // 验证数据
        $this->validate_for_add($topic);

        try {
            $this->_d->start_trans();

            // 题目信息入库
            $et_id = $this->_d->insert($topic);

            $condition = array();
            $condition["eb_id"] = $topic['eb_id'];

            if ($et_id) {

                // 题目标签属性入库
                if (!empty($topic['tag_list'])) {

                    $this->insert_topic_attr_data($topic['tag_list'], $topic['eb_id'], $et_id);
                }
                // 更新题库表中的题的个数
                $this->update_eb_topic_add($topic['et_type'], $condition);
            }

            $this->_d->commit();
        } catch (Exception $e) {

            $this->_d->rollback();
            E('_ERR_ADD_TOPIC_FAILED');

            return false;
        }

        //返回结果集
        $result['et_id'] = (int)$et_id;

        return true;
    }

    /**
     * 编辑题库题目(后台)
     * @author 英才
     * @param array $result 返回题目信息
     * @param array $reqData 请求数据
     *
     * @return bool
     */
    public function update_topic(&$result, $reqData)
    {
        $et_id = intval($reqData['et_id']);
        // 题目ID不能为空
        if (empty($et_id)) {

            E('_EMPTY_ET_ID');

            return false;
        }
        // 题目不存在
        if (!$old_topic = $this->_d->get($et_id)) {

            E('_ERR_TOPIC_NOT_FOUND');

            return false;
        }
        // 获取提交的题目数据
        $topic = $this->fetch_topic($reqData);
        // 验证数据
        $this->validate_for_add($topic);

        // 验证题目类型是否被修改
        if ($old_topic['et_type'] != $topic['et_type']) {

            E('_ERR_TOPIC_TYPE_UPDATE');

            return false;
        }

        // 标签列表
        $tag_list = $topic['tag_list'];

        unset($topic['tag_list']);

        try {
            $this->_d->start_trans();

            // 更新题目信息
            $this->_d->update_by_conds(array('et_id' => $et_id), $topic);

            // 删除原有的关联标签属性
            $this->_d_topic_attr->delete_by_conds(array('et_id' => $et_id));

            // 题目标签属性入库
            if (!empty($tag_list)) {

                // 重新插入关联标签属性
                $this->insert_topic_attr_data($tag_list, $topic['eb_id'], $et_id);
            }

            // 题目类型改变后，更新题库表中的题的个数(！！现在不允许修改题目类型了)
            if ($old_topic['et_type'] != $topic['et_type']) {

                $condition = array();
                $condition["eb_id"] = $topic['eb_id'];

                // 旧的题目类型数量-1
                $this->update_eb_topic_cutting($old_topic['et_type'], $condition);
                // 新的题目类型数量+1
                $this->update_eb_topic_add($topic['et_type'], $condition, 'edit');
            }

            $this->_d->commit();
        } catch (Exception $e) {

            $this->_d->rollback();
            E('_ERR_UPDATE_TOPIC_FAILED');

            return false;
        }

        //返回结果集
        $result['et_id'] = (int)$et_id;

        return true;
    }

    /**
     * 删除题库题目(后台)
     * @author 英才
     * @param array $reqData 请求数据
     *
     * @return bool
     */
    public function delete_topic($reqData)
    {
        $et_ids = array_column($reqData['et_ids'], 'et_id');

        if (empty($et_ids) || !is_array($et_ids)) {

            E('_EMPTY_ET_ID');

            return false;
        }

        try {
            $this->_d->start_trans();

            // 获取需要删除的所有题目
            $topic_del_list = $this->_d->list_by_pks($et_ids);
            // 删除题目
            $this->_d->delete($et_ids);
            // 删除题目关联的属性
            $this->_d_topic_attr->delete_by_conds(array('et_id' => $et_ids));
            // 更新题库下的题目数量
            foreach ($topic_del_list as $key => $value) {

                $condition["eb_id"] = $value['eb_id'];
                // 更新题库中题目所属类型的题目数量
                $this->update_eb_topic_cutting($value['et_type'], $condition);
                // 更新题库中题目的总数量
                $this->_d_bank->setDecNum("total_count", $condition, 1);
            }

            $this->_d->commit();
        } catch (Exception $e) {

            $this->_d->rollback();
            E('_ERR_DELETE_TOPIC_FAILED');

            return false;
        }

        return true;
    }

    /**
     * 获取题库题目详情(后台)
     * @author 英才
     * @param array $result 返回题目信息
     * @param array $reqData 请求数据
     *
     * @return bool
     */
    public function get_bank_topic(&$result, $reqData)
    {
        // 获取参数
        $et_id = rintval($reqData['et_id']);
        // 题目ID不能为空
        if (empty($et_id)) {

            E('_EMPTY_ET_ID');

            return false;
        }
        // 获取题目详情
        $topic = $this->_d->get($et_id);
        if (empty($topic)) {

            E('_ERR_TOPIC_NOT_FOUND');

            return false;
        }

        // 题目图片信息
        $pics_array = empty($topic['title_pic']) ? array() : explode(',', $topic['title_pic']);
        $pics_array_news = array();
        foreach ($pics_array as $v) {

            $pics_array_news[] = array('atId' => $v, 'atAttachment' => ImgUrl($v));
        }
        $topic['title_pic'] = $pics_array_news;

        // 题目选项反序列化
        $topic['options'] = empty($topic['options']) ? array() : unserialize($topic['options']);

        // 关键字反序列话
        $topic['answer_keyword'] = empty($topic['answer_keyword']) ? array() : unserialize($topic['answer_keyword']);

        // 获取题目属性列表
        $topic_attr_list = $this->_d_topic_attr->list_by_conds(array('et_id' => $et_id));

        // 题目属性ID列表
        $attr_ids = array_column($topic_attr_list, 'attr_id');

        // 获取所有标签属性列表
        $topic['tag_list'] = $this->_d_tag->get_all_tag_attr();

        // 重组标签属性列表
        foreach ($topic['tag_list'] as &$tag) {

            // 根据属性列表判断题目关联的标签属性选中状态
            foreach ($tag['attr_list'] as &$val) {
                if (in_array($val['attr_id'], $attr_ids)) {
                    $val['checked'] = 1;
                } else {
                    $val['checked'] = 0;
                }
            }
        }
        $topic['score'] = (int)$topic['score'];
        $result = $topic;

        return true;
    }

    /**
     * 获取题库题目列表(后台)
     * @author 英才
     * @param array $result 返回题目信息
     * @param array $reqData 请求数据
     *
     * @return bool
     */
    public function get_bank_topic_list(&$result, $reqData)
    {
        $eb_id = rintval($reqData['eb_id']);
        $title = raddslashes($reqData['title']);
        $et_type = rintval($reqData['et_type']);
        $attr_id = rintval($reqData['attr_id']);

        // 题库ID不能为空
        if (!$eb_id) {

            E('_EMPTY_EB_ID');

            return false;
        }
        // 题库不存在
        if (!$this->_d_bank->get($eb_id)) {

            E('_ERR_BANK_NO_EXISTS');

            return false;
        }

        // 默认值
        $page = !empty($reqData['page']) ? intval($reqData['page']) : self::DEFAULT_PAGE;
        $limit = !empty($reqData['limit']) ? intval($reqData['limit']) : self::DEFAULT_LIMIT_ADMIN;
        // 分页
        list($start, $limit) = page_limit($page, $limit);
        // 排序
        $order_option = array('order_num' => 'ASC', 'et_id' => 'DESC');

        // 初始化数据
        $conds = array();
        $conds['eb_id'] = $eb_id;
        $conds['et_type'] = $et_type ? $et_type : '';
        $conds['title'] = !empty($title) ? $title : '';

        // 属性ID是否存在
        if ($attr_id) {

            $conds['attr_id'] = $attr_id;
        }

        // 获取题目总数
        $total = $this->_d->count_by_where($conds);

        $list = array();
        // 获取题目列表
        if ($total > 0) {

            $list = $this->_d->list_by_where($conds, array($start, $limit), $order_option);
        }

        // 获取题目关联的所有属性
        foreach ($list as $key => &$val) {

            $topic_attr_list = $this->_d_topic_attr->list_by_conds(array('et_id' => $val['et_id']), null, array(),
                'attr_id');
            $attr_ids = array_column($topic_attr_list, 'attr_id');

            $attr_list = $this->_d_attr->list_by_pks($attr_ids);
            $val['attr_list'] = array_column($attr_list, 'attr_name');
        }

        $result['total'] = (int)$total;
        $result['page'] = (int)$page;
        $result['limit'] = (int)$limit;
        $result['list'] = $this->format_list($list);

        return true;
    }

    /**
     * 获取题库题目列表(RPC) 何岳龙
     *
     * @param array $result 返回题目信息
     * @param array $reqData 请求数据
     *
     * @return bool
     */
    public function get_bank_topic_rpc_list(&$result, $reqData)
    {
        $eb_id = rintval($reqData['eb_id']);
        $title = raddslashes($reqData['title']);
        $type_list = $reqData['type_list'];
        $attr_id = rintval($reqData['attr_id']);

        // 默认值
        $page = !empty($reqData['page']) ? intval($reqData['page']) : self::DEFAULT_PAGE;
        $limit = !empty($reqData['limit']) ? intval($reqData['limit']) : self::DEFAULT_LIMIT_ADMIN;
        // 分页
        list($start, $limit) = page_limit($page, $limit);
        // 排序
        $order_option = array('order_num' => 'ASC');

        // 初始化数据
        $conds = array();

        // 如果题目ID存在
        if (!empty($eb_id)) {

            $conds['eb_id'] = $eb_id;
        }

        // 实例化
        $et_types = array();

        // 获取类型列表
        if (!empty($type_list)) {

            $et_types = array_unique(array_filter(array_column($type_list, 'type')));
        }

        // 如果类型不为空
        if (!empty($et_types)) {
            $conds['et_type'] = $et_types;
        }

        $conds['title'] = !empty($title) ? $title : '';

        // 属性ID是否存在
        if ($attr_id) {

            $conds['attr_id'] = $attr_id;
        }

        // 获取题目总数
        $total = $this->_d->count_by_where($conds);

        $list = array();
        // 获取题目列表
        if ($total > 0) {

            $list = $this->_d->list_by_where($conds, array($start, $limit), $order_option);
        }

        // 获取题目关联的所有属性
        foreach ($list as $key => &$val) {

            $topic_attr_list = $this->_d_topic_attr->list_by_conds(array('et_id' => $val['et_id']), null, array(),
                'attr_id');
            $attr_ids = array_column($topic_attr_list, 'attr_id');

            $attr_list = $this->_d_attr->list_by_pks($attr_ids);
            $val['attr_list'] = array_column($attr_list, 'attr_name');
        }

        $result['total'] = (int)$total;
        $result['page'] = (int)$page;
        $result['limit'] = (int)$limit;
        $result['list'] = $this->format_list($list);

        return true;
    }

    /**
     * 验证新增活动数据 【谁写的？？？】
     * @author 英才
     * @param array $topic 活动数据
     *
     * @return bool
     */
    protected function validate_for_add(&$topic)
    {

        // 验证规则
        $rules = array(
            'eb_id' => 'require',
            // 'order_num' => 'require|number',
            'score' => 'require|number',
            'title' => 'require',
        );
        // 错误提示
        $msgs = array(
            'eb_id' => L('_EMPTY_EB_ID'),
            // 'order_num.require' => L('_EMPTY_TOPIC_ORDERBY'),
            // 'order_num.number' => L('_ERR_TOPIC_ORDERBY'),
            'score.number' => L('_ERR_SCORE_NUMBER'),
            'score.require' => L('_EMPTY_TOPIC_SCORE'),
            'title.require' => L('_EMPTY_ET_TITLE'),
        );
        // 开始验证
        $validate = new Validate($rules, $msgs);
        if (!$validate->check($topic)) {

            E($validate->getError());

            return false;
        }
        // 标题长度过长
        if ($this->utf8_strlen($topic['title']) > 500) {

            E('_ERR_TITLE_LENGTH');

            return false;
        }
        // 试题类型错误
        if (!in_array($topic['et_type'], array(
            self::TOPIC_TYPE_SINGLE,
            self::TOPIC_TYPE_JUDGMENT,
            self::TOPIC_TYPE_QUESTION,
            self::TOPIC_TYPE_MULTIPLE,
            self::TOPIC_TYPE_VOICE,
        ))
        ) {
            E('_ERR_TOPIC_TYPE_WRONG');

            return false;
        }
        // 不是语音题，答案不能为空
        if (empty($topic["answer"]) && $topic['et_type'] != self::TOPIC_TYPE_VOICE) {

            E('_EMPTY_ET_ANSWER');

            return false;
        }
        // 题库是否存在
        if (!$this->_d_bank->get($topic['eb_id'])) {

            E('_ERR_BANK_NO_EXISTS');

            return false;
        }
        // 题目图片
        if (!empty($topic['title_pic'])) {

            $atids = array_column($topic['title_pic'], 'atId');

            $topic['title_pic'] = implode(',', $atids);
        }
        // 选项 这是单选、多选题选项
        if ($topic["et_type"] == self::TOPIC_TYPE_MULTIPLE || $topic["et_type"] == self::TOPIC_TYPE_SINGLE) {

            // 选项不能为空
            if (empty($topic["options"])) {

                E('_EMPTY_TOPIC_OPTIONS');

                return false;
            }
            // 初始化选项值
            $option_values = array();
            // 选项值列表
            foreach ($topic['options'] as $v) {
                $option_values[] = $v['option_value'];
            }
            // 选项值不能有空
            if (count($option_values) != count(array_filter($option_values))) {

                E('_EMPTY_OPTIONS_VALUE');

                return false;
            }
            // 选项值不能有重复
            if (count($option_values) != count(array_unique($option_values))) {

                E('_ERR_OPTIONS_REPEAT');

                return false;
            }
            // 选项个数最多20个
            if (count($topic['options']) > 20) {

                E('_ERR_OPTIONS_MAX');

                return false;
            }

            $answer = array_unique(array_filter(explode(',', $topic['answer'])));
            // 多选题正确答案少于2个
            if ($topic["et_type"] == self::TOPIC_TYPE_MULTIPLE && count($answer) < 2) {

                E('_ERR_ANSWER_MIN_TWO');

                return false;
            }

            // 重新排序答案选项
            sort($answer);
            $topic['answer'] = implode(',', $answer);

            // 序列化选项
            $topic["options"] = serialize($topic["options"]);
        }
        // 匹配关键字 问答题
        if ($topic["et_type"] == self::TOPIC_TYPE_QUESTION && $topic["match_type"] == self::KEYWORD_OPEN) {

            // 判断关键字是否为空
            $keywords = array_column($topic["answer_keyword"], 'keyword');
            if (count($keywords) != count(array_filter($keywords))) {

                E('_EMPTY_TOPIC_KEYWORDS');

                return false;
            }
            // 判断关键字是否有重复
            if (count($keywords) != count(array_unique($keywords))) {

                E('_ERR_TOPIC_KEYWORDS_REPEAD');

                return false;
            }
            // 判断关键字覆盖率是否为100%
            if (array_sum(array_column($topic["answer_keyword"], 'percent')) != 100) {

                E('_ERR_KEYWORDS_COVERAGE');

                return false;
            }
            $topic["answer_keyword"] = serialize($topic["answer_keyword"]);
        } else {
            // 不是问答题，也没开启关键字匹配，清空关键字字段
            $topic["answer_keyword"] = serialize([]);
        }
        // 答案解析
        if (!empty($topic["answer_resolve"])) {
            if ($this->utf8_strlen($topic['answer_resolve']) > 500) {

                E("_ERR_ANSWER_RESOLVE_LENGTH");

                return false;
            }
        }
        // 正确答案
        if (!empty($topic["answer"])) {

            // 问答题正确答案超出字数限制
            if ($topic["et_type"] == self::TOPIC_TYPE_QUESTION && $this->utf8_strlen($topic['answer']) > 500) {

                E('_ERR_ANSWER_LENGTH');

                return false;
            }
        }
        // 正确答案覆盖率 【问答题】
        if ($topic["et_type"] == self::TOPIC_TYPE_QUESTION && !empty($topic["answer_coverage"])) {

            if ($topic["answer_coverage"] > 100) {

                E('_ERR_ANSWER_COVERAGE_LIMIT');

                return false;
            }
        }

        return true;
    }

    /**
     * 获取题目数据
     * @author 英才 *
     * @param array $topic 题目数据
     *
     * @return array|bool
     */
    protected function fetch_topic($topic)
    {
        return array(
            'eb_id' => rintval($topic['eb_id']),
            'et_type' => rintval($topic['et_type']),
            'order_num' => rintval($topic['order_num']),
            'score' => rintval($topic['score']),
            'title' => raddslashes($topic['title']),
            'title_pic' => !empty($topic['title_pic']) ? $topic['title_pic'] : '',
            'options' => !empty($topic['options']) ? $topic['options'] : '',
            'answer_coverage' => !empty($topic['answer_coverage']) ? raddslashes($topic['answer_coverage']) : '',
            'match_type' => rintval($topic['match_type']),
            'answer_keyword' => !empty($topic['answer_keyword']) ? $topic['answer_keyword'] : '',
            'answer' => !empty($topic['answer']) ? raddslashes($topic['answer']) : '',
            'answer_resolve' => !empty($topic['answer_resolve']) ? raddslashes($topic['answer_resolve']) : '',
            'tag_list' => !empty($topic['tag_list']) ? $topic['tag_list'] : '',
        );
    }

    /**
     * @param array $tab_list 标签属性列表
     * @author 英才
     * @param int $eb_id 题库ID
     * @param int $et_id 题目ID
     *
     * @return bool
     */
    protected function insert_topic_attr_data($tab_list, $eb_id, $et_id)
    {
        foreach ($tab_list as $val) {

            foreach ($val['attr_list'] as $attr) {
                $attr_data[] = array(
                    'etag_id' => $val['etag_id'],
                    'attr_id' => $attr['attr_id'],
                    'eb_id' => $eb_id,
                    'et_id' => $et_id,
                );
            }
        }

        // 如果题目关系属性数据存在
        if (!empty($attr_data)) {

            $this->_d_topic_attr->insert_all($attr_data);
        }

        return true;
    }

    /**
     * 更新题库中题目类别的个数(加)
     * @author 英才
     * @param  int $et_type 题目类型
     * @param  array $conds 更新条件
     * @param  string $type 操作方法
     *
     * @return bool
     */
    public function update_eb_topic_add($et_type, $conds, $type = 'add')
    {

        // 题库中这类题的个数 +1
        switch ($et_type) {
            case self::TOPIC_TYPE_SINGLE:
                // 单选题
                $this->_d_bank->setIncNum("single_count", $conds, 1);
                break;
            case self::TOPIC_TYPE_JUDGMENT:
                // 判断题
                $this->_d_bank->setIncNum("judgment_count", $conds, 1);
                break;
            case self::TOPIC_TYPE_QUESTION:
                // 问答题
                $this->_d_bank->setIncNum("question_count", $conds, 1);
                break;
            case self::TOPIC_TYPE_MULTIPLE:
                // 多选题
                $this->_d_bank->setIncNum("multiple_count", $conds, 1);
                break;
            case self::TOPIC_TYPE_VOICE:
                // 语音题
                $this->_d_bank->setIncNum("voice_count", $conds, 1);
                break;
            default:
                E('_ERR_TOPIC_TYPE_WRONG');

                return false;
                break;
        }

        if ($type == 'add') {

            // 题库中的题的个数 +1
            $this->_d_bank->setIncNum("total_count", $conds, 1);
        }

        return true;
    }

    /**
     * 更新题库中题目类别的个数（减）
     * @author 英才
     * @param  int $et_type 题目类型
     * @param  array $conds 更新条件
     *
     * @return bool
     */
    public function update_eb_topic_cutting($et_type, $conds)
    {
        // 题库中这类题的个数 -1
        switch ($et_type) {
            case self::TOPIC_TYPE_SINGLE:
                // 单选题
                $this->_d_bank->setDecNum("single_count", $conds, 1);
                break;
            case self::TOPIC_TYPE_JUDGMENT:
                // 判断题
                $this->_d_bank->setDecNum("judgment_count", $conds, 1);
                break;
            case self::TOPIC_TYPE_QUESTION:
                // 问答题
                $this->_d_bank->setDecNum("question_count", $conds, 1);
                break;
            case self::TOPIC_TYPE_MULTIPLE:
                // 多选题
                $this->_d_bank->setDecNum("multiple_count", $conds, 1);
                break;
            case self::TOPIC_TYPE_VOICE:
                // 语音题
                $this->_d_bank->setDecNum("voice_count", $conds, 1);
                break;
            default:
                E('_ERR_TOPIC_TYPE_WRONG');

                return false;
                break;
        }

        return true;
    }

    /**
     * 格式化题库列表数据
     * @author 英才
     * @param $data array 需要格式化的数据列表
     *
     * @return array
     */
    protected function format_list($data = array())
    {
        $list = array();
        if (!empty($data) && is_array($data)) {

            foreach ($data as $k => $v) {
                $list[$k]['et_id'] = rintval($v['et_id']);
                $list[$k]['title'] = $v['title'];
                $list[$k]['et_type'] = rintval($v['et_type']);
                $list[$k]['score'] = $v['score'];
                $list[$k]['order_num'] = rintval($v['order_num']);
                $list[$k]['use_num'] = rintval($v['use_num']);
                $list[$k]['attr_list'] = $v['attr_list'] ? $v['attr_list'] : array();
            }
        }

        return $list;
    }

    /**
     * 获取标题长度
     * @author 岳龙
     * @param array $list 列表数据
     *
     * @return int|string
     */

    private function countHead($list)
    {

        // 实例化
        $data = 0;

        // 循环数据
        for ($i = self::TIT_LEN_LEN; $i > 0; $i--) {

            // 判断非空
            if (!empty($list[$i])) {

                // 赋值
                $data = $i + 1;

                break;
            }

        }

        return $data;

    }

    /**
     * 获取head数据列表以及整合过的xls中数据列表
     * @author 岳龙
     * @param array $list xls数据列表
     *
     * @return array
     */

    public function get_list($list = array())
    {

        // 初始化返回值
        $data = array();

        // 初始化
        $i = 0;

        // 存储标题长度
        $storage = array();

        // 循环数据
        foreach ($list as $key => $v) {

            // 截取前三条数据
            if ($i < self::TPL_LEN) {
                $i++;
                continue;
            }

            // 赋值
            $data[] = array(
                'et_type' => $v[0] ? trim($v[0]) : '',
                'attr_list' => $v[1] ? $v[1] : '',
                'title' => $v[2] ? trim($v[2]) : '',
                'score' => (int)$v[3] ? trim($v[3]) : '',
                'answer' => $v[4] ? trim($v[4]) : '',
                'answer_resolve' => $v[5] ? trim($v[5]) : '',
                'A' => $v[6] ? trim($v[6]) : '',
                'B' => $v[7] ? trim($v[7]) : '',
                'C' => $v[8] ? trim($v[8]) : '',
                'D' => $v[9] ? trim($v[9]) : '',
                'E' => $v[10] ? trim($v[10]) : '',
                'F' => $v[11] ? trim($v[11]) : '',
                'G' => $v[12] ? trim($v[12]) : '',
                'H' => $v[13] ? trim($v[13]) : '',
                'I' => $v[14] ? trim($v[14]) : '',
                'J' => $v[15] ? trim($v[15]) : '',
            );

            // 存储所有表格长度
            $storage[] = $this->countHead($v);
        }

        // 表格题目数组
        $title = array();

        // 初始化表头最小长度
        $min = self::TIT_LEN;

        // 获取本文件中的类型
        $types = array_column($data,'et_type');

        // 含有多选题，则最小表头加一
        if(in_array($this->_xls_type['checkbox'],$types)){
            $min = self::TIT_LEN + self::LEN;
        }

        // 设置最大表头长度
        if (max($storage) < $min) {

            $max_title = $min;
        } else {

            $max_title = max($storage);
        }

        // 重组表格标题
        for ($i = 0; $i < $max_title; $i++) {

            // 标题表格列表
            $title[$i] = $this->__fieldName[$i];

            // 如果是最大值
            if ($max_title - self::LEN == $i) {

                // 新增是否导入成功
                $title[$i + self::LEN] = $this->__fieldName[self::HEAD_LEN];

            }

        }

        return array($data, $title, count($data), count($title) - self::LEN);
    }

    /**
     * 重新获取列表
     * @author 岳龙
     * @param array $data POST数据
     *
     * @return bool
     */
    public function get_data(&$data = array())
    {

        // 如果类型为判断题
        if ($data['type'] == $this->_xls_type['jundgment']) {

            // 初始化数据
            $i = 0;

            // 遍历数据
            foreach ($data as $key => $item) {

                // 如果为判断题
                if ($i > self::JU_LEN) {

                    $data[$key] = '';
                }

                $i++;
            }

        }

        return true;
    }

    /**
     * 判断列表条件
     * @author 岳龙
     * @param array $data POST 数据
     *
     * @return mixed
     */
    public function is_parameter($data)
    {
        // 实例化导出结果
        $result = array();

        // 是否为选择题
        $select = false;

        // 如果列表类型不符和要求
        if (!in_array($data['et_type'], $this->_xls_type)) {

            $result[] = array(
                'code' => 'et_type',
                'msg' => $this->_errorList['et_type'],
            );
        }

        // 如果分数排序不符和要求
        if (!is_numeric($data['score']) || strstr($data['score'], ".") || intval($data['score']) < 1) {

            $result[] = array(
                'code' => 'score',
                'msg' => $this->_errorList['score'],
            );

        }

        // 如果题目要求
        if (empty($data['title']) || $this->utf8_strlen($data['title']) > self::TIT_MUN) {

            $result[] = array(
                'code' => 'title',
                'msg' => $this->_errorList['title'],
            );

        }

        // 如果答案解析存在
        if (!empty($data['answer_resolve'])) {

            if ($this->utf8_strlen($data['answer_resolve']) > self::TIT_MUN) {
                $result[] = array(
                    'code' => 'answer_resolve',
                    'msg' => $this->_errorList['answer_resolve'],
                );
            }

        }
        // 答案数组
        $answer_data = array();

        // 选项数据A
        if (!empty($data['A'])) {

            array_push($answer_data, 'A');
        }

        // 选项数据B
        if (!empty($data['B'])) {

            array_push($answer_data, 'B');
        }
        // 选项数据C
        if (!empty($data['C'])) {

            array_push($answer_data, 'C');
        }
        // 选项数据D
        if (!empty($data['D'])) {

            array_push($answer_data, 'D');
        }
        // 选项数据E
        if (!empty($data['E'])) {

            array_push($answer_data, 'E');
        }
        // 选项数据F
        if (!empty($data['F'])) {

            array_push($answer_data, 'F');
        }
        // 选项数据G
        if (!empty($data['G'])) {

            array_push($answer_data, 'G');
        }
        // 选项数据H
        if (!empty($data['H'])) {

            array_push($answer_data, 'H');
        }

        // 选项数据I
        if (!empty($data['I'])) {

            array_push($answer_data, 'I');
        }

        // 选项数据J
        if (!empty($data['J'])) {

            array_push($answer_data, 'J');
        }

        // 如果类型为单选题
        if ($data['et_type'] == $this->_xls_type['radio']) {

            $select = true;

            // 如果答案不在选项中
            if (!in_array($data['answer'], $this->_options_list) || !in_array($data['answer'],
                    $answer_data) || empty($data['answer'])
            ) {

                $result[] = array(
                    'code' => 'answer',
                    'msg' => $this->_errorList['answer'],
                );
            }

            // 如果选项A不存在
            if (empty($data['A'])) {

                $result[] = array(
                    'code' => 'A',
                    'msg' => $this->_errorList['A'],
                );
            }

            // 如果选项B不存在
            if (empty($data['B'])) {

                $result[] = array(
                    'code' => 'B',
                    'msg' => $this->_errorList['B'],
                );

            }

        }

        // 如果类型为多选题
        if ($data['et_type'] == $this->_xls_type['checkbox']) {

            $select = true;

            // 拆分为数组
            $answer = str_split($data['answer']);

            // 遍历答案
            foreach ($answer as $key => $v) {

                // 如果答案不在选项中
                if (!in_array($v, $this->_options_list) || !in_array($v,
                        $answer_data) || count($answer) < self::CHECKBOX_LEN || empty($data[$v])
                ) {

                    $result[] = array(
                        'code' => 'answer',
                        'msg' => $this->_errorList['answer'],
                    );

                    break;
                }
            }

            // 如果选项A不存在
            if (empty($data['A'])) {

                $result[] = array(
                    'code' => 'A',
                    'msg' => $this->_errorList['A'],
                );
            }

            // 如果选项B不存在
            if (empty($data['B'])) {

                $result[] = array(
                    'code' => 'B',
                    'msg' => $this->_errorList['B'],
                );
            }

            // 如果选项C不存在
            if (empty($data['C'])) {

                $result[] = array(
                    'code' => 'C',
                    'msg' => $this->_errorList['C'],
                );
            }

        }

        // 如果是选择题
        if ($select) {

            // 获取选项错误原因
            $this->select($result, $data);
        }

        // 如果类型为判断题
        if ($data['et_type'] == $this->_xls_type['jundgment']) {

            // 如果答案不在选项中
            if (!in_array($data['answer'], $this->_choice_list)) {

                $result[] = array(
                    'code' => 'answer',
                    'msg' => $this->_errorList['answer'],
                );

            }

        }

        return $result;
    }

    /**
     * 获取选项错误原因
     * @author 岳龙
     * @param array $result 错误结果集
     * @param array $data POST参数数组
     *
     * @return bool
     */
    private function select(&$result = array(), $data = array())
    {

        // 遍历选项列表
        foreach ($this->_options_list as $key => $v) {

            // 如果选项存在
            if (!empty($data[$v])) {

                // 判断已存在对象上级是否存在
                if (empty($data[$this->_options_list[$key - self::LEN]]) && $key > self::LEN) {

                    // 遍历当前选项列表数据
                    for ($i = self::LEN + 1; $i < $key; $i++) {

                        // 如果选项列表对应的值不存在
                        if (empty($data[$this->_options_list[$i]])) {

                            $result[] = array(
                                'code' => $this->_options_list[$i],
                                'msg' => $this->_errorList[$this->_options_list[$i]],
                            );

                        }
                    }
                }

                // 如果选项中选项值超过指定个数抛出错误码
                if ($this->utf8_strlen($data[$v]) > self::FONT_LEN) {

                    $result[] = array(
                        'code' => $v,
                        'msg' => $this->_errorList[$v],
                    );
                }

            }
        }

        return true;
    }

    /**
     * xls数据写入到数据库
     * @author 岳龙
     * @param array $params POST 数据
     *
     * @return bool
     */
    public function insert_xls_data($params = array())
    {

        try {
            $this->start_trans();

            // 实例化类型
            $et_type = 0;

            // 获取答案
            $answer = raddslashes($params['data']['answer']);

            // 如果类型为单选
            if ($params['data']['et_type'] == $this->_xls_type['radio']) {
                $et_type = self::TOPIC_TYPE_SINGLE;
            }

            // 如果类型为多选
            if ($params['data']['et_type'] == $this->_xls_type['checkbox']) {
                $et_type = self::TOPIC_TYPE_MULTIPLE;

                $answer = implode(',', str_split($answer));
            }

            // 如果类型为判断
            if ($params['data']['et_type'] == $this->_xls_type['jundgment']) {
                $et_type = self::TOPIC_TYPE_JUDGMENT;
            }

            // 初始化选项
            $options = array();

            // 循环遍历列表
            foreach ($this->_options_list as $key => $v) {

                // 如果列表选项存在
                if (!empty($params['data'][$v])) {

                    $options[] = array(
                        'option_name' => $v,
                        'option_value' => $params['data'][$v],
                        'option_image_url' => '',
                        'option_image_id' => '',
                    );

                }
            }

            // 获取题目列表
            $list = $this->_d->list_by_conds(array('eb_id' => $params['eb_id']), null, array(), 'order_num');

            // 初始化排序数
            $order_num = 1;

            // 获取有题目
            if (!empty($list)) {

                // 获取所有排序数
                $storage = array_column($list, 'order_num');

                // 获取最大排序数+1
                $order_num = max($storage) + 1;
            }

            // 组装数据
            $data = array(
                'eb_id' => rintval($params['eb_id']),
                'et_type' => rintval($et_type),
                'order_num' => rintval($order_num),
                'score' => rintval($params['data']['score']),
                'title' => raddslashes($params['data']['title']),
                'options' => $params['data']['et_type'] != $this->_xls_type['jundgment'] ? serialize($options) : '',
                'answer' => !empty($answer) ? $answer : '',
                'answer_resolve' => !empty($params['data']['answer_resolve']) ? raddslashes($params['data']['answer_resolve']) : '',
            );

            // 写入数据库
            $topic_id = $this->insert($data);

            // 如果已写入数据库且有对应属性值
            if (!empty($topic_id) && (!empty($params['data']['attr_list']))) {

                // 初始化属性数据
                $attr_data = array();

                // 置换和字符串
                $attr_lists = str_replace('；', ';', $params['data']['attr_list']);

                // 字符串转数组
                $attr_list = explode(';', $attr_lists);

                // 遍历数据
                foreach ($attr_list as &$v) {

                    $v = trim($v);
                }

                // 获取所有标签属性数据
                $list = $this->_d_attr->list_by_conds(array('attr_name' => $attr_list));

                // 遍历数据
                foreach ($list as $key => $v) {

                    $attr_data[] = array(
                        'etag_id' => $v['etag_id'],
                        'attr_id' => $v['attr_id'],
                        'eb_id' => $params['eb_id'],
                        'et_id' => $topic_id,
                    );
                }

                // 如果题目关系属性数据存在
                if (!empty($attr_data)) {

                    $this->_d_topic_attr->insert_all($attr_data);
                }

            }

            // 更新题目数
            $this->update_eb_topic_add($et_type, array('eb_id' => $params['eb_id']));

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
     * 根据条件，查询包含已删除的数据
     * @author 岳龙
     * @param array $conds 条件数组
     * @param int|array $page_option 分页参数
     * @param array $order_option 排序
     * @param string $fields 读取字段
     *
     * @return array|bool
     */
    public function list_topic_contain_del($conds, $page_option, $order_option, $fields = '*')
    {

        return $this->_d->list_topic_contain_del($conds, $page_option, $order_option, $fields);
    }

    /**
     * 根据条件，统计包含已删除的数据记录数
     *
     * @param array $conds 条件数组
     * @param int|array $page_option 分页参数
     * @param array $order_option 排序
     * @param string $fields 读取字段
     *
     * @return array|bool
     */
    public function count_topic_contain_del($conds, $fields = '*')
    {

        return $this->_d->count_topic_contain_del($conds, $fields);
    }
}
