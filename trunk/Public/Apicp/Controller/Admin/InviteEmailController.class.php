<?php
/**
 * 发送管理员邀请邮件
 */

namespace Apicp\Controller\Admin;

use Common\Common\ShortUrl;
use VcySDK\Adminer;
use VcySDK\Enterprise;
use VcySDK\Service;

class InviteEmailController extends AbstractAnonymousController
{

    public function Index()
    {
        $eaId = I('post.eaId');
        if (empty($eaId)) {
            E(L('_ERR_PLS_SUBMIT_ID', ['name' => 'ID']));
            return false;
        }

        try {
            $enterpriseSdk = new Enterprise(Service::instance());
            $enterpriseData = $enterpriseSdk->detail();
            $adminerSdk = new Adminer(Service::instance());
            $adminer = $adminerSdk->fetch(['eaId' => $eaId]);

            $sdk = new Adminer(Service::instance());
            $aiaToken = md5(NOW_TIME . QY_DOMAIN . $eaId . random(8));
            $mobileFrontUrl = oaUrl('Frontend/Index/InviteEmail/Index', ['aiaToken' => $aiaToken]) . '?_identifier=common';
            $eaMobile = substr($this->_login->user['eaMobile'], 0, 3) . '****' . substr($this->_login->user['eaMobile'], -4);
            $inviteEmailData = [
                'eaId' => $eaId,
                'mcTplName' => Adminer::INVITE_EMAIL_TYPE_INVITE,
                'aiaToken' => $aiaToken,
                'mcSubject' => '畅移云工作平台管理员邀请',
                'mcVars' => [
                    "%adminer_name%" => $adminer['eaRealname'],
                    "%qy_name%" => $enterpriseData['epName'],
                    "%user_name%" => $this->_login->user['eaRealname'],
                    "%adminer_mobile%" => $eaMobile,
                    "%qrcode%" => $enterpriseData['corpWxqrcode'],
                    "%date%" => rgmdate(NOW_TIME, 'Y-m-d'),
                    "%url%" => $mobileFrontUrl
                ],
                'smsMessage' => '【畅移信息】尊敬的' . $adminer['eaRealname'] .
                    '，你已被 “' . $this->_login->user['eaRealname'] .
                    '（' . $eaMobile . '）”邀请成为【' .
                    $enterpriseData['epName'] . '】在畅移云工作平台的管理员，请点击以下链接激活您的账号。（该链接在48小时内有效）' .
                    ShortUrl::create($mobileFrontUrl),
            ];

            $sdk->inviteSendInvitation($inviteEmailData);
        } catch (\Exception $e) {
            E($e->getMessage());
            return false;
        }

        return true;
    }
}
