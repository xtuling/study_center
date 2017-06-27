<?php
/**
 * Created by IntelliJ IDEA.
 * 积分规则
 * User: zhoutao
 * Date: 2016/11/15
 * Time: 上午10:46
 */

namespace Api\Controller\Integral;

class RuleController extends AbstractController
{

    public function Index()
    {

        $this->_result = $this->integralCache['eirsDesc'];

        return true;
    }
}
