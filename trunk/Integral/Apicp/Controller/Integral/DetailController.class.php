<?php
/**
 * Created by IntelliJ IDEA.
 * 积分明细
 * User: zhoutao
 * Date: 2016/11/15
 * Time: 上午10:07
 */

namespace Apicp\Controller\Integral;

use VcySDK\Integral;
use VcySDK\Service;

class DetailController extends AbstractController
{

    public function Index()
    {
        $uid = I('post.memUid');
        $miType = I('post.miType', 'mi_type0');

        if (empty($uid)) {
            E('_ERR_EMPTY_UID');
            return false;
        }

        $sdk = new Integral(Service::instance());
        $this->_result = $sdk->detail([
            'memUid' => $uid,
            'miType' => $miType
        ]);

        return true;
    }
}
