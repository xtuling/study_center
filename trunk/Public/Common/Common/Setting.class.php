<?php
/**
 * Setting.class.php
 * 公共页面缓存操作
 * @author Deepseath
 * @version $Id$
 */
namespace Common\Common;

use Common\Service\CommonSettingService;
use Common\Model\CommonSettingModel;
use Com\Cache as ComCache;

/**
 * 公共 setting 配置值的缓存读取
 * @uses 调用方法：<pre>
 * // 读取某个配置项
 * $set = &Common\CommonSetting::instance();
 * $config = $set->get('Common.appConfig');
 * // 读取所有配置项
 * $set = &Setting::instance();
 * $config = $set->get('Common.listAll');
 * </pre>
 */
class Setting extends ComCache
{

    /**
     * 实例化
     *
     * @return \Common\Common\Cache
     */
    public static function &instance()
    {

        static $instance;
        if (empty($instance)) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * 获取 setting 的缓存信息
     *
     * @return array
     */
    public function listAll()
    {

        // 读取数据
        $servSetting = new CommonSettingService();
        $listAll = $servSetting->list_all();
        // 获取键值对
        $setting = [];
        if (empty($listAll)) {
            return $setting;
        }

        // 整理数据
        foreach ($listAll as $_set) {
            // 如果是数组, 则
            if (CommonSettingModel::TYPE_ARRAY == $_set['type']) {
                $_set['value'] = unserialize($_set['value']);
            }

            $setting[$_set['key']] = $_set['value'];
        }

        return $setting;
    }

    public function __call($method, $args)
    {

        if (in_array($method, ['appConfig'])) {
            // 获取指定 setting 值
            $settingService = new CommonSettingService();
            $set = $settingService->get_by_conds(['key' => $method]);
            if (!empty($set) && CommonSettingModel::TYPE_ARRAY == $set['type']) {
                return unserialize($set['value']);
            }
        }

        return [];
    }
}
