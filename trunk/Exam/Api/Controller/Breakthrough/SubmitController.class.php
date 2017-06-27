<?php
/**
 *  SubmitController.class.php
 * 【考试中心-手机端】考试手动交卷接口
 * @author: 蔡建华
 * @date :  2017-05-23
 * @version $Id$
 */

namespace Api\Controller\Breakthrough;

use Common\Service\BreakService;

class SubmitController extends AbstractController
{
    /**
     * @var  BreakService
     */
    protected $break_serv;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }
        // 实例化答卷ervice
        $this->break_serv = new BreakService();

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
        $ebreak_id = intval($params['ebreak_id']);
        /**
         * 判断答卷ID不能为空
         */
        if (!$ebreak_id) {
            E('_EMPTY_EBREAK_ID');
            return false;
        }
        // 闯关交卷
        $rel = $this->break_serv->submit_papers($ebreak_id, $this->uid);
        if (!$rel) {
            return false;
        }

        $this->_result = $rel;

        return true;
    }
}
