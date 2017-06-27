<?php
/**
 * Created by PhpStorm.
 */
namespace Frontend\Controller\Index;

class InviteEmailController extends AbstractController
{
    protected $_require_login = false;

    /**
     * 邀请管理员 页面
     * @author tony
     */
    public function Index()
    {
        $aiaToken = I('get.aiaToken');

        $url = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
        $url .= sprintf('/admincp/#/invite-admin?%s', http_build_query(['aiaToken' => $aiaToken, '_identifier' => 'common', 'ts' => MILLI_TIME]));
        redirect($url);
    }
}
