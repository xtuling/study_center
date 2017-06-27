<?php
/**
 * 应用安装时的消息回调
 * User: zhuxun37
 * Date: 16/8/11
 * Time: 下午3:44
 */

namespace Frontend\Controller\Callback;

use Common\Service\SettingService;
use Think\Log;
use VcySDK\Service;

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
     * $this->callBackData 格式如下:
     * {
     *   "epId":"B646C6F67F0000017D3965FCF2FD3A2F",
     *   "plPluginid":"D194A216C0A8C7BD2C339C03334A40EE",
     *   "thirdIdentifier":"QY",
     *   "eplAvailable":1,
     *   "epEnumber":"local",
     *   "qysSuiteid":"tj371afbea374f01b2",
     *   "flag":false,
     *   "corpid": "wxac606454f473e98f",
     *   "url":"http://thr.vchangyi.com/local/Contact/Frontend/Callback/Install"
     * }
     *
     * @return bool
     */
    public function Index()
    {
        Log::record(sprintf('---%s %s INSTALL START---', QY_DOMAIN, APP_IDENTIFIER), Log::INFO);
        Log::record(var_export($this->callBackData, true), Log::INFO);
        Log::record(sprintf('---%s %s INSTALL END ---', QY_DOMAIN, APP_IDENTIFIER), Log::INFO);

        $this->Install();
        exit('SUCCESS');
    }

    /**
     * 安装应用
     * @author liyifei
     * @return void
     */
    public function Install()
    {
        exit('SUCCESS');
    }

    /**
     * 卸载应用
     * @author zhonglei
     * @return void
     */
    public function Uninstall()
    {
        exit('SUCCESS');
    }

}
