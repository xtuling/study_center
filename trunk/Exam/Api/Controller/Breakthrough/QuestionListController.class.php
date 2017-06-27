<?php
/**
 * QuestionListController.class.php
 * 【考试中心-手机端】获取闯关试卷试题列表
 * @author: 蔡建华
 * @date :  2017-05-23
 * @version $Id$
 */

namespace Api\Controller\Breakthrough;

use Common\Service\BreakDetailService;
use Common\Service\BreakService;

class QuestionListController extends AbstractController
{
    /**
     * @var  BreakDetailService
     */
    protected $break_detail_serv;
    /**
     * @var BreakService
     */
    protected $break_serv;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }
        // 实例化闯关答卷详情service
        $this->break_detail_serv = new  BreakDetailService();

        // 实例化闯关答卷service
        $this->break_serv = new BreakService();

        return true;
    }

    public function Index_post()
    {
        $params = I('post.');
        // 默认值
        $page = !empty($params['page']) ? intval($params['page']) : self::DEFAULT_PAGE;
        $limit = !empty($params['limit']) ? intval($params['limit']) : self::DEFAULT_LIMIT_ONE;
        $ebreak_id = intval($params['ebreak_id']);
        /**
         * 判断答卷ID不能为空
         */
        if (!$ebreak_id) {
            E('_EMPTY_EBREAK_ID');
            return false;
        }
        // 获取闯关答题信息
        $data = $this->break_serv->get_by_conds(array(
            'ebreak_id' => $ebreak_id,
            'uid' => $this->uid,
            'is_status' => 0
        ));
        // 分页
        list($start, $limit) = page_limit($page, $limit);
        //  按照题号排序
        $order_option = array('order_num' => 'asc');
        $conds = array('ebreak_id' => $ebreak_id);
        // 获取记录总数
        $total = $this->break_detail_serv->count_by_conds($conds);
        // 获取列表数据
        $list = array();
        if ($total > 0) {
            $list = $this->break_detail_serv->list_by_conds($conds, array($start, $limit), $order_option, '*');
        } else {
            E('_ERR_DATA_NOT_TOPIC');
            return false;
        }
        $this->_result = array(
            'left_time' => MILLI_TIME - $data['my_begin_time'],
            'total' => intval($total),
            'limit' => intval($limit),
            'page' => intval($page),
            'list' => $this->break_detail_serv->question_list_param($list)
        );

        return true;
    }
}
