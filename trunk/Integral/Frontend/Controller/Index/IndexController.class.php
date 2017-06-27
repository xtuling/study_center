<?php
/**
 * 积分开始接口
 */
namespace Frontend\Controller\Index;

class IndexController extends \Common\Controller\Frontend\AbstractController
{

    /**
     * 不是必须登录
     * @var string $_require_login
     */
    protected $_require_login = false;

    public function Index()
    {
        redirectFront('/app/page/integral/my-integral', array('_identifier' => APP_IDENTIFIER));
    }

}
