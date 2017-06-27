<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 2017/5/23
 * Time: 下午5:56
 */

namespace Api\Controller\Invite;


use Common\Service\InviteLinkService;

class CreateLinkController extends AbstractController
{

    public function Index_post()
    {

        // 检查管理权限
        $this->checkCurrentInvitePower($this->_login->user);

        $inviteLinkService = new InviteLinkService();
        $inviteLinkService->newLink($this->_result, I('post.'), $this->_login->user);

        return true;
    }

}