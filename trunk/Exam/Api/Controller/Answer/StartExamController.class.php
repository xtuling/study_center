<?php
/**
 *  StartExamController.class.php
 * 【考试中心-手机端】开始作答接口
 * @author: 蔡建华
 * @date :  2017-05-23
 * @version $Id$
 */

namespace Api\Controller\Answer;

use Common\Service\AnswerService;
use Common\Service\CategoryService;
use Common\Service\PaperService;
use Common\Service\RightService;

class StartExamController extends AbstractController
{
    /**
     * @var  AnswerService
     */
    protected $answer_serv;
    /**
     * @var PaperService
     */
    protected $paper_serv;
    /**
     * @var RightService
     */
    protected $right_serv;


    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }
        // 实例化答卷service
        $this->answer_serv = new AnswerService();
        // 实例化试题Service
        $this->paper_serv = new PaperService();
        // 实例化试题Service
        $this->right_serv = new RightService();

        return true;
    }

    public function Index_post()
    {
        /*
         *  首先，判断试卷状态
         *  再次，判断试卷权限
         *  再次，判断试卷类型 （随机抽取和其他（再次，试卷快照是否存在））
         *  如果不是随机抽题直接（获取试卷内容，试卷基本信息，oa_exam_snapshot，oa_exam_paper）如果是随机抽题，首先或抽题规 再次根据规则随机抽取试题,判断题库题量
         *  接下来，创建答卷表，答卷详情表oa_exam_answer，oa_exam_answer_attach，oa_exam_answer_detail
         *  接下来返回 答卷ID
         * */
        $params = I('post.');
        //试卷ID
        $ep_id = intval($params['ep_id']);
        if (!$ep_id) {
            E('_EMPTY_EP_ID');
            return false;
        }
        // 获取试卷信息
        $data = $this->paper_serv->get_by_conds(array(
            'ep_id' => $ep_id,
            'exam_status>?' => PaperService::PAPER_DRAFT,
            'cate_status' => CategoryService::EC_OPEN_STATES
        ));
        //判断试卷是否存在
        if (empty($data)) {
            E('_ERR_DATA_EXAM_DEL_EXIST');
            return false;
        }
        $ep_status = $this->paper_serv->paper_status($data['exam_status'], $data['begin_time'], $data['end_time']);
        // 判断考试状态
        if ($ep_status == PaperService::STATUS_NOT_START) {
            //未开始
            E('_ERR_EXAM_STATUS_NOT_START');
            return false;
        } elseif ($ep_status == PaperService::STATUS_END) {
            //已结束
            E('_ERR_EXAM_STATUS_END');
            return false;
        } elseif ($ep_status == PaperService::STATUS_STOP) {

            ///已终止
            E('_ERR_EXAM_STATUS_STOP');
            return false;
        }
        //判断试卷权限
        if ($data['is_all'] != PaperService::AUTH_ALL) {
            $right = $this->right_serv->list_by_conds(array(
                "epc_id" => $ep_id,
                'er_type' => PaperService::RIGHT_PAPER
            ));
            if ((!empty($right)) &&$this->right_serv->check_get_quit($right, $this->uid)) {
                    E("_ERR_EXAM_QUIT");
                    return false;
            }
        }
        // 判断考生是否有没有交卷的答卷时。返回答卷ID,否则开始进行创建答卷信息，my_time 代表没有交卷，大于0表示已交卷;
        $rel = $this->answer_serv->get_by_conds(array('ep_id' => $ep_id, 'uid' => $this->uid, 'my_time' => 0));
        if (!empty($rel)) {
            //继续答卷
            $this->_result['ea_id'] = $rel['ea_id'];
        } else {
            //判断试卷是否测评试卷，如果是检查重复考试，如果有则返回，没有则继续考试
            if ($data['paper_type'] == PaperService::EVALUATION_PAPER_TYPE) {
                $count = $this->answer_serv->count_by_conds(array('ep_id' => $ep_id, 'uid' => $this->uid));
                if ($count > 0) {
                    E("_ERR_VISIT_EXAM");
                    return false;
                }
            }
            //开始进行考试
            $rel = $this->answer_serv->paper_start_exam($data, $this->uid);
            if (!$rel) {
                return false;
            }
            $this->_result = $rel;
        }

        return true;
    }
}
