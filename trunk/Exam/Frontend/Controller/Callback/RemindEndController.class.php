<?php
/**
 * 考试结束前定时提醒回调
 */

namespace Frontend\Controller\Callback;

use Common\Service\AnswerService;
use Common\Service\PaperService;
use Common\Service\RightService;

class RemindEndController extends AbstractController
{
    /**
     * @var  RightService  用户回复信息表
     */
    protected $right_s;

    /**
     * @var PaperService 试卷信息表
     */
    protected $paper_s;

    /**
     * @var AnswerService 回答信息表
     */
    protected $answer_s;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }

        // 实例化权限表
        $this->right_s = new RightService();
        // 实例化试卷表
        $this->paper_s = new PaperService();
        // 实例化回答表
        $this->answer_s = new AnswerService();

        return true;
    }

    public function Index()
    {

        // 获取需要发送的试卷id
        $back = $this->callBackData;
        $ep_id = $back['ep_id'];
        // 非空判断
        if (empty($ep_id)) {
            return true;
        }

        // 获取试卷基本详情
        $data = $this->paper_s->get($ep_id);
        if (empty($data)) {
            return true;
        }

        $conds = array(
            'epc_id' => $ep_id,
            'er_type' => AnswerService::RIGHT_PAPER
        );

        // 获取未参与考试人员列表及人数
        $data_users = $this->answer_s->get_unjoin_data($conds, $ep_id, $data['is_all']);

        $params['uids'] = $data_users['unjoin_list'];

        // 无需发送消息的情况
        if (empty($params['uids'])) {
            return true;
        }

        $params['name'] = $data['ep_name'];
        $params['description'] = $data['intro'];
        $params['img_id'] = $data['cover_id'];
        $params['begin_time'] = $data['begin_time'];
        $params['end_time'] = $data['end_time'];
        $params['id'] = $ep_id;

        // 发送考试结束前提醒
        $this->paper_s->send_msg($params, 4);

        return true;
    }

}
