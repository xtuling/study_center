<?php
/**
 * 根据题库获取标签属性（新建试卷时用）
 * BankAttrController.class.php
 * User: daijun
 * Date: 2017-05-23
 */

namespace Apicp\Controller\Tag;

use Common\Service\AttrService;
use Common\Service\TagService;
use Common\Service\TopicAttrService;

class BankAttrController extends AbstractController
{
    /**
     * @var TopicAttrService 试题属性关联关系表
     */
    protected $topic_attr_serv;

    public function before_action($action = '')
    {

        if (!parent::before_action($action)) {
            return false;
        }

        // 实例化试题属性关联关系表
        $this->topic_attr_serv = new TopicAttrService();

        return true;
    }

    public function Index_post()
    {
        /**
         * 1.根据题库列表去试题属性关系表查询属性集合
         * 2.去属性表查询并格式化数据返回
         */
        $bank_list = I('post.bank_list');

        // 验证参数
        if (empty($bank_list)) {
            E('_EMPTY_BANK_LIST');
            return false;
        }

        $eb_ids = array_column($bank_list, 'eb_id');
        if (empty($eb_ids)) {
            E('_EMPTY_BANK_LIST');
            return false;
        }

        // 获取返回参数
        $tag_data = $this->topic_attr_serv->get_bank_attr($eb_ids);

        // 组装返回参数
        $this->_result = array('tag_data' => $tag_data);

        return true;

    }

}
