<?php
/**
 * Created by IntelliJ IDEA.
 * 更新积分等级
 * Date: 17/5/24
 * Time: 下午2:19
 */
namespace Apicp\Controller\Level;

use Com\PackageValidate;
use Common\Common\Cache;
use Common\Model\SettingModel;
use VcySDK\Integral;
use VcySDK\Service;

class SettingUpdateController extends AbstractController
{

    public function Index_post()
    {

        $validate = new PackageValidate(
            [
                'eis_id' => 'require',
                'upgrade_type' => 'require|in:' . SettingModel::UPGRADE_TYPE_AVAILABLE . ',' . SettingModel::UPGRADE_TYPE_CUMULATIVE,
                'levels' => 'require',
            ],
            [
                'eis_id.require' => L('_ERR_PARAM_CAN_NOT_BE_EMPTY', ['name' => '等级记录ID']),
                'upgrade_type.require' => L('_ERR_PARAM_CAN_NOT_BE_EMPTY', ['name' => '升级依据']),
                'upgrade_type.in' => L('_ERR_PARAM_MUST_IN_RANGE', [
                    'name' => '升级依据类型',
                    'range' => SettingModel::UPGRADE_TYPE_AVAILABLE . ',' . SettingModel::UPGRADE_TYPE_CUMULATIVE
                ])
            ],
            [
                'eis_id',
                'upgrade_type',
                'levels',
            ]
        );

        $postData = $validate->postData;

        $this->checkRequestParams($postData);

        $updateParams['eisId'] = $postData['eis_id'];
        $updateParams['upgradeType'] = $postData['upgrade_type'];
        $updateParams['levels'] = $postData['levels'];

        try {
            $sdk = new Integral(Service::instance());
            $sdk->updateIntegralLevelSetting($updateParams);
        } catch (\Exception $e) {
            E($e->getMessage());
            return false;
        }

        $cache = Cache::instance();
        $cache->set('Common.LevelSetting', null);

        $this->_result = $postData;

        return true;
    }

    /**
     * 验证请求参数是否合法
     * @param $postData
     */
    private function checkRequestParams($postData)
    {
        $levels = $postData['levels'];

        $levelsCount = count($levels);

        // 积分等级最少两级
        if ($levelsCount < 2) {
            E('_ERR_INTEGRAL_LEVELS_SIZE');
        }

        // 第一级最大积分值不能小于1
        if (current($levels)['max'] < 1) {
            E('_ERR_INTEGRAL_FIRST_LEVELS_MAX');
        }

        // 最后一级最大积分值必须是-1
        if (end($levels)['max'] != -1) {
            E('_ERR_INTEGRAL_LAST_LEVELS_MAX');
        }

        reset($levels);

        // 最后一级的下标
        $lastLevelIndex = $levelsCount - 1;

        // 上一级的最大积分值
        $previousLevelMax = 0;

        foreach ($levels as $currentIndex => $level) {

            if (empty($level['name'])) {
                E(L('_ERR_INTEGRAL_LEVELS_NAME_NULL', ['currentLevel' => $currentIndex + 1]));
            }

            if (empty($level['max'])) {
                E(L('_ERR_INTEGRAL_LEVELS_MAX_NULL', ['currentLevel' => $currentIndex + 1]));
            }

            if ($currentIndex == 0 || $currentIndex == $lastLevelIndex) {
                $previousLevelMax = $level['max'];
                continue;
            }

            // 当前等级最大积分值必须大于上个等级的最大积分值
            if ($previousLevelMax != 0 && $level['max'] <= $previousLevelMax) {
                E(L('_ERR_INTEGRAL_ADJACENT_LEVELS_MAX', ['currentLevel' => $currentIndex + 1, 'previousLevel' => $currentIndex]));
            }

            $previousLevelMax = $level['max'];
        }
    }

}
