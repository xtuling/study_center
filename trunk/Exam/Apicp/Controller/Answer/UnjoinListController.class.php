<?php
/**
 * 获取考试统计详情未参与列表
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-05-24 15:19:33
 * @version $Id$
 */

namespace Apicp\Controller\Answer;

use Common\Service\AnswerService;
use Common\Service\PaperService;
use Common\Service\RightService;

class UnjoinListController extends AbstractController
{
    /**
     * @var  PaperService  实例化权限表对象
     */
    protected $paper_serv;

    /**
     * @var  AnswerService  实例化答卷表对象
     */
    protected $answer_serv;

    /**
     * @var  RightService  实例化权限表对象
     */
    protected $right_serv;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }

        $this->answer_serv = new AnswerService();
        $this->right_serv = new RightService();
        $this->paper_serv = new PaperService();

        return true;
    }

    public function Index_post()
    {
        $params = I('post.');
        //获取试卷ID
        $ep_id = rintval($params['ep_id']);

        if (empty($ep_id)) {

            E('_EMPTY_EP_ID');

            return false;
        }

        // 试卷详情
        $paper = $this->paper_serv->get($ep_id);
        // 试卷不存在
        if (empty($paper)) {

            E('_ERR_PAPER_NOT_FOUND');

            return false;
        }

        // 默认值
        $page = !empty($params['page']) ? intval($params['page']) : 1;
        $limit = !empty($params['limit']) ? intval($params['limit']) : AnswerService::DEFAULT_LIMIT_ADMIN;

        // 分页
        list($start, $limit) = page_limit($page, $limit);

        $conds = array(
            'epc_id' => $ep_id,
            'er_type' => AnswerService::RIGHT_PAPER,
        );

        // 获取未参与考试人员列表
        $unjoin_data = $this->answer_serv->get_unjoin_data($conds, $ep_id, $paper['is_all']);

        // 未参加人的列表
        $unjoin_list = $unjoin_data['unjoin_list'];

        $total = 0;

        if (!empty($unjoin_list)) {

            sort($unjoin_list);

            // 根据用户ID查询用户信息
            $members = $this->answer_serv->getUser($unjoin_list);

            // 处理数据
            $unjoin_list = $this->nojoin_data($members, $paper);

            // 总人数
            $total = count($unjoin_list);

            // 处理分页
            $unjoin_list = array_slice($unjoin_list, $start, $limit);
        }

        $this->_result = array(
            'total' => (int)$total,
            'limit' => (int)$limit,
            'page' => (int)$page,
            'list' => $unjoin_list,
        );

        return true;
    }

    /**
     * 处理未参加人员数据
     * @param array $members 所有未参加的人员
     * @param array $paper 考卷详情
     * @return array 未参加人员
     */
    private function nojoin_data($members, $paper)
    {
        $status = $paper['exam_status'];

        switch ($paper['exam_status']) {
            // 已发布
            case PaperService::PAPER_PUBLISH:
                // 【已开始】
                if (
                    $paper['begin_time'] < MILLI_TIME &&
                    ($paper['end_time'] >= MILLI_TIME || $paper['end_time'] == 0)
                ) {

                    $status = AnswerService::STATUS_ING;
                }
                // 【已结束】
                if ($paper['end_time'] > 0 && $paper['end_time'] < MILLI_TIME) {

                    $status = AnswerService::STATUS_END;
                }
                break;

            // 已终止
            case PaperService::PAPER_STOP:
                $status = AnswerService::STATUS_STOP;
                break;

            default:
        }

        $data = array();

        foreach ($members as $key => $value) {

            $data[] = array(
                'uid' => $key,
                'username' => $value['memUsername'],
                'dpName' => implode(',', array_column($value['dpName'], 'dpName')),
                'created' => $paper['created'],
                'exam_status' => $status,
            );
        }

        return $data;
    }

}
