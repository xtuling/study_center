<?php
/**
 * 验证邀请有效性
 */
namespace Apicp\Controller\AdminManager;

use VcySDK\Adminer;
use VcySDK\Exception;
use VcySDK\Service;

class InvitationActiveController extends AbstractController
{
    protected $_require_login = false;

    public function Index()
    {
        $aiaToken = I('post.aiaToken');
        if (empty($aiaToken)) {
            E(L('_ERR_PLS_SUBMIT_ID', ['name' => 'token']));
            return false;
        }

        try {
            $sdk = new Adminer(Service::instance());
            $ucResult = $sdk->inviteInvitationActive(['aiaToken' => $aiaToken]);
            $this->_result = [
                'aiaToken' => $ucResult['aiaToken'],
                'eaMobile' => $ucResult['eaMobile']
            ];
        } catch (Exception $e) {
            /**
             * UC ERROR CODE
             * ADD_ADMIN_NOT_FIND_ERROR
             * ADMIN_INVITE_INVALID
             */
            E($e->getSdkCode());
            return false;
        }

        return true;
    }
}

