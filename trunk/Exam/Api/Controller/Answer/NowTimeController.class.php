<?php
/**
 * NowTimeController.class.php
 *【考试中心-手机端】获取当前时间戳
 * @author: 何岳龙
 * @date :  2017年6月8日16:39:39
 * @version $Id$
 */

namespace Api\Controller\Answer;

class NowTimeController extends AbstractController
{

    public function Index_get()
    {
        $this->_result = array('time' => MILLI_TIME);
        return true;
    }
}
