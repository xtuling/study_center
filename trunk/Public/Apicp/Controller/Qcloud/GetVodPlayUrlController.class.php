<?php
/**
 * 获取视频转码结果
 */

namespace Apicp\Controller\Qcloud;

use VcySDK\FileConvert;
use VcySDK\Service;

class GetVodPlayUrlController extends AbstractController
{
    public function Index()
    {
        $qcloudServ = new FileConvert(Service::instance());
        $this->_result = $qcloudServ->getVodPlayUrl(I('post.fileId'));

        return true;
    }
}
