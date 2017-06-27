<?php

/**
 * Service.class.php
 * 接口操作类
 *
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt LGPL
 * @copyright  Copyright (c) 2014 - ? VcySDK (http://www.vchangyi.com/)
 * @author     zhuxun37
 * @version    1.0.0
 */
namespace VcySDK;

use Think\Exception;

class Service extends Base
{
    // 企业配置
    protected $_enterpriseConfig;

    /**
     * 单例实例化
     *
     * @return null|Service
     */
    public static function &instance()
    {

        static $instance = null;
        if (empty($instance)) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * 构造方法
     * Service constructor.
     */
    public function __construct()
    {

        // do nothing.
    }


    /**
     * 记录数据流
     */
    public function streamJsonData()
    {

        // 接收数据流
        $streamData = file_get_contents("php://input");
        Logger::write('接收到json数据:' . var_export($streamData, true));

        // 将Json转为array
        $arrayData = json_decode($streamData, true);

        // 验证数据签名
        // FIXME zhoutao 2016-08-22 11:22:15 临时去掉回调验证签名
        /**$sig = &ApiSig::instance();
         * if (!$sig->getParamAndCheck($arrayData)) {
         * Logger::write('验证签名错误' . var_export($arrayData, true));
         * throw new Exception('验证签名错误');
         * };*/

        return $arrayData;
    }

    /**
     * 初始化SDK相关信息
     *
     * @param $setting
     * @return bool
     */
    public function initSdk($setting)
    {
        if (!isset($setting['fileConvertApiUrl'])) {
            throw new Exception('_ERROR_MISS_CONFIG_FILECONVERTAPIURL');
        }
        $config = array(
            'apiUrl' => $setting['apiUrl'],
            'enumber' => $setting['enumber'],
            'appid' => empty($setting['appid']) ? '' : $setting['appid'],
            'pluginIdentifier' => $setting['pluginIdentifier'],
            'thirdIdentifier' => $setting['thirdIdentifier'],
            'logPath' => $setting['logPath'],
            'apiSecret' => $setting['apiSecret'],
            'apiSigExpire' => $setting['apiSigExpire'],
            'fileConvertApiUrl' => $setting['fileConvertApiUrl']
        );

        return $this->setConfig($config);
    }

    /**
     * 应用安装回调处理
     *
     * @param array $callbackData 回调数据
     *                             {
     *                             "epId":"772FF3D97F0000017A0F28797968B245",
     *                             "plPluginid":"D194A216C0A8C7BD2C339C03334A40EE",
     *                             "thirdIdentifier":"QY",
     *                             "eplAvailable":1,
     *                             "epEnumber":"t5thr",
     *                             "qysSuiteid":"tjb2af82c5590f1698",
     *                             "flag":false,
     *                             "corpid": "wx59cc12dbc3a0fe4c",
     *                             "url":"http://thr.vchangyi.com/t5thr/Jobtrain/Frontend/Callback/Install"
     *                             }
     * @param object $settingServ 应用setting表实例化
     *
     * @return bool
     */
    public function callbackSetSetting($callbackData, $settingServ)
    {

        $settingList = $settingServ->list_all();
        // 本地键值对数组
        if (!empty($settingList)) {
            $existKey = array_column($settingList, 'key');
            $keyValue = array_column($settingList, 'value');
            $localSetting = array_combine($existKey, $keyValue);
        }

        // 去除不要的数据
        unset($callbackData['url']);
        // 键值替换
        $callBackKeyToLocalKey = [
            'plPluginid' => 'pluginid',
            'qysSuiteid' => 'suiteid',
            'thirdIdentifier' => 'third_identifier',
            'epEnumber' => 'enumber',
        ];
        $insertData = [];
        $updateData = [];
        foreach ($callbackData as $key => $value) {
            // 替换键值
            $key = array_key_exists($key, $callBackKeyToLocalKey) ? $callBackKeyToLocalKey[$key] : $key;

            // 特殊处理
            $value = $key == 'third_identifier' ? strtolower($value) : $value;

            // 本地信息不为空  传输的信息里有这个项  并且数据不一样 则更新
            if (!empty($localSetting) && array_key_exists($key, $localSetting)) {
                if ($value != $localSetting[$key]) {
                    $updateData[$key] = $value;
                }
                continue;
            }

            // 特殊键值
            switch ($key) {
                case 'flag':
                    $insertData[] = [
                        'key' => $key,
                        'value' => $value ? 1 : 0,
                        'comment' => '是否会创建应用菜单: 1:创建，0: 不创建',
                    ];
                    break;
                case 'eplAvailable':
                    $insertData[] = [
                        'key' => $key,
                        'value' => $value,
                        'comment' => '启用状态：0:新应用，1:已启用，2:已删除; 3:已关闭;',
                    ];
                    break;
                case 'third_identifier':
                    $insertData[] = [
                        'key' => $key,
                        'value' => strtolower($value),
                        'comment' => '',
                    ];
                    break;
                default:
                    $insertData[] = [
                        'key' => $key,
                        'value' => $value,
                        'comment' => '',
                    ];
                    break;
            }
        }

        // 更新/写入数据
        if (!empty($insertData)) {
            $settingServ->insert_all($insertData);
        }
        if (!empty($updateData)) {
            foreach ($updateData as $key => $value) {
                $settingServ->update_by_conds(['key' => $key], ['value' => $value]);
            }
        }

        return true;
    }

    /**
     * 应用初始化失败容错
     *
     * @param $config
     * @return bool
     * @throws Exception
     */
    public function initError($config)
    {

        if (!empty($config['appid'])) {
            return true;
        }

        // UC获取应用安装数据
        $pluginServ = new EnterprisePlugin(Service::instance());
        $pluginList = $pluginServ->listAll();

        // 查询应用数据
        foreach ($pluginList as $_plugin) {
            // 应用开启
            if (strcasecmp($_plugin['plIdentifier'], APP_IDENTIFIER) == 0 && $pluginServ->isInstall($_plugin['available'])) {
                if (empty($this->_enterpriseConfig)) {
                    // 获取企业配置信息
                    $enterpriseSdk = new Enterprise(Service::instance());
                    $this->_enterpriseConfig = $enterpriseSdk->listSetting();
                }

                $config = [
                    'epId' => $_plugin['epId'],
                    'plPluginid' => $_plugin['plPluginid'],
                    'eplAvailable' => $_plugin['available'],
                    'thirdIdentifier' => $_plugin['thirdIdentifier'],
                    'epEnumber' => QY_DOMAIN,
                    'qysSuiteid' => $_plugin['qysSuiteid'],
                    'corpid' => $this->_enterpriseConfig['wxqyCorpid'],
                ];

                // 兼容Model、Service包含应用前缀的情况
                $db_prefix = cfg('DB_PREFIX');
                if (substr_count($db_prefix, '_') > 1) {
                    $settingServ = D('Common/Setting', 'Service');
                } else {
                    $settingServ = D('Common/' . APP_DIR . 'Setting', 'Service');
                }

                // 重新写入应用信息
                $this->callbackSetSetting($config, $settingServ);

                // 初始化 应用配置里的appid
                Service::instance()->setConfig([
                    'appid' => $this->_enterpriseConfig['wxqyCorpid'],
                ]);

                return true;
            }
        }

        // 抛错: 重新安装应用
        throw new Exception('_ERROR_MISS_PLUGIN_CONFIG_PLS_REINSTALL');
    }

    /**
     * 排除数组变为对象
     *
     * @param array $arrFrom
     * @param array $keyArr
     * @return bool
     */
    public function getValue(array &$arrFrom, array $keyArr)
    {

        if (empty($arrFrom) || empty($key)) {
            return false;
        }

        foreach ($arrFrom as $_key => &$value) {
            if (in_array($_key, $keyArr)) {
                $value = array_values($value);
            }
        }

        return true;
    }

    /**
     * 存在并且是数组
     *
     * @param array $arr 判断的来源数组
     * @param string $setKey 判断的键值
     * @param array $keyList 存在并且是数组的键值数组
     *
     * @return bool
     */
    public function setAndIsArr($arr, $setKey, &$keyList)
    {

        if (isset($arr[$setKey]) && is_array($arr[$setKey])) {
            $keyList[] = $setKey;
        }

        return true;
    }
}
