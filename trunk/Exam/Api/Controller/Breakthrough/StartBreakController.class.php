<?php
/**
 *  StartBreakController.class.php
 * 【考试中心-手机端】开始闯关
 * @author: 蔡建华
 * @date :  2017-05-23
 * @version $Id$
 */

namespace Api\Controller\Breakthrough;

use Common\Service\BreakDetailService;
use Common\Service\BreakService;

class StartBreakController extends AbstractController
{
    /**
     * @var BreakService
     */
    var $break_serv;
    var $break_detail_serv;

    public function before_action($action = '')
    {
        if (!parent::before_action($action)) {
            return false;
        }
        // 实例化答卷service
        $this->break_serv = new BreakService();
        $this->break_detail_serv = new BreakDetailService();

        return true;
    }

    public function Index_post()
    {
        $params = I('post.');
        //  课程ID
        $ec_id = intval($params['ec_id']);
        // 试卷ID
        $et_ids = $params['et_ids'];
        $rel = $this->break_serv->start_break($ec_id, $et_ids, $this->uid);
        if (!$rel) {
            return false;
        }
        $this->_result = $rel;
        
        return true;
    }
}
