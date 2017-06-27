<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/18
 * Time: 11:52
 */

namespace Api\Controller\Invite;

use Common\Service\InviteLinkService;
use VcySDK\Service;
use VcySDK\Enterprise;
use Common\Common\User;
use Common\Service\InviteSettingService;

class DetailController extends CheckUserController
{

    /**
     * 邀请函详情
     * @author zhonglei
     */
    public function Index_post()
    {

        $epService = new Enterprise(Service::instance());
        $ep = $epService->detail();
        $link_id = I('post.link_id', '', 'trim');

        // 如果已经接受了邀请
        $user = array();
        if ($this->_hasAcceptAndWrite($user, $link_id)) {
            $this->_result = array(
                'inviteUser' => $user,
                'qy_qrcode' => $ep['corpWxqrcode']
            );
            return true;
        }

        $this->_checkLinkId($link_id);

        $settingService = new InviteSettingService();
        $setting = $settingService->getSetting();

        $this->_result = [
            'qy_logo' => $ep['corpSquareLogo'],
            'qy_name' => $ep['corpName'],
            'content' => $setting['content'],
            'qy_qrcode' => $ep['corpWxqrcode']
        ];
    }
}
