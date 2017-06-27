<?php
/**
 * 获取试卷备选题列表
 * ToSelectListController.class.php
 * User: daijun
 * Date: 2017-05-23
 */

namespace Apicp\Controller\Paper;

use Common\Service\PaperTempService;

class ToSelectListController extends AbstractController
{
    /**
     * @var  PaperTempService 试卷试题关系信息表
     */
    protected $temp_serv;

    public function before_action($action = '')
    {

        if (!parent::before_action($action)) {
            return false;
        }

        // 实例化试卷试题关系信息表
        $this->temp_serv = new PaperTempService();

        return true;
    }

    public function Index_post()
    {
        /**
         * 1.根据试卷ID去paper_temp表查询试题id集合
         * 2.根据试题ID集合去topic表获取题目列表
         * 3.格式化返回数据
         */
        $param = I('post.');

        if (!$result = $this->temp_serv->get_temp_list($param)) {
            E('_ERR_TOPIC_TEMP_LIST');
            return false;
        }

        $this->_result = $result;

        return true;

    }

}
