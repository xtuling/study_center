<?php
/**
 * Created by PhpStorm.
 * User: zhonglei
 * Date: 2017/6/21
 * Time: 17:14
 */
namespace Common\Common;

use Com\Cache as ComCache;
use Common\Service\ConfigService;

class Config extends ComCache
{
    /**
     * 实例化
     * @return Config
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
     * 获取配置数据
     * @return array
     */
    public function answerConfig()
    {
        $configServ = new ConfigService();
        return $configServ->getData();
    }

    /**
     * 获取缓存数据
     * @return array
     */
    public function getCacheData()
    {
        return $this->get('Common.answerConfig');
    }

    /**
     * 清除缓存数据
     * @return bool
     */
    public function clearCacheData()
    {
        return $this->get('Common.answerConfig', null);
    }
}
