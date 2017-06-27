<?php
/**
 * ListController.class.php
 *【考试中心-手机端】考试作答情况列表接口
 * @author: 蔡建华
 * @date :  2017-05-23
 * @version $Id$
 */

namespace Api\Controller\Breakthrough;

use Common\Service\BreakDetailService;

class ListController extends AbstractController
{
    /**
     * @var  BreakDetailService
     */
    protected $break_detail_serv;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }
        // 实例化答卷详情service
        $this->break_detail_serv = new BreakDetailService();

        return true;
    }

    public function Index_post()
    {
        // 首先判断是否参与考试

        $params = I('post.');
        $ebreak_id = intval($params['ebreak_id']);
        if (!$ebreak_id) {
            E('_EMPTY_EBREAK_ID');
            return false;
        }
        // 答题总数
        $total = $this->break_detail_serv->count_by_conds(array("ebreak_id" => $ebreak_id));
        $data = array();
        $answer_num = 0;
        if ($total) {
            // 试题列表
            $data_detail = $this->break_detail_serv->list_by_conds(array("ebreak_id" => $ebreak_id));
            if (empty($data_detail)) {
                E("_ERR_NO_VISIT_EXAM");
                return false;
            }
            // 查询未答题的数量
            $answer_num = $this->break_detail_serv->count_by_conds(array("ebreak_id" => $ebreak_id, 'is_status' => 1));
            $data = $this->break_detail_serv->get_break_detail($data_detail);
        } else {
            E("_EMPTY_PAPER_DATA");
            return false;
        }
        $this->_result = array(
            'total' => intval($total),
            'unanswer_num' => intval($total - $answer_num),
            'answer_num' => intval($answer_num),
            'list' => $data
        );

        return true;
    }
}
