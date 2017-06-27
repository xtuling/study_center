<?php
/**
 * 【考试中心-手机端】考卷答题接口
 * @author: 蔡建华
 * @date :  2017-05-23
 * @version $Id$
 */

namespace Api\Controller\Answer;

use Common\Service\AnswerDetailService;
use Common\Service\AnswerService;

class SaveController extends AbstractController
{
    /**
     * @var  AnswerDetailService
     */
    protected $answer_detail_serv;
    /**
     * @var  AnswerService
     */
    protected $answer_serv;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }
        // 实例化答卷详情ervice
        $this->answer_detail_serv = new AnswerDetailService();
        // 实例化答卷service
        $this->answer_serv = new AnswerService();

        return true;
    }

    public function Index_post()
    {
        /****
         * 首先判断答题ID不能为空
         * 其次根据 如果答案为空，不更新答案，如果答案不为空表示已作答，更新答案
         */
        $params = I('post.');
        // 答题ID不能为空
        $ead_id = intval($params['ead_id']);
        // 答题ID不能为空
        if (!$ead_id) {
            E('_ERR_ECT_ID_EMPTY_FOR_VOICE');
            return false;
        }
        $my_answer = $params['my_answer'];
        // 判断答案是否为空
        if (empty($my_answer)) {
            E('_EMPTY_ANSWER');
            return false;
        }
        // 判断试题是否存在
        $data = $this->answer_detail_serv->get($ead_id);
        if (empty($data)) {
            E('_ERR_COUNT_DETAIL_EMPTY_FOR_VOICE');
            return false;
        }
        // 验证考试时间
        if (!$this->answer_serv->end_time_validation($params, $this->uid)) {
            E('_ERR_END_TIME_EXAM');
            return false;
        }
        // 判断试卷状态
        if (!$this->answer_serv->paper_answer_status($data['ea_id'], $this->uid)) {
            return false;
        }
        // 调用答题接口
        if (!$this->answer_detail_serv->answer_save($params, $data)) {
            return false;
        }
        return true;
    }
}
