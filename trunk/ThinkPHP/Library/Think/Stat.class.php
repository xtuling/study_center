<?php
/**
 * Stat.class.php
 * 接口访问统计记录
 * @author Deepseath
 * @version $Id$
 */
namespace Think;

use Com\Cookie;

/**
 * 接口访问统计记录
 * @uses self::save();
 */
class Stat
{

    /**
     * 操作句柄
     */
    protected static $handler;

    /**
     * 当前访问者信息
     */
    protected static $userInfo;

    /**
     * 当前访问日志信息
     */
    protected static $statInfo;

    /**
     * 构造方法
     * @param array $config 配置信息
     * @param array $userInfo 用户信息
     * <pre>
     * + userId 访问者 ID
     * + userName 访问者名字
     * + isAdmincp 是否是管理后台请求
     * </pre>
     * @return boolean
     */
    public function __construct($config, $userInfo)
    {
        // 检查配置项
        if (empty($config) || !isset($config['type'])) {
            return false;
        }

        self::$userInfo = $userInfo;

        // 根据不同存储类型记录日志
        $config['type'] = ucwords($config['type']);
        if ($config['type'] == 'File') {
            // 使用文件系统存储日志
            $storageType = strtolower($config['type']);
            $class = 'Think\\Stat\\Storage\\' . $config['type'];
            if (empty($config['storage'][$storageType])) {
                return false;
            }
            self::$handler = new $class($config['storage'][$storageType]);
        }
    }

    /**
     * 当前访问信息
     */
    static public function statInfo()
    {
        $userId = isset(self::$userInfo['userId']) ? self::$userInfo['userId'] : '';
        $userName = isset(self::$userInfo['userName']) ? self::$userInfo['userName'] : '';
        $isAdmincp = isset(self::$userInfo['isAdmincp']) ? self::$userInfo['isAdmincp'] : 0;
        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? (string) $_SERVER['HTTP_USER_AGENT'] : '';
        $ip = get_client_ip();
        $timestamp = round(microtime(true), 3) * 1000;

        $cookie = &Cookie::instance();
        $accessTag = $cookie->getx('accessTag');
        if (empty($accessTag)) {
            // 上次访问标记不存在，则重新生成并写入 cookie
            $accessTag = md5(QY_DOMAIN . "\t" . $isAdmincp . "\t" . $userId . "\t" . $userAgent . "\t" . $timestamp);
            $cookie->setx('accessTag', $accessTag);
        }

        self::$statInfo = [
            // 当前访问身份标记
            'accessTag' => $accessTag,
            // 访问时间戳
            'timestamp' => $timestamp,
            // 当前访问者 ID
            'userId' => $userId,
            // 当前访问者名字
            'userName' => $userName,
            // 前台访问还是管理后台访问
            'isAdmincp' => $isAdmincp,
            // 执行的动作接口
            'action' => __ACTION__,
            // 应用目录
            'appDir' => APP_DIR,
            // 应用唯一标识符
            'appIdentifier' => APP_IDENTIFIER,
            // 企业标识符
            'domain' => QY_DOMAIN,
            // 当前访问者的 IP 地址
            'ip' => $ip,
            // 浏览器代理信息
            'userAgent' => $userAgent,
            // 请求的 URL
            'uri' => (defined('BOARD_URL') && BOARD_URL) ? BOARD_URL
                        : (isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : ''),
            // 请求的 HOST
            'host' => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '',
            // 脚本路径
            'scriptFilename' => isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : '',
            // 访问来源 URL
            'referer' => isset($_SERVER['HTTP_REFERER']) ? (string) $_SERVER['HTTP_REFERER'] : '',
            // GET 数据
            'getData' => $_GET,
            // POST 数据
            'postData' => $_POST,
            // Cookie 原始数据
            'cookieSource' => $_COOKIE
        ];

        // 将比较大的数据进行缩小便于查询和存储
        self::$statInfo = self::_shrinkParamValues(self::$statInfo);

        return self::$statInfo;
    }

    /**
     * 调用静态存储类型方法
     * @param string $method
     * @param array $args
     */
    static public function __callstatic($method, $args)
    {
        // 调用存储驱动的方法
        if (method_exists(self::$handler, $method)) {
            return call_user_func_array(array(
                self::$handler,
                $method
            ), $args);
        }
    }

    /**
     * 对参数值进行容量缩小
     * @param unknown $params
     */
    static protected function _shrinkParamValues($params)
    {
        if (is_array($params)) {
            foreach ($params as &$_value) {
                $_value = self::_shrinkParamValues($_value);
            }
            return $params;
        } else {
            if (strlen($params) > 255) {
                return mb_substr($params, 0, 100) . ' ... ...';
            } else {
                return $params;
            }
        }
    }
}
