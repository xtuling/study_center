<?php
/**
 * 未读人员提醒
 * @author: 何岳龙
 * @date :  2017年5月31日16:48:05
 * @version $Id$
 */

namespace Apicp\Controller\Answer;

use Common\Service\AnswerService;
use Common\Service\PaperService;

class RemindController extends AbstractController
{
    /**
     * @var  PaperService  实例化答卷表对象
     */
    protected $paper_serv;

    /**
     * @var  AnswerService  实例化答卷表对象
     */
    protected $answer_serv;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }

        $this->paper_serv = new PaperService();
        $this->answer_serv = new AnswerService();

        return true;
    }

    public function Index_post()
    {
        $params = I('post.');

        $ep_id = rintval($params['ep_id']);


        // UIDS不能为空
        if (!empty($params['uids'])) {

            $uids = array_unique(array_filter(array_column($params['uids'], 'uid')));

            // 如果UID不存在
            if (empty($uids)) {

                E('_EMPTY_UID');

                return false;
            }

        }

        // 试卷ID不能为空
        if (empty($ep_id)) {

            E('_EMPTY_EP_ID');

            return false;
        }

        // 获取试卷信息
        $paper = $this->paper_serv->get($ep_id);
        // 试卷不存在
        if (empty($paper)) {

            E('_ERR_PAPER_NOT_FOUND');

            return false;
        }

        // 如果考试已终止
        if ($paper['exam_status'] == PaperService::PAPER_STOP) {


            E('_ERR_EXAM_STOP');

            return false;
        }

        // 如果考试已结束
        if ($paper['end_time'] <= MILLI_TIME) {

            E('_ERR_EXAM_END');

            return false;
        }

        // 如果未读人员列表为空
        if (empty($params['uids'])) {

            $conds = array(
                'epc_id' => $ep_id,
                'er_type' => AnswerService::RIGHT_PAPER
            );

            // 获取未参与考试人员列表及人数
            $unjoin_data = $this->answer_serv->get_unjoin_data($conds, $ep_id, $paper['is_all']);

            // 未参加人的列表
            $list = $unjoin_data['unjoin_list'];

            sort($list);

        } else {

            // 获取用户IDS
            $list = array_unique(array_filter(array_column($params['uids'], 'uid')));

        }

        // 消息提醒
        $data = array(
            'uids' => $list,
            'name' => $paper['ep_name'],
            'description' => $paper['intro'],
            'img_id' => $paper['cover_id'],
            'id' => $paper['ep_id']
        );

        // 给未读人员发送消息
        $this->answer_serv->send_msg($data, AnswerService::ANSWER_UN_MSG);

        $this->_result = array();

        return true;

    }

}
