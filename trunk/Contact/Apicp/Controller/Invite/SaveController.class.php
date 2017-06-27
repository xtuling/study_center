<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/27
 * Time: 20:35
 */
namespace Apicp\Controller\Invite;

use Common\Service\InviteSettingService;

class SaveController extends AbstractController
{

    /**
     * 【通讯录】保存邀请函内容
     * @author liyifei
     */
    public function Index_post()
    {
        $content = I('post.content', '' , 'trim');
        $shareContent = I('post.share_content', '' , 'trim');
        if (empty($content) || empty($shareContent)) {
            E('_ERR_PARAM_IS_NULL');
            return false;
        }

        $settingServ = new InviteSettingService();
        $upData = [
            'content' => $content,
            'share_content' => $shareContent,
        ];
        $settingServ->update_by_conds([], $upData);
    }
}
