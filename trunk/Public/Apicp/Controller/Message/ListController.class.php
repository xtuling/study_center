<?php
/**
 * 消息中心-列表
 */
namespace Apicp\Controller\Message;

use VcySDK\Enterprise;
use VcySDK\Service;

class ListController extends AbstractController
{
    public function Index()
    {
        $paramData['pageNum'] = I('post.page', 1, 'intval');
        $paramData['pageSize'] = I('post.limit', 15, 'intval');
        $paramData['emrReadStatus'] = I('post.type', 0, 'intval');

        $enmsgSdk = new Enterprise(Service::instance());
        $this->_result = $enmsgSdk->messageList($paramData);

        return true;
    }

}
