<?php
/**
 * Cache.class.php
 * 缓存
 * $Author$
 */

namespace Com;

class Cache
{

    /**
     * 实例化
     *
     * @return Cache
     */
    public static function &instance()
    {

        static $instance;
        if (empty($instance)) {
            $instance = new self();
        }

        return $instance;
    }

    public function __construct()
    {
        // do nothing.
    }

    /**
     * 获取缓存数据
     *
     * @param mixed $name    缓存名称
     * @param mixed $value   缓存值
     * @param mixed $options 缓存配置
     *
     * @return mixed
     */
    public function get($name, $value = '', $options = null)
    {

        $init = false;
        if (! is_array($options) && is_array($name)) {
            // 初始化缓存操作
            $this->init_options($name);
            $init = true;
        } else {
            // 检查缓存名称是否符合规范
            if (!is_array($options)) {
                $options = [];
            }
            $names = explode('.', $name);
            if (1 == count($names)) {
                array_unshift($names, MODULE_NAME);
                $name = implode('.', $names);
            // 是否跳过添加应用标识 skipAddIdentifier true 跳过
            } elseif (!in_array($name, cfg('CACHE_COMMON_FIELD')) && empty($options['skipAddIdentifier'])) {
                // 追加应用标识
                $names[1] = APP_DIR . $names[1];
                $name = implode('.', $names);
            }
            if (empty($options['prefix'])) {
                // 追加企业标识
                $options['prefix'] = QY_DOMAIN . '_';
            }
            $this->init_options($options);
        }

        $result = S($name, $value, $options);

        // 如果缓存不存在或者已过期
        $auto_create = isset($options['auto_create']) && false === $options['auto_create'] ? false : true;
        if (! $init && ! is_null($value) && false === $result && $auto_create) {
            $result = $this->__create($name, $options);
        }

        return $result;
    }

    /**
     * 更新缓存
     *
     * @param mixed $name    缓存名称
     * @param mixed $value   缓存值
     * @param mixed $options 缓存配置
     *
     * @return boolean
     */
    public function set($name, $value, $options = array())
    {
        if (!is_array($options)) {
            $options = [];
        }
        $options['auto_create'] = true;
        $this->get($name, $value, $options);

        return true;
    }

    /**
     * 创建缓存
     *
     * @param string $name    缓存名称
     * @param array  $options 缓存设置
     */
    private function __create($name, $options)
    {

        $names = explode('.', $name);
        $func = $names[1];
        // 去除应用标识
        $func = str_replace(APP_DIR, '', $func);
        // 如果是公共缓存
        if ('Common' == $names[0]) {
            $return = $this->$func();
        } else {
            $class = '\\' . $names[0] . '\Common\Cache';
            $cache = new $class();
            $return = $cache->$func();
        }
        S($name, $return, $options);

        return $return;
    }

    /**
     * 初始化, 保证缓存文件都在同一个目录下
     *
     * @param mixed $options 缓存配置
     *
     * @return boolean
     */
    public function init_options(&$options = null)
    {

        // 如果配置为空
        if (empty($options)) {
            $options = array();
        }

        if (!is_array($options)) {
            $options = [];
        }

        // 如果缓存目录不存在
        if (empty($options['temp'])) {
            $options['temp'] = get_sitedir();
        }

        return true;
    }

    /**
     * 默认处理方法
     *
     * @param $method
     * @param $args
     *
     * @return array
     */
    public function __call($method, $args)
    {

        E('_ERR_CACHE_UNDEFINED');
        return array();
    }

}
