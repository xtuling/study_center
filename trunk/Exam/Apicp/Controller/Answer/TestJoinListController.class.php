<?php
/**
 * 获取测试考试统计已参与详情列表
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-05-23 16:31:25
 * @version $Id$
 */

namespace Apicp\Controller\Answer;

use Common\Service\AnswerService;
use Common\Service\PaperService;

class TestJoinListController extends AbstractController
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
        // 试卷类型不是测评试卷
        if ($paper['paper_type'] != PaperService::EVALUATION_PAPER_TYPE) {

            E('_ERR_PAPER_TYPE_TEST');

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
            'my_score' => 'DESC',
            'created' => 'ASC'
        );

        $conds = array('ep_id' => $ep_id, 'my_time > ?' => 0);

        // 统计参与的人数
        $total = $this->answer_serv->count_by_conds($conds);

        // 参与这场考试的人员的考试信息
        $join = $this->answer_serv->list_by_conds($conds, $page_option, $order_by);

        if (!empty($join)) {

            // 参与考试的所有人的UID
            $uids = array_column($join, 'uid');
            // 参与考试的人员的详细信息列表
            $userlist = $this->answer_serv->getUser($uids);

            // 格式化返回字段信息
            foreach ($join as $key => &$val) {

                // 获取用户开始考试时间和结束考试时间
                $record = $this->answer_serv->get_by_conds(
                    array(
                        'uid' => $val['uid'],
                        'ep_id' => $ep_id
                    ),
                    array(),
                    'my_begin_time,my_time'
                );
                // 获取用户所在的所有部门
                $dpNames = array_column($userlist[$val['uid']]['dpName'], 'dpName');

                $val['ranking'] = intval($key + 1);
                $val['username'] = $userlist[$val['uid']]['memUsername'];
                $val['dpName'] = implode(',', $dpNames);
                $val['begin_time'] = $record['my_begin_time'];
                $val['end_time'] = $record['my_begin_time'] + $record['my_time'];

                unset(
                    $val['ep_id'],
                    $val['my_begin_time'],
                    $val['my_error_num'],
                    $val['paper_info'],
                    $val['domain'],
                    $val['status'],
                    $val['created'],
                    $val['updated'],
                    $val['deleted']
                );
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
