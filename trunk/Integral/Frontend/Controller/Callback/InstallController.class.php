<?php
/**
 * 应用安装时的消息回调
 * User: zhuxun37
 * Date: 16/8/11
 * Time: 下午3:44
 */

namespace Frontend\Controller\Callback;

use Think\Log;
use VcySDK\Service;
use Common\Service\SettingService;

class InstallController extends AbstractController
{

    /**
     * 安装消息回调
     *
     * $this->callBackData 格式如下:
     * {
     *   "epId":"772FF3D97F0000017A0F28797968B245",
     *   "plPluginid":"D194A216C0A8C7BD2C339C03334A40EE",
     *   "thirdIdentifier":"QY",
     *   "eplAvailable":1,
     *   "epEnumber":"t5thr",
     *   "qysSuiteid":"tjb2af82c5590f1698",
     *   "flag":false,
     *   "corpid": "wx59cc12dbc3a0fe4c",
     *   "url":"http://thr.vchangyi.com/t5thr/Jobtrain/Frontend/Callback/Install"
     * }
     *
     * @return bool
     */
    public function Index()
    {
        Log::record(sprintf('---%s %s INSTALL START---', QY_DOMAIN, APP_IDENTIFIER), Log::INFO);
        Log::record(var_export($this->callBackData, true), Log::INFO);
        Log::record(sprintf('---%s %s INSTALL END ---', QY_DOMAIN, APP_IDENTIFIER), Log::INFO);

        exit('SUCCESS');
    }
}
