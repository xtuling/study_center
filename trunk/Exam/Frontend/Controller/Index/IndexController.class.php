<?php
/**
 * 微信手机端跳转
 * Auth:houyingcai
 * Date:2017年5月4日18:18:32
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
        redirectFront('/app/page/exam/exam-list', array('_identifier' => APP_IDENTIFIER));

    }
}
