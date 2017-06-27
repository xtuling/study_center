<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/18
 * Time: 11:52
 */

namespace Api\Controller\Invite;

use VcySDK\Service;
use VcySDK\Enterprise;
use Common\Service\InviteSettingService;

class FastController extends AbstractController
{

    /**
     * 未认证企业号在分享时,无法自定义Url,只能分享当前Url,也就是快速邀请页
     * 因此本接口不能设置为必须登陆
     * 当外部人员访问快速邀请页调用本接口时,返回_ERR_OUTUSER_ACCESS_DENIED
     * 前端根据此错误,跳转至邀请函页面
     */
    protected $_require_login = false;

    /**
     * 快速邀请
     * @author zhonglei
     */
    public function Index_post()
    {

        $link_id = (int)I('post.link_id');
        if (empty($this->_login->user)) {
            E('_ERR_OUTUSER_ACCESS_DENIED');
        }

        $user = $this->_login->user;

        $settingServ = new InviteSettingService();
        $setting = $settingServ->get_by_conds([]);
        // 检查管理权限
        $this->checkCurrentInvitePower($user);

        $epServ = new Enterprise(Service::instance());
        $ep = $epServ->detail();

        $this->_result = [
            'username' => $user['memUsername'],
            'qy_logo' => $ep['corpSquareLogo'],
            'qy_name' => $ep['corpName'],
            'content' => $setting['content'],
            'share_content' => $setting['share_content'],
            'qrcode' => oaUrl('Frontend/Index/InviteQrcode/Index', ['link_id' => $link_id]),
            'qrcode_expire' => $setting['qrcode_expire'],
        ];
    }
}
