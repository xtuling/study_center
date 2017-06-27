<?php
/**
 * 新增视频转码文件回调数据
 */

namespace Apicp\Controller\Qcloud;

use VcySDK\FileConvert;
use VcySDK\Service;

class AddTranscodeUrlController extends AbstractController
{
    public function Index()
    {
        $qcloudServ = new FileConvert(Service::instance());
        $fileId = I('post.fileId');
        $notifyUrl = I('post.notifyUrl');

        $this->_result = $qcloudServ->add(array(
            qtnFileId => $fileId,
            qtnNotifyUrl => $notifyUrl
        ));

        return true;
    }
}
