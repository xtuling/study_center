<?php
/**
 * 【考试中心-手机端】考卷答题接口
 * @author: 蔡建华
 * @date :  2017-05-23
 * @version $Id$
 */

namespace Api\Controller\Breakthrough;

use Common\Service\BreakDetailService;
use Common\Service\BreakService;

class SaveController extends AbstractController
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
        /****
         * 首先判断答题ID不能为空
         * 其次根据 如果答案为空，不更新答案，如果答案不为空表示已作答，更新答案
         */
        $params = I('post.');
        // 答题ID不能为空
        $ebd_id = intval($params['ebd_id']);
        // 答题ID不能为空
        if (!$ebd_id) {
            E('_EMPTY_EBREAK_EB_ID');
            return false;
        }
        $my_answer = $params['my_answer'];
        if (empty($my_answer)) {
            E('_EMPTY_ANSWER');
            return false;
        }
        // 获取题目信息
        $data = $this->break_detail_serv->get($ebd_id);
        if (empty($data)) {
            E('_ERR_COUNT_DETAIL_EMPTY_FOR_VOICE');
            return false;
        }
        // 查询答卷信息
        $eb = $this->break_serv->get_by_conds(array('ebreak_id' => $data['ebreak_id'], 'uid' => $this->uid));
        if (empty($eb)) {
            E('_EMPTY_PAPER_DATA');
            return false;
        } else {
            if ($eb['is_status'] == 1) {
                E('_EMPTY_EBREAK_SUBMIT');
                return false;
            }
        }
        // 进行答题
        $rel = $this->break_detail_serv->answer_save($params, $data);
        if (!$rel) {
            return false;
        }
        return true;
    }
}
