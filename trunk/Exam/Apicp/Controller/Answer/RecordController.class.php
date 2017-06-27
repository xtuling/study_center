<?php
/**
 * 获取考试统计详情
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-05-23 16:33:46
 * @version $Id$
 */

namespace Apicp\Controller\Answer;

use Common\Service\AnswerService;
use Common\Service\CategoryService;
use Common\Service\PaperService;
use Common\Service\RightService;

class RecordController extends AbstractController
{
    /**
     * @var  AnswerService  实例化答卷表对象
     */
    protected $answer_serv;

    /**
     * @var  PaperService  实例化答卷表对象
     */
    protected $paper_serv;

    /**
     * @var  RightService  实例化答卷表对象
     */
    protected $right_serv;

    /**
     * @var  CategoryService  实例化答卷表对象
     */
    protected $cate_serv;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }

        $this->paper_serv = new PaperService();
        $this->answer_serv = new AnswerService();
        $this->right_serv = new RightService();
        $this->cate_serv = new CategoryService();

        return true;
    }

    public function Index_post()
    {
        $ep_id = I('ep_id', 0, 'intval');

        //获取试卷ID
        if (!$ep_id) {

            E('_EMPTY_EP_ID');

            return false;
        }
        // 获取试卷详情
        $paper = $this->paper_serv->get($ep_id);

        // 试卷不存在
        if (empty($paper)) {

            E('_ERR_PAPER_NOT_FOUND');

            return false;
        }

        $conds = array('epc_id' => $ep_id, 'er_type' => PaperService::RIGHT_PAPER);

        // 权限范围
        $right = array();
        if ($paper['is_all'] != PaperService::AUTH_ALL) {

            list($db_right, $right) = $this->right_serv->get_right_data($conds);
        }

        // 获取分类名称
        $category = $this->cate_serv->get($paper['ec_id']);

        // 获取未参与考试和已参与考试人数
        $join_data = $this->answer_serv->get_unjoin_data($conds, $ep_id, $paper['is_all']);

        // 根据用户ID查询用户信息
        $members = array();
        if (!empty($join_data['unjoin_list'])) {

            $members = $this->answer_serv->getUser($join_data['unjoin_list']);
        }
        // 【未已参与人数】
        $unjoin_count = count($members);

        // 实时统计的未参与人数和已参与人数与缓存的人数不符
        if (
            $unjoin_count != $paper['unjoin_count'] ||
            $join_data['join_count'] != $paper['join_count']
        ) {

            // 更新试卷表中缓存的未参与人数
            $this->paper_serv->update_by_paper(
                array('ep_id' => $ep_id),
                array(
                    'unjoin_count' => $unjoin_count,
                    'join_count' => $join_data['join_count']
                )
            );
        }

        // 组装返回数据
        $data = array(
            'paper_type' => (int)$paper['paper_type'],
            'ep_name' => $paper['ep_name'],
            'begin_time' => $paper['begin_time'],
            'end_time' => $paper['end_time'],
            'paper_time' => $paper['paper_time'],
            'total_score' => (int)$paper['total_score'],
            'pass_score' => (int)$paper['pass_score'],
            'ec_name' => $category['ec_name'],
            'is_all' => (int)$paper['is_all'],
            'right' => $right ? $right : array(),
            'join_count' => $join_data['join_count'],
            'unjoin_count' => $unjoin_count,
        );

        $this->_result = $data;

        return true;
    }

}
