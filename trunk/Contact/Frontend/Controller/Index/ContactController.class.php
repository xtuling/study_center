<?php
/**
 * Created by PhpStorm.
 * User: Slok
 * Date: 16/9/29
 * Time: 15:38
 */

namespace Frontend\Controller\Index;

class ContactController extends AbstractController
{

    /**
     * 通讯录
     * @author zhonglei
     */
    public function Index()
    {

        redirectFront('/app/page/invite/member-list');
    }
}
