<?php
/**
 * 微信手机端跳转
 * Auth:Xtong
 * Date:2017年06月02日
 */
namespace Frontend\Controller\Index;

class ListController extends \Common\Controller\Frontend\AbstractController
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
