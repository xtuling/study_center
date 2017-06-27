<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace Apicp\Controller\AdminManager;

use VcySDK\Service;
use VcySDK\Adminer;
use VcySDK\Enterprise;
use Common\Common\ShortUrl;

abstract class AbstractController extends \Common\Controller\Apicp\AbstractController
{

    /**
     * @type Adminer
     */
    protected $_sdkAdminer = null;

    public function before_action($action = '')
    {

        if (!parent::before_action($action)) {
            return false;
        }

        // 调用UC接口，查询管理员详情
        $this->_sdkAdminer = new Adminer(Service::instance());
        return true;
    }

    /**
     * 发送 邮箱&短信 邀请
     * @return bool
     */
    protected function inviteMsgSend($eaId)
    {
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
                    '，您已被 “' . $this->_login->user['eaRealname'] .
                    '（' . $eaMobile . '）”邀请成为【' .
                    $enterpriseData['epName'] . '企业号】在畅移云工作平台的管理员，请点击以下链接激活您的账号。（该链接在48小时内有效）' .
                    ShortUrl::create($mobileFrontUrl),
            ];

            $sdk->inviteSendInvitation($inviteEmailData);
        } catch (\Exception $e) {
            // 不做报错处理
        }

        return true;
    }
}
