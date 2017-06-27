<?php
/**
 * DetailController.class.php
 *【考试中心-手机端】获取考试（已参与）详情接口
 * @author: 蔡建华
 * @date :  2017-05-23
 * @version $Id$
 */

namespace Api\Controller\Answer;

use Common\Service\AnswerService;

class DetailController extends AbstractController
{
    /**
     * @var  AnswerService
     */
    protected $answer_serv;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }
        // 实例化答卷service
        $this->answer_serv = new AnswerService();

        return true;
    }

    public function Index_post()
    {
        /**
         *  判断答卷ID和试卷ID必须二选一，
         *  判断根据用户ID和答卷ID或者试卷ID查询答卷记录是否存在，如果存在，获取试卷信息和答题情况列表
         *  其中包括考试范围，试卷基本信息，返回试卷状态
         */
        $params = I('post.');
        //试卷ID
        $ep_id = intval($params['ep_id']);
        //答卷ID
        $ea_id = intval($params['ea_id']);
        // 答题ID和试卷ID不能同时为空
        if (($ea_id == 0 && $ep_id == 0) || ($ea_id > 0 && $ep_id > 0)) {
            E('_ERR_EA_EP_SOME');
            return false;
        }
        // 查询考试详情
        $rel = $this->answer_serv->answer_detail_info($ep_id, $ea_id, $this->uid);
        if (!$rel) {
            return false;
        }
        $this->_result = $rel;

        return true;
    }
}
