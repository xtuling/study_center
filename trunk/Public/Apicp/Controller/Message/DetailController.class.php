<?php
/**
 * 消息中心-新闻详情
 */
namespace Apicp\Controller\Message;

use VcySDK\Enterprise;
use VcySDK\Service;

class DetailController extends AbstractController
{

    public function Index()
    {
        $id = I('post.emsgId');
        if (empty($id)) {
            E(L('_ERR_PLS_SUBMIT_ID', ['name' => 'ID']));
            return false;
        }

        $enmsgSdk = new Enterprise(Service::instance());
        $this->_result = $enmsgSdk->messageDetail([
            'emsgId' => $id,
            'eaId' => $this->_login->user['eaId']
        ]);

        return true;
    }
}
