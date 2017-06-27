<?php
/**
 * ListController.class.php
 *【考试中心-手机端】考试作答情况列表接口
 * @author: 蔡建华
 * @date :  2017-05-23
 * @version $Id$
 */

namespace Api\Controller\Answer;

use Common\Service\AnswerDetailService;
use Common\Service\AnswerService;

class ListController extends AbstractController
{
    /**
     * @var  AnswerDetailService
     */
    protected $answer_detail_serv;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }
        // 实例化答卷详情service
        $this->answer_detail_serv = new AnswerDetailService();

        return true;
    }

    public function Index_post()
    {
        // 首先判断是否参与考试
        $params = I('post.');
        $ea_id = intval($params['ea_id']);
        // 0 答题中 1 全部解析 2 错题解析
        $type = intval($params['type']);
        if (!$ea_id) {
            E('_EMPTY_EA_ID');
            return false;
        }
        // 查询答题数量
        $total = $this->answer_detail_serv->count_by_conds(array("ea_id" => $ea_id));
        $data = array();
        // 查询未答题的数量
        $unanswer_num = 0;
        if ($total) {
            // 获取答卷详情
            $data_detail = $this->answer_detail_serv->list_by_conds(array("ea_id" => $ea_id));
            if (empty($data_detail)) {
                E("_ERR_NO_VISIT_EXAM");
                return false;
            }
            // 查询未答题的数量
            $unanswer_num = $this->answer_detail_serv->count_by_conds(array(
                "ea_id" => $ea_id,
                'is_pass' => AnswerService::DO_PASS_STATE,
                'my_answer' => ''
            ));
            // 获取试题状态列表
            $data = $this->answer_detail_serv->get_answer_detail($data_detail, $type);
        } else {
            E("_EMPTY_PAPER_DATA");
            return false;
        }
        // 查询总记录
        $count = $total;
        // 2 错题解析
        if ($type == 2) {
            $count = count($data);
        }
        $this->_result = array(
            // 查询总记录
            'total' => intval($count),
            'unanswer_num' => intval($unanswer_num),// 未题数
            'answer_num' => $count - intval($unanswer_num),// 答题数
            'list' => $data
        );
        return true;
    }
}
