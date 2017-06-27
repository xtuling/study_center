<?php
/**
 * Created by IntelliJ IDEA.
 * 获取积分等级配置
 * Date: 17/5/24
 * Time: 下午2:19
 */
namespace Apicp\Controller\Level;

use Common\Common\Cache;

class SettingGetController extends AbstractController
{

    public function Index_post()
    {
        $cache = Cache::instance();
        $levelSettingData = $cache->get('Common.LevelSetting');

        $this->_result = [
            'eis_id' => $levelSettingData['eisId'],
            'upgrade_type' => $levelSettingData['upgradeType'],
            'levels' => $levelSettingData['levels'],
        ];

        return true;
    }

}
