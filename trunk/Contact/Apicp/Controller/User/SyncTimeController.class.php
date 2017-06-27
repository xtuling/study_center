<?php
/**
 * Created by PhpStorm.
 * User: zhonglei
 * Date: 2016/12/28
 * Time: 15:51
 */

namespace Apicp\Controller\User;

use Common\Service\SettingService;

class SyncTimeController extends AbstractController
{
    /**
     * 获取最后一次同步的时间
     * @author zhonglei
     */
    public function Index_post()
    {

        $settingServ = new SettingService();
        $setting = $settingServ->get_by_conds(['key' => 'synctime']);
        $synctime = $setting ? intval($setting['value']) : null;

        $this->_result = [
            'synctime' => $synctime,
        ];
    }
}
