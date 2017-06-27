<?php
/**
 * 应用安装时的消息回调
 * User: zhuxun37
 * Date: 16/8/11
 * Time: 下午3:44
 */

namespace Frontend\Controller\Callback;

use Common\Sql\DefaultData;
use Think\Log;
use VcySDK\Service;
use Common\Service\SettingService;
use VcySDK\WxQy\Menu;

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

        // 处理回调数据
        $settingServ = new SettingService();
        $serv = &Service::instance();
        $serv->callbackSetSetting($this->callBackData, $settingServ);

        // 初始化数据
        $this->initData();

        exit('SUCCESS');
    }

    /**
     * 安装应用
     * @return bool
     */
    public function initData()
    {

        $setServ = new SettingService();
        // 默认数据
        $settings = DefaultData::$s_settings;
        // 获取所有的key
        $keys = array_column($settings, 'key');
        // 读取所有记录
        $list = $setServ->list_by_conds(array('key' => $keys));
        $keyExists = array_column($list, 'key');

        // 更新缺失配置
        foreach ($settings as $_set) {
            if (in_array($_set['key'], $keyExists)) {
                continue;
            }

            // 序列化数组
            if (is_array($_set['value'])) {
                $_set['value'] = serialize($_set['value']);
            }

            $setServ->insert($_set);
        }

        return true;
    }

    /**
     * 替换标签
     * @param array $menus 菜单数据
     * @return bool
     */
    protected function _convertMenu(&$menus)
    {

        foreach ($menus as $_key => &$_url) {
            if (is_string($_url) && 'url' == $_key) {
                $_url = str_replace(
                    array('{HOST}', '{ENUMBER}', '{PLUGINID}'),
                    array($_SERVER['HTTP_HOST'], QY_DOMAIN, $this->_setting['pluginid']),
                    $_url
                );
            } elseif (is_array($_url)) {
                $this->_convertMenu($_url);
            }
        }

        return true;
    }

    /**
     * 设置菜单
     *
     * @return void
     */
    protected function _setMenu()
    {

        // 读取菜单配置
        $menus = cfg('WeixinMenu');
        $this->_convertMenu($menus);

        // 实例化消息
        $m_service = new Menu(Service::instance());
        // 提交菜单到UC
        $params = array(
            'buttons' => $menus,
            'callbackUrl' => oaUrl('Frontend/Callback/ThirdMessage/Index')
        );

        $m_service->create($params);
    }

}
