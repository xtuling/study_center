<?php
/**
 * 删除文件
 */

namespace Apicp\Controller\Attachment;

use VcySDK\Service;
use VcySDK\Attach;

class DeleteFileController extends AbstractController
{

    public function Index()
    {
        $atIds = I('post.atIds');

        $params = [
            'atIds' => $atIds
        ];

        $attach = new Attach(Service::instance());
        $result = $attach->deleteFile($params);
        $this->_result = $result;

        return true;
    }
}
