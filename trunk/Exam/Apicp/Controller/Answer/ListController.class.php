<?php
/**
 * 获取考试统计列表
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-05-23 16:30:05
 * @version $Id$
 */

namespace Apicp\Controller\Answer;

use Common\Service\AnswerDetailService;
use Common\Service\AnswerService;
use Common\Service\PaperService;
use Common\Service\RightService;

class ListController extends AbstractController
{
    /**
     * @var  PaperService  实例化答卷表对象
     */
    protected $paper_serv;

    /**
     * @var  RightService  实例化答卷表对象
     */
    protected $right_serv;

    /**
     * @var  AnswerService  实例化答卷表对象
     */
    protected $answer_serv;

    /**
     * @var  AnswerDetailService  实例化答卷详情表对象
     */
    protected $answer_detail_serv;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }

        $this->paper_serv = new PaperService();
        $this->right_serv = new RightService();
        $this->answer_serv = new AnswerService();
        $this->answer_detail_serv = new AnswerDetailService();

        return true;
    }

    public function Index_post()
    {
        $params = I('post.');

        // 默认值
        $page = !empty($params['page']) ? intval($params['page']) : 1;
        $limit = !empty($params['limit']) ? intval($params['limit']) : PaperService::DEFAULT_LIMIT_ADMIN;

        // 分页
        list($start, $limit) = page_limit($page, $limit);

        // 分页参数
        $page_option = array($start, $limit);

        // 发布时间倒序
        $order_option = array('ep_id' => 'DESC');

        $paper_list = array();

        // 列表总数
        $params['search_type'] = 1;
        $total = $this->paper_serv->count_search_where($params);
        if ($total > 0) {

            $fields = 'ep_id,paper_type,ep_name,begin_time,end_time,exam_status,is_all,join_count,unjoin_count';
            $paper_list = $this->paper_serv->list_search_where($params, $page_option, $order_option, $fields);
        }

        // 组装返回数据
        $this->format_paper_list($paper_list);

        $this->_result = array(
            'total' => (int)$total,
            'limit' => (int)$limit,
            'page' => (int)$page,
            'list' => !empty($paper_list) ? $paper_list : array(),
        );

        return true;
    }

    /**
     * 组装返回数据
     * @param array $list 试卷列表
     * @return bool
     */
    protected function format_paper_list(&$list)
    {
        foreach ($list as &$paper) {

            $conds = array(
                'epc_id' => $paper['ep_id'],
                'er_type' => AnswerService::RIGHT_PAPER,
            );

            // 组装权限
            if ($paper['is_all'] != PaperService::AUTH_ALL) {

                list($db_right, $right) = $this->right_serv->get_right_data($conds);

                $paper['right'] = !empty($right) ? $right : array();
            }

            switch ($paper['exam_status']) {
                // 已发布
                case PaperService::PAPER_PUBLISH:
                    // 【已开始】
                    if (
                        $paper['begin_time'] < MILLI_TIME &&
                        ($paper['end_time'] >= MILLI_TIME || $paper['end_time'] == 0)
                    ) {

                        $paper['exam_status'] = PaperService::STATUS_ING;
                    }
                    // 【已结束】
                    if ($paper['end_time'] > 0 && $paper['end_time'] < MILLI_TIME) {

                        $paper['exam_status'] = PaperService::STATUS_END;
                    }
                    break;

                // 已终止
                case PaperService::PAPER_STOP:
                    $paper['exam_status'] = PaperService::STATUS_STOP;
                    break;

                default:
            }

            $paper['ep_id'] = (int)$paper['ep_id'];
            $paper['is_all'] = (int)$paper['is_all'];
            $paper['paper_type'] = (int)$paper['paper_type'];
        }

        return true;
    }

}
