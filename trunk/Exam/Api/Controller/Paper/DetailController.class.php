<?php
/**
 * 【考试中心-手机端】获取考试（未参与）详情接口
 *  DetailController.class.php
 * @author: 蔡建华
 * @date :  2017-05-23
 * @version $Id$
 */

namespace Api\Controller\Paper;

use Common\Service\AnswerService;
use Common\Service\CategoryService;
use Common\Service\PaperService;
use Common\Service\RightService;

class DetailController extends AbstractController
{
    /***
     * @var PaperService 试题yService对象
     */
    protected $paper_serv;
    /**
     * @var RightService 权限
     */
    protected $right_serv;
    /**
     * @var AnswerService
     */
    protected $answer_serv;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }
        // 实例化试题Service
        $this->paper_serv = new PaperService();
        // 实例化试答卷Service
        $this->answer_serv = new AnswerService();
        // 实例化试题Service
        $this->right_serv = new RightService();
        return true;
    }

    public function Index_post()
    {
        $params = I('post.');
        //试卷ID
        $ep_id = intval($params['ep_id']);
        if (!$ep_id) {
            E('_EMPTY_EP_ID');
            return false;
        }
        $data = $this->paper_serv->get_by_conds(array(
            'ep_id' => $ep_id
        ));
        //判断试卷信息是否存在
        if (empty($data)) {
            E('_EMPTY_PAPER_DATA');
            return false;
        }
        // 判断分类是否被禁用
        if ($data['cate_status'] != CategoryService::EC_OPEN_STATES) {
            E('_EMPTY_CATE_DATA');
            return false;
        }
        //判断试卷权限
        if ($data['is_all'] != PaperService::AUTH_ALL) {
            $right = $this->right_serv->list_by_conds(array(
                "epc_id" => $ep_id,
                'er_type' => PaperService::RIGHT_PAPER
            ));
            if (!empty($right)) {
                if ($this->right_serv->check_get_quit($right, $this->uid)) {
                    E("_ERR_EXAM_QUIT");
                    return false;
                }
            } else {
                E("_ERR_EXAM_QUIT");
                return false;
            }
            list($list,$right_data) = $this->right_serv->get_right_data(array(
                "epc_id" => $ep_id,
                'er_type' => PaperService::RIGHT_PAPER
            ));
            $data['right'] = $right_data;
        }
        // 格式化试卷新
        $rel = $this->paper_serv->format_paper_detail($data);
        if (!$rel) {
            return false;
        }
        $this->_result = $rel;
        return true;
    }
}
