<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 2016-11-17
 * Time: 11:16:40
 */

namespace Api\Controller\Invite;

use Common\Service\InviteUserService;
use VcySDK\Service;
use VcySDK\Enterprise;
use Common\Common\User;

class SuccessController extends CheckUserController
{

    /**
     * 成功页面
     * @author tony
     */
    public function Index_post()
    {

        $invite_id = I('post.invite_id', 0, 'intval');

        if (empty($invite_id)) {
            E('_ERR_PARAM_IS_NULL');
        }

        // 需要的数据是企业名称，关注二维码，以及邀请人的名称
        $inviteUserServ = new InviteUserService();
        $inviter = $inviteUserServ->get($invite_id);

        // 获取用户信息
        $userServ = new User();
        $inviter = $userServ->getByUid($inviter['invite_uid'], true);

        // 获取企业信息
        $epServ = new Enterprise(Service::instance());
        $ep = $epServ->detail();

        $reuslt = [
            'qy_name' => $ep['corpName'],
            'inviter_name' => $inviter['memUsername'],
            'qy_qrcode' => $ep['corpWxqrcode'],
        ];

        $this->_result = $reuslt;
    }
}
