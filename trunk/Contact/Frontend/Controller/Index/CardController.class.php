<?php
/**
 * Created by PhpStorm.
 * User: Slok
 * Date: 16/9/29
 * Time: 15:38
 */

namespace Frontend\Controller\Index;

class CardController extends AbstractController
{

    /**
     * 我的名片
     * @author zhonglei
     */
    public function Index()
    {

        redirectFront('/app/page/contacts/business-card', ['uid' => $this->uid]);
    }
}
