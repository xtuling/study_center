<?php
/**
 * 积分商城
 */
namespace Frontend\Controller\Index;

class PrizeListController extends \Common\Controller\Frontend\AbstractController
{

    /**
     * 不是必须登录
     * @var string $_require_login
     */
    protected $_require_login = false;

    public function Index()
    {
        redirectFront('/app/page/integral/integral-mall', array('_identifier' => APP_IDENTIFIER));
    }

}
