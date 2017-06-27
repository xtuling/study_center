<?php
/**
 * Created by IntelliJ IDEA.
 * 更新积分名称单位配置
 * User: zs_anything
 * Date: 17/5/27
 * Time: 下午2:19
 */
namespace Apicp\Controller\Integral;

use Com\PackageValidate;
use Common\Common\Cache;
use Common\Model\SettingModel;
use VcySDK\Integral;
use VcySDK\Service;

class UpdateUnitSettingController extends AbstractController
{

    public function Index_post()
    {

        $validate = new PackageValidate(
            [
                'eis_id' => 'require',
                'eis_key' => 'require',
                'eis_value' => 'require',
            ],
            [
                'eis_id.require' => L('_ERR_PARAM_CAN_NOT_BE_EMPTY', ['name' => '记录ID']),
                'eis_key.require' => L('_ERR_PARAM_CAN_NOT_BE_EMPTY', ['name' => '配置标识']),
                'eis_value.require' => L('_ERR_PARAM_CAN_NOT_BE_EMPTY', ['name' => '配置值']),

            ],
            [
                'eis_id',
                'eis_key',
                'eis_value',
            ]
        );

        $postData = $validate->postData;

        if (empty($postData['eis_value']['name'])) {
            E('_ERR_INTEGRAL_NAME_NULL');
        }

        if (empty($postData['eis_value']['unit'])) {
            E('_ERR_INTEGRAL_UNIT_NULL');
        }

        $updateParams['eisId'] = $postData['eis_id'];
        $updateParams['eisKey'] = $postData['eis_key'];
        $updateParams['eisValue'] = json_encode($postData['eis_value'], JSON_UNESCAPED_SLASHES);

        try {
            $sdk = new Integral(Service::instance());
            $sdk->updateEpIntegralCommonSetting($updateParams);
        } catch (\Exception $e) {
            E($e->getMessage());
            return false;
        }

        $cache = Cache::instance();
        $cache->set('Common.EnterpriseIntgrlCommonSetting', null);

        $this->_result = $postData;

        return true;
    }

}
