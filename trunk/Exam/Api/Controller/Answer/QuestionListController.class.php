<?php
/**
 * QuestionListController.class.php
 * 【考试中心-手机端】获取试卷试题列表
 * @author: 蔡建华
 * @date :  2017-05-23
 * @version $Id$
 */

namespace Api\Controller\Answer;

use Common\Service\AnswerDetailService;
use Common\Service\AnswerService;

class QuestionListController extends AbstractController
{
    /**
     * @var  AnswerDetailService
     */
    protected $answer_detail_serv;
    /**
     * @var AnswerService
     */
    protected $answer_serv;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }
        // 实例化答卷详情service
        $this->answer_detail_serv = new AnswerDetailService();

        // 实例化答卷service
        $this->answer_serv = new AnswerService();

        return true;
    }

    public function Index_post()
    {
        /****
         * 首先判断答卷ID不能为空
         * 其次根据 答卷ID获取试题
         */
        $params = I('post.');

        // 默认值
        $page = !empty($params['page']) ? intval($params['page']) : self::DEFAULT_PAGE;
        $limit = !empty($params['limit']) ? intval($params['limit']) : self::DEFAULT_LIMIT_ONE;
        $ea_id = intval($params['ea_id']);

        /**
         * 判断答卷ID不能为空
         */
        if (!$ea_id) {
            E('_EMPTY_EP_ID');
            return false;
        }

        // 判断试卷状态
        $info = $this->answer_serv->paper_answer_status($ea_id, $this->uid);
        if (!$info) {
            return false;
        }

        // 分页
        list($start, $limit) = page_limit($page, $limit);

        // 按照题号排序
        $order_option = array('order_num' => 'asc');
        $conds = array('ea_id' => $ea_id);

        // 获取记录总数
        $total = $this->answer_detail_serv->count_by_conds($conds);

        // 获取列表数据
        $list = array();
        if ($total > 0) {
            $list = $this->answer_detail_serv->list_by_conds($conds, array($start, $limit), $order_option, '*');
        } else {
            E('_ERR_DATA_NOT_TOPIC');
            return false;
        }

        $this->_result = array(
            'ep_name' => $info['ep_name'],
            'left_time' => $info['left_time'],
            'my_begin_time' => $info['my_begin_time'],
            'paper_time' => $info['paper_time'],
            'end_time' => $info['end_time'],
            'total' => intval($total),
            'limit' => intval($limit),
            'page' => intval($page),
            // 格式化题目信息
            'list' => $this->answer_detail_serv->question_list_param($list)
        );
        return true;
    }
}
