<?php
/**
 * 提前终止试卷
 * StopController.class.php
 * User: daijun
 * Date: 2017-05-23
 */

namespace Apicp\Controller\Paper;

use Common\Service\AnswerService;
use Common\Service\PaperService;

class StopController extends AbstractController
{
    /**
     * @var  PaperService 试卷信息表
     */
    protected $paper_serv;
    /**
     * @var AnswerService 答卷记录表
     */
    protected $answer_serv;

    public function before_action($action = '')
    {

        if (!parent::before_action($action)) {
            return false;
        }
        // 实例化试卷信息表
        $this->paper_serv = new PaperService();
        // 实例化试卷信息表
        $this->answer_serv = new AnswerService();

        return true;
    }

    public function Index_post()
    {
        /**
         * 1.根据试卷ID查询试卷状态，进行判断是否是进行中的考试，如果不是，则抛错
         * 2.更新试卷状态为已终止
         * 3.将正在进行的试卷修改为交卷状态
         */

        $ep_id = I('post.ep_id', 0, 'intval');
        $reason = I('post.reason');

        // 验证参数
        if (empty($ep_id)) {
            E('_EMPTY_PAPER_ID');
            return false;
        }

        if (empty($reason)) {
            E('_EMPTY_PAPER_REASON');
            return false;
        }

        // 获取试卷信息
        $data = $this->paper_serv->get($ep_id);

        // 判断试卷是否存在
        if (empty($data)) {
            E('_EMPTY_PAPER_DATA');
            return false;
        }

        // 获取试卷当前状态
        $paper_status = $this->paper_serv->paper_status($data['exam_status'], $data['begin_time'], $data['end_time']);

        // 如果不是进行中的试卷
        if ($paper_status != PaperService::STATUS_ING) {
            E('_ERR_STOP_ACTION');
            return false;
        }

        // 查询开始答卷但是没有交卷的记录
        $answer_list = $this->answer_serv->list_by_conds(array('ep_id' => $ep_id, 'my_time' => 0));
        foreach ($answer_list as $v) {
            // 执行交卷
            if (!$this->answer_serv->submit_papers($v['ea_id'], $v['uid'])) {
                return false;
            }
        }

        // 执行更新
        $this->paper_serv->update($ep_id,
            array('exam_status' => PaperService::PAPER_STOP, 'reason' => $reason, 'reason_time' => MILLI_TIME));

        $data['reason'] = $reason;

        // 推送消息和删除定时任务
        $this->paper_serv->stop_paper($data);

        return true;

    }

}
