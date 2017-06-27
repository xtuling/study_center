<?php
/**
 * Created by IntelliJ IDEA.
 * User: zhuxun37
 * Date: 2017/5/30
 * Time: 下午10:18
 */

namespace Frontend\Controller\Index;


class InviteListController extends AbstractController
{

    /**
     * 生成邀请链接(发起邀请)
     */
    public function Index()
    {

        redirectFront('/app/page/invite/invite-list');
    }

}