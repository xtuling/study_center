<?php
/**
 * 勋章开始
 */
namespace Frontend\Controller\Index;

class MedalController extends \Common\Controller\Frontend\AbstractController
{

    /**
     * 不是必须登录
     * @var string $_require_login
     */
    protected $_require_login = false;

    public function Index()
    {
        redirectFront('/app/page/integral/medal-list', array('_identifier' => APP_IDENTIFIER));
    }

}
