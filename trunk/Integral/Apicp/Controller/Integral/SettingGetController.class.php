<?php
/**
 * Created by IntelliJ IDEA.
 * 积分设置获取
 * User: zhoutao
 * Date: 2016/11/15
 * Time: 上午10:07
 */

namespace Apicp\Controller\Integral;

use Common\Common\Cache;

class SettingGetController extends AbstractController
{

    public function Index()
    {

        $cache = Cache::instance();
        $settingList = $cache->get('Common.StrategySetting');

        $this->_result = [
            'haveOpenStrategy' => ($settingList['openedRules'] > 0) ? self::TRUE : self::FALSE,
            'isOpen' => $settingList['eirsEnable'],
            'esrsDesc' => $settingList['eirsDesc']
        ];

        return true;
    }
}
