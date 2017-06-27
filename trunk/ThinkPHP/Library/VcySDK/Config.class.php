<?php
/**
 * Config.class.php
 * SDK配置
 *
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt LGPL
 * @copyright  Copyright (c) 2014 - ? VcySDK (http://www.vchangyi.com/)
 * @author     zhuxun37
 * @version    1.0.0
 */
namespace VcySDK;

use VcySDK\Logger;

class Config
{

    /**
     * 附加配置信息
     * + apiSigExpire int api有效期, 默认值
     * + apiSecret string api秘钥
     * + apiUrl string 接口地址
     * + enumber string 企业账户(域名)
     * + pluginIdentifier string 应用唯一标识
     * + thirdIdentifier string 第三方唯一标识
     * + fileConvertApiUrl string 文件转换接口地址
     * + logPath string 日志目录
     * + logSize int 日志文件大小
     *
     * @var array
     */
    private $config = array(
        'apiSigExpire' => 0,
        'apiSecret' => '',
        'apiUrl' => 'http://localhost',
        'appid' => '',
        'enumber' => '',
        'pluginIdentifier' => '',
        'thirdIdentifier' => '',
        'fileConvertApiUrl' => '',
        'logPath' => '',
        'logSize' => 10485760
    );

    /**
     * 单例实例化
     *
     * @return null|Config
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
     */
    public function __construct()
    {

        // 指定错误日志目录
        $this->config['logPath'] = dirname(__FILE__) . '/Logs/';
    }

    /**
     * 修改配置
     *
     * @param array $config 配置数组
     *
     * @return boolean
     */
    public function setConfig($config = array())
    {

        // 遍历配置数组, 逐个修改
        foreach ($config as $name => $value) {
            // 只更新已存在的配置
            if (array_key_exists($name, $this->config)) {
                $this->config[$name] = $value;
            }
        }

        return true;
    }

    /**
     * GET方法, 获取指定配置
     *
     * @param string $name 配置名称
     *
     * @return multitype:|NULL
     */
    public function __get($name)
    {

        // 如果有该配置项, 则返回
        if (isset($this->config[$name])) {
            return $this->config[$name];
        }

        Logger::write('config: ' . var_export($this->config, true) . '| $name: ' . $name);

        return null;
    }

    /**
     * SET方法, 设置配置值
     *
     * @param string $name  键值
     * @param mixed  $value 值
     *
     * @return boolean
     */
    public function __set($name, $value)
    {

        // 如果有该配置项, 则修改
        if (isset($this->config[$name])) {
            $this->config[$name] = $value;
        }

        return true;
    }
}
