<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/27
 * Time: 20:11
 */
namespace Apicp\Controller\Invite;

use VcySDK\Service;
use VcySDK\Enterprise;
use Common\Service\InviteSettingService;

class InfoController extends AbstractController
{

    /**
     * 【通讯录】获取邀请函内容
     * @author liyifei
     */
    public function Index_post()
    {
        $settingServ = new InviteSettingService();
        $data = $settingServ->get_by_conds([]);
        if (empty($data)) {
            E('_ERR_DATA_IS_NULL');
            return false;
        }

        // 企业信息
        $epServ = new Enterprise(Service::instance());
        $ep = $epServ->detail();

        $this->_result = [
            'qy_logo' => $ep['corpSquareLogo'],
            'qy_name' => $ep['corpName'],
            'content' => $data['content'],
            'share_content' => $data['share_content'],
        ];
    }
}
