<?php
/**
 * 微信手机端跳转
 * Auth:Xtong
 * Date:2017年06月02日
 */
namespace Frontend\Controller\Index;

use Common\Service\AnswerService;
use Common\Service\PaperService;

class MsgController extends \Common\Controller\Frontend\AbstractController
{

    /**
     * 不是必须登录
     * @var string $_require_login
     */
    protected $_require_login = false;

    public function Index()
    {
        $params = I('get.');


        // 如果已参与跳转已参与详情  未参与跳转未参与详情

        $service = new AnswerService();

        // 详情
        $info = $service->get_by_conds(array('ep_id' => $params['ep_id'], 'uid' => $this->uid, 'my_time > ?' => 0));

        // 试卷表初始化
        $paper_service = new PaperService();

        // 获取试卷详情
        $paper_info = $paper_service->get($params['ep_id']);

        $ep_status = $service->paper_status($paper_info['exam_status'], $paper_info['begin_time'],
            $paper_info['end_time']);

        // 未作答
        if (!empty($info)) {

            // 测评
            if ($paper_info['paper_type'] == PaperService::EVALUATION_PAPER_TYPE) {

                // 已作答测评详情
                redirectFront('app/page/exam/exam-result',
                    array(
                        '_identifier' => APP_IDENTIFIER,
                        'ea_id' => $info['ea_id'],
                        'pageType' => 'going',
                        'ep_status' => $ep_status
                    ));

            }

            // 模拟
            if ($paper_info['paper_type'] == PaperService::SIMULATION_PAPER_TYPE) {

                // 已作答模拟
                redirectFront('app/page/exam/exam-test-record',
                    array('_identifier' => APP_IDENTIFIER, 'ep_id' => $params['ep_id'], 'ep_status' => $ep_status));

            }


        } else {

            // 未作答
            redirectFront('app/page/exam/exam-start',
                array('_identifier' => APP_IDENTIFIER, 'ep_id' => $params['ep_id'], 'ep_status' => $ep_status));

        }

    }
}
