<?php
/**
 * Created by PhpStorm.
 * User: Slok
 * Date: 16/9/29
 * Time: 15:38
 */

namespace Frontend\Controller\Index;

use Common\Service\InviteUserService;

class CheckListController extends AbstractController
{

    /**
     * 审核列表
     * @author zhonglei
     */
    public function Index()
    {

        redirectFront('/app/page/contacts/myinvite-audit', ['list_type' => InviteUserService::MY_CHECK_LIST]);
    }
}
