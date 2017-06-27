<?php
/**
 * 获取模拟考试统计详情已参与列表
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-05-24 15:18:25
 * @version $Id$
 */

namespace Apicp\Controller\Answer;

use Common\Service\AnswerService;
use Common\Service\PaperService;

class MockJoinListController extends AbstractController
{
    /**
     * @var  AnswerService  实例化答卷表对象
     */
    protected $answer_serv;

    /**
     * @var  PaperService  实例化试卷表对象
     */
    protected $paper_serv;


    // 前置操作
    public function before_action($action = '')
    {
        if (parent::before_action($action) === false) {
            return false;
        }
        $this->answer_serv = new AnswerService();
        $this->paper_serv = new PaperService();

        return true;
    }

    // 主方法
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
        // 试卷类型不是模拟试卷
        if ($paper['paper_type'] != PaperService::SIMULATION_PAPER_TYPE) {

            E('_ERR_PAPER_TYPE_EVALUATION');

            return false;
        }

        // 默认值
        $page = !empty($params['page']) ? intval($params['page']) : 1;
        $limit = !empty($params['limit']) ? intval($params['limit']) : PaperService::DEFAULT_LIMIT_ADMIN;

        // 分页
        list($start, $limit) = page_limit($page, $limit);

        $page_option = array($start, $limit);

        // 排序方式
        $order_by = array(
            'my_max_score' => 'DESC',
            'created' => 'ASC'
        );

        $conds = array('ep_id' => $ep_id, 'my_time > ?' => 0);

        // 统计参与的人数
        $total = $this->answer_serv->count_mock_answer($conds);

        // 参与这场考试的人员的考试信息
        $join = $this->answer_serv->get_mock_join_list($conds, $page_option, $order_by, 'uid,my_score,created');

        if (!empty($join)) {

            // 参与考试的所有人的UID
            $uids = array_column($join, 'uid');
            // 参与考试的人员的详细信息列表
            $userlist = $this->answer_serv->getUser($uids);

            // 格式化返回字段信息
            foreach ($join as $key => &$val) {

                $val['ranking'] = intval($key + 1);
                $val['username'] = $userlist[$val['uid']]['memUsername'];

                // 获取用户部门信息
                $dpNames = array_column($userlist[$val['uid']]['dpName'], 'dpName');
                $val['dpName'] = implode(',', $dpNames);

                // 获取用户第一次参与模拟和最后一次参与模拟的时间
                $record = $this->answer_serv->get_by_conds(
                    array(
                        'uid' => $val['uid'],
                        'ep_id' => $ep_id
                    ),
                    array(),
                    'min(my_begin_time) as begin_time, max(my_begin_time) as end_time'
                );

                $val['begin_time'] = $record['begin_time'];
                $val['end_time'] = $record['end_time'];

            }
        }

        $this->_result = array(
            'total' => (int)$total,
            'limit' => (int)$limit,
            'page' => (int)$page,
            'list' => $join
        );

        return true;
    }

}
