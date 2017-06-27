<?php
/**
 *  SubmitController.class.php
 * 【考试中心-手机端】考试手动交卷接口
 * @author: 蔡建华
 * @date :  2017-05-23
 * @version $Id$
 */

namespace Api\Controller\Answer;

use Common\Service\AnswerService;

class SubmitController extends AbstractController
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
        // 实例化答卷ervice
        $this->answer_serv = new AnswerService();

        return true;
    }

    public function Index_post()
    {
        /***
         *  首先判断 答卷ID不能为空
         *  其次判断 考试时间范围
         *  再次 根据我的用户ID和答卷ID提交答卷并计算分数
         */
        $params = I('post.');
        $ea_id = intval($params['ea_id']);

        /**
         * 判断答卷ID不能为空
         */
        if (!$ea_id) {
            E('_EMPTY_EA_ID');

            return false;
        }

        // 交卷函数
        $res = $this->answer_serv->submit_papers($ea_id, $this->uid, $award,1);

        if (!$res) {
            return false;
        }

        $this->_result = array('list' => isset($award) ? $award : array());

        return true;
    }
}
