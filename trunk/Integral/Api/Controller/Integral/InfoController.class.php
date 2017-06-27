<?php
/**
 * Created by IntelliJ IDEA.
 * 用户基本信息详情
 * User: zhoutao
 * Date: 2016/11/15
 * Time: 上午10:46
 */

namespace Api\Controller\Integral;

use VcySDK\Integral;
use VcySDK\Service;

class InfoController extends AbstractController
{

    public function Index()
    {
        $sdk = new Integral(Service::instance());
        $this->_result = $sdk->detail(['memUid' => $this->_login->user['memUid']]);
        $this->_result['memFace'] = $this->_login->user['memFace'];

        return true;
    }
}
