<?php
/**
 * 应用安装时的消息回调
 * User: zhuxun37
 * Date: 16/8/11
 * Time: 下午3:44
 */

namespace Frontend\Controller\Callback;

use Common\Service\AttrService;
use Common\Service\InviteSettingService;
use Common\Service\SettingService;

class InstallController extends AbstractController
{
    /**
     * 应用状态,可用
     */
    const PLUGIN_STATE_AVAILABLE = 1;

    /**
     * 应用状态,不可用
     */
    const PLUGIN_STATE_UNAVAILABLE = 2;

    /**
     * 安装消息回调
     *
     * @return bool
     */
    public function Index()
    {
        $this->Install();
        exit('SUCCESS');
    }

    /**
     * 安装应用
     * @author zhonglei
     * @return void
     */
    public function Install()
    {
        // 默认数据
        $defaultData = \Common\Sql\DefaultData::installData();

        // 微信端管理权限配置
        $settingService = new SettingService();
        if (!$settings = $settingService->get_by_conds(array())) {
            foreach ($defaultData['setting'] as $_set) {
                $settingService->insert($_set);
            }
        }

        // 邀请函设置写入默认数据
        $inviteSettingServ = new InviteSettingService();
        $inviteSetting = $inviteSettingServ->get_by_conds([]);
        if (!$inviteSetting) {
            $inviteSettingServ->insert($defaultData['invite_setting']);
        }

        // 属性表写入默认数据
        $attrService = new AttrService();
        $attr = $attrService->get_by_conds([]);
        if (!$attr) {
            foreach ($defaultData['attr'] as $attr) {
                $attrService->insert($attr);
            }
        }
    }
}
