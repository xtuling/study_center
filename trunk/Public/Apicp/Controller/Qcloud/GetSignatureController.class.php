<?php
/**
 * 腾讯云点播视频 获取签名
 */

namespace Apicp\Controller\Qcloud;

use VcySDK\FileConvert;
use VcySDK\Service;

class GetSignatureController extends AbstractController
{
    public function Index()
    {
        $qcloudServ = new FileConvert(Service::instance());
        $this->_result = $qcloudServ->getSignature(['args' => I('post.args')]);

        return true;
    }
}
