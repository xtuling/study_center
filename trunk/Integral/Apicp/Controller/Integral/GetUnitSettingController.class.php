<?php
/**
 * Created by IntelliJ IDEA.
 * 企业积分名称单位配置获取
 * User: zs_anything
 * Date: 2017/05/27
 * Time: 上午10:07
 */

namespace Apicp\Controller\Integral;

use Common\Common\Cache;

class GetUnitSettingController extends AbstractController
{

    public function Index()
    {

        $cache = Cache::instance();
        $settingList = $cache->get('Common.EnterpriseIntgrlCommonSetting');
        $settingList = array_combine_by_key($settingList, "eisKey");

        $resSetting = $settingList['unit'];
        $unit = json_decode($resSetting['eisValue'], true);

        $this->_result = [
            'eis_id' => $resSetting['eisId'],
            'eis_key' => $resSetting['eisKey'],
            'eis_value' => $unit,
        ];

        return true;
    }
}
