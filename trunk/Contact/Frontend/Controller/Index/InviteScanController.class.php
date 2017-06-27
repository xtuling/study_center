<?php
/**
 * Created by PhpStorm.
 * User: Slok
 * Date: 16/9/29
 * Time: 15:38
 */

namespace Frontend\Controller\Index;

use Common\Service\InviteSettingService;

class InviteScanController extends AbstractController
{

    protected $_require_login = false;

    /**
     * 邀请二维码扫码跳转
     * @author zhonglei
     */
    public function Index()
    {

        $link_id = I('get.link_id', '', 'trim');

        if (empty($link_id)) {
            E('_ERR_UID_IS_NULL');
        }

        /**
         * $settingServ = new InviteSettingService();
         * $setting = $settingServ->get_by_conds([]);
         */

        redirectFront('/app/page/invite/invitation-card', ['link_id' => $link_id]);
        /**
         * if ($setting['qrcode_expire'] > MILLI_TIME) {
         * redirectFront('/app/page/contacts/invite-company-profile', ['uid' => $uid]);
         * } else {
         * redirectFront('/app/page/contacts/invite-error');
         * }
         */
    }
}
