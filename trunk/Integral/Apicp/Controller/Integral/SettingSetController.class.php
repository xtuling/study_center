<?php
/**
 * Created by IntelliJ IDEA.
 * 积分设置
 * User: zhoutao
 * Date: 2016/11/15
 * Time: 上午10:07
 */

namespace Apicp\Controller\Integral;

use Common\Common\Cache;
use VcySDK\Integral;
use VcySDK\Service;

class SettingSetController extends AbstractController
{

    public function Index()
    {

        $isOpen = I('post.isOpen');
        $esrsDesc = I('post.esrsDesc');

        if (empty($isOpen) && empty($esrsDesc)) {
            E('_ERR_INTEGRAL_RULE_CANT_EMPTY');
            return false;
        }

        // 获取当前配置信息
        $cache = Cache::instance();
        $strategySetting = $cache->get('Common.StrategySetting');
        // 要修改的配置信息
        if (!empty($isOpen) && in_array($isOpen, $this->isOpenArr)
            && ($isOpen != $strategySetting['eirsEnable'])) {
            $updateData['eirsEnable'] = $isOpen;
//            // 开启积分功能时,至少开启一项积分规则
//            if (($strategySetting['openedRules'] < 1) && ($isOpen == self::OPEN)) {
//                E('_ERR_MUST_HAD_OPEN_ONE_RULE');
//                return false;
//            }
        }
        if (!empty($esrsDesc) && ($esrsDesc != $strategySetting['eirsDesc'])) {
            $updateData['eirsDesc'] = $esrsDesc;
        }
        if (empty($updateData)) {
            return true;
        }
        $updateData['eirsId'] = $strategySetting['eirsId'];

        try {
            $sdk = new Integral(Service::instance());
            $sdk->updateSetting($updateData);
        } catch (\Exception $e) {
            E($e->getMessage());
            return false;
        }

        // 更新缓存
        $cache->set('Common.StrategySetting', null);

        return true;
    }
}
